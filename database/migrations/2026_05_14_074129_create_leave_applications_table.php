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
        Schema::create('leave_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->nullableMorphs('applicable');             // applicable_id + applicable_type
            $table->foreignId('leave_category_id')->constrained('leave_categories')->onDelete('restrict');
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('total_days');
            $table->enum('day_type', ['full_day', 'half_day'])->default('full_day');
            $table->string('reason');
            $table->text('description')->nullable();
            $table->string('document_path')->nullable();
            $table->string('contact_during_leave')->nullable();
            $table->string('handover_to')->nullable();

            // Approval Flow
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_note')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_applications');
    }
};
