<?php

namespace App\Livewire\Parent\Profile;

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
        return view('livewire.parent.profile.overview-component')
            ->with('user', $this->user)
            ->layout('layouts.parent.app', [
                    'title' => "Profile Overview | School SaaS",
                ]);
    }
}
