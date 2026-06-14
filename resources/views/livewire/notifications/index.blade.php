{{-- resources/views/livewire/notifications/index.blade.php --}}
<div>

    <div class="card">

        {{-- Floating Header --}}
        <div class="mat-card-header header-pink-gradient">
            <h5 id="cardHeaderTitleAllsections">Notifications</h5>
            <p id="cardHeaderSubtitle">Manage and review all your notifications in one place.</p>
        </div>

        <div class="card-header border-0">
            <div class="card-toolbar">

                {{-- Left: Search --}}
                <div class="card-toolbar-title">
                    <div style="position:relative;display:inline-flex;align-items:center">
                        <span class="material-icons-round" style="position:absolute;left:10px;font-size:17px;color:var(--muted);pointer-events:none">search</span>
                        <input type="text"
                               wire:model.live.debounce.300ms="search"
                               id="tableSearch"
                               placeholder="Search title or message..."
                               style="border:1px solid rgba(0,0,0,.1);border-radius:8px;padding:7px 12px 7px 32px;font-size:.78rem;font-family:inherit;color:var(--dark);outline:none;background:#f8f9fa;width:220px"/>
                    </div>
                </div>

                {{-- Filter: Read Status --}}
                <div class="col-md-2">
                    <select class="form-select form-select-sm" wire:model.live="filter">
                        <option value="all">All</option>
                        <option value="unread">Unread</option>
                        <option value="read">Read</option>
                    </select>
                </div>

                {{-- Filter: Type --}}
                <div class="col-md-2">
                    <select class="form-select form-select-sm" wire:model.live="type">
                        <option value="">All Types</option>
                        @foreach($typeLabels as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Filter: Priority --}}
                <div class="col-md-2">
                    <select class="form-select form-select-sm" wire:model.live="priority">
                        <option value="">All Priority</option>
                        <option value="high">High</option>
                        <option value="normal">Normal</option>
                        <option value="low">Low</option>
                    </select>
                </div>

                {{-- Reset --}}
                <button class="btn-outline bg-dark text-white"
                        wire:click="$set('search',''); $set('filter','all'); $set('type',''); $set('priority','')"
                        title="Reset filters">
                    <span class="material-icons-round">refresh</span>
                </button>

                {{-- Mark All Read --}}
                @if($unreadCount > 0)
                    <button class="btn-outline bg-dark text-white" wire:click="markAllAsRead">
                        <span class="material-icons-round">done_all</span>
                        <span>Mark All Read</span>
                    </button>
                @endif

                {{-- Clear All --}}
                <button class="btn-outline bg-dark text-white"
                        wire:click="clearAll"
                        wire:confirm="Delete all notifications? This action cannot be undone.">
                    <span class="material-icons-round">delete_sweep</span>
                    <span>Clear All</span>
                </button>

            </div>
        </div>

        {{-- Unread count info --}}
        <div class="px-3 pb-2 pt-0">
            @if($unreadCount > 0)
                <small class="text-muted">
                    You have <span class="badge bg-danger">{{ $unreadCount }}</span> unread notification(s).
                </small>
            @else
                <small class="text-muted">All notifications have been read ✓</small>
            @endif
        </div>

        {{-- Notification List --}}
        <div class="card-body pt-0 p-0">
            <div class="table-responsive">

                @forelse($notifications as $notif)
                    <div class="notif-list-item {{ $notif->isUnread() ? 'notif-list-unread' : '' }}"
                         wire:key="idx-{{ $notif->id }}">

                        {{-- Unread dot --}}
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
                        <div class="notif-list-content"
                             wire:click="markAsRead({{ $notif->id }})"
                             style="cursor:pointer; flex:1; min-width:0">
                            <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                                <span class="notif-list-title {{ $notif->isUnread() ? 'fw-semibold' : '' }}">
                                    {{ $notif->title }}
                                </span>
                                @if($notif->priority === 'high')
                                    <span class="badge bg-danger-subtle text-danger" style="font-size:10px">High</span>
                                @endif
                                @if($notif->isUnread())
                                    <span class="badge bg-primary-subtle text-primary" style="font-size:10px">New</span>
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
                                        class="act-btn edit" title="Mark as read">
                                    <span class="material-icons-round">done</span>
                                </button>
                            @endif
                            <button wire:click.stop="delete({{ $notif->id }})"
                                    class="act-btn delete" title="Delete">
                                <span class="material-icons-round">delete</span>
                            </button>
                        </div>

                    </div>
                @empty
                    <div class="text-center py-5 text-muted">
                        <span class="material-icons-round" style="font-size:52px;color:#ccc;display:block;margin-bottom:8px">notifications_none</span>
                        No notifications found.
                    </div>
                @endforelse

            </div>
        </div>

        {{-- Footer / Pagination --}}
        @if($notifications->hasPages())
            <div class="card-footer border-0 bg-white d-flex align-items-center justify-content-between flex-wrap gap-2 py-2 px-3">
                <small class="text-muted">
                    Showing {{ $notifications->firstItem() ?? 0 }}–{{ $notifications->lastItem() ?? 0 }} of {{ $notifications->total() }}
                </small>
                {{ $notifications->links('vendor.pagination.custom') }}
            </div>
        @endif

    </div>

</div>


@push('styles')
<style>
    /* ── Notification List Item ── */
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

    /* Unread dot */
    .notif-list-dot-wrap {
        width: 10px;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .notif-list-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: var(--primary, #e74c3c);
        display: block;
    }

    /* Icon circle */
    .notif-list-icon {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background: rgba(59,130,246,.1);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .notif-list-icon .material-icons-round {
        font-size: 20px;
        color: var(--primary, #2563eb);
    }

    .notif-list-title {
        font-size: .875rem;
        color: var(--dark, #212121);
    }

    .notif-list-message {
        font-size: .8rem;
        color: var(--muted, #616161);
        margin: 0 0 4px;
        line-height: 1.5;
    }

    .notif-list-actions {
        display: flex;
        gap: 6px;
        flex-shrink: 0;
    }

    /* ── Dark Mode ── */
    [data-theme="dark"] .notif-list-item {
        border-color: rgba(255,255,255,0.07);
    }

    [data-theme="dark"] .notif-list-unread {
        background: rgba(25, 118, 210, 0.08);
    }

    [data-theme="dark"] .notif-list-item:hover {
        background: rgba(255,255,255,0.04);
    }

    [data-theme="dark"] .notif-list-icon {
        background: rgba(25, 118, 210, 0.2);
    }
</style>
@endpush