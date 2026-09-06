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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->date('date')->nullable();
            // type control
            $table->enum('type', ['student', 'employee', 'exam']);

            // polymorphic (who is attending)
            $table->unsignedBigInteger('attendable_id');
            $table->string('attendable_type'); // Student / Employee

            // optional context
            $table->foreignId('class_id')->nullable()->constrained('academic_classes')->nullOnDelete();
            $table->foreignId('section_id')->nullable()->constrained('academic_sections')->nullOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained('academic_subjects')->nullOnDelete();
            $table->foreignId('exam_id')->nullable()->constrained('exam_setups')->nullOnDelete();

            // attendance data
            $table->enum('status', ['present', 'absent', 'late', 'leave']);

            $table->time('check_in')->nullable();
            $table->time('check_out')->nullable();

            $table->string('remarks')->nullable();
            $table->timestamps();

            $table->index(['attendable_type', 'attendable_id']);
            $table->index(['institution_id', 'date']);
            $table->index(['institution_id', 'class_id', 'section_id', 'date']);
            $table->index(['institution_id', 'subject_id', 'date'], 'attendances_institution_subject_date_idx');
            $table->index(['institution_id', 'exam_id', 'date'], 'attendances_institution_exam_date_idx');

            $table->unique(
                ['institution_id', 'attendable_type', 'attendable_id', 'date', 'type'], 'attendances_person_date_type_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
