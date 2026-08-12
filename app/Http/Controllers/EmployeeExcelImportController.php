<?php

namespace App\Http\Controllers;

use App\Services\EmployeeExcelChunkService;
use Illuminate\Http\JsonResponse;
use App\Imports\EmployeeDetailsWorkbookImport;
use App\Models\employee_details;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\User;
use App\Models\EmployeeImportBatch;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use RuntimeException;
use App\Support\DashboardExportWorkbook;
use Illuminate\Validation\ValidationException;
use Throwable;

class EmployeeExcelImportController extends Controller
{
    public function create(): View
    {
        return view('pages.employee-import', [
            'title' => 'Upload Employee Excel',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {   
        $validated = $request->validate([
            'excel_file' => [
                'required',
                'file',
                'mimes:xlsx,xls',
                'max:20480',
            ],
        ], [
            'excel_file.required' => 'File Excel wajib dipilih.',
            'excel_file.mimes' => 'File harus berformat XLSX atau XLS.',
            'excel_file.max' => 'Ukuran file maksimal 20 MB.',
        ]);

        $import = new EmployeeDetailsWorkbookImport();
        $file = $validated['excel_file'];

        try {
            /*
             * Seluruh import berada di dalam satu transaksi.
             * Jika satu baris gagal, perubahan dari batch sebelumnya ikut rollback.
             */
            DB::transaction(function () use ($import, $file): void {
                /*
     * Tahap 1:
     * insert employee baru dan update employee yang masih ada.
     */
                Excel::import($import, $file);

                /*
     * Tahap 2:
     * nonaktifkan employee yang tidak ada dalam Excel terbaru.
     */
                $import->finalizeStatuses();
            }, 3);
        } catch (RuntimeException $exception) {
            return back()
                ->withInput()
                ->with('error', $exception->getMessage());
        } catch (Throwable $exception) {
            Log::error('Employee Excel import gagal.', [
                'message' => $exception->getMessage(),
                'exception' => get_class($exception),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
            ]);

            return back()
                ->withInput()
                ->with(
                    'error',
                    app()->isLocal()
                        ? $exception->getMessage()
                        : 'Import gagal karena terjadi kesalahan database atau format file.'
                );
        }

        $summary = $import->summary();

        return redirect()
            ->route('employee.import.create')
            ->with(
                'success',
                sprintf(
                    'Import berhasil. %d employee aktif dalam file, %d data baru, %d data diperbarui, %d employee dinonaktifkan, dan %d baris dilewati.',
                    $summary['active_in_file'],
                    $summary['inserted'],
                    $summary['updated'],
                    $summary['deactivated'],
                    $summary['skipped']
                )
            );
    }
    public function startImport(
        Request $request
    ): JsonResponse {
        $validated = $request->validate([
            'excel_file' => [
                'required',
                'file',
                'mimes:xlsx,xls',
                'max:20480',
            ],
        ]);

        /*
     * Gunakan object hasil validasi.
     */
        $file = $validated['excel_file'];

        /*
     * Tolak file hasil export dashboard sebelum file
     * disimpan atau batch import dibuat.
     */
        try {
            $isDashboardExport =
                DashboardExportWorkbook::isDashboardExport(
                    $file
                );
        } catch (Throwable $exception) {
            report($exception);

            throw ValidationException::withMessages([
                'excel_file' => [
                    'The Excel file could not be read. '
                        . 'Please upload a valid employee import file.',
                ],
            ]);
        }

        if ($isDashboardExport) {
            throw ValidationException::withMessages([
                'excel_file' => [
                    'Files downloaded from the dashboard are '
                        . 'report-only files and cannot be uploaded '
                        . 'through Employee Import. Please use the '
                        . 'official employee master import file.',
                ],
            ]);
        }

        $batchId = (string) Str::uuid();

        $extension = strtolower(
            $file->getClientOriginalExtension()
        );

        $path = null;

        try {
            /*
         * Simpan secara eksplisit pada disk local.
         */
            $path = $file->storeAs(
                'employee-imports',
                "{$batchId}.{$extension}",
                'local'
            );

            if (!$path) {
                throw new RuntimeException(
                    'The uploaded file could not be stored.'
                );
            }

            $fullPath = Storage::disk('local')->path(
                $path
            );

            $reader = IOFactory::createReaderForFile(
                $fullPath
            );

            /*
         * Hanya membaca informasi worksheet terlebih dahulu.
         * Belum memuat seluruh isi workbook.
         */
            $worksheetInfo = $reader->listWorksheetInfo(
                $fullPath
            );

            $employeeSheet = collect(
                $worksheetInfo
            )->firstWhere(
                'worksheetName',
                'Employee Details'
            );

            if (!$employeeSheet) {
                throw ValidationException::withMessages([
                    'excel_file' => [
                        'The Employee Details sheet was not found.',
                    ],
                ]);
            }

            /*
         * Konfigurasi posisi row.
         *
         * Gunakan nilai ini apabila:
         * row 1 = header
         * row 2 = employee pertama
         */
            $headerRows = 1;
            $firstDataRow = 2;

            $worksheetTotalRows = (int) (
                $employeeSheet['totalRows'] ?? 0
            );

            $totalRows = max(
                $worksheetTotalRows - $headerRows,
                0
            );

            if ($totalRows === 0) {
                throw ValidationException::withMessages([
                    'excel_file' => [
                        'The file does not contain any employee data.',
                    ],
                ]);
            }

            EmployeeImportBatch::query()->create([
                'id' => $batchId,
                'file_path' => $path,
                'total_rows' => $totalRows,
                'next_row' => $firstDataRow,
                'status' => 'processing',
            ]);

            return response()->json([
                'message' => 'The file was successfully received.',
                'batch_id' => $batchId,
                'total' => $totalRows,
                'processed' => 0,
            ]);
        } catch (ValidationException $exception) {
            /*
         * Validation error tetap dikembalikan sebagai HTTP 422.
         */
            if ($path !== null) {
                Storage::disk('local')->delete(
                    $path
                );
            }

            throw $exception;
        } catch (Throwable $exception) {
            /*
         * Hapus file jika pembacaan workbook atau penyimpanan
         * batch mengalami kegagalan.
         */
            if ($path !== null) {
                Storage::disk('local')->delete(
                    $path
                );
            }

            report($exception);

            throw ValidationException::withMessages([
                'excel_file' => [
                    'The import could not be prepared. '
                        . 'Please check the Excel file and try again.',
                ],
            ]);
        }
    }

    public function finishImport(
        Request $request
    ): JsonResponse {
        $validated = $request->validate([
            'batch_id' => [
                'required',
                'uuid',
                'exists:employee_import_batches,id',
            ],
        ]);

        $batch = EmployeeImportBatch::query()
            ->findOrFail($validated['batch_id']);

        if (
            $batch->processed < $batch->total_rows
        ) {
            return response()->json([
                'message' =>
                'Seluruh data belum selesai diproses.',
            ], 422);
        }

        $deactivated = employee_details::query()
            ->where('active', 1)
            ->where(function ($query) use ($batch): void {
                $query
                    ->whereNull(
                        'last_seen_import_batch'
                    )
                    ->orWhere(
                        'last_seen_import_batch',
                        '!=',
                        $batch->id
                    );
            })
            ->update([
                'active' => 0,
                'inactive_at' => now(),
                'updated_at' => now(),
            ]);

        $batch->update([
            'deactivated' => $deactivated,
            'status' => 'completed',
        ]);

        Storage::delete($batch->file_path);

        return response()->json([
            'message' => 'Import berhasil.',
            'processed' => $batch->processed,
            'inserted' => $batch->inserted,
            'updated' => $batch->updated,
            'skipped' => $batch->skipped,
            'deactivated' => $deactivated,
        ]);
    }

    public function processImportChunk(
        Request $request,
        EmployeeExcelChunkService $service
    ): JsonResponse {
        $validated = $request->validate([
            'batch_id' => [
                'required',
                'uuid',
                'exists:employee_import_batches,id',
            ],
        ]);

        $batch = EmployeeImportBatch::query()
            ->findOrFail(
                $validated['batch_id']
            );

        if ($batch->status !== 'processing') {
            return response()->json([
                'message' =>
                'Batch import tidak sedang diproses.',
            ], 422);
        }

        $limit = 200;

        try {
            DB::transaction(
                function () use (
                    $batch,
                    $service,
                    $limit
                ): void {
                    $startRow = (int) $batch->next_row;

                    $result = $service->process(
                        batch: $batch,
                        startRow: $startRow,
                        limit: $limit
                    );

                    if (
                        $result['processed'] <= 0
                        && $batch->processed
                        < $batch->total_rows
                    ) {
                        throw new RuntimeException(
                            'Chunk tidak memproses baris apa pun.'
                        );
                    }

                    $newProcessed = min(
                        ((int) $batch->processed)
                            + $result['processed'],
                        (int) $batch->total_rows
                    );

                    $batch->update([
                        'next_row' =>
                        $result['next_row'],

                        'processed' =>
                        $newProcessed,

                        'inserted' => ((int) $batch->inserted)
                            + $result['inserted'],

                        'updated' => ((int) $batch->updated)
                            + $result['updated'],

                        'skipped' => ((int) $batch->skipped)
                            + $result['skipped'],
                    ]);
                },
                3
            );
        } catch (Throwable $exception) {
            $batch->update([
                'status' => 'failed',
                'error_message' =>
                $exception->getMessage(),
            ]);

            Log::error('Employee chunk import gagal.', [
                'batch_id' => $batch->id,
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ]);

            return response()->json([
                'message' =>
                $exception->getMessage(),
            ], 422);
        }

        $batch->refresh();

        return response()->json([
            'done' =>
            $batch->processed
                >= $batch->total_rows,

            'processed' =>
            (int) $batch->processed,

            'total' =>
            (int) $batch->total_rows,

            'inserted' =>
            (int) $batch->inserted,

            'updated' =>
            (int) $batch->updated,

            'skipped' =>
            (int) $batch->skipped,
        ]);
    }


    public function startSynchronization(): JsonResponse
    {
        /*
     * Hapus akun employee yang tidak lagi mempunyai
     * record employee aktif.
     *
     * Admin dan OD tidak akan terhapus karena hanya
     * role employee yang diproses.
     */
        $deleted = User::query()
            ->where('role', 'employee')
            ->whereNotExists(function ($query): void {
                $query
                    ->selectRaw('1')
                    ->from('employee_details')
                    ->whereColumn(
                        'employee_details.employee_id',
                        'users.employee_id'
                    )
                    ->where('employee_details.active', 1);
            })
            ->delete();

        /*
     * Hanya employee aktif yang akan dibuatkan
     * atau diperbarui akun login-nya.
     */
        $total = employee_details::query()
            ->where('active', 1)
            ->whereNotNull('employee_id')
            ->where('employee_id', '<>', '')
            ->count();

        return response()->json([
            'message' => 'Sinkronisasi dimulai.',
            'total' => $total,
            'deleted' => $deleted,
            'after_id' => 0,
        ]);
    }

    public function synchronizeChunk(
        Request $request
    ): JsonResponse {
        $validated = $request->validate([
            'after_id' => [
                'nullable',
                'integer',
                'min:0',
            ],
        ]);

        $afterId = (int) (
            $validated['after_id'] ?? 0
        );

        /*
     * Turunkan menjadi 20 jika 50 masih terasa lama.
     */
        $limit = 50;

        $employees = employee_details::query()
            ->select([
                'id',
                'employee_id',
                'display_name',
            ])
            ->where('active', 1)
            ->whereNotNull('employee_id')
            ->where('employee_id', '<>', '')
            ->where('id', '>', $afterId)
            ->orderBy('id')
            ->limit($limit)
            ->get();

        /*
     * Tidak ada data tersisa.
     */
        if ($employees->isEmpty()) {
            return response()->json([
                'done' => true,
                'processed' => 0,
                'created' => 0,
                'updated' => 0,
                'after_id' => $afterId,
            ]);
        }

        $employeeIds = $employees
            ->pluck('employee_id')
            ->map(
                fn($employeeId) =>
                trim((string) $employeeId)
            )
            ->filter()
            ->values();

        /*
     * Ambil users yang sudah ada dalam satu query.
     * Ini menghindari satu SELECT untuk setiap employee.
     */
        $existingUsers = User::query()
            ->whereIn('employee_id', $employeeIds)
            ->get()
            ->keyBy('employee_id');

        $newUsers = [];
        $created = 0;
        $updated = 0;
        $now = now();

        foreach ($employees as $employee) {
            $employeeId = trim(
                (string) $employee->employee_id
            );

            if ($employeeId === '') {
                continue;
            }

            $displayName = trim(
                (string) $employee->display_name
            );

            $displayName = $displayName !== ''
                ? $displayName
                : $employeeId;

            $existingUser = $existingUsers->get(
                $employeeId
            );

            /*
         * Akun sudah ada: hanya update ketika nama berubah.
         */
            if ($existingUser) {
                if ($existingUser->name !== $displayName) {
                    $existingUser->name = $displayName;
                    $existingUser->save();

                    $updated++;
                }

                continue;
            }

            /*
         * Akun baru dikumpulkan terlebih dahulu,
         * kemudian dimasukkan dengan satu query insert.
         */
            $newUsers[] = [
                'employee_id' => $employeeId,
                'name' => $displayName,
                'role' => 'employee',
                'password' => Hash::make($employeeId),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if ($newUsers !== []) {
            User::query()->insert($newUsers);

            $created = count($newUsers);
        }

        $lastId = (int) $employees
            ->last()
            ->id;

        return response()->json([
            'done' => $employees->count() < $limit,
            'processed' => $employees->count(),
            'created' => $created,
            'updated' => $updated,
            'after_id' => $lastId,
        ]);
    }
}
