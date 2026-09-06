<?php

namespace App\Livewire\Admin\Report;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\SalaryAdvanceRepayment;
use App\Models\Branch;

class SalaryAdvanceRepaymentReportComponent extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    public const SORTABLE_FIELDS = ['deducted_date', 'amount', 'created_at'];

    protected array $sortColumnMap = [
        'deducted_date' => 'salary_advance_repayments.deducted_date',
        'amount'        => 'salary_advance_repayments.amount',
        'created_at'    => 'salary_advance_repayments.created_at',
    ];

    public string $sortField     = 'deducted_date';
    public string $sortDirection = 'desc';

    // ---------- Filters ----------
    public string $dateFrom;
    public string $dateTo;
    public string $search  = '';
    public int    $perPage = 15;

    public function mount(): void
    {
        $this->dateFrom = now()->startOfMonth()->toDateString();
        $this->dateTo   = now()->toDateString();
    }

    private function activeBranchId(): ?int
    {
        return auth()->user()->branch_id
            ?? Branch::resolveMainBranchId(institution()->id);
    }

    public function updatingDateFrom(): void { $this->resetPage(); }
    public function updatingDateTo(): void   { $this->resetPage(); }
    public function updatingSearch(): void   { $this->resetPage(); }

    public function sortBy(string $field): void
    {
        if (! in_array($field, self::SORTABLE_FIELDS, true)) {
            return;
        }

        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField     = $field;
            $this->sortDirection = 'asc';
        }
    }

    protected function baseQuery()
    {
        $institutionId = institution()->id;
        $branchId      = $this->activeBranchId();

        return SalaryAdvanceRepayment::query()
            ->select('salary_advance_repayments.*')
            ->where('institution_id', $institutionId)
            ->where('branch_id', $branchId)
            ->whereBetween('deducted_date', [$this->dateFrom, $this->dateTo])
            ->when($this->search !== '', function ($q) {
                $q->whereHas('salaryAdvance.employee', function ($sub) {
                    $sub->where('name', 'like', "%{$this->search}%")
                        ->orWhere('employee_id', 'like', "%{$this->search}%");
                });
            });
    }

    public function render()
    {
        $sortColumn = $this->sortColumnMap[$this->sortField] ?? 'salary_advance_repayments.deducted_date';

        $records = $this->baseQuery()
            ->with(['salaryAdvance.employee:id,name,employee_id', 'salaryPayment:id,month'])
            ->orderBy($sortColumn, $this->sortDirection)
            ->paginate($this->perPage);

        $summaryBase = $this->baseQuery();

        $summary = [
            'total_repayments' => (clone $summaryBase)->count(),
            'total_amount'     => (clone $summaryBase)->sum('amount'),
        ];

        return view('livewire.admin.report.salary-advance-repayment-report-component', [
            'records' => $records,
            'summary' => $summary,
        ])
        ->layout('layouts.admin.app', [
            'title' => 'Reports | ' . institution()->name,
            'breadcrumbs' => [
                ['name' => 'Advance Repayment Report', 'url' => route('admin.reports.salary.advance-repayments')],
            ],
        ]);
    }
}
