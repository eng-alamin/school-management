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
        Schema::create('academic_class_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('session_id')->constrained('academic_sessions')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('academic_classes')->cascadeOnDelete();
            $table->foreignId('section_id')->nullable()->constrained('academic_sections')->nullOnDelete();
            $table->string('day');
            $table->json('data')->nullable();
            $table->timestamps();
        });

        DB::statement("
            ALTER TABLE academic_class_schedules
            ADD COLUMN section_id_for_unique BIGINT UNSIGNED
            GENERATED ALWAYS AS (COALESCE(section_id, 0)) STORED
        ");

        Schema::table('academic_class_schedules', function (Blueprint $table) {
            $table->unique(
                ['institution_id', 'branch_id', 'session_id', 'class_id', 'section_id_for_unique', 'day'],
                'ac_cl_sc_ins_bra_see_cla_sec_day_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academic_class_schedules');
    }
};
