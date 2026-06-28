<?php

namespace App\Livewire\Teacher\Event;

use Livewire\Component;
use App\Models\EventType;
use Livewire\WithPagination;

class TypeComponent extends Component
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

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
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
        $record = EventType::findOrFail($id);
        $this->editId = $id;
        $this->name = $record->name;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name' => $this->name,
        ];

        if ($this->editId) {
            $record = EventType::findOrFail($this->editId);
            $record->update($data);

            // ── Activity Log ───────────────────────────────────────
            activity()
                ->causedBy(auth()->user())
                ->performedOn($record)
                ->withProperties(['icon' => 'category', 'type' => 'event'])
                ->log('Event type updated: ' . $record->name);

        } else {
            $record = EventType::create($data);

            // ── Activity Log ───────────────────────────────────────
            activity()
                ->causedBy(auth()->user())
                ->performedOn($record)
                ->withProperties(['icon' => 'category', 'type' => 'event'])
                ->log('New event type created: ' . $record->name);
        }

        $this->showModal = false;
        $this->resetForm();
        session()->flash('success', $this->editId ? 'Data updated successfully!' : 'Data created successfully!');
    }

    private function resetForm(): void
    {
        $this->reset(['name', 'editId']);
        $this->resetValidation();
    }

    public function render()
    {
        $types = EventType::query()
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.teacher.event.type-component')
            ->with('types', $types)
            ->layout('layouts.teacher.app', [
                'title' => 'Event Type | ' . institution()->name,
            ]);
    }

    public function confirmDeleteRecord(int $id): void
    {
        $this->deleteId = $id;
        $this->confirmDelete = true;
    }

    public function deleteRecord(): void
    {
        $record = EventType::findOrFail($this->deleteId);

        // ── Activity Log ───────────────────────────────────────────
        activity()
            ->causedBy(auth()->user())
            ->performedOn($record)
            ->withProperties(['icon' => 'category', 'type' => 'event'])
            ->log('Event type deleted: ' . $record->name);

        $record->delete();
        $this->confirmDelete = false;
        $this->deleteId = null;
        session()->flash('success', 'Data deleted successfully!');
    }
}