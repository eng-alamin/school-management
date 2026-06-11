{{-- resources/views/livewire/admin/mailbox/important.blade.php --}}
<div class="mailbox-wrapper">
    @include('livewire.admin.mailbox.partials.sidebar')

    <div class="mailbox-content">
        @if($viewing)
            {{-- ══ MESSAGE VIEW ══════════════════════════════════════════════ --}}
            <div class="message-view">
                <div class="message-view-header">
                    <button wire:click="backToList" class="btn btn-light btn-sm">
                        <i class="fas fa-arrow-left me-1"></i> Back
                    </button>
                    <button wire:click="unmarkImportant({{ $viewing->id }})" class="btn btn-sm btn-warning">
                        <i class="fas fa-star me-1"></i> Remove from Important
                    </button>
                </div>

                <div class="message-view-body">
                    <h5 class="message-subject">
                        <i class="fas fa-star text-warning me-2"></i>{{ $viewing->subject }}
                    </h5>

                    <div class="message-meta">
                        <div class="meta-avatar">{{ strtoupper(substr($viewing->sender->name, 0, 1)) }}</div>
                        <div>
                            <div class="meta-from">
                                <span class="text-muted small">From:</span>
                                <strong class="ms-1">{{ $viewing->sender->name }}</strong>
                                <span class="text-muted small ms-2">&lt;{{ $viewing->sender->email }}&gt;</span>
                            </div>
                            <div class="meta-from mt-1">
                                <span class="text-muted small">To:</span>
                                <span class="ms-1">{{ $viewing->receiver->name }}</span>
                            </div>
                            <div class="meta-date text-muted small">
                                {{ $viewing->created_at->format('D, d M Y, h:i A') }}
                            </div>
                        </div>
                    </div>

                    <hr>
                    <div class="message-body-text">{!! nl2br(e($viewing->body)) !!}</div>
                </div>
            </div>

        @else
            {{-- ══ MESSAGE LIST ══════════════════════════════════════════════ --}}
            <div class="mailbox-header">
                <h4><i class="fas fa-star me-2 text-warning"></i>Important</h4>
                <div class="search-box">
                    <i class="fas fa-search search-icon"></i>
                    <input wire:model.live.debounce.400ms="search" type="text" class="form-control search-input" placeholder="Search important...">
                </div>
            </div>

            @if($messages->isEmpty())
                <div class="empty-state">
                    <i class="fas fa-star text-warning"></i>
                    <p>{{ $search ? 'No messages found.' : 'No important messages.' }}</p>
                </div>
            @else
                <div class="message-list">
                    @foreach($messages as $message)
                        @php $isReceived = $message->receiver_id === auth()->id(); @endphp
                        <div class="message-row {{ $isReceived && !$message->is_read ? 'unread' : '' }}"
                             wire:click="viewMessage({{ $message->id }})">
                            <div class="msg-avatar">
                                {{ $isReceived
                                    ? strtoupper(substr($message->sender->name, 0, 1))
                                    : strtoupper(substr($message->receiver->name, 0, 1)) }}
                            </div>
                            <div class="msg-info">
                                <div class="msg-top">
                                    <span class="msg-from">
                                        @if($isReceived)
                                            <span class="text-muted me-1" style="font-size:.8rem">From:</span>
                                            {{ $message->sender->name }}
                                        @else
                                            <span class="text-muted me-1" style="font-size:.8rem">To:</span>
                                            {{ $message->receiver->name }}
                                        @endif
                                    </span>
                                    <span class="msg-date">{{ $message->created_at->diffForHumans() }}</span>
                                </div>
                                <div class="msg-subject">{{ $message->subject }}</div>
                                <div class="msg-excerpt">{{ $message->excerpt }}</div>
                            </div>
                            <div class="msg-actions">
                                <i class="fas fa-star text-warning"
                                   wire:click.stop="unmarkImportant({{ $message->id }})"
                                   title="Remove from important"></i>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-3">{{ $messages->links() }}</div>
            @endif
        @endif
    </div>
</div>

@include('livewire.admin.mailbox.partials.styles')
