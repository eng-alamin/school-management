<?php

namespace App\Livewire\SuperAdmin;

use Livewire\Component;
use App\Models\School;
use App\Models\Student;
use App\Models\Employee;
use App\Models\Billing;

class DashboardComponent extends Component
{
    public $totalSchools;
    public $activeSchools;
    public $totalStudents;
    public $totalTeachers;
    public $totalRevenue;

    public function mount()
    {
        $this->totalSchools = School::count();

        $this->activeSchools = School::where('status', 'active')->count();

        $this->totalStudents = Student::count();

        $this->totalTeachers = Employee::count();

        $this->totalRevenue = Billing::sum('total_amount'); // adjust field name if needed
    }

    public function render()
    {
        return view('livewire.super-admin.dashboard-component')
            ->layout('layouts.superadmin.app');
    }
}