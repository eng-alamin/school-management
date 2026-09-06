<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grievances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();

            // Only set when a guardian files on behalf of a specific child;
            // null for self-filed student/teacher grievances.
            $table->foreignId('student_id')->nullable()->constrained()->nullOnDelete();

            $table->enum('complainant_type', ['student', 'guardian', 'teacher']);
            $table->foreignId('complainant_id')->constrained('users')->cascadeOnDelete();

            // Identity is ALWAYS stored (abuse prevention) — this flag only
            // controls whether the name is hidden in the Ministry review UI.
            $table->boolean('is_anonymous')->default(false);

            $table->string('category');
            $table->string('subject');
            $table->text('description');

            $table->enum('status', ['submitted', 'under_review', 'resolved', 'rejected', 'escalated'])->default('submitted');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->text('resolution_note')->nullable();
            $table->dateTime('resolved_at')->nullable();

            // Link to compliance_violations — set when Ministry escalates a
            // grievance into a formal violation record.
            $table->foreignId('violation_id')->nullable()->constrained('compliance_violations')->nullOnDelete();

            $table->timestamps();

            $table->index(['institution_id', 'status'], 'grievances_institution_status_idx');
            $table->index(['complainant_id', 'complainant_type'], 'grievances_complainant_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grievances');
    }
};