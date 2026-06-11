<?php

namespace App\Livewire\Tenant\Student;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\LeaveApplication;
use App\Models\LeaveCategory;
use App\Models\User;
use Carbon\Carbon;

class LeaveComponent extends Component
{
    use WithPagination, WithFileUploads;

    protected string $paginationTheme = 'bootstrap';

    // ── List ──
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
    public string  $status            = 'pending';
    public ?string $document_path     = null;
    public $attachment                = null;

    // ── Detail modal data ──
    public array $detail = [];

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

    public function mount(): void
    {
        $this->start_date = now()->format('Y-m-d');
        $this->end_date   = now()->format('Y-m-d');
    }

    public function updatingSearch(): void { $this->resetPage(); }

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
            'reason', 'document_path', 'attachment', 'comments', 'status',
        ]);
        $this->resetValidation();
    }

    public function sortBy(string $field): void
    {
        $this->sortDirection = ($this->sortField === $field && $this->sortDirection === 'asc')
            ? 'desc' : 'asc';
        $this->sortField = $field;
        $this->resetPage();
    }

    // ── Create ──
    public function openCreate(): void
    {
        $this->resetForm();
        $this->start_date = now()->format('Y-m-d');
        $this->end_date   = now()->format('Y-m-d');
        $this->showModal  = true;
    }

    // ── Detail view only ──
    public function openDetail(int $id): void
    {
        $record    = LeaveApplication::with(['applicable', 'leaveCategory', 'approvedByUser'])->findOrFail($id);
        $applicant = $record->applicable;

        $this->detail = [
            'id'             => $record->id,
            'reviewed_by'    => optional($record->approvedByUser)->name ?? '—',
            'applicant'      => optional($applicant)->name ?? '—',
            'leave_category' => optional($record->leaveCategory)->name ?? '—',
            'apply_date'     => $record->created_at?->format('d.M.Y h:i A'),
            'start_date'     => $record->start_date->format('d.M.Y'),
            'end_date'       => $record->end_date->format('d.M.Y'),
            'total_days'     => $record->total_days,
            'reason'         => $record->reason ?? '—',
            'document_path'  => $record->document_path,
            'status'         => $record->status,
        ];

        $this->detailId   = $id;
        $this->showDetail = true;
        $this->showModal  = false;
    }

    // ── Save ──
    public function save(): void
    {
        $this->validate();

        $user    = auth()->user();
        $student = $user->student;

        $filePath = null;
        if ($this->attachment) {
            $filePath = $this->attachment->store('leave-attachments', 'public');
        }

        LeaveApplication::create([
            'applicable_id'     => $user->id,
            'applicable_type'   => User::class,
            'leave_category_id' => $this->leave_category_id,
            'start_date'        => $this->start_date,
            'end_date'          => $this->end_date,
            'total_days'        => $this->getTotalDays(),
            'reason'            => $this->reason,
            'document_path'     => $filePath,
            'approval_note'     => $this->comments,
            'status'            => 'pending',
        ]);

        session()->flash('success', 'Leave application submitted successfully!');
        $this->showModal = false;
        $this->resetForm();
    }

    // ── Delete ──
    public function confirmDeleteRecord(int $id): void
    {
        $this->deleteId      = $id;
        $this->confirmDelete = true;
    }

    public function deleteRecord(): void
    {
        $app = LeaveApplication::findOrFail($this->deleteId);

        // শুধু pending application delete করা যাবে
        if ($app->status !== 'pending') {
            session()->flash('error', 'Only pending applications can be deleted.');
            $this->confirmDelete = false;
            return;
        }

        $app->delete();
        $this->confirmDelete = false;
        $this->deleteId      = null;
        session()->flash('success', 'Leave application deleted successfully!');
    }

    public function render()
    {
        $user = auth()->user();

        $applications = LeaveApplication::query()
            ->with(['applicable', 'leaveCategory'])
            ->where('applicable_id', $user->id)
            ->where('applicable_type', User::class)
            ->when($this->search, fn($q) => $q
                ->whereHas('leaveCategory', fn($c) => $c->where('name', 'like', "%{$this->search}%"))
            )
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $categories = LeaveCategory::orderBy('name')->get();

        return view('livewire.tenant.student.leave-component', compact('applications', 'categories'))
            ->layout('layouts.student.app', [
                'title' => "Leaves | Monarchy School",
            ]);
    }
}