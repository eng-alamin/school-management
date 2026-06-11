<?php

namespace App\Livewire\Admin\Attendance;

use Livewire\Component;
use App\Models\Attendance;
use App\Models\Employee;

class EmployeeComponent extends Component
{
    public $filterRole = '';
    public $date;

    public $data = [];
    public $hasAttendance = false;

    public function mount()
    {
        $this->date = now()->format('Y-m-d');
    }

    public function filter()
    {
        if (!$this->filterRole || !$this->date) {
            return;
        }

        $employeesQuery = Employee::with([
            'department',
            'designation',
            'user',
        ]);

        if ($this->filterRole) {

            $employeesQuery->whereHas('user', function ($query) {

                $query->where('role', $this->filterRole);

            });

        }

        $employees = $employeesQuery
            ->orderBy('name')
            ->get();

        if ($employees->isEmpty()) {

        $this->dispatch(
            'toast',
            type: 'error',
            message: 'No employees found.'
        );

        $this->hasAttendance = false;

        return;
    }

    $existing = Attendance::where('date', $this->date)
        ->where('type', 'employee')
        ->get()
        ->keyBy('attendable_id');

    $this->data = $employees->map(function ($employee) use ($existing) {

        $att = $existing[$employee->id] ?? null;

        return [
            'employee_id' => $employee->id,
            'name'        => $employee->name,
            'designation' => $employee->designation?->name,
            'department'  => $employee->department?->name,
            'role'        => $employee->user?->role,

            'status'      => $att->status ?? 'present',
            'remarks'     => $att->remarks ?? '',
        ];

    })->toArray();

    $this->hasAttendance = true;
}

    public function save()
    {
        $this->validate([
            'date' => 'required|date',
        ]);

        try {
            foreach ($this->data as $item) {

                Attendance::updateOrCreate(
                    [
                        'attendable_id'   => $item['employee_id'],
                        'attendable_type' => Employee::class,
                        'date'            => $this->date,
                        'type'            => 'employee',
                    ],
                    [
                        'status'  => $item['status'],
                        'remarks' => $item['remarks'],
                    ]
                );
            }

            $this->dispatch('toast', type: 'success', message: 'Attendance saved successfully!');

        } catch (\Exception $e) {
            $this->dispatch('toast', type: 'error', message: 'Something went wrong!');
        }
    }

    public function resetForm()
    {
        $this->filterRole = '';
        $this->date = now()->format('Y-m-d');
        $this->data = [];
        $this->hasAttendance = false;
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.admin.attendance.employee-component')
            ->layout('layouts.admin.app', [
                'title' => "Employee Attendance | School SaaS",
            ]);
    }
}