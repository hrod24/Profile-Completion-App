<?php

namespace App\Http\Controllers;

use App\Models\BusinessUnit;
use App\Models\Pic;
use App\Models\Department;
use App\Models\employee_details;
use Illuminate\Http\Request;
use App\Models\Source;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        /*
         * ============================================================
         * 1. HELPER UNTUK MENORMALISASI PARAMETER ARRAY
         * ============================================================
         *
         * Checkbox dapat menghasilkan:
         *
         * company[]=A&company[]=B
         *
         * Tetapi parameter juga bisa saja dikirim sebagai satu string.
         * Helper ini memastikan hasil akhirnya selalu Collection.
         */
        $normalizeArrayParameter = static function (
            mixed $parameter
        ) {
            if (!is_array($parameter)) {
                $parameter = [$parameter];
            }

            return collect($parameter)
                ->filter(
                    fn($value) =>
                    is_string($value) &&
                        trim($value) !== ''
                )
                ->map(
                    fn($value) =>
                    trim($value)
                )
                ->unique()
                ->values();
        };

        /*
         * ============================================================
         * 2. AMBIL PARAMETER SEARCH DAN FILTER
         * ============================================================
         */

        $search = trim(
            (string) $request->query(
                'search',
                ''
            )
        );

        $selectedCompanies =
            $normalizeArrayParameter(
                $request->query(
                    'company',
                    []
                )
            );

        $selectedBusinessUnits =
            $normalizeArrayParameter(
                $request->query(
                    'business_unit',
                    []
                )
            );

        $selectedPics = $normalizeArrayParameter($request->query('pic', []));

        $selectedSources = $normalizeArrayParameter(
            $request->query('sources', [])
        );

        /*
         * Department dari request belum tentu masih valid.
         *
         * Contohnya:
         * - Sebelumnya memilih DIV00001 + DEP00001.
         * - Kemudian division diganti menjadi DIV00002.
         *
         * DEP00001 perlu dibuang jika tidak berhubungan
         * dengan DIV00002.
         */
        $requestedDepartments =
            $normalizeArrayParameter(
                $request->query(
                    'department',
                    []
                )
            );

        /*
         * ============================================================
         * 3. AMBIL DEPARTMENT BERDASARKAN BUSINESS UNIT
         * ============================================================
         *
         * Jika tidak ada business unit yang dipilih:
         *     → tampilkan seluruh department.
         *
         * Jika ada business unit yang dipilih:
         *     → tampilkan gabungan department dari seluruh
         *       business unit yang dipilih.
         */
        $availableDepartmentQuery =
            Department::query();

        if ($selectedBusinessUnits->isNotEmpty()) {
            $availableDepartmentQuery->whereHas(
                'businessUnits',
                function ($query) use (
                    $selectedBusinessUnits
                ) {
                    $query->whereIn(
                        'business_units.business_unit_code',
                        $selectedBusinessUnits->all()
                    );
                }
            );
        }

        /*
         * Query utama berasal dari tabel departments.
         *
         * Karena itu department yang sama tidak muncul dua kali,
         * walaupun terhubung dengan lebih dari satu business unit.
         */
        $departments =
            $availableDepartmentQuery
            ->orderBy('department_name')
            ->orderBy('department_code')
            ->get([
                'department_code',
                'department_name',
            ])
            ->map(
                fn(Department $department) => [
                    'value' =>
                    $department->department_code,

                    'label' =>
                    $department->department_name,

                    'code' =>
                    $department->department_code,
                ]
            )
            ->values();

        /*
         * Ambil seluruh kode department yang tersedia
         * untuk business unit terpilih.
         */
        $availableDepartmentCodes =
            $departments->pluck('value');

        /*
         * Pertahankan hanya department pilihan yang masih valid.
         *
         * Jika tidak ada business unit yang dipilih,
         * availableDepartmentCodes berisi seluruh department.
         * Dengan demikian filter hanya berdasarkan department
         * tetap dapat digunakan.
         */
        $selectedDepartments =
            $requestedDepartments
            ->intersect(
                $availableDepartmentCodes
            )
            ->values();

        /*
         * ============================================================
         * 4. BUAT BASE QUERY UNTUK SELURUH FILTER
         * ============================================================
         *
         * Query ini digunakan oleh:
         *
         * - Card dashboard.
         * - Overall progress.
         * - Tabel employee.
         */
        $filterQuery =
            employee_details::query();

        /*
         * ============================================================
         * 5. FILTER COMPANY
         * ============================================================
         */
        if ($selectedCompanies->isNotEmpty()) {
            /*
             * __NULL__ merupakan value internal untuk label:
             * BELUM TERDAFTAR.
             */
            $includeUnregisteredCompany =
                $selectedCompanies->contains(
                    '__NULL__'
                );

            /*
             * Pisahkan company normal dari __NULL__.
             */
            $registeredCompanies =
                $selectedCompanies
                ->reject(
                    fn($company) =>
                    $company === '__NULL__'
                )
                ->values();

            $filterQuery->where(
                function ($query) use (
                    $registeredCompanies,
                    $includeUnregisteredCompany
                ) {
                    /*
                     * Company yang memiliki nama.
                     */
                    if (
                        $registeredCompanies
                        ->isNotEmpty()
                    ) {
                        $query->whereIn(
                            'company',
                            $registeredCompanies->all()
                        );
                    }

                    /*
                     * Company NULL atau string kosong.
                     */
                    if ($includeUnregisteredCompany) {
                        $unregisteredCondition =
                            function ($subQuery) {
                                $subQuery
                                    ->whereNull('company')
                                    ->orWhere(
                                        'company',
                                        ''
                                    );
                            };

                        /*
                         * Jika company biasa dan belum terdaftar
                         * dipilih bersamaan, gunakan OR.
                         */
                        if (
                            $registeredCompanies
                            ->isNotEmpty()
                        ) {
                            $query->orWhere(
                                $unregisteredCondition
                            );
                        } else {
                            $query->where(
                                $unregisteredCondition
                            );
                        }
                    }
                }
            );
        }

        /*
         * ============================================================
         * 6. FILTER BUSINESS UNIT / DIVISION
         * ============================================================
         */
        if ($selectedBusinessUnits->isNotEmpty()) {
            $filterQuery->whereIn(
                'business_unit_org_element_1',
                $selectedBusinessUnits->all()
            );
        }

        /*
         * ============================================================
         * 7. FILTER DEPARTMENT
         * ============================================================
         */
        if ($selectedDepartments->isNotEmpty()) {
            $filterQuery->whereIn(
                'department_org_element_2',
                $selectedDepartments->all()
            );
        }

        /*
        * ============================================================
        * FILTER PIC
        * ============================================================
        *
        * Checkbox PIC mengirim NIP PIC.
        *
        * Contoh URL:
        * ?pic[]=10001&pic[]=10002
        */
        if ($selectedPics->isNotEmpty()) {
            $filterQuery->whereIn(
                'pic_nip',
                $selectedPics->all()
            );
        }

        if ($selectedSources->isNotEmpty()) {
            $filterQuery->whereHas(
                'sourceData',
                function ($query) use ($selectedSources) {
                    $query->whereIn(
                        'source',
                        $selectedSources->all()
                    );
                }
            );
        }

        $sources = Source::query()
            ->whereNotNull('source')
            ->where('source', '!=', '')
            ->select('source')
            ->distinct()
            ->orderBy('source')
            ->pluck('source');

        /*
         * ============================================================
         * 8. HITUNG STATISTIK DASHBOARD
         * ============================================================
         *
         * Query harus di-clone agar setiap scope tidak mengubah
         * query yang akan digunakan oleh perhitungan berikutnya.
         */

        $totalEmployees =
            (clone $filterQuery)->count();

        $completedEmployees =   
            (clone $filterQuery)
            ->employeeDataComplete()
            ->count();

        $pendingEmployees = max(
            $totalEmployees -
                $completedEmployees,
            0
        );

        $completionPercentage =
            $totalEmployees > 0
            ? round(
                (
                    $completedEmployees /
                    $totalEmployees
                ) * 100,
                2
            )
            : 0;

        $hrIncompleteEmployees =
            (clone $filterQuery)
            ->hrIncomplete()
            ->count();

        $fullyCompleteEmployees =
            (clone $filterQuery)
            ->hrComplete()
            ->employeeDataComplete()
            ->count();

        $fullyIncompleteEmployees = max(
            $totalEmployees -
                $fullyCompleteEmployees,
            0
        );

        $fullCompletionPercentage =
            $totalEmployees > 0
            ? round(
                (
                    $fullyCompleteEmployees /
                    $totalEmployees
                ) * 100,
                2
            )
            : 0;


        /*
         * ============================================================
         * 9. QUERY KHUSUS TABEL EMPLOYEE
         * ============================================================
         *
         * Company, business unit, dan department memengaruhi:
         * - Statistik.
         * - Tabel.
         *
         * Search nama/NIP hanya memengaruhi tabel.
         */
        $employeeQuery =
            clone $filterQuery;

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
         * Tampilkan 15 employee per halaman.
         *
         * withQueryString() mempertahankan seluruh filter
         * pada link pagination.
         */
        $allEmployees = $employeeQuery
            ->with([
                'pic',
                'sourceData',
            ])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        /*
         * Data yang digunakan oleh komponen card dan progress.
         */
        $statisticsData = [
            'totalEmployees' =>
            $totalEmployees,

            'completedEmployees' =>
            $completedEmployees,

            'pendingEmployees' =>
            $pendingEmployees,

            'completionPercentage' =>
            $completionPercentage,

            'hrIncompleteEmployees' =>
            $hrIncompleteEmployees,

            'fullyCompleteEmployees' =>
            $fullyCompleteEmployees,

            'fullyIncompleteEmployees' =>
            $fullyIncompleteEmployees,

            'fullCompletionPercentage' =>
            $fullCompletionPercentage,
        ];

        /*
         * ============================================================
         * 10. RESPONSE AJAX
         * ============================================================
         *
         * Digunakan oleh:
         * - Live search.
         * - Filter company.
         * - Filter business unit.
         * - Filter department.
         * - Pagination.
         */
        if ($request->ajax()) {
            return response()->json([
                /*
                 * HTML tabel dan pagination terbaru.
                 */
                'html' => view(
                    'components.dashboard.table-results',
                    [
                        'employees' =>
                        $allEmployees,
                    ]
                )->render(),

                /*
                 * HTML card dan progress terbaru.
                 */
                'statisticsHtml' => view(
                    'components.dashboard.statistics',
                    $statisticsData
                )->render(),

                /*
                 * Isi dropdown department terbaru.
                 *
                 * Isi ini berubah berdasarkan business unit
                 * yang sedang dipilih.
                 */
                'departmentOptionsHtml' => view(
                    'components.dashboard.department-filter-options',
                    [
                        'departments' =>
                        $departments,

                        'selectedDepartments' =>
                        $selectedDepartments->all(),
                    ]
                )->render(),

                /*
                 * Department yang masih valid.
                 *
                 * JavaScript menggunakan data ini untuk membersihkan
                 * parameter department lama dari URL.
                 */
                'selectedDepartments' =>
                $selectedDepartments->all(),

                /*
                 * Total employee pada tabel setelah search
                 * dan seluruh filter diterapkan.
                 */
                'total' =>
                $allEmployees->total(),

                'search' =>
                $search,
            ]);
        }

        /*
         * ============================================================
         * 11. AMBIL DAFTAR COMPANY
         * ============================================================
         */
        $companies =
            employee_details::query()
            ->whereNotNull('company')
            ->where(
                'company',
                '<>',
                ''
            )
            ->select('company')
            ->distinct()
            ->orderBy('company')
            ->pluck('company')
            ->map(
                fn($company) => [
                    'value' =>
                    $company,

                    'label' =>
                    $company,
                ]
            )
            ->values();

        

        /*
         * Periksa apakah ada employee tanpa company.
         */
        $hasUnregisteredCompany =
            employee_details::query()
            ->where(
                function ($query) {
                    $query
                        ->whereNull('company')
                        ->orWhere(
                            'company',
                            ''
                        );
                }
            )
            ->exists();

        /*
         * Tambahkan pilihan BELUM TERDAFTAR
         * jika memang ada datanya.
         */
        if ($hasUnregisteredCompany) {
            $companies->prepend([
                'value' =>
                '__NULL__',

                'label' =>
                'BELUM TERDAFTAR',
            ]);
        }

        /*
         * ============================================================
         * 12. AMBIL DAFTAR BUSINESS UNIT
         * ============================================================
         */
        $businessUnits =
            BusinessUnit::query()
            ->orderBy(
                'business_unit_name'
            )
            ->orderBy(
                'business_unit_code'
            )
            ->get([
                'business_unit_code',
                'business_unit_name',
            ])
            ->map(
                fn(BusinessUnit $businessUnit) => [
                    'value' =>
                    $businessUnit
                        ->business_unit_code,

                    'label' =>
                    $businessUnit
                        ->business_unit_name,

                    'code' =>
                    $businessUnit
                        ->business_unit_code,
                ]
            )
            ->values();

        /*
         * ============================================================
         * 13. AMBIL DAFTAR PIC
         * ============================================================
         */

        $allPic = Pic::orderBy('name')->get(['nip', 'name']);

        /*
         * ============================================================
         * 13. TAMPILKAN HALAMAN DASHBOARD
         * ============================================================
         */
        return view('pages.dashboard', [
            'title' =>
            'Employee Dashboard',

            'companies' =>
            $companies,

            'businessUnits' =>
            $businessUnits,

            /*
             * Nilai department sudah menyesuaikan
             * business unit terpilih.
             */
            'departments' =>
            $departments,

            'pics' => $allPic,

            'selectedCompanies' =>
            $selectedCompanies->all(),

            'selectedBusinessUnits' =>
            $selectedBusinessUnits->all(),

            'selectedDepartments' =>
            $selectedDepartments->all(),
            
            'selectedPics' =>
            $selectedPics->all(),

            'employees' =>
            $allEmployees,

            'search' =>
            $search,

            'sources' => $sources,

            'selectedSources' =>
            $selectedSources->all(),

            /*
             * Memasukkan seluruh statistik ke view.
             */
            ...$statisticsData,
        ]);
    }

    public function employeeDetails(string $employeeId): JsonResponse
    {
        $employee = employee_details::query()
            ->with([
                'pic',
                'sourceData',
            ])
            ->where('employee_id', $employeeId)
            ->firstOrFail();

        /*
     * Ambil seluruh kolom asli pada tabel employee_details.
     */
        $tableColumns = Schema::getColumnListing(
            $employee->getTable()
        );

        /*
     * Kolom teknis tidak perlu ditampilkan kepada admin.
     * Hapus dari daftar ini jika Anda tetap ingin menampilkannya.
     */
        $hiddenColumns = [
            'id',
            'created_at',
            'updated_at',
            'deleted_at',
        ];

        $employeeConfiguredFields = array_values(
            array_unique(
                config('employee.employee_required_fields', [])
            )
        );

        $hrConfiguredFields = array_values(
            array_unique(
                config('employee.hr_required_fields', [])
            )
        );

        /*
     * Pastikan field identitas utama tetap masuk
     * ke kelompok Employee Data.
     */
        $employeeFields = array_values(
            array_unique([
                'display_name',
                ...$employeeConfiguredFields,
            ])
        );

        $employeeFields = array_values(
            array_intersect(
                $employeeFields,
                $tableColumns
            )
        );

        $hrFields = array_values(
            array_intersect(
                $hrConfiguredFields,
                $tableColumns
            )
        );

        /*
     * Kolom lain yang tidak tercantum dalam konfigurasi
     * tetap ditampilkan sebagai Additional Data.
     */
        $additionalFields = array_values(
            array_diff(
                $tableColumns,
                $hiddenColumns,
                $employeeFields,
                $hrFields
            )
        );

        $makeLabel = static function (string $field): string {
            $label = Str::of($field)
                ->replace('_', ' ')
                ->title()
                ->toString();

            return str_replace(
                [
                    ' Id',
                    'Ktp',
                    'Npwp',
                    'Bpjs',
                    'Pic',
                    ' Hr',
                    ' Od',
                    'Sso',
                ],
                [
                    ' ID',
                    'KTP',
                    'NPWP',
                    'BPJS',
                    'PIC',
                    ' HR',
                    ' OD',
                    'SSO',
                ],
                $label
            );
        };

        $makeFields = static function (
            array $fields
        ) use (
            $employee,
            $makeLabel
        ): array {
            return collect($fields)
                ->map(function (string $field) use (
                    $employee,
                    $makeLabel
                ): array {
                    $rawValue = data_get(
                        $employee,
                        $field
                    );

                    /*
                 * Nilai 0 tetap dianggap terisi.
                 */
                    $isFilled = $rawValue !== null
                        && (
                            !is_string($rawValue)
                            || trim($rawValue) !== ''
                        ) && $rawValue != '---' && $rawValue != '--';

                    $displayValue = null;

                    if ($isFilled) {
                        if ($field === 'active') {
                            $displayValue = (int) $rawValue === 1
                                ? 'Active'
                                : 'Blocked';
                        } elseif ($rawValue instanceof \DateTimeInterface) {
                            $displayValue = $rawValue->format(
                                'd M Y'
                            );
                        } elseif (is_bool($rawValue)) {
                            $displayValue = $rawValue
                                ? 'Yes'
                                : 'No';
                        } elseif (
                            is_array($rawValue)
                            || is_object($rawValue)
                        ) {
                            $displayValue = json_encode(
                                $rawValue,
                                JSON_PRETTY_PRINT
                                    | JSON_UNESCAPED_UNICODE
                            );
                        } else {
                            $displayValue = (string) $rawValue;
                        }
                    }

                    return [
                        'name' => $field,
                        'label' => $makeLabel($field),
                        'value' => $displayValue,
                        'filled' => $isFilled,
                    ];
                })
                ->values()
                ->all();
        };

        $groups = [
            [
                'title' => 'Employee Data',
                'description' => 'Information completed by the employee.',
                'fields' => $makeFields($employeeFields),
            ],
            [
                'title' => 'HR Data',
                'description' => 'Employment and administrative information completed by HR.',
                'fields' => $makeFields($hrFields),
            ],
        ];

        return response()->json([
            'employee_name' => $employee->display_name
                ?: 'Employee Details',

            'html' => view(
                'components.dashboard.employee-details',
                [
                    'employee' => $employee,
                    'groups' => $groups,
                ]
            )->render(),
        ]);
    }
}
