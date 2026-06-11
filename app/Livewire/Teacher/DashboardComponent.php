<?php

namespace App\Livewire\Tenant\Teacher;

use Livewire\Component;

class DashboardComponent extends Component
{
    public function render()
    {
        return view('livewire.tenant.teacher.dashboard-component')
        ->layout('layouts.teacher.app', [
            'title' => "Dashboard | Monarchy School",
        ]);
    }
}
    