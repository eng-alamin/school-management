<?php

namespace App\Livewire\Accountant\StudentAccounting;

use App\Models\FeeFine;
use App\Models\FeeGroup;
use App\Models\FeeGroupItem;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithPagination;

class FeeFineComponent extends Component
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
    public bool $showViewModal = false;
    public bool $confirmDelete = false;
    public ?int $deleteId = null;
    public ?int $viewId = null;

    // Form
    public ?int $editId = null;

    // UI-only helper — not saved to DB, only used to filter the Fee Item dropdown
    public ?int $fee_group_id = null;

    public ?int $fee_group_item_id = null;
    public string $fine_type = 'fixed';
    public string $fine_value = '';
    public string $late_fee_frequency = 'one_time';
    public bool $status = true;

    // Dynamic
    public array $groupItems = [];

    protected function rules(): array
    {
        return [
            'fee_group_id' => 'required|exists:fee_groups,id',

            'fee_group_item_id' => [
                'required',
                'exists:fee_group_items,id',
                // Ensure the selected item actually belongs to the selected group
                function ($attribute, $value, $fail) {
                    if ($value && ! collect($this->groupItems)->contains('id', (int) $value)) {
                        $fail('The selected Fee Item does not belong to this Fee Group.');
                    }
                },
                // One fine per fee_group_item (matches unique_fee_fine DB constraint)
                Rule::unique('fee_fines', 'fee_group_item_id')
                    ->where(fn ($q) => $q->where('institution_id', institution()->id))
                    ->ignore($this->editId),
            ],

            'fine_type' => 'required|in:fixed,percentage',

            'fine_value' => array_filter([
                'required', 'numeric', 'min:0',
                $this->fine_type === 'percentage' ? 'max:100' : null,
            ]),

            'late_fee_frequency' => 'required|in:one_time,daily,weekly,monthly,yearly',
            'status' => 'boolean',
        ];
    }

    protected function messages(): array
    {
        return [
            'fee_group_item_id.unique' => 'A fine has already been added for this Fee Item.',
            'fine_value.max'           => 'Percentage fine cannot exceed 100.',
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

    public function updatedFeeGroupId(): void
    {
        $this->fee_group_item_id = null;
        $this->loadGroupItems();
    }

    private function loadGroupItems(): void
    {
        $this->groupItems = $this->fee_group_id
            ? FeeGroupItem::with('feeType')
                ->where('fee_group_id', $this->fee_group_id)
                ->get()
                ->toArray()
            : [];
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
        $record = FeeFine::with('feeGroupItem.feeGroup')->findOrFail($id);

        $this->editId             = $id;
        $this->fee_group_id       = $record->feeGroupItem->fee_group_id;
        $this->fee_group_item_id  = $record->fee_group_item_id;
        $this->fine_type          = $record->fine_type;
        $this->fine_value         = (string) $record->fine_value;
        $this->late_fee_frequency = $record->late_fee_frequency;
        $this->status             = (bool) $record->status;

        $this->loadGroupItems();

        $this->showModal = true;
    }

    public function openView(int $id): void
    {
        $this->viewId = $id;
        $this->showViewModal = true;
    }

    public function save(): void
    {
        $this->validate();

        DB::beginTransaction();

        try {
            $data = [
                'institution_id'     => institution()->id,
                'fee_group_item_id'  => $this->fee_group_item_id,
                'fine_type'          => $this->fine_type,
                'fine_value'         => $this->fine_value,
                'late_fee_frequency' => $this->late_fee_frequency,
                'status'             => $this->status,
            ];

            if ($this->editId) {
                $fine = FeeFine::findOrFail($this->editId);
                $fine->update($data);
                $activityMessage = 'Fee Fine Updated for Item: ' . $fine->feeGroupItem->feeType->name;
            } else {
                $fine = FeeFine::create($data);
                $activityMessage = 'Fee Fine Created for Item: ' . $fine->feeGroupItem->feeType->name;
            }

            activity()
                ->performedOn($fine)
                ->withProperties([
                    'icon'           => 'gavel',
                    'type'           => $this->editId ? 'update' : 'create',
                    'institution_id' => institution()->id,
                ])
                ->log($activityMessage);

            DB::commit();

            $this->showModal = false;
            $this->resetForm();

            $this->dispatch('toast', type: 'success', message: $this->editId ? 'Fee Fine updated successfully!' : 'Fee Fine created successfully!');

        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatch('toast', type: 'error', message: 'Something went wrong!');
        }
    }

    private function resetForm(): void
    {
        $this->reset(['fee_group_id', 'fee_group_item_id', 'fine_value', 'editId', 'groupItems']);
        $this->fine_type          = 'fixed';
        $this->late_fee_frequency = 'one_time';
        $this->status             = true;
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
            $fine = FeeFine::with('feeGroupItem.feeType')->findOrFail($this->deleteId);

            activity()
                ->performedOn($fine)
                ->withProperties([
                    'icon'           => 'delete',
                    'type'           => 'delete',
                    'institution_id' => institution()->id,
                ])
                ->log('Fee Fine Deleted for Item: ' . ($fine->feeGroupItem->feeType->name ?? '—'));

            $fine->delete();

            DB::commit();

            $this->confirmDelete = false;
            $this->deleteId = null;

            $this->dispatch('toast', type: 'success', message: 'Fee Fine deleted successfully!');

        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatch('toast', type: 'error', message: 'Something went wrong!');
        }
    }

    /**
     * Status Toggle (list table td) — same pattern as FeeType / FeeGroup
     */
    public function toggleStatus(int $id): void
    {
        DB::beginTransaction();

        try {
            $fine = FeeFine::with('feeGroupItem.feeType')->findOrFail($id);
            $fine->status = ! $fine->status;
            $fine->save();

            activity()
                ->performedOn($fine)
                ->withProperties([
                    'icon'           => 'gavel',
                    'type'           => 'update',
                    'institution_id' => institution()->id,
                ])
                ->log('Fee Fine Status Updated for Item: ' . ($fine->feeGroupItem->feeType->name ?? '—'));

            DB::commit();

            $this->dispatch('toast', type: 'success', message: 'Status updated successfully!');

        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatch('toast', type: 'error', message: 'Something went wrong!');
        }
    }

    public function render()
    {
        $feeFines = FeeFine::query()
            ->with(['feeGroupItem.feeGroup', 'feeGroupItem.feeType'])
            ->when($this->search, fn ($q) => $q->whereHas('feeGroupItem.feeType', fn ($q2) =>
                $q2->where('name', 'like', "%{$this->search}%")
            )->orWhereHas('feeGroupItem.feeGroup', fn ($q2) =>
                $q2->where('name', 'like', "%{$this->search}%")
            ))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        // Include the currently-selected group even if it has since been
        // deactivated, otherwise editing an old fine hides its group from
        // the dropdown (bug fixed here — same class of issue as FeeGroup).
        $feeGroups = FeeGroup::where(function ($q) {
                $q->where('status', true)
                  ->orWhere('id', $this->fee_group_id);
            })
            ->orderBy('name')
            ->get();

        $viewFine = $this->viewId
            ? FeeFine::with('feeGroupItem.feeGroup', 'feeGroupItem.feeType')->find($this->viewId)
            : null;

        return view('livewire.accountant.student-accounting.fee-fine-component')
            ->with([
                'feeFines'  => $feeFines,
                'feeGroups' => $feeGroups,
                'viewFine'  => $viewFine,
            ])
            ->layout('layouts.accountant.app', [
                'title' => 'Fee Fine | ' . institution()->name,
            ]);
    }
}