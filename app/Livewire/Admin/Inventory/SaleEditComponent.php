<?php

namespace App\Livewire\Admin\Inventory;

use Livewire\Component;
use App\Models\InventorySale;
use App\Models\InventorySaleItem;
use App\Models\InventoryCategory;
use App\Models\InventoryProduct;
use App\Models\AcademicClass;
use App\Models\Student;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SaleEditComponent extends Component
{
 // ── Route model ──
    public int $saleId;

    // ── Sale header fields ──
    public string     $role           = '';
    public int|string $class_id       = '';
    public int|string $saleable_id    = '';
    public string     $bill_no        = '';
    public string     $date           = '';

    // ── Bill summary fields ──
    public float  $sub_total       = 0;
    public float  $total_discount  = 0;
    public float  $net_payable     = 0;
    public float  $received_amount = 0;
    public string $pay_via         = '';
    public string $remarks         = '';

    // ── Line items ──
    public array $items = [];

    protected function rules(): array
    {
        return [
            'role'                    => 'required|string|in:student,teacher,staff,other',
            'class_id'                => 'nullable|integer',
            'saleable_id'             => 'required|integer',
            'bill_no'                 => "required|string|max:255|unique:inventory_sales,bill_no,{$this->saleId}",
            'date'                    => 'required|date',
            'received_amount'         => 'nullable|numeric|min:0',
            'pay_via'                 => 'nullable|string|max:100',
            'remarks'                 => 'nullable|string|max:1000',
            'items'                   => 'required|array|min:1',
            'items.*.category_id'     => 'nullable|integer|exists:inventory_categories,id',
            'items.*.product_id'      => 'required|integer|exists:inventory_products,id',
            'items.*.unit_price'      => 'required|numeric|min:0',
            'items.*.quantity'        => 'required|integer|min:1',
            'items.*.discount'        => 'nullable|numeric|min:0',
        ];
    }

    protected function messages(): array
    {
        return [
            'role.required'               => 'Role is required.',
            'saleable_id.required'        => 'Please select a sale target.',
            'items.required'              => 'At least one item is required.',
            'items.min'                   => 'At least one item is required.',
            'items.*.product_id.required' => 'Product is required.',
            'items.*.unit_price.required' => 'Unit price is required.',
            'items.*.quantity.required'   => 'Quantity is required.',
            'items.*.quantity.min'        => 'Quantity must be at least 1.',
        ];
    }

    public function mount(int $id): void
    {
        $sale = InventorySale::with('items')->findOrFail($id);

        $this->saleId          = $sale->id;
        $this->role            = $sale->role ?? '';
        $this->saleable_id     = $sale->saleable_id;
        $this->bill_no         = $sale->bill_no;
        $this->date            = $sale->date;
        // $this->date            = $sale->date->format('Y-m-d');
        $this->received_amount = (float) $sale->received_amount;
        $this->pay_via         = $sale->pay_via ?? '';
        $this->remarks         = $sale->remarks ?? '';

        // Resolve class_id for student role
        if ($this->role === 'student') {
            $student = Student::find($sale->saleable_id);
            $this->class_id = $student?->class_id ?? '';
        }

        $this->items = $sale->items->map(fn($item) => [
            'id'          => $item->id,
            'category_id' => $item->category_id ?? '',
            'product_id'  => $item->product_id,
            'unit_price'  => $item->unit_price,
            'quantity'    => $item->quantity,
            'discount'    => $item->discount,
            'total_price' => $item->total_price,
        ])->toArray();

        $this->recalculate();
    }

    // ── Reset saleables when role changes ──
    public function updatedRole(): void
    {
        $this->saleable_id = '';
        $this->class_id    = '';
        $this->resetValidation(['saleable_id', 'class_id']);
    }

    // ── Reset saleables when class changes ──
    public function updatedClassId(): void
    {
        $this->saleable_id = '';
        $this->resetValidation('saleable_id');
    }

    // ── Handle item field changes ──
    public function updatedItems($value, $key): void
    {
        $parts = explode('.', $key);
        $index = (int) $parts[0];
        $field = $parts[1] ?? '';

        // Clear product when category changes
        if ($field === 'category_id') {
            $this->items[$index]['product_id']  = '';
            $this->items[$index]['unit_price']  = '';
            $this->items[$index]['total_price'] = 0;
        }

        // Auto-fill unit price when product is selected
        if ($field === 'product_id' && !empty($value)) {
            $product = InventoryProduct::find($value);
            if ($product) {
                $this->items[$index]['unit_price'] = $product->price ?? 0;
            }
        }

        $this->recalculateRow($index);
        $this->recalculate();
    }

    // ── Recalculate when received amount changes ──
    public function updatedReceivedAmount(): void
    {
        $this->recalculate();
    }

    // ── Add a blank item row ──
    public function addItem(): void
    {
        $this->items[] = [
            'id'          => null,
            'category_id' => '',
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
        $this->sub_total      = collect($this->items)->sum(fn($i) => (float)($i['unit_price'] ?? 0) * (int)($i['quantity'] ?? 1));
        $this->total_discount = collect($this->items)->sum(fn($i) => (float)($i['discount'] ?? 0));
        $this->net_payable    = max(0, $this->sub_total - $this->total_discount);
    }

    // ── Determine saleable_type from role ──
    private function saleableType(): string
    {
        return match($this->role) {
            'student'  => User::class,
            'teacher'  => User::class,
            'staff'    => User::class,
            default    => \App\Models\User::class,
        };
    }

    // ── Determine payment_status ──
    private function paymentStatus(): string
    {
        $received = (float) $this->received_amount;
        if ($received <= 0)                  return 'due';
        if ($received >= $this->net_payable) return 'paid';
        return 'partial';
    }

    // ── Save: update sale + sync items ──
    public function save(): void
    {
        $this->validate();

        DB::transaction(function () {

            $due = max(0, $this->net_payable - (float) $this->received_amount);

            $sale = InventorySale::findOrFail($this->saleId);

            $sale->update([
                'role'            => $this->role,
                'saleable_id'     => $this->saleable_id,
                'saleable_type'   => $this->saleableType(),
                'bill_no'         => $this->bill_no,
                'date'            => $this->date,
                'sub_total'       => $this->sub_total,
                'discount'        => $this->total_discount,
                'net_payable'     => $this->net_payable,
                'received_amount' => $this->received_amount ?: 0,
                'due_amount'      => $due,
                'pay_via'         => $this->pay_via ?: null,
                'payment_status'  => $this->paymentStatus(),
                'remarks'         => $this->remarks ?: null,
            ]);

            // IDs still present in form
            $keptIds = collect($this->items)
                ->pluck('id')
                ->filter()
                ->values()
                ->toArray();

            // Delete removed items
            InventorySaleItem::where('sale_id', $sale->id)
                ->whereNotIn('id', $keptIds)
                ->delete();

            // Update existing / create new
            foreach ($this->items as $item) {
                if (!empty($item['id'])) {
                    InventorySaleItem::where('id', $item['id'])->update([
                        'category_id' => $item['category_id'] ?: null,
                        'product_id'  => $item['product_id'],
                        'unit_price'  => $item['unit_price'],
                        'quantity'    => $item['quantity'],
                        'discount'    => $item['discount'] ?? 0,
                        'total_price' => $item['total_price'],
                    ]);
                } else {
                    InventorySaleItem::create([
                        'sale_id'     => $sale->id,
                        'category_id' => $item['category_id'] ?: null,
                        'product_id'  => $item['product_id'],
                        'unit_price'  => $item['unit_price'],
                        'quantity'    => $item['quantity'],
                        'discount'    => $item['discount'] ?? 0,
                        'total_price' => $item['total_price'],
                    ]);
                }
            }
        });

        $this->dispatch('date-updated', date: $this->date);
        $this->dispatch('toast', type: 'success', message: 'Data updated successfully!');
    }

    public function render()
    {
        $saleables = collect();

        if ($this->role === 'student') {
            $saleables = Student::query()
                ->when($this->class_id, fn($q) => $q->where('class_id', $this->class_id))
                ->orderBy('name')
                ->get(['id', 'name']);
        } elseif ($this->role === 'teacher') {
            $saleables = User::where('school_id', auth()->user()->school_id)->where('role', 'teacher')->orderBy('name')->get(['id', 'name']);
        } elseif ($this->role === 'staff') {
            $saleables = User::where('school_id', auth()->user()->school_id)->where('role', 'staff')->orderBy('name')->get(['id', 'name']);
        }

        return view('livewire.admin.inventory.sale-edit-component', [
            'categories' => InventoryCategory::with('products')->orderBy('name')->get(),
            'classes'    => AcademicClass::orderBy('name')->get(),
            'saleables'  => $saleables,
        ])->layout('layouts.admin.app', [
            'title' => 'Edit Sale | School SaaS',
        ]);
    }
}