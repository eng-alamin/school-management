<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_schedule_substitutes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();

            $table->date('date');

            $table->foreignId('class_schedule_id')->constrained('academic_class_schedules')->cascadeOnDelete();
            $table->unsignedTinyInteger('period_index'); // data JSON array-er index

            $table->foreignId('class_id')->constrained('academic_classes')->cascadeOnDelete();
            $table->foreignId('section_id')->nullable()->constrained('academic_sections')->nullOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained('academic_subjects')->nullOnDelete();

            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();

            $table->foreignId('original_teacher_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('substitute_teacher_id')->nullable()->constrained('users')->nullOnDelete();

            $table->enum('status', ['pending', 'assigned', 'confirmed', 'cancelled'])->default('pending');

            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('remarks')->nullable();

            $table->timestamps();

            // ekই period, ekই date-e duibar substitute row toiri hote parbe na
            $table->unique(
                ['institution_id', 'class_schedule_id', 'period_index', 'date'],
                'class_sched_sub_unique'
            );

            $table->index(['institution_id', 'date']);
            $table->index(['original_teacher_id', 'date']);
            $table->index(['substitute_teacher_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_schedule_substitutes');
    }
};