<?php

namespace App\Livewire\SuperAdmin\Log;

use App\Models\Institution;
use App\Models\SystemErrorLog;
use Livewire\Component;
use Livewire\WithPagination;

class SystemLogComponent extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    // List
    public string $search               = '';
    public string $filterStatus         = '';
    public string $filterPanel          = '';
    public ?int   $filterInstitutionId  = null;
    public int    $perPage              = 10;

    // View modal
    public ?SystemErrorLog $viewingLog = null;

    public function updatingSearch(): void              { $this->resetPage(); }
    public function updatingFilterStatus(): void         { $this->resetPage(); }
    public function updatingFilterPanel(): void          { $this->resetPage(); }
    public function updatingFilterInstitutionId(): void  { $this->resetPage(); }

    public function view(int $id): void
    {
        $this->viewingLog = SystemErrorLog::with(['institution', 'branch', 'user'])
            ->findOrFail($id);

        if ($this->viewingLog->status === SystemErrorLog::STATUS_NEW) {
            $this->viewingLog->update(['status' => SystemErrorLog::STATUS_REVIEWED]);
        }

        $this->dispatch('open-view-modal');
    }

    public function markResolved(int $id): void
    {
        SystemErrorLog::findOrFail($id)->update(['status' => SystemErrorLog::STATUS_RESOLVED]);

        $this->dispatch('toast', type: 'success', message: 'Resolved হিসেবে মার্ক করা হয়েছে।');
    }

    public function delete(int $id): void
    {
        $log = SystemErrorLog::findOrFail($id);

        activity()
            ->causedBy(auth()->user())
            ->withProperties([
                'icon' => 'delete',
                'type' => 'general',
                'log_id' => $log->id,
                'exception_class' => $log->exception_class,
            ])
            ->log('System error log deleted');

        $log->delete();

        $this->dispatch('toast', type: 'success', message: 'লগটি ডিলিট করা হয়েছে।');
    }

    public function render()
    {
        $logs = SystemErrorLog::with(['institution', 'branch', 'user'])
            ->when($this->search, fn ($q) =>
                $q->where(function ($sub) {
                    $sub->where('exception_class', 'like', "%{$this->search}%")
                        ->orWhere('message', 'like', "%{$this->search}%")
                        ->orWhere('component', 'like', "%{$this->search}%");
                })
            )
            ->when($this->filterStatus, fn ($q) =>
                $q->where('status', $this->filterStatus)
            )
            ->when($this->filterPanel, fn ($q) =>
                $q->where('panel', $this->filterPanel)
            )
            ->when($this->filterInstitutionId, fn ($q) =>
                $q->where('institution_id', $this->filterInstitutionId)
            )
            ->latest()
            ->paginate($this->perPage);

        $institutions = Institution::select('id', 'name')->orderBy('name')->get();

        return view('livewire.super-admin.log.system-log-component')
            ->with('logs', $logs)
            ->with('institutions', $institutions)
            ->layout('layouts.superadmin.app', [
                'title' => 'System Log | ' . setting('app_name', 'EMS'),
            ]);
    }
}