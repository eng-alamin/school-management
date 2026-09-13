<?php

namespace App\Livewire\Admin\Leave;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\LeaveApplication;
use App\Models\LeaveCategory;
use App\Models\AcademicSession;
use App\Models\Branch;
use App\Models\User;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ApplicationComponent extends Component
{
    use WithPagination, WithFileUploads;

    protected string $paginationTheme = 'bootstrap';

    private const SORTABLE_FIELDS = [
        'id', 'applicable_type', 'leave_category_id',
        'start_date', 'end_date', 'total_days', 'status', 'created_at',
    ];

    // ── List / Filter ──
    public string $search        = '';
    public int    $perPage       = 10;
    public string $sortField     = 'id';
    public string $sortDirection = 'desc';
    public string $roleFilter    = 'all';

    // ── Modal flags ──
    public bool $showModal     = false;
    public bool $showDetail    = false;
    public bool $confirmDelete = false;
    public ?int  $deleteId     = null;
    public ?int  $detailId     = null;

    // ── Form fields ──
    public ?int    $editId            = null;
    public string  $role              = '';
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

    // ── Dynamic applicant list (role select করলে populate হবে) ──
    public array $applicants = [];

    // ── Detail modal data ──
    public array $detail = [];

    // ── Role → Model class map ──
    protected array $roleModelMap = [
        'teacher'      => User::class,
        'accountant'   => User::class,
        'staff'        => User::class,
        'student'      => User::class,
    ];

    // Blade-এ role select / filter এই একই সোর্স থেকে বানানো হয়,
    // যাতে roleModelMap-এর সাথে drift না হয়।
    public const ROLE_LABELS = [
        'teacher'    => 'Teacher',
        'accountant' => 'Accountant',
        'staff'      => 'Staff',
        'student'    => 'Student',
    ];

    public string $routePrefix = '';

    public ?int $currentSessionId = null;

    public function mount(): void
    {
        $this->routePrefix = $this->resolveRoutePrefix();

        $this->start_date = now()->format('Y-m-d');
        $this->end_date   = now()->format('Y-m-d');

        $this->currentSessionId = $this->resolveCurrentSessionId();
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

    private function activeBranchId(): ?int
    {
        return auth()->user()->branch_id
            ?? Branch::resolveMainBranchId(institution()->id);
    }

    private function resolveCurrentSessionId(): ?int
    {
        return AcademicSession::query()
            ->where('institution_id', institution()->id)
            ->where('branch_id', $this->activeBranchId())
            ->active() // scopeActive() -> is_current = true
            ->value('id');
    }

    // ──────────────────────────────────────────
    // Validation
    // ──────────────────────────────────────────
    protected function rules(): array
    {
        return [
            'role'            => ['required', Rule::in(array_keys($this->roleModelMap))],
            'applicable_type' => ['required', Rule::in(array_values($this->roleModelMap))],
            'applicable_id'   => [
                'required',
                'integer',
                Rule::exists('users', 'id')
                    ->where('institution_id', institution()->id)
                    ->where('branch_id', $this->activeBranchId())
                    ->where('role', $this->role),
            ],
            'leave_category_id' => [
                'required',
                'integer',
                Rule::exists('leave_categories', 'id')
                    ->where('institution_id', institution()->id)
                    ->where('branch_id', $this->activeBranchId())
                    ->where('role', $this->role),
            ],
            'start_date'        => 'required|date',
            'end_date'          => 'required|date|after_or_equal:start_date',
            'reason'            => 'nullable|string|max:500',
            'attachment'        => 'nullable|file|max:5120',
            'comments'          => 'nullable|string|max:1000',
        ];
    }

    public function updatingSearch(): void    { $this->resetPage(); }
    public function updatingRoleFilter(): void { $this->resetPage(); }
    public function updatedPerPage(): void    { $this->resetPage(); }

    // Role select করলে applicant list ও role-অনুযায়ী leave category লোড হবে
    public function updatedRole(string $value): void
    {
        $this->applicable_id     = null;
        $this->applicable_type   = '';
        $this->applicants        = [];
        $this->leave_category_id = null;

        if (!$value) return;

        $modelClass = $this->roleModelMap[$value] ?? null;
        if (!$modelClass) return;

        $this->applicable_type = $modelClass;
        $this->applicants      = $this->loadApplicantsForRole($value);
    }

    public function sortBy(string $field): void
    {
        if (! in_array($field, self::SORTABLE_FIELDS, true)) {
            return;
        }

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

    // role অনুযায়ী applicant list লোড করার জন্য আলাদা helper —
    // এটা applicable_id রিসেট করে না, তাই edit মোডে নিরাপদে ব্যবহার করা যায়
    private function loadApplicantsForRole(string $value): array
    {
        if (!$value) return [];

        $modelClass = $this->roleModelMap[$value] ?? null;
        if (!$modelClass) return [];

        if ($modelClass === User::class) {
            return $modelClass::where('institution_id', institution()->id)
                ->where('branch_id', $this->activeBranchId())
                ->where('role', $value)
                ->orderBy('name')
                ->get(['id', 'name'])
                ->toArray();
        }

        return $modelClass::where('institution_id', institution()->id)
            ->where('branch_id', $this->activeBranchId())
            ->orderBy('name')
            ->get(['id', 'name'])
            ->toArray();
    }

    private function resetForm(): void
    {
        $this->reset([
            'editId', 'role', 'applicable_id', 'applicable_type',
            'leave_category_id', 'start_date', 'end_date',
            'reason', 'document_path', 'attachment', 'comments', 'status',
        ]);
        $this->applicants = [];
        $this->status = 'pending';
        $this->resetValidation();
    }

    // ──────────────────────────────────────────
    // Modal: Create
    // ──────────────────────────────────────────
    public function openCreate(): void
    {
        abort_unless((bool) $this->currentSessionId, 422, 'No active academic session found. Please set a current session first.');

        $this->resetForm();
        $this->start_date = now()->format('Y-m-d');
        $this->end_date   = now()->format('Y-m-d');
        $this->showModal  = true;
    }

    // ──────────────────────────────────────────
    // Modal: Edit — table row থেকে edit icon click করলে
    // ──────────────────────────────────────────
    public function openEdit(int $id): void
    {
        $record = LeaveApplication::with('applicable')
            ->where('institution_id', institution()->id)
            ->where('branch_id', $this->activeBranchId())
            ->findOrFail($id);

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

        $this->role = optional($record->applicable)->role ?? '';

        $this->applicants = $this->loadApplicantsForRole($this->role);

        $this->showDetail = false;
        $this->showModal  = true;
    }

    // ──────────────────────────────────────────
    // Modal: Detail (review/approve)
    // ──────────────────────────────────────────
    public function openDetail(int $id): void
    {
        $record = LeaveApplication::with(['applicable', 'leaveCategory', 'approvedByUser'])
            ->where('institution_id', institution()->id)
            ->where('branch_id', $this->activeBranchId())
            ->findOrFail($id);

        $applicant = $record->applicable;

        $this->detail = [
            'id'             => $record->id,
            'reviewed_by'    => optional($record->approvedByUser)->name ?? '—',
            'applicant'      => optional($applicant)->name ?? '—',
            'staff_id'       => optional($applicant)->employee_id ?? optional($applicant)->id ?? '—',
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

        $institutionId = institution()->id;
        $branchId      = $this->activeBranchId();

        DB::beginTransaction();

        try {
            $record = LeaveApplication::with('applicable')
                ->where('institution_id', $institutionId)
                ->where('branch_id', $branchId)
                ->findOrFail($this->detailId);

            $previousStatus = $record->status;

            $record->update([
                'status'        => $this->status,
                'approval_note' => $this->comments,
                'approved_by'   => auth()->id(),
                'approved_at'   => now(),
            ]);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($record)
                ->withProperties(['icon' => 'exit_to_app', 'type' => 'leave'])
                ->tap(function ($activity) use ($record) {
                    $activity->institution_id = $record->institution_id;
                    $activity->branch_id = $record->branch_id;
                })
                ->log('Leave application status changed to ' . ucfirst($this->status));

            // শুধু Approve বা Reject হলেই আবেদনকারীকে notification পাঠানো হবে
            if (in_array($this->status, ['approved', 'rejected']) && $record->applicable) {
                $applicant = $record->applicable;

                if ($applicant instanceof User) {
                    $statusLabel = ucfirst($this->status);
                    $period      = $record->start_date->format('d M Y') . ' - ' . $record->end_date->format('d M Y');

                    NotificationService::send(
                        $applicant,
                        'leave_request',
                        "Leave {$statusLabel}",
                        "আপনার {$period} সময়ের Leave Application {$statusLabel} করা হয়েছে।"
                            . ($this->comments ? " মন্তব্য: {$this->comments}" : ''),
                        ['icon' => 'exit_to_app'],
                        $this->status === 'rejected' ? 'high' : 'normal',
                    );
                } else {
                    Log::warning('Leave notification skipped: applicant is not a User instance.', [
                        'leave_application_id' => $record->id,
                        'applicable_type'      => get_class($applicant),
                    ]);
                }
            }

            DB::commit();

            $this->showDetail = false;
            $this->reset(['detailId', 'detail', 'comments']);
            $this->dispatch('toast', type: 'success', message: 'Status updated successfully!');
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatch('toast', type: 'error', message: 'কিছু একটা সমস্যা হয়েছে, আবার চেষ্টা করুন।');
            report($e);
        }
    }

    // ──────────────────────────────────────────
    // Save (Create / Update)
    // ──────────────────────────────────────────
    public function save(): void
    {
        abort_unless((bool) $this->currentSessionId, 422, 'No active academic session found. Please set a current session first.');

        $this->validate();

        $institutionId = institution()->id;
        $branchId      = $this->activeBranchId();
        $sessionId     = $this->currentSessionId;

        $newFilePath = null;
        $oldFilePath = null;

        DB::beginTransaction();

        try {
            if ($this->attachment) {
                $newFilePath = $this->attachment->store('leave-attachments', 'public');
            }

            $data = [
                'applicable_id'     => $this->applicable_id,
                'applicable_type'   => $this->applicable_type,
                'leave_category_id' => $this->leave_category_id,
                'start_date'        => $this->start_date,
                'end_date'          => $this->end_date,
                'total_days'        => $this->getTotalDays(),
                'reason'            => $this->reason,
                'approval_note'     => $this->comments,
                'status'            => $this->status ?: 'pending',
            ];

            if ($this->editId) {
                $record = LeaveApplication::with('applicable')
                    ->where('institution_id', $institutionId)
                    ->where('branch_id', $branchId)
                    ->findOrFail($this->editId);

                $oldFilePath = $record->document_path;
                $data['document_path'] = $newFilePath ?: $record->document_path;

                $record->update($data);

                activity()
                    ->causedBy(auth()->user())
                    ->performedOn($record)
                    ->withProperties(['icon' => 'exit_to_app', 'type' => 'leave'])
                    ->tap(function ($activity) use ($record) {
                        $activity->institution_id = $record->institution_id;
                        $activity->branch_id = $record->branch_id;
                    })
                    ->log('Leave application updated: ' . optional($record->applicable)->name . ' (' . $record->start_date->format('d M Y') . ' - ' . $record->end_date->format('d M Y') . ')');
            } else {
                $data['institution_id']  = $institutionId;
                $data['branch_id']       = $branchId;
                $data['session_id']      = $sessionId;
                $data['document_path']   = $newFilePath;

                $record = LeaveApplication::create($data);
                $record->load('applicable');

                activity()
                    ->causedBy(auth()->user())
                    ->performedOn($record)
                    ->withProperties(['icon' => 'exit_to_app', 'type' => 'leave'])
                    ->tap(function ($activity) use ($record) {
                        $activity->institution_id = $record->institution_id;
                        $activity->branch_id = $record->branch_id;
                    })
                    ->log('Leave application created: ' . optional($record->applicable)->name . ' (' . $record->start_date->format('d M Y') . ' - ' . $record->end_date->format('d M Y') . ')');
            }

            DB::commit();

            if ($newFilePath && $oldFilePath && $newFilePath !== $oldFilePath) {
                Storage::disk('public')->delete($oldFilePath);
            }

            $this->showModal = false;
            $this->resetForm();
            $this->dispatch('toast', type: 'success', message: $this->editId ? 'Leave application updated successfully!' : 'Leave application created successfully!');
        } catch (\Throwable $e) {
            DB::rollBack();

            if ($newFilePath) {
                Storage::disk('public')->delete($newFilePath);
            }

            $this->dispatch('toast', type: 'error', message: 'কিছু একটা সমস্যা হয়েছে, আবার চেষ্টা করুন।');
            report($e);
        }
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
        $institutionId = institution()->id;
        $branchId      = $this->activeBranchId();

        DB::beginTransaction();

        try {
            $record = LeaveApplication::with('applicable')
                ->where('institution_id', $institutionId)
                ->where('branch_id', $branchId)
                ->findOrFail($this->deleteId);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($record)
                ->withProperties(['icon' => 'exit_to_app', 'type' => 'leave'])
                ->tap(function ($activity) use ($record) {
                    $activity->institution_id = $record->institution_id;
                    $activity->branch_id = $record->branch_id;
                })
                ->log('Leave application deleted: ' . optional($record->applicable)->name . ' (' . $record->start_date->format('d M Y') . ' - ' . $record->end_date->format('d M Y') . ')');

            $record->delete();

            DB::commit();

            $this->confirmDelete = false;
            $this->deleteId      = null;
            $this->dispatch('toast', type: 'success', message: 'Leave application deleted successfully!');
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->confirmDelete = false;
            $this->dispatch('toast', type: 'error', message: 'কিছু একটা সমস্যা হয়েছে, আবার চেষ্টা করুন।');
            report($e);
        }
    }

    // ──────────────────────────────────────────
    // Render
    // ──────────────────────────────────────────
    public function render()
    {
        $institutionId = institution()->id;
        $branchId      = $this->activeBranchId();

        $applications = LeaveApplication::query()
            ->with(['applicable', 'leaveCategory'])
            ->where('institution_id', $institutionId)
            ->where('branch_id', $branchId)
            ->where('session_id', $this->currentSessionId)
            ->when($this->search, function ($q) {
                $q->where(function ($inner) {
                    $inner->whereHasMorph('applicable', '*', fn($e) => $e->where('name', 'like', "%{$this->search}%"))
                          ->orWhereHas('leaveCategory', fn($c) => $c->where('name', 'like', "%{$this->search}%"));
                });
            })
            ->when($this->roleFilter !== 'all', function ($q) {
                $model = $this->roleModelMap[$this->roleFilter] ?? null;
                if ($model) {
                    $q->where('applicable_type', $model);
                    if ($model === User::class) {
                        $q->whereHasMorph('applicable', $model, fn($e) => $e->where('role', $this->roleFilter));
                    }
                }
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        // Modal-এর Leave Category dropdown — শুধু বর্তমান institution + branch + সিলেক্ট করা Role-এর
        // জন্য প্রযোজ্য ক্যাটাগরি (branch_id filter যোগ করা হলো, নাহলে অন্য branch-এর category দেখা যেত)
        $categories = LeaveCategory::where('institution_id', $institutionId)
            ->where('branch_id', $branchId)
            ->when($this->role, fn ($q) => $q->where('role', $this->role))
            ->orderBy('name')
            ->get();

        return view('livewire.admin.leave.application-component', [
            'applications' => $applications,
            'categories'   => $categories,
            'roles'        => self::ROLE_LABELS,
        ])
            ->layout('layouts.admin.app', [
                'title' => 'Leave Application | ' . institution()->name,
            ]);
    }
}