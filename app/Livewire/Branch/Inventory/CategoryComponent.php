<?php

namespace App\Livewire\Branch\Inventory;

use Livewire\Component;
use App\Models\InventoryCategory;
use Livewire\WithPagination;
use Illuminate\Validation\Rule;

class CategoryComponent extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    protected const SORTABLE_FIELDS = ['id', 'name'];

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

    protected function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('inventory_categories', 'name')
                    ->where('institution_id', institution()->id)
                    ->where('branch_id', auth()->user()->branch_id)
                    ->ignore($this->editId),
            ],
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if (!in_array($field, self::SORTABLE_FIELDS, true)) {
            return;
        }

        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'desc';
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
        $record = InventoryCategory::findOrFail($id);

        $this->editId = $id;
        $this->name = $record->name;
        $this->showModal = true;
    }

    public function save(): void
    {
        // abort_unless(auth()->user()->can($this->editId ? 'inventory-category-update' : 'inventory-category-create'), 403);

        $this->validate();

        $data = [
            'name' => $this->name,
        ];

        if ($this->editId) {
            $record = InventoryCategory::findOrFail($this->editId);
            $record->update($data);

            activity()
                ->performedOn($record)
                ->tap(function ($activity) {
                    $activity->institution_id = institution()->id;
                })
                ->log('Inventory category updated');

            $this->dispatch('toast', type: 'success', message: 'Data updated successfully!');
        } else {
            $record = InventoryCategory::create($data);

            activity()
                ->performedOn($record)
                ->tap(function ($activity) {
                    $activity->institution_id = institution()->id;
                })
                ->log('Inventory category created');

            $this->dispatch('toast', type: 'success', message: 'Data created successfully!');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset(['name', 'editId']);
        $this->resetValidation();
    }

        public function confirmDeleteRecord(int $id): void
    {
        InventoryCategory::findOrFail($id);

        $this->deleteId = $id;
        $this->confirmDelete = true;
    }

    public function deleteRecord(): void
    {
        // abort_unless(auth()->user()->can('inventory-category-delete'), 403);

        $record = InventoryCategory::findOrFail($this->deleteId);

        activity()
            ->performedOn($record)
            ->tap(function ($activity) {
                $activity->institution_id = institution()->id;
            })
            ->log('Inventory category deleted');

        $record->delete();

        $this->confirmDelete = false;
        $this->deleteId = null;
        $this->dispatch('toast', type: 'success', message: 'Data deleted successfully!');
    }

    public function render()
    {
        $categories = InventoryCategory::query()
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('name', 'like', "%{$this->search}%");
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.admin.inventory.category-component')
            ->with('categories', $categories)
            ->layout('layouts.branch.app', [
                'title' => 'Inventory Category | ' . institution()->name,
            ]);
    }
}