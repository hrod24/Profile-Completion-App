<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Employee export columns
    |--------------------------------------------------------------------------
    |
    | The array order becomes the Excel column order.
    | The values are the exact headers from the supplied "Employee Details"
    | worksheet. PIC NIP is an application-specific column and is included
    | because it is not part of the excluded-column list.
    |
    */
    'columns' => [
        'employee_id' => 'Employee ID',
        'date_of_join' => 'Date of join (YYYY-MM-DD)',
        'employee_level_code' => 'Employee Level Code',
        'access_id' => 'Access id',
        'time_policy_code' => 'Time Policy Code',
        'shift_group_code' => 'Shift Group Code',
        'business_unit_org_element_1' => 'Business Unit(org_element_1)',
        'department_org_element_2' => 'Department(org_element_2)',
        'pic_nip' => 'PIC NIP',
        'emergency_full_name' => 'Emergency Full Name',
        'current_address' => 'Current Address',
        'mother_full_name' => 'Mother Full Name',
        'education_level' => 'Education Level',
        'primary_contact_number' => 'Primary Contact Number',
        'tax_number' => 'Tax Number',
        'emergency_contact_no' => 'Emergency Contact No',
        'current_provinsi' => 'Current Provinsi',
        'primary_email' => 'Primary Email',
        'display_name' => 'Display Name',
        'education_from' => 'Education From',
        'current_kotamadya_kabupaten' => 'Current Kotamadya Kabupaten',
        'education_end' => 'Education End',
        'current_kecamatan' => 'Current Kecamatan',
        'major' => 'Major',
        'company' => 'Company',
        'current_kelurahan' => 'Current Kelurahan',
        'institution_name' => 'Institution Name',
        'tax_movement_recalculate' => 'Tax Movement Recalculate',
        'residency_status' => 'Residency Status',
        'religion' => 'Religion',
        'current_postal_code' => 'Current Postal Code',
        'birth_place' => 'Birth Place',
        'date_of_birth' => 'Date Of Birth',
        'bpjs_jamsostek_contribution' => 'BPJS Jamsostek Contribution',
        'bpjs_pension_eligibility' => 'BPJS Pension Eligibility',
        'tax_fasilitas_code' => 'Tax Fasilitas Code',
        'marital_status' => 'Marital Status',
        'tax_object_code_monthly_code' => 'Tax object code monthly Code',
        'gender' => 'Gender',
        'ktp_address' => 'KTP Address',
        'blood_group' => 'Blood Group',
        'store_code' => 'Store Code',
        'ktp_provinsi' => 'KTP Provinsi',
        'ktp_number' => 'KTP Number',
        'base_cost_center' => 'Base Cost Center',
        'ktp_kotamadya_kabupaten' => 'KTP Kotamadya Kabupaten',
        'bpjs_kesehatan_number' => 'BPJS Kesehatan Number',
        'brand' => 'Brand',
        'ktp_kecamatan' => 'KTP Kecamatan',
        'bpjs_ketenagakerjaan_number' => 'BPJS Ketenagakerjaan Number',
        'bc_code' => 'BC Code',
        'ktp_kelurahan' => 'KTP Kelurahan',
        'ktp_postal_code' => 'KTP Postal Code',
        'salary_matrix' => 'Salary Matrix',
        'bank_code' => 'Bank Code',
        'nationality' => 'Nationality',
        'kep_sso_hash' => 'KEP SSO hash',
        'bpa1_sifat_pemotongan_code' => 'BPA1 Sifat Pemotongan Code',
    ],

    /*
    | These fields are intentionally never exported.
    | They are kept here as documentation and a safety check.
    */
    'excluded_columns' => [
        'id',
        'last_seen_import_batch',
        'inactive_at',
        'active',
        'ktp_filename',
        'kk_filename',
        'npwp_filename',
        'ijazah_filename',
        'employee_completed_at',
        'created_at',
        'updated_at',
    ],
];
