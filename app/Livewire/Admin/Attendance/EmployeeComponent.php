<?php

namespace App\Livewire\Admin\Attendance;

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

        $institutionId = auth()->user()->institution_id;

        $employeesQuery = Employee::with([
            'department',
            'designation',
            'user',
        ])
            // ── Defense-in-depth: explicit institution scoping (global scope
            // thakleo mount() e explicit rakha hocche, project convention onujayi) ──
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
            // ── FIX: institution scoping added defense-in-depth hisebe ──
            ->where('institution_id', $institutionId)
            ->get()
            ->keyBy('attendable_id');

        $this->data = $employees->map(function ($employee) use ($existing) {

            // ── FIX: attendable_id (employees.id, integer PK) diye key
            // korte hobe, employee_id (string code) diye na ──
            $att = $existing[$employee->id] ?? null;

            return [
                // ── FIX: 'id' field notun add kora holo, eta employees.id
                // (auto-increment PK) - attendable_id hisebe eitai use hobe ──
                'id'          => $employee->id,
                'employee_id' => $employee->employee_id,
                'photo'       => $employee->photo,
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

        $institutionId = auth()->user()->institution_id;

        // ✅ Fix: bulk-save loop DB::transaction() diye wrap kora holo
        DB::beginTransaction();
        try {
            foreach ($this->data as $item) {

                Attendance::updateOrCreate(
                    [
                        // ── FIX: employee_id (string code) na diye employees.id
                        // (integer PK) pathano hocche - main bug fix ──
                        'attendable_id'   => $item['id'],
                        'attendable_type' => Employee::class,
                        'date'            => $this->filterDate,
                        'type'            => 'employee',
                        // ── FIX: institution_id o save kora hocche defense-in-depth
                        // hisebe (StudentComponent e class_id/section_id joto
                        // scoping hoy, ekhane institution_id level e) ──
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
            // ✅ Fix: exception ekhon log hocche, age silently gile fela hoto
            Log::error('Employee attendance save failed: ' . $e->getMessage());
            $this->dispatch('toast', type: 'error', message: 'Something went wrong!');
        }
    }

    public function resetForm()
    {
        $this->filterRole     = '';
        $this->filterDate     = now()->format('Y-m-d');
        $this->data           = [];
        $this->hasAttendance  = false;
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.admin.attendance.employee-component')
            ->layout('layouts.admin.app', [
                'title' => 'Employee Attendance | ' . institution()->name,
            ]);
    }
}