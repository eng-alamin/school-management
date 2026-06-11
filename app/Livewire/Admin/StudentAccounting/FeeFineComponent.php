<?php

namespace App\Livewire\Admin\StudentAccounting;

use Livewire\Component;
use App\Models\FeeFine;
use App\Models\FeeGroup;
use App\Models\FeeGroupItem;
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
    public bool $confirmDelete = false;
    public ?int $deleteId = null;

    // Form
    public ?int $editId = null;
    public ?int $fee_group_id = null;
    public ?int $fee_group_item_id = null;
    public string $fine_type = 'fixed';
    public string $fine_value = '';
    public string $late_fee_frequency = 'one_time';

    // Dynamic
    public array $groupItems = [];

    protected function rules(): array
    {
        return [
            'fee_group_id'       => 'required|exists:fee_groups,id',
            'fee_group_item_id'  => 'nullable|exists:fee_group_items,id',
            'fine_type'          => 'required|in:fixed,percentage',
            'fine_value'         => 'required|numeric|min:0',
            'late_fee_frequency' => 'required|in:one_time,daily,weekly,monthly,yearly',
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFeeGroupId(): void
    {
        $this->fee_group_item_id = null;
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
        $record = FeeFine::findOrFail($id);
        $this->editId             = $id;
        $this->fee_group_id       = $record->fee_group_id;
        $this->fee_group_item_id  = $record->fee_group_item_id;
        $this->fine_type          = $record->fine_type;
        $this->fine_value         = $record->fine_value;
        $this->late_fee_frequency = $record->late_fee_frequency;

        $this->groupItems = FeeGroupItem::with('feeType')
            ->where('fee_group_id', $this->fee_group_id)
            ->get()
            ->toArray();

        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'fee_group_id'       => $this->fee_group_id,
            'fee_group_item_id'  => $this->fee_group_item_id ?: null,
            'fine_type'          => $this->fine_type,
            'fine_value'         => $this->fine_value,
            'late_fee_frequency' => $this->late_fee_frequency,
        ];

        if ($this->editId) {
            FeeFine::findOrFail($this->editId)->update($data);
            session()->flash('success', 'Fee Fine updated successfully!');
        } else {
            FeeFine::create($data);
            session()->flash('success', 'Fee Fine created successfully!');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset(['fee_group_id', 'fee_group_item_id', 'fine_value', 'editId', 'groupItems']);
        $this->fine_type          = 'fixed';
        $this->late_fee_frequency = 'one_time';
        $this->resetValidation();
    }

    public function confirmDeleteRecord(int $id): void
    {
        $this->deleteId = $id;
        $this->confirmDelete = true;
    }

    public function deleteRecord(): void
    {
        FeeFine::findOrFail($this->deleteId)->delete();
        $this->confirmDelete = false;
        $this->deleteId = null;
        session()->flash('success', 'Fee Fine deleted successfully!');
    }

    public function render()
    {
        $feeFines = FeeFine::query()
            ->with(['feeGroup', 'feeGroupItem.feeType'])
            ->when($this->search, fn($q) => $q->whereHas('feeGroup', fn($q2) =>
                $q2->where('name', 'like', "%{$this->search}%")
            ))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $feeGroups = FeeGroup::where('status', true)->orderBy('name')->get();

        return view('livewire.admin.student-accounting.fee-fine-component')
            ->with(['feeFines' => $feeFines, 'feeGroups' => $feeGroups])
            ->layout('layouts.admin.app', [
                'title' => "Student Accounting - Fee Fine | School SaaS",
            ]);
    }
}