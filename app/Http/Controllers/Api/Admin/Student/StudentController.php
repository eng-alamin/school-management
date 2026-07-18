<?php

namespace App\Http\Controllers\Api\Admin\Student;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Database\QueryException;

class StudentController extends Controller
{
    private array $statusOptions = ['active', 'inactive', 'graduated', 'transferred', 'dropped_out'];

    public function index(Request $request): JsonResponse
    {
        $institutionId = $request->user()->institution_id;
        $search = trim((string) $request->query('search', ''));
        $classId = $request->query('class_id');
        $sectionId = $request->query('section_id');
        $perPage = (int) $request->query('per_page', 20);
        if ($perPage <= 0 || $perPage > 100) {
            $perPage = 20;
        }

        $query = Student::with(['guardians', 'class', 'section', 'user'])
            ->where('institution_id', $institutionId);

        if ($classId) {
            $query->where('class_id', $classId);
        }

        if ($sectionId) {
            $query->where('section_id', $sectionId);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('student_id', 'like', "%{$search}%")
                    ->orWhere('registration_no', 'like', "%{$search}%")
                    ->orWhere('roll_no', 'like', "%{$search}%");
            });
        }

        $students = $query->latest()->paginate($perPage);

        return response()->json([
            'message' => 'Students fetched successfully.',
            'data'    => $students->items(),
            'meta'    => [
                'current_page' => $students->currentPage(),
                'last_page'    => $students->lastPage(),
                'total'        => $students->total(),
                'per_page'     => $students->perPage(),
            ],
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $institutionId = $request->user()->institution_id;

        $student = Student::with(['guardians', 'class', 'section', 'user'])
            ->where('institution_id', $institutionId)
            ->findOrFail($id);

        return response()->json([
            'message' => 'Student fetched successfully.',
            'data'    => $student,
        ]);
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $institutionId = $request->user()->institution_id;

        $student = Student::where('institution_id', $institutionId)->findOrFail($id);

        $validated = $request->validate([
            'status' => ['required', 'string', Rule::in($this->statusOptions)],
        ]);

        $oldStatus = $student->status;
        $student->update(['status' => $validated['status']]);

        if (function_exists('activity')) {
            activity()
                ->performedOn($student)
                ->withProperties([
                    'institution_id' => $institutionId,
                    'icon'           => 'toggle_on',
                    'type'           => 'student',
                    'old_status'     => $oldStatus,
                    'new_status'     => $validated['status'],
                ])
                ->log('Student status changed: ' . $student->name . ' (' . $oldStatus . ' → ' . $validated['status'] . ')');
        }

        return response()->json([
            'message' => 'Status updated successfully.',
            'data'    => $student->fresh(),
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $institutionId = $request->user()->institution_id;

        $student = Student::where('institution_id', $institutionId)->findOrFail($id);

        DB::beginTransaction();
        try {
            $userId = $student->user_id;
            $photoPath = $student->photo;

            // Guardian-Student pivot row age soriye dicchi, na hole
            // guardian_student FK constraint-e delete fail korte pare.
            if (method_exists($student, 'guardians')) {
                $student->guardians()->detach();
            }

            $student->delete();

            if ($userId) {
                User::where('id', $userId)->delete();
            }

            if ($photoPath) {
                Storage::disk('public')->delete($photoPath);
            }

            DB::commit();

            return response()->json(['message' => 'Student deleted successfully.']);
        } catch (QueryException $e) {
            DB::rollBack();

            // MySQL FK violation code = 23000. Student-er shathe kono
            // admission/attendance/result linked thakle delete kora jabe na.
            if ($e->getCode() === '23000') {
                return response()->json([
                    'message' => 'Ei Student ke delete kora jacche na, karon tar shathe kono related record (admission/attendance/result) linked ache. Age shei link soriye tarpor delete koro.',
                ], 422);
            }

            throw $e;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}