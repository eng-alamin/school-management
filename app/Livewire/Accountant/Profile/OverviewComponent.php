<?php

namespace App\Livewire\Tenant\Accountant\Profile;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class OverviewComponent extends Component
{
    public $user;
 
    public function mount()
    {
        $this->user = Auth::user();
    }

    public function render()
    {
        return view('livewire.tenant.accountant.profile.overview-component')
            ->with('user', $this->user)
            ->layout('layouts.accountant.app', [
                    'title' => "Profile Overview | School SaaS",
                ]);
    }
}
