<?php

namespace App\Livewire\Tenant\Student\Profile;

use Livewire\Component;
use App\Models\Student;

class DetailComponent extends Component
{
    public $student;

    public function mount(): void
    {
        $this->student = Student::with([
            'session',
            'class',
            'section',
            'category',
            'guardians',
            'user',
        ])->where('user_id', auth()->id())->firstOrFail();
    }

    public function render()
    {
        return view('livewire.tenant.student.profile.detail-component')
            ->with('student', $this->student)
            ->layout('layouts.student.app', [
                'title' => "Profile Details | Monarchy School",
            ]);
    }
}