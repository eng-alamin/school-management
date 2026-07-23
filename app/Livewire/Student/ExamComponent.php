<?php

namespace App\Livewire\Student;

use App\Models\AcademicClassAssign;
use App\Models\ExamSetup;
use Livewire\Component;
use Livewire\WithPagination;

class ExamComponent extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    public string $search        = '';
    public int    $perPage       = 10;
    public string $sortField     = 'name';
    public string $sortDirection = 'asc';

    // Modal
    public bool       $showViewModal = false;
    public ?ExamSetup $viewRecord    = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField     = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function openView(int $examSetupId): void
    {
        $this->viewRecord = ExamSetup::with([
            'classAssign.class',
            'classAssign.section',
            'schedules.examSetupDetail.classAssignDetail.subject',
        ])->findOrFail($examSetupId);

        $this->viewRecord->setRelation(
            'schedules',
            $this->viewRecord->schedules->sortBy(['exam_date', 'start_time'])
        );

        $this->showViewModal = true;
    }

    public function render()
    {
        $student = auth()->user()->student;

        $assign = null;

        if ($student?->class_id) {
            $assign = AcademicClassAssign::where('class_id', $student->class_id)
                ->where('section_id', $student->section_id)
                ->first();
        }

        $setups = ExamSetup::with(['classAssign.class', 'classAssign.section'])
            ->withCount([
                'schedules as total_subjects',
                'schedules as published_count' => fn ($q) => $q->where('is_published', true),
            ])
            ->whereHas('schedules')
            ->when(
                $assign,
                fn ($q) => $q->where('academic_class_assign_id', $assign->id),
                // Student-er kono class assign na thakle kono result e jeno na ashe
                fn ($q) => $q->whereRaw('1 = 0')
            )
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.student.exam-component')
            ->with('schedules', $setups)
            ->layout('layouts.student.app', [
                'title' => 'Exam Schedule | ' . institution()->name,
            ]);
    }
}