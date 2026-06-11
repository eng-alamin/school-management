{{-- resources/views/livewire/notifications/index.blade.php --}}

<div class="content-wrapper">

    {{-- Page Header --}}
    <div class="page-header">
        <div>
            <h4 class="page-title">Notifications</h4>
            <p class="text-muted mb-0" style="font-size:13px">
                @if($unreadCount > 0)
                    <span class="badge bg-danger">{{ $unreadCount }}</span> টি অপঠিত notification আছে
                @else
                    সব notification পড়া হয়েছে ✓
                @endif
            </p>
        </div>
        <div class="d-flex gap-2">
            @if($unreadCount > 0)
                <button wire:click="markAllAsRead" class="btn btn-sm btn-outline-primary">
                    <span class="material-icons-round" style="font-size:16px;vertical-align:-3px">done_all</span>
                    সব পড়া চিহ্নিত করুন
                </button>
            @endif
            <button wire:click="clearAll"
                    wire:confirm="সব notifications মুছে ফেলবেন? এটা undo করা যাবে না।"
                    class="btn btn-sm btn-outline-danger">
                <span class="material-icons-round" style="font-size:16px;vertical-align:-3px">delete_sweep</span>
                সব মুছুন
            </button>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card mb-3">
        <div class="card-body py-3">
            <div class="row g-2 align-items-center">

                <div class="col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">
                            <span class="material-icons-round" style="font-size:16px">search</span>
                        </span>
                        <input type="text"
                               wire:model.live.debounce.400ms="search"
                               class="form-control"
                               placeholder="শিরোনাম বা বার্তা খুঁজুন...">
                    </div>
                </div>

                <div class="col-md-2">
                    <select wire:model.live="filter" class="form-select form-select-sm">
                        <option value="all">সব</option>
                        <option value="unread">অপঠিত</option>
                        <option value="read">পঠিত</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <select wire:model.live="type" class="form-select form-select-sm">
                        <option value="">সব ধরন</option>
                        @foreach($typeLabels as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <select wire:model.live="priority" class="form-select form-select-sm">
                        <option value="">সব priority</option>
                        <option value="high">High</option>
                        <option value="normal">Normal</option>
                        <option value="low">Low</option>
                    </select>
                </div>

                <div class="col-md-1">
                    <button wire:click="$set('search',''); $set('filter','all'); $set('type',''); $set('priority','')"
                            class="btn btn-sm btn-outline-secondary w-100" title="Reset filters">
                        <span class="material-icons-round" style="font-size:16px">refresh</span>
                    </button>
                </div>

            </div>
        </div>
    </div>

    {{-- Notification List --}}
    <div class="card">
        <div class="card-body p-0">

            @forelse($notifications as $notif)
                <div class="notif-list-item {{ $notif->isUnread() ? 'notif-list-unread' : '' }}"
                     wire:key="idx-{{ $notif->id }}">

                    {{-- Unread indicator --}}
                    <div class="notif-list-dot-wrap">
                        @if($notif->isUnread())
                            <span class="notif-list-dot"></span>
                        @endif
                    </div>

                    {{-- Icon --}}
                    <div class="notif-list-icon">
                        <span class="material-icons-round">{{ $notif->icon }}</span>
                    </div>

                    {{-- Content --}}
                    <div class="notif-list-content" wire:click="markAsRead({{ $notif->id }})"
                         style="cursor:pointer; flex:1; min-width:0">
                        <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                            <span class="notif-list-title {{ $notif->isUnread() ? 'fw-semibold' : '' }}">
                                {{ $notif->title }}
                            </span>
                            @if($notif->priority === 'high')
                                <span class="badge bg-danger" style="font-size:10px">High</span>
                            @endif
                            @if($notif->isUnread())
                                <span class="badge bg-primary" style="font-size:10px">New</span>
                            @endif
                        </div>
                        <p class="notif-list-message">{{ $notif->message }}</p>
                        <small class="text-muted">
                            {{ $notif->created_at->diffForHumans() }}
                            &nbsp;·&nbsp;
                            {{ $notif->created_at->format('d M Y, h:i A') }}
                        </small>
                    </div>

                    {{-- Actions --}}
                    <div class="notif-list-actions">
                        @if($notif->isUnread())
                            <button wire:click.stop="markAsRead({{ $notif->id }})"
                                    class="btn btn-sm btn-outline-success" title="পড়া হিসেবে চিহ্নিত করুন">
                                <span class="material-icons-round" style="font-size:16px">done</span>
                            </button>
                        @endif
                        <button wire:click.stop="delete({{ $notif->id }})"
                                class="btn btn-sm btn-outline-danger" title="মুছুন">
                            <span class="material-icons-round" style="font-size:16px">delete</span>
                        </button>
                    </div>

                </div>
            @empty
                <div class="text-center py-5">
                    <span class="material-icons-round" style="font-size:52px;color:#ccc">notifications_none</span>
                    <p class="text-muted mt-2 mb-0">কোনো notification পাওয়া যায়নি</p>
                </div>
            @endforelse

        </div>

        @if($notifications->hasPages())
            <div class="card-footer">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>

</div>


@push('styles')
    <style>
        .notif-list-item {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 14px 20px;
    border-bottom: 1px solid var(--border-color, #f0f0f0);
    transition: background 0.15s;
    position: relative;
}

.notif-list-item:hover {
    background: var(--bg-hover, #fafafa);
}

.notif-list-unread {
    background: #fafbff;
}

.unread-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #1976d2;
    flex-shrink: 0;
}

.notif-list-icon {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: #e3f2fd;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.notif-list-icon .material-icons-round {
    font-size: 20px;
    color: #1976d2;
}

.notif-list-title {
    font-size: 14px;
    color: var(--text-primary, #212121);
}

.notif-list-message {
    font-size: 13px;
    color: var(--text-secondary, #616161);
    margin: 0 0 4px;
    line-height: 1.5;
}

.notif-list-actions {
    display: flex;
    gap: 6px;
    flex-shrink: 0;
}

/* ── Dark Mode ───────────────────────────────────────────────── */
[data-theme="dark"] .notif-item,
[data-theme="dark"] .notif-list-item {
    border-color: rgba(255,255,255,0.07);
}

[data-theme="dark"] .notif-unread,
[data-theme="dark"] .notif-list-unread {
    background: rgba(25, 118, 210, 0.08);
}

[data-theme="dark"] .notif-item:hover,
[data-theme="dark"] .notif-list-item:hover {
    background: rgba(255,255,255,0.04);
}

[data-theme="dark"] .notif-icon,
[data-theme="dark"] .notif-list-icon {
    background: rgba(25, 118, 210, 0.2);
}

[data-theme="dark"] .notif-header,
[data-theme="dark"] .notif-footer {
    background: var(--bg-card-dark, #1e1e2d);
    border-color: rgba(255,255,255,0.07);
}
    </style>
@endpush