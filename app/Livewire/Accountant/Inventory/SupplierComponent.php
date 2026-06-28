<?php

namespace App\Livewire\Accountant\Inventory;

use Livewire\Component;
use App\Models\InventorySupplier;
use Livewire\WithPagination;

class SupplierComponent extends Component
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
    public string $mobile = '';
    public string $email = '';
    public string $address = '';

    protected function rules(): array
    {
        return [
            'name'    => 'required|string|max:255',
            'mobile'  => 'nullable|string|max:20',
            'email'   => 'nullable|email|max:255',
            'address' => 'nullable|string|max:255',
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
        $record = InventorySupplier::findOrFail($id);
        $this->editId   = $id;
        $this->name     = $record->name;
        $this->mobile   = $record->mobile;
        $this->email    = $record->email ?? '';
        $this->address  = $record->address ?? '';
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name'    => $this->name,
            'mobile'  => $this->mobile,
            'email'   => $this->email,
            'address' => $this->address,
        ];

        if ($this->editId) {
            InventorySupplier::findOrFail($this->editId)->update($data);
            $this->dispatch('toast', type: 'success', message: 'Data updated successfully!');
        } else {
            InventorySupplier::create($data);
            $this->dispatch('toast', type: 'success', message: 'Data created successfully!');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset(['name', 'mobile', 'email', 'address', 'editId']);
        $this->resetValidation();
    }

    public function render()
    {
        $suppliers = InventorySupplier::query()
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%")
                                              ->orWhere('mobile', 'like', "%{$this->search}%")
                                              ->orWhere('email', 'like', "%{$this->search}%"))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.accountant.inventory.supplier-component')
            ->with('suppliers', $suppliers)
            ->layout('layouts.accountant.app', [
                'title' => 'Inventory Supplier | ' . institution()->name,
            ]);
    }

    public function confirmDeleteRecord(int $id): void
    {
        $this->deleteId = $id;
        $this->confirmDelete = true;
    }

    public function deleteRecord(): void
    {
        $record = InventorySupplier::findOrFail($this->deleteId);
        $record->delete();
        $this->confirmDelete = false;
        $this->deleteId = null;
        $this->dispatch('toast', type: 'success', message: 'Data deleted successfully!');
    }
}