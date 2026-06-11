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
        Schema::create('salary_assigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            // ── Foreign keys ──────────────────────────────────────
            $table->string('role');
            $table->foreignId('designation_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('salary_template_id')->constrained()->cascadeOnDelete();
 
            // ── Salary snapshot (copied from template at assign time) ──
            $table->string('salary_grade')->nullable();
            $table->decimal('basic_salary',    12, 2)->default(0);
            $table->decimal('overtime_rate',   10, 2)->default(0);
            $table->decimal('total_allowance', 12, 2)->default(0);
            $table->decimal('total_deduction', 12, 2)->default(0);
            $table->decimal('gross_salary',    12, 2)->default(0); // basic + allowance
            $table->decimal('net_salary',      12, 2)->default(0); // gross - deduction
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_assigns');
    }
};
