{{-- resources/views/livewire/notifications/index.blade.php --}}

<div class="content-wrapper">

    {{-- Page Header --}}
    <div class="page-header">
        <div>
            <h4 class="page-title" data-en="Notifications" data-bn="নোটিফিকেশন">Notifications</h4>
            <p class="page-subtitle text-muted">
                @if($unreadCount > 0)
                    <span class="badge bg-danger">{{ $unreadCount }}</span> টি অপঠিত notification
                @else
                    সব notification পড়া হয়েছে
                @endif
            </p>
        </div>
        <div class="page-header-actions">
            @if($unreadCount > 0)
                <button wire:click="markAllAsRead" class="btn btn-outline-primary btn-sm">
                    <span class="material-icons-round">done_all</span> সব পড়া হিসেবে চিহ্নিত করুন
                </button>
            @endif
            <button wire:click="clearAll"
                    wire:confirm="সব notifications মুছে ফেলবেন?"
                    class="btn btn-outline-danger btn-sm">
                <span class="material-icons-round">delete_sweep</span> সব মুছুন
            </button>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card mb-4">
        <div class="card-body py-3">
            <div class="row g-3 align-items-end">

                {{-- Search --}}
                <div class="col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><span class="material-icons-round" style="font-size:16px">search</span></span>
                        <input type="text" wire:model.live.debounce.400ms="search"
                               class="form-control" placeholder="Search notifications...">
                    </div>
                </div>

                {{-- Status Filter --}}
                <div class="col-md-2">
                    <select wire:model.live="filter" class="form-select form-select-sm">
                        <option value="all">সব</option>
                        <option value="unread">অপঠিত</option>
                        <option value="read">পঠিত</option>
                    </select>
                </div>

                {{-- Type Filter --}}
                <div class="col-md-3">
                    <select wire:model.live="type" class="form-select form-select-sm">
                        <option value="">সব ধরন</option>
                        @foreach($typeLabels as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Priority Filter --}}
                <div class="col-md-2">
                    <select wire:model.live="priority" class="form-select form-select-sm">
                        <option value="">সব priority</option>
                        <option value="high">High</option>
                        <option value="normal">Normal</option>
                        <option value="low">Low</option>
                    </select>
                </div>

                {{-- Reset --}}
                <div class="col-md-1">
                    <button wire:click="$set('search', ''); $set('filter', 'all'); $set('type', ''); $set('priority', '')"
                            class="btn btn-outline-secondary btn-sm w-100" title="Reset">
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
                     wire:key="n-{{ $notif->id }}">

                    {{-- Unread dot --}}
                    @if($notif->isUnread())
                        <div class="unread-dot"></div>
                    @else
                        <div style="width:8px; flex-shrink:0;"></div>
                    @endif

                    {{-- Icon --}}
                    <div class="notif-list-icon">
                        <span class="material-icons-round">{{ $notif->icon }}</span>
                    </div>

                    {{-- Content --}}
                    <div class="notif-list-content" wire:click="markAsRead({{ $notif->id }})"
                         style="cursor:pointer; flex:1;">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <strong class="notif-list-title">{{ $notif->title }}</strong>
                            @if($notif->priority === 'high')
                                <span class="badge bg-danger" style="font-size:10px;">High</span>
                            @endif
                            @if($notif->isUnread())
                                <span class="badge bg-primary" style="font-size:10px;">New</span>
                            @endif
                        </div>
                        <p class="notif-list-message">{{ $notif->message }}</p>
                        <small class="text-muted">{{ $notif->created_at->diffForHumans() }} · {{ $notif->created_at->format('d M Y, h:i A') }}</small>
                    </div>

                    {{-- Actions --}}
                    <div class="notif-list-actions">
                        @if($notif->isUnread())
                            <button wire:click.stop="markAsRead({{ $notif->id }})"
                                    class="btn btn-sm btn-outline-success" title="পড়া হিসেবে চিহ্নিত করুন">
                                <span class="material-icons-round" style="font-size:16px;">done</span>
                            </button>
                        @endif
                        <button wire:click.stop="delete({{ $notif->id }})"
                                class="btn btn-sm btn-outline-danger" title="মুছুন">
                            <span class="material-icons-round" style="font-size:16px;">delete</span>
                        </button>
                    </div>
                </div>
            @empty
                <div class="text-center py-5">
                    <span class="material-icons-round" style="font-size:48px; color:#ccc;">notifications_none</span>
                    <p class="text-muted mt-2">কোনো notification পাওয়া যায়নি</p>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if($notifications->hasPages())
            <div class="card-footer">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>

</div>
