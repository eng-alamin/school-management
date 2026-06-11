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
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('fee_group_id')->constrained('fee_groups')->cascadeOnDelete();
            $table->foreignId('fee_group_item_id')->nullable()->constrained('fee_group_items')->nullOnDelete();
            $table->enum('fine_type', ['fixed', 'percentage'])->default('fixed');
            $table->decimal('fine_value', 15, 2);
            $table->enum('late_fee_frequency', ['one_time','daily','weekly','monthly','yearly'])->default('one_time');
            $table->boolean('status')->default(true);
            $table->timestamps();
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
