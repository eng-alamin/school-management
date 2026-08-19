<?php

namespace App\Livewire\Branch\Attendance;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ExamSchedule;
use App\Models\ExamSetup;
use App\Models\AttendanceExamAssign;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ExamDutyAssignComponent extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    // ── Sort allowlist (security: raw column injection thekano jonno) ──
    private const SORTABLE_FIELDS = ['id', 'exam_date', 'subject_name', 'exam_name'];

    // List
    public string $search = '';
    public int $perPage = 10;
    public string $sortField = 'exam_date';
    public string $sortDirection = 'asc';

    // Filter: kon Exam Setup-er schedule dekhabe (blank = shob)
    public ?int $examSetupFilter = null;

    // Modal
    public bool $showModal = false;

    public ?int $examScheduleId = null;
    public array $teacher_array = [];

    protected function rules(): array
    {
        $institutionId = institution()->id;

        return [
            'teacher_array'   => 'required|array|min:1',
            'teacher_array.*' => [
                'integer',
                Rule::exists('users', 'id')
                    ->where(fn ($q) => $q->where('institution_id', $institutionId)
                        ->where('role', User::ROLE_TEACHER)),
            ],
        ];
    }

    protected function messages(): array
    {
        return [
            'teacher_array.required' => 'Please select at least one teacher for duty.',
            'teacher_array.min'      => 'Please select at least one teacher for duty.',
            'teacher_array.*.exists' => 'Selected teacher is invalid.',
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingExamSetupFilter(): void
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

    public function openManageDuty(int $examScheduleId): void
    {
        $institutionId = institution()->id;

        // Defense-in-depth: institution_id explicitly check kora holo (IDOR protection)
        $schedule = ExamSchedule::where('institution_id', $institutionId)
            ->findOrFail($examScheduleId);

        $this->examScheduleId = $schedule->id;

        $this->teacher_array = AttendanceExamAssign::where('institution_id', $institutionId)
            ->where('exam_schedule_id', $schedule->id)
            ->pluck('teacher_id')
            ->toArray();

        $this->resetValidation();
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $institutionId  = institution()->id;
        $examScheduleId = $this->examScheduleId;

        DB::transaction(function () use ($institutionId, $examScheduleId) {
            // Purono duty list delete kore notun list diye replace kora hocche (sync pattern)
            AttendanceExamAssign::where('institution_id', $institutionId)
                ->where('exam_schedule_id', $examScheduleId)
                ->delete();

            foreach ($this->teacher_array as $teacherId) {
                AttendanceExamAssign::create([
                    'institution_id'   => $institutionId,
                    'exam_schedule_id' => $examScheduleId,
                    'teacher_id'       => $teacherId,
                ]);
            }

            if (function_exists('activity')) {
                activity()
                    ->causedBy(auth()->user())
                    ->withProperties([
                        'exam_schedule_id' => $examScheduleId,
                        'teacher_ids'      => $this->teacher_array,
                    ])
                    ->log('Updated exam duty assignment');
            }
        });

        $this->dispatch('toast', type: 'success', message: 'Exam duty assigned successfully!');

        $this->showModal = false;
        $this->reset(['examScheduleId', 'teacher_array']);
    }

    public function render()
    {
        $institutionId = institution()->id;

        // ── Sort field -> actual qualified column mapping ──
        $sortColumnMap = [
            'id'           => 'exam_schedules.id',
            'exam_date'    => 'exam_schedules.exam_date',
            'subject_name' => 'sub.name',
            'exam_name'    => 'es.name',
        ];
        $sortColumn = $sortColumnMap[$this->sortField] ?? 'exam_schedules.exam_date';

        $schedules = ExamSchedule::query()
            ->select('exam_schedules.*')
            ->join('exam_setups as es', 'es.id', '=', 'exam_schedules.exam_setup_id')
            ->join('exam_setup_details as esd', 'esd.id', '=', 'exam_schedules.exam_setup_detail_id')
            ->join('academic_class_assign_details as acad', 'acad.id', '=', 'esd.academic_class_assign_detail_id')
            ->leftJoin('academic_subjects as sub', 'sub.id', '=', 'acad.subject_id')
            ->with([
                'examSetup.classAssign.academicClass',
                'examSetup.classAssign.academicSection',
                'examSetup.term',
                'examSetup.type',
                'examSetupDetail.classAssignDetail.subject',
            ])
            ->where('exam_schedules.institution_id', $institutionId)
            ->when($this->search, fn ($q) => $q->where('sub.name', 'like', "%{$this->search}%"))
            ->when($this->examSetupFilter, fn ($q) => $q->where('exam_schedules.exam_setup_id', $this->examSetupFilter))
            ->orderBy($sortColumn, $this->sortDirection)
            ->paginate($this->perPage);

        // ── N+1 thekano jonno ekbare shob schedule-er assigned teachers load kora hocche ──
        $scheduleIds = $schedules->pluck('id');

        $dutyMap = AttendanceExamAssign::with('teacher')
            ->where('institution_id', $institutionId)
            ->whereIn('exam_schedule_id', $scheduleIds)
            ->get()
            ->groupBy('exam_schedule_id');

        $teachers = User::where('role', User::ROLE_TEACHER)
            ->where('institution_id', $institutionId)
            ->orderBy('name')
            ->pluck('name', 'id');

        // ── Filter dropdown-er jonno shob Exam Setup list (class + term shoho) ──
        $examSetups = ExamSetup::with(['classAssign.academicClass', 'classAssign.academicSection', 'term'])
            ->where('institution_id', $institutionId)
            ->orderBy('name')
            ->get();

        return view('livewire.admin.attendance.exam-duty-assign-component')
            ->with('schedules', $schedules)
            ->with('dutyMap', $dutyMap)
            ->with('teachers', $teachers)
            ->with('examSetups', $examSetups)
            ->layout('layouts.branch.app', [
                'title' => 'Exam Duty Assign | ' . institution()->name,
            ]);
    }
}