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
        Schema::create('salary_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
 
            // Basic Info
            $table->string('name'); // e.g. "Senior Teacher Package 2026"
            $table->string('salary_grade'); // Keeping string for now (MVP); can migrate to FK later
            $table->decimal('basic_salary', 12, 2)->default(0);
            $table->decimal('overtime_rate', 12, 2)->nullable();
 
            // Cached Salary Summary (auto-synced via SalaryTemplateChildObserver, never set manually)
            $table->decimal('total_allowance', 12, 2)->default(0);
            $table->decimal('total_deduction', 12, 2)->default(0);
            $table->decimal('net_salary', 12, 2)->default(0);
 
            // Status control
            $table->boolean('is_active')->default(true);
 
            $table->timestamps();
            $table->softDeletes();
 
            // Prevent duplicate template name per institution
            $table->unique(['institution_id', 'name', 'deleted_at'], 'salary_templates_institution_name_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_templates');
    }
};
