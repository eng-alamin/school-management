<?php

namespace App\Http\Controllers\Api\Admin\Employee;

use App\Http\Controllers\Controller;
use App\Models\EmployeeDesignation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class DesignationController extends Controller
{
    /**
     * GET /api/admin/employee/designations
     */
    public function index(Request $request): JsonResponse
    {
        $institutionId = auth()->user()->institution_id;

        $perPage = (int) $request->query('per_page', 10);
        $search  = trim((string) $request->query('search', ''));

        $designations = EmployeeDesignation::query()
            ->where('institution_id', $institutionId)
            ->when($search !== '', fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->paginate($perPage);

        return response()->json([
            'message' => 'Designations fetched successfully',
            'data'    => $designations->items(),
            'meta'    => [
                'current_page' => $designations->currentPage(),
                'last_page'    => $designations->lastPage(),
                'per_page'     => $designations->perPage(),
                'total'        => $designations->total(),
            ],
        ]);
    }

    /**
     * GET /api/admin/employee/designations/{id}
     */
    public function show(int $id): JsonResponse
    {
        $institutionId = auth()->user()->institution_id;

        $designation = EmployeeDesignation::where('institution_id', $institutionId)->find($id);

        if (! $designation) {
            return response()->json(['message' => 'Designation not found'], 404);
        }

        return response()->json([
            'message' => 'Designation fetched successfully',
            'data'    => $designation,
        ]);
    }

    /**
     * POST /api/admin/employee/designations
     */
    public function store(Request $request): JsonResponse
    {
        $institutionId = auth()->user()->institution_id;

        $validator = $this->validateDesignation($request, $institutionId);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }

        $designation = EmployeeDesignation::create([
            'institution_id' => $institutionId,
            'name'           => $validator->validated()['name'],
        ]);

        return response()->json([
            'message' => 'Designation created successfully',
            'data'    => $designation,
        ], 201);
    }

    /**
     * PUT/PATCH /api/admin/employee/designations/{id}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $institutionId = auth()->user()->institution_id;

        $designation = EmployeeDesignation::where('institution_id', $institutionId)->find($id);

        if (! $designation) {
            return response()->json(['message' => 'Designation not found'], 404);
        }

        $validator = $this->validateDesignation($request, $institutionId, $designation->id);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }

        $designation->update(['name' => $validator->validated()['name']]);

        return response()->json([
            'message' => 'Designation updated successfully',
            'data'    => $designation->fresh(),
        ]);
    }

    /**
     * DELETE /api/admin/employee/designations/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        $institutionId = auth()->user()->institution_id;

        $designation = EmployeeDesignation::where('institution_id', $institutionId)->find($id);

        if (! $designation) {
            return response()->json(['message' => 'Designation not found'], 404);
        }

        $designation->delete();

        return response()->json(['message' => 'Designation deleted successfully']);
    }

    // ── Helpers ──────────────────────────────────────────────────────────

    private function validateDesignation(Request $request, int $institutionId, ?int $ignoreId = null)
    {
        return Validator::make($request->all(), [
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('employee_designations', 'name')
                    ->where('institution_id', $institutionId)
                    ->ignore($ignoreId),
            ],
        ], [
            'name.required' => 'Designation name is required.',
            'name.unique'   => 'This designation name already exists.',
        ]);
    }
}