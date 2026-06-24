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
        Schema::create('salary_payments', function (Blueprint $table) {
               $table->id();
            $table->foreignId('institution_id')->constrained('institutions')->cascadeOnDelete();
                $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
                $table->foreignId('salary_assign_id')->nullable()->constrained('salary_assigns')->nullOnDelete();
                $table->date('month');

                $table->decimal('basic_salary', 15, 2)->default(0);
                $table->decimal('total_allowance', 15, 2)->default(0);
                $table->decimal('total_deduction', 15, 2)->default(0);

                $table->decimal('overtime_hour', 8, 2)->default(0);
                $table->decimal('overtime_rate', 15, 2)->default(0);
                $table->decimal('overtime_amount', 15, 2)->default(0);

                $table->decimal('gross_salary', 15, 2)->default(0);
                $table->decimal('net_salary', 15, 2)->default(0);

                $table->date('payment_date')->nullable();
                $table->enum('payment_method', ['cash','bank','cheque','mobile_banking'])->default('cash');
                $table->foreignId('account_id')->nullable()->constrained('office_accounts')->nullOnDelete();
                $table->string('transaction_id')->nullable();
                $table->enum('status', ['paid','unpaid','partial'])->default('unpaid');
                $table->text('note')->nullable();
                
                $table->foreignId('paid_by')->nullable()->constrained('users')->nullOnDelete();

                $table->timestamps();
                $table->softDeletes();

                $table->unique(
                    ['employee_id', 'month'],
                    'employee_salary_month_unique'
                );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_payments');
    }
};
