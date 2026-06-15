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
        Schema::create('homeworks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            
            // Academic
            $table->foreignId('class_id')->constrained('academic_classes')->cascadeOnDelete();
            $table->foreignId('section_id')->nullable()->constrained('academic_sections')->nullOnDelete();
            $table->foreignId('subject_id')->constrained('academic_subjects')->cascadeOnDelete();

            // Teacher
            $table->foreignId('teacher_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->string('title');
            $table->longText('description');

            // Dates
            $table->date('homework_date');
            $table->date('submission_date');

            // Publish Schedule
            $table->boolean('published_later')->default(false);
            $table->dateTime('schedule_date')->nullable();

            // Attachment
            $table->string('attachment')->nullable();

            // Notification
            $table->boolean('send_sms')->default(false);

            // Status
            $table->enum('status', ['draft', 'published', 'closed'])->default('published');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('homeworks');
    }
};
