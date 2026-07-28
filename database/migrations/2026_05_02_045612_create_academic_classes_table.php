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
        Schema::create('academic_classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->string('name');
            $table->integer('numeric')->nullable();
            $table->boolean('has_section')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['institution_id', 'deleted_at'], 'academic_classes_institution_idx');

            $table->unique(
                ['institution_id', 'name', 'deleted_at'],
                'academic_classes_institution_name_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academic_classes');
    }
};
