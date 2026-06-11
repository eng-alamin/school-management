<?php

namespace App\Livewire\Tenant\Accountant;

use Livewire\Component;

class DashboardComponent extends Component
{
    public function render()
    {
        return view('livewire.tenant.accountant.dashboard-component')
        ->layout('layouts.accountant.app', [
            'title' => "Dashboard | Monarchy School",
        ]);
    }
}
