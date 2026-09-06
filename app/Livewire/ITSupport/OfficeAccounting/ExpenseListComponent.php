<?php

namespace App\Livewire\ITSupport\OfficeAccounting;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\OfficeExpense;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ExpenseListComponent extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    // List
    public string $search = '';
    public int $perPage = 10;
    public string $sortField = 'id';
    public string $sortDirection = 'asc';

    // Delete
    public bool $confirmDelete = false;
    public ?int $deleteId = null;

    public string $routePrefix = '';

    public function mount(): void
    {
        $this->routePrefix = $this->resolveRoutePrefix();
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
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function confirmDeleteRecord(int $id): void
    {
        $this->deleteId = $id;
        $this->confirmDelete = true;
    }

    public function deleteRecord(): void
    {
        DB::beginTransaction();

        try {
            $record = OfficeExpense::findOrFail($this->deleteId);

            if ($record->attachment && Storage::disk('public')->exists($record->attachment)) {
                Storage::disk('public')->delete($record->attachment);
            }

            $record->delete();

            activity()
                ->performedOn($record)
                ->withProperties(['institution_id' => institution()->id])
                ->log('Office Expense Deleted');

            DB::commit();

            $this->confirmDelete = false;
            $this->deleteId = null;

            $this->dispatch('toast', type: 'success', message: 'Expense deleted successfully!');

        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatch('toast', type: 'error', message: 'Something went wrong!');
        }
    }

    public function render()
    {
        $expenses = OfficeExpense::query()
            ->with(['account:id,name', 'head:id,name'])
            ->when($this->search, fn ($q) => $q
                ->where('reference', 'like', "%{$this->search}%")
                ->orWhere('pay_via', 'like', "%{$this->search}%")
                ->orWhere('voucher_no', 'like', "%{$this->search}%")
                ->orWhere('amount', 'like', "%{$this->search}%")
                ->orWhereHas('account', fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            )
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.admin.office-accounting.expense-list-component')
            ->with('expenses', $expenses)
            ->layout('layouts.itsupport.app', [
                'title' => 'Expenses | ' . institution()->name,
            ]);
    }
}