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

    protected string $paginationTheme = 'bootstrap';

    public string $search = '';
    public int $perPage = 10;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        /** @var Student|null $student */
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

    /**
     * Ekta class-e multiple section thakte pare, ebong prottek section-er
     * jonno alada AcademicClassAssign row thake (migration-er unique
     * constraint: institution_id + class_id + section_id). Tai shudhu
     * class_id diye filter korle wrong section-er assign chole ashte
     * pare — student-er nijer section_id-o match kora hocche.
     */
    private function getClassAssign(?Student $student): ?AcademicClassAssign
    {
        if (! $student?->class_id) {
            return null;
        }

        return AcademicClassAssign::with(['class', 'section'])
            ->where('class_id', $student->class_id)
            ->where('section_id', $student->section_id)
            ->first();
    }

    /**
     * Subject list ebong prottek subject-er nijer teacher
     * (academic_class_assign_details.teacher_id) details() relation
     * theke ana hocche — AcademicTeacherAssign model ekhane ar
     * lagche na, karon schema onujayi teacher assignment class-wise na,
     * subject-wise.
     */
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