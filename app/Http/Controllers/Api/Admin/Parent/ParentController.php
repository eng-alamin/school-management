<?php

namespace App\Http\Controllers\Api\Admin\Parent;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Guardian;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Database\QueryException;

class ParentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $institutionId = $request->user()->institution_id;
        $search = trim((string) $request->query('search', ''));
        $perPage = (int) $request->query('per_page', 20);
        if ($perPage <= 0 || $perPage > 100) {
            $perPage = 20;
        }

        $query = Guardian::with('students')
            ->whereHas('user', fn ($q) => $q->where('institution_id', $institutionId));

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%")
                    ->orWhere('occupation', 'like', "%{$search}%");
            });
        }

        $parents = $query->latest()->paginate($perPage);

        return response()->json([
            'message' => 'Parents fetched successfully.',
            'data'    => $parents->items(),
            'meta'    => [
                'current_page' => $parents->currentPage(),
                'last_page'    => $parents->lastPage(),
                'total'        => $parents->total(),
                'per_page'     => $parents->perPage(),
            ],
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $institutionId = $request->user()->institution_id;

        $guardian = Guardian::with(['user', 'students'])
            ->whereHas('user', fn ($q) => $q->where('institution_id', $institutionId))
            ->findOrFail($id);

        return response()->json([
            'message' => 'Parent fetched successfully.',
            'data'    => $guardian,
        ]);
    }

    private function rules(?int $userId = null): array
    {
        return [
            'name'         => 'required|string|max:255',
            'relation'     => 'nullable|string|max:50',
            'father_name'  => 'nullable|string|max:255',
            'mother_name'  => 'nullable|string|max:255',
            'occupation'   => 'nullable|string|max:255',
            'income'       => 'nullable|numeric',
            'education'    => 'nullable|string|max:255',
            'mobile'       => 'required|string|max:20',
            'email'        => 'nullable|email',
            'address'      => 'nullable|string',
            'photo'        => 'nullable|image|max:2048',
            'username'     => ['required', 'string', Rule::unique('users', 'username')->ignore($userId)],
            'password'     => 'nullable|string|min:4',
        ];
    }

    public function store(Request $request): JsonResponse
    {
        $institutionId = $request->user()->institution_id;
        $validated = $request->validate($this->rules());

        DB::beginTransaction();
        try {
            $user = User::create([
                'institution_id' => $institutionId,
                'role'           => 'parent',
                'name'           => $validated['name'],
                'username'       => $validated['username'],
                'email'          => $validated['email'] ?? null,
                'password'       => $validated['password'] ?? '1234',
            ]);

            $photoPath = null;
            if ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')->store('guardians', 'public');
            }

            $guardian = Guardian::create([
                'user_id'     => $user->id,
                'name'        => $validated['name'],
                'relation'    => $validated['relation'] ?? null,
                'father_name' => $validated['father_name'] ?? null,
                'mother_name' => $validated['mother_name'] ?? null,
                'occupation'  => $validated['occupation'] ?? null,
                'income'      => $validated['income'] ?? null,
                'education'   => $validated['education'] ?? null,
                'mobile'      => $validated['mobile'],
                'email'       => $validated['email'] ?? null,
                'address'     => $validated['address'] ?? null,
                'photo'       => $photoPath,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Parent created successfully.',
                'data'    => $guardian,
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $institutionId = $request->user()->institution_id;

        $guardian = Guardian::with('user')
            ->whereHas('user', fn ($q) => $q->where('institution_id', $institutionId))
            ->findOrFail($id);

        $validated = $request->validate($this->rules($guardian->user_id));

        DB::beginTransaction();
        try {
            $userData = [
                'name'     => $validated['name'],
                'username' => $validated['username'],
                'email'    => $validated['email'] ?? null,
            ];
            if (!empty($validated['password'])) {
                $userData['password'] = $validated['password'];
            }
            $guardian->user->update($userData);

            $guardianData = [
                'name'        => $validated['name'],
                'relation'    => $validated['relation'] ?? null,
                'father_name' => $validated['father_name'] ?? null,
                'mother_name' => $validated['mother_name'] ?? null,
                'occupation'  => $validated['occupation'] ?? null,
                'income'      => $validated['income'] ?? null,
                'education'   => $validated['education'] ?? null,
                'mobile'      => $validated['mobile'],
                'email'       => $validated['email'] ?? null,
                'address'     => $validated['address'] ?? null,
            ];

            if ($request->hasFile('photo')) {
                $oldPhoto = $guardian->photo;
                $guardianData['photo'] = $request->file('photo')->store('guardians', 'public');
                if ($oldPhoto) {
                    Storage::disk('public')->delete($oldPhoto);
                }
            }

            $guardian->update($guardianData);
            DB::commit();

            return response()->json([
                'message' => 'Parent updated successfully.',
                'data'    => $guardian->fresh(),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $institutionId = $request->user()->institution_id;

        $guardian = Guardian::whereHas('user', fn ($q) => $q->where('institution_id', $institutionId))
            ->findOrFail($id);

        DB::beginTransaction();
        try {
            $userId = $guardian->user_id;
            $photoPath = $guardian->photo;

            $guardian->delete();
            User::where('id', $userId)->delete();

            if ($photoPath) {
                Storage::disk('public')->delete($photoPath);
            }

            DB::commit();

            return response()->json(['message' => 'Parent deleted successfully.']);
        } catch (QueryException $e) {
            DB::rollBack();

            // MySQL FK violation code = 23000. Ei guardian kono admission/student-er
            // shathe linked thakle delete kora jabe na - user-ke bujhiye dite hobe.
            if ($e->getCode() === '23000') {
                return response()->json([
                    'message' => 'Ei Parent ke delete kora jacche na, karon tar shathe kono Student/Admission link kora ache. Age shei link soriye tarpor delete koro.',
                ], 422);
            }

            throw $e;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}