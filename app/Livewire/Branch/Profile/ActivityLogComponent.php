<?php

namespace App\Livewire\Branch\Profile;

use Livewire\Component;

class ActivityLogComponent extends Component
{
    public function render()
    {
        return view('livewire.branch.profile.activity-log-component')
            ->layout('layouts.branch.app', [
                'title' => 'Login Logs | ' . institution()->name,
            ]);
    }
}
