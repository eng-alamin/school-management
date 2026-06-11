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
        Schema::create('inventory_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->unique();
            $table->foreignId('category_id')->constrained('inventory_categories')->cascadeOnDelete();
            $table->foreignId('purchase_unit_id')->constrained('inventory_units')->cascadeOnDelete();
            $table->foreignId('sales_unit_id')->constrained('inventory_units')->cascadeOnDelete();
            $table->decimal('unit_ratio', 15, 2)->default(1);
            $table->decimal('purchase_price', 15, 2)->default(0);
            $table->decimal('sales_price', 15, 2)->default(0);
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_products');
    }
};
