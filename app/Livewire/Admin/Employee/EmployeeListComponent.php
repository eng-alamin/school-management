<?php

namespace App\Livewire\Admin\Employee;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Employee;

class EmployeeListComponent extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    // List
    public string $search = '';
    public int $perPage = 10;
    public string $sortField = 'id';
    public string $sortDirection = 'desc';

    // Delete
    public bool $confirmDelete = false;
    public ?int $deleteId = null;

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

    public function confirmDeleteRecord(int $id): void
    {
        $this->deleteId      = $id;
        $this->confirmDelete = true;
    }

    public function deleteRecord(): void
    {
        Employee::findOrFail($this->deleteId)->delete();
        $this->confirmDelete = false;
        $this->deleteId      = null;
        $this->dispatch('toast', type: 'success', message: 'Employee deleted successfully!');
    }

    public function render()
    {
        $employees = Employee::with('designation', 'department')
            ->when($this->search, fn($q) => $q
                ->where('name', 'like', "%{$this->search}%")
                ->orWhere('email', 'like', "%{$this->search}%")
                ->orWhere('phone', 'like', "%{$this->search}%")
                ->orWhereHas('designation', fn($q) => $q->where('name', 'like', "%{$this->search}%"))
                ->orWhereHas('department', fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            )
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.admin.employee.employee-list-component')
            ->with('employees', $employees)
            ->layout('layouts.admin.app', [
                'title' => "Employee List | School SaaS",
            ]);
    }
}