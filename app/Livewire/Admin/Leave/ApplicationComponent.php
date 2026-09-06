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

class ApplicationComponent extends Component
{
    use WithPagination, WithFileUploads;

    protected string $paginationTheme = 'bootstrap';

    private const SORTABLE_FIELDS = ['id', 'start_date', 'end_date', 'status', 'created_at'];

    // ── List / Filter ──
    public string $search        = '';
    public int    $perPage       = 10;
    public string $sortField     = 'id';
    public string $sortDirection = 'desc';
    public string $filterRole    = '';

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
            'role'              => 'required|string',
            'applicable_id'     => 'required|integer',
            'applicable_type'   => 'required|string',
            'leave_category_id' => [
                'required',
                'integer',
                \Illuminate\Validation\Rule::exists('leave_categories', 'id')
                    ->where('institution_id', institution()->id),
            ],
            'start_date'        => 'required|date',
            'end_date'          => 'required|date|after_or_equal:start_date',
            'reason'            => 'nullable|string|max:500',
            'attachment'        => 'nullable|file|max:5120',
            'comments'          => 'nullable|string|max:1000',
        ];
    }

    public function updatingSearch(): void    { $this->resetPage(); }
    public function updatingFilterRole(): void { $this->resetPage(); }
    public function updatedPerPage(): void    { $this->resetPage(); }

    // Role select করলে applicant list লোড হবে, applicable_type সেট হবে
    public function updatedRole(string $value): void
    {
        $this->applicable_id   = null;
        $this->applicable_type = '';
        $this->applicants      = [];

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

        // applicable_type দিয়ে role বের করা সম্ভব না কারণ সব role-ই User::class এর সাথে map করা।
        // আসল role আসবে সম্পর্কিত User রেকর্ডের 'role' column থেকে।
        $this->role = optional($record->applicable)->role ?? '';

        // updatedRole() কল করলে applicable_id null হয়ে যেত, তাই সরাসরি applicants লোড করি
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
            $application = LeaveApplication::with('applicable')
                ->where('institution_id', $institutionId)
                ->where('branch_id', $branchId)
                ->findOrFail($this->detailId);

            $previousStatus = $application->status;

            $application->update([
                'status'        => $this->status,
                'approval_note' => $this->comments,
                'approved_by'   => auth()->id(),
                'approved_at'   => now(),
            ]);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($application)
                ->withProperties([
                    'icon' => 'fact_check',
                    'type' => 'leave',
                    'from' => $previousStatus,
                    'to'   => $this->status,
                ])
                ->tap(function ($activity) use ($institutionId) {
                    $activity->institution_id = $institutionId;
                })
                ->log('Leave application status changed to ' . ucfirst($this->status));

            // শুধু Approve বা Reject হলেই আবেদনকারীকে notification পাঠানো হবে
            if (in_array($this->status, ['approved', 'rejected']) && $application->applicable) {
                $applicant = $application->applicable;

                if ($applicant instanceof User) {
                    $statusLabel = ucfirst($this->status);
                    $period      = $application->start_date->format('d M Y') . ' - ' . $application->end_date->format('d M Y');

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
                        'leave_application_id' => $application->id,
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
                $application = LeaveApplication::with('applicable')
                    ->where('institution_id', $institutionId)
                    ->where('branch_id', $branchId)
                    ->findOrFail($this->editId);

                $oldFilePath = $application->document_path;
                $data['document_path'] = $newFilePath ?: $application->document_path;

                $application->update($data);

                activity()
                    ->causedBy(auth()->user())
                    ->performedOn($application)
                    ->withProperties(['icon' => 'edit', 'type' => 'leave'])
                    ->tap(function ($activity) use ($institutionId) {
                        $activity->institution_id = $institutionId;
                    })
                    ->log('Leave application updated');

                $message = 'Leave application updated successfully!';
            } else {
                $data['institution_id']  = $institutionId;
                $data['branch_id']       = $branchId;
                $data['session_id']      = $sessionId;
                $data['document_path']   = $newFilePath;

                $application = LeaveApplication::create($data);
                $application->load('applicable');

                activity()
                    ->causedBy(auth()->user())
                    ->performedOn($application)
                    ->withProperties(['icon' => 'event_busy', 'type' => 'leave'])
                    ->tap(function ($activity) use ($institutionId) {
                        $activity->institution_id = $institutionId;
                    })
                    ->log('New leave application created');

                $message = 'Leave application created successfully!';
            }

            DB::commit();

            if ($newFilePath && $oldFilePath && $newFilePath !== $oldFilePath) {
                Storage::disk('public')->delete($oldFilePath);
            }

            $this->showModal = false;
            $this->resetForm();
            $this->dispatch('toast', type: 'success', message: $message);
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
            $application = LeaveApplication::with('applicable')
                ->where('institution_id', $institutionId)
                ->where('branch_id', $branchId)
                ->findOrFail($this->deleteId);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($application)
                ->withProperties(['icon' => 'delete', 'type' => 'leave'])
                ->tap(function ($activity) use ($institutionId) {
                    $activity->institution_id = $institutionId;
                })
                ->log('Leave application deleted');

            $application->delete();

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
            ->when($this->filterRole, function ($q) {
                $model = $this->roleModelMap[$this->filterRole] ?? null;
                if ($model) {
                    $q->where('applicable_type', $model);
                    if ($model === User::class) {
                        $q->whereHasMorph('applicable', $model, fn($e) => $e->where('role', $this->filterRole));
                    }
                }
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $categories = LeaveCategory::where('institution_id', $institutionId)
            ->orderBy('name')
            ->get();

        return view('livewire.admin.leave.application-component', compact('applications', 'categories'))
            ->layout('layouts.admin.app', [
                'title' => 'Leave Application | ' . institution()->name,
            ]);
    }
}