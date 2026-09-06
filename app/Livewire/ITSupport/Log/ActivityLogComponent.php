<?php

namespace App\Livewire\ITSupport\Log;

use App\Models\Branch;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Activitylog\Models\Activity;

class ActivityLogComponent extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    // List
    public string $search     = '';
    public string $filterType = '';
    public int    $perPage    = 10;

    public function updatingSearch(): void     { $this->resetPage(); }
    public function updatingFilterType(): void { $this->resetPage(); }

    private function resolveActiveBranchId(): ?int
    {
        $user = auth()->user();

        return $user->branch_id
            ?? Branch::resolveMainBranchId($user->institution_id);
    }

    public function render()
    {
        $institutionId = auth()->user()->institution_id;
        $branchId      = $this->resolveActiveBranchId();

        $logs = Activity::with('causer')
            ->when($institutionId, fn($q) =>
                $q->where('institution_id', $institutionId)
            )
            ->when($branchId, fn($q) =>
                $q->where('branch_id', $branchId)
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
            ->layout('layouts.itsupport.app', [
                'title' => 'Activity Log | ' . setting('app_name', 'EMS'),
            ]);
    }
}