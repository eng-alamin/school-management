<?php

namespace App\Livewire\Teacher\Leave;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\LeaveApplication;
use App\Models\LeaveCategory;
use App\Models\User;
use Carbon\Carbon;

class ApplyLeaveComponent extends Component
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
    public ?int    $leave_category_id = null;
    public string  $start_date        = '';
    public string  $end_date          = '';
    public string  $reason            = '';
    public string  $comments          = '';
    public ?string $document_path     = null;
    public $attachment                = null;

    // ── Detail modal data ──
    public array $detail = [];

    // ──────────────────────────────────────────
    // Validation
    // ──────────────────────────────────────────
    protected function rules(): array
    {
        return [
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
        $this->start_date = now()->format('Y-m-d');
        $this->end_date   = now()->format('Y-m-d');
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

    private function resetForm(): void
    {
        $this->reset([
            'editId', 'leave_category_id', 'start_date', 'end_date',
            'reason', 'document_path', 'attachment', 'comments',
        ]);
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
    // Modal: Edit — শুধু pending/cancelled হলে
    // ──────────────────────────────────────────
    public function openEdit(int $id): void
    {
        $record = LeaveApplication::findOrFail($id);

        // শুধু নিজের এবং pending/cancelled application edit করা যাবে
        if ($record->applicable_id !== auth()->id() || !in_array($record->status, ['pending', 'cancelled'])) {
            session()->flash('error', 'You cannot edit this application.');
            return;
        }

        $this->editId            = $id;
        $this->leave_category_id = $record->leave_category_id;
        $this->start_date        = $record->start_date->format('Y-m-d');
        $this->end_date          = $record->end_date->format('Y-m-d');
        $this->reason            = $record->reason ?? '';
        $this->comments          = $record->approval_note ?? '';
        $this->document_path     = $record->document_path;

        $this->showDetail = false;
        $this->showModal  = true;
    }

    // ──────────────────────────────────────────
    // Modal: Detail (view only — no status change)
    // ──────────────────────────────────────────
    public function openDetail(int $id): void
    {
        $record    = LeaveApplication::with(['leaveCategory', 'approvedByUser'])->findOrFail($id);

        $this->detail = [
            'id'             => $record->id,
            'reviewed_by'    => optional($record->approvedByUser)->name ?? '—',
            'leave_category' => optional($record->leaveCategory)->name ?? '—',
            'apply_date'     => $record->created_at?->format('d.M.Y h:i A'),
            'start_date'     => $record->start_date->format('d.M.Y'),
            'end_date'       => $record->end_date->format('d.M.Y'),
            'total_days'     => $record->total_days,
            'reason'         => $record->reason ?? '—',
            'approval_note'  => $record->approval_note ?? '—',
            'status'         => $record->status,
            'document_path'  => $record->document_path,
        ];

        $this->detailId   = $id;
        $this->showDetail = true;
        $this->showModal  = false;
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
            'applicable_id'     => auth()->id(),
            'applicable_type'   => User::class,
            'leave_category_id' => $this->leave_category_id,
            'start_date'        => $this->start_date,
            'end_date'          => $this->end_date,
            'total_days'        => $this->getTotalDays(),
            'reason'            => $this->reason,
            'document_path'     => $filePath,
            'approval_note'     => $this->comments,
            'status'            => 'pending',
        ];

        if ($this->editId) {
            LeaveApplication::findOrFail($this->editId)->update($data);
            session()->flash('success', 'Leave application updated successfully!');
        } else {
            LeaveApplication::create($data);
            session()->flash('success', 'Leave application submitted successfully!');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    // ──────────────────────────────────────────
    // Delete — শুধু pending/cancelled হলে
    // ──────────────────────────────────────────
    public function confirmDeleteRecord(int $id): void
    {
        $record = LeaveApplication::findOrFail($id);

        // শুধু নিজের এবং pending/cancelled application delete করা যাবে
        if ($record->applicable_id !== auth()->id() || !in_array($record->status, ['pending', 'cancelled'])) {
            session()->flash('error', 'You can only delete pending or cancelled applications.');
            return;
        }

        $this->deleteId      = $id;
        $this->confirmDelete = true;
    }

    public function deleteRecord(): void
    {
        $record = LeaveApplication::findOrFail($this->deleteId);

        // Double-check before delete
        if ($record->applicable_id !== auth()->id() || !in_array($record->status, ['pending', 'cancelled'])) {
            session()->flash('error', 'You can only delete pending or cancelled applications.');
            $this->confirmDelete = false;
            return;
        }

        $record->delete();
        $this->confirmDelete = false;
        $this->deleteId      = null;
        session()->flash('success', 'Leave application deleted successfully!');
    }

    // ──────────────────────────────────────────
    // Render — শুধু নিজের applications
    // ──────────────────────────────────────────
    public function render()
    {
        $applications = LeaveApplication::query()
            ->with(['leaveCategory', 'approvedByUser'])
            ->where('applicable_id', auth()->id())
            ->where('applicable_type', User::class)
            ->when($this->search, function ($q) {
                $q->whereHas('leaveCategory', fn($c) => $c->where('name', 'like', "%{$this->search}%"));
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $categories = LeaveCategory::orderBy('name')->get();

        return view('livewire.teacher.leave.apply-leave-component', compact('applications', 'categories'))
            ->layout('layouts.teacher.app', [
                'title' => 'My Leave Applications | ' . institution()->name,
            ]);
    }
}