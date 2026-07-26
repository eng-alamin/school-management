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
        Schema::create('fee_setups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('academic_classes')->cascadeOnDelete();
            $table->foreignId('fee_type_id')->constrained('fee_types')->cascadeOnDelete();
            $table->decimal('amount', 10, 2)->default(0);
            $table->enum('frequency', ['monthly', 'yearly', 'one_time'])->default('monthly');   
            $table->unsignedTinyInteger('billing_month')->nullable(); // yearly হলে কোন মাসে (1-12), one_time হলে null
            $table->boolean('status')->default(true);
            $table->timestamps();
            $table->softDeletes();
 
            $table->unique(['institution_id', 'class_id', 'fee_type_id', 'deleted_at'], 'unique_fee_setup');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fee_setups');
    }
};
