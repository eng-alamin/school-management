<?php

namespace App\Livewire\Admin\Notice;

use Livewire\Component;
use App\Models\Notice;
use App\Models\User;
use App\Models\Branch;
use App\Models\AcademicSession;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Services\NotificationService;
use sms_net_bd\SMS;

class NoticeComponent extends Component
{
    use WithPagination, WithFileUploads;

    protected string $paginationTheme = 'bootstrap';

    private const SORTABLE_FIELDS = ['created_at', 'title', 'published_at', 'priority', 'status'];

    // List
    public string $search = '';
    public string $filterAudience = '';
    public string $filterPriority = '';
    public string $filterStatus = '';
    public int $perPage = 10;

    // Modal
    public bool $showModal = false;
    public bool $showViewModal = false;
    public bool $confirmDelete = false;
    public ?int $deleteId = null;
    public ?Notice $viewRecord = null;

    // Form
    public ?int $editId = null;
    public string $title = '';
    public string $description = '';
    public string $audience = 'all';
    public string $priority = 'medium';
    public string $status = 'active';
    public string $published_at = '';
    public string $expires_at = '';
    public $attachment = null;
    public string $existingAttachment = '';
    public string $existingAttachmentName = '';
    public bool $sendSms = false;

    public ?int $currentSessionId = null;

    protected function rules(): array
    {
        return [
            'title' => 'required|min:3|max:255',
            'description' => 'required|min:10',
            'audience' => 'required|in:all,admin,teacher,student,parent',
            'priority' => 'required|in:low,medium,high,urgent',
            'status' => 'required|in:active,inactive',
            'published_at' => 'required|date',
            'expires_at' => 'nullable|date|after:published_at',
            'attachment' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
        ];
    }

    public function mount(): void
    {
        $this->published_at = today()->toDateString();
        $this->currentSessionId = $this->resolveCurrentSessionId();
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

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingFilterAudience(): void { $this->resetPage(); }
    public function updatingFilterPriority(): void { $this->resetPage(); }
    public function updatingFilterStatus(): void { $this->resetPage(); }
    public function updatedPerPage(): void { $this->resetPage(); }

    public function openCreate(): void
    {
        abort_unless((bool) $this->currentSessionId, 422, 'No active academic session found. Please set a current session first.');

        $this->resetForm();
        $this->editId = null;
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $record = Notice::where('institution_id', institution()->id)
            ->where('branch_id', $this->activeBranchId())
            ->where('session_id', $this->currentSessionId)
            ->findOrFail($id);

        $this->editId = $id;
        $this->title = $record->title;
        $this->description = $record->description;
        $this->audience = $record->audience;
        $this->priority = $record->priority;
        $this->status = $record->status;
        $this->published_at = $record->published_at->toDateString();
        $this->expires_at = $record->expires_at?->toDateString() ?? '';
        $this->existingAttachment = $record->attachment ?? '';
        $this->existingAttachmentName = $record->attachment_name ?? '';
        $this->attachment = null;
        $this->showModal = true;
    }

    public function openView(int $id): void
    {
        $this->viewRecord = Notice::with('creator')
            ->where('institution_id', institution()->id)
            ->where('branch_id', $this->activeBranchId())
            ->where('session_id', $this->currentSessionId)
            ->findOrFail($id);

        $this->showViewModal = true;
    }

    public function save(): void
    {
        abort_unless((bool) $this->currentSessionId, 422, 'No active academic session found. Please set a current session first.');

        $this->validate();

        $institutionId = institution()->id;
        $branchId      = $this->activeBranchId();
        $sessionId     = $this->currentSessionId;

        DB::beginTransaction();

        try {
            $attachmentPath = $this->existingAttachment;
            $attachmentName = $this->existingAttachmentName;
            $oldAttachmentToDelete = null;

            if ($this->attachment) {
                if ($attachmentPath) {
                    $oldAttachmentToDelete = $attachmentPath;
                }
                $attachmentPath = $this->attachment->store('notices', 'public');
                $attachmentName = $this->attachment->getClientOriginalName();
            }

            $data = [
                'created_by' => auth()->id(),
                'title' => $this->title,
                'description' => $this->description,
                'audience' => $this->audience,
                'priority' => $this->priority,
                'status' => $this->status,
                'published_at' => $this->published_at,
                'expires_at' => $this->expires_at ?: null,
                'attachment' => $attachmentPath ?: null,
                'attachment_name' => $attachmentName ?: null,
            ];

            $isNew = ! $this->editId;

            if ($this->editId) {
                $record = Notice::where('institution_id', $institutionId)
                    ->where('branch_id', $branchId)
                    ->where('session_id', $sessionId)
                    ->findOrFail($this->editId);

                $record->update($data);

                activity()
                    ->causedBy(auth()->user())
                    ->performedOn($record)
                    ->withProperties(['icon' => 'campaign', 'type' => 'notice'])
                    ->tap(function ($activity) use ($record) {
                        $activity->institution_id = $record->institution_id;
                        $activity->branch_id = $record->branch_id;
                    })
                    ->log('Notice updated: ' . $record->title);
            } else {
                $data['institution_id'] = $institutionId;
                $data['branch_id']      = $branchId;
                $data['session_id']     = $sessionId;

                $record = Notice::create($data);

                activity()
                    ->causedBy(auth()->user())
                    ->performedOn($record)
                    ->withProperties(['icon' => 'campaign', 'type' => 'notice'])
                    ->tap(function ($activity) use ($record) {
                        $activity->institution_id = $record->institution_id;
                        $activity->branch_id = $record->branch_id;
                    })
                    ->log('New notice created: ' . $record->title);
            }

            DB::commit();

            if ($oldAttachmentToDelete) {
                Storage::disk('public')->delete($oldAttachmentToDelete);
            }

            // Notification এবং SMS commit এর পরে পাঠানো হচ্ছে, এবং শুধু নতুন
            // Notice তৈরির সময় (Edit করলে বারবার পাঠানো ঠিক না)।
            if ($isNew) {
                $targetUsers = $this->getTargetUsers($this->audience, $institutionId, $branchId);

                $notificationMessage = \Str::limit(strip_tags($this->description), 150);

                if ($this->audience === 'all') {
                    NotificationService::sendToAll(
                        $institutionId,
                        'announcement',
                        $this->title,
                        $notificationMessage,
                        ['icon' => 'campaign'],
                        $this->priority === 'urgent' ? 'high' : 'normal'
                    );
                } else {
                    NotificationService::sendToRole(
                        $institutionId,
                        $this->audience,
                        'announcement',
                        $this->title,
                        $notificationMessage,
                        ['icon' => 'campaign'],
                        $this->priority === 'urgent' ? 'high' : 'normal'
                    );
                }

                if (setting('sms_enabled') == '1' && $this->sendSms) {
                    $this->sendSmsToUsers($targetUsers, $this->title, $notificationMessage);
                }
            }

            $this->dispatch('toast', type: 'success', message: $isNew ? 'Data created successfully!' : 'Data updated successfully!');

            $this->showModal = false;
            $this->resetForm();

        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatch('toast', type: 'error', message: 'Something went wrong!');
            report($e);
        }
    }

    private function getTargetUsers(string $audience, int $institutionId, ?int $branchId)
    {
        $query = User::where('institution_id', $institutionId)
            ->where('branch_id', $branchId);

        if ($audience !== 'all') {
            $query->where('role', $audience);
        }

        return $query->whereNotNull('phone')->where('phone', '!=', '')->get();
    }

    private function sendSmsToUsers($users, string $title, string $message): void
    {
        if ($users->isEmpty()) {
            return;
        }

        $sms = new SMS();
        $smsText = "{$title}: {$message}";

        foreach ($users as $user) {
            try {
                $sms->sendSMS($smsText, $user->phone);
            } catch (\Throwable $e) {
                continue;
            }
        }
    }

    public function confirmDeleteRecord(int $id): void
    {
        $this->deleteId = $id;
        $this->confirmDelete = true;
    }

    public function deleteRecord(): void
    {
        $institutionId = institution()->id;
        $branchId      = $this->activeBranchId();

        DB::beginTransaction();

        try {
            $record = Notice::where('institution_id', $institutionId)
                ->where('branch_id', $branchId)
                ->where('session_id', $this->currentSessionId)
                ->findOrFail($this->deleteId);

            $attachmentPath = $record->attachment;

            activity()
                ->causedBy(auth()->user())
                ->performedOn($record)
                ->withProperties(['icon' => 'campaign', 'type' => 'notice'])
                ->tap(function ($activity) use ($record) {
                    $activity->institution_id = $record->institution_id;
                    $activity->branch_id = $record->branch_id;
                })
                ->log('Notice deleted: ' . $record->title);

            $record->delete();

            DB::commit();

            if ($attachmentPath) {
                Storage::disk('public')->delete($attachmentPath);
            }

            $this->confirmDelete = false;
            $this->deleteId = null;

            $this->dispatch('toast', type: 'success', message: 'Data deleted successfully!');

        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatch('toast', type: 'error', message: 'Something went wrong!');
            report($e);
        }
    }

    public function toggleStatus(int $id): void
    {
        $institutionId = institution()->id;
        $branchId      = $this->activeBranchId();

        DB::beginTransaction();

        try {
            $record = Notice::where('institution_id', $institutionId)
                ->where('branch_id', $branchId)
                ->where('session_id', $this->currentSessionId)
                ->findOrFail($id);

            $newStatus = $record->status === 'active' ? 'inactive' : 'active';
            $record->update(['status' => $newStatus]);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($record)
                ->withProperties(['icon' => 'campaign', 'type' => 'notice'])
                ->tap(function ($activity) use ($record) {
                    $activity->institution_id = $record->institution_id;
                    $activity->branch_id = $record->branch_id;
                })
                ->log('Notice status changed to ' . $newStatus . ': ' . $record->title);

            DB::commit();

            $this->dispatch('toast', type: 'success', message: 'Data updated successfully!');

        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatch('toast', type: 'error', message: 'Something went wrong!');
            report($e);
        }
    }

    public function removeAttachment(): void
    {
        if (! $this->editId || ! $this->existingAttachment) {
            return;
        }

        $institutionId = institution()->id;
        $branchId      = $this->activeBranchId();

        DB::beginTransaction();

        try {
            $record = Notice::where('institution_id', $institutionId)
                ->where('branch_id', $branchId)
                ->where('session_id', $this->currentSessionId)
                ->findOrFail($this->editId);

            $attachmentPath = $record->attachment;

            $record->update([
                'attachment' => null,
                'attachment_name' => null,
            ]);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($record)
                ->withProperties(['icon' => 'campaign', 'type' => 'notice'])
                ->tap(function ($activity) use ($record) {
                    $activity->institution_id = $record->institution_id;
                    $activity->branch_id = $record->branch_id;
                })
                ->log('Attachment removed from notice: ' . $record->title);

            DB::commit();

            if ($attachmentPath) {
                Storage::disk('public')->delete($attachmentPath);
            }

            $this->existingAttachment = '';
            $this->existingAttachmentName = '';

            $this->dispatch('toast', type: 'success', message: 'Data removed successfully!');

        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatch('toast', type: 'error', message: 'Something went wrong!');
            report($e);
        }
    }

    private function resetForm(): void
    {
        $this->reset([
            'title', 'description', 'expires_at', 'attachment',
            'existingAttachment', 'existingAttachmentName', 'editId',
        ]);
        $this->audience = 'all';
        $this->priority = 'medium';
        $this->status = 'active';
        $this->published_at = today()->toDateString();
        $this->sendSms = false;
        $this->resetValidation();
    }

    public function render()
    {
        $institutionId = institution()->id;
        $branchId      = $this->activeBranchId();

        $notices = Notice::with('creator')
            ->where('institution_id', $institutionId)
            ->where('branch_id', $branchId)
            ->where('session_id', $this->currentSessionId)
            ->when($this->search, fn ($q) =>
                $q->where(fn ($q2) =>
                    $q2->where('title', 'like', "%{$this->search}%")
                       ->orWhere('description', 'like', "%{$this->search}%")
                )
            )
            ->when($this->filterAudience, fn ($q) => $q->where('audience', $this->filterAudience))
            ->when($this->filterPriority, fn ($q) => $q->where('priority', $this->filterPriority))
            ->when($this->filterStatus, fn ($q) => $q->where('status', $this->filterStatus))
            ->latest()
            ->paginate($this->perPage);

        return view('livewire.admin.notice.notice-component')
            ->with('notices', $notices)
            ->layout('layouts.admin.app', [
                'title' => 'Notice Board | ' . institution()->name,
            ]);
    }
}