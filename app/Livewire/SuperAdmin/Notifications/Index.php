<?php

namespace App\Livewire\SuperAdmin\Notifications;

use App\Models\Notification;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $filter   = 'all';
    public string $type     = '';
    public string $search   = '';
    public string $priority = '';

    protected $queryString = [
        'filter'   => ['except' => 'all'],
        'type'     => ['except' => ''],
        'search'   => ['except' => ''],
        'priority' => ['except' => ''],
    ];

    public function updatingFilter(): void   { $this->resetPage(); }
    public function updatingType(): void     { $this->resetPage(); }
    public function updatingSearch(): void   { $this->resetPage(); }
    public function updatingPriority(): void { $this->resetPage(); }

    // ─── Actions ──────────────────────────────────────────────────────────────

    public function markAsRead(int $id): void
    {
        Notification::whereKey($id)
            ->where('notifiable_id', auth()->id())
            ->where('notifiable_type', auth()->user()::class)
            ->first()
            ?->markAsRead();
    }

    public function markAllAsRead(): void
    {
        auth()->user()->markAllNotificationsAsRead();
    }

    public function delete(int $id): void
    {
        Notification::whereKey($id)
            ->where('notifiable_id', auth()->id())
            ->where('notifiable_type', auth()->user()::class)
            ->delete();
    }

    public function clearAll(): void
    {
        auth()->user()->notifications()->delete();
    }

    // ─── Render ───────────────────────────────────────────────────────────────

    public function render()
    {
        $query = auth()->user()->notifications();

        if ($this->filter === 'unread') {
            $query->unread();
        } elseif ($this->filter === 'read') {
            $query->read();
        }

        if ($this->type) {
            $query->byType($this->type);
        }

        if ($this->priority) {
            $query->where('priority', $this->priority);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('title', 'like', "%{$this->search}%")
                  ->orWhere('message', 'like', "%{$this->search}%");
            });
        }

        return view('livewire.notifications.index', [
            'notifications' => $query->paginate(20),
            'unreadCount'   => auth()->user()->unreadNotificationsCount(),
            'typeLabels'    => Notification::typeLabels(),
        ])->layout('layouts.superadmin.app', [
                'title' => 'Notifications | ' . setting('app_name', 'EMS'),
            ]);
    }
}
