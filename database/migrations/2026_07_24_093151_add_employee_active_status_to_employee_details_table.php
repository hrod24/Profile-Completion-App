<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_details', function (Blueprint $table) {
            $table->boolean('is_active')
                ->default(true)
                ->index()
                ->after('employee_id');

            /*
             * Menandai employee terlihat pada batch import mana.
             * Ini lebih aman daripada menyimpan ribuan Employee ID
             * untuk query WHERE NOT IN.
             */
            $table->uuid('last_seen_import_batch')
                ->nullable()
                ->index()
                ->after('is_active');

            $table->timestamp('inactive_at')
                ->nullable()
                ->index()
                ->after('last_seen_import_batch');
        });
    }

    public function down(): void
    {
        Schema::table('employee_details', function (Blueprint $table) {
            $table->dropColumn([
                'is_active',
                'last_seen_import_batch',
                'inactive_at',
            ]);
        });
    }
};
