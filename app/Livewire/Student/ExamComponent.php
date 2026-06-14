<?php

namespace App\Livewire\Student;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ExamSchedule;

class ExamComponent extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    public string $search        = '';
    public int    $perPage       = 10;
    public string $sortField     = 'id';
    public string $sortDirection = 'asc';

    public bool $showViewModal  = false;
    public ?ExamSchedule $viewRecord = null;

    public function updatingSearch(): void { $this->resetPage(); }

    public function sortBy(string $field): void
    {
        $this->sortDirection = ($this->sortField === $field && $this->sortDirection === 'asc')
            ? 'desc' : 'asc';
        $this->sortField = $field;
        $this->resetPage();
    }

    public function openView(int $id): void
    {
        $this->viewRecord    = ExamSchedule::with('exam', 'class', 'section')->findOrFail($id);
        $this->showViewModal = true;
    }

    public function render()
    {
        $student = auth()->user()->student;

        $schedules = ExamSchedule::with('exam', 'class', 'section')
            ->when($student?->class_id,   fn($q) => $q->where('class_id',   $student->class_id))
            ->when($student?->section_id, fn($q) => $q->where('section_id', $student->section_id))
            ->when($this->search, fn($q) => $q
                ->whereHas('exam',    fn($e) => $e->where('name', 'like', "%{$this->search}%"))
                ->orWhereHas('class', fn($e) => $e->where('name', 'like', "%{$this->search}%"))
            )
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.student.exam-component')
            ->with('schedules', $schedules)
            ->layout('layouts.student.app', [
                'title' => "Exam Schedule | Monarchy School",
            ]);
    }
}