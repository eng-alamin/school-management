<?php

namespace App\Livewire\Accountant\OfficeAccounting;

use Livewire\Component;
use App\Models\OfficeAccount;
use Livewire\WithPagination;

class AccountComponent extends Component
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
    public string $number = '';
    public string $description = '';
    public string $opening_balance = '0';

    protected function rules(): array
    {
        return [
            'name'    => 'required|string|max:255',
            'number'  => 'nullable|string|max:255',
            'description'     => 'nullable|string',
            'opening_balance' => 'nullable|numeric|min:0',
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
        $record = OfficeAccount::findOrFail($id);
        $this->editId           = $id;
        $this->name     = $record->name;
        $this->number   = $record->number ?? '';
        $this->description      = $record->description ?? '';
        $this->opening_balance  = $record->opening_balance;
        $this->showModal        = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name'    => $this->name,
            'number'  => $this->number ?: null,
            'description'     => $this->description ?: null,
            'opening_balance' => $this->opening_balance ?? 0,
        ];

        if ($this->editId) {
            OfficeAccount::findOrFail($this->editId)->update($data);
            session()->flash('success', 'Account updated successfully!');
        } else {
            OfficeAccount::create($data);
            session()->flash('success', 'Account created successfully!');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset(['name', 'number', 'description', 'opening_balance', 'editId']);
        $this->resetValidation();
    }

    public function confirmDeleteRecord(int $id): void
    {
        $this->deleteId = $id;
        $this->confirmDelete = true;
    }

    public function deleteRecord(): void
    {
        OfficeAccount::findOrFail($this->deleteId)->delete();
        $this->confirmDelete = false;
        $this->deleteId = null;
        session()->flash('success', 'Account deleted successfully!');
    }

    public function render()
    {
        $accounts = OfficeAccount::query()
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.accountant.office-accounting.account-component')
            ->with('accounts', $accounts)
            ->layout('layouts.accountant.app', [
                'title' => "Office Accounting - Account | School SaaS",
            ]);
    }
}