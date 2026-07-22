<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('employee_details', function (Blueprint $table) {
            $table->id();
            $table->date('date_of_join')->nullable();
            $table->string('employee_id', 20)->unique();
            $table->string('access_id', 20)->nullable()->unique();
            $table->string('time_policy_code', 50)->nullable();
            $table->string('shift_group_code', 50)->nullable();
            $table->string('business_unit_org_element_1', 100)->nullable();
            $table->string('department_org_element_2', 100)->nullable();
            $table->string('pic_nip',100)->nullable();
            $table->string('emergency_full_name', 255)->nullable();
            $table->text('current_address')->nullable();
            $table->string('mother_full_name', 255)->nullable();
            $table->string('education_level', 30)->nullable();
            $table->string('primary_contact_number', 30)->nullable();
            $table->string('tax_number', 30)->nullable()->index();
            $table->string('emergency_contact_no', 30)->nullable();
            $table->string('current_provinsi', 255)->nullable();
            $table->string('education_from', 255)->nullable();
            $table->string('primary_email', 191)->index();
            $table->string('display_name', 255);
            $table->string('current_kotamadya_kabupaten', 255)->nullable();
            $table->string('education_end', 255)->nullable();
            $table->string('current_kecamatan', 255)->nullable();
            $table->string('major', 255)->nullable();
            $table->string('company', 255)->nullable()->index();
            $table->string('current_kelurahan', 250)->nullable();
            $table->string('institution_name', 150)->nullable();
            $table->string('tax_movement_recalculate', 255)->nullable();
            $table->string('residency_status', 50)->nullable();
            $table->string('religion', 50)->nullable();
            $table->string('current_postal_code', 50)->nullable();
            $table->string('birth_place', 255)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('bpjs_jamsostek_contribution', 255)->nullable();
            $table->string('bpjs_pension_eligibility', 255)->nullable();
            $table->string('tax_fasilitas_code', 255)->nullable();
            $table->string('marital_status', 50)->nullable();
            $table->text('tax_object_code_monthly_code')->nullable();
            $table->string('gender', 20)->nullable();
            $table->text('ktp_address')->nullable();
            $table->string('blood_group', 10)->nullable();
            $table->string('store_code', 30)->nullable()->index();
            $table->string('ktp_provinsi', 255)->nullable();
            $table->string('ktp_number', 25)->nullable()->index();
            $table->string('base_cost_center', 255)->nullable();
            $table->string('ktp_kotamadya_kabupaten', 255)->nullable();
            $table->string('bpjs_kesehatan_number', 30)->nullable()->index();
            $table->string('brand', 255)->nullable()->index();
            $table->string('ktp_kecamatan', 255)->nullable();
            $table->string('bpjs_ketenagakerjaan_number', 30)->nullable()->index();
            $table->string('bc_code', 30)->nullable();
            $table->string('ktp_kelurahan', 255)->nullable();
            $table->string('ktp_postal_code', 30)->nullable();
            $table->string('salary_matrix', 255)->nullable();
            $table->string('bank_code', 30)->nullable();
            $table->string('nationality', 50)->nullable();
            $table->string('kep_sso_hash', 50)->nullable();
            $table->string('bpa1_sifat_pemotongan_code', 50)->nullable();
            $table->string('ktp_filename', 255)->nullable();
            $table->string('kk_filename', 255)->nullable();
            $table->string('ijazah_filename', 255)->nullable();
            $table->string('npwp_filename', 255)->nullable();
            $table->timestamps();
            $table->foreign('business_unit_org_element_1')->references('business_unit_code')->on('business_units')->onDelete('cascade');
            $table->foreign('department_org_element_2')->references('department_code')->on('departments')->onDelete('cascade');
            $table->foreign('pic_nip')->references('nip')->on('pics')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_details');
    }
};
