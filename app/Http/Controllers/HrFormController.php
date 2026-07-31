<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class HrFormController extends Controller
{
    public function index(Request $request): View
    {
        $config = $this->listConfig();

        $selectedPic = trim(
            (string) $request->query('pic', '')
        );

        $search = trim(
            (string) $request->query('search', '')
        );

        $employees = $this->eligibleEmployeesQuery($config)
            ->when(
                $selectedPic !== '',
                fn($query) => $query->where(
                    'e.' . $config['pic_column'],
                    $selectedPic
                )
            )
            ->when(
                $search !== '',
                function ($query) use ($search): void {
                    $keyword = '%' . $search . '%';

                    $query->where(
                        function ($searchQuery) use ($keyword): void {
                            $searchQuery
                                ->whereRaw(
                                    'CAST(e.employee_id AS CHAR) LIKE ?',
                                    [$keyword]
                                )
                                ->orWhere(
                                    'e.display_name',
                                    'like',
                                    $keyword
                                );
                        }
                    );
                }
            )
            ->select([
                'e.employee_id',
                'e.display_name',
                'e.' . $config['pic_column'] . ' as pic_id',
                'p.' . $config['pic_name_column'] . ' as pic_name',
            ])
            ->orderBy('e.display_name')
            ->orderBy('e.employee_id')
            ->paginate(15)
            ->withQueryString();

        /*
     * Daftar PIC tidak mengikuti search agar pilihan PIC
     * tetap menampilkan seluruh PIC yang memiliki employee eligible.
     */
        $pics = $this->eligibleEmployeesQuery($config)
            ->select([
                'e.' . $config['pic_column'] . ' as id',
                'p.' . $config['pic_name_column'] . ' as name',
            ])
            ->distinct()
            ->orderBy('p.' . $config['pic_name_column'])
            ->get();

        return view('hr-form.index', [
            'employees' => $employees,
            'pics' => $pics,
            'selectedPic' => $selectedPic,
            'search' => $search,
        ]);
    }

    public function edit(string $employeeId): View
    {
        $config = $this->listConfig();

        $employee = DB::table($config['employee_table'] . ' as e')
            ->join(
                $config['pic_table'] . ' as p',
                'p.' . $config['pic_primary_key'],
                '=',
                'e.' . $config['pic_column']
            )
            ->where('e.employee_id', $employeeId)
            ->whereNotNull('e.' . $config['pic_column'])
            ->whereIn(
                DB::raw(
                    'LOWER(TRIM(CAST(e.'
                        . $this->quoteIdentifier($config['status_column'])
                        . ' AS CHAR)))'
                ),
                $this->activeStatusValues($config)
            )
            ->select([
                'e.*',
                'p.' . $config['pic_name_column'] . ' as pic_name',
            ])
            ->first();

        abort_if(! $employee, 404, 'Data employee tidak ditemukan atau tidak memenuhi syarat.');

        $employeeData = $this->normalizeEmployeeForForm((array) $employee);

        return view('hr-form.edit', [
            'employee' => $employee,
            'employeeData' => $employeeData,
            'groups' => $this->formGroups(),
        ]);
    }

    public function update(Request $request, string $employeeId): RedirectResponse
    {
        $config = $this->listConfig();
        $returnPic = trim((string) $request->input('_pic_filter', ''));
        $returnSearch = trim(
            (string) $request->input('_search_filter', '')
        );

        $employeeExists = DB::table($config['employee_table'])
            ->where('employee_id', $employeeId)
            ->whereNotNull($config['pic_column'])
            ->whereIn(
                DB::raw(
                    'LOWER(TRIM(CAST('
                        . $this->quoteIdentifier($config['status_column'])
                        . ' AS CHAR)))'
                ),
                $this->activeStatusValues($config)
            )
            ->exists();

        abort_if(! $employeeExists, 404, 'Data employee tidak ditemukan atau tidak memenuhi syarat.');

        $fields = $this->editableFields();
        $payload = $this->normalizePayload($request->only($fields));
        $rules = [];
        $attributes = [];

        foreach ($fields as $field) {
            $meta = config("employee.hr_field_meta.{$field}", []);
            $type = $meta['type'] ?? 'text';
            $attributes[$field] = $meta['label']
                ?? str($field)->replace('_', ' ')->title()->toString();

            if ($type === 'date') {
                $rules[$field] = ['bail', 'required', 'date_format:Y-m-d'];
                continue;
            }

            if ($type === 'select' && ! empty($meta['options'])) {
                $rules[$field] = [
                    'bail',
                    'required',
                    Rule::in($meta['options']),
                ];
                continue;
            }

            $rules[$field] = ['bail', 'required', 'string', 'max:1000'];
        }

        $validated = Validator::make(
            $payload,
            $rules,
            [
                'required' => ':attribute wajib diisi.',
                'date_format' => ':attribute harus menggunakan format tanggal yang valid.',
                'in' => ':attribute memiliki nilai yang tidak valid.',
                'max' => ':attribute terlalu panjang.',
            ],
            $attributes
        )->validate();

        foreach ($this->booleanFields() as $booleanField) {
            if (array_key_exists($booleanField, $validated)) {
                $validated[$booleanField] = $validated[$booleanField] === 'Yes' ? 1 : 0;
            }
        }

        DB::transaction(function () use ($config, $employeeId, $validated): void {
            DB::table($config['employee_table'])
                ->where('employee_id', $employeeId)
                ->update($validated);
        });

        $returnParameters = [];

        if ($returnPic !== '') {
            $returnParameters['pic'] = $returnPic;
        }

        if ($returnSearch !== '') {
            $returnParameters['search'] = $returnSearch;
        }

        return redirect()
            ->route(
                'hr-form.index',
                $returnParameters
            )
            ->with(
                'success',
                "Data HR untuk Employee ID {$employeeId} berhasil disimpan."
            );
    }

    private function eligibleEmployeesQuery(array $config)
    {
        $query = DB::table($config['employee_table'] . ' as e')
            ->join(
                $config['pic_table'] . ' as p',
                'p.' . $config['pic_primary_key'],
                '=',
                'e.' . $config['pic_column']
            )
            ->whereNotNull('e.' . $config['pic_column'])
            ->whereIn(
                DB::raw(
                    'LOWER(TRIM(CAST(e.'
                        . $this->quoteIdentifier($config['status_column'])
                        . ' AS CHAR)))'
                ),
                $this->activeStatusValues($config)
            );

        $emptyValues = collect(config('employee.empty_values', []))
            ->filter(fn($value): bool => is_string($value) && trim($value) !== '')
            ->map(fn(string $value): string => strtoupper(trim($value)))
            ->values()
            ->all();

        $query->where(function ($missingFieldsQuery) use ($emptyValues): void {
            foreach ($this->editableFields() as $field) {
                $qualifiedColumn = 'e.' . $field;
                $sqlColumn = 'e.' . $this->quoteIdentifier($field);

                $missingFieldsQuery->orWhere(function ($fieldQuery) use (
                    $qualifiedColumn,
                    $sqlColumn,
                    $emptyValues
                ): void {
                    $fieldQuery
                        ->whereNull($qualifiedColumn)
                        ->orWhereRaw(
                            'TRIM(CAST(' . $sqlColumn . ' AS CHAR)) = ?',
                            ['']
                        );

                    if ($emptyValues !== []) {
                        $placeholders = implode(', ', array_fill(0, count($emptyValues), '?'));

                        $fieldQuery->orWhereRaw(
                            'UPPER(TRIM(CAST(' . $sqlColumn . ' AS CHAR))) IN (' . $placeholders . ')',
                            $emptyValues
                        );
                    }
                });
            }
        });

        return $query;
    }

    private function formGroups(): array
    {
        $fieldMeta = config('employee.hr_field_meta', []);
        $groupMeta = config('employee.hr_field_groups', []);

        return collect($this->editableFields())
            ->map(function (string $field) use ($fieldMeta): array {
                $meta = $fieldMeta[$field] ?? [];

                return array_merge([
                    'name' => $field,
                    'label' => str($field)->replace('_', ' ')->title()->toString(),
                    'group' => 'employment',
                    'type' => 'text',
                    'placeholder' => null,
                    'inputmode' => null,
                    'options' => [],
                    'rows' => 3,
                ], $meta, ['name' => $field]);
            })
            ->groupBy('group')
            ->map(function ($fields, string $groupKey) use ($groupMeta): array {
                return [
                    'key' => $groupKey,
                    'title' => $groupMeta[$groupKey]['title']
                        ?? str($groupKey)->headline()->toString(),
                    'description' => $groupMeta[$groupKey]['description'] ?? null,
                    'fields' => $fields->values()->all(),
                ];
            })
            ->values()
            ->all();
    }

    private function normalizeEmployeeForForm(array $employee): array
    {
        $emptyValues = config('employee.empty_values', []);

        return collect($this->editableFields())
            ->mapWithKeys(function (string $field) use ($employee, $emptyValues): array {
                $value = $employee[$field] ?? '';

                if (is_string($value)) {
                    $value = trim($value);
                }

                if (
                    $value === null
                    || $value === ''
                    || in_array($value, $emptyValues, true)
                ) {
                    return [$field => ''];
                }

                if (in_array($field, $this->booleanFields(), true)) {
                    return [$field => in_array($value, [1, '1', true, 'Yes', 'yes'], true)
                        ? 'Yes'
                        : 'No'];
                }

                if ($field === 'date_of_join') {
                    return [$field => substr((string) $value, 0, 10)];
                }

                return [$field => $value];
            })
            ->all();
    }

    private function normalizePayload(array $payload): array
    {
        $emptyValues = config('employee.empty_values', []);

        return collect($payload)
            ->map(function ($value) use ($emptyValues) {
                if (! is_string($value)) {
                    return $value;
                }

                $value = trim($value);

                return in_array($value, $emptyValues, true) ? null : $value;
            })
            ->all();
    }

    private function editableFields(): array
    {
        return collect(config('employee.hr_required_fields', []))
            ->filter(fn($field): bool => is_string($field) && $field !== 'employee_id')
            ->unique()
            ->values()
            ->all();
    }

    private function booleanFields(): array
    {
        return [
            'tax_movement_recalculate',
            'bpjs_jamsostek_contribution',
            'bpjs_pension_eligibility',
        ];
    }

    private function listConfig(): array
    {
        return array_merge([
            'employee_table' => 'employee_details',
            'status_column' => 'active',
            'active_status_values' => ['1'],
            'pic_column' => 'pic_nip',
            'pic_table' => 'pics',
            'pic_primary_key' => 'nip',
            'pic_name_column' => 'name',
        ], config('employee.hr_employee_list', []));
    }

    private function activeStatusValues(array $config): array
    {
        $values = collect($config['active_status_values'] ?? ['Active'])
            ->filter(fn($value): bool => is_string($value) && trim($value) !== '')
            ->map(fn(string $value): string => strtolower(trim($value)))
            ->unique()
            ->values()
            ->all();

        return $values !== [] ? $values : ['active'];
    }

    private function quoteIdentifier(string $identifier): string
    {
        return '`' . str_replace('`', '``', $identifier) . '`';
    }
}
