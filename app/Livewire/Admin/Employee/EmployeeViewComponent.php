<?php

namespace App\Livewire\Admin\Employee;

use Livewire\Component;
use App\Models\Employee;

class EmployeeViewComponent extends Component
{
    public $employee;

    public function mount(int $id)
    {
        $this->employee = Employee::with([
            'designation',
            'department',
            'user',
        ])->findOrFail($id);
    }

    public function render()
    {
        return view('livewire.admin.employee.employee-view-component')
            ->with('employee', $this->employee)
            ->layout('layouts.admin.app', [
                'title' => 'Employee Overview | ' . institution()->name,
            ]);
    }
}