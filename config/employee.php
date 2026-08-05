<?php

return [

  /*
    |--------------------------------------------------------------------------
    | Kolom wajib yang diisi HR
    |--------------------------------------------------------------------------
    |
    | Daftar ini menjadi sumber tunggal untuk field form, sinkronisasi,
    | validasi, dan penyimpanan data HR.
    |
    */

  'hr_required_fields' => [
    'employee_id',
    'access_id',
    'company',
    'tax_movement_recalculate',
    'residency_status',
    'bpjs_jamsostek_contribution',
    'bpjs_pension_eligibility',
    'tax_fasilitas_code',
    'tax_object_code_monthly_code',
    'store_code',
    'base_cost_center',
    'bpjs_kesehatan_number',
    'brand',
    'bpjs_ketenagakerjaan_number',
    'bc_code',
    'salary_matrix',
    'bank_code',
    'kep_sso_hash',
    'bpa1_sifat_pemotongan_code',
    'shift_group_code',
    'time_policy_code',
    'date_of_join',
    'department_org_element_2',
    'business_unit_org_element_1',
  ],

  /*
    |--------------------------------------------------------------------------
    | Pengelompokan tampilan form HR
    |--------------------------------------------------------------------------
    */

  'hr_field_groups' => [
    'employment' => [
      'title' => 'Employment Information',
      'description' => 'Informasi utama penempatan dan status karyawan.',
    ],
    'organization' => [
      'title' => 'Organization & Work Policy',
      'description' => 'Unit organisasi, lokasi, shift, dan kebijakan waktu kerja.',
    ],
    'tax_bpjs' => [
      'title' => 'Tax & BPJS',
      'description' => 'Informasi pajak serta kepesertaan BPJS karyawan.',
    ],
    'payroll' => [
      'title' => 'Payroll & System Code',
      'description' => 'Kode payroll dan integrasi sistem yang dikelola HR.',
    ],
  ],

  /*
    |--------------------------------------------------------------------------
    | Metadata tampilan field HR
    |--------------------------------------------------------------------------
    |
    | Field yang ditampilkan tetap mengikuti hr_required_fields. Metadata ini
    | hanya mengatur label, grup, tipe input, placeholder, dan pilihan nilai.
    |
    */

  'hr_field_meta' => [
    'employee_id' => [
      'label' => 'Employee ID',
      'group' => 'employment',
      'type' => 'text',
      'placeholder' => 'Contoh: 6949',
    ],
    'access_id' => [
      'label' => 'Access ID',
      'group' => 'employment',
      'type' => 'text',
      'placeholder' => 'Contoh: 0000006949',
    ],
    'company' => [
      'label' => 'Company',
      'group' => 'employment',
      'type' => 'select',
      'options' => ['PT. ADI SPORT RETAILINDO', 'PT. KANMO GAYA ABADI', 'PT. KANMO MULTI GEMILANG', 'PT. KANMO RETAILINDO', 'PT. KANMO WESTON RETAILINDO', 'PT. MULTITREND INDO'],
    ],
    'date_of_join' => [
      'label' => 'Date of Join',
      'group' => 'employment',
      'type' => 'date',
    ],
    'store_code' => [
      'label' => 'Store Code',
      'group' => 'organization',
      'type' => 'text',
    ],
    'brand' => [
      'label' => 'Brand',
      'group' => 'organization',
      'type' => 'text',
    ],
    'base_cost_center' => [
      'label' => 'Base Cost Center',
      'group' => 'organization',
      'type' => 'text',
    ],
    'department_org_element_2' => [
      'label' => 'Department (Org Element 2)',
      'group' => 'organization',
      'type' => 'select',
      'options' => [],
      'placeholder' => 'Select a department',
    ],

    'business_unit_org_element_1' => [
      'label' => 'Business Unit (Org Element 1)',
      'group' => 'organization',
      'type' => 'select',
      'options' => [],
      'placeholder' => 'Select a business unit',
    ],
    'shift_group_code' => [
      'label' => 'Shift Group Code',
      'group' => 'organization',
      'type' => 'text',
    ],
    'time_policy_code' => [
      'label' => 'Time Policy Code',
      'group' => 'organization',
      'type' => 'text',
    ],
    'tax_movement_recalculate' => [
      'label' => 'Tax Movement Recalculate',
      'group' => 'tax_bpjs',
      'type' => 'select',
      'options' => ['Yes', 'No'],
    ],
    'residency_status' => [
      'label' => 'Residency Status',
      'group' => 'tax_bpjs',
      'type' => 'text',
      'placeholder' => 'Contoh: Resident/Local',
    ],
    'bpjs_jamsostek_contribution' => [
      'label' => 'BPJS Jamsostek Contribution',
      'group' => 'tax_bpjs',
      'type' => 'select',
      'options' => ['Yes', 'No'],
    ],
    'bpjs_pension_eligibility' => [
      'label' => 'BPJS Pension Eligibility',
      'group' => 'tax_bpjs',
      'type' => 'select',
      'options' => ['Yes', 'No'],
    ],
    'tax_fasilitas_code' => [
      'label' => 'Tax Fasilitas Code',
      'group' => 'tax_bpjs',
      'type' => 'text',
    ],
    'tax_object_code_monthly_code' => [
      'label' => 'Tax Object Code Monthly',
      'group' => 'tax_bpjs',
      'type' => 'textarea',
      'rows' => 3,
    ],
    'bpjs_kesehatan_number' => [
      'label' => 'BPJS Kesehatan Number',
      'group' => 'tax_bpjs',
      'type' => 'text',
      'inputmode' => 'numeric',
    ],
    'bpjs_ketenagakerjaan_number' => [
      'label' => 'BPJS Ketenagakerjaan Number',
      'group' => 'tax_bpjs',
      'type' => 'text',
      'inputmode' => 'numeric',
    ],
    'salary_matrix' => [
      'label' => 'Salary Matrix',
      'group' => 'payroll',
      'type' => 'text',
    ],
    'bank_code' => [
      'label' => 'Bank Code',
      'group' => 'payroll',
      'type' => 'text',
    ],
    'bc_code' => [
      'label' => 'BC Code',
      'group' => 'payroll',
      'type' => 'text',
    ],
    'kep_sso_hash' => [
      'label' => 'KEP SSO Hash',
      'group' => 'payroll',
      'type' => 'text',
    ],
    'bpa1_sifat_pemotongan_code' => [
      'label' => 'BPA1 Sifat Pemotongan Code',
      'group' => 'payroll',
      'type' => 'textarea',
      'rows' => 3,
    ],
  ],

  /*
    |--------------------------------------------------------------------------
    | Konfigurasi daftar employee untuk HR
    |--------------------------------------------------------------------------
    |
    | Sesuaikan nama kolom di bawah dengan struktur database proyek.
    | Default menganggap PIC disimpan pada employee_details.pic_id,
    | nama PIC berasal dari users.name, dan status ada pada kolom status.
    |
    */

  'hr_employee_list' => [
    'employee_table' => 'employee_details',
    'status_column' => 'active',
    'active_status_values' => ['1'],
    'pic_column' => 'pic_nip',
    'pic_table' => 'pics',
    'pic_primary_key' => 'nip',
    'pic_name_column' => 'name',
  ],

  /*
|--------------------------------------------------------------------------
| Business Unit dan Department
|--------------------------------------------------------------------------
*/

  'hr_organization_relations' => [
    'business_unit_table' => 'business_units',
    'business_unit_code_column' => 'business_unit_code',
    'business_unit_name_column' => 'business_unit_name',

    'department_table' => 'departments',
    'department_code_column' => 'department_code',
    'department_name_column' => 'department_name',

    'relation_table' => 'business_unit_and_departments',
    'relation_business_unit_column' => 'business_unit_code',
    'relation_department_column' => 'department_code',
  ],

  /*
    |--------------------------------------------------------------------------
    | Kolom wajib yang diisi employee
    |--------------------------------------------------------------------------
    */

  'employee_required_fields' => [
    'emergency_full_name',
    'current_address',
    'mother_full_name',
    'education_level',
    'primary_contact_number',
    'tax_number',
    'emergency_contact_no',
    'current_provinsi',
    'primary_email',
    'display_name',
    'current_kotamadya_kabupaten',
    'major',
    'institution_name',
    'religion',
    'birth_place',
    'date_of_birth',
    'marital_status',
    'gender',
    'ktp_address',
    'blood_group',
    'ktp_number',
    'nationality',
    'current_postal_code',
    'current_kecamatan',
    'current_kelurahan',
    'education_from',
    'education_end',
    'ktp_provinsi',
    'ktp_kotamadya_kabupaten',
    'ktp_kecamatan',
    'ktp_kelurahan',
    'ktp_postal_code',
    'ktp_filename',
    'kk_filename',
    'ijazah_filename',
    'npwp_filename',
  ],

  /*
    |--------------------------------------------------------------------------
    | Nilai yang dianggap kosong
    |--------------------------------------------------------------------------
    */

  'empty_values' => [
    '-',
    '--',
    'N/A',
  ],
];
