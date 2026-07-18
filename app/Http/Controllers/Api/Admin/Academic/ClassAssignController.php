<?php

namespace App\Http\Controllers\Api\Admin\Academic;

use App\Http\Controllers\Controller;
use App\Models\AcademicClassAssign;
use App\Models\AcademicClassAssignDetail;
use App\Models\AcademicClass;
use App\Models\AcademicSubject;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ClassAssignController extends Controller
{
    /**
     * List all class assignments for the logged-in institution.
     * NOTE: get() use kora hoyeche, paginate() na — Flutter-e plain List expect kora hoy.
     */
    public function index(Request $request): JsonResponse
    {
        $institutionId = $request->user()->institution_id;

        $assigns = AcademicClassAssign::with(['class', 'section', 'details.subject', 'details.teacher'])
            ->where('institution_id', $institutionId)
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'message' => 'Class assignments fetched successfully.',
            'data'    => $assigns,
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $institutionId = $request->user()->institution_id;

        $assign = AcademicClassAssign::with(['class', 'section', 'details.subject', 'details.teacher'])
            ->where('institution_id', $institutionId)
            ->find($id);

        if (!$assign) {
            return response()->json(['message' => 'Class assignment not found.'], 404);
        }

        return response()->json([
            'message' => 'Class assignment fetched successfully.',
            'data'    => $assign,
        ]);
    }

    /**
     * Dropdown data for the Flutter form: classes (with sections), subjects, teachers.
     */
    public function formData(Request $request): JsonResponse
    {
        $institutionId = $request->user()->institution_id;

        $classes = AcademicClass::with('sections:id,name')
            ->where('institution_id', $institutionId)
            ->orderBy('name')
            ->get(['id', 'name']);

        $subjects = AcademicSubject::where('institution_id', $institutionId)
            ->orderBy('name')
            ->get(['id', 'name']);

        $teachers = User::where('institution_id', $institutionId)
            ->where('role', User::ROLE_TEACHER)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json([
            'message' => 'Form data fetched successfully.',
            'data' => [
                'classes'  => $classes,
                'subjects' => $subjects,
                'teachers' => $teachers,
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $institutionId = $request->user()->institution_id;

        $validated = $request->validate([
            'class_id'              => [
                'required', 'integer',
                Rule::exists('academic_classes', 'id')->where('institution_id', $institutionId),
            ],
            'section_id'            => [
                'nullable', 'integer',
                Rule::exists('academic_sections', 'id')->where('institution_id', $institutionId),
            ],
            'subjects'               => ['nullable', 'array'],
            'subjects.*.subject_id'  => [
                'required', 'integer',
                Rule::exists('academic_subjects', 'id')->where('institution_id', $institutionId),
            ],
            'subjects.*.teacher_id'  => [
                'nullable', 'integer',
                Rule::exists('users', 'id')->where('institution_id', $institutionId),
            ],
        ]);

        $assign = DB::transaction(function () use ($validated, $institutionId) {
            $assign = AcademicClassAssign::create([
                'institution_id' => $institutionId,
                'class_id'       => $validated['class_id'],
                'section_id'     => $validated['section_id'] ?? null,
            ]);

            foreach ($validated['subjects'] ?? [] as $row) {
                AcademicClassAssignDetail::create([
                    'academic_class_assign_id' => $assign->id,
                    'subject_id'               => $row['subject_id'],
                    'teacher_id'               => $row['teacher_id'] ?? null,
                ]);
            }

            return $assign->load(['class', 'section', 'details.subject', 'details.teacher']);
        });

        return response()->json([
            'message' => 'Class assigned successfully.',
            'data'    => $assign,
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $institutionId = $request->user()->institution_id;

        $assign = AcademicClassAssign::where('institution_id', $institutionId)->find($id);

        if (!$assign) {
            return response()->json(['message' => 'Class assignment not found.'], 404);
        }

        $validated = $request->validate([
            'class_id'              => [
                'required', 'integer',
                Rule::exists('academic_classes', 'id')->where('institution_id', $institutionId),
            ],
            'section_id'            => [
                'nullable', 'integer',
                Rule::exists('academic_sections', 'id')->where('institution_id', $institutionId),
            ],
            'subjects'               => ['nullable', 'array'],
            'subjects.*.subject_id'  => [
                'required', 'integer',
                Rule::exists('academic_subjects', 'id')->where('institution_id', $institutionId),
            ],
            'subjects.*.teacher_id'  => [
                'nullable', 'integer',
                Rule::exists('users', 'id')->where('institution_id', $institutionId),
            ],
        ]);

        DB::transaction(function () use ($assign, $validated) {
            $assign->update([
                'class_id'   => $validated['class_id'],
                'section_id' => $validated['section_id'] ?? null,
            ]);

            // Purano details mucche notun kore boshano (simple & safe approach)
            $assign->details()->delete();

            foreach ($validated['subjects'] ?? [] as $row) {
                AcademicClassAssignDetail::create([
                    'academic_class_assign_id' => $assign->id,
                    'subject_id'               => $row['subject_id'],
                    'teacher_id'               => $row['teacher_id'] ?? null,
                ]);
            }
        });

        $assign = $assign->fresh(['class', 'section', 'details.subject', 'details.teacher']);

        return response()->json([
            'message' => 'Class assignment updated successfully.',
            'data'    => $assign,
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $institutionId = $request->user()->institution_id;

        $assign = AcademicClassAssign::where('institution_id', $institutionId)->find($id);

        if (!$assign) {
            return response()->json(['message' => 'Class assignment not found.'], 404);
        }

        DB::transaction(function () use ($assign) {
            $assign->details()->delete();
            $assign->delete();
        });

        return response()->json(['message' => 'Class assignment deleted successfully.']);
    }
}