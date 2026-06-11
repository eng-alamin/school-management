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
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
                $table->date('date')->nullable();
                // type control
                $table->enum('type', ['student', 'employee', 'exam']);

                // polymorphic (who is attending)
                $table->unsignedBigInteger('attendable_id');
                $table->string('attendable_type'); // Student / Employee

                // optional context
                $table->foreignId('class_id')->nullable();
                $table->foreignId('section_id')->nullable();
                $table->foreignId('subject_id')->nullable();
                $table->foreignId('exam_id')->nullable();

                // attendance data
                $table->enum('status', ['present', 'absent', 'late', 'leave']);

                $table->time('check_in')->nullable();
                $table->time('check_out')->nullable();

                $table->string('remarks')->nullable();
            $table->timestamps();
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
