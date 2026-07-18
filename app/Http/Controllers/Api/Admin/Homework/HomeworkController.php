<?php

namespace App\Http\Controllers\Api\Admin\Homework;

use App\Http\Controllers\Controller;
use App\Models\Homework;
use App\Models\AcademicClass;
use App\Models\AcademicClassAssign;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Database\QueryException;

class HomeworkController extends Controller
{
    /**
     * NOTE: 'homeworks' table-e institution_id column ache dhore ei controller
     * lekha hoyeche (Parent/Student module-er moto direct scoping). Jodi
     * homeworks table-e institution_id na thake, tahole eta class relation
     * diye scope korte hobe (whereHas('class', fn($q) => $q->where('institution_id', ...))).
     * Ei assumption age confirm kore nao.
     */
    public function index(Request $request): JsonResponse
    {
        $institutionId = $request->user()->institution_id;
        $search = trim((string) $request->query('search', ''));
        $classId = $request->query('class_id');
        $sectionId = $request->query('section_id');
        $perPage = (int) $request->query('per_page', 20);
        if ($perPage <= 0 || $perPage > 100) {
            $perPage = 20;
        }

        $query = Homework::with(['class', 'section', 'subject', 'teacher'])
            ->where('institution_id', $institutionId);

        if ($classId) {
            $query->where('class_id', $classId);
        }

        if ($sectionId) {
            $query->where('section_id', $sectionId);
        }

        if ($search !== '') {
            $query->where('title', 'like', "%{$search}%");
        }

        $homeworks = $query->latest()->paginate($perPage);

        return response()->json([
            'message' => 'Homeworks fetched successfully.',
            'data'    => $homeworks->items(),
            'meta'    => [
                'current_page' => $homeworks->currentPage(),
                'last_page'    => $homeworks->lastPage(),
                'total'        => $homeworks->total(),
                'per_page'     => $homeworks->perPage(),
            ],
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $institutionId = $request->user()->institution_id;

        $homework = Homework::with(['class', 'section', 'subject', 'teacher'])
            ->where('institution_id', $institutionId)
            ->findOrFail($id);

        return response()->json([
            'message' => 'Homework fetched successfully.',
            'data'    => $homework,
        ]);
    }

    /**
     * Add/Edit form-er jonno dropdown data dey.
     * - class_id na dile: shudhu classes + teachers (initial load)
     * - class_id dile: + available sections
     * - class_id + section_id (ba section_id na dile "no section" mode) dile: + available subjects
     *
     * Query params: ?class_id=&section_id=
     */
    public function formData(Request $request): JsonResponse
    {
        $institutionId = $request->user()->institution_id;
        $classId = $request->query('class_id');
        $sectionId = $request->query('section_id'); // 'all' | numeric id | null

        $classes = AcademicClass::whereIn('id', AcademicClassAssign::distinct()->pluck('class_id'))
            ->where('institution_id', $institutionId)
            ->orderBy('name')
            ->get(['id', 'name']);

        $teachers = Employee::where('institution_id', $institutionId)
            ->orderBy('name')
            ->get(['id', 'name']);

        $sections = [];
        $subjects = [];

        if ($classId) {
            $assigns = AcademicClassAssign::with('section')
                ->where('class_id', $classId)
                ->whereNotNull('section_id')
                ->get();

            $sections = $assigns
                ->filter(fn($a) => $a->section)
                ->map(fn($a) => ['id' => $a->section->id, 'name' => $a->section->name])
                ->unique('id')
                ->values();

            $resolvedSectionId = ($sectionId && $sectionId !== 'all') ? $sectionId : null;

            $subjectQuery = AcademicClassAssign::where('class_id', $classId);
            if ($resolvedSectionId) {
                $subjectQuery->where('section_id', $resolvedSectionId);
            } else {
                $subjectQuery->whereNull('section_id');
            }

            $assign = $subjectQuery->with('details.subject')->first();

            if ($assign && $assign->details->isNotEmpty()) {
                $subjects = $assign->details
                    ->filter(fn($detail) => $detail->subject)
                    ->map(fn($detail) => ['id' => $detail->subject->id, 'name' => $detail->subject->name])
                    ->unique('id')
                    ->sortBy('name')
                    ->values();
            }
        }

        return response()->json([
            'message' => 'Form data fetched successfully.',
            'data'    => [
                'classes'  => $classes,
                'teachers' => $teachers,
                'sections' => $sections,
                'subjects' => $subjects,
            ],
        ]);
    }

    /**
     * Currently valid subject_id list-er against tamper-proof validation e
     * use hoy — client-er pathano subject_id ta shotti current class/section
     * combination-er sathe assigned kina check kore.
     */
    private function validSubjectIds(int $classId, $sectionId): array
    {
        $resolvedSectionId = ($sectionId && $sectionId !== 'all') ? $sectionId : null;

        $query = AcademicClassAssign::where('class_id', $classId);
        if ($resolvedSectionId) {
            $query->where('section_id', $resolvedSectionId);
        } else {
            $query->whereNull('section_id');
        }

        $assign = $query->with('details')->first();

        return $assign ? $assign->details->pluck('subject_id')->toArray() : [];
    }

    private function validSectionIds(int $classId): array
    {
        return AcademicClassAssign::where('class_id', $classId)
            ->whereNotNull('section_id')
            ->pluck('section_id')
            ->map(fn($id) => (string) $id)
            ->toArray();
    }

    private function rules(Request $request, ?int $classIdForCheck = null): array
    {
        return [
            'class_id'        => 'required|exists:academic_classes,id',
            'section_id'      => [
                'nullable',
                function ($attribute, $value, $fail) use ($request) {
                    $classId = (int) $request->input('class_id');
                    if ($value && $value !== 'all' && $classId && !in_array((string) $value, $this->validSectionIds($classId))) {
                        $fail('Selected section is not valid for the selected class.');
                    }
                },
            ],
            'subject_id'      => [
                'required',
                'exists:academic_subjects,id',
                function ($attribute, $value, $fail) use ($request) {
                    $classId = (int) $request->input('class_id');
                    $sectionId = $request->input('section_id');
                    if ($classId && !in_array((int) $value, $this->validSubjectIds($classId, $sectionId))) {
                        $fail('Selected subject is not assigned to the selected class/section.');
                    }
                },
            ],
            'teacher_id'      => 'nullable|exists:employees,id',
            'title'           => 'required|string|max:255',
            'description'     => 'required|string',
            'homework_date'   => 'required|date',
            'submission_date' => 'required|date|after_or_equal:homework_date',
            'published_later' => 'nullable|boolean',
            'schedule_date'   => 'nullable|required_if:published_later,1|date|after:now',
            'attachment'      => 'nullable|file|max:10240',
            'send_sms'        => 'nullable|boolean',
            'status'          => ['required', Rule::in(['draft', 'published', 'closed'])],
        ];
    }

    public function store(Request $request): JsonResponse
    {
        $institutionId = $request->user()->institution_id;
        $validated = $request->validate($this->rules($request));

        $attachmentPath = null;

        try {
            if ($request->hasFile('attachment')) {
                $attachmentPath = $request->file('attachment')->store('homeworks', 'public');
            }

            $sectionId = ($validated['section_id'] ?? null) && $validated['section_id'] !== 'all'
                ? $validated['section_id']
                : null;

            $homework = DB::transaction(function () use ($request, $validated, $institutionId, $sectionId, $attachmentPath) {
                $homework = Homework::create([
                    'institution_id'   => $institutionId,
                    'class_id'         => $validated['class_id'],
                    'section_id'       => $sectionId,
                    'subject_id'       => $validated['subject_id'],
                    'teacher_id'       => $validated['teacher_id'] ?? null,
                    'title'            => $validated['title'],
                    'description'      => $validated['description'],
                    'homework_date'    => $validated['homework_date'],
                    'submission_date'  => $validated['submission_date'],
                    'published_later'  => $request->boolean('published_later'),
                    'schedule_date'    => $validated['schedule_date'] ?? null,
                    'attachment'       => $attachmentPath,
                    'send_sms'         => $request->boolean('send_sms'),
                    'status'           => $validated['status'],
                ]);

                if (function_exists('activity')) {
                    activity()->performedOn($homework)->log('Homework "' . $homework->title . '" created');
                }

                return $homework;
            });

            return response()->json([
                'message' => 'Homework created successfully.',
                'data'    => $homework->fresh(['class', 'section', 'subject', 'teacher']),
            ], 201);
        } catch (\Throwable $e) {
            if ($attachmentPath) {
                Storage::disk('public')->delete($attachmentPath);
            }
            throw $e;
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $institutionId = $request->user()->institution_id;

        $homework = Homework::where('institution_id', $institutionId)->findOrFail($id);

        $validated = $request->validate($this->rules($request));

        $newAttachmentPath = null;
        $oldAttachmentPath = null;

        try {
            $sectionId = ($validated['section_id'] ?? null) && $validated['section_id'] !== 'all'
                ? $validated['section_id']
                : null;

            if ($request->hasFile('attachment')) {
                $newAttachmentPath = $request->file('attachment')->store('homeworks', 'public');
                $oldAttachmentPath = $homework->attachment;
            }

            $attachmentPath = $newAttachmentPath ?: $homework->attachment;

            DB::transaction(function () use ($request, $homework, $validated, $sectionId, $attachmentPath) {
                $homework->update([
                    'class_id'         => $validated['class_id'],
                    'section_id'       => $sectionId,
                    'subject_id'       => $validated['subject_id'],
                    'teacher_id'       => $validated['teacher_id'] ?? null,
                    'title'            => $validated['title'],
                    'description'      => $validated['description'],
                    'homework_date'    => $validated['homework_date'],
                    'submission_date'  => $validated['submission_date'],
                    'published_later'  => $request->boolean('published_later'),
                    'schedule_date'    => $validated['schedule_date'] ?? null,
                    'attachment'       => $attachmentPath,
                    'send_sms'         => $request->boolean('send_sms'),
                    'status'           => $validated['status'],
                ]);

                if (function_exists('activity')) {
                    activity()->performedOn($homework)->log('Homework "' . $homework->title . '" updated');
                }
            });

            // DB commit successful hoyar por-i purono file delete kora — orphan file avoid korte.
            if ($oldAttachmentPath) {
                Storage::disk('public')->delete($oldAttachmentPath);
            }

            return response()->json([
                'message' => 'Homework updated successfully.',
                'data'    => $homework->fresh(['class', 'section', 'subject', 'teacher']),
            ]);
        } catch (\Throwable $e) {
            if ($newAttachmentPath) {
                Storage::disk('public')->delete($newAttachmentPath);
            }
            throw $e;
        }
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $institutionId = $request->user()->institution_id;

        $homework = Homework::where('institution_id', $institutionId)->findOrFail($id);

        DB::beginTransaction();
        try {
            $attachmentPath = $homework->attachment;

            if (function_exists('activity')) {
                activity()->performedOn($homework)->log('Homework "' . $homework->title . '" deleted');
            }

            $homework->delete();
            DB::commit();

            if ($attachmentPath) {
                Storage::disk('public')->delete($attachmentPath);
            }

            return response()->json(['message' => 'Homework deleted successfully.']);
        } catch (QueryException $e) {
            DB::rollBack();

            if ($e->getCode() === '23000') {
                return response()->json([
                    'message' => 'Ei Homework ke delete kora jacche na, karon tar shathe kono related record linked ache.',
                ], 422);
            }

            throw $e;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}