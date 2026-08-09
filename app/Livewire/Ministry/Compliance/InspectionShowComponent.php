<?php

namespace App\Livewire\Ministry\Compliance;

use App\Models\ComplianceViolation;
use App\Models\Inspection;
use App\Models\InspectionChecklistItem;
use App\Models\InspectionResult;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class InspectionShowComponent extends Component
{
    public Inspection $inspection;

    // scores[checklist_item_id] = ['score' => x, 'remarks' => y]
    public array $scores = [];

    public bool $showCancelModal = false;
    public string $cancelReason = '';

    public bool $showViolationModal = false;
    public string $violationSeverity = ComplianceViolation::SEVERITY_MINOR;
    public string $violationDescription = '';

    public function mount(Inspection $inspection): void
    {
        $this->inspection = $inspection->load(['institution', 'results.checklistItem', 'violations']);

        $checklistItems = InspectionChecklistItem::where('is_active', true)->orderBy('sort_order')->get();
        $existingResults = $this->inspection->results->keyBy('checklist_item_id');

        foreach ($checklistItems as $item) {
            $existing = $existingResults->get($item->id);
            $this->scores[$item->id] = [
                'score' => $existing?->score !== null ? (string) $existing->score : '',
                'remarks' => $existing?->remarks ?? '',
            ];
        }
    }

    public function getChecklistItemsProperty()
    {
        return InspectionChecklistItem::where('is_active', true)->orderBy('sort_order')->get();
    }

    public function conductInspection(): void
    {
        // Re-fetch fresh — never trust the mounted public property for a
        // state-changing action (protects against a tampered Livewire payload).
        $inspection = Inspection::findOrFail($this->inspection->id);

        if (!$inspection->isScheduled()) {
            $this->dispatch('toast', type: 'error', message: 'This inspection is not in a scheduled state.');
            return;
        }

        $checklistItems = InspectionChecklistItem::where('is_active', true)->get()->keyBy('id');

        $rules = [];
        foreach ($checklistItems as $id => $item) {
            $rules["scores.{$id}.score"] = ['required', 'numeric', 'min:0', "max:{$item->max_score}"];
            $rules["scores.{$id}.remarks"] = ['nullable', 'string', 'max:1000'];
        }

        $validated = $this->validate($rules);

        DB::beginTransaction();
        try {
            $totalScore = 0;
            $totalMax = 0;

            foreach ($checklistItems as $id => $item) {
                $score = (float) $validated['scores'][$id]['score'];
                $remarks = $validated['scores'][$id]['remarks'] ?? null;

                InspectionResult::updateOrCreate(
                    ['inspection_id' => $inspection->id, 'checklist_item_id' => $id],
                    ['score' => $score, 'remarks' => $remarks]
                );

                $totalScore += $score;
                $totalMax += $item->max_score;
            }

            $overallScore = $totalMax > 0 ? round(($totalScore / $totalMax) * 100, 2) : null;

            $inspection->update([
                'status' => Inspection::STATUS_COMPLETED,
                'conducted_at' => now(),
                'overall_score' => $overallScore,
            ]);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($inspection)
                ->withProperties(['overall_score' => $overallScore])
                ->tap(function ($activity) use ($inspection) {
                    $activity->institution_id = $inspection->institution_id;
                })
                ->log('inspection_completed');

            DB::commit();
            $this->inspection = $inspection->fresh(['institution', 'results.checklistItem', 'violations']);
            $this->dispatch('toast', type: 'success', message: 'Inspection completed and scored.');
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatch('toast', type: 'error', message: 'Something went wrong. Please try again.');
        }
    }

    public function openCancelModal(): void
    {
        $this->cancelReason = '';
        $this->showCancelModal = true;
    }

    public function confirmCancel(): void
    {
        $this->validate(['cancelReason' => ['required', 'string', 'min:5', 'max:1000']]);

        $inspection = Inspection::findOrFail($this->inspection->id);

        if (!$inspection->isScheduled()) {
            $this->dispatch('toast', type: 'error', message: 'Only scheduled inspections can be cancelled.');
            $this->showCancelModal = false;
            return;
        }

        DB::beginTransaction();
        try {
            $inspection->update([
                'status' => Inspection::STATUS_CANCELLED,
                'notes' => trim(($inspection->notes ?? '') . "\n\nCancelled: {$this->cancelReason}"),
            ]);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($inspection)
                ->withProperties(['reason' => $this->cancelReason])
                ->tap(function ($activity) use ($inspection) {
                    $activity->institution_id = $inspection->institution_id;
                })
                ->log('inspection_cancelled');

            DB::commit();
            $this->inspection = $inspection->fresh(['institution', 'results.checklistItem', 'violations']);
            $this->showCancelModal = false;
            $this->dispatch('toast', type: 'success', message: 'Inspection cancelled.');
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatch('toast', type: 'error', message: 'Something went wrong. Please try again.');
        }
    }

    public function openViolationModal(): void
    {
        $this->violationSeverity = ComplianceViolation::SEVERITY_MINOR;
        $this->violationDescription = '';
        $this->showViolationModal = true;
    }

    public function raiseViolation(): void
    {
        $validated = $this->validate([
            'violationSeverity' => ['required', 'in:' . implode(',', ComplianceViolation::SEVERITIES)],
            'violationDescription' => ['required', 'string', 'min:5', 'max:2000'],
        ]);

        $inspection = Inspection::findOrFail($this->inspection->id);

        DB::beginTransaction();
        try {
            $violation = ComplianceViolation::create([
                'institution_id' => $inspection->institution_id,
                'inspection_id' => $inspection->id,
                'severity' => $validated['violationSeverity'],
                'description' => $validated['violationDescription'],
                'status' => ComplianceViolation::STATUS_OPEN,
                'reported_by' => auth()->id(),
            ]);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($violation)
                ->withProperties(['severity' => $violation->severity])
                ->tap(function ($activity) use ($inspection) {
                    $activity->institution_id = $inspection->institution_id;
                })
                ->log('violation_reported');

            DB::commit();
            $this->inspection = $inspection->fresh(['institution', 'results.checklistItem', 'violations']);
            $this->showViolationModal = false;
            $this->dispatch('toast', type: 'success', message: 'Violation reported.');
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatch('toast', type: 'error', message: 'Something went wrong. Please try again.');
        }
    }

    public function render()
    {
        return view('livewire.ministry.compliance.inspection-show-component')
        ->layout('layouts.ministry.app', [
            'title' => 'Compliances | ' . setting('app_name', 'EMS'),
        ]);
    }
}