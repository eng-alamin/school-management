<?php

namespace App\Livewire\Admin\Report;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\InventorySale;
use App\Models\Branch;
use Illuminate\Support\Facades\DB;

class InventorySaleReportComponent extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    public const SORTABLE_FIELDS = ['date', 'bill_no', 'net_payable', 'received_amount', 'due_amount', 'payment_status', 'created_at'];

    protected array $sortColumnMap = [
        'date'             => 'inventory_sales.date',
        'bill_no'          => 'inventory_sales.bill_no',
        'net_payable'      => 'inventory_sales.net_payable',
        'received_amount'  => 'inventory_sales.received_amount',
        'due_amount'       => 'inventory_sales.due_amount',
        'payment_status'   => 'inventory_sales.payment_status',
        'created_at'       => 'inventory_sales.created_at',
    ];

    public string $sortField     = 'date';
    public string $sortDirection = 'desc';

    // ---------- Filters ----------
    public string $dateFrom;
    public string $dateTo;
    public string $paymentStatus = 'all';
    public string $payVia        = 'all';
    public string $search        = '';
    public int    $perPage       = 15;

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

    public function updatingDateFrom(): void      { $this->resetPage(); }
    public function updatingDateTo(): void        { $this->resetPage(); }
    public function updatingPaymentStatus(): void { $this->resetPage(); }
    public function updatingPayVia(): void        { $this->resetPage(); }
    public function updatingSearch(): void        { $this->resetPage(); }

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
     * Common filters (date range, pay-via, search) ছাড়া payment_status filter।
     * Summary count-এর জন্য payment_status বাদে বাকি সব filter দরকার হয়, তাই আলাদা রাখা হলো।
     */
    protected function baseQueryWithoutStatus()
    {
        $institutionId = institution()->id;
        $branchId      = $this->activeBranchId();

        return InventorySale::query()
            ->select('inventory_sales.*')
            ->where('institution_id', $institutionId)
            ->where('branch_id', $branchId)
            ->whereBetween('date', [$this->dateFrom, $this->dateTo])
            ->when($this->payVia !== 'all', fn ($q) => $q->where('pay_via', $this->payVia))
            ->when($this->search !== '', function ($q) {
                $q->where(function ($inner) {
                    $inner->where('bill_no', 'like', "%{$this->search}%")
                          ->orWhereHasMorph('saleable', '*', function ($sub) {
                              $sub->where('name', 'like', "%{$this->search}%");
                          });
                });
            });
    }

    protected function baseQuery()
    {
        return $this->baseQueryWithoutStatus()
            ->when($this->paymentStatus !== 'all', fn ($q) => $q->where('payment_status', $this->paymentStatus));
    }

    public function render()
    {
        $sortColumn = $this->sortColumnMap[$this->sortField] ?? 'inventory_sales.date';

        $records = $this->baseQuery()
            ->with(['saleable', 'items:id,sale_id,product_id,quantity'])
            ->withCount('items')
            ->orderBy($sortColumn, $this->sortDirection)
            ->paginate($this->perPage);

        // Summary — একই ফিল্টার (তারিখ, pay-via, search) দিয়ে, শুধু payment_status বাদে
        $summaryBase = $this->baseQueryWithoutStatus();

        $summary = [
            'total_bills'     => (clone $summaryBase)->count(),
            'total_net'       => (clone $summaryBase)->sum('net_payable'),
            'total_received'  => (clone $summaryBase)->sum('received_amount'),
            'total_due'       => (clone $summaryBase)->sum('due_amount'),
        ];

        return view('livewire.admin.report.inventory-sale-report-component', [
            'records' => $records,
            'summary' => $summary,
        ])
        ->layout('layouts.admin.app', [
            'title' => 'Reports | ' . institution()->name,
            'breadcrumbs' => [
                ['name' => 'Sale Report', 'url' => route('admin.reports.inventory.sales')],
            ],
        ]);
    }
}