<?php

namespace App\Livewire\Ministry\Compliance;

use App\Models\Inspection;
use App\Models\Institution;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class InspectionFormComponent extends Component
{
    public ?int $institutionId = null;
    public string $institutionSearch = '';
    public string $scheduledAt = '';
    public string $notes = '';

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
            ->where('verification_status', Institution::VERIFICATION_VERIFIED)
            ->limit(10)
            ->get(['id', 'name', 'eiin']);
    }

    public function selectInstitution(int $id, string $name): void
    {
        $this->institutionId = $id;
        $this->institutionSearch = $name;
    }

    public function save()
    {
        $validated = $this->validate([
            'institutionId' => ['required', 'integer', 'exists:institutions,id'],
            'scheduledAt' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        DB::beginTransaction();
        try {
            $inspection = Inspection::create([
                'institution_id' => $validated['institutionId'],
                'scheduled_at' => $validated['scheduledAt'],
                'notes' => $validated['notes'],
                'status' => Inspection::STATUS_SCHEDULED,
                'created_by' => auth()->id(),
            ]);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($inspection)
                ->withProperties(['institution_id' => $inspection->institution_id])
                ->tap(function ($activity) use ($inspection) {
                    $activity->institution_id = $inspection->institution_id;
                })
                ->log('inspection_scheduled');

            DB::commit();
            $this->dispatch('toast', type: 'success', message: 'Inspection scheduled.');

            return redirect()->route('ministry.compliance.inspections.show', $inspection);
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatch('toast', type: 'error', message: 'Something went wrong. Please try again.');
        }
    }

    public function render()
    {
        return view('livewire.ministry.compliance.inspection-form-component')
        ->layout('layouts.ministry.app', [
            'title' => 'Compliances | ' . setting('app_name', 'EMS'),
        ]);
    }
}