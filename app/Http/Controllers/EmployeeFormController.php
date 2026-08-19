<?php

namespace App\Http\Controllers;

use App\Models\employee_details;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;


class EmployeeFormController extends Controller
{
    /**
     * Menampilkan form dan melakukan sync berdasarkan employee_id.
     */
    public function show(Request $request)
    {
        $user = $this->authenticatedEmployee($request);

        $existingDocuments = [
            'ijazah_filename' =>
            filled($user->ijazah_filename)
                && Storage::disk('public')->exists(
                    $user->ijazah_filename
                ),

            'ktp_filename' =>
            filled($user->ktp_filename)
                && Storage::disk('public')->exists(
                    $user->ktp_filename
                ),

            'kk_filename' =>
            filled($user->kk_filename)
                && Storage::disk('public')->exists(
                    $user->kk_filename
                ),

            'npwp_filename' =>
            filled($user->npwp_filename)
                && Storage::disk('public')->exists(
                    $user->npwp_filename
                ),
        ];

        return view('pages.form', [
            'title' => 'Employee Form',
            'user' => $user,
            'existingDocuments' => $existingDocuments,
            'employeeRequiredFields' => config(
                'employee.employee_required_fields',
                []
            ),
        ]);
    }

    /**
     * Menyimpan data yang menjadi tanggung jawab employee.
     */
    public function submit(Request $request)
    {
        /*
     * Employee selalu diambil dari akun yang login.
     */
        $employee = $this->authenticatedEmployee($request);

        /*
     * Jangan percaya employee_id dari browser.
     */
        $request->merge([
            'employee_id' => $employee->employee_id,
            'ktp_postal_code' =>
            $request->input('current_postal_code'),
        ]);

        $employeeRequiredFields = config(
            'employee.employee_required_fields',
            []
        );

        $emptyValues = array_map(
            fn($value) => strtoupper(
                trim((string) $value)
            ),
            config('employee.empty_values', [])
        );

        $notPlaceholder = function (
            string $attribute,
            mixed $value,
            \Closure $fail
        ) use ($emptyValues): void {
            $normalizedValue = strtoupper(
                trim((string) $value)
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

        $documentIsMissing = function (
            string $field
        ) use ($employee): bool {
            $path = $employee->{$field};

            return blank($path)
                || !Storage::disk('public')->exists($path);
        };

        /*
     * Gunakan seluruh rules milikmu.
     */

        $fileFields = [
            'ktp_filename',
            'kk_filename',
            'ijazah_filename',
            'npwp_filename',
        ];

        $rules = [
            /*
     * Employee ID tidak diambil dari input bebas.
     * Nilainya sudah di-merge dari akun yang login.
     */
            'employee_id' => [
                'required',
                'string',
                'max:20',
                'exists:employee_details,employee_id',
            ],

            /*
     * ==========================================================
     * Personal information
     * ==========================================================
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
                    'Christianity',
                    'Catholicism',
                    'Hinduism',
                    'Buddhism',
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

            'mother_full_name' => [
                'string',
                'max:100',
            ],

            /*
     * ==========================================================
     * Contact and emergency contact
     * ==========================================================
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
     * ==========================================================
     * Current/domicile address
     * ==========================================================
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
     * ==========================================================
     * KTP information
     * ==========================================================
     */
            'ktp_number' => [
                'string',
                'digits:16',
            ],

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

            /*
     * Nilai field ini diambil dari current_postal_code
     * melalui $request->merge().
     */
            'ktp_postal_code' => [
                'string',
                'digits:5',
            ],

            /*
     * ==========================================================
     * Tax information
     * ==========================================================
     */
            'tax_number' => [
                'string',
                'max:30',
                'regex:/^[0-9.\-]+$/',
            ],

            /*
     * ==========================================================
     * Education information
     * ==========================================================
     */
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
                'between:1900,2100',
                'gte:education_from',
            ],

            /*
     * ==========================================================
     * Employee documents
     * ==========================================================
     */
            'ktp_filename' => [
                Rule::requiredIf(
                    fn(): bool =>
                    $documentIsMissing('ktp_filename')
                ),
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:5120',
            ],

            'kk_filename' => [
                Rule::requiredIf(
                    fn(): bool =>
                    $documentIsMissing('kk_filename')
                ),
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:5120',
            ],

            'ijazah_filename' => [
                Rule::requiredIf(
                    fn(): bool =>
                    $documentIsMissing('ijazah_filename')
                ),
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:5120',
            ],

            'npwp_filename' => [
                Rule::requiredIf(
                    fn(): bool =>
                    $documentIsMissing('npwp_filename')
                ),
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:5120',
            ],
        ];

        $unknownFields = array_diff(
            $employeeRequiredFields,
            array_keys($rules)
        );

        if ($unknownFields !== []) {
            throw new \RuntimeException(
                'Validation belum tersedia untuk field: '
                    . implode(', ', $unknownFields)
            );
        }

        foreach ($rules as $field => &$fieldRules) {
            /*
     * Employee ID sudah required secara permanen.
     */
            if ($field === 'employee_id') {
                continue;
            }

            /*
     * File menggunakan Rule::requiredIf().
     */
            if (in_array($field, $fileFields, true)) {
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

                $fieldRules[] = $notPlaceholder;
            } else {
                /*
         * Jika nanti suatu field dihapus dari daftar wajib,
         * nilai kosong tetap diperbolehkan.
         */
                array_unshift(
                    $fieldRules,
                    'nullable'
                );
            }
        }

        unset($fieldRules);

        $validated = $request->validate(
            $rules,
            [
                'employee_id.exists' =>
                'Employee ID tidak ditemukan.',

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
                "Tahun mulai pendidikan harus berada antara 1800 dan 2100.",

                'education_end.integer' =>
                'Tahun selesai pendidikan harus berupa angka.',

                'education_end.digits' =>
                'Tahun selesai pendidikan harus terdiri dari 4 angka.',

                'education_end.between' =>
                "Tahun selesai pendidikan harus berada antara 1800 dan 2100.",

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

                'ktp_filename.mimes' =>
                'File KTP harus berformat PDF, JPG, JPEG, atau PNG.',

                'kk_filename.mimes' =>
                'File Kartu Keluarga harus berformat PDF, JPG, JPEG, atau PNG.',

                'ijazah_filename.mimes' =>
                'File ijazah harus berformat PDF, JPG, JPEG, atau PNG.',

                'npwp_filename.mimes' =>
                'File NPWP harus berformat PDF, JPG, JPEG, atau PNG.',

                'ktp_filename.max' =>
                'Ukuran file KTP maksimal 5 MB.',

                'kk_filename.max' =>
                'Ukuran file Kartu Keluarga maksimal 5 MB.',

                'ijazah_filename.max' =>
                'Ukuran file ijazah maksimal 5 MB.',

                'npwp_filename.max' =>
                'Ukuran file NPWP maksimal 5 MB.',
            ],
            [
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
            ]
        );

        /*
     * Simpan seluruh field text yang tervalidasi,
     * termasuk field opsional yang diisi employee.
     */
        $employeeData = Arr::except(
            $validated,
            array_merge(
                $fileFields,
                ['employee_id']
            )
        );

        $employee->fill($employeeData);

        $fileFieldMap = [
            'ktp_filename' => [
                'prefix' => 'ktp',
                'folder' => 'ktp',
            ],
            'kk_filename' => [
                'prefix' => 'kk',
                'folder' => 'kk',
            ],
            'ijazah_filename' => [
                'prefix' => 'ijazah',
                'folder' => 'ijazah',
            ],
            'npwp_filename' => [
                'prefix' => 'npwp',
                'folder' => 'npwp',
            ],
        ];

        $safeEmployeeId = preg_replace(
            '/[^A-Za-z0-9_-]/',
            '_',
            $employee->employee_id
        );

        foreach ($fileFieldMap as $field => $config) {
            if (!$request->hasFile($field)) {
                continue;
            }

            $file = $request->file($field);

            $extension = strtolower(
                $file->getClientOriginalExtension()
            );

            $fileName = $safeEmployeeId. '.' . $extension;

            $directory =
                'employee-documents/'
                . $config['folder'];

            $oldPath = $employee->{$field};

            /*
         * Simpan file baru lebih dahulu.
         */
            $newPath = $file->storeAs(
                $directory,
                $fileName,
                'public'
            );

            $employee->{$field} = $newPath;

            /*
         * Hapus file lama jika nama/path-nya berbeda.
         */
            if (
                filled($oldPath)
                && $oldPath !== $newPath
                && Storage::disk('public')->exists($oldPath)
            ) {
                Storage::disk('public')->delete($oldPath);
            }
        }

        /*
     * Simpan data terlebih dahulu tanpa langsung menandai complete.
     */
        $employee->save();
        $employee->refresh();

        $recognizedAsComplete = employee_details::query()
            ->whereKey($employee->id)
            ->employeeDataComplete()
            ->exists();

        if (!$recognizedAsComplete) {
            if (!is_null($employee->employee_completed_at)) {
                $employee->update([
                    'employee_completed_at' => null,
                ]);
            }

            return redirect()
                ->route('employee.form')
                ->with(
                    'error',
                    'Data tersimpan, tetapi masih ada field employee yang belum dianggap lengkap.'
                );
        }

        if (is_null($employee->employee_completed_at)) {
            $employee->update([
                'employee_completed_at' => now(),
            ]);
        }

        return redirect()
            ->route('employee.form')
            ->with(
                'success',
                'Employee data has been successfully processed. Please leave this page or press the log out button. You can log in again later to update your data if needed.'
            );
    }

    public function saveStep(Request $request)
    {
        $step = (int) $request->input('step');

        if ($step < 1 || $step > 5) {
            return response()->json([
                'message' => 'Step tidak valid.',
            ], 422);
        }

        /*
     * Employee harus selalu berasal dari
     * akun yang sedang login.
     */
        $employee =
            $this->authenticatedEmployee($request);

        $rules =
            $this->rulesForStep(
                $step,
                $employee
            );

        $validated =
            $request->validate($rules);

        /*
     * Khusus Step 3.
     */
        if ($step === 3) {
            $validated['ktp_postal_code'] =
                $validated['current_postal_code']
                ?? null;
        }

        $employee->update($validated);

        $employee->refresh();

        return response()->json([
            'success' => true,

            'message' =>
            "Step {$step} berhasil disimpan.",

            'step' =>
            $step,

            'completion' =>
            $employee->profile_completion,
        ]);
    }

    private function rulesForStep(
        int $step,
        employee_details $employee
    ): array {
        return match ($step) {
            1 => $this->stepOneRules(),

            2 => $this->stepTwoRules(),

            3 => $this->stepThreeRules(),

            4 => $this->stepFourRules(),

            5 => $this->stepFiveRules(),

            6 => $this->stepSixRules(
                $employee
            ),

            default => [],
        };
    }

    private function stepOneRules(): array
    {
        return [
            'ktp_number' => [
                'required',
                'string',
                'max:16 ',
            ],
        ];
    }

    private function stepTwoRules(): array
    {
        return [

            'display_name' => [
                'required',
                'string',
                'max:100',
            ],

            'gender' => [
                'required',
                Rule::in([
                    'Male',
                    'Female',
                ]),
            ],

            'birth_place' => [
                'required',
                'string',
                'max:100',
            ],

            'date_of_birth' => [
                'required',
                'date',
            ],

            'religion' => [
                'required',
                'string',
                'max:50',
            ],

            'marital_status' => [
                'required',
                'string',
                'max:50',
            ],

            'blood_group' => [
                'nullable',
                'string',
                'max:10',
            ],

            'nationality' => [
                'required',
                'string',
                'max:50',
            ],
        ];
    }

    private function stepThreeRules(): array
    {
        return [

            'primary_email' => [
                'required',
                'email',
                'max:191',
            ],

            'primary_contact_number' => [
                'required',
                'string',
                'max:30',
                'regex:/^[0-9+\-\s()]+$/',
            ],

            'emergency_full_name' => [
                'nullable',
                'string',
                'max:100',
            ],

            'emergency_contact_no' => [
                'nullable',
                'regex:/^[0-9+\-\s()]+$/',
                'string',
                'max:30',
            ],

            'current_address' => [
                'required',
                'string',
            ],

            'current_provinsi' => [
                'nullable',
                'string',
                'max:100',
            ],

            'current_kotamadya_kabupaten' => [
                'nullable',
                'string',
                'max:100',
            ],

            'current_kecamatan' => [
                'nullable',
                'string',
                'max:100',
            ],

            'current_kelurahan' => [
                'nullable',
                'string',
                'max:100',
            ],

            'current_postal_code' => [
                'nullable',
                'string',
                'digits:5',
            ],

            'ktp_address' => [
                'nullable',
                'string',
            ],

            'ktp_provinsi' => [
                'nullable',
                'string',
                'max:100',
            ],

            'ktp_kotamadya_kabupaten' => [
                'nullable',
                'string',
                'max:100',
            ],

            'ktp_kecamatan' => [
                'nullable',
                'string',
                'max:100',
            ],

            'ktp_kelurahan' => [
                'nullable',
                'string',
                'max:100',
            ],
        ];
    }

    private function stepFourRules(): array
    {
        return [

            'mother_full_name' => [
                'nullable',
                'string',
                'max:100',
            ],

            'education_level' => [
                'nullable',
                'string',
                'max:30',
            ],

            'major' => [
                'nullable',
                'string',
                'max:100',
            ],

            'institution_name' => [
                'nullable',
                'string',
                'max:150',
            ],

            /*
         * education_from / education_end hanya menyimpan TAHUN.
         */
            'education_from' => [
                'nullable',
                'integer',
                'digits:4',
                'between:1800,2100',
            ],

            'education_end' => [
                'nullable',
                'integer',
                'digits:4',
                'between:1800,2100',
                'gte:education_from',
            ],
        ];
    }

    private function stepFiveRules(): array
    {
        return [

            'tax_number' => [
                'nullable',
                'string',
                'max:30',
                'regex:/^[0-9.\-]+$/',
            ],
        ];
    }

    private function stepSixRules(
        employee_details $employee
    ): array {
        $rules = [
            'employee_id' => [
                'required',
                'string',
                'exists:employee_details,employee_id',
            ],
        ];

        $fileFields = [
            'ijazah_filename',
            'ktp_filename',
            'kk_filename',
            'npwp_filename',
        ];

        $employeeRequiredFields =
            config(
                'employee.employee_required_fields',
                []
            );

        foreach ($fileFields as $field) {
            $isRequired = in_array(
                $field,
                $employeeRequiredFields,
                true
            );

            $hasExistingFile =
                filled($employee->{$field});

            $rules[$field] = [
                Rule::requiredIf(
                    $isRequired &&
                        !$hasExistingFile
                ),

                'nullable',
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:5120',
            ];
        }

        return $rules;
    }

    private function storeDocuments(
        Request $request,
        employee_details $employee,
        array $validated
    ): array {
        $fileFields = [
            'ijazah_filename' => 'ijazah',
            'ktp_filename' => 'ktp',
            'kk_filename' => 'kk',
            'npwp_filename' => 'npwp',
        ];

        foreach (
            $fileFields as $field => $prefix
        ) {
            if (!$request->hasFile($field)) {
                /*
             * Jangan overwrite file lama dengan NULL.
             */
                unset($validated[$field]);

                continue;
            }

            $file =
                $request->file($field);

            $extension =
                strtolower(
                    $file->getClientOriginalExtension()
                );

            $filename =
                "{$employee->employee_id}.{$extension}";

            $path = $file->storeAs(
                'employee-documents',
                $filename,
                'public'
            );

            $validated[$field] =
                $path;
        }

        return $validated;
    }

    private function authenticatedEmployee(
        Request $request
    ): employee_details {
        $employeeId = $request->user()->employee_id;

        abort_if(
            blank($employeeId),
            403,
            'Akun tidak terhubung dengan data employee.'
        );

        return employee_details::query()
            ->where('employee_id', $employeeId)
            ->firstOrFail();
    }
}
