{{-- resources/views/livewire/notification-bell.blade.php --}}

<div class="topnav-dropdown-wrap" wire:poll.5000ms="$refresh">

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
                    <span class="notif-badge-count ms-2">{{ $unreadCount }} New</span>
                @endif
            </h6>
            @if($unreadCount > 0)
                <button wire:click="markAllAsRead"
                        wire:loading.attr="disabled"
                        class="btn btn-sm btn-link p-0 text-muted"
                        style="font-size:12px; text-decoration:none;">
                    <span wire:loading.remove wire:target="markAllAsRead">সব পড়া হিসেবে চিহ্নিত করুন</span>
                    <span wire:loading wire:target="markAllAsRead">
                        <span class="material-icons-round" style="font-size:14px; vertical-align:-3px;">refresh</span>
                    </span>
                </button>
            @endif
        </div>

        {{-- Notification List --}}
        @forelse($notifications as $notif)
            <div class="notif-item {{ $notif->isUnread() ? 'notif-unread' : '' }}"
                 wire:key="notif-{{ $notif->id }}">

                {{-- Icon --}}
                <div class="notif-icon {{ $notif->isUnread() ? 'notif-icon-unread' : '' }}">
                    <span class="material-icons-round">{{ $notif->icon }}</span>
                </div>

                {{-- Content --}}
                <div class="notif-text" style="flex:1; cursor:pointer;"
                     wire:click="markAsRead({{ $notif->id }})">
                    <p>
                        @if($notif->isUnread())
                            <strong>{{ $notif->title }}</strong>
                        @else
                            {{ $notif->title }}
                        @endif
                        — {{ Str::limit($notif->message, 60) }}
                    </p>
                    <span>{{ $notif->created_at->diffForHumans() }}</span>

                    {{-- Priority badge --}}
                    @if($notif->priority === 'high')
                        <span class="badge badge-danger ms-1" style="font-size:10px;">High</span>
                    @endif
                </div>

                {{-- Delete button --}}
                <button wire:click.stop="deleteNotification({{ $notif->id }})"
                        class="btn btn-sm btn-link p-0 text-muted ms-2"
                        title="Delete">
                    <span class="material-icons-round" style="font-size:16px;">close</span>
                </button>
            </div>
        @empty
            <div class="notif-empty">
                <span class="material-icons-round" style="font-size:36px; color:#ccc;">notifications_none</span>
                <p class="text-muted mt-2 mb-0" style="font-size:13px;">কোনো notification নেই</p>
            </div>
        @endforelse

        {{-- Load More --}}
        @if($notifications->count() >= $limit)
            <div style="text-align:center; padding:8px;">
                <button wire:click="loadMore" class="btn btn-sm btn-outline-secondary" style="font-size:12px;">
                    <span wire:loading.remove wire:target="loadMore">আরো দেখুন</span>
                    <span wire:loading wire:target="loadMore">লোড হচ্ছে...</span>
                </button>
            </div>
        @endif

        {{-- Footer --}}
        <div class="notif-footer">
            <a href="{{ route('accountant.notifications.index') }}">সব notifications দেখুন →</a>
        </div>
    </div>

</div>
