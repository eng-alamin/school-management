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
        Schema::create('admissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('guardian_user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->boolean('is_new')->default(false);
            // ── Applicant (student) info — mirrors students table fields ──
            $table->string('application_no')->nullable();
            $table->string('applicant_name');
            $table->string('gender')->nullable();
            $table->string('blood_group')->nullable();
            $table->date('dob')->nullable();
            $table->string('religion')->nullable();
            $table->string('mobile')->nullable();
            $table->string('email')->nullable();
            $table->text('present_address')->nullable();
            $table->text('permanent_address')->nullable();
            $table->string('photo')->nullable();
            $table->text('previous_institution')->nullable();
            $table->text('qualification')->nullable();
 
            // ── Applied for ──
            $table->foreignId('applied_session_id')->nullable()->constrained('academic_sessions')->nullOnDelete();
            $table->foreignId('applied_class_id')->nullable()->constrained('academic_classes')->nullOnDelete();
            $table->unsignedInteger('admission_test_score')->nullable();
            $table->string('document_path')->nullable();
 
            // ── Guardian info (raw intake — official record lives in guardians table after approval) ──
            $table->string('guardian_name')->nullable();
            $table->string('guardian_relation')->nullable();
            $table->string('father_name')->nullable();
            $table->string('mother_name')->nullable();
            $table->string('guardian_occupation')->nullable();
            $table->string('guardian_mobile')->nullable();
            $table->string('guardian_email')->nullable();
            $table->text('guardian_address')->nullable();
 
            // ── Review / status ──
            $table->enum('status', ['pending', 'approved', 'rejected', 'waiting_list'])
                ->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
 
            // ── Traceability: which student record this admission became ──
            $table->foreignId('student_id')->nullable()->constrained('students')->nullOnDelete();
 
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['institution_id', 'application_no', 'deleted_at'], 'admissions_institution_application_no_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admissions');
    }
};
