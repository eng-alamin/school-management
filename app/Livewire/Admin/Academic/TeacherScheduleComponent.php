<?php

namespace App\Livewire\Admin\Academic;

use Livewire\Component;
use App\Models\AcademicClassSchedule;
use App\Models\AcademicClassAssignDetail;
use App\Models\User;

class TeacherScheduleComponent extends Component
{
    public string $teacher_id = '';

    public bool  $hasSchedule  = false;
    public array $scheduleGrid = [];
    public array $days         = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

    public function filter(): void
    {
        $this->validate([
            'teacher_id' => 'required|exists:users,id',
        ]);

        $this->hasSchedule  = false;
        $this->scheduleGrid = [];

        // এই teacher যে class+section এ assign আছে সেগুলো বের করো
        // academic_class_assign_details.teacher_id দিয়ে
        $assignDetails = AcademicClassAssignDetail::with('classAssign')
            ->where('teacher_id', $this->teacher_id)
            ->get();

        if ($assignDetails->isEmpty()) {
            return;
        }

        // class+section pair collect করো
        $classSectionPairs = $assignDetails
            ->map(fn($d) => [
                'class_id'   => $d->classAssign->class_id ?? null,
                'section_id' => $d->classAssign->section_id ?? null,
            ])
            ->filter(fn($p) => $p['class_id'])
            ->unique(fn($p) => $p['class_id'] . '-' . $p['section_id'])
            ->values();

        // Teacher এর নাম বের করো (schedule JSON এ teacher নাম string হিসেবে আছে)
        $teacher = User::find($this->teacher_id);
        $teacherName = $teacher?->name ?? '';

        $allRows = collect();

        foreach ($classSectionPairs as $pair) {
            $schedules = AcademicClassSchedule::with(['class', 'section'])
                ->where('class_id', $pair['class_id'])
                ->where('section_id', $pair['section_id'])
                ->get();

            foreach ($schedules as $schedule) {
                foreach ($schedule->data ?? [] as $period) {
                    // JSON এ teacher নাম match করো
                    $periodTeacher = $period['teacher'] ?? '';
                    if (strcasecmp(trim($periodTeacher), trim($teacherName)) !== 0) {
                        continue;
                    }

                    $allRows->push([
                        'day'        => $schedule->day,
                        'class'      => $schedule->class?->name   ?? '—',
                        'section'    => $schedule->section?->name ?? '',
                        'subject'    => $period['subject']    ?? '—',
                        'teacher'    => $period['teacher']    ?? '—',
                        'start_time' => $period['start_time'] ?? null,
                        'end_time'   => $period['end_time']   ?? null,
                        'class_room' => $period['class_room'] ?? null,
                    ]);
                }
            }
        }

        if ($allRows->isEmpty()) {
            return;
        }

        // Unique time slots sort by start_time
        $timeSlots = $allRows
            ->map(fn($r) => ['start_time' => $r['start_time'], 'end_time' => $r['end_time']])
            ->unique('start_time')
            ->sortBy('start_time')
            ->values();

        // Grid: period × day
        $grid = [];
        foreach ($timeSlots as $slot) {
            $row = [
                'start_time' => $slot['start_time'],
                'end_time'   => $slot['end_time'],
            ];
            foreach ($this->days as $day) {
                $match = $allRows->first(
                    fn($r) => $r['day'] === $day && $r['start_time'] === $slot['start_time']
                );
                $row[$day] = $match ?: null;
            }
            $grid[] = $row;
        }

        $this->scheduleGrid = $grid;
        $this->hasSchedule  = true;
    }

    public function render()
    {
        // Teacher role এর সব user
        $teachers = User::where('role', 'teacher')
            ->where('institution_id', institution()->id)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('livewire.admin.academic.teacher-schedule-component')
            ->with('teachers', $teachers)
            ->layout('layouts.admin.app', [
                'title' => 'Teacher Schedule | ' . institution()->name,
            ]);
    }
}