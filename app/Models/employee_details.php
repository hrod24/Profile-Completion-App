<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class employee_details extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $with = ['department', 'pic', 'businessUnit'];

    protected $casts = [
        'date_of_birth' => 'date',
        'date_of_join' => 'date',
        'education_from' => 'date',
        'education_end' => 'date',
        'employee_completed_at' => 'datetime',
        'active' => 'boolean',
        'inactive_at' => 'datetime',
    ];

    public function businessUnit(): BelongsTo
    {
        return $this->belongsTo(
            BusinessUnit::class,
            'business_unit_org_element_1',
            'business_unit_code'
        );
    }

    public function pic(): BelongsTo
    {
        return $this->belongsTo(
            Pic::class,
            'pic_nip',
            'nip'
        );
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(
            Department::class,
            'department_org_element_2',
            'department_code'
        );
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', 1);
    }

    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('active', 0);
    }

    /**
     * Employee yang masih memiliki kolom wajib HR kosong.
     */
    public function scopeHrIncomplete(Builder $query): Builder
    {
        $requiredFields = config('employee.hr_required_fields', []);
        $emptyValues = config('employee.empty_values', []);

        return $query->where(function (Builder $query) use (
            $requiredFields,
            $emptyValues
        ) {
            foreach ($requiredFields as $field) {
                /*
                 * Nama kolom berasal dari file config aplikasi,
                 * bukan dari input pengguna.
                 */
                $query->orWhereRaw(
                    "COALESCE(TRIM(CAST(`{$field}` AS CHAR)), '') = ''"
                );

                if (!empty($emptyValues)) {
                    $placeholders = implode(
                        ',',
                        array_fill(0, count($emptyValues), '?')
                    );

                    $normalizedValues = array_map(
                        fn($value) => strtoupper(trim($value)),
                        $emptyValues
                    );

                    $query->orWhereRaw(
                        "UPPER(TRIM(CAST(`{$field}` AS CHAR))) 
                        IN ({$placeholders})",
                        $normalizedValues
                    );
                }
            }
        });
    }

    /**
     * Employee yang seluruh kolom wajib HR-nya sudah lengkap.
     */
    public function scopeHrComplete(Builder $query): Builder
    {
        $requiredFields = config('employee.hr_required_fields', []);
        $emptyValues = config('employee.empty_values', []);

        foreach ($requiredFields as $field) {
            $query->whereRaw(
                "COALESCE(TRIM(CAST(`{$field}` AS CHAR)), '') != ''"
            );

            if (!empty($emptyValues)) {
                $placeholders = implode(
                    ',',
                    array_fill(0, count($emptyValues), '?')
                );

                $normalizedValues = array_map(
                    fn($value) => strtoupper(trim($value)),
                    $emptyValues
                );

                $query->whereRaw(
                    "UPPER(TRIM(CAST(`{$field}` AS CHAR))) 
                    NOT IN ({$placeholders})",
                    $normalizedValues
                );
            }
        }

        return $query;
    }

    /**
     * Employee yang seluruh kolom wajib employee-nya sudah terisi.
     */
    public function scopeEmployeeDataComplete(Builder $query): Builder
    {
        $requiredFields = config(
            'employee.employee_required_fields',
            []
        );

        $emptyValues = config(
            'employee.empty_values',
            []
        );

        foreach ($requiredFields as $field) {
            /*
         * Tidak boleh NULL, string kosong,
         * atau hanya berisi spasi.
         */
            $query->whereRaw(
                "COALESCE(TRIM(CAST(`{$field}` AS CHAR)), '') != ''"
            );

            /*
         * Tidak boleh berisi nilai placeholder
         * seperti "-", "--", atau "N/A".
         */
            if (!empty($emptyValues)) {
                $placeholders = implode(
                    ',',
                    array_fill(0, count($emptyValues), '?')
                );

                $normalizedValues = array_map(
                    fn($value) => strtoupper(trim($value)),
                    $emptyValues
                );

                $query->whereRaw(
                    "UPPER(TRIM(CAST(`{$field}` AS CHAR)))
                 NOT IN ({$placeholders})",
                    $normalizedValues
                );
            }
        }

        return $query;
    }

    /**
     * Menghitung jumlah field yang sudah terisi.
     */
    private function calculateCompletion(array $fields): array
    {
        $fields = array_values(array_unique($fields));

        $totalFields = count($fields);

        if ($totalFields === 0) {
            return [
                'filled' => 0,
                'total' => 0,
                'percentage' => 0,
            ];
        }

        $emptyValues = array_map(
            fn($value) => strtoupper(trim((string) $value)),
            config('employee.empty_values', [])
        );

        $filledFields = 0;

        foreach ($fields as $field) {
            $value = $this->getAttribute($field);

            if (is_null($value)) {
                continue;
            }

            if ($value instanceof \DateTimeInterface) {
                $normalizedValue = $value->format('Y-m-d');
            } else {
                $normalizedValue = trim((string) $value);
            }

            if ($normalizedValue === '') {
                continue;
            }

            if (
                in_array(
                    strtoupper($normalizedValue),
                    $emptyValues,
                    true
                )
            ) {
                continue;
            }

            /*
         * Nilai 0 tetap dianggap terisi.
         */
            $filledFields++;
        }

        return [
            'filled' => $filledFields,
            'total' => $totalFields,
            'percentage' => round(
                ($filledFields / $totalFields) * 100,
                2
            ),
        ];
    }

    /**
     * Persentase kelengkapan data yang diisi employee.
     */
    public function getProfileCompletionAttribute(): float
    {
        return $this->calculateCompletion(
            config('employee.employee_required_fields', [])
        )['percentage'];
    }

    /**
     * Jumlah kolom employee yang sudah terisi.
     */
    public function getProfileCompletionFilledAttribute(): int
    {
        return $this->calculateCompletion(
            config('employee.employee_required_fields', [])
        )['filled'];
    }

    /**
     * Total kolom wajib employee.
     */
    public function getProfileCompletionTotalAttribute(): int
    {
        return $this->calculateCompletion(
            config('employee.employee_required_fields', [])
        )['total'];
    }

    /**
     * Persentase kelengkapan data yang diisi HR/OD.
     */
    public function getProfileOdCompletionAttribute(): float
    {
        return $this->calculateCompletion(
            config('employee.hr_required_fields', [])
        )['percentage'];
    }

    /**
     * Jumlah kolom HR yang sudah terisi.
     */
    public function getProfileOdCompletionFilledAttribute(): int
    {
        return $this->calculateCompletion(
            config('employee.hr_required_fields', [])
        )['filled'];
    }

    /**
     * Total kolom wajib HR.
     */
    public function getProfileOdCompletionTotalAttribute(): int
    {
        return $this->calculateCompletion(
            config('employee.hr_required_fields', [])
        )['total'];
    }

    /**
     * Persentase kelengkapan seluruh data:
     * field employee + field HR/OD.
     */
    public function getOverallProfileCompletionAttribute(): float
    {
        $fields = array_merge(
            config('employee.employee_required_fields', []),
            config('employee.hr_required_fields', [])
        );

        return $this->calculateCompletion($fields)['percentage'];
    }

    /**
     * Jumlah seluruh kolom HR dan employee yang sudah terisi.
     */
    public function getOverallProfileCompletionFilledAttribute(): int
    {
        $fields = array_merge(
            config('employee.employee_required_fields', []),
            config('employee.hr_required_fields', [])
        );

        return $this->calculateCompletion($fields)['filled'];
    }

    /**
     * Total seluruh kolom wajib HR dan employee.
     */
    public function getOverallProfileCompletionTotalAttribute(): int
    {
        $fields = array_merge(
            config('employee.employee_required_fields', []),
            config('employee.hr_required_fields', [])
        );

        return $this->calculateCompletion($fields)['total'];
    }
}
