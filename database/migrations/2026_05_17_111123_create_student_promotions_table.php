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
        Schema::create('student_promotions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();

            
            $table->foreignId('from_session_id')->constrained('academic_sessions')->cascadeOnDelete();
            $table->foreignId('to_session_id')->constrained('academic_sessions')->cascadeOnDelete();

            $table->foreignId('from_class_id')->nullable()->constrained('academic_classes')->nullOnDelete();
            $table->foreignId('to_class_id')->nullable()->constrained('academic_classes')->nullOnDelete();

            $table->foreignId('from_section_id')->nullable()->constrained('academic_sections')->nullOnDelete();
            $table->foreignId('to_section_id')->nullable()->constrained('academic_sections')->nullOnDelete();

            $table->foreignId('from_group_id')->nullable()->constrained('academic_groups')->nullOnDelete();
            $table->foreignId('to_group_id')->nullable()->constrained('academic_groups')->nullOnDelete();

            $table->string('from_roll_no')->nullable();
            $table->string('to_roll_no')->nullable();

            $table->boolean('carry_forward_due')->default(false);
            $table->boolean('is_alumni')->default(false);

            $table->foreignId('promoted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('promoted_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_promotions');
    }
};
