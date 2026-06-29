<?php

namespace App\Livewire\Admin\Log;

use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Activitylog\Models\Activity;

class ActivityLogComponent extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    // List
    public string $search    = '';
    public string $filterType = '';
    public int    $perPage   = 10;

    public function updatingSearch(): void   { $this->resetPage(); }
    public function updatingFilterType(): void { $this->resetPage(); }

    public function render()
    {
        $logs = Activity::with('causer')
            ->when(auth()->user()->institution_id, fn($q) =>
                $q->where('institution_id', auth()->user()->institution_id)
            )
            ->when($this->search, fn($q) =>
                $q->where('description', 'like', "%{$this->search}%")
            )
            ->when($this->filterType, fn($q) =>
                $q->whereJsonContains('properties->type', $this->filterType)
            )
            ->latest()
            ->paginate($this->perPage);

        return view('livewire.admin.log.activity-log-component')
            ->with('logs', $logs)
            ->layout('layouts.admin.app', [
                'title' => 'Activity Log | ' . institution()->name,
            ]);
    }
}