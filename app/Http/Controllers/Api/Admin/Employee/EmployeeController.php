<?php

namespace App\Http\Controllers\Api\Admin\Employee;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeDepartment;
use App\Models\EmployeeDesignation;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    /**
     * GET /api/admin/employee/form-data
     * Dropdown data (departments + designations) for Add/Edit form.
     */
    public function formData(): JsonResponse
    {
        $institutionId = auth()->user()->institution_id;

        return response()->json([
            'message' => 'Form data fetched successfully',
            'data'    => [
                'departments'  => EmployeeDepartment::where('institution_id', $institutionId)
                    ->orderBy('name')
                    ->get(['id', 'name']),
                'designations' => EmployeeDesignation::where('institution_id', $institutionId)
                    ->orderBy('name')
                    ->get(['id', 'name']),
            ],
        ]);
    }

    /**
     * GET /api/admin/employee
     */
    public function index(Request $request): JsonResponse
    {
        $institutionId = auth()->user()->institution_id;

        $perPage = (int) $request->query('per_page', 10);
        $search  = trim((string) $request->query('search', ''));

        $employees = Employee::query()
            ->with(['user', 'designation', 'department'])
            ->where('institution_id', $institutionId)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('employee_id', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('mobile', 'like', "%{$search}%")
                        ->orWhereHas('designation', fn ($q2) => $q2->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('department', fn ($q2) => $q2->where('name', 'like', "%{$search}%"));
                });
            })
            ->orderByDesc('id')
            ->paginate($perPage);

        return response()->json([
            'message' => 'Employees fetched successfully',
            'data'    => $employees->items(),
            'meta'    => [
                'current_page' => $employees->currentPage(),
                'last_page'    => $employees->lastPage(),
                'per_page'     => $employees->perPage(),
                'total'        => $employees->total(),
            ],
        ]);
    }

    /**
     * GET /api/admin/employee/{id}
     */
    public function show(int $id): JsonResponse
    {
        $institutionId = auth()->user()->institution_id;

        $employee = Employee::with(['user', 'designation', 'department'])
            ->where('institution_id', $institutionId)
            ->find($id);

        if (! $employee) {
            return response()->json(['message' => 'Employee not found'], 404);
        }

        return response()->json([
            'message' => 'Employee fetched successfully',
            'data'    => $employee,
        ]);
    }

    /**
     * POST /api/admin/employee
     * multipart/form-data (photo_upload optional file)
     */
    public function store(Request $request): JsonResponse
    {
        $institutionId = auth()->user()->institution_id;

        $validator = $this->validateEmployee($request, $institutionId);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();

        DB::beginTransaction();

        try {
            $user = User::create([
                'institution_id' => $institutionId,
                'role'           => $validated['role'],
                'name'           => $validated['name'],
                'username'       => $validated['username'],
                'email'          => $validated['email'] ?? null,
                'password'       => ! empty($validated['password']) ? $validated['password'] : '12345678',
            ]);

            $photoPath = null;
            if ($request->hasFile('photo_upload')) {
                $photoPath = $request->file('photo_upload')->store('employees', 'public');
            }

            $institutionCode = 'SCH' . str_pad((string) $institutionId, 2, '0', STR_PAD_LEFT);
            $year            = now()->format('y');

            $lastEmployee = Employee::where('institution_id', $institutionId)
                ->lockForUpdate()
                ->orderByDesc('id')
                ->first();

            $serial = $lastEmployee
                ? ((int) substr($lastEmployee->employee_id, -4)) + 1
                : 1;

            $employeeId = $institutionCode . $year . str_pad((string) $serial, 4, '0', STR_PAD_LEFT);

            $employee = Employee::create([
                'institution_id'    => $institutionId,
                'user_id'           => $user->id,
                'employee_id'       => $employeeId,
                'joining_date'      => $validated['joining_date'],
                'designation_id'    => $validated['designation_id'],
                'department_id'     => $validated['department_id'],
                'qualification'     => $validated['qualification'] ?? null,
                'experience_detail' => $validated['experience_detail'] ?? null,
                'total_experience'  => $validated['total_experience'] ?? null,
                'name'              => $validated['name'],
                'dob'               => $validated['dob'] ?? null,
                'religion'          => $validated['religion'] ?? null,
                'mobile'            => $validated['mobile'] ?? null,
                'email'             => $validated['email'] ?? null,
                'present_address'   => $validated['present_address'] ?? null,
                'permanent_address' => $validated['permanent_address'] ?? null,
                'photo'             => $photoPath,
                'bank_name'         => $validated['bank_name'] ?? null,
                'holder_name'       => $validated['holder_name'] ?? null,
                'bank_branch'       => $validated['bank_branch'] ?? null,
                'bank_address'      => $validated['bank_address'] ?? null,
                'ifsc_code'         => $validated['ifsc_code'] ?? null,
                'account_no'        => $validated['account_no'] ?? null,
                'status'            => 'active',
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Employee created successfully',
                'data'    => $employee->load(['user', 'designation', 'department']),
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Something went wrong while creating employee',
            ], 500);
        }
    }

    /**
     * POST /api/admin/employee/{id} (with _method=PUT, multipart/form-data)
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $institutionId = auth()->user()->institution_id;

        $employee = Employee::where('institution_id', $institutionId)->find($id);

        if (! $employee) {
            return response()->json(['message' => 'Employee not found'], 404);
        }

        $validator = $this->validateEmployee($request, $institutionId, $employee->user_id);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();

        DB::beginTransaction();

        try {
            $userData = [
                'role'     => $validated['role'],
                'name'     => $validated['name'],
                'username' => $validated['username'],
                'email'    => $validated['email'] ?? null,
            ];

            if (! empty($validated['password'])) {
                $userData['password'] = $validated['password'];
            }

            User::where('id', $employee->user_id)->update($userData);

            $employeeData = [
                'joining_date'      => $validated['joining_date'],
                'designation_id'    => $validated['designation_id'],
                'department_id'     => $validated['department_id'],
                'qualification'     => $validated['qualification'] ?? null,
                'experience_detail' => $validated['experience_detail'] ?? null,
                'total_experience'  => $validated['total_experience'] ?? null,
                'name'              => $validated['name'],
                'dob'               => $validated['dob'] ?? null,
                'religion'          => $validated['religion'] ?? null,
                'mobile'            => $validated['mobile'] ?? null,
                'email'             => $validated['email'] ?? null,
                'present_address'   => $validated['present_address'] ?? null,
                'permanent_address' => $validated['permanent_address'] ?? null,
                'bank_name'         => $validated['bank_name'] ?? null,
                'holder_name'       => $validated['holder_name'] ?? null,
                'bank_branch'       => $validated['bank_branch'] ?? null,
                'bank_address'      => $validated['bank_address'] ?? null,
                'ifsc_code'         => $validated['ifsc_code'] ?? null,
                'account_no'        => $validated['account_no'] ?? null,
            ];

            if ($request->hasFile('photo_upload')) {
                $oldPhoto = $employee->photo;

                $employeeData['photo'] = $request->file('photo_upload')->store('employees', 'public');

                if ($oldPhoto) {
                    Storage::disk('public')->delete($oldPhoto);
                }
            }

            $employee->update($employeeData);

            DB::commit();

            return response()->json([
                'message' => 'Employee updated successfully',
                'data'    => $employee->fresh()->load(['user', 'designation', 'department']),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Something went wrong while updating employee',
            ], 500);
        }
    }

    /**
     * DELETE /api/admin/employee/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        $institutionId = auth()->user()->institution_id;

        $employee = Employee::where('institution_id', $institutionId)->find($id);

        if (! $employee) {
            return response()->json(['message' => 'Employee not found'], 404);
        }

        if ($employee->photo) {
            Storage::disk('public')->delete($employee->photo);
        }

        activity()
            ->performedOn($employee)
            ->withProperties([
                'institution_id' => $employee->institution_id,
                'icon'           => 'delete',
                'type'           => 'employee',
            ])
            ->log('Employee deleted: ' . $employee->name);

        $employee->delete();

        return response()->json(['message' => 'Employee deleted successfully']);
    }

    // ── Helpers ──────────────────────────────────────────────────────────

    private function validateEmployee(Request $request, int $institutionId, ?int $ignoreUserId = null)
    {
        return Validator::make($request->all(), [
            'role'              => 'required|string|in:admin,teacher,accountant,staff',
            'joining_date'      => 'required|date',
            'designation_id'    => [
                'required',
                Rule::exists('employee_designations', 'id')->where('institution_id', $institutionId),
            ],
            'department_id'     => [
                'required',
                Rule::exists('employee_departments', 'id')->where('institution_id', $institutionId),
            ],
            'qualification'     => 'nullable|string|max:255',
            'experience_detail' => 'nullable|string',
            'total_experience'  => 'nullable|string|max:100',

            'name'              => 'required|string|max:255',
            'dob'               => 'nullable|date',
            'religion'          => 'nullable|string|in:muslim,hindu,christian,buddhist',
            'mobile'            => 'nullable|string|max:20',
            'email'             => ['nullable', 'email', Rule::unique('users', 'email')->ignore($ignoreUserId)],
            'present_address'   => 'nullable|string',
            'permanent_address' => 'nullable|string',
            'photo_upload'      => 'nullable|image|max:2048',

            'username'          => ['required', 'string', Rule::unique('users', 'username')->ignore($ignoreUserId)],
            'password'          => 'nullable|string|min:8',

            'bank_name'         => 'nullable|string|max:255',
            'holder_name'       => 'nullable|string|max:255',
            'bank_branch'       => 'nullable|string|max:255',
            'bank_address'      => 'nullable|string',
            'ifsc_code'         => 'nullable|string|max:50',
            'account_no'        => 'nullable|string|max:50',
        ], [
            'designation_id.required' => 'Designation select korte hobe.',
            'designation_id.exists'   => 'Selected designation valid na.',
            'department_id.required'  => 'Department select korte hobe.',
            'department_id.exists'    => 'Selected department valid na.',
            'email.unique'            => 'Ei email diye already ekjon user ache.',
            'username.unique'         => 'Ei username already use hocche.',
        ]);
    }
}