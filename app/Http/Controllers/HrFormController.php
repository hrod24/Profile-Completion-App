<?php

namespace App\Http\Controllers;

use App\Models\employee_details;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class HrFormController extends Controller
{
    public function index(Request $request): View
    {
        $config =
            $this->listConfig();

        $selectedPic = trim(
            (string) $request->query(
                'pic',
                ''
            )
        );

        $search = trim(
            (string) $request->query(
                'search',
                ''
            )
        );

        $employees =
            $this->eligibleEmployeesQuery(
                $config
            )
            ->when(
                $selectedPic !== '',
                fn($query) =>
                $query->where(
                    'e.'
                        . $config['pic_column'],
                    $selectedPic
                )
            )
            ->when(
                $search !== '',
                function (
                    $query
                ) use ($search): void {
                    $keyword =
                        '%'
                        . $search
                        . '%';

                    $query->where(
                        function (
                            $searchQuery
                        ) use ($keyword): void {
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

                'e.'
                    . $config['pic_column']
                    . ' as pic_id',

                'p.'
                    . $config['pic_name_column']
                    . ' as pic_name',
            ])
            ->orderBy(
                'e.display_name'
            )
            ->orderBy(
                'e.employee_id'
            )
            ->paginate(15)
            ->withQueryString();


        $pics =
            $this->eligibleEmployeesQuery(
                $config
            )
            ->select([
                'e.'
                    . $config['pic_column']
                    . ' as id',

                'p.'
                    . $config['pic_name_column']
                    . ' as name',
            ])
            ->distinct()
            ->orderBy(
                'p.'
                    . $config['pic_name_column']
            )
            ->get();


        return view(
            'hr-form.index',
            [
                'employees' =>
                $employees,

                'pics' =>
                $pics,

                'selectedPic' =>
                $selectedPic,

                'search' =>
                $search,
            ]
        );
    }


    /*
     * ============================================================
     * EDIT
     * ============================================================
     */
    public function edit(
        string $employeeId
    ): View {
        $config =
            $this->listConfig();

        $employee =
            DB::table(
                $config['employee_table']
                    . ' as e'
            )
            ->join(
                $config['pic_table']
                    . ' as p',

                'p.'
                    . $config['pic_primary_key'],

                '=',

                'e.'
                    . $config['pic_column']
            )
            ->where(
                'e.employee_id',
                $employeeId
            )
            ->whereNotNull(
                'e.'
                    . $config['pic_column']
            )
            ->whereIn(
                DB::raw(
                    'LOWER(TRIM(CAST(e.'
                        . $this->quoteIdentifier(
                            $config['status_column']
                        )
                        . ' AS CHAR)))'
                ),
                $this->activeStatusValues(
                    $config
                )
            )
            ->select([
                'e.*',

                'p.'
                    . $config['pic_name_column']
                    . ' as pic_name',
            ])
            ->first();


        abort_if(
            !$employee,
            404,
            'Data employee tidak ditemukan atau tidak memenuhi syarat.'
        );


        $employeeData =
            $this
            ->normalizeEmployeeForForm(
                (array) $employee
            );


        $organizationConfig =
            $this->organizationConfig();


        $businessUnits =
            DB::table(
                $organizationConfig['business_unit_table']
            )
            ->select([
                $organizationConfig['business_unit_code_column']
                    . ' as code',

                $organizationConfig['business_unit_name_column']
                    . ' as name',
            ])
            ->orderBy(
                $organizationConfig['business_unit_name_column']
            )
            ->orderBy(
                $organizationConfig['business_unit_code_column']
            )
            ->get();


        $departmentsByBusinessUnit =
            DB::table(
                $organizationConfig['relation_table']
                    . ' as relation'
            )
            ->join(
                $organizationConfig['department_table']
                    . ' as department',

                'department.'
                    . $organizationConfig['department_code_column'],

                '=',

                'relation.'
                    . $organizationConfig['relation_department_column']
            )
            ->select([
                'relation.'
                    . $organizationConfig['relation_business_unit_column']
                    . ' as business_unit_code',

                'department.'
                    . $organizationConfig['department_code_column']
                    . ' as code',

                'department.'
                    . $organizationConfig['department_name_column']
                    . ' as name',
            ])
            ->orderBy(
                'department.'
                    . $organizationConfig['department_name_column']
            )
            ->orderBy(
                'department.'
                    . $organizationConfig['department_code_column']
            )
            ->get()
            ->groupBy(
                'business_unit_code'
            )
            ->map(
                function (
                    $departments
                ): array {
                    return $departments
                        ->map(
                            function (
                                $department
                            ): array {
                                return [
                                    'code' =>
                                    $department
                                        ->code,

                                    'name' =>
                                    $department
                                        ->name,
                                ];
                            }
                        )
                        ->values()
                        ->all();
                }
            )
            ->all();


        /*
         * Attachment Personal Info.
         */
        $documentFields = [
            'ijazah_filename',
            'ktp_filename',
            'kk_filename',
            'npwp_filename',
        ];

        $existingDocuments = [];

        foreach (
            $documentFields
            as $field
        ) {
            $path =
                $employee->{$field}
                ?? null;

            $existingDocuments[$field] =
                filled($path)
                && Storage::disk(
                    'public'
                )->exists(
                    $path
                );
        }


        return view(
            'hr-form.edit',
            [
                'employee' =>
                $employee,

                'employeeData' =>
                $employeeData,

                'groups' =>
                $this->formGroups(),

                'businessUnits' =>
                $businessUnits,

                'departmentsByBusinessUnit' =>
                $departmentsByBusinessUnit,

                'employeeRequiredFields' =>
                config(
                    'employee.employee_required_fields',
                    []
                ),

                'existingDocuments' =>
                $existingDocuments,
            ]
        );
    }


    /*
     * ============================================================
     * UPDATE EMPLOYMENT INFO
     * ============================================================
     */
    public function update(
        Request $request,
        string $employeeId
    ): RedirectResponse {
        $config =
            $this->listConfig();

        $returnPic = trim(
            (string) $request->input(
                '_pic_filter',
                ''
            )
        );

        $returnSearch = trim(
            (string) $request->input(
                '_search_filter',
                ''
            )
        );


        $employeeExists =
            DB::table(
                $config['employee_table']
            )
            ->where(
                'employee_id',
                $employeeId
            )
            ->whereNotNull(
                $config['pic_column']
            )
            ->whereIn(
                DB::raw(
                    'LOWER(TRIM(CAST('
                        . $this->quoteIdentifier(
                            $config['status_column']
                        )
                        . ' AS CHAR)))'
                ),
                $this->activeStatusValues(
                    $config
                )
            )
            ->exists();


        abort_if(
            !$employeeExists,
            404,
            'Data employee tidak ditemukan atau tidak memenuhi syarat.'
        );


        $fields =
            $this->editableFields();

        $payload =
            $this->normalizePayload(
                $request->only(
                    $fields
                )
            );

        $organizationConfig =
            $this->organizationConfig();

        $businessUnitField =
            'business_unit_org_element_1';

        $departmentField =
            'department_org_element_2';

        $selectedBusinessUnit =
            $payload[$businessUnitField] ?? '';

        $rules = [];
        $attributes = [];


        foreach (
            $fields
            as $field
        ) {
            $meta = config(
                "employee.hr_field_meta.{$field}",
                []
            );

            $type =
                $meta['type']
                ?? 'text';

            $attributes[$field] =
                $meta['label']
                ?? str($field)
                ->replace(
                    '_',
                    ' '
                )
                ->title()
                ->toString();


            if (
                $field ===
                $businessUnitField
            ) {
                $rules[$field] = [
                    'bail',
                    'required',
                    'string',

                    Rule::exists(
                        $organizationConfig['business_unit_table'],
                        $organizationConfig['business_unit_code_column']
                    ),
                ];

                continue;
            }


            if (
                $field ===
                $departmentField
            ) {
                $rules[$field] = [
                    'bail',
                    'required',
                    'string',

                    Rule::exists(
                        $organizationConfig['relation_table'],
                        $organizationConfig['relation_department_column']
                    )->where(
                        function (
                            $query
                        ) use (
                            $organizationConfig,
                            $selectedBusinessUnit
                        ): void {
                            $query->where(
                                $organizationConfig['relation_business_unit_column'],
                                $selectedBusinessUnit
                            );
                        }
                    ),
                ];

                continue;
            }


            if (
                $type === 'date'
            ) {
                $rules[$field] = [
                    'bail',
                    'required',
                    'date_format:Y-m-d',
                ];

                continue;
            }


            if (
                $type === 'select'
                && !empty($meta['options'])
            ) {
                $rules[$field] = [
                    'bail',
                    'required',

                    Rule::in(
                        $meta['options']
                    ),
                ];

                continue;
            }


            $rules[$field] = [
                'bail',
                'required',
                'string',
                'max:1000',
            ];
        }


        $validated =
            Validator::make(
                $payload,
                $rules,
                [
                    'required' =>
                    ':attribute wajib diisi.',

                    'date_format' =>
                    ':attribute harus menggunakan format tanggal yang valid.',

                    'in' =>
                    ':attribute memiliki nilai yang tidak valid.',

                    'exists' =>
                    ':attribute memiliki nilai yang tidak valid.',

                    'max' =>
                    ':attribute terlalu panjang.',

                    $businessUnitField
                        . '.exists' =>
                    'Business Unit yang dipilih tidak tersedia.',

                    $departmentField
                        . '.exists' =>
                    'Department yang dipilih tidak berelasi dengan Business Unit.',
                ],
                $attributes
            )
            ->validate();


        foreach (
            $this->booleanFields()
            as $booleanField
        ) {
            if (
                array_key_exists(
                    $booleanField,
                    $validated
                )
            ) {
                $validated[$booleanField] =
                    $validated[$booleanField] === 'Yes'
                    ? 1
                    : 0;
            }
        }


        DB::transaction(
            function () use (
                $config,
                $employeeId,
                $validated
            ): void {
                DB::table(
                    $config['employee_table']
                )
                    ->where(
                        'employee_id',
                        $employeeId
                    )
                    ->update(
                        $validated
                    );
            }
        );


        $returnParameters = [];

        if (
            $returnPic !== ''
        ) {
            $returnParameters['pic'] = $returnPic;
        }

        if (
            $returnSearch !== ''
        ) {
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


    /*
     * ============================================================
     * UPDATE PERSONAL INFO
     * ============================================================
     */
    public function updatePersonal(
        Request $request,
        string $employeeId
    ): RedirectResponse {
        $config =
            $this->listConfig();


        $employeeExists =
            DB::table(
                $config['employee_table']
            )
            ->where(
                'employee_id',
                $employeeId
            )
            ->whereNotNull(
                $config['pic_column']
            )
            ->whereIn(
                DB::raw(
                    'LOWER(TRIM(CAST('
                        . $this->quoteIdentifier(
                            $config['status_column']
                        )
                        . ' AS CHAR)))'
                ),
                $this->activeStatusValues(
                    $config
                )
            )
            ->exists();


        abort_if(
            !$employeeExists,
            404,
            'Data employee tidak ditemukan atau tidak memenuhi syarat.'
        );


        $employee =
            employee_details::query()
            ->where(
                'employee_id',
                $employeeId
            )
            ->firstOrFail();


        /*
         * Sama dengan employee form:
         * kode pos KTP mengikuti current postal code.
         */
        $request->merge([
            'ktp_postal_code' =>
            $request->input(
                'current_postal_code'
            ),
        ]);


        $employeeRequiredFields =
            config(
                'employee.employee_required_fields',
                []
            );


        $emptyValues =
            array_map(
                fn($value) =>
                strtoupper(
                    trim(
                        (string) $value
                    )
                ),

                config(
                    'employee.empty_values',
                    []
                )
            );


        $notPlaceholder =
            function (
                string $attribute,
                mixed $value,
                \Closure $fail
            ) use ($emptyValues): void {
                $normalizedValue =
                    strtoupper(
                        trim(
                            (string) $value
                        )
                    );

                if (
                    in_array(
                        $normalizedValue,
                        $emptyValues,
                        true
                    )
                ) {
                    $fail(
                        "Field {$attribute} harus diisi dengan data yang valid."
                    );
                }
            };


        $fileFields = [
            'ktp_filename',
            'kk_filename',
            'ijazah_filename',
            'npwp_filename',
        ];


        $documentIsMissing =
            function (
                string $field
            ) use ($employee): bool {
                $path =
                    $employee->{$field};

                return
                    blank($path)
                    || !Storage::disk(
                        'public'
                    )->exists(
                        $path
                    );
            };


        $rules =
            $this->personalRules(
                $documentIsMissing
            );


        /*
         * Required / nullable berdasarkan
         * employee_required_fields.
         */
        foreach (
            $rules
            as $field => &$fieldRules
        ) {
            if (
                in_array(
                    $field,
                    $fileFields,
                    true
                )
            ) {
                continue;
            }

            if (
                in_array(
                    $field,
                    $employeeRequiredFields,
                    true
                )
            ) {
                array_unshift(
                    $fieldRules,
                    'required'
                );

                $fieldRules[] =
                    $notPlaceholder;
            } else {
                array_unshift(
                    $fieldRules,
                    'nullable'
                );
            }
        }

        unset(
            $fieldRules
        );


        $validated =
            Validator::make(
                $request->all(),
                $rules,
                $this->personalValidationMessages(),
                $this->personalValidationAttributes()
            )
            ->validate();


        /*
         * File tidak dimasukkan ke fill().
         */
        $employeeData =
            Arr::except(
                $validated,
                $fileFields
            );


        $employee->fill(
            $employeeData
        );


        /*
         * ========================================================
         * FILE ATTACHMENT
         * ========================================================
         */
        $fileFieldMap = [
            'ktp_filename' =>
            'ktp',

            'kk_filename' =>
            'kk',

            'ijazah_filename' =>
            'ijazah',

            'npwp_filename' =>
            'npwp',
        ];


        $safeEmployeeId =
            preg_replace(
                '/[^A-Za-z0-9_-]/',
                '_',
                $employee
                    ->employee_id
            );


        foreach (
            $fileFieldMap
            as $field => $folder
        ) {
            if (
                !$request
                    ->hasFile(
                        $field
                    )
            ) {
                continue;
            }


            $file =
                $request->file(
                    $field
                );


            $extension =
                strtolower(
                    $file
                        ->getClientOriginalExtension()
                );


            /*
             * Nama file:
             * NIP.extension
             */
            $fileName =
                $safeEmployeeId
                . '.'
                . $extension;


            $directory =
                'employee-documents/'
                . $folder;


            $oldPath =
                $employee->{$field};


            $newPath =
                $file->storeAs(
                    $directory,
                    $fileName,
                    'public'
                );


            $employee->{$field} =
                $newPath;


            /*
             * Kalau extension berubah,
             * hapus file lama.
             */
            if (
                filled(
                    $oldPath
                )
                && $oldPath
                !== $newPath
                && Storage::disk(
                    'public'
                )->exists(
                    $oldPath
                )
            ) {
                Storage::disk(
                    'public'
                )->delete(
                    $oldPath
                );
            }
        }


        $employee->save();
        $employee->refresh();


        /*
         * ========================================================
         * RECALCULATE EMPLOYEE COMPLETION
         * ========================================================
         */
        $recognizedAsComplete =
            employee_details::query()
            ->whereKey(
                $employee->id
            )
            ->employeeDataComplete()
            ->exists();


        if (
            $recognizedAsComplete
        ) {
            if (
                is_null(
                    $employee
                        ->employee_completed_at
                )
            ) {
                $employee->update([
                    'employee_completed_at' =>
                    now(),
                ]);
            }
        } else {
            if (
                !is_null(
                    $employee
                        ->employee_completed_at
                )
            ) {
                $employee->update([
                    'employee_completed_at' =>
                    null,
                ]);
            }
        }


        $parameters = [
            'employeeId' =>
            $employeeId,

            'tab' =>
            'personal',
        ];


        $returnPic = trim(
            (string) $request->input(
                '_pic_filter',
                ''
            )
        );

        $returnSearch = trim(
            (string) $request->input(
                '_search_filter',
                ''
            )
        );


        if (
            $returnPic !== ''
        ) {
            $parameters['pic'] = $returnPic;
        }

        if (
            $returnSearch !== ''
        ) {
            $parameters['search'] = $returnSearch;
        }


        return redirect()
            ->route(
                'hr-form.edit',
                $parameters
            )
            ->with(
                'success',
                "Personal Info Employee ID {$employeeId} berhasil disimpan."
            );
    }


    /*
     * ============================================================
     * PERSONAL RULES
     * ============================================================
     */
    private function personalRules(
        \Closure $documentIsMissing
    ): array {
        $employeeRequiredFields =
            config(
                'employee.employee_required_fields',
                []
            );

        $isRequired =
            fn(string $field): bool =>
            in_array(
                $field,
                $employeeRequiredFields,
                true
            );


        return [
            /*
             * Identity
             */
            'ktp_number' => [
                'string',
                'digits:16',
            ],


            /*
             * Personal profile
             */
            'display_name' => [
                'string',
                'max:100',
            ],

            'gender' => [
                Rule::in([
                    'Male',
                    'Female',
                ]),
            ],

            'birth_place' => [
                'string',
                'max:100',
            ],

            'date_of_birth' => [
                'date',
                'after_or_equal:1900-01-01',
                'before_or_equal:today',
            ],

            'religion' => [
                Rule::in([
                    'Islam',
                    'Hinduism',
                    'Christianity',
                    'Buddhism',
                    'Catholicism',
                    'Sikhism',
                    'Other',
                ]),
            ],

            'marital_status' => [
                Rule::in([
                    'Single',
                    'Married',
                    'Divorced',
                    'Widowed',
                ]),
            ],

            'blood_group' => [
                Rule::in([
                    'A+',
                    'B+',
                    'AB+',
                    'O+',
                    'A-',
                    'B-',
                    'AB-',
                    'O-',
                ]),
            ],

            'nationality' => [
                'string',
                'max:50',
            ],


            /*
             * Contact
             */
            'primary_email' => [
                'email:rfc',
                'max:191',
            ],

            'primary_contact_number' => [
                'string',
                'max:30',
                'regex:/^[0-9+\-\s()]+$/',
            ],

            'emergency_full_name' => [
                'string',
                'max:100',
            ],

            'emergency_contact_no' => [
                'string',
                'max:30',
                'regex:/^[0-9+\-\s()]+$/',
            ],


            /*
             * Current address
             */
            'current_address' => [
                'string',
                'max:2000',
            ],

            'current_provinsi' => [
                'string',
                'max:100',
            ],

            'current_kotamadya_kabupaten' => [
                'string',
                'max:100',
            ],

            'current_kecamatan' => [
                'string',
                'max:100',
            ],

            'current_kelurahan' => [
                'string',
                'max:100',
            ],

            'current_postal_code' => [
                'string',
                'digits:5',
            ],


            /*
             * KTP address
             */
            'ktp_address' => [
                'string',
                'max:2000',
            ],

            'ktp_provinsi' => [
                'string',
                'max:100',
            ],

            'ktp_kotamadya_kabupaten' => [
                'string',
                'max:100',
            ],

            'ktp_kecamatan' => [
                'string',
                'max:100',
            ],

            'ktp_kelurahan' => [
                'string',
                'max:100',
            ],

            'ktp_postal_code' => [
                'string',
                'digits:5',
            ],


            /*
             * Family & education
             */
            'mother_full_name' => [
                'string',
                'max:100',
            ],

            'education_level' => [
                Rule::in([
                    'SMA',
                    'SMK',
                    'D1',
                    'D2',
                    'D3',
                    'D4',
                    'S1',
                    'S2',
                    'S3',
                ]),
            ],

            'major' => [
                'string',
                'max:100',
            ],

            'institution_name' => [
                'string',
                'max:150',
            ],

            'education_from' => [
                'integer',
                'digits:4',
                'between:1800,2100',
            ],

            'education_end' => [
                'integer',
                'digits:4',
                'between:1800,2100',
                'gte:education_from',
            ],


            /*
             * Tax
             */
            'tax_number' => [
                'string',
                'max:30',
                'regex:/^[0-9.\-]+$/',
            ],


            /*
             * Attachments
             */
            'ktp_filename' => [
                Rule::requiredIf(
                    fn(): bool =>
                    $isRequired(
                        'ktp_filename'
                    )
                        && $documentIsMissing(
                            'ktp_filename'
                        )
                ),

                'nullable',
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:5120',
            ],

            'kk_filename' => [
                Rule::requiredIf(
                    fn(): bool =>
                    $isRequired(
                        'kk_filename'
                    )
                        && $documentIsMissing(
                            'kk_filename'
                        )
                ),

                'nullable',
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:5120',
            ],

            'ijazah_filename' => [
                Rule::requiredIf(
                    fn(): bool =>
                    $isRequired(
                        'ijazah_filename'
                    )
                        && $documentIsMissing(
                            'ijazah_filename'
                        )
                ),

                'nullable',
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:5120',
            ],

            'npwp_filename' => [
                Rule::requiredIf(
                    fn(): bool =>
                    $isRequired(
                        'npwp_filename'
                    )
                        && $documentIsMissing(
                            'npwp_filename'
                        )
                ),

                'nullable',
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:5120',
            ],
        ];
    }


    private function personalValidationMessages(): array
    {
        return [
            'date_of_birth.date' =>
            'Tanggal lahir harus berupa tanggal yang valid.',

            'date_of_birth.after_or_equal' =>
            'Tanggal lahir tidak valid.',

            'date_of_birth.before_or_equal' =>
            'Tanggal lahir tidak boleh melebihi hari ini.',

            'primary_email.email' =>
            'Format email utama tidak valid.',

            'primary_contact_number.regex' =>
            'Nomor kontak utama hanya boleh berisi angka, tanda +, tanda -, spasi, atau tanda kurung.',

            'emergency_contact_no.regex' =>
            'Nomor kontak darurat hanya boleh berisi angka, tanda +, tanda -, spasi, atau tanda kurung.',

            'ktp_number.digits' =>
            'Nomor KTP harus terdiri dari tepat 16 angka.',

            'tax_number.regex' =>
            'Nomor pajak hanya boleh berisi angka, titik, dan tanda hubung.',

            'current_postal_code.digits' =>
            'Kode pos domisili harus terdiri dari 5 angka.',

            'ktp_postal_code.digits' =>
            'Kode pos KTP harus terdiri dari 5 angka.',

            'education_from.integer' =>
            'Tahun mulai pendidikan harus berupa angka.',

            'education_from.digits' =>
            'Tahun mulai pendidikan harus terdiri dari 4 angka.',

            'education_from.between' =>
            'Tahun mulai pendidikan harus berada antara 1800 dan 2100.',

            'education_end.integer' =>
            'Tahun selesai pendidikan harus berupa angka.',

            'education_end.digits' =>
            'Tahun selesai pendidikan harus terdiri dari 4 angka.',

            'education_end.between' =>
            'Tahun selesai pendidikan harus berada antara 1800 dan 2100.',

            'education_end.gte' =>
            'Tahun selesai pendidikan tidak boleh lebih kecil dari tahun mulai pendidikan.',

            'ktp_filename.required' =>
            'File KTP wajib diunggah.',

            'kk_filename.required' =>
            'File Kartu Keluarga wajib diunggah.',

            'ijazah_filename.required' =>
            'File ijazah wajib diunggah.',

            'npwp_filename.required' =>
            'File NPWP wajib diunggah.',

            '*.mimes' =>
            'File harus berformat PDF, JPG, JPEG, atau PNG.',

            '*.max' =>
            'Ukuran file maksimal 5 MB.',
        ];
    }


    private function personalValidationAttributes(): array
    {
        return [
            'display_name' =>
            'nama lengkap',

            'gender' =>
            'jenis kelamin',

            'birth_place' =>
            'tempat lahir',

            'date_of_birth' =>
            'tanggal lahir',

            'religion' =>
            'agama',

            'marital_status' =>
            'status pernikahan',

            'blood_group' =>
            'golongan darah',

            'nationality' =>
            'kewarganegaraan',

            'mother_full_name' =>
            'nama lengkap ibu',

            'primary_email' =>
            'email utama',

            'primary_contact_number' =>
            'nomor kontak utama',

            'emergency_full_name' =>
            'nama kontak darurat',

            'emergency_contact_no' =>
            'nomor kontak darurat',

            'current_address' =>
            'alamat domisili',

            'current_provinsi' =>
            'provinsi domisili',

            'current_kotamadya_kabupaten' =>
            'kota/kabupaten domisili',

            'current_kecamatan' =>
            'kecamatan domisili',

            'current_kelurahan' =>
            'kelurahan domisili',

            'current_postal_code' =>
            'kode pos domisili',

            'ktp_number' =>
            'nomor KTP',

            'ktp_address' =>
            'alamat KTP',

            'ktp_provinsi' =>
            'provinsi KTP',

            'ktp_kotamadya_kabupaten' =>
            'kota/kabupaten KTP',

            'ktp_kecamatan' =>
            'kecamatan KTP',

            'ktp_kelurahan' =>
            'kelurahan KTP',

            'ktp_postal_code' =>
            'kode pos KTP',

            'tax_number' =>
            'nomor pajak',

            'education_level' =>
            'tingkat pendidikan',

            'major' =>
            'jurusan',

            'institution_name' =>
            'nama institusi pendidikan',

            'education_from' =>
            'tahun mulai pendidikan',

            'education_end' =>
            'tahun selesai pendidikan',
        ];
    }


    /*
     * ============================================================
     * EMPLOYEE LIST QUERY
     * ============================================================
     */
    private function eligibleEmployeesQuery(
        array $config
    ) {
        $query =
            DB::table(
                $config['employee_table']
                    . ' as e'
            )
            ->join(
                $config['pic_table']
                    . ' as p',

                'p.'
                    . $config['pic_primary_key'],

                '=',

                'e.'
                    . $config['pic_column']
            )
            ->whereNotNull(
                'e.'
                    . $config['pic_column']
            )
            ->whereIn(
                DB::raw(
                    'LOWER(TRIM(CAST(e.'
                        . $this->quoteIdentifier(
                            $config['status_column']
                        )
                        . ' AS CHAR)))'
                ),
                $this->activeStatusValues(
                    $config
                )
            );


        $emptyValues =
            collect(
                config(
                    'employee.empty_values',
                    []
                )
            )
            ->filter(
                fn($value): bool =>
                is_string(
                    $value
                )
                    && trim(
                        $value
                    ) !== ''
            )
            ->map(
                fn(string $value): string =>
                strtoupper(
                    trim(
                        $value
                    )
                )
            )
            ->values()
            ->all();


        $query->where(
            function (
                $missingFieldsQuery
            ) use (
                $emptyValues
            ): void {
                foreach (
                    $this->editableFields()
                    as $field
                ) {
                    $qualifiedColumn =
                        'e.'
                        . $field;

                    $sqlColumn =
                        'e.'
                        . $this->quoteIdentifier(
                            $field
                        );


                    $missingFieldsQuery
                        ->orWhere(
                            function (
                                $fieldQuery
                            ) use (
                                $qualifiedColumn,
                                $sqlColumn,
                                $emptyValues
                            ): void {
                                $fieldQuery
                                    ->whereNull(
                                        $qualifiedColumn
                                    )
                                    ->orWhereRaw(
                                        'TRIM(CAST('
                                            . $sqlColumn
                                            . ' AS CHAR)) = ?',
                                        ['']
                                    );


                                if (
                                    $emptyValues
                                    !== []
                                ) {
                                    $placeholders =
                                        implode(
                                            ', ',
                                            array_fill(
                                                0,
                                                count(
                                                    $emptyValues
                                                ),
                                                '?'
                                            )
                                        );


                                    $fieldQuery
                                        ->orWhereRaw(
                                            'UPPER(TRIM(CAST('
                                                . $sqlColumn
                                                . ' AS CHAR))) IN ('
                                                . $placeholders
                                                . ')',
                                            $emptyValues
                                        );
                                }
                            }
                        );
                }
            }
        );


        return $query;
    }


    /*
     * ============================================================
     * FORM GROUPS
     * ============================================================
     */
    private function formGroups(): array
    {
        $fieldMeta =
            config(
                'employee.hr_field_meta',
                []
            );

        $groupMeta =
            config(
                'employee.hr_field_groups',
                []
            );


        return collect(
            $this->editableFields()
        )
            ->map(
                function (
                    string $field
                ) use (
                    $fieldMeta
                ): array {
                    $meta =
                        $fieldMeta[$field] ?? [];


                    return array_merge(
                        [
                            'name' =>
                            $field,

                            'label' =>
                            str($field)
                                ->replace(
                                    '_',
                                    ' '
                                )
                                ->title()
                                ->toString(),

                            'group' =>
                            'employment',

                            'type' =>
                            'text',

                            'placeholder' =>
                            null,

                            'inputmode' =>
                            null,

                            'options' =>
                            [],

                            'rows' =>
                            3,
                        ],
                        $meta,
                        [
                            'name' =>
                            $field,
                        ]
                    );
                }
            )
            ->groupBy(
                'group'
            )
            ->map(
                function (
                    $fields,
                    string $groupKey
                ) use (
                    $groupMeta
                ): array {
                    return [
                        'key' =>
                        $groupKey,

                        'title' =>
                        $groupMeta[$groupKey]['title']
                            ?? str(
                                $groupKey
                            )
                            ->headline()
                            ->toString(),

                        'description' =>
                        $groupMeta[$groupKey]['description']
                            ?? null,

                        'fields' =>
                        $fields
                            ->values()
                            ->all(),
                    ];
                }
            )
            ->values()
            ->all();
    }


    /*
     * ============================================================
     * NORMALIZE EMPLOYMENT DATA
     * ============================================================
     */
    private function normalizeEmployeeForForm(
        array $employee
    ): array {
        $emptyValues =
            config(
                'employee.empty_values',
                []
            );


        return collect(
            $this->editableFields()
        )
            ->mapWithKeys(
                function (
                    string $field
                ) use (
                    $employee,
                    $emptyValues
                ): array {
                    $value =
                        $employee[$field] ?? '';


                    if (
                        is_string(
                            $value
                        )
                    ) {
                        $value =
                            trim(
                                $value
                            );
                    }


                    if (
                        $value === null
                        || $value === ''
                        || in_array(
                            $value,
                            $emptyValues,
                            true
                        )
                    ) {
                        return [
                            $field => '',
                        ];
                    }


                    if (
                        in_array(
                            $field,
                            $this->booleanFields(),
                            true
                        )
                    ) {
                        return [
                            $field =>
                            in_array(
                                $value,
                                [
                                    1,
                                    '1',
                                    true,
                                    'Yes',
                                    'yes',
                                ],
                                true
                            )
                                ? 'Yes'
                                : 'No',
                        ];
                    }


                    if (
                        $field ===
                        'date_of_join'
                    ) {
                        return [
                            $field =>
                            substr(
                                (string) $value,
                                0,
                                10
                            ),
                        ];
                    }


                    return [
                        $field =>
                        $value,
                    ];
                }
            )
            ->all();
    }


    private function normalizePayload(
        array $payload
    ): array {
        $emptyValues =
            config(
                'employee.empty_values',
                []
            );


        return collect(
            $payload
        )
            ->map(
                function (
                    $value
                ) use (
                    $emptyValues
                ) {
                    if (
                        !is_string(
                            $value
                        )
                    ) {
                        return $value;
                    }


                    $value =
                        trim(
                            $value
                        );


                    return in_array(
                        $value,
                        $emptyValues,
                        true
                    )
                        ? null
                        : $value;
                }
            )
            ->all();
    }


    /*
     * ============================================================
     * EMPLOYMENT FIELD CONFIG
     * ============================================================
     */
    private function editableFields(): array
    {
        return collect(
            config(
                'employee.hr_required_fields',
                []
            )
        )
            ->filter(
                fn($field): bool =>
                is_string(
                    $field
                )
                    && $field
                    !== 'employee_id'
            )
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
        return array_merge(
            [
                'employee_table' =>
                'employee_details',

                'status_column' =>
                'active',

                'active_status_values' =>
                ['1'],

                'pic_column' =>
                'pic_nip',

                'pic_table' =>
                'pics',

                'pic_primary_key' =>
                'nip',

                'pic_name_column' =>
                'name',
            ],
            config(
                'employee.hr_employee_list',
                []
            )
        );
    }


    private function activeStatusValues(
        array $config
    ): array {
        $values =
            collect(
                $config['active_status_values']
                    ?? ['Active']
            )
            ->filter(
                fn($value): bool =>
                is_string(
                    $value
                )
                    && trim(
                        $value
                    ) !== ''
            )
            ->map(
                fn(
                    string $value
                ): string =>
                strtolower(
                    trim(
                        $value
                    )
                )
            )
            ->unique()
            ->values()
            ->all();


        return $values !== []
            ? $values
            : ['active'];
    }


    private function quoteIdentifier(
        string $identifier
    ): string {
        return '`'
            . str_replace(
                '`',
                '``',
                $identifier
            )
            . '`';
    }


    private function organizationConfig(): array
    {
        return array_merge(
            [
                'business_unit_table' =>
                'business_units',

                'business_unit_code_column' =>
                'business_unit_code',

                'business_unit_name_column' =>
                'business_unit_name',

                'department_table' =>
                'departments',

                'department_code_column' =>
                'department_code',

                'department_name_column' =>
                'department_name',

                'relation_table' =>
                'business_unit_and_departments',

                'relation_business_unit_column' =>
                'business_unit_code',

                'relation_department_column' =>
                'department_code',
            ],
            config(
                'employee.hr_organization_relations',
                []
            )
        );
    }
}
