<?php

namespace App\Livewire\Tenant\Student;

use Livewire\Component;

class DashboardComponent extends Component
{
    public function render()
    {
        return view('livewire.tenant.student.dashboard-component')
        ->layout('layouts.student.app', [
            'title' => "Dashboard | Monarchy School",
        ]);
    }
}
