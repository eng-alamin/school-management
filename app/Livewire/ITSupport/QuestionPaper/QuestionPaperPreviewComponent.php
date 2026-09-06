<?php

namespace App\Livewire\ITSupport\QuestionPaper;

use App\Models\Branch;
use App\Models\QuestionPaper;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class QuestionPaperPreviewComponent extends Component
{
    public QuestionPaper $paper;
    public array $groupedQuestions = [];

    public function mount(int $paperId): void
    {
        $institutionId = Auth::user()->institution_id;
        $branchId = $this->activeBranchId();

        $this->paper = QuestionPaper::query()
            ->where('institution_id', $institutionId)
            ->where('branch_id', $branchId)
            ->with(['questions.options', 'questions.matches', 'questions.parts', 'sectionLabels'])
            ->findOrFail($paperId);

        $this->buildGroups();
    }

    private function activeBranchId(): ?int
    {
        return Auth::user()->branch_id
            ?? Branch::resolveMainBranchId(institution()->id);
    }

    protected function buildGroups(): void
    {
        $labelOverrides = $this->paper->sectionLabels->pluck('label_bn', 'section_key');

        $groups = [];
        foreach ($this->paper->questions as $question) {
            $header = $question->resolvedSectionHeader();
            $groups[$header]['label_bn'] = $labelOverrides[$header] ?? $header;
            $groups[$header]['questions'][] = $question;
        }

        $this->groupedQuestions = $groups;
    }

    public function render()
    {
        return view('livewire.admin.question-paper.question-paper-preview-component')
        ->layout('layouts.itsupport.app', [
            'title' => 'Question Paper Preview | ' . institution()->name,
        ]);
    }
}