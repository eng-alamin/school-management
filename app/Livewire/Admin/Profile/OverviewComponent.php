<?php

namespace App\Livewire\Admin\Profile;

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
        return view('livewire.admin.profile.overview-component')
            ->with('user', $this->user)
            ->layout('layouts.admin.app', [
                    'title' => "Profile Overview | School SaaS",
                ]);
    }
}
