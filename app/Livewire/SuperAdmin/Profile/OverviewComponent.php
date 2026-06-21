<?php

namespace App\Livewire\SuperAdmin\Profile;

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
        return view('livewire.super-admin.profile.overview-component')
            ->with('user', $this->user)
            ->layout('layouts.superadmin.app', [
                    'title' => "Profile Overview | School SaaS",
                ]);
    }
}
