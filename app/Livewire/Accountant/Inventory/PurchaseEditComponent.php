<?php

namespace App\Livewire\Accountant\Inventory;

use Livewire\Component;
use App\Models\InventoryPurchase;
use App\Models\InventoryPurchaseItem;
use App\Models\InventorySupplier;
use App\Models\InventoryStore;
use App\Models\InventoryProduct;
use Illuminate\Support\Facades\DB;

class PurchaseEditComponent extends Component
{
    // ── Route model ──
    public int $purchaseId;

    // ── Purchase header fields ──
    public int|string $supplier_id     = '';
    public int|string $store_id        = '';
    public string     $bill_no         = '';
    public string     $purchase_status = 'pending';
    public string     $date            = '';
    public string     $remarks         = '';

    // ── Line items ──
    public array $items = [];

    // ── Computed total ──
    public float $net_total = 0;

    protected function rules(): array
    {
        return [
            'supplier_id'              => 'required|integer|exists:inventory_suppliers,id',
            'store_id'                 => 'required|integer|exists:inventory_stores,id',
            'bill_no'                  => "required|string|max:255|unique:inventory_purchases,bill_no,{$this->purchaseId}",
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

    public function mount(int $id): void
    {
        $purchase = InventoryPurchase::with('items')->findOrFail($id);

        $this->purchaseId      = $purchase->id;
        $this->supplier_id     = $purchase->supplier_id;
        $this->store_id        = $purchase->store_id;
        $this->bill_no         = $purchase->bill_no;
        $this->purchase_status = $purchase->purchase_status;
        $this->date            = $purchase->date;
        $this->remarks         = $purchase->remarks ?? '';

        $this->items = $purchase->items->map(fn($item) => [
            'id'          => $item->id,
            'product_id'  => $item->product_id,
            'unit_price'  => $item->unit_price,
            'quantity'    => $item->quantity,
            'discount'    => $item->discount,
            'total_price' => $item->total_price,
        ])->toArray();

        $this->recalculate();
    }

    // ── Add a blank item row ──
    public function addItem(): void
    {
        $this->items[] = [
            'id'          => null,
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

    // ── Product select হলে purchase_price auto-fill হবে,
    //    অন্য field change হলে শুধু recalculate হবে ──
    public function updatedItems($value, $key): void
    {
        $parts = explode('.', $key);
        $index = (int) $parts[0];
        $field = $parts[1] ?? '';

        if ($field === 'product_id' && !empty($value)) {
            $product = InventoryProduct::find($value);
            if ($product) {
                $this->items[$index]['unit_price'] = $product->purchase_price;
            }
        }

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

    // ── Save: update purchase header + sync items ──
    public function save(): void
    {
        $this->validate();

        DB::transaction(function () {

            $purchase = InventoryPurchase::findOrFail($this->purchaseId);

            $purchase->update([
                'supplier_id'     => $this->supplier_id,
                'store_id'        => $this->store_id,
                'bill_no'         => $this->bill_no,
                'purchase_status' => $this->purchase_status,
                'date'            => $this->date,
                'net_total'       => $this->net_total,
                'remarks'         => $this->remarks ?: null,
            ]);

            // Form-এ যে item-গুলো এখনও আছে তাদের ID collect করো
            $keptIds = collect($this->items)
                ->pluck('id')
                ->filter()
                ->values()
                ->toArray();

            // Form থেকে বাদ দেওয়া item-গুলো DB থেকে delete করো
            InventoryPurchaseItem::where('purchase_id', $purchase->id)
                ->whereNotIn('id', $keptIds)
                ->delete();

            // পুরনো item update করো, নতুন item create করো
            foreach ($this->items as $item) {
                if (!empty($item['id'])) {
                    InventoryPurchaseItem::where('id', $item['id'])
                        ->update([
                            'product_id'  => $item['product_id'],
                            'unit_price'  => $item['unit_price'],
                            'quantity'    => $item['quantity'],
                            'discount'    => $item['discount'] ?? 0,
                            'total_price' => $item['total_price'],
                        ]);
                } else {
                    InventoryPurchaseItem::create([
                        'purchase_id' => $purchase->id,
                        'product_id'  => $item['product_id'],
                        'unit_price'  => $item['unit_price'],
                        'quantity'    => $item['quantity'],
                        'discount'    => $item['discount'] ?? 0,
                        'total_price' => $item['total_price'],
                    ]);
                }
            }
        });

        $this->dispatch('toast', type: 'success', message: 'Data updated successfully!');
    }

    public function render()
    {
        return view('livewire.accountant.inventory.purchase-edit-component', [
            'suppliers' => InventorySupplier::orderBy('name')->get(),
            'stores'    => InventoryStore::orderBy('name')->get(),
            'products'  => InventoryProduct::orderBy('name')->get(),
        ])->layout('layouts.accountant.app', [
            'title' => 'Edit Purchase | ' . institution()->name,
        ]);
    }
}