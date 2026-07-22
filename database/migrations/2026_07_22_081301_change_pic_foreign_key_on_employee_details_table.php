<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_details', function (Blueprint $table) {
            $table->dropForeign([
                'pic_nip',
            ]);

            $table->foreign('pic_nip')
                ->references('nip')
                ->on('pics')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('employee_details', function (Blueprint $table) {
            $table->dropForeign([
                'pic_nip',
            ]);

            $table->foreign('pic_nip')
                ->references('nip')
                ->on('pics')
                ->cascadeOnDelete();
        });
    }
};
