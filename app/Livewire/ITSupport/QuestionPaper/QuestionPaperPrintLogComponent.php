<?php

namespace App\Livewire\ITSupport\QuestionPaper;

use App\Models\Branch;
use App\Models\ExamSetup;
use App\Models\QuestionPaperPrintLog;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class QuestionPaperPrintLogComponent extends Component
{
    use WithPagination;

    public string $search = '';
    public string $examFilter = '';

    protected $queryString = ['search' => ['except' => '']];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingExamFilter(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset('search', 'examFilter');
        $this->resetPage();
    }

    public function getLogsProperty()
    {
        $institutionId = Auth::user()->institution_id;
        $branchId = $this->activeBranchId();

        return QuestionPaperPrintLog::query()
            ->where('institution_id', $institutionId)
            ->where('branch_id', $branchId)
            ->with(['printedBy:id,name', 'exam:id,name', 'subject:id,name'])
            ->when($this->search !== '', function ($q) {
                $q->where('watermark_code', 'like', '%' . addcslashes(trim($this->search), '%_') . '%');
            })
            ->when($this->examFilter !== '', fn ($q) => $q->where('exam_id', $this->examFilter))
            ->latest('printed_at')
            ->paginate(20);
    }

    public function getExamOptionsProperty()
    {
        $institutionId = Auth::user()->institution_id;
        $branchId = $this->activeBranchId();

        $examIds = QuestionPaperPrintLog::query()
            ->where('institution_id', $institutionId)
            ->where('branch_id', $branchId)
            ->distinct()
            ->pluck('exam_id');

        return ExamSetup::with('classAssign.academicClass', 'classAssign.academicSection')
            ->whereIn('id', $examIds)
            ->orderBy('name')
            ->get();
    }

    private function activeBranchId(): ?int
    {
        return Auth::user()->branch_id
            ?? Branch::resolveMainBranchId(institution()->id);
    }

    public function render()
    {
        return view('livewire.admin.question-paper.question-paper-print-log-component', [
            'logs' => $this->logs,
            'examOptions' => $this->examOptions,
        ])
        ->layout('layouts.itsupport.app', [
            'title' => 'Print Log | ' . institution()->name,
        ]);
    }
}