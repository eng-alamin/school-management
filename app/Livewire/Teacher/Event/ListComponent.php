<?php

namespace App\Livewire\Teacher\Event;

use Livewire\Component;
use App\Models\Event;
use Livewire\WithPagination;

class ListComponent extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    // List
    public string $search = '';
    public int $perPage = 10;
    public string $sortField = 'id';
    public string $sortDirection = 'asc';

    // Fields sortBy() is allowed to touch — keeps the wire:click payload from
    // being used to sort by an arbitrary/unindexed or unrelated column.
    private const SORTABLE_FIELDS = ['id', 'title', 'date_from', 'date_to', 'created_at'];

    // Delete
    public bool $confirmDelete = false;
    public ?int $deleteId = null;

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

        $this->resetPage();
    }

    public function confirmDeleteRecord(int $id): void
    {
        $this->deleteId = $id;
        $this->confirmDelete = true;
    }

    public function deleteRecord(): void
    {
        // Global institution scope on the Event model (BelongsToInstitution)
        // already keeps this to the current institution's events.
        $record = Event::findOrFail($this->deleteId);
        $record->delete();
        $this->confirmDelete = false;
        $this->deleteId = null;

        // ── Activity Log ───────────────────────────────────────────
        activity()
            ->causedBy(auth()->user())
            ->performedOn($record)
            ->withProperties(['icon' => 'event', 'type' => 'event'])
            ->tap(function ($activity) use ($record) {
                    $activity->institution_id = $record->institution_id;
                })
            ->log('Event deleted: ' . $record->title);

        session()->flash('success', 'Event deleted successfully!');
    }

    public function render()
    {
        $events = Event::query()
            ->when($this->search, fn($q) => $q->where('title', 'like', "%{$this->search}%"))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.teacher.event.list-component')
            ->with('events', $events)
            ->layout('layouts.teacher.app', [
                'title' => 'Events | ' . institution()->name,
            ]);
    }
}