<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inspection_checklist_items', function (Blueprint $table) {
            $table->id();
            $table->string('category');
            $table->string('criterion');
            $table->unsignedTinyInteger('max_score')->default(10);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'sort_order'], 'checklist_items_active_sort_idx');
        });

        Schema::create('inspections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->date('scheduled_at')->nullable();
            $table->dateTime('conducted_at')->nullable();
            $table->enum('status', ['scheduled', 'completed', 'cancelled'])->default('scheduled');
            $table->decimal('overall_score', 5, 2)->nullable(); // percentage, 0-100
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['institution_id', 'status'], 'inspections_institution_status_idx');
        });

        Schema::create('inspection_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inspection_id')->constrained()->cascadeOnDelete();
            $table->foreignId('checklist_item_id')->constrained('inspection_checklist_items')->cascadeOnDelete();
            $table->decimal('score', 5, 2);
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->unique(['inspection_id', 'checklist_item_id'], 'inspection_results_unique');
        });

        Schema::create('compliance_violations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('inspection_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('severity', ['minor', 'major', 'critical'])->default('minor');
            $table->text('description');
            $table->enum('status', ['open', 'resolved', 'escalated'])->default('open');
            $table->dateTime('resolved_at')->nullable();
            $table->text('resolution_note')->nullable();
            $table->foreignId('reported_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['institution_id', 'status'], 'violations_institution_status_idx');
            $table->index(['severity', 'status'], 'violations_severity_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compliance_violations');
        Schema::dropIfExists('inspection_results');
        Schema::dropIfExists('inspections');
        Schema::dropIfExists('inspection_checklist_items');
    }
};