<?php

namespace App\Livewire\SuperAdmin;

use Livewire\Component;
use App\Models\School;
use App\Models\User;
use App\Models\Student;
use App\Models\Employee;
use App\Models\Invoice;

class DashboardComponent extends Component
{
    public $totalSchools;
    public $activeSchools;
    public $totalStudents;
    public $activeStudents;
    public $totalTeachers;
    public $activeTeachers;
    public $totalRevenue;

    public function mount()
    {
        $this->totalSchools  = School::count();
        $this->activeSchools = School::where('status', true)->count();
        $this->totalStudents = User::where('role', 'student')->count();
        $this->activeStudents = User::where('is_active', true)->where('role', 'student')->count();
        $this->totalTeachers = User::where('role', 'teacher')->count();
        $this->activeTeachers = User::where('is_active', true)->where('role', 'teacher')->count();
        $this->totalRevenue = Invoice::sum('total_amount');
    }

    public function render()
    {
        return view('livewire.super-admin.dashboard-component')
            ->layout('layouts.superadmin.app');
    }
}