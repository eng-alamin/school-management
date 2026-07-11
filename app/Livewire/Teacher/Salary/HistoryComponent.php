<?php

namespace App\Livewire\Teacher\Salary;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Employee;
use App\Models\SalaryPayment;

class HistoryComponent extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    // ── Table controls ───────────────────────────────────────────
    public int $perPage = 10;

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }


    protected function currentEmployee(): Employee
    {
        return Employee::where('user_id', auth()->id())
            ->where('institution_id', institution()->id)
            ->firstOrFail();
    }

    protected function salaryQuery(Employee $employee)
    {
        return SalaryPayment::query()
            ->where('employee_id', $employee->id)
            ->where('institution_id', institution()->id)
            ->latest('month');
    }

    public function render()
    {
        $employee = $this->currentEmployee();

        $salaries = $this->salaryQuery($employee)->paginate($this->perPage);

        return view('livewire.teacher.salary.history-component', [
            'salaries' => $salaries,
        ])->layout('layouts.teacher.app', [
            'title' => 'Salary History | ' . institution()->name,
        ]);
    }
}