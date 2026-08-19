<?php

namespace App\Livewire\Branch\Academic;

use App\Models\AcademicClassSchedule;
use App\Models\SubstituteAssignment;
use App\Services\SubstituteTeacherService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class SubstituteTeacherComponent extends Component
{
    public string $date;

    public array $periods     = []; // key = "scheduleId-periodIndex"
    public array $suggestions = [];
    public array $selected    = [];

    public bool $loaded = false;

    public function mount()
    {
        $this->date = now()->toDateString();
    }

    public function loadPeriods()
    {
        $this->validate(['date' => 'required|date']);

        $institutionId = institution()->id;
        $service       = app(SubstituteTeacherService::class);

        $rows = $service->getPeriodsNeedingSubstitute($institutionId, $this->date);

        $this->periods     = [];
        $this->suggestions = [];
        $this->selected    = [];

        foreach ($rows as $row) {
            $key = $row['class_schedule_id'] . '-' . $row['period_index'];
            $this->periods[$key] = $row;

            if ($row['existing'] && $row['existing']->status !== SubstituteAssignment::STATUS_CANCELLED) {
                $this->selected[$key] = $row['existing']->substitute_teacher_id;
                continue;
            }

            if (!$row['start_time'] || !$row['end_time']) {
                $this->suggestions[$key] = [];
                continue;
            }

            $free = $service->suggestFreeTeachers(
                $institutionId,
                $this->date,
                $row['start_time'],
                $row['end_time'],
                $row['original_teacher_id']
            );

            $this->suggestions[$key] = $free->toArray();
            $this->selected[$key]    = $free->first()->id ?? '';
        }

        $this->loaded = true;
    }

    public function assign(string $key)
    {
        // abort_unless(auth()->user()->can('academic.substitute.assign'), 403);

        $institutionId = institution()->id;
        $row = $this->periods[$key] ?? null;

        if (!$row) {
            $this->dispatch('toast', type: 'error', message: 'Invalid period.');
            return;
        }

        $substituteTeacherId = $this->selected[$key] ?? null;
        $allowedIds = collect($this->suggestions[$key] ?? [])->pluck('id')->toArray();

        if (!$substituteTeacherId || !in_array((int) $substituteTeacherId, $allowedIds)) {
            $this->dispatch('toast', type: 'error', message: 'Please select a valid free teacher.');
            return;
        }

        // Defense-in-depth: institution_id explicit re-check
        $schedule = AcademicClassSchedule::where('institution_id', $institutionId)
            ->find($row['class_schedule_id']);

        if (!$schedule) {
            $this->dispatch('toast', type: 'error', message: 'Schedule not found.');
            return;
        }

        try {
            DB::transaction(function () use ($institutionId, $row, $substituteTeacherId, $schedule) {
                $assignment = SubstituteAssignment::updateOrCreate(
                    [
                        'institution_id'    => $institutionId,
                        'class_schedule_id' => $row['class_schedule_id'],
                        'period_index'      => $row['period_index'],
                        'date'              => $this->date,
                    ],
                    [
                        'branch_id'             => $schedule->branch_id,
                        'class_id'              => $row['class_id'],
                        'section_id'            => $row['section_id'],
                        'subject_id'            => $row['subject_id'],
                        'start_time'            => $row['start_time'],
                        'end_time'              => $row['end_time'],
                        'original_teacher_id'   => $row['original_teacher_id'],
                        'substitute_teacher_id' => $substituteTeacherId,
                        'status'                => SubstituteAssignment::STATUS_ASSIGNED,
                        'assigned_by'           => auth()->id(),
                    ]
                );

                activity()
                    ->performedOn($assignment)
                    ->causedBy(auth()->user())
                    ->withProperties(['icon' => 'swap_horiz', 'type' => 'substitute_teacher_assigned', 'date' => $this->date])
                    ->tap(function ($activity) use ($institutionId) {
                        $activity->institution_id = $institutionId;
                    })
                    ->log('Substitute teacher assigned');
            });

            $this->dispatch('toast', type: 'success', message: 'Substitute teacher assigned!');
            $this->loadPeriods();

        } catch (\Exception $e) {
            Log::error('Substitute assign failed', ['institution_id' => $institutionId, 'key' => $key, 'error' => $e->getMessage()]);
            $this->dispatch('toast', type: 'error', message: 'Something went wrong. Please try again.');
        }
    }

    public function cancel(string $key)
    {
        // abort_unless(auth()->user()->can('academic.substitute.assign'), 403);

        $institutionId = institution()->id;
        $row = $this->periods[$key] ?? null;

        if (!$row) return;

        try {
            $assignment = SubstituteAssignment::where('institution_id', $institutionId)
                ->where('class_schedule_id', $row['class_schedule_id'])
                ->where('period_index', $row['period_index'])
                ->where('date', $this->date)
                ->first();

            if ($assignment) {
                activity()
                    ->performedOn($assignment)
                    ->causedBy(auth()->user())
                    ->withProperties(['icon' => 'cancel', 'type' => 'substitute_teacher_cancelled'])
                    ->tap(function ($activity) use ($institutionId) {
                        $activity->institution_id = $institutionId;
                    })
                    ->log('Substitute teacher assignment cancelled');

                $assignment->update(['status' => SubstituteAssignment::STATUS_CANCELLED]);
            }

            $this->dispatch('toast', type: 'success', message: 'Assignment cancelled.');
            $this->loadPeriods();

        } catch (\Exception $e) {
            Log::error('Substitute cancel failed', ['error' => $e->getMessage()]);
            $this->dispatch('toast', type: 'error', message: 'Something went wrong.');
        }
    }

    public function render()
    {
        return view('livewire.admin.academic.substitute-teacher-component')
            ->layout('layouts.branch.app', [
                'title' => 'Substitute Teacher | ' . institution()->name,
            ]);
    }
}