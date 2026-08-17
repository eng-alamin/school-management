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
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 20); // e.g. MIR, UTR - used in ID generation, invoice prefix etc.
            $table->text('address')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();
 
            // Marks the branch every fresh user session defaults to for this institution
            $table->boolean('is_main')->default(false);
            $table->boolean('is_active')->default(true);
 
            $table->timestamps();
            $table->softDeletes();
            
            $table->unique(['institution_id', 'code', 'deleted_at'], 'branches_institution_code_unique');
            $table->unique(['institution_id', 'name', 'deleted_at'], 'branches_institution_name_unique');
            $table->index(['institution_id', 'is_active'], 'branches_institution_active_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branches');
    }
};
