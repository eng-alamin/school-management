<?php

namespace App\Livewire\ITSupport\Academic;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use App\Models\StudentEnrollment;
use App\Models\AcademicClass;
use App\Models\AcademicClassAssign;

class StudentEnrollmentComponent extends Component
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

    protected array $statusOptions = ['running', 'promoted', 'left', 'alumni'];

    /**
     * sortBy() ke arbitrary column-e sort korte deoya jabe na —
     * shudhu allowlist-e thaka field gulo accept hobe.
     */
    private const SORTABLE_FIELDS = ['created_at', 'roll_no', 'status'];

    public function updatedSearch(): void
    {
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

        $assigns = AcademicClassAssign::with('section')
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
        try {
            DB::transaction(function () {
                $enrollment = StudentEnrollment::where('institution_id', institution()->id)
                    ->findOrFail($this->deleteId);

                $enrollment->delete();
            });

            $this->confirmDelete = false;
            $this->deleteId      = null;

            $this->dispatch('toast', type: 'success', message: 'Enrollment deleted successfully!');
        } catch (\Exception $e) {
            $this->dispatch('toast', type: 'error', message: 'Delete failed: ' . $e->getMessage());
        }
    }

    /**
     * Status icon click korle eta call hobe — modal open kore, current
     * status ta preselect kore rakhe.
     */
    public function openStatusModal(int $id): void
    {
        $enrollment = StudentEnrollment::where('institution_id', institution()->id)
            ->findOrFail($id);

        $this->statusId        = $enrollment->id;
        $this->newStatus       = $enrollment->status;
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
        $this->validate([
            'statusId'  => 'required|integer|exists:student_enrollments,id,institution_id,' . institution()->id,
            'newStatus' => 'required|string|in:' . implode(',', $this->statusOptions),
        ]);

        DB::transaction(function () {
            $enrollment = StudentEnrollment::where('institution_id', institution()->id)
                ->findOrFail($this->statusId);

            $oldStatus = $enrollment->status;

            $enrollment->update(['status' => $this->newStatus]);

            activity()
                ->performedOn($enrollment)
                ->withProperties([
                    'institution_id' => $enrollment->institution_id,
                    'icon' => 'toggle_on',
                    'type' => 'student_enrollment',
                    'old_status' => $oldStatus,
                    'new_status' => $this->newStatus,
                ])
                ->log('Enrollment status changed: ' . $enrollment->student?->name . ' (' . $oldStatus . ' → ' . $this->newStatus . ')');
        });

        $this->dispatch('toast', type: 'success', message: 'Status updated successfully!');

        $this->closeStatusModal();
    }

    public function render()
    {
        $classes = AcademicClass::whereIn('id', AcademicClassAssign::distinct()->pluck('class_id'))
            ->orderBy('name')
            ->get();

        $enrollments = StudentEnrollment::with(['student', 'class', 'section', 'group'])
            ->where('institution_id', institution()->id)
            ->when($this->filterClass, fn($q) =>
                $q->where('class_id', $this->filterClass)
            )
            ->when($this->filterSection && $this->filterSection !== 'all', fn($q) =>
                $q->where('section_id', $this->filterSection)
            )
            ->when($this->search, fn($q) => $q->where(function ($q) {
                $q->where('roll_no', 'like', "%{$this->search}%")
                    ->orWhereHas('student', function ($sq) {
                        $sq->where('name', 'like', "%{$this->search}%")
                            ->orWhere('student_id', 'like', "%{$this->search}%")
                            ->orWhere('registration_no', 'like', "%{$this->search}%");
                    });
            }))
            ->orderBy($this->sortField, $this->sortDir)
            ->paginate($this->perPage);

        return view('livewire.admin.academic.student-enrollment-component')
            ->with('enrollments', $enrollments)
            ->with('classes', $classes)
            ->with('statusOptions', $this->statusOptions)
            ->layout('layouts.itsupport.app', [
                'title' => 'Student Enrollment | ' . institution()->name,
            ]);
    }
}