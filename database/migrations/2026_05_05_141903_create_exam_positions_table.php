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
        Schema::create('exam_positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('exam_setup_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_class_assign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();

            $table->decimal('total_obtained', 8, 2)->default(0);
            $table->decimal('total_full_mark', 8, 2)->default(0);
            $table->decimal('percentage', 5, 2)->default(0);
            $table->enum('result', ['pass', 'fail', 'absent', 'incomplete'])->default('incomplete');

            $table->decimal('gpa', 3, 2)->nullable();
            $table->string('grade')->nullable();
            $table->string('rank_scope')->default('class'); // class / section / group

            $table->unsignedInteger('previous_position')->nullable();
            $table->unsignedInteger('position')->nullable();

            $table->text('principal_comment')->nullable();
            $table->text('teacher_comment')->nullable();

            $table->timestamps();

            $table->index(['exam_setup_id', 'academic_class_assign_id']);
            $table->index(['institution_id', 'academic_session_id']);
            $table->index(['student_id']);

            $table->unique(
                ['exam_setup_id', 'academic_class_assign_id', 'student_id'],
                'exam_position_exam_class_student_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_positions');
    }
};
