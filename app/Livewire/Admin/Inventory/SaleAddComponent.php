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
use Illuminate\Support\Facades\DB;

class SaleAddComponent extends Component
{
    // ── Sale header fields ──
    public string     $role           = '';
    public int|string $class_id       = '';
    public int|string $saleable_id    = '';
    public string     $bill_no        = '';
    public string     $date           = '';

    // ── Bill summary fields ──
    public float      $sub_total       = 0;
    public float      $total_discount  = 0;
    public float      $net_payable     = 0;
    public float      $received_amount = 0;
    public string     $pay_via         = '';
    public string     $remarks         = '';

    // ── Line items ──
    public array $items = [];

    protected function rules(): array
    {
        return [
            'role'                         => 'required|string|in:student,employee,other',
            'class_id'                     => 'nullable|integer',
            'saleable_id'                  => 'required|integer',
            'bill_no'                      => 'required|string|max:255|unique:inventory_sales,bill_no',
            'date'                         => 'required|date',
            'received_amount'              => 'nullable|numeric|min:0',
            'pay_via'                      => 'nullable|string|max:100',
            'remarks'                      => 'nullable|string|max:1000',
            'items'                        => 'required|array|min:1',
            'items.*.category_id'          => 'nullable|integer|exists:inventory_categories,id',
            'items.*.product_id'           => 'required|integer|exists:inventory_products,id',
            'items.*.unit_price'           => 'required|numeric|min:0',
            'items.*.quantity'             => 'required|integer|min:1',
            'items.*.discount'             => 'nullable|numeric|min:0',
        ];
    }

    protected function messages(): array
    {
        return [
            'role.required'                    => 'Role is required.',
            'saleable_id.required'             => 'Please select a sale target.',
            'items.required'                   => 'At least one item is required.',
            'items.min'                        => 'At least one item is required.',
            'items.*.product_id.required'      => 'Product is required.',
            'items.*.unit_price.required'      => 'Unit price is required.',
            'items.*.quantity.required'        => 'Quantity is required.',
            'items.*.quantity.min'             => 'Quantity must be at least 1.',
        ];
    }

    public function mount(): void
    {
        $this->date    = now()->format('Y-m-d');
        $this->bill_no = $this->generateBillNo();
        $this->addItem();
    }

    // ── Reset saleables when role changes ──
    public function updatedRole(): void
    {
        $this->saleable_id = '';
        $this->class_id    = '';
        $this->resetValidation(['saleable_id', 'class_id']);
    }

    // ── Reset saleables when class changes (for student filter) ──
    public function updatedClassId(): void
    {
        $this->saleable_id = '';
        $this->resetValidation('saleable_id');
    }

    // ── Reset product when category changes in a row ──
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

    // ── Recalculate received_amount triggers due_amount update ──
    public function updatedReceivedAmount(): void
    {
        $this->recalculate();
    }

    // ── Add a blank item row ──
    public function addItem(): void
    {
        $this->items[] = [
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
            'student' => Student::class,
            'employee'   => Employee::class,
            default   => \App\Models\User::class,
        };
    }

    // ── Determine payment_status ──
    private function paymentStatus(): string
    {
        $received = (float) $this->received_amount;
        if ($received <= 0)                        return 'due';
        if ($received >= $this->net_payable)       return 'paid';
        return 'partial';
    }

    // ── Generate next bill number ──
    private function generateBillNo(): string
    {
        $last = InventorySale::latest('id')->value('bill_no');
        $next = $last ? ((int) preg_replace('/\D/', '', $last)) + 1 : 1;
        return str_pad($next, 4, '0', STR_PAD_LEFT);
    }

    // ── Save sale + items in a transaction ──
    public function save(): void
    {
        $this->validate();

        DB::transaction(function () {

            $due = max(0, $this->net_payable - (float) $this->received_amount);

            $sale = InventorySale::create([
                'role'             => $this->role,
                'saleable_id'      => $this->saleable_id,
                'saleable_type'    => $this->saleableType(),
                'bill_no'          => $this->bill_no,
                'date'             => $this->date,
                'sub_total'        => $this->sub_total,
                'discount'         => $this->total_discount,
                'net_payable'      => $this->net_payable,
                'received_amount'  => $this->received_amount ?: 0,
                'due_amount'       => $due,
                'pay_via'          => $this->pay_via ?: null,
                'payment_status'   => $this->paymentStatus(),
                'remarks'          => $this->remarks ?: null,
            ]);

            foreach ($this->items as $item) {
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
        });

        $this->dispatch('toast', type: 'success', message: 'Data created successfully!');
        $this->resetForm();
    }

    public function resetForm(): void
    {
        $this->reset([
            'role', 'class_id', 'saleable_id',
            'bill_no', 'remarks', 'items',
            'pay_via', 'received_amount',
            'sub_total', 'total_discount', 'net_payable',
        ]);
        $this->date    = now()->format('Y-m-d');
        $this->bill_no = $this->generateBillNo();
        $this->resetValidation();
        $this->addItem();
    }

    public function render()
    {
        // Build saleables list dynamically based on role
        $saleables = collect();

        if ($this->role === 'student') {
            $saleables = Student::query()
                ->when($this->class_id, fn($q) => $q->where('class_id', $this->class_id))
                ->orderBy('name')
                ->get(['id', 'name']);
        } elseif ($this->role === 'employee') {
            $saleables = Employee::orderBy('name')->get(['id', 'name']);
        }

        return view('livewire.admin.inventory.sale-add-component', [
            'categories' => InventoryCategory::with('products')->orderBy('name')->get(),
            'classes'    => AcademicClass::orderBy('name')->get(),
            'saleables'  => $saleables,
        ])->layout('layouts.admin.app', [
            'title' => 'Add Sale | School SaaS',
        ]);
    }
}