{{-- resources/views/livewire/admin/mailbox/partials/sidebar.blade.php --}}
@php
    $unreadCount    = \App\Models\Message::inbox(auth()->id())->where('is_read', false)->count();
    $importantCount = \App\Models\Message::important(auth()->id())->count();
    $trashCount     = \App\Models\Message::trash(auth()->id())->count();
@endphp

<div class="mailbox-sidebar">
    <a href="{{ route($routePrefix . 'mailbox.compose') }}"
       class="compose-btn {{ request()->routeIs($routePrefix . 'mailbox.compose') ? 'active' : '' }}">
        <i class="fas fa-pen"></i>
        <span>Compose</span>
    </a>

    <nav class="sidebar-nav">
        <a href="{{ route($routePrefix . 'mailbox.inbox') }}"
           class="nav-item {{ request()->routeIs($routePrefix . 'mailbox.inbox') ? 'active' : '' }}">
            <i class="fas fa-inbox"></i>
            <span>Inbox</span>
            @if($unreadCount > 0)
                <span class="badge bg-primary">{{ $unreadCount }}</span>
            @endif
        </a>

        <a href="{{ route($routePrefix . 'mailbox.sent') }}"
           class="nav-item {{ request()->routeIs($routePrefix . 'mailbox.sent') ? 'active' : '' }}">
            <i class="fas fa-paper-plane"></i>
            <span>Sent</span>
        </a>

        <a href="{{ route($routePrefix . 'mailbox.important') }}"
           class="nav-item {{ request()->routeIs($routePrefix . 'mailbox.important') ? 'active' : '' }}">
            <i class="fas fa-star"></i>
            <span>Important</span>
            @if($importantCount > 0)
                <span class="badge bg-warning text-dark">{{ $importantCount }}</span>
            @endif
        </a>

        <a href="{{ route($routePrefix . 'mailbox.trash') }}"
           class="nav-item {{ request()->routeIs($routePrefix . 'mailbox.trash') ? 'active' : '' }}">
            <i class="fas fa-trash"></i>
            <span>Trash</span>
            @if($trashCount > 0)
                <span class="badge bg-danger">{{ $trashCount }}</span>
            @endif
        </a>
    </nav>
</div>