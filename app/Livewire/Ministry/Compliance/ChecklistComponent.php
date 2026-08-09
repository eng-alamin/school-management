<?php

namespace App\Livewire\Ministry\Compliance;

use App\Models\InspectionChecklistItem;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class ChecklistComponent extends Component
{
    use WithPagination;

    // Allowlist prevents arbitrary/unsafe column names reaching orderBy()
    private const SORTABLE_FIELDS = ['category', 'criterion', 'max_score', 'sort_order', 'created_at'];

    #[Url(history: true)]
    public string $search = '';

    #[Url(history: true)]
    public string $statusFilter = 'active'; // active|inactive|all

    public string $sortField = 'sort_order';
    public string $sortDirection = 'asc';

    public bool $showModal = false;
    public ?int $editingId = null;

    public string $category = '';
    public string $criterion = '';
    public int $maxScore = 10;
    public int $sortOrder = 0;
    public bool $isActive = true;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if (!in_array($field, self::SORTABLE_FIELDS, true)) {
            return;
        }

        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEditModal(int $id): void
    {
        $item = InspectionChecklistItem::findOrFail($id);

        $this->editingId = $item->id;
        $this->category = $item->category;
        $this->criterion = $item->criterion;
        $this->maxScore = $item->max_score;
        $this->sortOrder = $item->sort_order;
        $this->isActive = $item->is_active;
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->category = '';
        $this->criterion = '';
        $this->maxScore = 10;
        $this->sortOrder = 0;
        $this->isActive = true;
        $this->resetErrorBag();
    }

    public function save(): void
    {
        $validated = $this->validate([
            'category' => ['required', 'string', 'max:255'],
            'criterion' => ['required', 'string', 'max:500'],
            'maxScore' => ['required', 'integer', 'min:1', 'max:100'],
            'sortOrder' => ['required', 'integer', 'min:0'],
            'isActive' => ['boolean'],
        ]);

        DB::beginTransaction();
        try {
            InspectionChecklistItem::updateOrCreate(
                ['id' => $this->editingId],
                [
                    'category' => $validated['category'],
                    'criterion' => $validated['criterion'],
                    'max_score' => $validated['maxScore'],
                    'sort_order' => $validated['sortOrder'],
                    'is_active' => $validated['isActive'],
                ]
            );

            DB::commit();
            $this->dispatch('toast', type: 'success', message: $this->editingId ? 'Checklist item updated.' : 'Checklist item created.');
            $this->closeModal();
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatch('toast', type: 'error', message: 'Something went wrong. Please try again.');
        }
    }

    public function toggleActive(int $id): void
    {
        DB::beginTransaction();
        try {
            $item = InspectionChecklistItem::findOrFail($id);
            $item->update(['is_active' => !$item->is_active]);

            DB::commit();
            $this->dispatch('toast', type: 'success', message: 'Status updated.');
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatch('toast', type: 'error', message: 'Something went wrong. Please try again.');
        }
    }

    // Hard delete only allowed when never used — otherwise it would orphan
    // historical inspection_results. Otherwise, deactivate instead.
    public function delete(int $id): void
    {
        $item = InspectionChecklistItem::withCount('results')->findOrFail($id);

        if ($item->results_count > 0) {
            $this->dispatch('toast', type: 'error', message: 'Cannot delete — already used in inspections. Deactivate it instead.');
            return;
        }

        DB::beginTransaction();
        try {
            $item->delete();
            DB::commit();
            $this->dispatch('toast', type: 'success', message: 'Checklist item deleted.');
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatch('toast', type: 'error', message: 'Something went wrong. Please try again.');
        }
    }

    public function render()
    {
        $query = InspectionChecklistItem::query()->withCount('results');

        if ($this->search !== '') {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('category', 'like', "%{$search}%")
                    ->orWhere('criterion', 'like', "%{$search}%");
            });
        }

        if ($this->statusFilter === 'active') {
            $query->where('is_active', true);
        } elseif ($this->statusFilter === 'inactive') {
            $query->where('is_active', false);
        }

        $items = $query->orderBy($this->sortField, $this->sortDirection)->paginate(15);

        return view('livewire.ministry.compliance.checklist-component', [
            'items' => $items,
        ])->layout('layouts.ministry.app', [
            'title' => 'Compliances | ' . setting('app_name', 'EMS'),
        ]);
    }
}