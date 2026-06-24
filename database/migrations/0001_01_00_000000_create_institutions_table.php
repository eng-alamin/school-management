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
        Schema::create('institutions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type')->nullable();
            $table->string('eiin')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('city')->nullable();
            $table->text('address')->nullable();
            $table->string('language')->default('en');        // 'Bangla' না, locale code ভালো
            $table->string('timezone')->default('Asia/Dhaka');
            $table->json('weekends')->nullable();
            $table->enum('unique_roll', ['class_wise', 'section_wise', 'disabled'])->default('class_wise');
            $table->boolean('teacher_restricted')->default(false);
            $table->string('academic_year')->nullable();

            // Currency
            $table->string('currency', 20)->default('BDT');
            $table->string('currency_symbol', 10)->default('৳');
            $table->string('currency_format')->default('1,00,000.00'); // ✏️ format টা বদলে দিলাম
            $table->string('symbol_position')->default('prefix');

            // Registration
            $table->boolean('enable_registration_prefix')->default(false);
            $table->string('institution_code_prefix')->nullable();
            $table->unsignedBigInteger('register_start_from')->default(1);
            $table->unsignedInteger('register_no_digit')->default(4);

            // Fees
            $table->boolean('offline_payment_enabled')->default(true);
            $table->unsignedInteger('due_days')->default(30);
            $table->boolean('due_fees_calculation_with_fine')->default(false);

            // Auto login
            $table->boolean('auto_generate_student_login')->default(false);
            $table->boolean('auto_generate_guardian_login')->default(false);

            // Logos
            $table->string('system_logo')->nullable();
            $table->string('text_logo')->nullable();
            $table->string('print_logo')->nullable();
            $table->string('report_logo')->nullable();

            $table->boolean('status')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('institutions');
    }
};
