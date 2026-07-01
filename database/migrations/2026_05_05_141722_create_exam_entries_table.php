<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('exam_setup_id')->constrained()->cascadeOnDelete();
            $table->foreignId('exam_setup_detail_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();

            $table->boolean('is_absent')->default(false);

            $table->decimal('practical_obtained', 8, 2)->nullable();
            $table->decimal('written_obtained', 8, 2)->nullable();
            $table->decimal('mcq_obtained', 8, 2)->nullable();
            $table->decimal('total_obtained', 8, 2)->nullable();

            $table->string('grade', 10)->nullable();
            $table->text('remarks')->nullable();

            $table->timestamps();

            $table->unique(
                ['exam_setup_detail_id', 'student_id'],
                'exam_entry_subject_student_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_entries');
    }
};