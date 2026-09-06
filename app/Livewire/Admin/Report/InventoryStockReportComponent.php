<?php

namespace App\Livewire\Admin\Report;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\InventoryProduct;
use App\Models\InventoryCategory;
use App\Models\Branch;
use Illuminate\Support\Facades\DB;

class InventoryStockReportComponent extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    public const SORTABLE_FIELDS = ['name', 'purchased_qty', 'sold_qty', 'current_stock'];

    public string $sortField     = 'name';
    public string $sortDirection = 'asc';

    // ---------- Filters ----------
    public string $categoryId       = 'all';
    public string $search           = '';
    public bool   $lowStockOnly     = false;
    public int    $lowStockThreshold = 10;
    public int    $perPage          = 15;

    private function activeBranchId(): ?int
    {
        return auth()->user()->branch_id
            ?? Branch::resolveMainBranchId(institution()->id);
    }

    public function updatingCategoryId(): void { $this->resetPage(); }
    public function updatingSearch(): void     { $this->resetPage(); }
    public function updatingLowStockOnly(): void { $this->resetPage(); }

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
     * Product-wise purchased vs sold quantity aggregate করে current stock বের করা হয়।
     * কোনো আলাদা stock টেবিল নেই বলে এখানে:
     *   current_stock = SUM(purchase items.quantity) - SUM(sale items.quantity)
     */
    protected function baseQuery()
    {
        $institutionId = institution()->id;
        $branchId      = $this->activeBranchId();

        $purchasedSub = DB::table('inventory_purchase_items')
            ->select('product_id', DB::raw('SUM(quantity) as purchased_qty'))
            ->where('institution_id', $institutionId)
            ->where('branch_id', $branchId)
            ->groupBy('product_id');

        $soldSub = DB::table('inventory_sale_items')
            ->select('product_id', DB::raw('SUM(quantity) as sold_qty'))
            ->where('institution_id', $institutionId)
            ->where('branch_id', $branchId)
            ->groupBy('product_id');

        return InventoryProduct::query()
            ->where('inventory_products.institution_id', $institutionId)
            ->where('inventory_products.branch_id', $branchId)
            ->leftJoinSub($purchasedSub, 'purchased', function ($join) {
                $join->on('inventory_products.id', '=', 'purchased.product_id');
            })
            ->leftJoinSub($soldSub, 'sold', function ($join) {
                $join->on('inventory_products.id', '=', 'sold.product_id');
            })
            ->select(
                'inventory_products.*',
                DB::raw('COALESCE(purchased.purchased_qty, 0) as purchased_qty'),
                DB::raw('COALESCE(sold.sold_qty, 0) as sold_qty'),
                DB::raw('COALESCE(purchased.purchased_qty, 0) - COALESCE(sold.sold_qty, 0) as current_stock'),
                DB::raw('(COALESCE(purchased.purchased_qty, 0) - COALESCE(sold.sold_qty, 0)) * inventory_products.purchase_price as stock_value')
            )
            ->when($this->categoryId !== 'all', fn ($q) => $q->where('inventory_products.category_id', $this->categoryId))
            ->when($this->search !== '', function ($q) {
                $q->where(function ($inner) {
                    $inner->where('inventory_products.name', 'like', "%{$this->search}%")
                          ->orWhere('inventory_products.code', 'like', "%{$this->search}%");
                });
            })
            ->when($this->lowStockOnly, function ($q) {
                $q->havingRaw('current_stock <= ?', [$this->lowStockThreshold]);
            });
    }

    public function render()
    {
        $sortColumn = $this->sortField === 'name' ? 'inventory_products.name' : $this->sortField;

        $records = $this->baseQuery()
            ->with(['category:id,name', 'purchaseUnit:id,name', 'salesUnit:id,name'])
            ->orderBy($sortColumn, $this->sortDirection)
            ->paginate($this->perPage);

        // Summary — category/search filter সহ কিন্তু low-stock toggle ছাড়া (পুরো ছবি একবারে দেখানোর জন্য),
        // একটাই query execute করে collection থেকে সব হিসাব বের করা হয়েছে (repeated query এড়ানোর জন্য)
        $lowStockOnlyBackup   = $this->lowStockOnly;
        $this->lowStockOnly   = false;
        $summaryRows          = $this->baseQuery()->get(['inventory_products.*']);
        $this->lowStockOnly   = $lowStockOnlyBackup;

        $summary = [
            'total_products'     => $summaryRows->count(),
            'total_stock_value'  => $summaryRows->sum('stock_value'),
            'low_stock'          => $summaryRows->where('current_stock', '<=', $this->lowStockThreshold)->where('current_stock', '>', 0)->count(),
            'out_of_stock'       => $summaryRows->where('current_stock', '<=', 0)->count(),
        ];

        $categories = InventoryCategory::where('institution_id', institution()->id)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('livewire.admin.report.inventory-stock-report-component', [
            'records'    => $records,
            'summary'    => $summary,
            'categories' => $categories,
        ])
        ->layout('layouts.admin.app', [
            'title' => 'Reports | ' . institution()->name,
            'breadcrumbs' => [
                ['name' => 'Stock Report', 'url' => route('admin.reports.inventory.stock')],
            ],
        ]);
    }
}