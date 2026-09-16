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


    public bool $showCreateModal = false;
    public ?string $newExamName = null;
    public ?int $newExamSetupId = null;
    public ?int $newSubjectId = null;

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

    public function updatingExamFilter(): void
    {
        $this->subjectFilter = null;
        $this->resetPage();
    }

    public function updatingSubjectFilter(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'examFilter', 'subjectFilter']);
        $this->resetPage();
    }

    public function getExamFilterOptionsProperty()
    {
        return QuestionPaper::query()
            ->where('institution_id', Auth::user()->institution_id)
            ->where('branch_id', $this->activeBranchId())
            ->select('exam_id', 'exam_name')
            ->distinct()
            ->orderBy('exam_name')
            ->get();
    }

    public function getSubjectFilterOptionsProperty()
    {
        return QuestionPaper::query()
            ->where('institution_id', Auth::user()->institution_id)
            ->where('branch_id', $this->activeBranchId())
            ->when($this->examFilter, fn ($q) => $q->where('exam_id', $this->examFilter))
            ->select('subject_id', 'subject_label')
            ->distinct()
            ->orderBy('subject_label')
            ->get();
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

    public function updatedNewExamName(): void
    {
        $this->newExamSetupId = null;
        $this->newSubjectId = null;
    }

    public function updatedNewExamSetupId(): void
    {
        $this->newSubjectId = null;
    }

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