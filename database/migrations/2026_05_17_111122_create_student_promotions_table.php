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
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();

            $table->foreignId('from_session_id')->constrained('academic_sessions')->cascadeOnDelete();
            $table->foreignId('to_session_id')->constrained('academic_sessions')->cascadeOnDelete();

            $table->foreignId('from_class_id')->constrained('academic_classes')->cascadeOnDelete();
            $table->foreignId('to_class_id')->constrained('academic_classes')->cascadeOnDelete();

            $table->foreignId('from_section_id')->constrained('academic_sections')->cascadeOnDelete();
            $table->foreignId('to_section_id')->constrained('academic_sections')->cascadeOnDelete();

            $table->string('from_roll')->nullable();
            $table->string('to_roll')->nullable();

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
