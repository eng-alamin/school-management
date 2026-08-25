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
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('class_id')->constrained('academic_classes')->cascadeOnDelete();
            $table->foreignId('section_id')->nullable()->constrained('academic_sections')->nullOnDelete();
            $table->timestamps();

        });

        // PostgreSQL এ UNSIGNED টাইপ নেই, তাই driver অনুযায়ী column type আলাদা করা হচ্ছে।
        $driver = DB::connection()->getDriverName();
        $columnType = $driver === 'pgsql' ? 'BIGINT' : 'BIGINT UNSIGNED';

        DB::statement("
            ALTER TABLE academic_class_assigns
            ADD COLUMN section_id_for_unique {$columnType}
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