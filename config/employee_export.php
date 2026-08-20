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
        'access_id' => 'Access id',
        'employee_id' => 'Employee ID',
        'display_name' => 'Full Name',
        'date_of_join' => 'Date of join (YYYY-MM-DD)',
        'primary_contact_number' => 'Primary Contact Number',
        'primary_email' => 'Primary Email',
        'company' => 'Company',
        'store_code' => 'Store Code',
        'base_cost_center' => 'Base Cost Center',
        'brand' => 'Brand',
        'bc_code' => 'BC Code',
        'employee_level_code' => 'Employee Level Code',
        'kep_sso_hash' => 'KEP SSO hash',
        'business_unit_org_element_1' => 'Business Unit(org_element_1)',
        'department_org_element_2' => 'Department(org_element_2)',
        'time_policy_code' => 'Time Policy Code',
        'shift_group_code' => 'Shift Group Code',
        'current_address' => 'Current Address',
        'current_provinsi' => 'Current Provinsi',
        'current_kotamadya_kabupaten' => 'Current Kotamadya Kabupaten',
        'current_kecamatan' => 'Current Kecamatan',
        'current_kelurahan' => 'Current Kelurahan',
        'current_postal_code' => 'Current Postal Code',
        'ktp_address' => 'KTP Address',
        'ktp_provinsi' => 'KTP Provinsi',
        'ktp_number' => 'KTP Number',
        'ktp_kotamadya_kabupaten' => 'KTP Kotamadya Kabupaten',
        'ktp_kecamatan' => 'KTP Kecamatan',
        'ktp_kelurahan' => 'KTP Kelurahan',
        'ktp_postal_code' => 'KTP Postal Code',
        'education_level' => 'Education Level',
        'institution_name' => 'Institution Name',
        'major' => 'Major',
        'education_from' => 'Education From',
        'education_end' => 'Education End',
        'emergency_full_name' => 'Emergency Full Name',
        'emergency_contact_no' => 'Emergency Contact No',
        'mother_full_name' => 'Mother Full Name',
        'tax_number' => 'Tax Number',
        'tax_fasilitas_code' => 'Tax Fasilitas Code',
        'tax_object_code_monthly_code' => 'Tax object code monthly Code',
        'tax_movement_recalculate' => 'Tax Movement Recalculate',
        'bpjs_jamsostek_contribution' => 'BPJS Jamsostek Contribution',
        'bpjs_kesehatan_number' => 'BPJS Kesehatan Number',
        'bpjs_ketenagakerjaan_number' => 'BPJS Ketenagakerjaan Number',
        'bpjs_pension_eligibility' => 'BPJS Pension Eligibility',
        'religion' => 'Religion',
        'birth_place' => 'Birth Place',
        'date_of_birth' => 'Date Of Birth',
        'marital_status' => 'Marital Status',
        'gender' => 'Gender',
        'blood_group' => 'Blood Group',
        'nationality' => 'Nationality',
        'pic_nip' => 'PIC NIP', 
        'residency_status' => 'Residency Status',
        'salary_matrix' => 'Salary Matrix',
        'bank_code' => 'Bank Code',
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
