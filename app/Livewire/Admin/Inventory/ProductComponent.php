<?php

namespace App\Livewire\Admin\Inventory;

use Livewire\Component;
use App\Models\InventoryProduct;
use App\Models\InventoryCategory;
use App\Models\InventoryUnit;
use Livewire\WithPagination;
use Illuminate\Support\Str;

class ProductComponent extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    // List
    public string $search = '';
    public int $perPage = 10;
    public string $sortField = 'id';
    public string $sortDirection = 'asc';

    // Modal
    public bool $showModal = false;
    public bool $confirmDelete = false;
    public ?int $deleteId = null;

    // Form
    public ?int $editId = null;
    public string $name = '';
    public string $code = '';
    public ?int $category_id = null;
    public ?int $purchase_unit_id = null;
    public ?int $sales_unit_id = null;
    public string $unit_ratio = '1';
    public string $purchase_price = '0';
    public string $sales_price = '0';
    public string $remarks = '';

    protected function rules(): array
    {
        return [
            'name'             => 'required|string|max:255',
            'code'             => 'nullable|string|max:255|unique:inventory_products,code' . ($this->editId ? ",{$this->editId}" : ''),
            'category_id'      => 'required|exists:inventory_categories,id',
            'purchase_unit_id' => 'required|exists:inventory_units,id',
            'sales_unit_id'    => 'required|exists:inventory_units,id',
            'unit_ratio'       => 'required|numeric|min:0',
            'purchase_price'   => 'required|numeric|min:0',
            'sales_price'      => 'required|numeric|min:0',
            'remarks'          => 'nullable|string',
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->editId = null;
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $record = InventoryProduct::findOrFail($id);
        $this->editId           = $id;
        $this->name             = $record->name;
        $this->code             = $record->code;
        $this->category_id      = $record->category_id;
        $this->purchase_unit_id = $record->purchase_unit_id;
        $this->sales_unit_id    = $record->sales_unit_id;
        $this->unit_ratio       = $record->unit_ratio;
        $this->purchase_price   = $record->purchase_price;
        $this->sales_price      = $record->sales_price;
        $this->remarks          = $record->remarks ?? '';
        $this->showModal        = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name'             => $this->name,
            'code'             => $this->code,
            'category_id'      => $this->category_id,
            'purchase_unit_id' => $this->purchase_unit_id,
            'sales_unit_id'    => $this->sales_unit_id,
            'unit_ratio'       => $this->unit_ratio,
            'purchase_price'   => $this->purchase_price,
            'sales_price'      => $this->sales_price,
            'remarks'          => $this->remarks,
        ];

        if ($this->editId) {
            $product = InventoryProduct::findOrFail($this->editId);
            if (empty($data['code'])) {
                $data['code'] = Str::slug($this->name) . '-' . $product->id;
            }

            InventoryProduct::findOrFail($this->editId)->update($data);
            $this->dispatch('toast', type: 'success', message: 'Data updated successfully!');
        } else {
            $product = InventoryProduct::create($data);

            if (empty($this->code)) {
                $product->update([
                    'code' => Str::slug($this->name) . '-' . $product->id,
                ]);
            }
            
            $this->dispatch('toast', type: 'success', message: 'Data created successfully!');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset([
            'name', 'code', 'category_id', 'purchase_unit_id', 'sales_unit_id',
            'unit_ratio', 'purchase_price', 'sales_price', 'remarks', 'editId',
        ]);
        $this->unit_ratio     = '1';
        $this->purchase_price = '0';
        $this->sales_price    = '0';
        $this->resetValidation();
    }

    public function render()
    {
        $products = InventoryProduct::query()
            ->with(['category', 'purchaseUnit', 'salesUnit'])
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%")
                                              ->orWhere('code', 'like', "%{$this->search}%"))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $categories = InventoryCategory::orderBy('name')->get();
        $units       = InventoryUnit::orderBy('name')->get();

        return view('livewire.admin.inventory.product-component')
            ->with('products', $products)
            ->with('categories', $categories)
            ->with('units', $units)
            ->layout('layouts.admin.app', [
                'title' => 'Inventory Product | ' . institution()->name,
            ]);
    }

    public function confirmDeleteRecord(int $id): void
    {
        $this->deleteId = $id;
        $this->confirmDelete = true;
    }

    public function deleteRecord(): void
    {
        $record = InventoryProduct::findOrFail($this->deleteId);
        $record->delete();
        $this->confirmDelete = false;
        $this->deleteId = null;
        $this->dispatch('toast', type: 'success', message: 'Data deleted successfully!');
    }
}