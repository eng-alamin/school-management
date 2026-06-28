<?php

namespace App\Livewire\Admin\Employee;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\EmployeeDesignation;

class DesignationComponent extends Component
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
            'name' => 'required|string|max:255',
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
        $record = EmployeeDesignation::findOrFail($id);
        $this->editId    = $id;
        $this->name      = $record->name;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        if ($this->editId) {
            EmployeeDesignation::findOrFail($this->editId)->update(['name' => $this->name]);
            $this->dispatch('toast', type: 'success', message: 'Designation updated successfully!');
        } else {
            EmployeeDesignation::create(['name' => $this->name]);
            $this->dispatch('toast', type: 'success', message: 'Designation created successfully!');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function confirmDeleteRecord(int $id): void
    {
        $this->deleteId      = $id;
        $this->confirmDelete = true;
    }

    public function deleteRecord(): void
    {
        EmployeeDesignation::findOrFail($this->deleteId)->delete();
        $this->confirmDelete = false;
        $this->deleteId      = null;
        $this->dispatch('toast', type: 'success', message: 'Designation deleted successfully!');
    }

    private function resetForm(): void
    {
        $this->reset(['name', 'editId']);
        $this->resetValidation();
    }

    public function render()
    {
        $designations = EmployeeDesignation::query()
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.admin.employee.designation-component')
            ->with('designations', $designations)
            ->layout('layouts.admin.app', [
                'title' => 'Designation | ' . institution()->name,
            ]);
    }
}