<?php

namespace App\Livewire\Teacher\Salary;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Employee;
use App\Models\SalaryAdvance;

class AdvanceComponent extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    // ── Table controls ───────────────────────────────────────────
    public int $perPage = 10;

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    /**
     * Resolve the Employee record for the currently logged-in Teacher.
     *
     * SECURITY NOTE: Re-fetched on every request instead of stored in a
     * public Livewire property, so a teacher can never view another
     * employee's advance records by tampering with a persisted property.
     */
    protected function currentEmployee(): Employee
    {
        return Employee::where('user_id', auth()->id())
            ->where('institution_id', institution()->id)
            ->firstOrFail();
    }

    public function render()
    {
        $employee = $this->currentEmployee();

        $advances = SalaryAdvance::query()
            ->where('employee_id', $employee->id)
            ->where('institution_id', institution()->id)
            ->latest('advance_date')
            ->paginate($this->perPage);

        return view('livewire.teacher.salary.advance-component', [
            'advances' => $advances,
        ])->layout('layouts.teacher.app', [
            'title' => 'Advance Salary | ' . institution()->name,
        ]);
    }
}