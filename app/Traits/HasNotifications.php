<?php

namespace App\Traits;

use App\Models\Notification;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasNotifications
{
    public function notifications(): MorphMany
    {
        return $this->morphMany(Notification::class, 'notifiable')
            ->forSchool($this->school_id)
            ->latest();
    }

    public function unreadNotifications(): MorphMany
    {
        return $this->morphMany(Notification::class, 'notifiable')
            ->forSchool($this->school_id)
            ->unread()
            ->latest();
    }

    public function unreadNotificationsCount(): int
    {
        return $this->unreadNotifications()->count();
    }

    public function markAllNotificationsAsRead(): int
    {
        return $this->unreadNotifications()->update(['read_at' => now()]);
    }
}
