<?php

namespace App\Http\Controllers\Api\Admin\Academic;

use App\Http\Controllers\Controller;
use App\Models\AcademicClassSchedule;
use App\Models\AcademicClassAssignDetail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TeacherScheduleController extends Controller
{
    private array $days = [
        'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday',
    ];

    /**
     * Teachers dropdown list for this institution.
     */
    public function teachers(Request $request): JsonResponse
    {
        $institutionId = $request->user()->institution_id;

        $teachers = User::where('role', 'teacher')
            ->where('institution_id', $institutionId)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json([
            'message' => 'Teachers fetched successfully.',
            'data'    => $teachers,
        ]);
    }

    /**
     * Weekly schedule grid for one teacher, pre-built server side
     * so the mobile app just renders it (no heavy logic on device).
     */
    public function schedule(Request $request): JsonResponse
    {
        $institutionId = $request->user()->institution_id;

        $validated = $request->validate([
            'teacher_id' => 'required|integer|exists:users,id',
        ]);

        $teacherId = $validated['teacher_id'];

        $teacher = User::where('institution_id', $institutionId)
            ->find($teacherId, ['id', 'name']);

        if (!$teacher) {
            return response()->json([
                'message' => 'Teacher not found.',
                'data'    => [
                    'has_schedule' => false,
                    'days'         => $this->days,
                    'grid'         => [],
                ],
            ]);
        }

        $assignDetails = AcademicClassAssignDetail::with('classAssign')
            ->where('teacher_id', $teacherId)
            ->get();

        $classSectionPairs = $assignDetails
            ->map(fn ($d) => [
                'class_id'   => $d->classAssign->class_id ?? null,
                'section_id' => $d->classAssign->section_id ?? null,
            ])
            ->filter(fn ($p) => $p['class_id'] && ($p['institution_check'] = true))
            ->unique(fn ($p) => $p['class_id'] . '-' . $p['section_id'])
            ->values();

        if ($classSectionPairs->isEmpty()) {
            return response()->json([
                'message' => 'Schedule fetched successfully.',
                'data'    => [
                    'has_schedule' => false,
                    'days'         => $this->days,
                    'grid'         => [],
                ],
            ]);
        }

        $teacherName = trim($teacher->name);
        $allRows = collect();

        foreach ($classSectionPairs as $pair) {
            $schedules = AcademicClassSchedule::with(['class', 'section'])
                ->where('institution_id', $institutionId)
                ->where('class_id', $pair['class_id'])
                ->where('section_id', $pair['section_id'])
                ->get();

            foreach ($schedules as $schedule) {
                foreach ($schedule->data ?? [] as $period) {
                    $periodTeacher = trim($period['teacher'] ?? '');
                    if (strcasecmp($periodTeacher, $teacherName) !== 0) {
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
            return response()->json([
                'message' => 'Schedule fetched successfully.',
                'data'    => [
                    'has_schedule' => false,
                    'days'         => $this->days,
                    'grid'         => [],
                ],
            ]);
        }

        $timeSlots = $allRows
            ->filter(fn ($r) => $r['start_time'])
            ->map(fn ($r) => [
                'start_time' => $r['start_time'],
                'end_time'   => $r['end_time'],
            ])
            ->unique('start_time')
            ->sortBy('start_time')
            ->values();

        $grid = [];
        foreach ($timeSlots as $slot) {
            $row = [
                'start_time' => $slot['start_time'],
                'end_time'   => $slot['end_time'],
            ];
            foreach ($this->days as $day) {
                $match = $allRows->first(
                    fn ($r) => $r['day'] === $day && $r['start_time'] === $slot['start_time']
                );
                $row[$day] = $match ?: null;
            }
            $grid[] = $row;
        }

        return response()->json([
            'message' => 'Schedule fetched successfully.',
            'data'    => [
                'has_schedule' => true,
                'days'         => $this->days,
                'grid'         => $grid,
            ],
        ]);
    }
}