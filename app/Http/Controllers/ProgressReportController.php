<?php

namespace App\Http\Controllers;

use App\Models\employee_details;
use Illuminate\Support\Facades\DB;

class ProgressReportController extends Controller
{
    public function index()
    {
        /*
         * ============================================================
         * 1. SELURUH FIELD WAJIB PROFILE
         * ============================================================
         *
         * Progress report menghitung:
         *
         * - field Employee
         * - field HR / OD
         *
         * secara gabungan.
         */
        $requiredFields = array_values(
            array_unique(
                array_merge(
                    config(
                        'employee.employee_required_fields',
                        []
                    ),
                    config(
                        'employee.hr_required_fields',
                        []
                    )
                )
            )
        );

        $requiredFieldsPerEmployee =
            count($requiredFields);

        /*
         * Nilai yang dianggap belum terisi.
         */
        $emptyValues = array_map(
            fn($value) =>
            strtoupper(
                trim((string) $value)
            ),
            config(
                'employee.empty_values',
                []
            )
        );

        /*
         * ============================================================
         * 2. BUAT SQL UNTUK MENGHITUNG FIELD TERISI
         * ============================================================
         */
        $fieldExpressions = [];
        $bindings = [];

        foreach ($requiredFields as $field) {
            /*
             * Nama field berasal dari config aplikasi,
             * bukan request user.
             */
            $condition = "
                COALESCE(
                    TRIM(
                        CAST(
                            `employee_details`.`{$field}`
                            AS CHAR
                        )
                    ),
                    ''
                ) != ''
            ";

            /*
             * -, --, N/A dianggap belum terisi.
             */
            if (!empty($emptyValues)) {
                $placeholders = implode(
                    ',',
                    array_fill(
                        0,
                        count($emptyValues),
                        '?'
                    )
                );

                $condition .= "
                    AND UPPER(
                        TRIM(
                            CAST(
                                `employee_details`.`{$field}`
                                AS CHAR
                            )
                        )
                    ) NOT IN ({$placeholders})
                ";

                foreach ($emptyValues as $emptyValue) {
                    $bindings[] = $emptyValue;
                }
            }

            $fieldExpressions[] = "
                SUM(
                    CASE
                        WHEN {$condition}
                        THEN 1
                        ELSE 0
                    END
                )
            ";
        }

        /*
         * Bila config field kosong, hindari SQL kosong.
         */
        $completedFieldsExpression =
            !empty($fieldExpressions)
            ? implode(
                ' + ',
                $fieldExpressions
            )
            : '0';

        /*
         * ============================================================
         * 3. HITUNG PROGRESS PER SOURCE
         * ============================================================
         *
         * employee_details.employee_level_code
         *              ↓
         * sources.employee_level_code
         *              ↓
         * sources.source
         */
        $sourceRows = employee_details::query()
            ->join(
                'sources',
                'employee_details.employee_level_code',
                '=',
                'sources.employee_level_code'
            )
            ->select(
                'sources.source'
            )
            ->selectRaw(
                'COUNT(employee_details.id) AS headcount'
            )
            ->selectRaw(
                "COALESCE(
                    ({$completedFieldsExpression}),
                    0
                ) AS completed_fields",
                $bindings
            )
            ->groupBy(
                'sources.source'
            )
            ->orderByRaw("
                CASE
                    WHEN sources.source = 'HEAD OFFICE' THEN 1
                    WHEN sources.source = 'STORE' THEN 2
                    WHEN sources.source = 'WAREHOUSE' THEN 3
                    ELSE 4
                END
            ")
            ->orderBy(
                'sources.source'
            )
            ->get();

        /*
         * ============================================================
         * 4. HITUNG TOTAL FIELD DAN PERCENTAGE SETIAP SOURCE
         * ============================================================
         */
        $progressReports = $sourceRows
            ->map(
                function ($row) use (
                    $requiredFieldsPerEmployee
                ) {
                    $source = $row->source;

                    $headcount =
                        (int) $row->headcount;

                    /*
             * ========================================================
             * COMPLETED EMPLOYEE
             * ========================================================
             *
             * Employee dianggap Completed hanya jika:
             *
             * - seluruh field wajib HR/OD lengkap
             * - seluruh field wajib Employee lengkap
             */
                    $completedEmployees =
                        employee_details::query()
                        ->whereHas(
                            'sourceData',
                            function ($query) use ($source) {
                                $query->where(
                                    'source',
                                    $source
                                );
                            }
                        )
                        ->hrComplete()
                        ->employeeDataComplete()
                        ->count();

                    /*
             * Employee yang masih memiliki minimal
             * satu required field kosong.
             */
                    $notCompletedEmployees = max(
                        $headcount -
                            $completedEmployees,
                        0
                    );

                    /*
             * ========================================================
             * FIELD-LEVEL PROGRESS
             * ========================================================
             *
             * Ini tetap digunakan untuk Percentage (%).
             */
                    $completedFields =
                        (int) $row->completed_fields;

                    $totalFields =
                        $headcount *
                        $requiredFieldsPerEmployee;

                    $notCompletedFields = max(
                        $totalFields -
                            $completedFields,
                        0
                    );

                    $percentage =
                        $totalFields > 0
                        ? round(
                            (
                                $completedFields /
                                $totalFields
                            ) * 100,
                            2
                        )
                        : 0;

                    return [
                        'source' =>
                        $source,

                        'headcount' =>
                        $headcount,

                        /*
                 * Jumlah EMPLOYEE.
                 */
                        'completed' =>
                        $completedEmployees,

                        'not_completed' =>
                        $notCompletedEmployees,

                        /*
                 * Hanya dipakai untuk menghitung percentage.
                 */
                        'completed_fields' =>
                        $completedFields,

                        'not_completed_fields' =>
                        $notCompletedFields,

                        'total_fields' =>
                        $totalFields,

                        'percentage' =>
                        $percentage,
                    ];
                }
            );

        /*
         * ============================================================
         * 5. TOTAL SELURUH SOURCE
         * ============================================================
         */
        $totalHeadcount =
            $progressReports->sum(
                'headcount'
            );

        $totalCompletedFields =
            $progressReports->sum(
                'completed_fields'
            );

        $totalNotCompletedFields =
            $progressReports->sum(
                'not_completed_fields'
            );

        $totalCompletedEmployees =
            $progressReports->sum(
                'completed'
            );

        $totalNotCompletedEmployees =
            $progressReports->sum(
                'not_completed'
            );

        $totalFields =
            $progressReports->sum(
                'total_fields'
            );

        /*
         * Jangan mengambil rata-rata percentage Source.
         *
         * Hitung kembali:
         *
         * seluruh field terisi
         * --------------------
         * seluruh slot field
         */
        $totalPercentage =
            $totalFields > 0
            ? round(
                (
                    $totalCompletedFields /
                    $totalFields
                ) * 100,
                2
            )
            : 0;

        return view(
            'pages.progress-report',
            [
                'title' =>
                'Progress Report',

                'progressReports' =>
                $progressReports,

                'requiredFieldsPerEmployee' =>
                $requiredFieldsPerEmployee,

                'totalHeadcount' =>
                $totalHeadcount,

                'totalCompletedFields' =>
                $totalCompletedFields,

                'totalNotCompletedFields' =>
                $totalNotCompletedFields,

                'totalFields' =>
                $totalFields,

                'totalPercentage' =>
                $totalPercentage,

                'totalCompletedEmployees' =>
                $totalCompletedEmployees,

                'totalNotCompletedEmployees' =>
                $totalNotCompletedEmployees,
            ]
        );
    }

    public function source(string $source)
    {
        $source = strtoupper(
            trim(
                urldecode($source)
            )
        );

        $allowedSources = [
            'HEAD OFFICE',
            'STORE',
            'WAREHOUSE',
        ];

        abort_unless(
            in_array(
                $source,
                $allowedSources,
                true
            ),
            404
        );

        /*
     * Required fields Employee + HR.
     */
        $requiredFields =
            $this->getRequiredFields();

        $requiredFieldsPerEmployee =
            count($requiredFields);

        [
            $completedFieldExpression,
            $fieldBindings,
        ] = $this->buildFilledFieldsExpression(
            $requiredFields
        );

        /*
     * ============================================================
     * HEAD OFFICE
     * ============================================================
     *
     * Group berdasarkan Business Unit.
     */
        if ($source === 'HEAD OFFICE') {
            $dimensionLabel =
                'Business Unit';

            $groupColumn =
                'business_unit_org_element_1';

            $rows = employee_details::query()
                ->join(
                    'sources',
                    'employee_details.employee_level_code',
                    '=',
                    'sources.employee_level_code'
                )
                ->leftJoin(
                    'business_units',
                    'employee_details.business_unit_org_element_1',
                    '=',
                    'business_units.business_unit_code'
                )
                ->where(
                    'sources.source',
                    'HEAD OFFICE'
                )
                ->selectRaw(
                    '
                employee_details.business_unit_org_element_1
                    AS group_code
                '
                )
                ->selectRaw(
                    "
                COALESCE(
                    business_units.business_unit_name,
                    'BELUM TERDAFTAR'
                ) AS group_name
                "
                )
                ->selectRaw(
                    'COUNT(employee_details.id) AS headcount'
                )
                ->selectRaw(
                    "
                COALESCE(
                    ({$completedFieldExpression}),
                    0
                ) AS completed_fields
                ",
                    $fieldBindings
                )
                ->groupBy(
                    'employee_details.business_unit_org_element_1',
                    'business_units.business_unit_name'
                )
                ->orderBy(
                    'business_units.business_unit_name'
                )
                ->get();
        }

        /*
     * ============================================================
     * STORE
     * ============================================================
     *
     * Group berdasarkan Department.
     */ elseif ($source === 'STORE') {
            $dimensionLabel =
                'Department';

            $groupColumn =
                'department_org_element_2';

            $rows = employee_details::query()
                ->join(
                    'sources',
                    'employee_details.employee_level_code',
                    '=',
                    'sources.employee_level_code'
                )
                ->leftJoin(
                    'departments',
                    'employee_details.department_org_element_2',
                    '=',
                    'departments.department_code'
                )
                ->where(
                    'sources.source',
                    'STORE'
                )
                ->selectRaw(
                    '
                employee_details.department_org_element_2
                    AS group_code
                '
                )
                ->selectRaw(
                    "
                COALESCE(
                    departments.department_name,
                    'BELUM TERDAFTAR'
                ) AS group_name
                "
                )
                ->selectRaw(
                    'COUNT(employee_details.id) AS headcount'
                )
                ->selectRaw(
                    "
                COALESCE(
                    ({$completedFieldExpression}),
                    0
                ) AS completed_fields
                ",
                    $fieldBindings
                )
                ->groupBy(
                    'employee_details.department_org_element_2',
                    'departments.department_name'
                )
                ->orderBy(
                    'departments.department_name'
                )
                ->get();
        }

        /*
     * ============================================================
     * WAREHOUSE
     * ============================================================
     *
     * Tidak perlu breakdown lagi.
     */ else {
            $dimensionLabel =
                'Warehouse';

            $groupColumn = null;

            $rows = employee_details::query()
                ->join(
                    'sources',
                    'employee_details.employee_level_code',
                    '=',
                    'sources.employee_level_code'
                )
                ->where(
                    'sources.source',
                    'WAREHOUSE'
                )
                ->selectRaw(
                    "'WAREHOUSE' AS group_name"
                )
                ->selectRaw(
                    'NULL AS group_code'
                )
                ->selectRaw(
                    'COUNT(employee_details.id) AS headcount'
                )
                ->selectRaw(
                    "
                COALESCE(
                    ({$completedFieldExpression}),
                    0
                ) AS completed_fields
                ",
                    $fieldBindings
                )
                ->get();
        }

        /*
     * ============================================================
     * HITUNG COMPLETED EMPLOYEE + PERCENTAGE
     * ============================================================
     */
        $reports = $rows->map(
            function ($row) use (
                $source,
                $groupColumn,
                $requiredFieldsPerEmployee
            ) {
                $headcount =
                    (int) $row->headcount;

                /*
             * Base query untuk status employee Completed.
             */
                $completedQuery =
                    employee_details::query()
                    ->whereHas(
                        'sourceData',
                        function ($query) use ($source) {
                            $query->where(
                                'source',
                                $source
                            );
                        }
                    );

                /*
             * HEAD OFFICE / STORE membutuhkan
             * filter group masing-masing.
             */
                if ($groupColumn !== null) {
                    if ($row->group_code === null) {
                        $completedQuery->whereNull(
                            $groupColumn
                        );
                    } else {
                        $completedQuery->where(
                            $groupColumn,
                            $row->group_code
                        );
                    }
                }

                /*
             * Employee dianggap complete jika
             * HR + employee field semuanya lengkap.
             */
                $completedEmployees =
                    $completedQuery
                    ->hrComplete()
                    ->employeeDataComplete()
                    ->count();

                $notCompletedEmployees = max(
                    $headcount -
                        $completedEmployees,
                    0
                );

                /*
             * Percentage berbasis FIELD.
             */
                $completedFields =
                    (int) $row->completed_fields;

                $totalFields =
                    $headcount *
                    $requiredFieldsPerEmployee;

                $percentage =
                    $totalFields > 0
                    ? round(
                        (
                            $completedFields /
                            $totalFields
                        ) * 100,
                        2
                    )
                    : 0;

                return [
                    'code' =>
                    $row->group_code,

                    'name' =>
                    $row->group_name,

                    'headcount' =>
                    $headcount,

                    'completed' =>
                    $completedEmployees,

                    'not_completed' =>
                    $notCompletedEmployees,

                    'completed_fields' =>
                    $completedFields,

                    'total_fields' =>
                    $totalFields,

                    'percentage' =>
                    $percentage,
                ];
            }
        );

        /*
     * ============================================================
     * TOTAL
     * ============================================================
     */
        $totalHeadcount =
            $reports->sum(
                'headcount'
            );

        $totalCompletedEmployees =
            $reports->sum(
                'completed'
            );

        $totalNotCompletedEmployees =
            $reports->sum(
                'not_completed'
            );

        $totalCompletedFields =
            $reports->sum(
                'completed_fields'
            );

        $totalFields =
            $reports->sum(
                'total_fields'
            );

        $totalPercentage =
            $totalFields > 0
            ? round(
                (
                    $totalCompletedFields /
                    $totalFields
                ) * 100,
                2
            )
            : 0;

        return view(
            'pages.progress-report-detail',
            [
                'title' =>
                "Progress Report - {$source}",

                'source' =>
                $source,

                'dimensionLabel' =>
                $dimensionLabel,

                'reports' =>
                $reports,

                'totalHeadcount' =>
                $totalHeadcount,

                'totalCompletedEmployees' =>
                $totalCompletedEmployees,

                'totalNotCompletedEmployees' =>
                $totalNotCompletedEmployees,

                'totalPercentage' =>
                $totalPercentage,
            ]
        );
    }

    private function getRequiredFields(): array
    {
        return array_values(
            array_unique(
                array_merge(
                    config(
                        'employee.employee_required_fields',
                        []
                    ),
                    config(
                        'employee.hr_required_fields',
                        []
                    )
                )
            )
        );
    }

    private function buildFilledFieldsExpression(
        array $requiredFields
    ): array {
        $emptyValues = array_map(
            fn($value) =>
            strtoupper(
                trim((string) $value)
            ),
            config(
                'employee.empty_values',
                []
            )
        );

        $fieldExpressions = [];
        $bindings = [];

        foreach ($requiredFields as $field) {
            $condition = "
            COALESCE(
                TRIM(
                    CAST(
                        `employee_details`.`{$field}`
                        AS CHAR
                    )
                ),
                ''
            ) != ''
        ";

            if (!empty($emptyValues)) {
                $placeholders = implode(
                    ',',
                    array_fill(
                        0,
                        count($emptyValues),
                        '?'
                    )
                );

                $condition .= "
                AND UPPER(
                    TRIM(
                        CAST(
                            `employee_details`.`{$field}`
                            AS CHAR
                        )
                    )
                )
                NOT IN ({$placeholders})
            ";

                foreach ($emptyValues as $emptyValue) {
                    $bindings[] = $emptyValue;
                }
            }

            $fieldExpressions[] = "
            SUM(
                CASE
                    WHEN {$condition}
                    THEN 1
                    ELSE 0
                END
            )
        ";
        }

        return [
            empty($fieldExpressions)
                ? '0'
                : implode(
                    ' + ',
                    $fieldExpressions
                ),

            $bindings,
        ];
    }
}
