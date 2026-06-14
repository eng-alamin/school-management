<?php

namespace App\Livewire\Parent;

use Livewire\Component;
use App\Models\Guardian;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;

class DashboardComponent extends Component
{
    public $children;

    public function mount()
    {
        $guardian = Guardian::where('user_id', Auth::id())
            ->with(['students.class', 'students.section'])
            ->first();

        $this->children = $guardian ? $guardian->students : collect();
    }

    public function goToDashboard($studentId)
    {
    $student = \App\Models\Student::with('user')->findOrFail($studentId);

        Auth::login($student->user);

        request()->session()->regenerate();

        return $this->redirect(route('student.dashboard'));
    }

    public function render()
    {
        return view('livewire.parent.dashboard-component')
            ->layout('layouts.parent.app', [
                'title' => 'Dashboard | Monarchy School',
            ]);
    }
}