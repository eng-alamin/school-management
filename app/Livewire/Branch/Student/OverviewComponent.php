<?php

namespace App\Livewire\Branch\Student;

use Livewire\Component;
use App\Models\Student;

class OverviewComponent extends Component
{
    public $student;

    public function mount(int $id)
    {
        $this->student = Student::with([
            'session',
            'class',
            'section',
            'group',
            'guardians.user',
            'user',
        ])
            ->where('institution_id', auth()->user()->institution_id)
            ->findOrFail($id);
    }

    public function render()
    {
        return view('livewire.branch.student.overview-component')
            ->with('student', $this->student)
            ->layout('layouts.branch.app', [
                'title' => 'Student Overview | ' . institution()->name,
            ]);
    }
}