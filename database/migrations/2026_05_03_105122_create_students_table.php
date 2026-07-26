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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
 
            // Academic
            $table->foreignId('session_id')->nullable()->constrained('academic_sessions')->nullOnDelete();
            $table->string('student_id');
            $table->string('registration_no')->nullable();
            $table->string('roll_no')->nullable();
            $table->date('admission_date')->nullable();
            $table->foreignId('class_id')->nullable()->constrained('academic_classes')->nullOnDelete();
            $table->foreignId('section_id')->nullable()->constrained('academic_sections')->nullOnDelete();
            $table->foreignId('group_id')->nullable()->constrained('academic_groups')->nullOnDelete();
 
            // Student Info
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
 
            // Previous institution
            $table->text('previous_institution')->nullable();
            $table->text('qualification')->nullable();
            $table->text('remarks')->nullable();
 
            $table->enum('status', ['active', 'inactive', 'graduated', 'transferred', 'dropped_out'])->default('active')->index();
 
            $table->timestamps();
            $table->softDeletes();
 
            $table->unique(['user_id', 'deleted_at'], 'students_user_id_deleted_at_unique');
 
            $table->unique(['institution_id', 'student_id', 'deleted_at'], 'students_institution_student_id_unique');
            $table->unique(['institution_id', 'registration_no', 'deleted_at'], 'students_institution_registration_no_unique');
 
            $table->index(['institution_id', 'status', 'deleted_at'], 'students_institution_status_idx');
            $table->index(['institution_id', 'class_id', 'section_id', 'group_id', 'deleted_at'], 'students_institution_class_section_group_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
