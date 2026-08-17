<?php

namespace App\Livewire\Branch\Profile;

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
        return view('livewire.branch.profile.overview-component')
            ->with('user', $this->user)
            ->layout('layouts.branch.app', [
                    'title' => 'Profile Overview | ' . institution()->name,
                ]);
    }
}
