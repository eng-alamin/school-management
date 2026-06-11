<?php

namespace App\Livewire\Tenant\Student;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\AcademicClassAssign;
use App\Models\AcademicSubject;
use App\Models\AcademicTeacherAssign;

class SubjectComponent extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    public string $search = '';
    public int $perPage = 10;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $student = auth()->user()->student;
        $classId = $student?->class_id;

        $assign = AcademicClassAssign::with(['class', 'section'])
            ->where('class_id', $classId)
            ->first();

        $subjects = collect();
        $teacher  = null;

        if ($assign) {
            $subjectNames = $assign->subjects ?? [];

            $subjects = AcademicSubject::whereIn('name', $subjectNames)
                ->when($this->search, fn($q) => $q
                    ->where('name', 'like', "%{$this->search}%")
                    ->orWhere('code', 'like', "%{$this->search}%")
                    ->orWhere('type', 'like', "%{$this->search}%")
                    ->orWhere('author', 'like', "%{$this->search}%")
                )
                ->paginate($this->perPage);

            $teacher = AcademicTeacherAssign::with('teacher')
                ->where('class_id', $assign->class_id)
                ->where('section_id', $assign->section_id)
                ->first()?->teacher;
        }

        return view('livewire.tenant.student.subject-component', [
            'subjects' => $subjects,
            'assign'   => $assign,
            'teacher'  => $teacher,
        ])->layout('layouts.student.app', [
            'title' => "Subjects | Monarchy School",
        ]);
    }
}