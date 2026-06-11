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
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            
            // Academic Details
            $table->date('joining_date')->nullable();
            $table->foreignId('designation_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->string('qualification')->nullable();
            $table->text('experience_detail')->nullable();
            $table->string('total_experience')->nullable();

            // Employee Details
            $table->string('name');
            $table->string('gender')->nullable();
            $table->string('blood_group')->nullable();
            $table->date('dob')->nullable();
            $table->string('religion')->nullable();
            $table->string('mobile')->nullable();
            $table->string('email')->nullable();
            $table->text('present_address')->nullable();
            $table->text('permanent_address')->nullable();
            $table->string('photo')->nullable();

            // Bank Info
            $table->string('bank_name')->nullable();
            $table->string('holder_name')->nullable();
            $table->string('bank_branch')->nullable();
            $table->string('bank_address')->nullable();
            $table->string('ifsc_code')->nullable();
            $table->string('account_no')->nullable();

            $table->timestamps();
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
