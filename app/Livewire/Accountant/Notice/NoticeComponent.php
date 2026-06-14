<?php

namespace App\Livewire\Accountant\Notice;

use App\Models\Notice;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use App\Services\NotificationService;

class NoticeComponent extends Component
{
    use WithPagination, WithFileUploads;

    protected string $paginationTheme = 'bootstrap';

    // List
    public string $search          = '';
    public string $filterAudience  = '';
    public string $filterPriority  = '';
    public string $filterStatus    = '';
    public int    $perPage         = 10;

    // Modal
    public bool      $showModal     = false;
    public bool      $showViewModal = false;
    public bool      $confirmDelete = false;
    public ?int      $deleteId      = null;
    public ?Notice   $viewRecord    = null;

    // Form
    public ?int    $editId                  = null;
    public string  $title                   = '';
    public string  $description             = '';
    public string  $audience                = 'all';
    public string  $priority                = 'medium';
    public string  $status                  = 'active';
    public string  $published_at            = '';
    public string  $expires_at              = '';
    public         $attachment              = null;
    public string  $existingAttachment      = '';
    public string  $existingAttachmentName  = '';

    protected function rules(): array
    {
        return [
            'title'        => 'required|min:3|max:255',
            'description'  => 'required|min:10',
            'audience'     => 'required|in:all,accountant,teacher,student',
            'priority'     => 'required|in:low,medium,high,urgent',
            'status'       => 'required|in:active,inactive',
            'published_at' => 'required|date',
            'expires_at'   => 'nullable|date|after:published_at',
            'attachment'   => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
        ];
    }

    public function mount(): void
    {
        $this->published_at = today()->toDateString();
    }

    public function updatingSearch(): void         { $this->resetPage(); }
    public function updatingFilterAudience(): void { $this->resetPage(); }
    public function updatingFilterPriority(): void { $this->resetPage(); }
    public function updatingFilterStatus(): void   { $this->resetPage(); }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->editId    = null;
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $record = Notice::findOrFail($id);
        $this->editId                 = $id;
        $this->title                  = $record->title;
        $this->description            = $record->description;
        $this->audience               = $record->audience;
        $this->priority               = $record->priority;
        $this->status                 = $record->status;
        $this->published_at           = $record->published_at->toDateString();
        $this->expires_at             = $record->expires_at?->toDateString() ?? '';
        $this->existingAttachment     = $record->attachment ?? '';
        $this->existingAttachmentName = $record->attachment_name ?? '';
        $this->attachment             = null;
        $this->showModal              = true;
    }

    public function openView(int $id): void
    {
        $this->viewRecord    = Notice::with('creator')->findOrFail($id);
        $this->showViewModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $attachmentPath = $this->existingAttachment;
        $attachmentName = $this->existingAttachmentName;

        if ($this->attachment) {
            if ($attachmentPath) {
                Storage::disk('public')->delete($attachmentPath);
            }
            $attachmentPath = $this->attachment->store('notices', 'public');
            $attachmentName = $this->attachment->getClientOriginalName();
        }

        $data = [
            'created_by'      => auth()->id(),
            'title'           => $this->title,
            'description'     => $this->description,
            'audience'        => $this->audience,
            'priority'        => $this->priority,
            'status'          => $this->status,
            'published_at'    => $this->published_at,
            'expires_at'      => $this->expires_at ?: null,
            'attachment'      => $attachmentPath ?: null,
            'attachment_name' => $attachmentName ?: null,
        ];

        if ($this->editId) {
            $record = Notice::findOrFail($this->editId);
            $record->update($data);

            // ── Activity Log ───────────────────────────────────────
            activity()
                ->causedBy(auth()->user())
                ->performedOn($record)
                ->withProperties(['icon' => 'campaign', 'type' => 'notice'])
                ->log('Notice updated: ' . $record->title);

            $this->dispatch('toast', type: 'success', message: 'Data updated successfully!');
        } else {
            $record = Notice::create($data);

            NotificationService::sendToAll(auth()->user()->school_id, 'announcement', 'Notice', $this->title, [], 'high');

            // ── Activity Log ───────────────────────────────────────
            activity()
                ->causedBy(auth()->user())
                ->performedOn($record)
                ->withProperties(['icon' => 'campaign', 'type' => 'notice'])
                ->log('New notice created: ' . $record->title);

            $this->dispatch('toast', type: 'success', message: 'Data created successfully!');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function confirmDeleteRecord(int $id): void
    {
        $this->deleteId      = $id;
        $this->confirmDelete = true;
    }

    public function deleteRecord(): void
    {
        $record = Notice::findOrFail($this->deleteId);

        // ── Activity Log ───────────────────────────────────────────
        activity()
            ->causedBy(auth()->user())
            ->performedOn($record)
            ->withProperties(['icon' => 'campaign', 'type' => 'notice'])
            ->log('Notice deleted: ' . $record->title);

        if ($record->attachment) {
            Storage::disk('public')->delete($record->attachment);
        }
        $record->delete();
        $this->confirmDelete = false;
        $this->deleteId      = null;
        $this->dispatch('toast', type: 'success', message: 'Data deleted successfully!');
    }

    public function toggleStatus(int $id): void
    {
        $record = Notice::findOrFail($id);
        $newStatus = $record->status === 'active' ? 'inactive' : 'active';
        $record->update(['status' => $newStatus]);

        // ── Activity Log ───────────────────────────────────────────
        activity()
            ->causedBy(auth()->user())
            ->performedOn($record)
            ->withProperties(['icon' => 'campaign', 'type' => 'notice'])
            ->log('Notice status changed to ' . $newStatus . ': ' . $record->title);

        $this->dispatch('toast', type: 'success', message: 'Data updated successfully!');
    }

    public function removeAttachment(): void
    {
        if ($this->editId && $this->existingAttachment) {
            Storage::disk('public')->delete($this->existingAttachment);
            $record = Notice::findOrFail($this->editId);
            $record->update([
                'attachment'      => null,
                'attachment_name' => null,
            ]);

            // ── Activity Log ───────────────────────────────────────
            activity()
                ->causedBy(auth()->user())
                ->performedOn($record)
                ->withProperties(['icon' => 'campaign', 'type' => 'notice'])
                ->log('Attachment removed from notice: ' . $record->title);

            $this->existingAttachment     = '';
            $this->existingAttachmentName = '';
            $this->dispatch('toast', type: 'success', message: 'Data removed successfully!');
        }
    }

    private function resetForm(): void
    {
        $this->reset([
            'title', 'description', 'expires_at', 'attachment',
            'existingAttachment', 'existingAttachmentName', 'editId',
        ]);
        $this->audience     = 'all';
        $this->priority     = 'medium';
        $this->status       = 'active';
        $this->published_at = today()->toDateString();
        $this->resetValidation();
    }

    public function render()
    {
        $notices = Notice::with('creator')
            ->when($this->search, fn ($q) =>
                $q->where(fn ($q2) =>
                    $q2->where('title', 'like', "%{$this->search}%")
                       ->orWhere('description', 'like', "%{$this->search}%")
                )
            )
            ->when($this->filterAudience, fn ($q) => $q->where('audience', $this->filterAudience))
            ->when($this->filterPriority, fn ($q) => $q->where('priority', $this->filterPriority))
            ->when($this->filterStatus,   fn ($q) => $q->where('status', $this->filterStatus))
            ->latest()
            ->paginate($this->perPage);

        return view('livewire.accountant.notice.notice-component')
            ->with('notices', $notices)
            ->layout('layouts.accountant.app', [
                'title' => "Notice Board | School SaaS",
            ]);
    }
}