<?php

namespace App\Http\Controllers\Api\Admin\Academic;

use App\Http\Controllers\Controller;
use App\Models\AcademicClassSchedule;
use App\Models\AcademicClassAssign;
use App\Models\AcademicClassAssignDetail;
use App\Models\AcademicClass;
use App\Models\AcademicSection;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ClassScheduleController extends Controller
{
    private array $days = [
        'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday',
    ];

    /**
     * Only classes that already have at least one Class Assign.
     */
    public function classes(Request $request): JsonResponse
    {
        $institutionId = $request->user()->institution_id;

        $classIds = AcademicClassAssign::where('institution_id', $institutionId)
            ->distinct()
            ->pluck('class_id');

        $classes = AcademicClass::whereIn('id', $classIds)
            ->where('institution_id', $institutionId)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json([
            'message' => 'Classes fetched successfully.',
            'data'    => $classes,
        ]);
    }

    /**
     * Only sections that are assigned to the given class.
     */
    public function sections(Request $request): JsonResponse
    {
        $institutionId = $request->user()->institution_id;

        $validated = $request->validate([
            'class_id' => 'required|integer',
        ]);

        $sectionIds = AcademicClassAssign::where('institution_id', $institutionId)
            ->where('class_id', $validated['class_id'])
            ->whereNotNull('section_id')
            ->distinct()
            ->pluck('section_id');

        $sections = AcademicSection::whereIn('id', $sectionIds)
            ->where('institution_id', $institutionId)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->unique('id')
            ->values();

        return response()->json([
            'message' => 'Sections fetched successfully.',
            'data'    => $sections,
        ]);
    }

    /**
     * Subjects (+ default teacher) and unique teacher list, derived from
     * AcademicClassAssignDetail, for the given class (+ optional section).
     * section_id = null  -> class-wide assign (no section)
     * section_id = 'all' -> every section under this class combined
     * section_id = <id>  -> that specific section only
     */
    public function subjectsAndTeachers(Request $request): JsonResponse
    {
        $institutionId = $request->user()->institution_id;

        $validated = $request->validate([
            'class_id'   => 'required|integer',
            'section_id' => 'nullable',
        ]);

        $classId   = $validated['class_id'];
        $sectionId = $validated['section_id'] ?? null;

        $assignQuery = AcademicClassAssign::where('institution_id', $institutionId)
            ->where('class_id', $classId);

        if ($sectionId === 'all') {
            // keep all sections under this class
        } elseif ($sectionId === null || $sectionId === '') {
            $assignQuery->whereNull('section_id');
        } else {
            $assignQuery->where('section_id', $sectionId);
        }

        $assignIds = $assignQuery->pluck('id');

        $details = AcademicClassAssignDetail::with(['subject', 'teacher'])
            ->whereIn('academic_class_assign_id', $assignIds)
            ->get();

        $subjects = $details
            ->filter(fn ($d) => $d->subject)
            ->unique('subject_id')
            ->map(fn ($d) => [
                'name'            => $d->subject->name,
                'default_teacher' => $d->teacher->name ?? '',
            ])
            ->unique('name')
            ->values();

        $teachers = $details
            ->filter(fn ($d) => $d->teacher)
            ->unique('teacher_id')
            ->map(fn ($d) => ['name' => $d->teacher->name])
            ->unique('name')
            ->values();

        return response()->json([
            'message' => 'Subjects and teachers fetched successfully.',
            'data' => [
                'subjects' => $subjects,
                'teachers' => $teachers,
            ],
        ]);
    }

    /**
     * Existing schedule rows for one specific day. If none found,
     * 'exists' is false and 'data' is an empty array.
     */
    public function show(Request $request): JsonResponse
    {
        $institutionId = $request->user()->institution_id;

        $validated = $request->validate([
            'class_id'   => 'required|integer',
            'section_id' => 'nullable|integer',
            'day'        => 'required|string',
        ]);

        $schedule = AcademicClassSchedule::where('institution_id', $institutionId)
            ->where('class_id', $validated['class_id'])
            ->where('section_id', $validated['section_id'] ?? null)
            ->where('day', $validated['day'])
            ->first();

        return response()->json([
            'message' => 'Schedule fetched successfully.',
            'data' => [
                'exists' => (bool) $schedule,
                'rows'   => $schedule ? ($schedule->data ?? []) : [],
            ],
        ]);
    }

    /**
     * Full week grid for the list screen: one entry per day (empty array if none).
     */
    public function week(Request $request): JsonResponse
    {
        $institutionId = $request->user()->institution_id;

        $validated = $request->validate([
            'class_id'   => 'required|integer',
            'section_id' => 'nullable|integer',
        ]);

        $schedules = AcademicClassSchedule::where('institution_id', $institutionId)
            ->where('class_id', $validated['class_id'])
            ->where('section_id', $validated['section_id'] ?? null)
            ->get()
            ->keyBy('day');

        $week = [];
        foreach ($this->days as $day) {
            $week[$day] = $schedules->has($day) ? ($schedules[$day]->data ?? []) : [];
        }

        return response()->json([
            'message' => 'Week schedule fetched successfully.',
            'data' => [
                'days' => $this->days,
                'week' => $week,
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $institutionId = $request->user()->institution_id;

        $validated = $request->validate([
            'class_id'               => 'required|integer',
            'section_id'             => 'nullable|integer',
            'day'                    => 'required|string|max:20',
            'rows'                   => 'required|array|min:1',
            'rows.*.subject'         => 'required|string',
            'rows.*.teacher'         => 'required|string',
            'rows.*.start_time'      => 'required|date_format:H:i',
            'rows.*.end_time'        => 'required|date_format:H:i|after:rows.*.start_time',
            'rows.*.class_room'      => 'nullable|string|max:100',
        ]);

        $schedule = AcademicClassSchedule::updateOrCreate(
            [
                'institution_id' => $institutionId,
                'class_id'       => $validated['class_id'],
                'section_id'     => $validated['section_id'] ?? null,
                'day'            => $validated['day'],
            ],
            [
                'data' => $validated['rows'],
            ]
        );

        return response()->json([
            'message' => 'Class schedule saved successfully.',
            'data'    => $schedule,
        ]);
    }
}