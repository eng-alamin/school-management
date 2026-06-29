<?php

namespace App\Livewire\Teacher\Leave;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\LeaveApplication;
use App\Models\LeaveCategory;
use App\Models\User;
use Carbon\Carbon;

class StudentLeaveComponent extends Component
{
    use WithPagination, WithFileUploads;

    protected string $paginationTheme = 'bootstrap';

    // ── List / Filter ──
    public string $search        = '';
    public int    $perPage       = 10;
    public string $sortField     = 'id';
    public string $sortDirection = 'asc';

    // ── Modal flags ──
    public bool $showModal     = false;
    public bool $showDetail    = false;
    public bool $confirmDelete = false;
    public ?int  $deleteId     = null;
    public ?int  $detailId     = null;

    // ── Form fields ──
    public ?int    $editId            = null;
    public ?int    $applicable_id     = null;
    public string  $applicable_type   = '';
    public ?int    $leave_category_id = null;
    public string  $start_date        = '';
    public string  $end_date          = '';
    public string  $reason            = '';
    public string  $comments          = '';
    public string  $status            = 'pending';
    public ?string $document_path     = null;
    public $attachment                = null;

    // ── Student list ──
    public array $applicants = [];

    // ── Detail modal data ──
    public array $detail = [];

    // ── Hardcoded role & model for this component ──
    protected string $fixedRole  = 'student';
    protected string $modelClass = User::class;

    // ──────────────────────────────────────────
    // Validation
    // ──────────────────────────────────────────
    protected function rules(): array
    {
        return [
            'applicable_id'     => 'required|integer',
            'leave_category_id' => 'required|integer',
            'start_date'        => 'required|date',
            'end_date'          => 'required|date|after_or_equal:start_date',
            'reason'            => 'nullable|string|max:500',
            'attachment'        => 'nullable|file|max:5120',
            'comments'          => 'nullable|string|max:1000',
        ];
    }

    // ──────────────────────────────────────────
    // Lifecycle
    // ──────────────────────────────────────────
    public function mount(): void
    {
        $this->applicable_type = $this->modelClass;
        $this->start_date      = now()->format('Y-m-d');
        $this->end_date        = now()->format('Y-m-d');
        $this->loadApplicants();
    }

    public function updatingSearch(): void { $this->resetPage(); }

    public function sortBy(string $field): void
    {
        $this->sortDirection = ($this->sortField === $field && $this->sortDirection === 'asc')
            ? 'desc' : 'asc';
        $this->sortField = $field;
        $this->resetPage();
    }

    // ──────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────
    public function getTotalDays(): int
    {
        if ($this->start_date && $this->end_date) {
            return (int) Carbon::parse($this->start_date)
                ->diffInDays(Carbon::parse($this->end_date)) + 1;
        }
        return 0;
    }

    private function loadApplicants(): void
    {
        $this->applicants = User::where('role', $this->fixedRole)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->toArray();
    }

    private function resetForm(): void
    {
        $this->reset([
            'editId', 'applicable_id',
            'leave_category_id', 'start_date', 'end_date',
            'reason', 'document_path', 'attachment', 'comments', 'status',
        ]);
        $this->applicable_type = $this->modelClass;
        $this->resetValidation();
    }

    // ──────────────────────────────────────────
    // Modal: Create
    // ──────────────────────────────────────────
    public function openCreate(): void
    {
        $this->resetForm();
        $this->start_date = now()->format('Y-m-d');
        $this->end_date   = now()->format('Y-m-d');
        $this->showModal  = true;
    }

    // ──────────────────────────────────────────
    // Modal: Edit
    // ──────────────────────────────────────────
    public function openEdit(int $id): void
    {
        $record = LeaveApplication::with('applicable')->findOrFail($id);

        $this->editId            = $id;
        $this->applicable_type   = $record->applicable_type;
        $this->applicable_id     = $record->applicable_id;
        $this->leave_category_id = $record->leave_category_id;
        $this->start_date        = $record->start_date->format('Y-m-d');
        $this->end_date          = $record->end_date->format('Y-m-d');
        $this->reason            = $record->reason ?? '';
        $this->comments          = $record->approval_note ?? '';
        $this->status            = $record->status;
        $this->document_path     = $record->document_path;

        $this->showDetail = false;
        $this->showModal  = true;
    }

    // ──────────────────────────────────────────
    // Modal: Detail (review/approve)
    // ──────────────────────────────────────────
    public function openDetail(int $id): void
    {
        $record    = LeaveApplication::with(['applicable', 'leaveCategory', 'approvedByUser'])->findOrFail($id);
        $applicant = $record->applicable;

        $this->detail = [
            'id'             => $record->id,
            'reviewed_by'    => optional($record->approvedByUser)->name ?? '—',
            'applicant'      => optional($applicant)->name ?? '—',
            'staff_id'       => optional($applicant)->student_id ?? optional($applicant)->id ?? '—',
            'leave_category' => optional($record->leaveCategory)->name ?? '—',
            'apply_date'     => $record->created_at?->format('d.M.Y h:i A'),
            'start_date'     => $record->start_date->format('d.M.Y'),
            'end_date'       => $record->end_date->format('d.M.Y'),
            'reason'         => $record->reason ?? '—',
            'document_path'  => $record->document_path,
        ];

        $this->status     = $record->status;
        $this->comments   = $record->approval_note ?? '';
        $this->detailId   = $id;
        $this->showDetail = true;
        $this->showModal  = false;
    }

    public function saveDetail(): void
    {
        $this->validate([
            'status'   => 'required|in:pending,approved,rejected,cancelled',
            'comments' => 'nullable|string|max:1000',
        ]);

        LeaveApplication::findOrFail($this->detailId)->update([
            'status'        => $this->status,
            'approval_note' => $this->comments,
            'approved_by'   => auth()->id(),
            'approved_at'   => now(),
        ]);

        session()->flash('success', 'Status updated successfully!');
        $this->showDetail = false;
        $this->reset(['detailId', 'detail', 'comments']);
    }

    // ──────────────────────────────────────────
    // Save (Create / Update)
    // ──────────────────────────────────────────
    public function save(): void
    {
        $this->validate();

        $filePath = $this->document_path;
        if ($this->attachment) {
            $filePath = $this->attachment->store('leave-attachments', 'public');
        }

        $data = [
            'applicable_id'     => $this->applicable_id,
            'applicable_type'   => $this->applicable_type,
            'leave_category_id' => $this->leave_category_id,
            'start_date'        => $this->start_date,
            'end_date'          => $this->end_date,
            'total_days'        => $this->getTotalDays(),
            'reason'            => $this->reason,
            'document_path'     => $filePath,
            'approval_note'     => $this->comments,
            'status'            => $this->status ?: 'pending',
        ];

        if ($this->editId) {
            LeaveApplication::findOrFail($this->editId)->update($data);
            session()->flash('success', 'Leave application updated successfully!');
        } else {
            LeaveApplication::create($data);
            session()->flash('success', 'Leave application created successfully!');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    // ──────────────────────────────────────────
    // Delete
    // ──────────────────────────────────────────
    public function confirmDeleteRecord(int $id): void
    {
        $this->deleteId      = $id;
        $this->confirmDelete = true;
    }

    public function deleteRecord(): void
    {
        LeaveApplication::findOrFail($this->deleteId)->delete();
        $this->confirmDelete = false;
        $this->deleteId      = null;
        session()->flash('success', 'Leave application deleted successfully!');
    }

    // ──────────────────────────────────────────
    // Render
    // ──────────────────────────────────────────
    public function render()
    {
        $applications = LeaveApplication::query()
            ->with(['applicable', 'leaveCategory'])
            ->where('applicable_type', $this->modelClass)
            ->whereHasMorph('applicable', $this->modelClass, fn($q) => $q->where('role', $this->fixedRole))
            ->when($this->search, function ($q) {
                $q->where(function ($inner) {
                    $inner->whereHasMorph('applicable', $this->modelClass, fn($e) => $e->where('name', 'like', "%{$this->search}%"))
                          ->orWhereHas('leaveCategory', fn($c) => $c->where('name', 'like', "%{$this->search}%"));
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $categories = LeaveCategory::orderBy('name')->get();

        return view('livewire.teacher.leave.student-leave-component', compact('applications', 'categories'))
            ->layout('layouts.teacher.app', [
                'title' => 'Student Leave Application | ' . institution()->name,
            ]);
    }
}