<?php

namespace App\Livewire\ITSupport\OfficeAccounting;

use Livewire\Component;
use App\Models\OfficeAccount;
use Illuminate\Support\Facades\DB;
use Livewire\WithPagination;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;

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
            'name' => 'required|string|max:255',
            'number' => [
                'nullable', 'string', 'max:255',
                Rule::unique('office_accounts', 'number')
                    ->where('institution_id', institution()->id)
                    ->ignore($this->editId),
            ],
            'description' => 'nullable|string',
            'opening_balance' => 'nullable|numeric|min:0',
        ];
    }

    protected function messages(): array
    {
        return [
            'number.unique' => 'This account number has already been used.',
        ];
    }

    /**
     * Dispatch validation errors as toast (project standard pattern)
     */
    protected function failedValidation(Validator $validator)
    {
        $this->dispatch('toast', type: 'error', message: $validator->errors()->first());

        throw new ValidationException($validator);
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

        $this->editId          = $record->id;
        $this->name            = $record->name;
        $this->number          = $record->number ?? '';
        $this->description     = $record->description ?? '';
        $this->opening_balance = (string) $record->opening_balance;
        $this->showModal       = true;
    }

    public function save(): void
    {
        $this->validate();

        DB::beginTransaction();

        try {
            $data = [
                'institution_id'  => institution()->id,
                'name'            => $this->name,
                'number'          => $this->number ?: null,
                'description'     => $this->description ?: null,
                'opening_balance' => $this->opening_balance ?: 0,
            ];

            if ($this->editId) {
                $account = OfficeAccount::findOrFail($this->editId);
                $account->update($data);
                $message = 'Data updated successfully!';
            } else {
                $account = OfficeAccount::create($data);
                $message = 'Data created successfully!';
            }

            activity()
                ->performedOn($account)
                ->withProperties(['institution_id' => institution()->id])
                ->log($this->editId ? 'Office Account Updated' : 'Office Account Created');

            DB::commit();

            $this->dispatch('toast', type: 'success', message: $message);

            $this->showModal = false;
            $this->resetForm();

        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatch('toast', type: 'error', message: 'Something went wrong!');
        }
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
        DB::beginTransaction();

        try {
            $account = OfficeAccount::findOrFail($this->deleteId);
            $account->delete();

            activity()
                ->performedOn($account)
                ->withProperties(['institution_id' => institution()->id])
                ->log('Office Account Deleted');

            DB::commit();

            $this->confirmDelete = false;
            $this->deleteId = null;

            $this->dispatch('toast', type: 'success', message: 'Data deleted successfully!');

        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatch('toast', type: 'error', message: 'Something went wrong!');
        }
    }

    public function toggleStatus(int $id): void
    {
        DB::beginTransaction();

        try {
            $account = OfficeAccount::findOrFail($id);
            $account->is_active = ! $account->is_active;
            $account->save();

            activity()
                ->performedOn($account)
                ->withProperties(['institution_id' => institution()->id])
                ->log('Office Account Status Updated');

            DB::commit();

            $this->dispatch('toast', type: 'success', message: 'Status updated successfully!');

        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatch('toast', type: 'error', message: 'Something went wrong!');
        }
    }

    public function render()
    {
        $accounts = OfficeAccount::query()
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('number', 'like', "%{$this->search}%");
            }))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.admin.office-accounting.account-component')
            ->with('accounts', $accounts)
            ->layout('layouts.itsupport.app', [
                'title' => 'Office Accounting - Account | ' . institution()->name,
            ]);
    }
}