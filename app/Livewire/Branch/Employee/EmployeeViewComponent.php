<?php

namespace App\Livewire\Branch\Employee;

use Livewire\Component;
use App\Models\Employee;

class EmployeeViewComponent extends Component
{
    public $employee;

    public string $routePrefix = '';

    public function mount(int $id)
    {
        $this->employee = Employee::with([
            'designation',
            'department',
            'user',
        ])->findOrFail($id);
        
        $this->routePrefix = $this->resolveRoutePrefix();
    }

    protected function resolveRoutePrefix(): string
    {
        $routeName = request()->route()?->getName();

        if ($routeName && str_contains($routeName, '.')) {
            return explode('.', $routeName)[0] . '.';
        }

        $segment = request()->segment(1);

        return $segment ? $segment . '.' : '';
    }

    public function render()
    {
        return view('livewire.admin.employee.employee-view-component')
            ->with('employee', $this->employee)
            ->layout('layouts.branch.app', [
                'title' => 'Employee Overview | ' . institution()->name,
            ]);
    }
}