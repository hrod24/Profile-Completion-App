<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\BusinessUnit;
use App\Models\Department;
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
        * ============================================================
        * 6. PROGRESS REPORT BY PIC
        * ============================================================
        *
        * Headcount:
        * jumlah employee yang menjadi tanggung jawab PIC.
        *
        * Completed:
        * employee yang seluruh field HR + employee sudah lengkap.
        *
        * Percentage:
        * berdasarkan seluruh field yang sudah terisi.
        */

        /*
        * Ambil headcount + completed fields per PIC.
        *
        * LEFT JOIN digunakan agar employee yang belum memiliki PIC
        * juga dapat terlihat sebagai "BELUM ADA PIC".
        */
        $picRows = employee_details::query()
            ->leftJoin(
                'pics',
                'employee_details.pic_nip',
                '=',
                'pics.nip'
            )
            ->selectRaw(
                'employee_details.pic_nip AS pic_nip'
            )
            ->selectRaw(
                "
        COALESCE(
            pics.name,
            'BELUM ADA PIC'
        ) AS pic_name
        "
            )
            ->selectRaw(
                'COUNT(employee_details.id) AS headcount'
            )
            ->selectRaw(
                "
        COALESCE(
            ({$completedFieldsExpression}),
            0
        ) AS completed_fields
        ",
                $bindings
            )
            ->groupBy(
                'employee_details.pic_nip',
                'pics.name'
            )
            ->orderByRaw(
                'employee_details.pic_nip IS NULL DESC'
            )
            ->orderBy(
                'pics.name'
            )
            ->get();

        /*
        * Jumlah employee complete per PIC.
        *
        * Menggunakan scope yang sama dengan dashboard:
        * - hrComplete()
        * - employeeDataComplete()
        */
        $completedEmployeesByPic = employee_details::query()
            ->hrComplete()
            ->employeeDataComplete()
            ->selectRaw(
                'pic_nip, COUNT(*) AS completed_count'
            )
            ->groupBy('pic_nip')
            ->get()
            ->mapWithKeys(
                fn($row) => [
                    $row->pic_nip ?? '__NULL__'
                    => (int) $row->completed_count,
                ]
            );

        $picProgressReports = $picRows
            ->map(
                function ($row) use (
                    $requiredFieldsPerEmployee,
                    $completedEmployeesByPic
                ) {
                    $picKey =
                        $row->pic_nip ?? '__NULL__';

                    $headcount =
                        (int) $row->headcount;

                    /*
             * Jumlah employee yang benar-benar
             * sudah lengkap seluruh profil.
             */
                    $completedEmployees =
                        (int) (
                            $completedEmployeesByPic[$picKey] ?? 0
                        );

                    $notCompletedEmployees = max(
                        $headcount -
                            $completedEmployees,
                        0
                    );

                    /*
             * Field-level percentage.
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
                        'nip' =>
                        $row->pic_nip,

                        'name' =>
                        $row->pic_name,

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

        /*
        * ============================================================
        * TOTAL PROGRESS BY PIC
        * ============================================================
        */

        $totalPicHeadcount =
            $picProgressReports->sum(
                'headcount'
            );

        $totalPicCompletedEmployees =
            $picProgressReports->sum(
                'completed'
            );

        $totalPicNotCompletedEmployees =
            $picProgressReports->sum(
                'not_completed'
            );

        $totalPicCompletedFields =
            $picProgressReports->sum(
                'completed_fields'
            );

        $totalPicFields =
            $picProgressReports->sum(
                'total_fields'
            );

        $totalPicPercentage =
            $totalPicFields > 0
            ? round(
                (
                    $totalPicCompletedFields /
                    $totalPicFields
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

                'picProgressReports' =>
                $picProgressReports,

                'totalPicHeadcount' =>
                $totalPicHeadcount,

                'totalPicCompletedEmployees' =>
                $totalPicCompletedEmployees,

                'totalPicNotCompletedEmployees' =>
                $totalPicNotCompletedEmployees,

                'totalPicPercentage' =>
                $totalPicPercentage,
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

    public function groupEmployees(
        Request $request,
        string $source,
        string $groupCode
    ) {
        $source = strtoupper(
            trim(
                urldecode($source)
            )
        );

        $status = strtolower(
            trim(
                (string) $request->query(
                    'status',
                    'all'
                )
            )
        );

        $allowedStatuses = [
            'all',
            'completed',
            'not_completed',
        ];

        if (!in_array($status, $allowedStatuses, true)) {
            $status = 'all';
        }

        /*
     * Hanya HEAD OFFICE dan STORE yang memiliki
     * drill-down group → employee.
     */
        abort_unless(
            in_array(
                $source,
                [
                    'HEAD OFFICE',
                    'STORE',
                    'WAREHOUSE',
                ],
                true
            ),
            404
        );

        $isUnregistered =
            $groupCode === '__NULL__';

        /*
        * ============================================================
        * HEAD OFFICE
        * ============================================================
        */
        if ($source === 'HEAD OFFICE') {
            $dimensionLabel =
                'Business Unit';

            $groupColumn =
                'business_unit_org_element_1';

            if ($groupCode === '__NULL__') {
                $groupName =
                    'BELUM TERDAFTAR';

                $resolvedGroupCode = null;
            } else {
                $businessUnit =
                    BusinessUnit::query()
                    ->where(
                        'business_unit_code',
                        $groupCode
                    )
                    ->firstOrFail();

                $groupName =
                    $businessUnit
                    ->business_unit_name;

                $resolvedGroupCode =
                    $businessUnit
                    ->business_unit_code;
            }
        }

        /*
        * ============================================================
        * STORE
        * ============================================================
        */ elseif ($source === 'STORE') {
            $dimensionLabel =
                'Department';

            $groupColumn =
                'department_org_element_2';

            if ($groupCode === '__NULL__') {
                $groupName =
                    'BELUM TERDAFTAR';

                $resolvedGroupCode = null;
            } else {
                $department =
                    Department::query()
                    ->where(
                        'department_code',
                        $groupCode
                    )
                    ->firstOrFail();

                $groupName =
                    $department
                    ->department_name;

                $resolvedGroupCode =
                    $department
                    ->department_code;
            }
        }

        /*
        * ============================================================
        * WAREHOUSE
        * ============================================================
        *
        * Warehouse tidak membutuhkan Business Unit
        * maupun Department.
        */ else {
            abort_unless(
                $groupCode === '__ALL__',
                404
            );

            $dimensionLabel =
                'Warehouse';

            $groupColumn = null;

            $groupName =
                'WAREHOUSE';

            $resolvedGroupCode = null;
        }

        /*
     * ============================================================
     * BASE QUERY
     * ============================================================
     *
     * Filter:
     *
     * Source + Business Unit/Department.
     */
        $groupQuery =
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
        * HEAD OFFICE dan STORE memiliki group.
        * WAREHOUSE cukup berdasarkan source.
        */
        if ($groupColumn !== null) {
            if ($resolvedGroupCode === null) {
                $groupQuery->where(
                    function ($query) use ($groupColumn) {
                        $query
                            ->whereNull($groupColumn)
                            ->orWhere(
                                $groupColumn,
                                ''
                            );
                    }
                );
            } else {
                $groupQuery->where(
                    $groupColumn,
                    $resolvedGroupCode
                );
            }
        }

        /*
     * ============================================================
     * SUMMARY
     * ============================================================
     */
        $headcount =
            (clone $groupQuery)
            ->count();

        $completedEmployees =
            (clone $groupQuery)
            ->hrComplete()
            ->employeeDataComplete()
            ->count();

        $notCompletedEmployees = max(
            $headcount -
                $completedEmployees,
            0
        );

        /*
     * ============================================================
     * SEARCH
     * ============================================================
     *
     * Search hanya memengaruhi tabel employee.
     * Summary tetap menunjukkan seluruh group.
     */
        $search = trim(
            (string) $request->query(
                'search',
                ''
            )
        );

        $employeeQuery =
            clone $groupQuery;

        /*
        * ============================================================
        * FILTER STATUS COMPLETION
        * ============================================================
        */
        if ($status === 'completed') {
            /*
            * Hanya employee yang seluruh:
            *
            * - required field HR
            * - required field Employee
            *
            * sudah lengkap.
            */
            $employeeQuery
                ->hrComplete()
                ->employeeDataComplete();
        }

        if ($status === 'not_completed') {
            /*
            * Cari ID employee yang benar-benar sudah complete.
            */
            $completedEmployeeIds =
                employee_details::query()
                ->select('id')
                ->hrComplete()
                ->employeeDataComplete();

            /*
            * Employee selain ID di atas berarti belum complete.
            *
            * Filter Source dan group tetap berasal dari
            * $groupQuery pada outer query.
            */
            $employeeQuery->whereNotIn(
                'id',
                $completedEmployeeIds
            );
        }

        if ($search !== '') {
            $employeeQuery->where(
                function ($query) use ($search) {
                    $query
                        ->where(
                            'employee_id',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'display_name',
                            'like',
                            "%{$search}%"
                        );
                }
            );
        }

        /*
     * ============================================================
     * EMPLOYEE LIST
     * ============================================================
     */
        $employees =
            $employeeQuery
            ->with([
                'pic',
                'sourceData',
                'businessUnit',
                'department',
            ])
            ->orderBy('display_name')
            ->orderBy('employee_id')
            ->paginate(20)
            ->withQueryString();

        return view(
            'pages.progress-report-employees',
            [
                'title' =>
                "Progress Report - {$groupName}",

                'source' =>
                $source,

                'dimensionLabel' =>
                $dimensionLabel,

                'groupName' =>
                $groupName,

                'groupCode' =>
                $resolvedGroupCode,

                'headcount' =>
                $headcount,

                'completedEmployees' =>
                $completedEmployees,

                'notCompletedEmployees' =>
                $notCompletedEmployees,

                'employees' =>
                $employees,

                'search' =>
                $search,

                'status' => $status,
            ]
        );
    }

    public function sourceNotCompletedEmployees(
        Request $request,
        string $source
    ) {
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
     * ============================================================
     * EMPLOYEE REQUIRED FIELDS ONLY
     * ============================================================
     *
     * Field ini dipakai untuk kolom "Data".
     *
     * HR required fields TIDAK ditampilkan.
     */
        $employeeRequiredFields =
            array_values(
                array_unique(
                    config(
                        'employee.employee_required_fields',
                        []
                    )
                )
            );

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
     * BASE SOURCE QUERY
     * ============================================================
     */
        $sourceQuery =
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
        * ============================================================
        * EMPLOYEE PROFILE INCOMPLETE ONLY
        * ============================================================
        *
        * Halaman Reminder hanya menampilkan employee
        * yang masih memiliki field Employee yang belum lengkap.
        *
        * Kelengkapan HR / OD tidak diperhitungkan di halaman ini.
        */
        $employeeCompletedIds =
            employee_details::query()
            ->select('employee_details.id')
            ->whereHas(
                'sourceData',
                function ($query) use ($source) {
                    $query->where(
                        'source',
                        $source
                    );
                }
            )
            ->employeeDataComplete();

        /*
        * Ambil hanya employee yang Employee Profile-nya
        * belum complete.
        */
        $employeeQuery =
            (clone $sourceQuery)
            ->whereNotIn(
                'employee_details.id',
                $employeeCompletedIds
            );

        /*
     * Total Not Completed.
     */
        $totalNotCompletedEmployees =
            (clone $employeeQuery)
            ->count();

        /*
     * ============================================================
     * SEARCH
     * ============================================================
     */
        $search = trim(
            (string) $request->query(
                'search',
                ''
            )
        );

        if ($search !== '') {
            $employeeQuery->where(
                function ($query) use ($search) {
                    $query
                        ->where(
                            'employee_id',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'display_name',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'primary_email',
                            'like',
                            "%{$search}%"
                        );
                }
            );
        }

        /*
     * ============================================================
     * EMPLOYEE LIST
     * ============================================================
     */
        $employees =
            $employeeQuery
            ->with([
                'businessUnit',
                'department',
                'pic',
                'sourceData',
            ])
            ->orderBy('display_name')
            ->orderBy('employee_id')
            ->get();

        /*
     * Tambahkan daftar field Employee yang masih kosong
     * ke setiap employee.
     */
        $employees
            ->transform(
                function ($employee) use (
                    $employeeRequiredFields,
                    $emptyValues
                ) {
                    $missingFields = [];

                    foreach (
                        $employeeRequiredFields
                        as $field
                    ) {
                        $value =
                            $employee
                            ->getAttribute(
                                $field
                            );

                        if (
                            $this->isMissingRequiredValue(
                                $value,
                                $emptyValues
                            )
                        ) {
                            $missingFields[] = [
                                'field' => $field,

                                'label' =>
                                $this
                                    ->employeeFieldLabel(
                                        $field
                                    ),
                            ];
                        }
                    }

                    $employee->setAttribute(
                        'missing_employee_fields',
                        $missingFields
                    );

                    return $employee;
                }
            );

        return view(
            'pages.progress-report-source-not-completed',
            [
                'title' =>
                "Not Completed Employees - {$source}",

                'source' =>
                $source,

                'employees' =>
                $employees,

                'search' =>
                $search,

                'totalNotCompletedEmployees' =>
                $totalNotCompletedEmployees,
            ]
        );
    }

    private function isMissingRequiredValue(
        mixed $value,
        array $emptyValues
    ): bool {
        if (is_null($value)) {
            return true;
        }

        if ($value instanceof \DateTimeInterface) {
            $normalized =
                $value->format(
                    'Y-m-d'
                );
        } else {
            $normalized =
                trim(
                    (string) $value
                );
        }

        if ($normalized === '') {
            return true;
        }

        return in_array(
            strtoupper($normalized),
            $emptyValues,
            true
        );
    }

    private function employeeFieldLabel(
        string $field
    ): string {
        $labels = [
            'emergency_full_name' =>
            'Emergency Contact Name',

            'current_address' =>
            'Current Address',

            'mother_full_name' =>
            'Mother Full Name',

            'education_level' =>
            'Education Level',

            'primary_contact_number' =>
            'Primary Contact Number',

            'tax_number' =>
            'Tax Number',

            'emergency_contact_no' =>
            'Emergency Contact Number',

            'current_provinsi' =>
            'Current Province',

            'primary_email' =>
            'Primary Email',

            'display_name' =>
            'Display Name',

            'current_kotamadya_kabupaten' =>
            'Current City / Regency',

            'major' =>
            'Major',

            'institution_name' =>
            'Institution Name',

            'religion' =>
            'Religion',

            'birth_place' =>
            'Birth Place',

            'date_of_birth' =>
            'Date of Birth',

            'marital_status' =>
            'Marital Status',

            'gender' =>
            'Gender',

            'ktp_address' =>
            'KTP Address',

            'blood_group' =>
            'Blood Group',

            'ktp_number' =>
            'KTP Number',

            'nationality' =>
            'Nationality',

            'ijazah_filename' =>
            'Ijazah Attachment',

            'ktp_filename' =>
            'KTP Attachment',

            'npwp_filename' =>
            'NPWP Attachment',

            'kk_filename' =>
            'KK Attachment',
        ];

        return $labels[$field]
            ?? Str::headline($field);
    }
}
