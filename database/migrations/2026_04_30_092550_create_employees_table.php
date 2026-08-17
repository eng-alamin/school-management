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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
 
            // Academic Details
            $table->string('employee_id');
            $table->date('joining_date')->nullable();
            $table->foreignId('designation_id')->nullable()->constrained('employee_designations')->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('employee_departments')->nullOnDelete();
            $table->string('qualification')->nullable();
            $table->text('experience_detail')->nullable();
            $table->string('total_experience')->nullable();
            $table->text('comments')->nullable();
 
            // Employee Details
            $table->string('name');
            $table->string('gender')->nullable();
            $table->string('blood_group')->nullable();
            $table->date('dob')->nullable();
            $table->string('religion')->nullable();
            $table->string('mobile')->nullable()->index();
            $table->string('email')->nullable()->index();
 
            $table->text('present_address')->nullable();
            $table->text('permanent_address')->nullable();
            $table->string('photo')->nullable();
            $table->enum('status', ['active', 'inactive', 'resigned', 'terminated'])->default('active')->index();
 
            // Bank Info
            $table->string('bank_name')->nullable();
            $table->string('holder_name')->nullable();
            $table->string('bank_branch')->nullable();
            $table->string('bank_address')->nullable();
            $table->string('ifsc_code')->nullable();
            $table->string('account_no')->nullable();
 
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['user_id', 'deleted_at'], 'employees_user_id_deleted_at_unique');
            $table->unique(['institution_id', 'employee_id', 'deleted_at'],'employees_institution_employee_id_unique');
            $table->index(['institution_id', 'status', 'deleted_at'], 'employees_institution_status_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
