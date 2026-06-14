{{-- resources/views/livewire/accountant/mailbox/partials/sidebar.blade.php --}}
<div class="mailbox-sidebar">
    <a href="{{ route('accountant.mailbox.compose') }}" class="bg-dark text-white compose-btn {{ request()->routeIs('accountant.mailbox.compose') ? 'active' : '' }}">
        <i class="fas fa-pen"></i>
        <span>Compose</span>
    </a>

    <nav class="sidebar-nav">
        <a href="{{ route('accountant.mailbox.inbox') }}"
           class="nav-item {{ request()->routeIs('accountant.mailbox.inbox') ? 'active' : '' }}">
            <i class="fas fa-inbox"></i>
            <span>Inbox</span>
            @php
                $unread = \App\Models\Message::inbox(auth()->id())->where('is_read', false)->count();
            @endphp
            @if($unread > 0)
                <span class="badge badge-primary">{{ $unread }}</span>
            @endif
        </a>

        <a href="{{ route('accountant.mailbox.sent') }}"
           class="nav-item {{ request()->routeIs('accountant.mailbox.sent') ? 'active' : '' }}">
            <i class="fas fa-paper-plane"></i>
            <span>Sent</span>
        </a>

        <a href="{{ route('accountant.mailbox.important') }}"
           class="nav-item {{ request()->routeIs('accountant.mailbox.important') ? 'active' : '' }}">
            <i class="fas fa-star"></i>
            <span>Important</span>
            @php
                $importantCount = \App\Models\Message::important(auth()->id())->count();
            @endphp
            @if($importantCount > 0)
                <span class="badge badge-warning">{{ $importantCount }}</span>
            @endif
        </a>

        <a href="{{ route('accountant.mailbox.trash') }}"
           class="nav-item {{ request()->routeIs('accountant.mailbox.trash') ? 'active' : '' }}">
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
