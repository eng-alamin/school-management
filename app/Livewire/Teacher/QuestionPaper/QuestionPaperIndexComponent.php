<?php

namespace App\Livewire\Teacher\QuestionPaper;

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
        return view('livewire.teacher.question-paper.question-paper-index-component', [
            'papers' => $this->papers,
        ])
        ->layout('layouts.teacher.app', [
            'title' => 'Question Paper | ' . institution()->name,
        ]);
    }
}