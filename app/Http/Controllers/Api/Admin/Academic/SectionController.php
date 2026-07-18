<?php

namespace App\Http\Controllers\Api\Admin\Academic;

use App\Http\Controllers\Controller;
use App\Models\AcademicSection;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class SectionController extends Controller
{
    /**
     * GET /api/admin/academic/sections
     * Institution-scoped list + search. Plain array response
     * (no pagination wrapper) — Flutter service expects `data` to be
     * a JSON List directly, same as Department/Group/Subject modules.
     */
    public function index(Request $request): JsonResponse
    {
        $institutionId = $request->user()->institution_id;

        $query = AcademicSection::query()
            ->where('institution_id', $institutionId);

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->string('search') . '%');
        }

        $sections = $query->orderBy('id', 'desc')->get();

        return response()->json([
            'message' => 'Sections fetched successfully',
            'data' => $sections,
        ]);
    }

    /**
     * GET /api/admin/academic/sections/{id}
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $institutionId = $request->user()->institution_id;

        $section = AcademicSection::where('institution_id', $institutionId)->find($id);

        if (!$section) {
            return response()->json([
                'message' => 'Section not found',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'message' => 'Section fetched successfully',
            'data' => $section,
        ]);
    }

    /**
     * POST /api/admin/academic/sections
     */
    public function store(Request $request): JsonResponse
    {
        $institutionId = $request->user()->institution_id;

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('academic_sections', 'name')
                    ->where(fn ($q) => $q->where('institution_id', $institutionId)),
            ],
            'capacity' => ['nullable', 'integer', 'min:0'],
        ]);

        $section = AcademicSection::create([
            'institution_id' => $institutionId,
            'name' => $validated['name'],
            'capacity' => $validated['capacity'] ?? null,
        ]);

        return response()->json([
            'message' => 'Section created successfully',
            'data' => $section,
        ], 201);
    }

    /**
     * PUT/PATCH /api/admin/academic/sections/{id}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $institutionId = $request->user()->institution_id;

        $section = AcademicSection::where('institution_id', $institutionId)->find($id);

        if (!$section) {
            return response()->json([
                'message' => 'Section not found',
                'data' => null,
            ], 404);
        }

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('academic_sections', 'name')
                    ->where(fn ($q) => $q->where('institution_id', $institutionId))
                    ->ignore($section->id),
            ],
            'capacity' => ['nullable', 'integer', 'min:0'],
        ]);

        $section->update([
            'name' => $validated['name'],
            'capacity' => $validated['capacity'] ?? null,
        ]);

        return response()->json([
            'message' => 'Section updated successfully',
            'data' => $section->fresh(),
        ]);
    }

    /**
     * DELETE /api/admin/academic/sections/{id}
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $institutionId = $request->user()->institution_id;

        $section = AcademicSection::where('institution_id', $institutionId)->find($id);

        if (!$section) {
            return response()->json([
                'message' => 'Section not found',
                'data' => null,
            ], 404);
        }

        $section->delete();

        return response()->json([
            'message' => 'Section deleted successfully',
            'data' => null,
        ]);
    }
}