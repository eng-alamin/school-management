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
        Schema::create('fee_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('fee_group_id')->constrained('fee_groups')->cascadeOnDelete();
            $table->foreignId('class_id')->nullable()->constrained('academic_classes')->nullOnDelete();
            $table->foreignId('section_id')->nullable()->constrained('academic_sections')->nullOnDelete();
            $table->date('allocated_date') ->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
            $table->unique(['student_id','fee_group_id','class_id','section_id'], 'fee_allocation_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fee_allocations');
    }
};
