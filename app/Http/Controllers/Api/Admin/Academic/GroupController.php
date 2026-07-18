<?php

namespace App\Http\Controllers\Api\Admin\Academic;

use App\Http\Controllers\Controller;
use App\Models\AcademicGroup;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class GroupController extends Controller
{
    /**
     * GET /api/admin/academic/groups
     * Institution-scoped list + search. Plain array response
     * (no pagination wrapper) — Flutter service expects `data` to be
     * a JSON List directly, same as Department/Designation modules.
     */
    public function index(Request $request): JsonResponse
    {
        $institutionId = $request->user()->institution_id;

        $query = AcademicGroup::query()
            ->where('institution_id', $institutionId);

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->string('search') . '%');
        }

        $groups = $query->orderBy('id', 'desc')->get();

        return response()->json([
            'message' => 'Academic groups fetched successfully',
            'data' => $groups,
        ]);
    }

    /**
     * GET /api/admin/academic/groups/{id}
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $institutionId = $request->user()->institution_id;

        $group = AcademicGroup::where('institution_id', $institutionId)->find($id);

        if (!$group) {
            return response()->json([
                'message' => 'Academic group not found',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'message' => 'Academic group fetched successfully',
            'data' => $group,
        ]);
    }

    /**
     * POST /api/admin/academic/groups
     */
    public function store(Request $request): JsonResponse
    {
        $institutionId = $request->user()->institution_id;

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('academic_groups', 'name')
                    ->where(fn ($q) => $q->where('institution_id', $institutionId)),
            ],
        ]);

        $group = AcademicGroup::create([
            'institution_id' => $institutionId,
            'name' => $validated['name'],
        ]);

        return response()->json([
            'message' => 'Academic group created successfully',
            'data' => $group,
        ], 201);
    }

    /**
     * PUT/PATCH /api/admin/academic/groups/{id}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $institutionId = $request->user()->institution_id;

        $group = AcademicGroup::where('institution_id', $institutionId)->find($id);

        if (!$group) {
            return response()->json([
                'message' => 'Academic group not found',
                'data' => null,
            ], 404);
        }

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('academic_groups', 'name')
                    ->where(fn ($q) => $q->where('institution_id', $institutionId))
                    ->ignore($group->id),
            ],
        ]);

        $group->update([
            'name' => $validated['name'],
        ]);

        return response()->json([
            'message' => 'Academic group updated successfully',
            'data' => $group->fresh(),
        ]);
    }

    /**
     * DELETE /api/admin/academic/groups/{id}
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $institutionId = $request->user()->institution_id;

        $group = AcademicGroup::where('institution_id', $institutionId)->find($id);

        if (!$group) {
            return response()->json([
                'message' => 'Academic group not found',
                'data' => null,
            ], 404);
        }

        $group->delete();

        return response()->json([
            'message' => 'Academic group deleted successfully',
            'data' => null,
        ]);
    }
}