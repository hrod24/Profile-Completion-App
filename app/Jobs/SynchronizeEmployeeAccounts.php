<?php

namespace App\Jobs;

use App\Models\employee_details;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class SynchronizeEmployeeAccounts implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Beri waktu cukup untuk worker queue.
     */
    public int $timeout = 1200;

    /**
     * Hindari sinkronisasi otomatis berulang
     * apabila terjadi error data.
     */
    public int $tries = 1;

    public function handle(): void
    {
        /*
         * Hanya hapus akun role employee yang sudah nonaktif.
         * Admin dan OD tidak ikut dihapus.
         */
        User::query()
            ->where('role', 'employee')
            ->whereIn(
                'employee_id',
                employee_details::query()
                    ->select('employee_id')
                    ->where('active', 0)
            )
            ->delete();

        /*
         * Proses employee aktif sedikit demi sedikit.
         */
        employee_details::query()
            ->select([
                'id',
                'employee_id',
                'display_name',
                'primary_email',
            ])
            ->where('active', 1)
            ->whereNotNull('employee_id')
            ->where('employee_id', '<>', '')
            ->orderBy('id')
            ->chunkById(
                100,
                function ($employees): void {
                    foreach ($employees as $employee) {
                        $employeeId = trim(
                            (string) $employee->employee_id
                        );

                        $email = strtolower(
                            trim(
                                (string) $employee->primary_email
                            )
                        );

                        /*
                         * Jika users.email tidak nullable,
                         * employee tanpa email harus dilewati.
                         */
                        if ($employeeId === '' || $email === '') {
                            Log::warning(
                                'Akun employee dilewati saat sinkronisasi.',
                                [
                                    'employee_id' => $employeeId,
                                    'reason' => 'Employee ID atau email kosong.',
                                ]
                            );

                            continue;
                        }

                        $user = User::query()
                            ->firstOrNew([
                                'employee_id' => $employeeId,
                            ]);

                        $isNewAccount = !$user->exists;

                        $user->name =
                            filled($employee->display_name)
                            ? $employee->display_name
                            : $employeeId;

                        $user->email = $email;

                        /*
                         * Password dan role hanya ditetapkan
                         * ketika akun pertama kali dibuat.
                         */
                        if ($isNewAccount) {
                            $user->role = 'employee';
                            $user->password = $employeeId;
                        }

                        $user->save();
                    }
                }
            );
    }

    public function failed(Throwable $exception): void
    {
        Log::error(
            'Job sinkronisasi akun employee gagal.',
            [
                'message' => $exception->getMessage(),
                'exception' => get_class($exception),
            ]
        );
    }
}
