<?php

namespace App\Http\Controllers\Api\Admin\Academic;

use App\Http\Controllers\Controller;
use App\Models\AcademicClass;
use App\Models\AcademicClassSection;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class ClassController extends Controller
{
    /**
     * GET /api/admin/academic/classes
     * Institution-scoped list + search (name/numeric/section name).
     * Plain array response (no pagination wrapper) — Flutter service
     * expects `data` to be a JSON List directly, same as other modules.
     * `sections` relation eager-loaded so the app can show badges
     * without an extra request per class.
     */
    public function index(Request $request): JsonResponse
    {
        $institutionId = $request->user()->institution_id;

        $query = AcademicClass::with('sections')
            ->where('institution_id', $institutionId);

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('numeric', 'like', "%{$search}%")
                    ->orWhereHas('sections', fn ($q2) => $q2->where('name', 'like', "%{$search}%"));
            });
        }

        $classes = $query->orderBy('id', 'desc')->get();

        return response()->json([
            'message' => 'Classes fetched successfully',
            'data' => $classes,
        ]);
    }

    /**
     * GET /api/admin/academic/classes/{id}
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $institutionId = $request->user()->institution_id;

        $class = AcademicClass::with('sections')
            ->where('institution_id', $institutionId)
            ->find($id);

        if (!$class) {
            return response()->json([
                'message' => 'Class not found',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'message' => 'Class fetched successfully',
            'data' => $class,
        ]);
    }

    /**
     * POST /api/admin/academic/classes
     */
    public function store(Request $request): JsonResponse
    {
        $institutionId = $request->user()->institution_id;

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('academic_classes', 'name')
                    ->where(fn ($q) => $q->where('institution_id', $institutionId)),
            ],
            'numeric' => ['nullable', 'integer'],
            'section_ids' => ['nullable', 'array'],
            'section_ids.*' => ['integer', 'exists:academic_sections,id'],
        ]);

        $class = AcademicClass::create([
            'institution_id' => $institutionId,
            'name' => $validated['name'],
            'numeric' => $validated['numeric'] ?? null,
        ]);

        $this->syncSections($class->id, $validated['section_ids'] ?? []);

        return response()->json([
            'message' => 'Class created successfully',
            'data' => $class->fresh('sections'),
        ], 201);
    }

    /**
     * PUT/PATCH /api/admin/academic/classes/{id}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $institutionId = $request->user()->institution_id;

        $class = AcademicClass::where('institution_id', $institutionId)->find($id);

        if (!$class) {
            return response()->json([
                'message' => 'Class not found',
                'data' => null,
            ], 404);
        }

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('academic_classes', 'name')
                    ->where(fn ($q) => $q->where('institution_id', $institutionId))
                    ->ignore($class->id),
            ],
            'numeric' => ['nullable', 'integer'],
            'section_ids' => ['nullable', 'array'],
            'section_ids.*' => ['integer', 'exists:academic_sections,id'],
        ]);

        $class->update([
            'name' => $validated['name'],
            'numeric' => $validated['numeric'] ?? null,
        ]);

        $this->syncSections($class->id, $validated['section_ids'] ?? []);

        return response()->json([
            'message' => 'Class updated successfully',
            'data' => $class->fresh('sections'),
        ]);
    }

    /**
     * DELETE /api/admin/academic/classes/{id}
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $institutionId = $request->user()->institution_id;

        $class = AcademicClass::where('institution_id', $institutionId)->find($id);

        if (!$class) {
            return response()->json([
                'message' => 'Class not found',
                'data' => null,
            ], 404);
        }

        AcademicClassSection::where('class_id', $class->id)->delete();
        $class->delete();

        return response()->json([
            'message' => 'Class deleted successfully',
            'data' => null,
        ]);
    }

    /**
     * Replaces all pivot rows for a class with the given section ids.
     * Delete-then-insert (same approach as the Livewire component) keeps
     * this simple and avoids needing a belongsToMany relation just for sync().
     */
    private function syncSections(int $classId, array $sectionIds): void
    {
        AcademicClassSection::where('class_id', $classId)->delete();

        foreach (array_unique($sectionIds) as $sectionId) {
            AcademicClassSection::create([
                'class_id' => $classId,
                'section_id' => $sectionId,
            ]);
        }
    }
}