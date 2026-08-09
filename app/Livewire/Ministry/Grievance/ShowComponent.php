<?php

namespace App\Livewire\Ministry\Grievance;

use App\Models\ComplianceViolation;
use App\Models\Grievance;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class ShowComponent extends Component
{
    public Grievance $grievance;

    public bool $showResolveModal = false;
    public string $resolveAction = Grievance::STATUS_RESOLVED;
    public string $resolutionNote = '';

    public bool $showViolationModal = false;
    public string $violationSeverity = ComplianceViolation::SEVERITY_MINOR;

    public function mount(Grievance $grievance): void
    {
        $this->grievance = $grievance->load(['institution', 'student', 'assignedTo', 'violation']);
    }

    public function startReview(): void
    {
        $grievance = Grievance::findOrFail($this->grievance->id);

        if ($grievance->status !== Grievance::STATUS_SUBMITTED) {
            $this->dispatch('toast', type: 'error', message: 'Only newly submitted grievances can move to review.');
            return;
        }

        DB::beginTransaction();
        try {
            $grievance->update([
                'status' => Grievance::STATUS_UNDER_REVIEW,
                'assigned_to' => $grievance->assigned_to ?? auth()->id(),
            ]);

            DB::commit();
            $this->grievance = $grievance->fresh(['institution', 'student', 'assignedTo', 'violation']);
            $this->dispatch('toast', type: 'success', message: 'Marked under review.');
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatch('toast', type: 'error', message: 'Something went wrong. Please try again.');
        }
    }

    public function openResolveModal(string $action): void
    {
        $this->resolveAction = $action; // resolved|rejected|escalated
        $this->resolutionNote = '';
        $this->showResolveModal = true;
    }

    public function confirmResolve(): void
    {
        $this->validate([
            'resolutionNote' => ['required', 'string', 'min:5', 'max:2000'],
            'resolveAction' => ['required', 'in:resolved,rejected,escalated'],
        ]);

        $grievance = Grievance::findOrFail($this->grievance->id);

        if ($grievance->isClosed()) {
            $this->dispatch('toast', type: 'error', message: 'This grievance is already closed.');
            $this->showResolveModal = false;
            return;
        }

        DB::beginTransaction();
        try {
            $grievance->update([
                'status' => $this->resolveAction,
                'resolution_note' => $this->resolutionNote,
                'resolved_at' => in_array($this->resolveAction, [Grievance::STATUS_RESOLVED, Grievance::STATUS_REJECTED], true) ? now() : null,
            ]);

            DB::commit();
            $this->grievance = $grievance->fresh(['institution', 'student', 'assignedTo', 'violation']);
            $this->showResolveModal = false;
            $this->dispatch('toast', type: 'success', message: 'Grievance updated.');
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatch('toast', type: 'error', message: 'Something went wrong. Please try again.');
        }
    }

    public function openViolationModal(): void
    {
        $this->violationSeverity = ComplianceViolation::SEVERITY_MINOR;
        $this->showViolationModal = true;
    }

    // Converts a grievance into a formal compliance violation against the institution —
    // reuses the same compliance_violations table built for the Inspection module.
    public function convertToViolation(): void
    {
        $this->validate([
            'violationSeverity' => ['required', 'in:' . implode(',', ComplianceViolation::SEVERITIES)],
        ]);

        $grievance = Grievance::findOrFail($this->grievance->id);

        if ($grievance->violation_id !== null) {
            $this->dispatch('toast', type: 'error', message: 'A violation has already been raised from this grievance.');
            $this->showViolationModal = false;
            return;
        }

        DB::beginTransaction();
        try {
            $violation = ComplianceViolation::create([
                'institution_id' => $grievance->institution_id,
                'inspection_id' => null,
                'severity' => $this->violationSeverity,
                'description' => "Raised from grievance #{$grievance->id}: {$grievance->subject}",
                'status' => ComplianceViolation::STATUS_OPEN,
                'reported_by' => auth()->id(),
            ]);

            $grievance->update(['violation_id' => $violation->id]);

            DB::commit();
            $this->grievance = $grievance->fresh(['institution', 'student', 'assignedTo', 'violation']);
            $this->showViolationModal = false;
            $this->dispatch('toast', type: 'success', message: 'Violation created from this grievance.');
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatch('toast', type: 'error', message: 'Something went wrong. Please try again.');
        }
    }

    public function render()
    {
        return view('livewire.ministry.grievance.show-component')
        ->layout('layouts.ministry.app', [
            'title' => 'Compliances | ' . setting('app_name', 'EMS'),
        ]);
    }
}