<?php

namespace App\Livewire\Accountant\StudentAccounting;

use Livewire\Component;
use App\Models\FeeGroup;
use App\Models\FeeType;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Livewire\WithPagination;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;

class FeeGroupComponent extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    // List
    public string $search = '';
    public int $perPage = 10;
    public string $sortField = 'id';
    public string $sortDirection = 'asc';

    // Modal states
    public bool $showModal = false;
    public bool $showViewModal = false;
    public bool $confirmDelete = false;
    public ?int $deleteId = null;
    public ?int $viewId = null;

    // Form
    public ?int $editId = null;
    public string $name = '';
    public string $code = '';
    public string $description = '';
    public bool $status = true;

    public array $selectedItems = [];
    public bool $selectAll = false;

    // Fee Group Items (dynamic rows)
    public array $items = [];

    protected function rules(): array
    {
        return [
            'name'           => 'required|string|max:255',
            'code'           => [
                'nullable', 'string', 'max:50',
                Rule::unique('fee_groups', 'code')
                    ->where('institution_id', institution()->id)
                    ->ignore($this->editId),
            ],
            'description'    => 'nullable|string',
            'status'         => 'boolean',
            'selectedItems'  => 'array|min:1',
            'items.*.amount' => 'nullable|numeric|min:0',
        ];
    }

    protected function messages(): array
    {
        return [
            'selectedItems.min' => 'Please select at least one Fee Type.',
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

    public function updatedSelectAll(bool $value): void
    {
        $this->selectedItems = $value
            ? array_column($this->items, 'fee_type_id')
            : [];
    }

    public function updatedSelectedItems(): void
    {
        $this->selectAll = count($this->items) > 0
            && count($this->selectedItems) === count($this->items);
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->editId = null;

        $this->items = FeeType::where('status', true)
            ->orderBy('name')
            ->get()
            ->map(fn ($ft) => [
                'fee_type_id'   => $ft->id,
                'fee_type_name' => $ft->name,
                'amount'        => '0',
            ])->toArray();

        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $record = FeeGroup::with('items.feeType')->findOrFail($id);

        $this->editId        = $id;
        $this->name          = $record->name;
        $this->code          = $record->code;
        $this->description   = $record->description ?? '';
        $this->status        = (bool) $record->status;
        $this->selectedItems = $record->items->pluck('fee_type_id')->toArray();

        $saved = $record->items->keyBy('fee_type_id');

        // Include active fee types PLUS any fee type already attached to
        // this group even if it has since been deactivated — otherwise
        // editing would silently drop that association on save.
        $feeTypes = FeeType::where(function ($q) {
                $q->where('status', true)
                  ->orWhereIn('id', $this->selectedItems);
            })
            ->orderBy('name')
            ->get();

        $this->items = $feeTypes->map(fn ($ft) => [
                'fee_type_id'   => $ft->id,
                'fee_type_name' => $ft->name,
                // raw amount (no number_format) — commas break number input
                'amount'        => $saved->has($ft->id) ? (string) $saved[$ft->id]->amount : '0',
            ])->toArray();

        $this->updatedSelectedItems();

        $this->showModal = true;
    }

    public function openView(int $id): void
    {
        $this->viewId        = $id;
        $this->showViewModal = true;
    }

    public function save(): void
    {
        $this->validate();

        // Extra guard: a selected item must have an amount > 0
        foreach ($this->items as $item) {
            if (in_array($item['fee_type_id'], $this->selectedItems) && (float) ($item['amount'] ?? 0) <= 0) {
                $this->dispatch('toast', type: 'error', message: "Please enter a valid amount for: {$item['fee_type_name']}");
                return;
            }
        }

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
                $group = FeeGroup::findOrFail($this->editId);
                $group->update($data);
                $activityMessage = 'Fee Group Updated: ' . $group->name;
            } else {
                $group = FeeGroup::create($data);
                $activityMessage = 'Fee Group Created: ' . $group->name;
            }

            // ── Diff-based sync ──────────────────────────────────────────
            // Only remove items the accountant actually unchecked, and
            // updateOrCreate() everything else so existing IDs (and
            // anything referencing them — fee_allocations, invoice items)
            // stay intact instead of being cascade-deleted on every edit.
            $existingFeeTypeIds = $group->items()->pluck('fee_type_id')->toArray();
            $toRemove = array_diff($existingFeeTypeIds, $this->selectedItems);

            if (! empty($toRemove)) {
                $group->items()->whereIn('fee_type_id', $toRemove)->delete();
            }

            foreach ($this->items as $item) {
                if (in_array($item['fee_type_id'], $this->selectedItems)) {
                    $group->items()->updateOrCreate(
                        ['fee_type_id' => $item['fee_type_id']],
                        [
                            'institution_id' => institution()->id,
                            'amount'         => $item['amount'] ?? 0,
                        ]
                    );
                }
            }

            activity()
                ->performedOn($group)
                ->withProperties([
                    'icon'           => 'sell',
                    'type'           => $this->editId ? 'update' : 'create',
                    'institution_id' => institution()->id,
                ])
                ->log($activityMessage);

            DB::commit();

            $this->showModal = false;
            $this->resetForm();

            $this->dispatch('toast', type: 'success', message: $this->editId ? 'Fee Group updated successfully!' : 'Fee Group created successfully!');

        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatch('toast', type: 'error', message: 'Something went wrong!');
        }
    }

    private function resetForm(): void
    {
        $this->reset(['name', 'code', 'description', 'editId', 'items', 'selectedItems']);
        $this->status    = true;
        $this->selectAll = false;
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
            $group = FeeGroup::findOrFail($this->deleteId);

            activity()
                ->performedOn($group)
                ->withProperties([
                    'icon'           => 'delete',
                    'type'           => 'delete',
                    'institution_id' => institution()->id,
                ])
                ->log('Fee Group Deleted: ' . $group->name);

            $group->delete();

            DB::commit();

            $this->confirmDelete = false;
            $this->deleteId = null;

            $this->dispatch('toast', type: 'success', message: 'Fee Group deleted successfully!');

        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatch('toast', type: 'error', message: 'Something went wrong!');
        }
    }

    /**
     * Status Toggle (list table td) — same pattern as FeeTypeComponent
     */
    public function toggleStatus(int $id): void
    {
        DB::beginTransaction();

        try {
            $group = FeeGroup::findOrFail($id);
            $group->status = ! $group->status;
            $group->save();

            activity()
                ->performedOn($group)
                ->withProperties([
                    'icon'           => 'sell',
                    'type'           => 'update',
                    'institution_id' => institution()->id,
                ])
                ->log('Fee Group Status Updated: ' . $group->name);

            DB::commit();

            $this->dispatch('toast', type: 'success', message: 'Status updated successfully!');

        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatch('toast', type: 'error', message: 'Something went wrong!');
        }
    }

    public function render()
    {
        $feeGroups = FeeGroup::query()
            ->with('items.feeType')
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('code', 'like', "%{$this->search}%");
            }))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $viewGroup = $this->viewId ? FeeGroup::with('items.feeType')->find($this->viewId) : null;

        return view('livewire.accountant.student-accounting.fee-group-component')
            ->with([
                'feeGroups' => $feeGroups,
                'viewGroup' => $viewGroup,
            ])
            ->layout('layouts.accountant.app', [
                'title' => 'Fee Group | ' . institution()->name,
            ]);
    }
}