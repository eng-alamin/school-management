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
        Schema::create('exam_setup_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_setup_id')->constrained('exam_setups')->cascadeOnDelete();
            $table->foreignId('academic_class_assign_detail_id')->constrained('academic_class_assign_details')->cascadeOnDelete();
            $table->decimal('full_mark', 8, 2)->default(100);
            $table->decimal('pass_mark', 8, 2)->default(33);
            $table->decimal('written_mark', 8, 2)->default(0);
            $table->decimal('mcq_mark', 8, 2)->default(0);
            $table->decimal('practical_mark', 8, 2)->default(0);
            $table->integer('serial')->default(1);
            $table->timestamps();

            $table->unique(
                ['exam_setup_id', 'academic_class_assign_detail_id'],
                'exam_setup_subject_unique'
            );

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_setup_details');
    }
};
