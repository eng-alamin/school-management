<?php

namespace App\Livewire\Frontend;

use Livewire\Component;
use App\Models\User;
use App\Models\Institution;
use App\Models\Employee;

class HomeComponent extends Component
{
    public function render()
    {
        $users = User::get();
        $institutions = Institution::get();
        $employees = Employee::get();

        return view('livewire.frontend.home-component')
            ->with('users', $users)
            ->with('institutions', $institutions)
            ->with('employees', $employees)
            ->layout('layouts.frontend.app', [
                'title' => "Home | Monarchy School",
            ]);
    }
}