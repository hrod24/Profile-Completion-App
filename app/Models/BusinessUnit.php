<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BusinessUnit extends Model
{
    protected $table = 'business_units';

    protected $primaryKey = 'business_unit_code';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'business_unit_code',
        'business_unit_name',
    ];

    public function departments(): BelongsToMany
    {
        return $this->belongsToMany(
            Department::class,
            'business_unit_and_departments',
            'business_unit_code',
            'department_code',
            'business_unit_code',
            'department_code'
        );
    }

    public function employees(): HasMany
    {
        return $this->hasMany(
            employee_details::class,
            'business_unit_org_element_1',
            'business_unit_code'
        );
    }
}
