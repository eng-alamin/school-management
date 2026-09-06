<?php

namespace App\Livewire\Admin\Event;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Event;
use App\Models\AcademicSession;
use App\Models\Branch;

class ListComponent extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    private const SORTABLE_FIELDS = ['id', 'title', 'date_from', 'date_to', 'audience', 'created_at'];

    // List
    public string $search = '';
    public int $perPage = 10;
    public string $sortField = 'id';
    public string $sortDirection = 'desc';

    // Delete
    public bool $confirmDelete = false;
    public ?int $deleteId = null;

    public string $routePrefix = '';

    public ?int $currentSessionId = null;

    public function mount(): void
    {
        $this->routePrefix      = $this->resolveRoutePrefix();
        $this->currentSessionId = $this->resolveCurrentSessionId();
    }

    protected function resolveRoutePrefix(): string
    {
        $routeName = request()->route()?->getName();

        if ($routeName && str_contains($routeName, '.')) {
            return explode('.', $routeName)[0] . '.';
        }

        $segment = request()->segment(1);

        return $segment ? $segment . '.' : '';
    }

    private function activeBranchId(): ?int
    {
        return auth()->user()->branch_id
            ?? Branch::resolveMainBranchId(institution()->id);
    }

    private function resolveCurrentSessionId(): ?int
    {
        return AcademicSession::query()
            ->where('institution_id', institution()->id)
            ->where('branch_id', $this->activeBranchId())
            ->active() // scopeActive() -> is_current = true
            ->value('id');
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if (! in_array($field, self::SORTABLE_FIELDS, true)) {
            return;
        }

        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField     = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function confirmDeleteRecord(int $id): void
    {
        $this->deleteId      = $id;
        $this->confirmDelete = true;
    }

    public function deleteRecord(): void
    {
        $institutionId = institution()->id;
        $branchId      = $this->activeBranchId();

        $event = Event::where('institution_id', $institutionId)
            ->where('branch_id', $branchId)
            ->find($this->deleteId);

        if (!$event) {
            $this->confirmDelete = false;
            $this->deleteId      = null;
            $this->dispatch('toast', type: 'error', message: 'Event not found.');
            return;
        }

        $imagePath = $event->image;
        $title     = $event->title;

        try {
            DB::transaction(function () use ($event, $title, $institutionId) {
                activity()
                    ->causedBy(auth()->user())
                    ->performedOn($event)
                    ->withProperties(['icon' => 'event', 'type' => 'event'])
                    ->tap(fn ($a) => $a->institution_id = $institutionId)
                    ->log('Event deleted: ' . $title);

                $event->delete();
            });

            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }

            $this->dispatch('toast', type: 'success', message: 'Event deleted successfully!');

        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: 'Delete failed: ' . $e->getMessage());
            report($e);
        }

        $this->confirmDelete = false;
        $this->deleteId      = null;
    }

    public function render()
    {
        $institutionId = institution()->id;
        $branchId      = $this->activeBranchId();

        $events = Event::query()
            ->with('eventType')
            ->where('institution_id', $institutionId)
            ->where('branch_id', $branchId)
            ->where('session_id', $this->currentSessionId)
            ->when($this->search, fn($q) =>
                $q->where('title', 'like', '%' . $this->search . '%')
            )
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.admin.event.list-component')
            ->with('events', $events)
            ->layout('layouts.admin.app', [
                'title' => 'Events | ' . institution()->name,
            ]);
    }
}