<?php

namespace App\Livewire\Admin\Inventory;

use Livewire\Component;
use App\Models\InventoryUnit;
use Livewire\WithPagination;
use Illuminate\Validation\Rule;

class UnitComponent extends Component
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

    protected function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('inventory_units', 'name')
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
        $record = InventoryUnit::findOrFail($id);
        $this->editId = $id;
        $this->name = $record->name;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name' => $this->name,
        ];

        if ($this->editId) {
            $record = InventoryUnit::findOrFail($this->editId);
            $record->update($data);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($record)
                ->withProperties(['icon' => 'inventory_2', 'type' => 'inventory'])
                ->tap(function ($activity) use ($record) {
                    $activity->institution_id = $record->institution_id;
                    $activity->branch_id = $record->branch_id;
                })
                ->log('Inventory unit updated: ' . $record->name);

            $this->dispatch('toast', type: 'success', message: 'Unit updated successfully!');
        } else {
            $record = InventoryUnit::create($data);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($record)
                ->withProperties(['icon' => 'inventory_2', 'type' => 'inventory'])
                ->tap(function ($activity) use ($record) {
                    $activity->institution_id = $record->institution_id;
                    $activity->branch_id = $record->branch_id;
                })
                ->log('Inventory unit created: ' . $record->name);

            $this->dispatch('toast', type: 'success', message: 'Unit created successfully!');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset(['name', 'editId']);
        $this->resetValidation();
    }

    public function render()
    {
        $units = InventoryUnit::query()
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.admin.inventory.unit-component')
            ->with('units', $units)
            ->layout('layouts.admin.app', [
                'title' => 'Inventory Unit | ' . institution()->name,
            ]);
    }

    public function confirmDeleteRecord(int $id): void
    {
        $this->deleteId = $id;
        $this->confirmDelete = true;
    }

    public function deleteRecord(): void
    {
        $record = InventoryUnit::findOrFail($this->deleteId);
        $record->delete();
        $this->confirmDelete = false;
        $this->deleteId = null;

        activity()
            ->causedBy(auth()->user())
            ->performedOn($record)
            ->withProperties(['icon' => 'inventory_2', 'type' => 'inventory'])
            ->tap(function ($activity) use ($record) {
                $activity->institution_id = $record->institution_id;
                $activity->branch_id = $record->branch_id;
            })
            ->log('Inventory unit deleted: ' . $record->name);

        $this->dispatch('toast', type: 'success', message: 'Unit deleted successfully!');
    }
}