{{-- resources/views/livewire/student/mailbox/partials/sidebar.blade.php --}}
@php
    $unreadCount    = \App\Models\Message::inbox(auth()->id())->where('is_read', false)->count();
    $importantCount = \App\Models\Message::important(auth()->id())->count();
    $trashCount     = \App\Models\Message::trash(auth()->id())->count();
@endphp

<div class="mailbox-sidebar">
    <a href="{{ route('student.mailbox.compose') }}"
       class="compose-btn {{ request()->routeIs('student.mailbox.compose') ? 'active' : '' }}">
        <i class="fas fa-pen"></i>
        <span>Compose</span>
    </a>

    <nav class="sidebar-nav">
        <a href="{{ route('student.mailbox.inbox') }}"
           class="nav-item {{ request()->routeIs('student.mailbox.inbox') ? 'active' : '' }}">
            <i class="fas fa-inbox"></i>
            <span>Inbox</span>
            @if($unreadCount > 0)
                <span class="badge bg-primary">{{ $unreadCount }}</span>
            @endif
        </a>

        <a href="{{ route('student.mailbox.sent') }}"
           class="nav-item {{ request()->routeIs('student.mailbox.sent') ? 'active' : '' }}">
            <i class="fas fa-paper-plane"></i>
            <span>Sent</span>
        </a>

        <a href="{{ route('student.mailbox.important') }}"
           class="nav-item {{ request()->routeIs('student.mailbox.important') ? 'active' : '' }}">
            <i class="fas fa-star"></i>
            <span>Important</span>
            @if($importantCount > 0)
                <span class="badge bg-warning text-dark">{{ $importantCount }}</span>
            @endif
        </a>

        <a href="{{ route('student.mailbox.trash') }}"
           class="nav-item {{ request()->routeIs('student.mailbox.trash') ? 'active' : '' }}">
            <i class="fas fa-trash"></i>
            <span>Trash</span>
            @if($trashCount > 0)
                <span class="badge bg-danger">{{ $trashCount }}</span>
            @endif
        </a>
    </nav>
</div>