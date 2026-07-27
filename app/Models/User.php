<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'employee_id',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(
            employee_details::class,
            'employee_id',
            'employee_id'
        );
    }

    public function isAdmin(): bool
    {
        return in_array(
            $this->role,
            ['admin', 'od'],
            true
        );
    }

    public function isEmployee(): bool
    {
        return $this->role === 'employee';
    }
}
