<?php

namespace App\Livewire\ITSupport\Report;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\InventoryPurchase;
use App\Models\InventorySupplier;
use App\Models\InventoryStore;
use App\Models\Branch;

class InventoryPurchaseReportComponent extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    public const SORTABLE_FIELDS = ['date', 'bill_no', 'net_total', 'purchase_status', 'created_at'];

    protected array $sortColumnMap = [
        'date'            => 'inventory_purchases.date',
        'bill_no'         => 'inventory_purchases.bill_no',
        'net_total'       => 'inventory_purchases.net_total',
        'purchase_status' => 'inventory_purchases.purchase_status',
        'created_at'      => 'inventory_purchases.created_at',
    ];

    public string $sortField     = 'date';
    public string $sortDirection = 'desc';

    // ---------- Filters ----------
    public string $dateFrom;
    public string $dateTo;
    public string $purchaseStatus = 'all';
    public string $supplierId     = 'all';
    public string $storeId        = 'all';
    public string $search         = '';
    public int    $perPage        = 15;

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

    public function updatingDateFrom(): void       { $this->resetPage(); }
    public function updatingDateTo(): void         { $this->resetPage(); }
    public function updatingPurchaseStatus(): void { $this->resetPage(); }
    public function updatingSupplierId(): void     { $this->resetPage(); }
    public function updatingStoreId(): void        { $this->resetPage(); }
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
     * Common filters (date range, supplier, store, search) ছাড়া purchase_status filter।
     * Summary count-এর জন্য purchase_status বাদে বাকি সব filter দরকার হয়, তাই আলাদা রাখা হলো।
     */
    protected function baseQueryWithoutStatus()
    {
        $institutionId = institution()->id;
        $branchId      = $this->activeBranchId();

        return InventoryPurchase::query()
            ->select('inventory_purchases.*')
            ->where('institution_id', $institutionId)
            ->where('branch_id', $branchId)
            ->whereBetween('date', [$this->dateFrom, $this->dateTo])
            ->when($this->supplierId !== 'all', fn ($q) => $q->where('supplier_id', $this->supplierId))
            ->when($this->storeId !== 'all', fn ($q) => $q->where('store_id', $this->storeId))
            ->when($this->search !== '', fn ($q) => $q->where('bill_no', 'like', "%{$this->search}%"));
    }

    protected function baseQuery()
    {
        return $this->baseQueryWithoutStatus()
            ->when($this->purchaseStatus !== 'all', fn ($q) => $q->where('purchase_status', $this->purchaseStatus));
    }

    public function render()
    {
        $sortColumn = $this->sortColumnMap[$this->sortField] ?? 'inventory_purchases.date';

        $records = $this->baseQuery()
            ->with(['supplier:id,name', 'store:id,name'])
            ->withCount('items')
            ->orderBy($sortColumn, $this->sortDirection)
            ->paginate($this->perPage);

        $summaryBase = $this->baseQueryWithoutStatus();

        $summary = [
            'total_bills'  => (clone $summaryBase)->count(),
            'total_amount' => (clone $summaryBase)->sum('net_total'),
            'pending'      => (clone $summaryBase)->where('purchase_status', 'pending')->count(),
            'received'     => (clone $summaryBase)->where('purchase_status', 'received')->count(),
        ];

        $suppliers = InventorySupplier::where('institution_id', institution()->id)
            ->orderBy('name')
            ->get(['id', 'name']);

        $stores = InventoryStore::where('institution_id', institution()->id)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('livewire.admin.report.inventory-purchase-report-component', [
            'records'   => $records,
            'summary'   => $summary,
            'suppliers' => $suppliers,
            'stores'    => $stores,
        ])
        ->layout('layouts.itsupport.app', [
            'title' => 'Reports | ' . institution()->name,
            'breadcrumbs' => [
                ['name' => 'Purchase Report', 'url' => route('itsupport.reports.inventory.purchases')],
            ],
        ]);
    }
}