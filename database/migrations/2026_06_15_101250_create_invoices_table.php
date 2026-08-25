<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
            $table->foreignId('institution_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type')->default('registration'); // 'registration' বা 'billing'
            $table->string('invoice_no')->nullable(); // registration এর invoice_no লাগে না
            $table->unsignedTinyInteger('month')->nullable();
            $table->unsignedSmallInteger('year')->nullable();
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('payable_amount', 10, 2)->default(0);
            $table->enum('status', ['free', 'pending', 'paid', 'overdue', 'failed'])->default('pending');
            $table->date('due_date')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->string('transaction_id')->nullable(); // SSLCommerz tran_id, দুই ধরনের payment-এর জন্যই
            $table->string('val_id')->nullable();
            $table->string('payment_method')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(
                ['institution_id', 'type', 'status', 'year', 'month'],
                'invoices_institution_type_status_year_month_index'
            );
        });

        // MySQL এ শুধু VIRTUAL সাপোর্ট করে, PostgreSQL এ শুধু STORED সাপোর্ট করে (PG 12+)।
        // তাই driver অনুযায়ী আলাদা keyword বসাতে হচ্ছে।
        $driver = DB::connection()->getDriverName();
        $generatedKeyword = $driver === 'pgsql' ? 'STORED' : 'VIRTUAL';

        DB::statement(<<<SQL
            ALTER TABLE invoices
            ADD COLUMN invoice_no_active VARCHAR(255)
            GENERATED ALWAYS AS (CASE WHEN deleted_at IS NULL THEN invoice_no ELSE NULL END) {$generatedKeyword}
        SQL);

        DB::statement(<<<SQL
            ALTER TABLE invoices
            ADD COLUMN transaction_id_active VARCHAR(255)
            GENERATED ALWAYS AS (CASE WHEN deleted_at IS NULL THEN transaction_id ELSE NULL END) {$generatedKeyword}
        SQL);

        Schema::table('invoices', function (Blueprint $table) {
            $table->unique('invoice_no_active', 'invoices_invoice_no_active_unique');
            $table->unique('transaction_id_active', 'invoices_transaction_id_active_unique');
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