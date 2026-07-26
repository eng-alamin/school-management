<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('academic_class_assigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('academic_classes')->cascadeOnDelete();
            $table->foreignId('section_id')->nullable()->constrained('academic_sections')->nullOnDelete();
            $table->timestamps();
        });

        // The generated column and the unique index that depends on it must be
        // added AFTER the table physically exists. Running these statements
        // inside the Schema::create() closure fails because the table has not
        // been created yet at the point the closure runs.
        DB::statement("
            ALTER TABLE academic_class_assigns
            ADD COLUMN section_id_for_unique BIGINT UNSIGNED
            GENERATED ALWAYS AS (COALESCE(section_id, 0)) STORED
        ");

        Schema::table('academic_class_assigns', function (Blueprint $table) {
            $table->unique(
                ['institution_id', 'class_id', 'section_id_for_unique'],
                'academic_class_assigns_institution_class_section_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academic_class_assigns');
    }
};
