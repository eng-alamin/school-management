<?php

namespace App\Livewire\Admin\Student;

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
        return view('livewire.admin.student.student-overview-component')
            ->with('student', $this->student)
            ->layout('layouts.admin.app', [
                'title' => "Student Overview | School SaaS",
            ]);
    }
}