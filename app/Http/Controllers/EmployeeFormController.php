<?php

namespace App\Http\Controllers;

use App\Models\employee_details;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;


class EmployeeFormController extends Controller
{
    /**
     * Menampilkan form dan melakukan sync berdasarkan employee_id.
     */
    public function show(Request $request)
    {
        $user = $this->authenticatedEmployee($request);

        $existingDocuments = [
            'ijazah_filename' =>
            filled($user->ijazah_filename)
                && Storage::disk('public')->exists(
                    $user->ijazah_filename
                ),

            'ktp_filename' =>
            filled($user->ktp_filename)
                && Storage::disk('public')->exists(
                    $user->ktp_filename
                ),

            'kk_filename' =>
            filled($user->kk_filename)
                && Storage::disk('public')->exists(
                    $user->kk_filename
                ),

            'npwp_filename' =>
            filled($user->npwp_filename)
                && Storage::disk('public')->exists(
                    $user->npwp_filename
                ),
        ];

        return view('pages.form', [
            'title' => 'Employee Form',
            'user' => $user,
            'existingDocuments' => $existingDocuments,
            'employeeRequiredFields' => config(
                'employee.employee_required_fields',
                []
            ),
        ]);
    }

    /**
     * Menyimpan data yang menjadi tanggung jawab employee.
     */
    public function submit(Request $request)
    {
        /*
     * Employee selalu diambil dari akun yang login.
     */
        $employee = $this->authenticatedEmployee($request);

        /*
     * Jangan percaya employee_id dari browser.
     */
        $request->merge([
            'employee_id' => $employee->employee_id,
            'ktp_postal_code' =>
            $request->input('current_postal_code'),
        ]);

        $employeeRequiredFields = config(
            'employee.employee_required_fields',
            []
        );

        $emptyValues = array_map(
            fn($value) => strtoupper(
                trim((string) $value)
            ),
            config('employee.empty_values', [])
        );

        $notPlaceholder = function (
            string $attribute,
            mixed $value,
            \Closure $fail
        ) use ($emptyValues): void {
            $normalizedValue = strtoupper(
                trim((string) $value)
            );

            if (
                in_array(
                    $normalizedValue,
                    $emptyValues,
                    true
                )
            ) {
                $fail(
                    "Field {$attribute} harus diisi dengan data yang valid."
                );
            }
        };

        $fileFields = [
            'ktp_filename',
            'kk_filename',
            'ijazah_filename',
            'npwp_filename',
        ];

        $documentIsMissing = function (
            string $field
        ) use ($employee): bool {
            $path = $employee->{$field};

            return blank($path)
                || !Storage::disk('public')->exists($path);
        };

        /*
     * Gunakan seluruh rules milikmu.
     */
        $rules = [
            'employee_id' => [
                'required',
                'string',
                'max:20',
                'exists:employee_details,employee_id',
            ],

            // Semua rules field text milikmu...

            'ktp_filename' => [
                Rule::requiredIf(
                    fn() => $documentIsMissing('ktp_filename')
                ),
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:5120',
            ],

            'kk_filename' => [
                Rule::requiredIf(
                    fn() => $documentIsMissing('kk_filename')
                ),
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:5120',
            ],

            'ijazah_filename' => [
                Rule::requiredIf(
                    fn() => $documentIsMissing('ijazah_filename')
                ),
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:5120',
            ],

            'npwp_filename' => [
                Rule::requiredIf(
                    fn() => $documentIsMissing('npwp_filename')
                ),
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:5120',
            ],
        ];

        $unknownFields = array_diff(
            $employeeRequiredFields,
            array_keys($rules)
        );

        if ($unknownFields !== []) {
            throw new \RuntimeException(
                'Validation belum tersedia untuk field: '
                    . implode(', ', $unknownFields)
            );
        }

        foreach ($employeeRequiredFields as $field) {
            if (in_array($field, $fileFields, true)) {
                continue;
            }

            array_unshift(
                $rules[$field],
                'required'
            );

            $rules[$field][] = $notPlaceholder;
        }

        $validated = $request->validate(
            $rules,
            [
                'date_of_birth.before_or_equal' =>
                'Tanggal lahir tidak boleh melebihi hari ini.',

                'primary_contact_number.regex' =>
                'Format nomor kontak utama tidak valid.',

                'emergency_contact_no.regex' =>
                'Format nomor kontak darurat tidak valid.',

                'ktp_number.regex' =>
                'Nomor KTP hanya boleh berisi angka.',

                'education_end.gte' =>
                'Tahun selesai pendidikan tidak boleh lebih kecil dari tahun mulai pendidikan.',

                'ktp_filename.required' =>
                'File KTP wajib diupload.',

                'kk_filename.required' =>
                'File KK wajib diupload.',

                'ijazah_filename.required' =>
                'File ijazah wajib diupload.',

                'npwp_filename.required' =>
                'File NPWP wajib diupload.',
            ]
        );

        /*
     * Simpan seluruh field text yang tervalidasi,
     * termasuk field opsional yang diisi employee.
     */
        $employeeData = Arr::except(
            $validated,
            array_merge(
                $fileFields,
                ['employee_id']
            )
        );

        $employee->fill($employeeData);

        $fileFieldMap = [
            'ktp_filename' => [
                'prefix' => 'ktp',
                'folder' => 'ktp',
            ],
            'kk_filename' => [
                'prefix' => 'kk',
                'folder' => 'kk',
            ],
            'ijazah_filename' => [
                'prefix' => 'ijazah',
                'folder' => 'ijazah',
            ],
            'npwp_filename' => [
                'prefix' => 'npwp',
                'folder' => 'npwp',
            ],
        ];

        $safeEmployeeId = preg_replace(
            '/[^A-Za-z0-9_-]/',
            '_',
            $employee->employee_id
        );

        foreach ($fileFieldMap as $field => $config) {
            if (!$request->hasFile($field)) {
                continue;
            }

            $file = $request->file($field);

            $extension = strtolower(
                $file->getClientOriginalExtension()
            );

            $fileName =
                $config['prefix']
                . '_'
                . $safeEmployeeId
                . '.'
                . $extension;

            $directory =
                'employee-documents/'
                . $config['folder'];

            $oldPath = $employee->{$field};

            /*
         * Simpan file baru lebih dahulu.
         */
            $newPath = $file->storeAs(
                $directory,
                $fileName,
                'public'
            );

            $employee->{$field} = $newPath;

            /*
         * Hapus file lama jika nama/path-nya berbeda.
         */
            if (
                filled($oldPath)
                && $oldPath !== $newPath
                && Storage::disk('public')->exists($oldPath)
            ) {
                Storage::disk('public')->delete($oldPath);
            }
        }

        /*
     * Simpan data terlebih dahulu tanpa langsung menandai complete.
     */
        $employee->save();
        $employee->refresh();

        $recognizedAsComplete = employee_details::query()
            ->whereKey($employee->id)
            ->employeeDataComplete()
            ->exists();

        if (!$recognizedAsComplete) {
            if (!is_null($employee->employee_completed_at)) {
                $employee->update([
                    'employee_completed_at' => null,
                ]);
            }

            return redirect()
                ->route('employee.form')
                ->with(
                    'error',
                    'Data tersimpan, tetapi masih ada field employee yang belum dianggap lengkap.'
                );
        }

        if (is_null($employee->employee_completed_at)) {
            $employee->update([
                'employee_completed_at' => now(),
            ]);
        }

        return redirect()
            ->route('employee.form')
            ->with(
                'success',
                'Data employee berhasil dilengkapi.'
            );
    }

    private function authenticatedEmployee(
        Request $request
    ): employee_details {
        $employeeId = $request->user()->employee_id;

        abort_if(
            blank($employeeId),
            403,
            'Akun tidak terhubung dengan data employee.'
        );

        return employee_details::query()
            ->where('employee_id', $employeeId)
            ->firstOrFail();
    }
}
