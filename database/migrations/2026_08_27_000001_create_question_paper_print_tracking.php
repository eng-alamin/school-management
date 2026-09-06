<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Who is allowed to print which paper, in which time window
        Schema::create('question_paper_print_authorizations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // the single authorized printer
            $table->foreignId('exam_id')->constrained('exam_setups');
            $table->foreignId('subject_id')->constrained('academic_subjects');
            $table->dateTime('valid_from');
            $table->dateTime('valid_until');
            $table->boolean('is_revoked')->default(false);
            $table->timestamps();

            $table->index(['institution_id', 'branch_id', 'exam_id', 'subject_id'], 'qppa_ins_bra_exa_sub_idx');
        });

        // One row per ACTUAL print event — this is the forensic ledger
        Schema::create('question_paper_print_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('exam_id')->constrained('exam_setups');
            $table->foreignId('subject_id')->constrained('academic_subjects');
            $table->foreignId('printed_by')->constrained('users');
            $table->foreignId('print_authorization_id')->constrained('question_paper_print_authorizations');

            // The core forensic identifier — this exact string is what gets
            // encoded into spacing/pattern/QR inside the printed PDF.
            $table->string('watermark_code', 64)->unique();
            $table->json('question_order')->nullable();
            $table->unsignedBigInteger('shuffle_seed')->nullable();

            $table->unsignedSmallInteger('copy_count')->default(1);
            $table->string('ip_address', 45)->nullable();
            $table->string('device_fingerprint')->nullable();
            $table->timestamp('printed_at');
            $table->timestamps();

            $table->index('watermark_code');
        });

        // Optional: if a leaked copy is ever found & decoded, log the incident
        Schema::create('question_paper_leak_incidents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('print_log_id')->nullable()->constrained('question_paper_print_logs');
            $table->string('decoded_watermark_code', 64)->nullable();
            $table->text('notes')->nullable();
            $table->string('evidence_file_path')->nullable();
            $table->foreignId('reported_by')->nullable()->constrained('users');
            $table->timestamp('reported_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('question_paper_leak_incidents');
        Schema::dropIfExists('question_paper_print_logs');
        Schema::dropIfExists('question_paper_print_authorizations');
    }
};
