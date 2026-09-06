<?php

namespace App\Livewire\Admin\Report;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\SalaryPayment;
use App\Models\EmployeeDesignation;
use App\Models\Branch;
use Illuminate\Support\Facades\DB;

class SalaryPaymentReportComponent extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    public const SORTABLE_FIELDS = ['month', 'gross_salary', 'net_salary', 'status', 'payment_date', 'created_at'];

    protected array $sortColumnMap = [
        'month'         => 'salary_payments.month',
        'gross_salary'  => 'salary_payments.gross_salary',
        'net_salary'    => 'salary_payments.net_salary',
        'status'        => 'salary_payments.status',
        'payment_date'  => 'salary_payments.payment_date',
        'created_at'    => 'salary_payments.created_at',
    ];

    public string $sortField     = 'month';
    public string $sortDirection = 'desc';

    // ---------- Filters ----------
    public string $monthFrom;
    public string $monthTo;
    public string $designationId  = 'all';
    public string $status         = 'all';
    public string $paymentMethod  = 'all';
    public string $search         = '';
    public int    $perPage        = 15;

    public function mount(): void
    {
        $this->monthFrom = now()->startOfYear()->format('Y-m-01');
        $this->monthTo   = now()->format('Y-m-01');
    }

    private function activeBranchId(): ?int
    {
        return auth()->user()->branch_id
            ?? Branch::resolveMainBranchId(institution()->id);
    }

    public function updatingMonthFrom(): void      { $this->resetPage(); }
    public function updatingMonthTo(): void        { $this->resetPage(); }
    public function updatingDesignationId(): void  { $this->resetPage(); }
    public function updatingPaymentMethod(): void  { $this->resetPage(); }
    public function updatingStatus(): void         { $this->resetPage(); }
    public function updatingSearch(): void         { $this->resetPage(); }

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
     * Common filters (month range, designation, pay method, search) ছাড়া status filter।
     * Summary count-এর জন্য status বাদে বাকি সব filter দরকার হয়, তাই আলাদা রাখা হলো।
     */
    protected function baseQueryWithoutStatus()
    {
        $institutionId = institution()->id;
        $branchId      = $this->activeBranchId();

        return SalaryPayment::query()
            ->select('salary_payments.*')
            ->where('institution_id', $institutionId)
            ->where('branch_id', $branchId)
            ->whereBetween('month', [$this->monthFrom, $this->monthTo])
            ->when($this->designationId !== 'all', function ($q) {
                $q->whereHas('employee', fn ($sub) => $sub->where('designation_id', $this->designationId));
            })
            ->when($this->paymentMethod !== 'all', fn ($q) => $q->where('payment_method', $this->paymentMethod))
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
        $sortColumn = $this->sortColumnMap[$this->sortField] ?? 'salary_payments.month';

        $records = $this->baseQuery()
            ->with(['employee:id,name,employee_id,designation_id', 'employee.designation:id,name', 'account:id,name', 'paidBy:id,name'])
            ->orderBy($sortColumn, $this->sortDirection)
            ->paginate($this->perPage);

        // Summary — একই ফিল্টার (মাস, designation, pay method, search) দিয়ে, শুধু status বাদে
        $summaryBase = $this->baseQueryWithoutStatus();

        $summary = [
            'total_payslips'  => (clone $summaryBase)->count(),
            'total_net'       => (clone $summaryBase)->sum('net_salary'),
            'paid_count'      => (clone $summaryBase)->where('status', 'paid')->count(),
            'pending_count'   => (clone $summaryBase)->whereIn('status', ['unpaid', 'partial'])->count(),
        ];

        $designations = EmployeeDesignation::where('institution_id', institution()->id)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('livewire.admin.report.salary-payment-report-component', [
            'records'      => $records,
            'summary'      => $summary,
            'designations' => $designations,
        ])
        ->layout('layouts.admin.app', [
            'title' => 'Reports | ' . institution()->name,
            'breadcrumbs' => [
                ['name' => 'Salary Payment Report', 'url' => route('admin.reports.salary.payments')],
            ],
        ]);
    }
}
