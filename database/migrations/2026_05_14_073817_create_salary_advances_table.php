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
        Schema::create('salary_advances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
 
            // ── Advance amount details ─────────────────────────────
            $table->decimal('amount', 12, 2);              // total advance given
            $table->decimal('remaining_amount', 12, 2);     // auto-synced via Observer from repayments
            $table->decimal('installment_amount', 12, 2)->nullable();
            // ↑ null = deduct full remaining_amount in the very next payment (lump sum)
            //   set  = deduct this fixed amount each month until remaining_amount hits 0
 
            $table->date('advance_date');
            $table->text('reason')->nullable();
 
            // active   = still has remaining_amount > 0, will be auto-deducted from future payments
            // settled  = remaining_amount reached 0, fully repaid
            $table->enum('status', ['active', 'settled'])->default('active');
 
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
 
            $table->timestamps();
            $table->softDeletes();
 
            $table->index(['institution_id', 'employee_id', 'status'], 'index_salary_advances_lookup');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_advances');
    }
};
