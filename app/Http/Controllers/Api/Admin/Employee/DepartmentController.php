<?php

namespace App\Http\Controllers\Api\Admin\Employee;

use App\Http\Controllers\Controller;
use App\Models\EmployeeDepartment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class DepartmentController extends Controller
{
    /**
     * GET /api/admin/employee/departments
     */
    public function index(Request $request): JsonResponse
    {
        $institutionId = auth()->user()->institution_id;

        $perPage = (int) $request->query('per_page', 10);
        $search  = trim((string) $request->query('search', ''));

        $departments = EmployeeDepartment::query()
            ->where('institution_id', $institutionId)
            ->when($search !== '', fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->paginate($perPage);

        return response()->json([
            'message' => 'Departments fetched successfully',
            'data'    => $departments->items(),
            'meta'    => [
                'current_page' => $departments->currentPage(),
                'last_page'    => $departments->lastPage(),
                'per_page'     => $departments->perPage(),
                'total'        => $departments->total(),
            ],
        ]);
    }

    /**
     * GET /api/admin/employee/departments/{id}
     */
    public function show(int $id): JsonResponse
    {
        $institutionId = auth()->user()->institution_id;

        $department = EmployeeDepartment::where('institution_id', $institutionId)->find($id);

        if (! $department) {
            return response()->json(['message' => 'Department not found'], 404);
        }

        return response()->json([
            'message' => 'Department fetched successfully',
            'data'    => $department,
        ]);
    }

    /**
     * POST /api/admin/employee/departments
     */
    public function store(Request $request): JsonResponse
    {
        $institutionId = auth()->user()->institution_id;

        $validator = $this->validateDepartment($request, $institutionId);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }

        $department = EmployeeDepartment::create([
            'institution_id' => $institutionId,
            'name'           => $validator->validated()['name'],
        ]);

        return response()->json([
            'message' => 'Department created successfully',
            'data'    => $department,
        ], 201);
    }

    /**
     * PUT/PATCH /api/admin/employee/departments/{id}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $institutionId = auth()->user()->institution_id;

        $department = EmployeeDepartment::where('institution_id', $institutionId)->find($id);

        if (! $department) {
            return response()->json(['message' => 'Department not found'], 404);
        }

        $validator = $this->validateDepartment($request, $institutionId, $department->id);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }

        $department->update(['name' => $validator->validated()['name']]);

        return response()->json([
            'message' => 'Department updated successfully',
            'data'    => $department->fresh(),
        ]);
    }

    /**
     * DELETE /api/admin/employee/departments/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        $institutionId = auth()->user()->institution_id;

        $department = EmployeeDepartment::where('institution_id', $institutionId)->find($id);

        if (! $department) {
            return response()->json(['message' => 'Department not found'], 404);
        }

        $department->delete();

        return response()->json(['message' => 'Department deleted successfully']);
    }

    // ── Helpers ──────────────────────────────────────────────────────────

    private function validateDepartment(Request $request, int $institutionId, ?int $ignoreId = null)
    {
        return Validator::make($request->all(), [
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('employee_departments', 'name')
                    ->where('institution_id', $institutionId)
                    ->ignore($ignoreId),
            ],
        ], [
            'name.required' => 'Department name is required.',
            'name.unique'   => 'This department name already exists.',
        ]);
    }
}