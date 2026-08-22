<?php

namespace App\Livewire\Accountant\Event;

use Livewire\Component;
use App\Models\EventType;
use Livewire\WithPagination;
use Illuminate\Validation\Rule;

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
        $institutionId = auth()->user()->institution_id;

        return [
            // BUG FIX: added institution-scoped uniqueness so the same
            // institution cannot create duplicate event type names.
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('event_types', 'name')
                    ->where(fn($q) => $q->where('institution_id', $institutionId))
                    ->ignore($this->editId),
            ],
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
        $record = EventType::where('institution_id', auth()->user()->institution_id)
            ->findOrFail($id);

        $this->editId = $id;
        $this->name = $record->name;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $institutionId = auth()->user()->institution_id;

        if ($this->editId) {
            $record = EventType::where('institution_id', $institutionId)
                ->findOrFail($this->editId);

            $record->update(['name' => $this->name]);

            // ── Activity Log ───────────────────────────────────────
            activity()
                ->causedBy(auth()->user())
                ->performedOn($record)
                ->withProperties(['icon' => 'category', 'type' => 'event'])
                ->tap(function ($activity) use ($institutionId) {
                    $activity->institution_id = $institutionId;
                })
                ->log('Event type updated: ' . $record->name);

        } else {
            // BUG FIX: institution_id was never set, which would violate
            // the NOT NULL foreign key constraint on event_types table.
            $record = EventType::create([
                'institution_id' => $institutionId,
                'name'           => $this->name,
            ]);

            // ── Activity Log ───────────────────────────────────────
            activity()
                ->causedBy(auth()->user())
                ->performedOn($record)
                ->withProperties(['icon' => 'category', 'type' => 'event'])
                ->tap(function ($activity) use ($institutionId) {
                    $activity->institution_id = $institutionId;
                })
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
        $institutionId = auth()->user()->institution_id;

        $types = EventType::query()
            ->where('institution_id', $institutionId)
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.admin.event.type-component')
            ->with('types', $types)
            ->layout('layouts.accountant.app', [
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
        // BUG FIX (Security/IDOR): scoped by institution_id.
        $record = EventType::where('institution_id', auth()->user()->institution_id)
            ->findOrFail($this->deleteId);

        // ── Activity Log ───────────────────────────────────────────
        activity()
            ->causedBy(auth()->user())
            ->performedOn($record)
            ->withProperties(['icon' => 'category', 'type' => 'event'])
            ->tap(function ($activity) use ($record) {
                $activity->institution_id = $record->institution_id;
            })
            ->log('Event type deleted: ' . $record->name);

        $record->delete();
        $this->confirmDelete = false;
        $this->deleteId = null;
        session()->flash('success', 'Data deleted successfully!');
    }
}