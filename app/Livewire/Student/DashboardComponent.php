<?php

namespace App\Livewire\Student;

use Livewire\Component;

class DashboardComponent extends Component
{
    public function render()
    {
        return view('livewire.student.dashboard-component')
        ->layout('layouts.student.app', [
            'title' => "Dashboard | Monarchy School",
        ]);
    }
}
