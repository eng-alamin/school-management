<?php

namespace App\Livewire\Admin\QuestionPaper;

use App\Models\Branch;
use App\Models\AcademicSession;
use App\Models\ExamSetup;
use App\Models\ExamSetupDetail;
use App\Models\QuestionPaper;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class QuestionPaperIndexComponent extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    public ?int $currentSessionId = null;

    public string $search = '';
    public ?int $examFilter = null;
    public ?int $subjectFilter = null;
    public int $perPage = 15;

    // ---------- "নতুন প্রশ্নপত্র" create flow ----------
    // A question paper is written against one ExamSetup row (= one exam for
    // one class) and one subject inside that exam's ExamSetupDetail list.
    // So the picker cascades: Exam name -> Class (resolves the ExamSetup
    // row) -> Subject (resolves the ExamSetupDetail row).
    public bool $showCreateModal = false;
    public ?string $newExamName = null;
    public ?int $newExamSetupId = null;   // = the chosen Exam+Class row -> becomes route {examId}
    public ?int $newSubjectId = null;     // = academic_subjects.id from the chosen ExamSetupDetail -> {subjectId}

    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';

    private const SORTABLE_FIELDS = ['exam_name', 'subject_label', 'class_label', 'full_marks', 'created_at'];

    private array $sortColumnMap = [
        'exam_name' => 'exam_name',
        'subject_label' => 'subject_label',
        'class_label' => 'class_label',
        'full_marks' => 'full_marks',
        'created_at' => 'created_at',
    ];

    public function mount(): void
    {
        $this->currentSessionId = $this->resolveCurrentSessionId();
    }

    private function activeBranchId(): ?int
    {
        return auth()->user()->branch_id
            ?? Branch::resolveMainBranchId(institution()->id);
    }

    private function resolveCurrentSessionId(): ?int
    {
        return AcademicSession::query()
            ->where('institution_id', institution()->id)
            ->where('branch_id', $this->activeBranchId())
            ->active() // scopeActive() -> is_current = true
            ->value('id');
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if (!in_array($field, self::SORTABLE_FIELDS, true)) {
            return; // ignore unknown/unsafe fields
        }

        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function openCreateModal(): void
    {
        $this->reset(['newExamName', 'newExamSetupId', 'newSubjectId']);
        $this->showCreateModal = true;
    }

    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
    }

    /** Step 1 changed -> forget everything downstream of it. */
    public function updatedNewExamName(): void
    {
        $this->newExamSetupId = null;
        $this->newSubjectId = null;
    }

    /** Step 2 changed -> forget the subject choice. */
    public function updatedNewExamSetupId(): void
    {
        $this->newSubjectId = null;
    }

    /**
     * STEP 1 — distinct exam names set up for the current institution/branch
     * and active session. The same exam name (e.g. "Half Yearly Exam") has
     * one ExamSetup row per class, so we only show the name here.
     */
    public function getExamNameOptionsProperty()
    {
        return ExamSetup::query()
            ->where('institution_id', Auth::user()->institution_id)
            ->where('branch_id', $this->activeBranchId())
            ->where('academic_session_id', $this->resolveCurrentSessionId())
            ->orderBy('name')
            ->distinct()
            ->pluck('name');
    }

    /**
     * STEP 2 — every class this exam name has been set up for. Each option's
     * value IS the exam_setups.id, since that single row already pins down
     * both "which exam" and "which class".
     */
    public function getClassOptionsProperty()
    {
        if (!$this->newExamName) {
            return collect();
        }

        return ExamSetup::query()
            ->where('institution_id', Auth::user()->institution_id)
            ->where('branch_id', $this->activeBranchId())
            ->where('academic_session_id', $this->resolveCurrentSessionId())
            ->where('name', $this->newExamName)
            ->with('classAssign.academicClass')
            ->get()
            ->map(fn (ExamSetup $setup) => [
                'id' => $setup->id,
                'label' => trim(($setup->classAssign?->academicClass?->name ?? 'Class')
                    . ($setup->classAssign?->section ? ' - ' . $setup->classAssign->section : '')),
            ])
            ->sortBy('label')
            ->values();
    }

    /**
     * STEP 3 — subjects entered under the chosen ExamSetup via its
     * ExamSetupDetail rows. Option value is the real academic_subjects.id
     * (subject_id on question_papers is a hard FK to that table).
     *
     * NOTE: assumes AcademicClassAssignDetail::subject() -> belongsTo
     * AcademicSubject. If that relation/column is named differently in the
     * real models, rename it here — search-and-replace `classAssignDetail.subject`.
     */
    public function getSubjectOptionsProperty()
    {
        if (!$this->newExamSetupId) {
            return collect();
        }

        return ExamSetupDetail::query()
            ->where('institution_id', Auth::user()->institution_id)
            ->where('branch_id', $this->activeBranchId())
            ->where('exam_setup_id', $this->newExamSetupId)
            ->with('classAssignDetail.subject')
            ->orderBy('serial')
            ->get()
            ->map(fn (ExamSetupDetail $detail) => [
                'id' => $detail->classAssignDetail?->subject?->id,
                'label' => $detail->classAssignDetail?->subject?->name,
            ])
            ->filter(fn ($row) => filled($row['id']))
            ->unique('id')
            ->values();
    }

    public function createNew()
    {
        $this->validate([
            'newExamSetupId' => 'required|integer',
            'newSubjectId' => 'required|integer',
        ], [], [
            'newExamSetupId' => 'Class',
            'newSubjectId' => 'Subject',
        ]);

        // Defense-in-depth: confirm both belong to this institution/branch
        // before handing them to the builder route — a tampered Livewire
        // payload must not be trusted just because validation passed.
        $institutionId = Auth::user()->institution_id;
        $branchId = $this->activeBranchId();

        $examSetup = ExamSetup::query()
            ->where('institution_id', $institutionId)
            ->where('branch_id', $branchId)
            ->find($this->newExamSetupId);

        abort_unless($examSetup, 403);

        $subjectBelongsToExam = ExamSetupDetail::query()
            ->where('institution_id', $institutionId)
            ->where('branch_id', $branchId)
            ->where('exam_setup_id', $examSetup->id)
            ->whereHas('classAssignDetail.subject', fn ($q) => $q->where('academic_subjects.id', $this->newSubjectId))
            ->exists();

        abort_unless($subjectBelongsToExam, 403);

        $this->showCreateModal = false;

        // Avoid creating a second QuestionPaper row for an exam+subject pair
        // that already has one — route straight to editing the existing
        // paper instead. Without this check, clicking "Add Question Paper"
        // twice for the same exam/subject silently produced a duplicate.
        $existingPaper = QuestionPaper::query()
            ->where('institution_id', $institutionId)
            ->where('branch_id', $branchId)
            ->where('exam_id', $examSetup->id)
            ->where('subject_id', $this->newSubjectId)
            ->first();

        if ($existingPaper) {
            return redirect()->route('admin.question-papers.builder.edit', [
                'examId' => $examSetup->id,
                'subjectId' => $this->newSubjectId,
                'paperId' => $existingPaper->id,
            ]);
        }

        return redirect()->route('admin.question-papers.builder', [
            'examId' => $examSetup->id,
            'subjectId' => $this->newSubjectId,
        ]);
    }

    public function getPapersProperty()
    {
        $institutionId = Auth::user()->institution_id;
        $branchId = $this->activeBranchId();

        $column = $this->sortColumnMap[$this->sortField] ?? 'created_at';

        return QuestionPaper::query()
            ->where('institution_id', $institutionId)
            ->where('branch_id', $branchId)
            ->when($this->examFilter, fn ($q) => $q->where('exam_id', $this->examFilter))
            ->when($this->subjectFilter, fn ($q) => $q->where('subject_id', $this->subjectFilter))
            ->when($this->search, function ($q) {
                $term = '%' . $this->search . '%';
                $q->where(function ($inner) use ($term) {
                    $inner->where('exam_name', 'like', $term)
                        ->orWhere('subject_label', 'like', $term)
                        ->orWhere('class_label', 'like', $term)
                        ->orWhere('institute_name', 'like', $term);
                });
            })
            ->withCount('questions')
            ->orderBy($column, $this->sortDirection)
            ->paginate($this->perPage);
    }

    public function render()
    {
        return view('livewire.admin.question-paper.question-paper-index-component', [
            'papers' => $this->papers,
        ])
        ->layout('layouts.admin.app', [
            'title' => 'Question Paper | ' . institution()->name,
        ]);
    }
}