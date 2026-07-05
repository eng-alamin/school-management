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
        Schema::create('fee_fines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fee_group_item_id')->constrained()->cascadeOnDelete();
            $table->enum('fine_type', ['fixed', 'percentage'])->default('fixed');
            $table->decimal('fine_value', 15, 2);
            $table->enum('late_fee_frequency', ['one_time','daily','weekly','monthly','yearly'])->default('one_time');
            $table->boolean('status')->default(true);
            $table->timestamps();

            $table->unique(['institution_id', 'fee_group_item_id'], 'unique_fee_fine');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fee_fines');
    }
};
