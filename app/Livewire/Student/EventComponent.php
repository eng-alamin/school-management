<?php

namespace App\Livewire\Student;

use Livewire\Component;
use App\Models\Event;
use Livewire\WithPagination;

class EventComponent extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    public string $search        = '';
    public int    $perPage       = 10;
    public string $sortField     = 'id';
    public string $sortDirection = 'asc';

    public bool   $showDetail = false;
    public ?Event $detailRecord = null;

    public function updatingSearch(): void { $this->resetPage(); }

    public function sortBy(string $field): void
    {
        $this->sortDirection = ($this->sortField === $field && $this->sortDirection === 'asc')
            ? 'desc' : 'asc';
        $this->sortField = $field;
        $this->resetPage();
    }

    public function openDetail(int $id): void
    {
        $this->detailRecord = Event::findOrFail($id);
        $this->showDetail   = true;
    }

    public function render()
    {
        $events = Event::query()
            ->when($this->search, fn($q) => $q->where('title', 'like', "%{$this->search}%"))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.student.event-component')
            ->with('events', $events)
            ->layout('layouts.student.app', [
                'title' => "Events | Monarchy School",
            ]);
    }
}