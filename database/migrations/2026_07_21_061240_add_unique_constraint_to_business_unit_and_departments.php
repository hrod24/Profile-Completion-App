<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(
            'business_unit_and_departments',
            function (Blueprint $table) {
                $table->unique(
                    [
                        'business_unit_code',
                        'department_code',
                    ],
                    'business_unit_department_unique'
                );
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'business_unit_and_departments',
            function (Blueprint $table) {
                $table->dropUnique(
                    'business_unit_department_unique'
                );
            }
        );
    }
};
