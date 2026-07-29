<?php

namespace App\Livewire\Teacher\Attendance;

use Livewire\Component;
use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EmployeeComponent extends Component
{
    public $filterRole = '';
    public $filterDate;

    public $data = [];
    public $hasAttendance = false;

    public function mount()
    {
        $this->filterDate = now()->format('Y-m-d');
    }

    public function filter()
    {
        if (!$this->filterRole) {
            $this->dispatch('toast', type: 'error', message: 'Please select a role.');
            return;
        }

        $institutionId = institution()->id;

        $employeesQuery = Employee::with([
            'department',
            'designation',
            'user',
        ])
            // Defense-in-depth: explicit institution scoping (global scope
            // already covers it, but project convention keeps this explicit too)
            ->where('institution_id', $institutionId);

        if ($this->filterRole) {
            $employeesQuery->whereHas('user', function ($query) {
                $query->where('role', $this->filterRole);
            });
        }

        $employees = $employeesQuery->orderBy('name')->get();

        if ($employees->isEmpty()) {
            $this->dispatch('toast', type: 'error', message: 'No employees found.');
            $this->hasAttendance = false;
            return;
        }

        $existing = Attendance::where('date', $this->filterDate)
            ->where('type', 'employee')
            ->where('institution_id', $institutionId)
            ->get()
            ->keyBy('attendable_id');

        $this->data = $employees->map(function ($employee) use ($existing) {

            $att = $existing[$employee->id] ?? null;

            return [
                // 'id' = employees.id (auto-increment PK) — attendable_id
                // hisebe eitai use hobe. Age eta 'employee_id' name-e chilo
                // (value already $employee->id chilo tai bug chilo na), kintu
                // naming confusing — mone hoy string admission code, tai clarity-r
                // jonno rename kora holo (Admin panel-er already-fixed naming-er sathe mile).
                'id'          => $employee->id,
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
            'filterDate' => 'required|date',
        ]);

        $institutionId = institution()->id;

        DB::beginTransaction();
        try {
            foreach ($this->data as $item) {

                Attendance::updateOrCreate(
                    [
                        'attendable_id'   => $item['id'],
                        'attendable_type' => Employee::class,
                        'date'            => $this->filterDate,
                        'type'            => 'employee',
                        'institution_id'  => $institutionId,
                    ],
                    [
                        'status'  => $item['status'],
                        'remarks' => $item['remarks'],
                    ]
                );
            }

            DB::commit();
            $this->dispatch('toast', type: 'success', message: 'Attendance saved successfully!');

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Teacher employee attendance save failed: ' . $e->getMessage());
            $this->dispatch('toast', type: 'error', message: 'Something went wrong!');
        }
    }

    public function resetForm()
    {
        $this->filterRole = '';
        $this->filterDate = now()->format('Y-m-d');
        $this->data = [];
        $this->hasAttendance = false;
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.teacher.attendance.employee-component')
            ->layout('layouts.teacher.app', [
                'title' => 'Employee Attendance | ' . institution()->name,
            ]);
    }
}