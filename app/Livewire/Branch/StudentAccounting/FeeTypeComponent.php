<?php

namespace App\Livewire\Branch\StudentAccounting;

use Livewire\Component;
use App\Models\FeeType;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Livewire\WithPagination;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;

class FeeTypeComponent extends Component
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
    public string $code = '';
    public string $description = '';
    public bool $status = true;

    protected function rules(): array
    {
        return [
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('fee_types', 'name')
                    ->where('institution_id', institution()->id)
                    ->ignore($this->editId),
            ],
            'code' => [
                'nullable', 'string', 'max:50',
                Rule::unique('fee_types', 'code')
                    ->where('institution_id', institution()->id)
                    ->ignore($this->editId),
            ],
            'description' => 'nullable|string',
        ];
    }

    protected function messages(): array
    {
        return [
            'code.unique'       => 'This code has already been used.',
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

    public function updatedName($value): void
    {
        $this->code = $value ? Str::slug($value, '_') : '';
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
        $record = FeeType::findOrFail($id);

        $this->editId      = $record->id;
        $this->name        = $record->name;
        $this->code        = $record->code;
        $this->description = $record->description ?? '';
        $this->status      = (bool) $record->status;
        $this->showModal   = true;
    }

    public function save(): void
    {
        $this->validate();

        DB::beginTransaction();

        try {
            $data = [
                'institution_id' => institution()->id,
                'name'           => $this->name,
                'code'           => Str::slug($this->name, '_'),
                'description'    => $this->description ?: null,
                'status'         => $this->status,
            ];

            if ($this->editId) {
                $feeType = FeeType::findOrFail($this->editId);
                $feeType->update($data);
                $message = 'Data updated successfully!';
            } else {
                $feeType = FeeType::create($data);
                $message = 'Data created successfully!';
            }

            activity()
                ->performedOn($feeType)
                ->withProperties(['institution_id' => institution()->id])
                ->log($this->editId ? 'Fee Type Updated' : 'Fee Type Created');

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
        $this->reset(['name', 'code', 'description', 'status', 'editId']);
        $this->resetValidation();
    }

    protected function isProtectedFeeType(string $code): bool
    {
        return in_array($code, ['monthly_fee', 'admission_fee', 'registration_fee']);
    }

    public function confirmDeleteRecord(int $id): void
    {
        $feeType = FeeType::findOrFail($id);

        if ($this->isProtectedFeeType($feeType->code)) {
            $this->dispatch('toast', type: 'error', message: 'এই Fee Type সিস্টেম ডিফল্ট, ডিলিট করা যাবে না।');
            return;
        }

        $this->deleteId = $id;
        $this->confirmDelete = true;
    }

    public function deleteRecord(): void
    {
        DB::beginTransaction();

        try {
            $feeType = FeeType::findOrFail($this->deleteId);
            $feeType->delete();

            activity()
                ->performedOn($feeType)
                ->withProperties(['institution_id' => institution()->id])
                ->log('Fee Type Deleted');

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
            $feeType = FeeType::findOrFail($id);
            $feeType->status = ! $feeType->status;
            $feeType->save();

            activity()
                ->performedOn($feeType)
                ->withProperties(['institution_id' => institution()->id])
                ->log('Fee Type Status Updated');

            DB::commit();

            $this->dispatch('toast', type: 'success', message: 'Status updated successfully!');

        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatch('toast', type: 'error', message: 'Something went wrong!');
        }
    }

    public function render()
    {
        $feeTypes = FeeType::query()
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('code', 'like', "%{$this->search}%");
            }))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.admin.student-accounting.fee-type-component')
            ->with('feeTypes', $feeTypes)
            ->layout('layouts.branch.app', [
                'title' => 'Fee Type | ' . institution()->name,
            ]);
    }
}