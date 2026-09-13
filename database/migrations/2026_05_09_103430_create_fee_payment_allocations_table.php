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
        Schema::create('fee_payment_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fee_payment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fee_invoice_item_id')->constrained()->cascadeOnDelete();
            $table->decimal('allocated_amount', 15, 2);
            $table->timestamps();
 
            // একটা payment একই invoice item-এ দুইবার আলাদা row হিসেবে allocate হতে পারবে না
            $table->unique(['fee_payment_id', 'fee_invoice_item_id'], 
            'fee_payment_allocations_pay_inv_unique');

            $table->index(['institution_id', 'branch_id'], 'fpa_ins_bra_idx');
            $table->index(['fee_invoice_item_id'], 'fpa_item_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fee_payment_allocations');
    }
};
