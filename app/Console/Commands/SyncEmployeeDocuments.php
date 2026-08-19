<?php

namespace App\Console\Commands;

use App\Models\employee_details;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class SyncEmployeeDocuments extends Command
{
    protected $signature = 'employee:sync-documents
                            {--apply : Update database}
                            {--force : Timpa path lama yang sudah ada}';

    protected $description =
        'Sinkronisasi attachment employee berdasarkan NIP pada nama file';

    public function handle(): int
    {
        $disk = Storage::disk('public');

        /*
         * Sesuaikan dengan nama folder kamu.
         *
         * Jika folder sebenarnya:
         * storage/app/public/employee-document
         *
         * ubah menjadi:
         * $baseDirectory = 'employee-document';
         */
        $baseDirectory = 'employee-documents';

        /*
         * Folder → kolom database.
         */
        $documentTypes = [
            'ktp' => 'ktp_filename',
            'kk' => 'kk_filename',
            'ijazah' => 'ijazah_filename',
            'npwp' => 'npwp_filename',
        ];

        $allowedExtensions = [
            'pdf',
            'jpg',
            'jpeg',
            'png',
        ];

        /*
         * Ambil employee satu kali saja.
         */
        $employees = employee_details::query()
            ->get([
                'id',
                'employee_id',
                'display_name',
                'ktp_filename',
                'kk_filename',
                'ijazah_filename',
                'npwp_filename',
            ])
            ->keyBy(
                fn ($employee) =>
                    trim((string) $employee->employee_id)
            );

        $totalFiles = 0;
        $matched = 0;
        $updated = 0;
        $alreadyExists = 0;
        $employeeNotFound = 0;
        $invalidFiles = 0;

        $notFoundRows = [];

        foreach (
            $documentTypes
            as $folder => $databaseField
        ) {
            $directory =
                "{$baseDirectory}/{$folder}";

            $this->newLine();

            $this->info(
                strtoupper($folder)
                . " → {$databaseField}"
            );

            if (!$disk->exists($directory)) {
                $this->warn(
                    "Folder tidak ditemukan: {$directory}"
                );

                continue;
            }

            /*
             * Contoh:
             *
             * employee-documents/ktp/123456.pdf
             */
            $files = $disk->files($directory);

            foreach ($files as $path) {
                $totalFiles++;

                $extension = strtolower(
                    pathinfo(
                        $path,
                        PATHINFO_EXTENSION
                    )
                );

                if (
                    !in_array(
                        $extension,
                        $allowedExtensions,
                        true
                    )
                ) {
                    $invalidFiles++;

                    $this->warn(
                        "[SKIP] Format tidak didukung: {$path}"
                    );

                    continue;
                }

                /*
                 * Nama file tanpa extension = NIP.
                 *
                 * 123456.pdf
                 * ↓
                 * 123456
                 */
                $employeeId = trim(
                    pathinfo(
                        $path,
                        PATHINFO_FILENAME
                    )
                );

                if ($employeeId === '') {
                    $invalidFiles++;

                    continue;
                }

                $employee =
                    $employees->get($employeeId);

                if (!$employee) {
                    $employeeNotFound++;

                    $notFoundRows[] = [
                        $folder,
                        $employeeId,
                        $path,
                    ];

                    $this->warn(
                        "[NOT FOUND] {$employeeId} | {$path}"
                    );

                    continue;
                }

                $matched++;

                /*
                 * Jangan overwrite data lama secara default.
                 */
                if (
                    filled($employee->{$databaseField})
                    && !$this->option('force')
                ) {
                    $alreadyExists++;

                    $this->line(
                        "[SKIP] {$employeeId}"
                        . " | {$databaseField}"
                        . " sudah memiliki value"
                    );

                    continue;
                }

                $this->line(
                    "[MATCH] {$employeeId}"
                    . " | {$databaseField}"
                    . " → {$path}"
                );

                /*
                 * Tanpa --apply hanya preview.
                 */
                if (!$this->option('apply')) {
                    continue;
                }

                $employee->{$databaseField} =
                    $path;

                $employee->save();

                $updated++;
            }
        }

        $this->newLine();

        $this->info(
            '========== HASIL SINKRONISASI =========='
        );

        $this->table(
            [
                'Status',
                'Jumlah',
            ],
            [
                [
                    'Total file',
                    $totalFiles,
                ],
                [
                    'Cocok dengan employee',
                    $matched,
                ],
                [
                    'Database di-update',
                    $updated,
                ],
                [
                    'DB sudah punya path',
                    $alreadyExists,
                ],
                [
                    'Employee tidak ditemukan',
                    $employeeNotFound,
                ],
                [
                    'File tidak valid',
                    $invalidFiles,
                ],
            ]
        );

        if (!$this->option('apply')) {
            $this->newLine();

            $this->warn(
                'DRY RUN - database BELUM diubah.'
            );

            $this->line(
                'Periksa hasil di atas terlebih dahulu.'
            );

            $this->newLine();

            $this->info(
                'Jika sudah benar jalankan:'
            );

            $this->line(
                'php artisan employee:sync-documents --apply'
            );
        }

        if (!empty($notFoundRows)) {
            $this->newLine();

            $this->warn(
                'File yang NIP-nya tidak ditemukan di database:'
            );

            $this->table(
                [
                    'Jenis',
                    'NIP',
                    'File',
                ],
                $notFoundRows
            );
        }

        return self::SUCCESS;
    }
}