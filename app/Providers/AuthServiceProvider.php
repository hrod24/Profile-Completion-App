<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        //
    ];

    public function boot(): void
    {
        Gate::define(
            'access-admin-pages',
            function (User $user): bool {
                return in_array(
                    $user->role,
                    ['admin', 'od'],
                    true
                );
            }
        );

        Gate::define(
            'access-employee-form',
            function (User $user): bool {
                return $user->role === 'employee'
                    && filled($user->employee_id);
            }
        );
    }
}
