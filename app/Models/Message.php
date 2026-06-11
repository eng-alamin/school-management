<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_read'                 => 'boolean',
        'is_important'            => 'boolean',
        'is_trashed_by_sender'    => 'boolean',
        'is_trashed_by_receiver'  => 'boolean',
        'is_deleted_by_sender'    => 'boolean',
        'is_deleted_by_receiver'  => 'boolean',
        'read_at'                 => 'datetime',
    ];

    /* ------------------------------------------------------------------ */
    /*  Relationships                                                        */
    /* ------------------------------------------------------------------ */

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    /* ------------------------------------------------------------------ */
    /*  Scopes                                                              */
    /* ------------------------------------------------------------------ */

    /** Inbox: messages received by $userId, not trashed/deleted by receiver */
    public function scopeInbox($query, int $userId)
    {
        return $query->where('receiver_id', $userId)
                     ->where('is_trashed_by_receiver', false)
                     ->where('is_deleted_by_receiver', false);
    }

    /** Sent: messages sent by $userId, not trashed/deleted by sender */
    public function scopeSent($query, int $userId)
    {
        return $query->where('sender_id', $userId)
                     ->where('is_trashed_by_sender', false)
                     ->where('is_deleted_by_sender', false);
    }

    /** Important: starred messages visible to $userId */
    public function scopeImportant($query, int $userId)
    {
        return $query->where('is_important', true)
                     ->where(function ($q) use ($userId) {
                         $q->where(function ($q2) use ($userId) {
                             $q2->where('receiver_id', $userId)
                                ->where('is_deleted_by_receiver', false);
                         })->orWhere(function ($q2) use ($userId) {
                             $q2->where('sender_id', $userId)
                                ->where('is_deleted_by_sender', false);
                         });
                     });
    }

    /** Trash: messages trashed by $userId (sender or receiver) */
    public function scopeTrash($query, int $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('receiver_id', $userId)
              ->where('is_trashed_by_receiver', true)
              ->where('is_deleted_by_receiver', false);
        })->orWhere(function ($q) use ($userId) {
            $q->where('sender_id', $userId)
              ->where('is_trashed_by_sender', true)
              ->where('is_deleted_by_sender', false);
        });
    }

    /* ------------------------------------------------------------------ */
    /*  Helpers                                                             */
    /* ------------------------------------------------------------------ */

    public function markAsRead(): void
    {
        if (! $this->is_read) {
            $this->update(['is_read' => true, 'read_at' => now()]);
        }
    }

    public function getExcerptAttribute(): string
    {
        return \Str::limit(strip_tags($this->body), 80);
    }
}
