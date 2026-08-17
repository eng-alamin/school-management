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
        Schema::create('institutions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type')->nullable();
            $table->string('medium')->default('bangla_medium');
            $table->string('eiin')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('division')->nullable();
            $table->string('district')->nullable();
            $table->string('city')->nullable();
            $table->text('address')->nullable();
            $table->json('weekends')->nullable();
            $table->enum('unique_roll', ['class_wise', 'section_wise', 'disabled'])->default('class_wise');
            $table->string('academic_year')->nullable();

            // Registration
            $table->boolean('enable_registration_prefix')->default(false);
            $table->string('registration_code_prefix')->nullable();
            $table->unsignedBigInteger('registration_start_from')->default(1);
            $table->unsignedTinyInteger('registration_digit_length')->default(6);

            // Student ID
            $table->boolean('enable_student_id_prefix')->default(false);
            $table->string('student_id_code_prefix')->nullable();
            $table->unsignedBigInteger('student_id_start_from')->default(1);
            $table->unsignedTinyInteger('student_id_digit_length')->default(6);

            // Employee ID
            $table->boolean('enable_employee_id_prefix')->default(false);
            $table->string('employee_id_code_prefix')->nullable();
            $table->unsignedBigInteger('employee_id_start_from')->default(1);
            $table->unsignedTinyInteger('employee_id_digit_length')->default(6);

            // Fees
            $table->unsignedInteger('due_days')->default(30);
            $table->boolean('due_fees_calculation_with_fine')->default(false);

            // Logos
            $table->string('system_logo')->nullable();
            $table->string('text_logo')->nullable();
            $table->string('print_logo')->nullable();
            $table->string('report_logo')->nullable();

            $table->json('setup_progress')->nullable();
            $table->boolean('setup_completed')->default(false);

            // Status
            $table->boolean('status')->default(true);

            // Ministry Verification / Oversight
            $table->enum('verification_status', ['pending', 'verified', 'rejected', 'suspended'])->default('pending');
            $table->unsignedBigInteger('verified_by')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->text('verification_note')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['division', 'district']);
            $table->index(['verification_status'], 'institutions_verification_status_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('institutions');
    }
};