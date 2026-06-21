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
            Schema::create('sms_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('school_id')->nullable()->constrained()->onDelete('cascade');
                $table->foreignId('sent_by')->nullable()->constrained('users')->onDelete('set null'); // কে পাঠালো (admin/system)
                $table->string('type')->default('individual'); // individual / bulk / attendance / fee_due / exam / notice / birthday
                $table->string('to');                          // যাকে SMS পাঠানো হয়েছে
                $table->text('message');                        // SMS এর content
                $table->string('status')->default('sent');      // sent / failed
                $table->text('response')->nullable();           // provider থেকে যা response আসে (debug এর জন্য)
                $table->decimal('cost', 8, 2)->default(0);       // balance deduction-এর জন্য
                $table->timestamps();
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sms_logs');
    }
};
