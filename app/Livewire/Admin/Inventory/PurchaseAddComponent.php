<?php

namespace App\Livewire\Admin\Inventory;

use Livewire\Component;
use App\Models\InventoryPurchase;
use App\Models\InventoryPurchaseItem;
use App\Models\InventorySupplier;
use App\Models\InventoryStore;
use App\Models\InventoryProduct;
use Illuminate\Support\Facades\DB;

class PurchaseAddComponent extends Component
{
    // ── Purchase header fields ──
    public int|string $supplier_id   = '';
    public int|string $store_id      = '';
    public string     $bill_no       = '';
    public string     $purchase_status = 'pending';
    public string     $date          = '';
    public string     $remarks       = '';

    // ── Line items ──
    public array $items = [];

    // ── Computed total (kept in sync on every item change) ──
    public float $net_total = 0;

    protected function rules(): array
    {
        return [
            'supplier_id'              => 'required|integer|exists:inventory_suppliers,id',
            'store_id'                 => 'required|integer|exists:inventory_stores,id',
            'bill_no'                  => 'required|string|max:255|unique:inventory_purchases,bill_no',
            'purchase_status'          => 'required|in:pending,ordered,completed,received,cancelled',
            'date'                     => 'required|date',
            'remarks'                  => 'nullable|string|max:1000',
            'items'                    => 'required|array|min:1',
            'items.*.product_id'       => 'required|integer|exists:inventory_products,id',
            'items.*.unit_price'       => 'required|numeric|min:0',
            'items.*.quantity'         => 'required|integer|min:1',
            'items.*.discount'         => 'nullable|numeric|min:0',
        ];
    }

    protected function messages(): array
    {
        return [
            'items.required'               => 'At least one purchase item is required.',
            'items.min'                    => 'At least one purchase item is required.',
            'items.*.product_id.required'  => 'Product is required.',
            'items.*.unit_price.required'  => 'Unit price is required.',
            'items.*.quantity.required'    => 'Quantity is required.',
            'items.*.quantity.min'         => 'Quantity must be at least 1.',
        ];
    }

    public function mount(): void
    {
        $this->date = now()->format('Y-m-d');
        $this->addItem();
    }

    // ── Add a blank item row ──
    public function addItem(): void
    {
        $this->items[] = [
            'product_id'  => '',
            'unit_price'  => '',
            'quantity'    => 1,
            'discount'    => 0,
            'total_price' => 0,
        ];
    }

    // ── Remove an item row ──
    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
        $this->recalculate();
    }

    // ── Recalculate a row's total and the net total whenever any item field changes ──
    public function updatedItems($value, $key): void
    {
        // $key is like "0.unit_price" or "1.quantity"
        $index = (int) explode('.', $key)[0];
        $this->recalculateRow($index);
        $this->recalculate();
    }

    private function recalculateRow(int $index): void
    {
        if (!isset($this->items[$index])) return;

        $row      = $this->items[$index];
        $price    = (float) ($row['unit_price'] ?? 0);
        $qty      = (int)   ($row['quantity']   ?? 1);
        $discount = (float) ($row['discount']   ?? 0);

        $this->items[$index]['total_price'] = max(0, ($price * $qty) - $discount);
    }

    private function recalculate(): void
    {
        $this->net_total = collect($this->items)->sum('total_price');
    }

    // ── Save purchase + items in a transaction ──
    public function save(): void
    {
        $this->validate();

        DB::transaction(function () {

            $purchase = InventoryPurchase::create([
                'supplier_id'     => $this->supplier_id,
                'store_id'        => $this->store_id,
                'bill_no'         => $this->bill_no,
                'purchase_status' => $this->purchase_status,
                'date'            => $this->date,
                'net_total'       => $this->net_total,
                'remarks'         => $this->remarks ?: null,
            ]);

            foreach ($this->items as $item) {
                InventoryPurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id'  => $item['product_id'],
                    'unit_price'  => $item['unit_price'],
                    'quantity'    => $item['quantity'],
                    'discount'    => $item['discount'] ?? 0,
                    'total_price' => $item['total_price'],
                ]);
            }
        });

        $this->dispatch('toast', type: 'success', message: 'Data created successfully!');
        $this->resetForm();
    }

    public function resetForm(): void
    {
        $this->reset([
            'supplier_id', 'store_id', 'bill_no',
            'remarks', 'items', 'net_total',
        ]);
        $this->purchase_status = 'pending';
        $this->date            = now()->format('Y-m-d');
        $this->resetValidation();
        $this->addItem();
    }

    public function render()
    {
        return view('livewire.admin.inventory.purchase-add-component', [
            'suppliers' => InventorySupplier::orderBy('name')->get(),
            'stores'    => InventoryStore::orderBy('name')->get(),
            'products'  => InventoryProduct::orderBy('name')->get(),
        ])->layout('layouts.admin.app', [
            'title' => 'Add Purchase | School SaaS',
        ]);
    }
}