<?php

namespace App\Livewire\Admin\Student;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Student;
use App\Models\AcademicClass;
use App\Models\AcademicClassAssign;
use Illuminate\Support\Facades\DB;

class StudentListComponent extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    public $search    = '';
    public $perPage   = 10;
    public $sortField = 'created_at';
    public $sortDir   = 'desc';

    public $filterClass   = '';
    public $filterSection = '';

    public array $availableSections = [];

    // Delete
    public bool $confirmDelete = false;
    public ?int $deleteId      = null;

    // Status Update
    public bool $showStatusModal = false;
    public ?int $statusId        = null;
    public string $newStatus     = '';

    protected array $statusOptions = ['active', 'inactive', 'graduated', 'transferred', 'dropped_out'];

    // ✅ Sortable field allowlist — direct $sortField orderBy() e pass kora
    // theke bipodjonok, sob somoy ei list er against validate korte hobe.
    private const SORTABLE_FIELDS = [
        'name',
        'student_id',
        'registration_no',
        'roll_no',
        'admission_date',
        'created_at',
        'status',
    ];

    // ✅ perPage er jonno hard cap — DoS/heavy-query protection
    private const MAX_PER_PAGE = 100;

    public string $routePrefix = '';

    public function mount(): void
    {
        $this->routePrefix = $this->resolveRoutePrefix();
    }

    protected function resolveRoutePrefix(): string
    {
        $routeName = request()->route()?->getName();

        if ($routeName && str_contains($routeName, '.')) {
            return explode('.', $routeName)[0] . '.';
        }

        $segment = request()->segment(1);

        return $segment ? $segment . '.' : '';
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage($value): void
    {
        // ✅ Unvalidated perPage always capped
        $this->perPage = min((int) $value ?: 10, self::MAX_PER_PAGE);
        $this->resetPage();
    }

    // ── Class filter change → sections reload, page reset ──
    public function updatedFilterClass($value): void
    {
        $this->filterSection     = '';
        $this->availableSections = [];
        $this->resetPage();

        if (!$value) {
            return;
        }

        $institutionId = auth()->user()->institution_id;

        $assigns = AcademicClassAssign::with('section')
            ->where('institution_id', $institutionId)
            ->where('class_id', $value)
            ->whereNotNull('section_id')
            ->get();

        $this->availableSections = $assigns
            ->filter(fn($a) => $a->section)
            ->map(fn($a) => ['id' => $a->section->id, 'name' => $a->section->name])
            ->unique('id')
            ->values()
            ->toArray();
    }

    // ── Section filter change → just reset page, table auto-refresh hobe ──
    public function updatedFilterSection(): void
    {
        $this->resetPage();
    }

    public function sortBy($field): void
    {
        // ✅ Allowlist validation — jekono raw field name orderBy() e jete parbe na
        if (!in_array($field, self::SORTABLE_FIELDS, true)) {
            return;
        }

        if ($this->sortField === $field) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDir   = 'asc';
        }

        $this->resetPage();
    }

    public function confirmDeleteRecord(int $id): void
    {
        $this->deleteId      = $id;
        $this->confirmDelete = true;
    }

    public function deleteRecord(): void
    {
        $institutionId = auth()->user()->institution_id;

        // abort_unless(auth()->user()->can('institution.students.delete'), 403);

        DB::beginTransaction();

        try {
            // ✅ institution_id scoping — IDOR protection
            $student = Student::where('institution_id', $institutionId)
                ->findOrFail($this->deleteId);

            // ✅ Activity log delete() এর আগে (project standard)
            activity()
                ->causedBy(auth()->user())
                ->performedOn($student)
                ->withProperties(['icon' => 'school', 'type' => 'student'])
                ->tap(function ($activity) use ($student) {
                    $activity->institution_id = $student->institution_id;
                    $activity->branch_id = $student->branch_id;
                })
                ->log('Student deleted: ' . $student->name);

            $user = $student->user;

            // ✅ soft-deleted username/email future re-use er jonno suffix
            if ($user) {
                $timestampSuffix = '_deleted_' . now()->timestamp;

                $user->update([
                    'username' => $user->username . $timestampSuffix,
                    'email'    => $user->email ? $user->email . $timestampSuffix : $user->email,
                ]);

                $user->delete();
            }

            $student->delete();

            DB::commit();

            $this->confirmDelete = false;
            $this->deleteId      = null;
            $this->dispatch('toast', type: 'success', message: 'Student deleted successfully!');
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            $this->dispatch('toast', type: 'error', message: 'Something went wrong while deleting the student.');
        }
    }

    /**
     * Status icon click korle eta call hobe — modal open kore, current
     * status ta preselect kore rakhe.
     */
    public function openStatusModal(int $id): void
    {
        // abort_unless(auth()->user()->can('institution.students.edit'), 403);

        $institutionId = auth()->user()->institution_id;

        // ✅ IDOR fix: institution_id scoping
        $student = Student::where('institution_id', $institutionId)
            ->findOrFail($id);

        $this->statusId        = $student->id;
        $this->newStatus       = $student->status;
        $this->showStatusModal = true;
    }

    public function closeStatusModal(): void
    {
        $this->showStatusModal = false;
        $this->statusId        = null;
        $this->newStatus       = '';
    }

    public function updateStatus(): void
    {
        // abort_unless(auth()->user()->can('institution.students.edit'), 403);

        $this->validate([
            'statusId'  => 'required|integer|exists:students,id',
            'newStatus' => 'required|string|in:' . implode(',', $this->statusOptions),
        ]);

        $institutionId = auth()->user()->institution_id;

        // ✅ IDOR fix: institution_id scoping
        $student = Student::where('institution_id', $institutionId)
            ->findOrFail($this->statusId);

        $oldStatus = $student->status;

        $student->update(['status' => $this->newStatus]);

        // ✅ ->tap() diye institution_id + branch_id — project standard onujayi
        activity()
            ->causedBy(auth()->user())
            ->performedOn($student)
            ->withProperties([
                'icon' => 'toggle_on',
                'type' => 'student',
                'old_status' => $oldStatus,
                'new_status' => $this->newStatus,
            ])
            ->tap(function ($activity) use ($student) {
                $activity->institution_id = $student->institution_id;
                $activity->branch_id = $student->branch_id;
            })
            ->log('Student status changed: ' . $student->name . ' (' . $oldStatus . ' → ' . $this->newStatus . ')');

        $this->dispatch('toast', type: 'success', message: 'Status updated successfully!');
        $this->closeStatusModal();
    }

    public function render()
    {
        $institutionId = auth()->user()->institution_id;

        $classes = AcademicClass::where('institution_id', $institutionId)
            ->whereIn('id', AcademicClassAssign::where('institution_id', $institutionId)
                ->distinct()
                ->pluck('class_id'))
            ->orderBy('name')
            ->get();

        // ✅ sortField ke allowlist er against fallback validate kora,
        // jate kono karone invalid value orderBy() e na jay.
        $sortField = in_array($this->sortField, self::SORTABLE_FIELDS, true)
            ? $this->sortField
            : 'created_at';

        $sortDir = $this->sortDir === 'asc' ? 'asc' : 'desc';

        $query = Student::with(['guardians', 'class', 'section'])
            ->where('institution_id', $institutionId)
            ->when($this->filterClass, fn($q) =>
                $q->where('class_id', $this->filterClass)
            )
            ->when($this->filterSection && $this->filterSection !== 'all', fn($q) =>
                $q->where('section_id', $this->filterSection)
            )
            ->when($this->search, fn($q) => $q->where(fn($q) => $q
                ->where('name', 'like', "%{$this->search}%")
                ->orWhere('student_id', 'like', "%{$this->search}%")
                ->orWhere('registration_no', 'like', "%{$this->search}%")
                ->orWhere('roll_no', 'like', "%{$this->search}%")))
            ->orderBy($sortField, $sortDir);

        $perPage = min((int) $this->perPage ?: 10, self::MAX_PER_PAGE);

        $students = $query->paginate($perPage);

        return view('livewire.admin.student.student-list-component')
            ->with('students', $students)
            ->with('classes', $classes)
            ->with('statusOptions', $this->statusOptions)
            ->layout('layouts.admin.app', [
                'title' => 'Student List | ' . institution()->name,
            ]);
    }
}