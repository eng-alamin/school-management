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
        Schema::create('leave_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('leave_category_id')->constrained('leave_categories')->onDelete('cascade');
            $table->year('year');
            $table->integer('total_days');                         // মোট বরাদ্দ দিন
            $table->integer('used_days')->default(0);              // ব্যবহৃত দিন
            $table->integer('remaining_days');                     // বাকি দিন
            $table->integer('carried_forward_days')->default(0);   // গত বছর থেকে আনা দিন
            $table->timestamps();
 
            $table->unique(['employee_id', 'leave_category_id', 'year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_balances');
    }
};
