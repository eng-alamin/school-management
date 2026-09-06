<?php

namespace App\Livewire\ITSupport\Inventory;

use Livewire\Component;
use App\Models\InventorySale;
use Livewire\WithPagination;

class SaleListComponent extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    // List
    public string $search        = '';
    public int    $perPage       = 10;
    public string $sortField     = 'id';
    public string $sortDirection = 'asc';

    // Delete
    public bool  $confirmDelete = false;
    public ?int  $deleteId      = null;

    public string $routePrefix = '';

    public function mount(): void
    {
        $this->routePrefix = $this->resolveRoutePrefix();

        // Surface a success toast after redirect back from Create/Edit pages.
        if (session()->has('toast_success')) {
            $this->dispatch('toast', type: 'success', message: session()->pull('toast_success'));
        }
    }

    protected function resolveRoutePrefix(): string
    {
        $routeName = request()->route()?->getName();

        if ($routeName && str_contains($routeName, '.')) {
            return explode('.', $routeName)[0] . '.';
        }

        $segment = request()->segment(1);

        return $segment ? $segment . '.' : '';
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
            $this->sortField     = $field;
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
        $record = InventorySale::findOrFail($this->deleteId);
        $record->delete(); // cascades to inventory_sale_items
        $this->confirmDelete = false;
        $this->deleteId      = null;
        $this->dispatch('toast', type: 'success', message: 'Data deleted successfully!');
    }

    public function render()
    {
        $sales = InventorySale::query()
            ->with(['saleable'])
            ->when($this->search, function ($q) {
                $q->where('bill_no', 'like', "%{$this->search}%")
                  ->orWhere('role', 'like', "%{$this->search}%");
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.admin.inventory.sale-list-component')
            ->with('sales', $sales)
            ->layout('layouts.itsupport.app', [
                'title' => 'Sales | ' . institution()->name,
            ]);
    }
}