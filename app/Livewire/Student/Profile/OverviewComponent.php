<?php

namespace App\Livewire\Student\Profile;

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
        return view('livewire.student.profile.overview-component')
            ->with('user', $this->user)
            ->layout('layouts.student.app', [
                    'title' => "Profile Overview | School SaaS",
                ]);
    }
}
