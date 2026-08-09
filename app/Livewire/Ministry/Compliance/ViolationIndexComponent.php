<?php

namespace App\Livewire\Ministry\Compliance;

use App\Models\ComplianceViolation;
use App\Models\Institution;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class ViolationIndexComponent extends Component
{
    use WithPagination;

    private const SORTABLE_FIELDS = ['severity', 'status', 'created_at', 'resolved_at'];

    #[Url(history: true)]
    public string $search = '';

    #[Url(history: true)]
    public string $severity = '';

    #[Url(history: true)]
    public string $status = '';

    #[Url(history: true)]
    public string $division = '';

    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';

    public bool $showCreateModal = false;
    public string $institutionSearch = '';
    public ?int $institutionId = null;
    public string $newSeverity = ComplianceViolation::SEVERITY_MINOR;
    public string $newDescription = '';

    public bool $showResolveModal = false;
    public ?int $resolvingId = null;
    public string $resolveAction = 'resolved'; // resolved|escalated
    public string $resolutionNote = '';

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingSeverity(): void { $this->resetPage(); }
    public function updatingStatus(): void { $this->resetPage(); }
    public function updatingDivision(): void { $this->resetPage(); }

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

    public function getSeveritiesProperty(): array
    {
        return ComplianceViolation::SEVERITY_LABELS;
    }

    public function getStatusesProperty(): array
    {
        return ComplianceViolation::STATUS_LABELS;
    }

    public function getDivisionsProperty(): array
    {
        return Institution::DIVISIONS;
    }

    public function getInstitutionOptionsProperty()
    {
        if (strlen($this->institutionSearch) < 2) {
            return collect();
        }

        return Institution::query()
            ->where(function ($q) {
                $q->where('name', 'like', "%{$this->institutionSearch}%")
                    ->orWhere('eiin', 'like', "%{$this->institutionSearch}%");
            })
            ->limit(10)
            ->get(['id', 'name', 'eiin']);
    }

    public function selectInstitution(int $id, string $name): void
    {
        $this->institutionId = $id;
        $this->institutionSearch = $name;
    }

    public function openCreateModal(): void
    {
        $this->reset(['institutionId', 'institutionSearch', 'newDescription']);
        $this->newSeverity = ComplianceViolation::SEVERITY_MINOR;
        $this->showCreateModal = true;
    }

    public function createViolation(): void
    {
        $validated = $this->validate([
            'institutionId' => ['required', 'integer', 'exists:institutions,id'],
            'newSeverity' => ['required', 'in:' . implode(',', ComplianceViolation::SEVERITIES)],
            'newDescription' => ['required', 'string', 'min:5', 'max:2000'],
        ]);

        DB::beginTransaction();
        try {
            $violation = ComplianceViolation::create([
                'institution_id' => $validated['institutionId'],
                'inspection_id' => null,
                'severity' => $validated['newSeverity'],
                'description' => $validated['newDescription'],
                'status' => ComplianceViolation::STATUS_OPEN,
                'reported_by' => auth()->id(),
            ]);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($violation)
                ->withProperties(['severity' => $violation->severity, 'source' => 'direct'])
                ->tap(function ($activity) use ($violation) {
                    $activity->institution_id = $violation->institution_id;
                })
                ->log('violation_reported');

            DB::commit();
            $this->showCreateModal = false;
            $this->dispatch('toast', type: 'success', message: 'Violation reported.');
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatch('toast', type: 'error', message: 'Something went wrong. Please try again.');
        }
    }

    public function openResolveModal(int $id, string $action): void
    {
        $this->resolvingId = $id;
        $this->resolveAction = $action;
        $this->resolutionNote = '';
        $this->showResolveModal = true;
    }

    public function confirmResolve(): void
    {
        $this->validate([
            'resolutionNote' => ['required', 'string', 'min:5', 'max:1000'],
            'resolveAction' => ['required', 'in:resolved,escalated'],
        ]);

        $violation = ComplianceViolation::findOrFail($this->resolvingId);

        if (!$violation->isOpen()) {
            $this->dispatch('toast', type: 'error', message: 'This violation is already closed.');
            $this->showResolveModal = false;
            return;
        }

        DB::beginTransaction();
        try {
            $violation->update([
                'status' => $this->resolveAction,
                'resolution_note' => $this->resolutionNote,
                'resolved_at' => $this->resolveAction === ComplianceViolation::STATUS_RESOLVED ? now() : null,
            ]);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($violation)
                ->withProperties(['status' => $this->resolveAction, 'note' => $this->resolutionNote])
                ->tap(function ($activity) use ($violation) {
                    $activity->institution_id = $violation->institution_id;
                })
                ->log('violation_' . $this->resolveAction);

            DB::commit();
            $this->showResolveModal = false;
            $this->dispatch('toast', type: 'success', message: 'Violation updated.');
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatch('toast', type: 'error', message: 'Something went wrong. Please try again.');
        }
    }

    public function render()
    {
        $query = ComplianceViolation::query()->with(['institution', 'inspection', 'reportedBy']);

        if ($this->search !== '') {
            $search = $this->search;
            $query->whereHas('institution', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('eiin', 'like', "%{$search}%");
            });
        }

        if ($this->severity !== '') {
            $query->where('severity', $this->severity);
        }

        if ($this->status !== '') {
            $query->where('status', $this->status);
        }

        if ($this->division !== '') {
            $division = $this->division;
            $query->whereHas('institution', function ($q) use ($division) {
                $q->where('division', $division);
            });
        }

        $violations = $query->orderBy($this->sortField, $this->sortDirection)->paginate(15);

        return view('livewire.ministry.compliance.violation-index-component', [
            'violations' => $violations,
        ])->layout('layouts.ministry.app', [
            'title' => 'Compliances | ' . setting('app_name', 'EMS'),
        ]);
    }
}