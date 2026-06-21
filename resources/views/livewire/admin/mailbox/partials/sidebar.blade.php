{{-- resources/views/livewire/admin/mailbox/partials/sidebar.blade.php --}}
<div class="mailbox-sidebar">
    <a href="{{ route('admin.mailbox.compose') }}" class="compose-btn {{ request()->routeIs('admin.mailbox.compose') ? 'active' : '' }}">
        <i class="fas fa-pen"></i>
        <span>Compose</span>
    </a>

    <nav class="sidebar-nav">
        <a href="{{ route('admin.mailbox.inbox') }}"
           class="nav-item {{ request()->routeIs('admin.mailbox.inbox') ? 'active' : '' }}">
            <i class="fas fa-inbox"></i>
            <span>Inbox</span>
            @php
                $unread = \App\Models\Message::inbox(auth()->id())->where('is_read', false)->count();
            @endphp
            @if($unread > 0)
                <span class="badge badge-primary">{{ $unread }}</span>
            @endif
        </a>

        <a href="{{ route('admin.mailbox.sent') }}"
           class="nav-item {{ request()->routeIs('admin.mailbox.sent') ? 'active' : '' }}">
            <i class="fas fa-paper-plane"></i>
            <span>Sent</span>
        </a>

        <a href="{{ route('admin.mailbox.important') }}"
           class="nav-item {{ request()->routeIs('admin.mailbox.important') ? 'active' : '' }}">
            <i class="fas fa-star"></i>
            <span>Important</span>
            @php
                $importantCount = \App\Models\Message::important(auth()->id())->count();
            @endphp
            @if($importantCount > 0)
                <span class="badge badge-warning">{{ $importantCount }}</span>
            @endif
        </a>

        <a href="{{ route('admin.mailbox.trash') }}"
           class="nav-item {{ request()->routeIs('admin.mailbox.trash') ? 'active' : '' }}">
            <i class="fas fa-trash"></i>
            <span>Trash</span>
            @php
                $trashCount = \App\Models\Message::trash(auth()->id())->count();
            @endphp
            @if($trashCount > 0)
                <span class="badge badge-danger">{{ $trashCount }}</span>
            @endif
        </a>
    </nav>
</div>
