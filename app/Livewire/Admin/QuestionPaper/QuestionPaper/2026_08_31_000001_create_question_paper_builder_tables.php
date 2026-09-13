<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The paper itself — meta fields mirror the builder's "Paper Details" panel.
        Schema::create('question_papers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('exam_id')->constrained('exam_setups');
            $table->foreignId('subject_id')->constrained('academic_subjects');
            $table->foreignId('academic_class_id')->nullable()->constrained('academic_classes');

            $table->string('institute_name')->nullable();  // metaInstitute
            $table->string('exam_name')->nullable();        // metaExam
            $table->string('class_label')->nullable();      // metaClass (free text, as builder allows)
            $table->string('subject_label')->nullable();    // metaSubject (free text, as builder allows)
            $table->decimal('full_marks', 6, 2)->default(0);
            $table->string('time_label')->nullable();       // e.g. "3 Hours"
            $table->enum('language', ['en', 'bn'])->default('bn');

            // Snapshot/lock: once true, this paper is frozen (no further edits)
            // so the exact content that was printed can never drift from what
            // forensic tracing later refers back to.
            $table->boolean('is_locked')->default(false);
            $table->timestamp('locked_at')->nullable();
            $table->foreignId('locked_by')->nullable()->constrained('users');

            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->index(['institution_id', 'branch_id', 'exam_id', 'subject_id'], 'qp_ins_bra_exa_sub_idx');
        });

        // Dynamic per-paper section headings + their Bangla label override.
        // Mirrors sectionNamesBn in the builder — user can type any heading
        // on a question (not limited to Objective/Subjective/Creative/Practical).
        Schema::create('question_paper_section_labels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_paper_id')->constrained()->cascadeOnDelete();
            $table->string('section_key');   // e.g. "Objective", "Trigonometry", "বীজগণিত"
            $table->string('label_bn')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['question_paper_id', 'section_key'], 'qpsl_pap_sec_idx');
        });

        // One row per question block in the builder.
        Schema::create('question_paper_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_paper_id')->constrained()->cascadeOnDelete();

            // e.g. MCQ_SINGLE, TRUE_FALSE, MATCHING, CREATIVE, VIVA ...
            $table->string('type', 40);
            // e.g. options | matching_pairs | stimulus_parts | plain
            $table->string('family', 20);

            // Free-typed section/chapter heading (drives grouping in preview).
            // Falls back to the type's default section (e.g. "Objective") if blank.
            $table->string('section_header')->nullable();

            $table->text('question_text')->nullable();  // used by options/plain families
            $table->text('stimulus_text')->nullable();  // used by stimulus_parts family (CQ, passage, etc.)
            $table->decimal('marks', 6, 2)->default(0);  // total marks (options/plain/matching); stimulus_parts sums its own parts

            $table->string('figure_path')->nullable();   // stored file (upload or drawn), replaces base64 dataURL
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->index(['question_paper_id', 'sort_order'], 'qpq_pap_sor_idx');
        });

        // family = "options" (MCQ, True/False, Yes/No, Assertion-Reason, ...)
        Schema::create('question_paper_question_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_paper_question_id');
            $table->foreign('question_paper_question_id', 'qpqo_question_fk')->references('id')->on('question_paper_questions')->cascadeOnDelete();
            // $table->foreignId('question_paper_question_id')->constrained()->cascadeOnDelete();
            $table->text('option_text');
            $table->boolean('is_correct')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // family = "matching_pairs" (Matching)
        Schema::create('question_paper_question_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_paper_question_id');
            $table->foreign('question_paper_question_id', 'qpqm_question_fk')->references('id')->on('question_paper_questions')->cascadeOnDelete();
            // $table->foreignId('question_paper_question_id')->constrained()->cascadeOnDelete();
            $table->text('left_text');
            $table->text('right_text');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // family = "stimulus_parts" (Creative/CQ, Case Study, Reading Comprehension, Passage)
        Schema::create('question_paper_question_parts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_paper_question_id');
            $table->foreign('question_paper_question_id', 'qpqp_question_fk')->references('id')->on('question_paper_questions')->cascadeOnDelete();
            // $table->foreignId('question_paper_question_id')->constrained()->cascadeOnDelete();
            $table->string('part_label', 10); // e.g. "ক", "খ", "a", "1"
            $table->text('part_text')->nullable();
            $table->decimal('marks', 6, 2)->default(0);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('question_paper_question_parts');
        Schema::dropIfExists('question_paper_question_matches');
        Schema::dropIfExists('question_paper_question_options');
        Schema::dropIfExists('question_paper_questions');
        Schema::dropIfExists('question_paper_section_labels');
        Schema::dropIfExists('question_papers');
    }
};
