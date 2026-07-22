<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_details', function (Blueprint $table) {
            $table->timestamp('employee_completed_at')
                ->nullable()
                ->index();
        });
    }

    public function down(): void
    {
        Schema::table('employee_details', function (Blueprint $table) {
            $table->dropColumn('employee_completed_at');
        });
    }
};
