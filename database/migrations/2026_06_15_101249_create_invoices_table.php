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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->nullable()->constrained()->onDelete('cascade'); // registration এর সময় institution থাকে না
            $table->string('type')->default('registration'); // 'registration' বা 'billing'
            $table->string('invoice_no')->nullable(); // registration এর invoice_no লাগে না
            $table->unsignedTinyInteger('month')->nullable();
            $table->unsignedSmallInteger('year')->nullable();
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('payable_amount', 10, 2)->default(0);
            $table->enum('status', ['free','pending', 'paid', 'overdue', 'failed'])->default('pending');
            $table->date('due_date')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->string('transaction_id')->nullable(); // SSLCommerz tran_id, দুই ধরনের payment-এর জন্যই
            $table->string('val_id')->nullable();
            $table->string('payment_method')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['invoice_no', 'deleted_at'], 'invoices_invoice_no_unique');
            $table->unique(['transaction_id', 'deleted_at'], 'invoices_transaction_id_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
