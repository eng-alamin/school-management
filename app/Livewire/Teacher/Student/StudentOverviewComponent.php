<?php

namespace App\Livewire\Teacher\Student;

use Livewire\Component;
use App\Models\Student;

class StudentOverviewComponent extends Component
{
    public $student;

    public function mount(int $id)
    {
        $this->student = Student::with([
            'session',
            'class',
            'section',
            'category',
            'guardians',
            'user',
        ])->findOrFail($id);
    }

    public function render()
    {
        return view('livewire.teacher.student.student-overview-component')
            ->with('student', $this->student)
            ->layout('layouts.teacher.app', [
                'title' => "Student Overview | School SaaS",
            ]);
    }
}