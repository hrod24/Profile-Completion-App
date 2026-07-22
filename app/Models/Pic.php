<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pic extends Model
{
    use HasFactory;

    protected $fillable = [
        'nip',
        'name',
    ];

    public function employees(): HasMany
    {
        return $this->hasMany(
            employee_details::class,
            'pic_nip',
            'nip'
        );
    }
}
