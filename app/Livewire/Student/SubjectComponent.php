<?php

namespace App\Livewire\Student;

use App\Models\AcademicClassAssign;
use App\Models\Student;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Component;
use Livewire\WithPagination;

class SubjectComponent extends Component
{
    use WithPagination;

    public string $paginationTheme = 'bootstrap';

    public string $search = '';
    public int $perPage = 10;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $student = auth()->user()->student;

        $assign  = $this->getClassAssign($student);
        $details = $this->getSubjectDetails($assign);

        return view('livewire.student.subject-component', [
            'details' => $details,
            'assign'  => $assign,
        ])->layout('layouts.student.app', [
            'title' => 'Subjects | Monarchy School',
        ]);
    }

    private function getClassAssign(?Student $student): ?AcademicClassAssign
    {
        if (! $student?->class_id) {
            return null;
        }

        return AcademicClassAssign::with(['class', 'section'])
            ->where('institution_id', $student->institution_id)
            ->where('class_id', $student->class_id)
            ->where('section_id', $student->section_id)
            ->first();
    }

    private function getSubjectDetails(?AcademicClassAssign $assign): LengthAwarePaginator
    {
        if (! $assign) {
            return new LengthAwarePaginator([], 0, $this->perPage);
        }

        return $assign->details()
            ->with(['subject', 'teacher'])
            ->when($this->search, function ($query) {
                $query->whereHas('subject', function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                        ->orWhere('code', 'like', "%{$this->search}%")
                        ->orWhere('type', 'like', "%{$this->search}%")
                        ->orWhere('author', 'like', "%{$this->search}%");
                });
            })
            ->paginate($this->perPage);
    }
}