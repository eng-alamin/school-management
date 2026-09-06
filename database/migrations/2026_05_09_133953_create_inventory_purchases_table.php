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
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained('inventory_suppliers')->nullOnDelete();
            $table->foreignId('store_id')->nullable()->constrained('inventory_stores')->nullOnDelete();
            $table->string('bill_no');
            $table->enum('purchase_status', ['pending','ordered','completed','received','cancelled'])->default('pending');
            $table->date('date');
            $table->decimal('net_total', 15, 2)->default(0);
            $table->text('remarks')->nullable();
            
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['institution_id', 'bill_no', 'deleted_at'], 'inventory_purchases_institution_bill_no_unique');
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
