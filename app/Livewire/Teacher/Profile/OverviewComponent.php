<?php

namespace App\Livewire\Teacher\Profile;

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
        return view('livewire.teacher.profile.overview-component')
            ->with('user', $this->user)
            ->layout('layouts.teacher.app', [
                    'title' => 'Profile Overview | ' . institution()->name,
                ]);
    }
}
