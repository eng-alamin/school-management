<?php

namespace App\Livewire\ITSupport\Inventory;

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

    // ── Tracks whether user has manually edited Received Amount.
    //    While false, Received Amount auto-syncs to Net Payable.
    //    Once the user types into the field, auto-sync stops so we
    //    never overwrite a manually entered (e.g. partial) payment. ──
    public bool $receivedAmountTouched = false;

    // ── Line items ──
    public array $items = [];

    protected function rules(): array
    {
        return [
            'role'                         => 'required|string|in:student,teacher,staff,other',
            'class_id'                     => 'nullable|integer',
            'saleable_id'                  => 'required|integer',
            'bill_no'                      => 'required|string|max:255|unique:inventory_sales,bill_no,NULL,id,institution_id,' . institution()->id,
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

    // ── Role change হলে saleable reset ──
    public function updatedRole(): void
    {
        $this->saleable_id = '';
        $this->class_id    = '';
        $this->resetValidation(['saleable_id', 'class_id']);
    }

    // ── Class change হলে saleable reset ──
    public function updatedClassId(): void
    {
        $this->saleable_id = '';
        $this->resetValidation('saleable_id');
    }

    // ── Category change হলে product reset,
    //    Product select হলে sales_price auto-fill ──
    public function updatedItems($value, $key): void
    {
        $parts = explode('.', $key);
        $index = (int) $parts[0];
        $field = $parts[1] ?? '';

        // category_id change হলে সেই row-এর product ও price clear করো
        if ($field === 'category_id') {
            $this->items[$index]['product_id']  = '';
            $this->items[$index]['unit_price']  = '';
            $this->items[$index]['total_price'] = 0;
        }

        // product_id select হলে sales_price এনে unit_price-এ বসাও
        if ($field === 'product_id' && !empty($value)) {
            $product = InventoryProduct::where('institution_id', institution()->id)
                ->find($value);

            if ($product) {
                $this->items[$index]['unit_price'] = number_format($product->sales_price, 0);
            }
        }

        $this->recalculateRow($index);
        $this->recalculate();
    }

    // ── Received amount manually change হলে recalculate,
    //    এবং auto-sync বন্ধ করে দাও (user নিজে হাতে বসিয়েছে) ──
    public function updatedReceivedAmount(): void
    {
        $this->receivedAmountTouched = true;
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

        // ── Received Amount ডিফল্টভাবে Net Payable-এর সমান থাকবে,
        //    যতক্ষণ না ইউজার নিজে হাত দিয়ে amount পরিবর্তন করে ──
        if (!$this->receivedAmountTouched) {
            $this->received_amount = $this->net_payable;
        }
    }

    // ── Role থেকে saleable_type নির্ধারণ ──
    private function saleableType(): string
    {
        return match($this->role) {
            'student'  => User::class,
            'teacher'  => User::class,
            'staff'    => User::class,
            default    => \App\Models\User::class,
        };
    }

    // ── Payment status নির্ধারণ ──
    private function paymentStatus(): string
    {
        $received = (float) $this->received_amount;
        if ($received <= 0)                  return 'due';
        if ($received >= $this->net_payable) return 'paid';
        return 'partial';
    }

    // ── পরবর্তী bill number generate (নিজের institution-এর মধ্যে) ──
    private function generateBillNo(): string
    {
        $last = InventorySale::where('institution_id', institution()->id)
            ->latest('id')
            ->value('bill_no');

        $next = $last
            ? ((int) preg_replace('/\D/', '', $last)) + 1
            : 1;

        return 'BILL-' . str_pad($next, 4, '0', STR_PAD_LEFT);
    }

    // ── Save sale + items in a transaction ──
    public function save(): void
    {
        $this->validate();

        DB::transaction(function () {

            $due = max(0, $this->net_payable - (float) $this->received_amount);

            $sale = InventorySale::create([
                'institution_id'  => institution()->id,
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

            foreach ($this->items as $item) {
                InventorySaleItem::create([
                    'institution_id' => institution()->id,
                    'sale_id'        => $sale->id,
                    'category_id'    => $item['category_id'] ?: null,
                    'product_id'     => $item['product_id'],
                    'unit_price'     => $item['unit_price'],
                    'quantity'       => $item['quantity'],
                    'discount'       => $item['discount'] ?? 0,
                    'total_price'    => $item['total_price'],
                ]);
            }
        });

        session()->flash('toast_success', 'Data created successfully!');
        $this->redirectRoute('itsupport.inventory.sale.list', navigate: true);
    }

    public function resetForm(): void
    {
        $this->reset([
            'role', 'class_id', 'saleable_id',
            'bill_no', 'remarks', 'items',
            'pay_via', 'received_amount',
            'sub_total', 'total_discount', 'net_payable',
        ]);
        $this->receivedAmountTouched = false;
        $this->dispatch('date-updated', date: $this->date);
        $this->bill_no = $this->generateBillNo();
        $this->resetValidation();
        $this->addItem();
    }

    public function render()
    {
        $saleables = collect();

        if ($this->role === 'student') {
            $saleables = Student::query()
                ->where('institution_id', institution()->id)
                ->when($this->class_id, fn($q) => $q->where('class_id', $this->class_id))
                ->orderBy('id')
                ->get(['id', 'name']);
        } elseif ($this->role === 'teacher') {
            $saleables = User::where('institution_id', institution()->id)->where('role', 'teacher')->orderBy('name')->get(['id', 'name']);
        } elseif ($this->role === 'staff') {
            $saleables = User::where('institution_id', institution()->id)->where('role', 'staff')->orderBy('name')->get(['id', 'name']);
        }

        return view('livewire.admin.inventory.sale-add-component', [
            'categories' => InventoryCategory::where('institution_id', institution()->id)->with('products')->orderBy('name')->get(),
            'classes'    => AcademicClass::where('institution_id', institution()->id)->orderBy('id')->get(),
            'saleables'  => $saleables,
        ])->layout('layouts.itsupport.app', [
            'title' => 'Add Sale | ' . institution()->name,
        ]);
    }
}