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
        Schema::create('salary_advance_repayments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('salary_advance_id')->constrained()->cascadeOnDelete();
 
            // Which month's SalaryPayment this deduction came from.
            // Nullable so a repayment can (rarely) be recorded manually without
            // being tied to a specific payment run.
            $table->foreignId('salary_payment_id')->nullable()->constrained()->nullOnDelete();
 
            $table->decimal('amount', 12, 2);
            $table->date('deducted_date');
 
            $table->timestamps();
 
            // One advance can only be deducted once per payment (prevents double-deduction
            // if the same month's payment is somehow processed twice).
            $table->unique(['salary_advance_id', 'salary_payment_id'], 'unique_advance_repayment_per_payment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_advance_repayments');
    }
};
