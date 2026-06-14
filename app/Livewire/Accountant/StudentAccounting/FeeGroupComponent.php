<?php

namespace App\Livewire\Accountant\StudentAccounting;

use Livewire\Component;
use App\Models\FeeGroup;
use App\Models\FeeType;
use App\Models\FeeGroupItem;
use Livewire\WithPagination;

class FeeGroupComponent extends Component
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
    public string $description = '';

    public array $selectedItems = [];
    public bool $selectAll = false;

    // Fee Group Items (dynamic rows)
    public array $items = [];

    protected function rules(): array
    {
        return [
            'name'               => 'required|string|max:255',
            'description'        => 'nullable|string',
            'items'              => 'array',
            'items.*.fee_type_id'=> 'required|exists:fee_types,id',
            'items.*.amount'     => 'required|numeric|min:0',
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

    public function updatedSelectAll(bool $value): void
    {
        $this->selectedItems = $value
            ? array_column($this->items, 'fee_type_id')
            : [];
    }

    public function updatedSelectedItems(): void
    {
        $this->selectAll = count($this->selectedItems) === count($this->items);
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->editId = null;

        // Load all active fee types as fixed rows
        $this->items = FeeType::where('status', true)
            ->orderBy('name')
            ->get()
            ->map(fn($ft) => [
                'fee_type_id'   => $ft->id,
                'fee_type_name' => $ft->name,
                'amount'        => '0',
            ])->toArray();

        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $record            = FeeGroup::with('items.feeType')->findOrFail($id);
        $this->editId      = $id;
        $this->name        = $record->name;
        $this->description = $record->description ?? '';
        $this->selectedItems = $record->items->pluck('fee_type_id')->toArray();

        // Merge all fee types with saved amounts
        $saved = $record->items->keyBy('fee_type_id');

        $this->items = FeeType::where('status', true)
            ->orderBy('name')
            ->get()
            ->map(fn($ft) => [
                'fee_type_id'   => $ft->id,
                'fee_type_name' => $ft->name,
                'amount'        => $saved->has($ft->id) ? $saved[$ft->id]->amount : '0',
            ])->toArray();

        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name'        => $this->name,
            'description' => $this->description ?: null,
        ];

        if ($this->editId) {
            $group = FeeGroup::findOrFail($this->editId);
            $group->update($data);
            $group->items()->delete();
            session()->flash('success', 'Fee Group updated successfully!');
        } else {
            $group = FeeGroup::create($data);
            session()->flash('success', 'Fee Group created successfully!');
        }

        // শুধু selected items save হবে
        foreach ($this->items as $item) {
            if (in_array($item['fee_type_id'], $this->selectedItems)) {
                $group->items()->create([
                    'fee_type_id' => $item['fee_type_id'],
                    'amount'      => $item['amount'] ?? 0,
                ]);
            }
        }

        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset(['name', 'description', 'editId', 'items', 'selectedItems']);
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
        FeeGroup::findOrFail($this->deleteId)->delete();
        $this->confirmDelete = false;
        $this->deleteId = null;
        session()->flash('success', 'Fee Group deleted successfully!');
    }

    public function render()
    {
        $feeGroups = FeeGroup::query()
            ->with('items.feeType')  // withCount বাদ, এটা যোগ
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $feeTypes = FeeType::where('status', true)->orderBy('name')->get();

        return view('livewire.accountant.student-accounting.fee-group-component')
            ->with(['feeGroups' => $feeGroups, 'feeTypes' => $feeTypes])
            ->layout('layouts.accountant.app', [
                'title' => "Student Accounting - Fee Group | School SaaS",
            ]);
    }
}