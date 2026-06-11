<?php

namespace App\Livewire;

use App\Models\Notification;
use Illuminate\Support\Collection;
use Livewire\Component;

class NotificationBell extends Component
{
    public int $limit = 10;

    // ─── Computed Properties ──────────────────────────────────────────────────

    public function getUnreadCountProperty(): int
    {
        return auth()->user()->unreadNotificationsCount();
    }

    public function getNotificationsProperty(): Collection
    {
        return auth()->user()->notifications()->take($this->limit)->get();
    }

    // ─── Actions ──────────────────────────────────────────────────────────────

    public function markAsRead(int $id): void
    {
        // BelongsToSchool SchoolScope automatically school filter করবে
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

    public function loadMore(): void
    {
        $this->limit += 10;
    }

    // ─── Render ───────────────────────────────────────────────────────────────

    public function render()
    {
        return view('livewire.notification-bell', [
            'notifications' => $this->notifications,
            'unreadCount'   => $this->unreadCount,
        ]);
    }
}
