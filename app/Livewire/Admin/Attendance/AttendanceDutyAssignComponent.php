<?php

namespace App\Livewire\Admin\Attendance;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use App\Models\AttendanceClassAssign;
use App\Models\AcademicClassAssign;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AttendanceDutyAssignComponent extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    // ── Sort/Search allowlist (security: raw column injection thekano jonno) ──
    private const SORTABLE_FIELDS = ['id', 'class_name', 'section_name', 'teacher_name'];

    // List
    public string $search = '';
    public int $perPage = 10;
    public string $sortField = 'id';
    public string $sortDirection = 'asc';

    // Modal
    public bool $showModal = false;
    public bool $confirmDelete = false;
    public ?int $deleteId = null;

    // Form
    public ?int $editId = null;
    public $academic_class_assign_id = '';
    public $teacher_id = '';

    protected function rules(): array
    {
        $institutionId = institution()->id;

        return [
            'academic_class_assign_id' => [
                'required',
                Rule::exists('academic_class_assigns', 'id')
                    ->where(fn ($q) => $q->where('institution_id', $institutionId)),
                // Ekta class+section-e already teacher assigned thakle duplicate kora jabe na
                Rule::unique('attendance_class_assigns', 'academic_class_assign_id')
                    ->ignore($this->editId),
            ],
            'teacher_id' => [
                'required',
                Rule::exists('users', 'id')
                    ->where(fn ($q) => $q->where('institution_id', $institutionId)
                        ->where('role', User::ROLE_TEACHER)),
            ],
        ];
    }

    protected function messages(): array
    {
        return [
            'academic_class_assign_id.required' => 'Please select a class/section.',
            'academic_class_assign_id.exists'   => 'Selected class/section is invalid.',
            'academic_class_assign_id.unique'   => 'This class/section already has an attendance duty teacher assigned.',
            'teacher_id.required' => 'Please select a teacher.',
            'teacher_id.exists'   => 'Selected teacher is invalid.',
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        // ── Allowlist check: sortable field na hole silently ignore ──
        if (! in_array($field, self::SORTABLE_FIELDS, true)) {
            return;
        }

        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->editId = null;
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $institutionId = institution()->id;

        // Defense-in-depth: institution_id explicitly check kora holo (IDOR protection)
        $record = AttendanceClassAssign::where('institution_id', $institutionId)
            ->findOrFail($id);

        $this->editId = $id;
        $this->academic_class_assign_id = $record->academic_class_assign_id;
        $this->teacher_id = $record->teacher_id;

        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $institutionId = institution()->id;

        DB::transaction(function () use ($institutionId) {
            $data = [
                'institution_id'           => $institutionId,
                'academic_class_assign_id' => $this->academic_class_assign_id,
                'teacher_id'               => $this->teacher_id,
            ];

            if ($this->editId) {
                $assign = AttendanceClassAssign::where('institution_id', $institutionId)
                    ->findOrFail($this->editId);
                $assign->update($data);
            } else {
                $assign = AttendanceClassAssign::create($data);
            }

            if (function_exists('activity')) {
                activity()
                    ->causedBy(auth()->user())
                    ->performedOn($assign)
                    ->withProperties([
                        'academic_class_assign_id' => $assign->academic_class_assign_id,
                        'teacher_id'               => $assign->teacher_id,
                    ])
                    ->log($this->editId ? 'Updated attendance duty assignment' : 'Created attendance duty assignment');
            }
        });

        $this->dispatch(
            'toast',
            type: 'success',
            message: $this->editId ? 'Attendance duty updated successfully!' : 'Attendance duty assigned successfully!'
        );

        $this->showModal = false;
        $this->resetForm();
    }

    public function confirmDeleteRecord(int $id): void
    {
        $this->deleteId = $id;
        $this->confirmDelete = true;
    }

    public function deleteRecord(): void
    {
        $institutionId = institution()->id;

        // Defense-in-depth: institution_id explicitly check kora holo (IDOR protection)
        $assign = AttendanceClassAssign::where('institution_id', $institutionId)
            ->findOrFail($this->deleteId);

        DB::transaction(function () use ($assign) {
            $classAssign = $assign->classAssign;
            $className   = $classAssign?->academicClass?->name;
            $sectionName = $classAssign?->academicSection?->name;

            $assign->delete();

            if (function_exists('activity')) {
                activity()
                    ->causedBy(auth()->user())
                    ->withProperties(['class_name' => $className, 'section_name' => $sectionName])
                    ->log('Deleted attendance duty assignment');
            }
        });

        $this->confirmDelete = false;
        $this->deleteId = null;
        $this->dispatch('toast', type: 'success', message: 'Attendance duty removed successfully!');
    }

    private function resetForm(): void
    {
        $this->reset(['academic_class_assign_id', 'teacher_id', 'editId']);
        $this->resetValidation();
    }

    // Dropdown-e শুধু oi class-section গুলো দেখাবে যেগুলোর এখনো attendance duty assign করা হয়নি
    // (edit korar somoy nijer currently-assigned row-take bad dile o dekhabe, tai editId diye exclude kora hocche)
    #[Computed]
    public function availableClassAssigns()
    {
        $institutionId = institution()->id;

        $assignedIds = AttendanceClassAssign::where('institution_id', $institutionId)
            ->when($this->editId, fn ($q) => $q->where('id', '!=', $this->editId))
            ->pluck('academic_class_assign_id');

        return AcademicClassAssign::with(['academicClass', 'academicSection'])
            ->where('institution_id', $institutionId)
            ->whereNotIn('id', $assignedIds)
            ->get();
    }

    public function render()
    {
        $institutionId = institution()->id;

        // ── Sort field -> actual qualified column mapping ──
        $sortColumnMap = [
            'id'           => 'attendance_class_assigns.id',
            'class_name'   => 'ac.name',
            'section_name' => 'asec.name',
            'teacher_name' => 'u.name',
        ];
        $sortColumn = $sortColumnMap[$this->sortField] ?? 'attendance_class_assigns.id';

        $assigns = AttendanceClassAssign::query()
            ->select('attendance_class_assigns.*')
            ->join('academic_class_assigns as aca', 'aca.id', '=', 'attendance_class_assigns.academic_class_assign_id')
            ->leftJoin('academic_classes as ac', 'ac.id', '=', 'aca.class_id')
            ->leftJoin('academic_sections as asec', 'asec.id', '=', 'aca.section_id')
            ->leftJoin('users as u', 'u.id', '=', 'attendance_class_assigns.teacher_id')
            ->with(['classAssign.academicClass', 'classAssign.academicSection', 'teacher'])
            ->where('attendance_class_assigns.institution_id', $institutionId)
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                // ── OR condition always wrapped in closure (institution scope leak thekano) ──
                $q->where('ac.name', 'like', "%{$this->search}%")
                  ->orWhere('asec.name', 'like', "%{$this->search}%")
                  ->orWhere('u.name', 'like', "%{$this->search}%");
            }))
            ->orderBy($sortColumn, $this->sortDirection)
            ->paginate($this->perPage);

        $teachers = User::where('role', User::ROLE_TEACHER)
            ->where('institution_id', $institutionId)
            ->orderBy('name')
            ->pluck('name', 'id');

        return view('livewire.admin.attendance.attendance-duty-assign-component')
            ->with('assigns', $assigns)
            ->with('teachers', $teachers)
            ->layout('layouts.admin.app', [
                'title' => 'Attendance Duty Assign | ' . institution()->name,
            ]);
    }
}