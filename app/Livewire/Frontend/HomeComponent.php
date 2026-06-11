<?php

namespace App\Livewire\Frontend;

use Livewire\Component;

class HomeComponent extends Component
{
    public function render()
    {
        return view('livewire.frontend.home-component')
        ->layout('layouts.frontend.app', [
            'title' => "Home | Monarchy School",
        ]);
    }
}