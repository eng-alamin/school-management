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
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->integer('numeric')->nullable();
            $table->boolean('has_section')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['institution_id', 'branch_id', 'deleted_at'], 'academic_classes_ins_bra_del_idx');
            $table->unique(['institution_id', 'branch_id', 'name', 'deleted_at'], 'academic_classes_ins_bra_name_del_unique');
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
