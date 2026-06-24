{{-- resources/views/livewire/notification-bell.blade.php --}}

<div class="topnav-dropdown-wrap" wire:poll.15000ms="$refresh">

    {{-- Bell Button --}}
    <button class="icon-btn" id="notifBtn" title="Notifications"
            onclick="toggleDropdown('notifDropdown', event)">
        <span class="material-icons-round">notifications</span>
        @if($unreadCount > 0)
            <span class="notif-badge">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
        @endif
    </button>

    {{-- Dropdown --}}
    <div class="topnav-dropdown" id="notifDropdown" style="min-width:360px; max-height:520px; overflow-y:auto;">

        {{-- Header --}}
        <div class="notif-header">
            <h6>
                Notifications
                @if($unreadCount > 0)
                    <span class="notif-badge-count">{{ $unreadCount }} New</span>
                @endif
            </h6>
            @if($unreadCount > 0)
                <button wire:click="markAllAsRead"
                        wire:loading.attr="disabled"
                        class="btn">
                    <span wire:loading.remove wire:target="markAllAsRead">Mark All Read</span>
                    <span wire:loading wire:target="markAllAsRead">
                        <span class="material-icons-round" style="font-size:14px;vertical-align:-3px">refresh</span>
                    </span>
                </button>
            @endif
        </div>

        {{-- List --}}
        @forelse($notifications as $notif)
            <div class="notif-item {{ $notif->isUnread() ? 'notif-unread' : '' }}"
                 wire:key="bell-{{ $notif->id }}">

                <div class="notif-icon">
                    <span class="material-icons-round">{{ $notif->icon }}</span>
                </div>

                <div class="notif-text" style="flex:1; cursor:pointer"
                     wire:click="markAsRead({{ $notif->id }})">
                    <p>
                        @if($notif->isUnread())
                            <strong>{{ $notif->title }}</strong>
                        @else
                            {{ $notif->title }}
                        @endif
                        — {{ Str::limit($notif->message, 55) }}
                    </p>
                    <span>{{ $notif->created_at->diffForHumans() }}</span>
                    @if($notif->priority === 'high')
                        <span class="notif-priority-badge">High</span>
                    @endif
                </div>

                <button wire:click.stop="delete({{ $notif->id }})"
                        class="btn" title="মুছুন">
                    <span class="material-icons-round" style="font-size:16px">close</span>
                </button>
            </div>
        @empty
            <div class="notif-empty">
                <span class="material-icons-round">notifications_none</span>
                <p>কোনো notification নেই</p>
            </div>
        @endforelse

        {{-- Load More --}}
        @if($notifications->count() >= $limit)
            <div style="text-align:center; padding:8px 0">
                <button wire:click="loadMore" class="notif-load-more-btn">
                    <span wire:loading.remove wire:target="loadMore">আরো দেখুন</span>
                    <span wire:loading wire:target="loadMore">লোড হচ্ছে...</span>
                </button>
            </div>
        @endif

        {{-- Footer --}}
        <div class="notif-footer">
            <a href="{{ route('admin.notifications.index') }}">View all notifications →</a>
        </div>

    </div>
</div>
