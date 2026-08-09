<?php

namespace App\Livewire\Ministry\Profile;

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
        return view('livewire.ministry.profile.overview-component')
            ->with('user', $this->user)
            ->layout('layouts.ministry.app', [
                    'title' => 'Profile Overview | ' . setting('app_name', 'EMS'),
                ]);
    }
}
