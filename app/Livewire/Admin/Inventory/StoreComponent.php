<?php

namespace App\Livewire\Admin\Inventory;

use Livewire\Component;
use App\Models\InventoryStore;
use Livewire\WithPagination;
use Illuminate\Support\Str;

class StoreComponent extends Component
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
    public string $mobile = '';
    public string $address = '';
    public string $description = '';

    protected function rules(): array
    {
        return [
            'name'        => 'required|string|max:255',
            'code'        => 'nullable|string|max:255|unique:inventory_stores,code' . ($this->editId ? ",{$this->editId}" : ''),
            'mobile'      => 'nullable|string|max:20',
            'address'     => 'nullable|string|max:255',
            'description' => 'nullable|string',
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
        $record = InventoryStore::findOrFail($id);
        $this->editId      = $id;
        $this->name        = $record->name;
        $this->code        = $record->code;
        $this->mobile      = $record->mobile;
        $this->address     = $record->address ?? '';
        $this->description = $record->description ?? '';
        $this->showModal   = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name'        => $this->name,
            'code'        => $this->code,
            'mobile'      => $this->mobile,
            'address'     => $this->address,
            'description' => $this->description,
        ];

        if ($this->editId) {
            $store = InventoryStore::findOrFail($this->editId);
            if (empty($data['code'])) {
                $data['code'] = Str::slug($this->name) . '-' . $store->id;
            }

            InventoryStore::findOrFail($this->editId)->update($data);
            $this->dispatch('toast', type: 'success', message: 'Data updated successfully!');
        } else {
            $store = InventoryStore::create($data);

            if (empty($this->code)) {
                $store->update([
                    'code' => Str::slug($this->name) . '-' . $store->id,
                ]);
            }

            $this->dispatch('toast', type: 'success', message: 'Data created successfully!');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset(['name', 'code', 'mobile', 'address', 'description', 'editId']);
        $this->resetValidation();
    }

    public function render()
    {
        $stores = InventoryStore::query()
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%")
                                              ->orWhere('code', 'like', "%{$this->search}%"))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.admin.inventory.store-component')
            ->with('stores', $stores)
            ->layout('layouts.admin.app', [
                'title' => 'Inventory Store | ' . institution()->name,
            ]);
    }

    public function confirmDeleteRecord(int $id): void
    {
        $this->deleteId = $id;
        $this->confirmDelete = true;
    }

    public function deleteRecord(): void
    {
        $record = InventoryStore::findOrFail($this->deleteId);
        $record->delete();
        $this->confirmDelete = false;
        $this->deleteId = null;
        $this->dispatch('toast', type: 'success', message: 'Data deleted successfully!');
    }
}