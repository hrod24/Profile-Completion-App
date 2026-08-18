<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'employee_import_batches',
            function (Blueprint $table): void {
                $table->uuid('id')->primary();

                $table->string('file_path');

                $table->unsignedInteger('total_rows')
                    ->default(0);

                $table->unsignedInteger('next_row')
                    ->default(2);

                $table->unsignedInteger('processed')
                    ->default(0);

                $table->unsignedInteger('inserted')
                    ->default(0);

                $table->unsignedInteger('updated')
                    ->default(0);

                $table->unsignedInteger('skipped')
                    ->default(0);

                $table->unsignedInteger('deactivated')
                    ->default(0);

                $table->string('status')
                    ->default('pending');

                $table->text('error_message')
                    ->nullable();

                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'employee_import_batches'
        );
    }
};
