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
        Schema::create('inventory_purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained('inventory_suppliers')->cascadeOnDelete();
            $table->foreignId('store_id')->constrained('inventory_stores')->cascadeOnDelete();
            $table->string('bill_no')->unique();
            $table->enum('purchase_status', ['pending','ordered','completed','received','cancelled'])->default('pending');
            $table->date('date');
            $table->decimal('net_total', 15, 2)->default(0);
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_purchases');
    }
};
