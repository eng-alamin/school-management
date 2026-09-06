<?php

namespace App\Livewire\ITSupport\Report;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\SalaryAdvance;
use App\Models\Branch;
use Illuminate\Support\Facades\DB;

class SalaryAdvanceReportComponent extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    public const SORTABLE_FIELDS = ['advance_date', 'amount', 'remaining_amount', 'status', 'created_at'];

    protected array $sortColumnMap = [
        'advance_date'      => 'salary_advances.advance_date',
        'amount'            => 'salary_advances.amount',
        'remaining_amount'  => 'salary_advances.remaining_amount',
        'status'            => 'salary_advances.status',
        'created_at'        => 'salary_advances.created_at',
    ];

    public string $sortField     = 'advance_date';
    public string $sortDirection = 'desc';

    // ---------- Filters ----------
    public string $dateFrom;
    public string $dateTo;
    public string $status = 'all';
    public string $search = '';
    public int    $perPage = 15;

    public function mount(): void
    {
        $this->dateFrom = now()->startOfYear()->toDateString();
        $this->dateTo   = now()->toDateString();
    }

    private function activeBranchId(): ?int
    {
        return auth()->user()->branch_id
            ?? Branch::resolveMainBranchId(institution()->id);
    }

    public function updatingDateFrom(): void { $this->resetPage(); }
    public function updatingDateTo(): void   { $this->resetPage(); }
    public function updatingStatus(): void   { $this->resetPage(); }
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

    /**
     * Common filters (date range, search) ছাড়া status filter।
     * Summary count-এর জন্য status বাদে বাকি সব filter দরকার হয়, তাই আলাদা রাখা হলো।
     */
    protected function baseQueryWithoutStatus()
    {
        $institutionId = institution()->id;
        $branchId      = $this->activeBranchId();

        return SalaryAdvance::query()
            ->select('salary_advances.*')
            ->where('institution_id', $institutionId)
            ->where('branch_id', $branchId)
            ->whereBetween('advance_date', [$this->dateFrom, $this->dateTo])
            ->when($this->search !== '', function ($q) {
                $q->whereHas('employee', function ($sub) {
                    $sub->where('name', 'like', "%{$this->search}%")
                        ->orWhere('employee_id', 'like', "%{$this->search}%");
                });
            });
    }

    protected function baseQuery()
    {
        return $this->baseQueryWithoutStatus()
            ->when($this->status !== 'all', fn ($q) => $q->where('status', $this->status));
    }

    public function render()
    {
        $sortColumn = $this->sortColumnMap[$this->sortField] ?? 'salary_advances.advance_date';

        $records = $this->baseQuery()
            ->with(['employee:id,name,employee_id', 'createdBy:id,name'])
            ->withCount('repayments')
            ->orderBy($sortColumn, $this->sortDirection)
            ->paginate($this->perPage);

        // Summary — একই ফিল্টার (তারিখ, search) দিয়ে, শুধু status বাদে
        $summaryBase = $this->baseQueryWithoutStatus();

        $summary = [
            'total_advances' => (clone $summaryBase)->count(),
            'total_amount'   => (clone $summaryBase)->sum('amount'),
            'total_remaining'=> (clone $summaryBase)->sum('remaining_amount'),
            'active_count'   => (clone $summaryBase)->where('status', 'active')->count(),
        ];

        return view('livewire.admin.report.salary-advance-report-component', [
            'records' => $records,
            'summary' => $summary,
        ])
        ->layout('layouts.itsupport.app', [
            'title' => 'Reports | ' . institution()->name,
            'breadcrumbs' => [
                ['name' => 'Salary Advance Report', 'url' => route('itsupport.reports.salary.advances')],
            ],
        ]);
    }
}
