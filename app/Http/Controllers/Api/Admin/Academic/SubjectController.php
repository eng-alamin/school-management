<?php

namespace App\Http\Controllers\Api\Admin\Academic;

use App\Http\Controllers\Controller;
use App\Models\AcademicSubject;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class SubjectController extends Controller
{
    /**
     * GET /api/admin/academic/subjects
     * Institution-scoped list + search (name/code/author). Plain array
     * response (no pagination wrapper) — Flutter service expects `data`
     * to be a JSON List directly, same as Department/Group modules.
     */
    public function index(Request $request): JsonResponse
    {
        $institutionId = $request->user()->institution_id;

        $query = AcademicSubject::query()
            ->where('institution_id', $institutionId);

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('author', 'like', "%{$search}%");
            });
        }

        $subjects = $query->orderBy('id', 'desc')->get();

        return response()->json([
            'message' => 'Subjects fetched successfully',
            'data' => $subjects,
        ]);
    }

    /**
     * GET /api/admin/academic/subjects/{id}
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $institutionId = $request->user()->institution_id;

        $subject = AcademicSubject::where('institution_id', $institutionId)->find($id);

        if (!$subject) {
            return response()->json([
                'message' => 'Subject not found',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'message' => 'Subject fetched successfully',
            'data' => $subject,
        ]);
    }

    /**
     * POST /api/admin/academic/subjects
     */
    public function store(Request $request): JsonResponse
    {
        $institutionId = $request->user()->institution_id;

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('academic_subjects', 'name')
                    ->where(fn ($q) => $q->where('institution_id', $institutionId)),
            ],
            'code' => ['nullable', 'string', 'max:50'],
            'author' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', 'string', Rule::in(['Theory', 'Practical', 'Optional', 'Mandatory'])],
        ]);

        $subject = AcademicSubject::create([
            'institution_id' => $institutionId,
            'name' => $validated['name'],
            'code' => $validated['code'] ?? null,
            'author' => $validated['author'] ?? null,
            'type' => $validated['type'] ?? null,
        ]);

        return response()->json([
            'message' => 'Subject created successfully',
            'data' => $subject,
        ], 201);
    }

    /**
     * PUT/PATCH /api/admin/academic/subjects/{id}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $institutionId = $request->user()->institution_id;

        $subject = AcademicSubject::where('institution_id', $institutionId)->find($id);

        if (!$subject) {
            return response()->json([
                'message' => 'Subject not found',
                'data' => null,
            ], 404);
        }

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('academic_subjects', 'name')
                    ->where(fn ($q) => $q->where('institution_id', $institutionId))
                    ->ignore($subject->id),
            ],
            'code' => ['nullable', 'string', 'max:50'],
            'author' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', 'string', Rule::in(['Theory', 'Practical', 'Optional', 'Mandatory'])],
        ]);

        $subject->update([
            'name' => $validated['name'],
            'code' => $validated['code'] ?? null,
            'author' => $validated['author'] ?? null,
            'type' => $validated['type'] ?? null,
        ]);

        return response()->json([
            'message' => 'Subject updated successfully',
            'data' => $subject->fresh(),
        ]);
    }

    /**
     * DELETE /api/admin/academic/subjects/{id}
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $institutionId = $request->user()->institution_id;

        $subject = AcademicSubject::where('institution_id', $institutionId)->find($id);

        if (!$subject) {
            return response()->json([
                'message' => 'Subject not found',
                'data' => null,
            ], 404);
        }

        $subject->delete();

        return response()->json([
            'message' => 'Subject deleted successfully',
            'data' => null,
        ]);
    }
}