<?php

namespace App\Http\Controllers\Api\Admin\Academic;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class SessionController extends Controller
{
    /**
     * GET /api/admin/academic/sessions
     * Institution-scoped list + search + pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $institutionId = $request->user()->institution_id;

        $query = AcademicSession::query()
            ->where('institution_id', $institutionId);

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->string('search') . '%');
        }

        $query->orderBy('id', 'desc');

        // Plain array response (no pagination wrapper) — Flutter service
        // expects `data` to be a JSON List directly, same as Department/
        // Designation modules.
        $sessions = $query->get();

        return response()->json([
            'message' => 'Academic sessions fetched successfully',
            'data' => $sessions,
        ]);
    }

    /**
     * GET /api/admin/academic/sessions/{id}
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $institutionId = $request->user()->institution_id;

        $session = AcademicSession::where('institution_id', $institutionId)
            ->find($id);

        if (!$session) {
            return response()->json([
                'message' => 'Academic session not found',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'message' => 'Academic session fetched successfully',
            'data' => $session,
        ]);
    }

    /**
     * POST /api/admin/academic/sessions
     */
    public function store(Request $request): JsonResponse
    {
        $institutionId = $request->user()->institution_id;

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('academic_sessions', 'name')
                    ->where(fn ($q) => $q->where('institution_id', $institutionId)),
            ],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'is_current' => ['boolean'],
        ]);

        $session = AcademicSession::create([
            'institution_id' => $institutionId,
            'name' => $validated['name'],
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'is_current' => $validated['is_current'] ?? false,
        ]);

        if ($session->is_current) {
            $this->demoteOtherCurrentSessions($institutionId, $session->id);
        }

        return response()->json([
            'message' => 'Academic session created successfully',
            'data' => $session,
        ], 201);
    }

    /**
     * PUT/PATCH /api/admin/academic/sessions/{id}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $institutionId = $request->user()->institution_id;

        $session = AcademicSession::where('institution_id', $institutionId)
            ->find($id);

        if (!$session) {
            return response()->json([
                'message' => 'Academic session not found',
                'data' => null,
            ], 404);
        }

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('academic_sessions', 'name')
                    ->where(fn ($q) => $q->where('institution_id', $institutionId))
                    ->ignore($session->id),
            ],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'is_current' => ['boolean'],
        ]);

        $session->update([
            'name' => $validated['name'],
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'is_current' => $validated['is_current'] ?? false,
        ]);

        if ($session->is_current) {
            $this->demoteOtherCurrentSessions($institutionId, $session->id);
        }

        return response()->json([
            'message' => 'Academic session updated successfully',
            'data' => $session->fresh(),
        ]);
    }

    /**
     * DELETE /api/admin/academic/sessions/{id}
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $institutionId = $request->user()->institution_id;

        $session = AcademicSession::where('institution_id', $institutionId)
            ->find($id);

        if (!$session) {
            return response()->json([
                'message' => 'Academic session not found',
                'data' => null,
            ], 404);
        }

        if ($session->is_current) {
            return response()->json([
                'message' => 'Current session delete kora jabe na. Onno ekta session current korar por delete koro.',
                'data' => null,
            ], 422);
        }

        $session->delete();

        return response()->json([
            'message' => 'Academic session deleted successfully',
            'data' => null,
        ]);
    }

    /**
     * Ensures only one session per institution can be marked is_current.
     */
    private function demoteOtherCurrentSessions(int $institutionId, int $exceptId): void
    {
        AcademicSession::where('institution_id', $institutionId)
            ->where('id', '!=', $exceptId)
            ->where('is_current', true)
            ->update(['is_current' => false]);
    }
}