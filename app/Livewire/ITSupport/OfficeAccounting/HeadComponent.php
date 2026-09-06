<?php

namespace App\Livewire\ITSupport\OfficeAccounting;

use Livewire\Component;
use App\Models\OfficeHead;
use Illuminate\Support\Facades\DB;
use Livewire\WithPagination;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;

class HeadComponent extends Component
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
    public string $type = 'deposit';

    protected function rules(): array
    {
        return [
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('office_heads', 'name')
                    ->where('institution_id', institution()->id)
                    ->where('type', $this->type)
                    ->ignore($this->editId),
            ],
            'type' => 'required|in:deposit,expense',
        ];
    }

    protected function messages(): array
    {
        return [
            'name.unique' => 'This head name already exists for the selected type.',
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
        $record = OfficeHead::findOrFail($id);

        $this->editId = $record->id;
        $this->name   = $record->name;
        $this->type   = $record->type;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        DB::beginTransaction();

        try {
            $data = [
                'institution_id' => institution()->id,
                'name'           => $this->name,
                'type'           => $this->type,
            ];

            if ($this->editId) {
                $head = OfficeHead::findOrFail($this->editId);
                $head->update($data);
                $message = 'Data updated successfully!';
            } else {
                $head = OfficeHead::create($data);
                $message = 'Data created successfully!';
            }

            activity()
                ->performedOn($head)
                ->withProperties(['institution_id' => institution()->id])
                ->log($this->editId ? 'Office Head Updated' : 'Office Head Created');

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
        $this->reset(['name', 'type', 'editId']);
        $this->type = 'deposit';
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
            $head = OfficeHead::findOrFail($this->deleteId);
            $head->delete();

            activity()
                ->performedOn($head)
                ->withProperties(['institution_id' => institution()->id])
                ->log('Office Head Deleted');

            DB::commit();

            $this->confirmDelete = false;
            $this->deleteId = null;

            $this->dispatch('toast', type: 'success', message: 'Data deleted successfully!');

        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatch('toast', type: 'error', message: 'Something went wrong!');
        }
    }

    /**
     * Status Toggle (list table এর td তে toggle button)
     */
    public function toggleStatus(int $id): void
    {
        DB::beginTransaction();

        try {
            $head = OfficeHead::findOrFail($id);
            $head->is_active = ! $head->is_active;
            $head->save();

            activity()
                ->performedOn($head)
                ->withProperties(['institution_id' => institution()->id])
                ->log('Office Head Status Updated');

            DB::commit();

            $this->dispatch('toast', type: 'success', message: 'Status updated successfully!');

        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatch('toast', type: 'error', message: 'Something went wrong!');
        }
    }

    public function render()
    {
        $heads = OfficeHead::query()
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.admin.office-accounting.head-component')
            ->with('heads', $heads)
            ->layout('layouts.itsupport.app', [
                'title' => 'Office Accounting - Head | ' . institution()->name,
            ]);
    }
}