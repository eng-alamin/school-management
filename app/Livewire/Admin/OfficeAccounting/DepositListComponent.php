<?php

namespace App\Livewire\Admin\OfficeAccounting;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\OfficeDeposit;

class DepositListComponent extends Component
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
        $record = OfficeDeposit::findOrFail($this->deleteId);

        if ($record->attachment && \Storage::disk('public')->exists($record->attachment)) {
            \Storage::disk('public')->delete($record->attachment);
        }

        $record->delete();

        $this->confirmDelete = false;
        $this->deleteId      = null;

        session()->flash('success', 'Deposit deleted successfully!');
    }

    public function render()
    {
        $deposits = OfficeDeposit::query()
            ->when($this->search, fn($q) => $q
                ->where('reference', 'like', "%{$this->search}%")
                ->orWhere('pay_via',  'like', "%{$this->search}%")
                ->orWhere('amount',   'like', "%{$this->search}%")
                ->orWhereHas('account', fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            )
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.admin.office-accounting.deposit-list-component')
            ->with('deposits', $deposits)
            ->layout('layouts.admin.app', [
                'title' => 'Deposits | Monarchy School',
            ]);
    }
}