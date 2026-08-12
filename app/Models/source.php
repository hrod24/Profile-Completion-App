<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Source extends Model
{
    use HasFactory;

    protected $fillable = ['employee_level_code', 'employee_level_name', 'source'];

    public function employees(): HasMany
    {
        return $this->hasMany(
            employee_details::class,
            'employee_level_code',
            'employee_level_code'
        );
    }
}
