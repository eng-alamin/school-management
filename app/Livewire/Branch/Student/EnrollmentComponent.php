<?php

namespace App\Livewire\Branch\Student;

use Livewire\Component;
use App\Models\Student;
use App\Models\StudentEnrollment;

class EnrollmentComponent extends Component
{
    public Student $student;

    public function mount(int $id)
    {
        $this->student = Student::where('institution_id', auth()->user()->institution_id)
            ->findOrFail($id);
    }

    public function render()
    {
        $institutionId = auth()->user()->institution_id;

        $enrollments = StudentEnrollment::with(['class', 'section', 'group'])
            ->where('institution_id', $institutionId)
            ->where('student_id', $this->student->id)
            ->orderByDesc('id')
            ->get();

        return view('livewire.branch.student.enrollment-component')
            ->with('student', $this->student)
            ->with('enrollments', $enrollments)
            ->layout('layouts.branch.app', [
                'title' => 'Enrollment History | ' . institution()->name,
            ]);
    }
}