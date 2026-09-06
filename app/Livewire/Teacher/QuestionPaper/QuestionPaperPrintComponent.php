<?php

namespace App\Livewire\Teacher\QuestionPaper;

use App\Models\Branch;
use App\Models\QuestionPaperPrintAuthorization;
use App\Models\QuestionPaper;
use App\Models\QuestionPaperQuestion;
use App\Services\QuestionOrderShuffleService;
use App\Services\QuestionPaperPdfGenerator;
use App\Services\QuestionPaperWatermarkService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class QuestionPaperPrintComponent extends Component
{
    public int $examId;
    public int $subjectId;
    public ?QuestionPaperPrintAuthorization $authorization = null;

    public function mount(int $examId, int $subjectId): void
    {
        $institutionId = Auth::user()->institution_id;
        $branchId = $this->activeBranchId();

        $this->authorization = QuestionPaperPrintAuthorization::query()
            ->where('institution_id', $institutionId)
            ->where('branch_id', $branchId)
            ->where('user_id', Auth::id())
            ->where('exam_id', $examId)
            ->where('subject_id', $subjectId)
            ->first();

        $this->examId = $examId;
        $this->subjectId = $subjectId;

        abort_unless($this->authorization, 403, 'You are not authorized to print this question paper.');
    }

    private function activeBranchId(): ?int
    {
        return Auth::user()->branch_id
            ?? Branch::resolveMainBranchId(institution()->id);
    }

    public function getIsWindowOpenProperty(): bool
    {
        return $this->authorization && $this->authorization->isCurrentlyOpen();
    }

    /** Shown on the print page so the employee can confirm what they're about to print. */
    public function getPaperProperty(): ?QuestionPaper
    {
        $institutionId = Auth::user()->institution_id;
        $branchId = $this->activeBranchId();

        return QuestionPaper::query()
            ->where('institution_id', $institutionId)
            ->where('branch_id', $branchId)
            ->where('exam_id', $this->examId)
            ->where('subject_id', $this->subjectId)
            ->withCount('questions')
            ->first();
    }

    public function print(
        QuestionPaperWatermarkService $watermarkService,
        QuestionPaperPdfGenerator $generator,
        QuestionOrderShuffleService $shuffleService
    ) {
        $institutionId = Auth::user()->institution_id;
        $branchId = $this->activeBranchId();

        // Server-side re-check: the blade only *hides* the print button when
        // the window is closed/revoked — that's UI convenience, not a
        // security boundary. Without this check, a user with a stale,
        // revoked, or not-yet-open authorization row could fire this action
        // directly (e.g. via the browser dev tools) and still receive a
        // fully watermarked, logged copy of the paper, defeating the entire
        // point of the authorization/window system.
        abort_unless($this->authorization, 403);
        abort_unless($this->authorization->isCurrentlyOpen(), 403, 'Your print window is not currently open.');

        $paper = QuestionPaper::query()
            ->where('institution_id', $institutionId)
            ->where('branch_id', $branchId)
            ->where('exam_id', $this->examId)
            ->where('subject_id', $this->subjectId)
            ->with(['exam', 'questions.options', 'questions.matches', 'questions.parts'])
            ->firstOrFail();

        // Only a locked (finalized) paper may enter the print pipeline —
        // otherwise forensic tracing could point at content that has since
        // been edited, which would make the evidence meaningless.
        abort_unless($paper->is_locked, 409, 'This question paper must be locked before it can be printed.');

        // Step 1: log the print event and mint the forensic watermark_code.
        $log = $watermarkService->authorizeAndLogPrint(
            $this->authorization,
            $institutionId,
            $branchId,
            request()->ip(),
            request()->header('X-Device-Fingerprint')
        );

        // Structured per-family question data (options / matches / parts /
        // figure kept intact — NOT flattened to text) so the PDF generator
        // can render the same rich layout as the preview screen: MCQ option
        // grid, matching-pairs table, stimulus box, marks badges, figures.
        $questions = $paper->questions->map(fn (QuestionPaperQuestion $q) => [
            'id' => $q->id,
            'section' => $q->resolvedSectionHeader(),
            'family' => $q->family,
            'question_text' => $q->question_text,
            'stimulus_text' => $q->stimulus_text,
            'marks' => (float) $q->marks,
            'figure_path' => $q->figure_path,
            'options' => $q->options->map(fn ($o) => [
                'text' => $o->option_text,
            ])->toArray(),
            'matches' => $q->matches->map(fn ($m) => [
                'left' => $m->left_text, 'right' => $m->right_text,
            ])->toArray(),
            'parts' => $q->parts->map(fn ($p) => [
                'label' => $p->part_label, 'text' => $p->part_text, 'marks' => (float) $p->marks,
            ])->toArray(),
        ])->toArray();

        // Step 2: only shuffle if the exam has this feature toggled on.
        if ($paper->exam->randomize_question_order) {
            $result = $shuffleService->shuffleForPrint($questions);
            $questions = $result['questions'];

            $shuffleService->recordDistribution($log, $result['question_order'], $result['seed']);
        }

        // Step 3: render PDF with watermark layers 1-3.
        $pdfBinary = $generator->generate($log->watermark_code, [
            'institute_name' => $paper->institute_name ?: institution()->name,
            'exam_name' => $paper->exam_name ?: $paper->exam->name,
            'class_label' => $paper->class_label,
            'subject_label' => $paper->subject_label,
            'full_marks' => $paper->full_marks,
            'time_label' => $paper->time_label,
            'questions' => $questions,
        ]);

        return response()->streamDownload(
            fn () => print($pdfBinary),
            "question-paper-{$this->subjectId}.pdf",
            ['Content-Type' => 'application/pdf']
        );
    }

    public function render()
    {
        return view('livewire.teacher.question-paper.question-paper-print-component')
            ->layout('layouts.teacher.app', [
                'title' => 'Print Question Paper | ' . institution()->name,
            ]);
    }
}