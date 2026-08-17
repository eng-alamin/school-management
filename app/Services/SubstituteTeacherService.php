<?php

namespace App\Services;

use App\Models\AcademicClassSchedule;
use App\Models\AcademicSubject;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\SubstituteAssignment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class SubstituteTeacherService
{
    /**
     * Given date, sob class-schedule scan kore ber kore kon kon period e
     * assigned teacher shei din absent/leave e ache.
     */
    public function getPeriodsNeedingSubstitute(int $institutionId, string $date): Collection
    {
        $dayName = Carbon::parse($date)->format('l'); // 'Saturday' style — schedules.day er shathe match

        $absentTeacherIds = $this->getAbsentTeacherIds($institutionId, $date);

        if ($absentTeacherIds->isEmpty()) {
            return collect();
        }

        $schedules = AcademicClassSchedule::with(['academicClass', 'academicSection'])
            ->where('institution_id', $institutionId)
            ->where('day', $dayName)
            ->get();

        if ($schedules->isEmpty()) {
            return collect();
        }

        // Ei date-er jonno already toiri kora substitute row gula (pending/assigned/etc)
        $existing = SubstituteAssignment::where('institution_id', $institutionId)
            ->where('date', $date)
            ->get()
            ->keyBy(fn ($s) => $s->class_schedule_id . '-' . $s->period_index);

        $raw = collect();

        foreach ($schedules as $schedule) {
            foreach ((array) $schedule->data as $index => $period) {
                $teacherId = $period['teacher_id'] ?? null;

                if (!$teacherId || !$absentTeacherIds->contains((int) $teacherId)) {
                    continue;
                }

                $raw->push([
                    'class_schedule_id'   => $schedule->id,
                    'period_index'        => $index,
                    'class_id'            => $schedule->class_id,
                    'section_id'          => $schedule->section_id,
                    'class_name'          => $schedule->academicClass?->name ?? '—',
                    'section_name'        => $schedule->academicSection?->name ?? '',
                    'subject_id'          => $period['subject_id'] ?? null,
                    'start_time'          => $period['start_time'] ?? null,
                    'end_time'            => $period['end_time'] ?? null,
                    'class_room'          => $period['class_room'] ?? null,
                    'original_teacher_id' => (int) $teacherId,
                    'existing'            => $existing->get($schedule->id . '-' . $index),
                ]);
            }
        }

        if ($raw->isEmpty()) {
            return $raw;
        }

        // N+1 thekano: subject/teacher naam ekbare bulk resolve
        $subjectIds   = $raw->pluck('subject_id')->filter()->unique();
        $subjectNames = AcademicSubject::whereIn('id', $subjectIds)->pluck('name', 'id');

        $teacherIds = $raw->pluck('original_teacher_id')
            ->merge($raw->pluck('existing')->filter()->pluck('substitute_teacher_id')->filter())
            ->unique();
        $teacherNames = User::whereIn('id', $teacherIds)->pluck('name', 'id');

        return $raw->map(function ($row) use ($subjectNames, $teacherNames) {
            $row['subject_name']    = $subjectNames[$row['subject_id']] ?? '—';
            $row['teacher_name']    = $teacherNames[$row['original_teacher_id']] ?? '—';
            $row['substitute_name'] = $row['existing']
                ? ($teacherNames[$row['existing']->substitute_teacher_id] ?? '—')
                : null;
            return $row;
        })->sortBy('start_time')->values();
    }

    /**
     * Nirdishto date+time slot-e "free" teacher der list ber kore.
     * Exclude: (1) absent teacher, (2) shei somoy onno kono class-e already assigned teacher,
     * (3) shei somoy already onno kono period-e substitute hishebe boshano teacher.
     */
    public function suggestFreeTeachers(
        int $institutionId,
        string $date,
        string $startTime,
        string $endTime,
        ?int $excludeTeacherId = null
    ): Collection {
        $dayName = Carbon::parse($date)->format('l');

        $absentTeacherIds = $this->getAbsentTeacherIds($institutionId, $date);
        $busyTeacherIds   = collect();

        $schedules = AcademicClassSchedule::where('institution_id', $institutionId)
            ->where('day', $dayName)
            ->get();

        foreach ($schedules as $schedule) {
            foreach ((array) $schedule->data as $period) {
                $pStart   = $period['start_time'] ?? null;
                $pEnd     = $period['end_time'] ?? null;
                $pTeacher = $period['teacher_id'] ?? null;

                if (!$pTeacher || !$pStart || !$pEnd) {
                    continue;
                }

                if ($this->timesOverlap($startTime, $endTime, $pStart, $pEnd)) {
                    $busyTeacherIds->push((int) $pTeacher);
                }
            }
        }

        $subRows = SubstituteAssignment::where('institution_id', $institutionId)
            ->where('date', $date)
            ->whereNotNull('substitute_teacher_id')
            ->whereIn('status', [SubstituteAssignment::STATUS_ASSIGNED, SubstituteAssignment::STATUS_CONFIRMED])
            ->get();

        foreach ($subRows as $row) {
            if ($row->start_time && $row->end_time
                && $this->timesOverlap($startTime, $endTime, $row->start_time, $row->end_time)) {
                $busyTeacherIds->push((int) $row->substitute_teacher_id);
            }
        }

        $excludedIds = $absentTeacherIds
            ->merge($busyTeacherIds)
            ->when($excludeTeacherId, fn ($c) => $c->push($excludeTeacherId))
            ->unique();

        return User::where('institution_id', $institutionId)
            ->where('role', User::ROLE_TEACHER)
            ->whereNotIn('id', $excludedIds)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    /**
     * Attendance polymorphic-e Employee er upor thake, tai Employee.user_id
     * diye actual teacher (User) id-te map kora hocche.
     */
    protected function getAbsentTeacherIds(int $institutionId, string $date): Collection
    {
        $employeeIds = Attendance::where('institution_id', $institutionId)
            ->where('type', 'employee')
            ->where('attendable_type', Employee::class)
            ->where('date', $date)
            ->whereIn('status', ['absent', 'leave'])
            ->pluck('attendable_id');

        if ($employeeIds->isEmpty()) {
            return collect();
        }

        return Employee::where('institution_id', $institutionId)
            ->whereIn('id', $employeeIds)
            ->whereNotNull('user_id')
            ->pluck('user_id')
            ->map(fn ($id) => (int) $id);
    }

    protected function timesOverlap(string $start1, string $end1, string $start2, string $end2): bool
    {
        return $start1 < $end2 && $start2 < $end1;
    }
}