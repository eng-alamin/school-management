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
        Schema::create('academic_teacher_assigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('academic_classes')->cascadeOnDelete();
            $table->foreignId('section_id')->nullable()->constrained('academic_sections')->nullOnDelete();
            $table->foreignId('teacher_id')->constrained('employees')->cascadeOnDelete();
            $table->json('subjects')->nullable();
            $table->timestamps();

            $table->unique(
                ['class_id', 'section_id', 'teacher_id'],
                'teacher_class_section_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academic_teacher_assigns');
    }
};
