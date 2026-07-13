<?php

namespace App\Livewire\Accountant\Employee;

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
        return view('livewire.accountant.employee.employee-view-component')
            ->with('employee', $this->employee)
            ->layout('layouts.accountant.app', [
                'title' => 'Employee Overview | ' . institution()->name,
            ]);
    }
}