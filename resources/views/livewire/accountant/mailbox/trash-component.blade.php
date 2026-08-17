{{-- resources/views/livewire/accountant/mailbox/trash.blade.php --}}
<div class="mailbox-wrapper">
    @include('livewire.accountant.mailbox.partials.sidebar')

    <div class="mailbox-content">
        @if($viewing)
            {{-- ══ MESSAGE VIEW ══════════════════════════════════════════════ --}}
            <div class="message-view">
                <div class="message-view-header">
                    <button wire:click="backToList" class="btn btn-light btn-sm">
                        <i class="fas fa-arrow-left me-1"></i> Back
                    </button>
                    <div class="message-view-actions">
                        <button wire:click="restoreMessage({{ $viewing->id }})" class="btn btn-sm btn-success">
                            <i class="fas fa-undo me-1"></i> Restore
                        </button>
                        <button wire:click="permanentDelete({{ $viewing->id }})"
                                class="btn btn-sm btn-danger"
                                onclick="return confirm('This will permanently delete the message. Are you sure?')">
                            <i class="fas fa-times-circle me-1"></i> Delete Forever
                        </button>
                    </div>
                </div>

                <div class="message-view-body">
                    <div class="trash-notice">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        This message is in the trash. Restore it or permanently delete it.
                    </div>

                    <h5 class="message-subject">{{ $viewing->subject }}</h5>

                    <div class="message-meta">
                        <div class="meta-avatar">{{ strtoupper(substr($viewing->sender->name, 0, 1)) }}</div>
                        <div>
                            <div class="meta-from">
                                <span class="text-muted small">From:</span>
                                <strong class="ms-1">{{ $viewing->sender->name }}</strong>
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
                <h4><i class="fas fa-trash me-2 text-danger"></i>Trash</h4>
                <div class="d-flex align-items-center gap-3">
                    <div class="search-box">
                        <i class="fas fa-search search-icon"></i>
                        <input wire:model.live.debounce.400ms="search" type="text" class="form-control search-input" placeholder="Search trash...">
                    </div>
                    @if(!$messages->isEmpty())
                        @if($confirmEmpty)
                            <div class="d-flex align-items-center gap-2">
                                <span class="text-danger small fw-semibold">Sure?</span>
                                <button wire:click="emptyTrash" class="btn btn-danger btn-sm">Yes, Empty</button>
                                <button wire:click="$set('confirmEmpty', false)" class="btn btn-light btn-sm">Cancel</button>
                            </div>
                        @else
                            <button wire:click="$set('confirmEmpty', true)" class="btn btn-outline-danger btn-sm">
                                <i class="fas fa-trash me-1"></i> Empty Trash
                            </button>
                        @endif
                    @endif
                </div>
            </div>

            @if($messages->isEmpty())
                <div class="empty-state">
                    <i class="fas fa-trash-alt"></i>
                    <p>{{ $search ? 'No messages found.' : 'Trash is empty.' }}</p>
                </div>
            @else
                <div class="trash-notice mb-3">
                    <i class="fas fa-info-circle me-2"></i>
                    Messages in trash are only visible to you. Permanently deleted messages cannot be recovered.
                </div>

                <div class="message-list">
                    @foreach($messages as $message)
                        @php $isReceived = $message->receiver_id === auth()->id(); @endphp
                        <div class="message-row trashed" wire:click="viewMessage({{ $message->id }})">
                            <div class="msg-avatar trashed-avatar">
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
                            <div class="msg-actions" onclick.stop>
                                <i class="fas fa-undo text-success me-2"
                                   wire:click.stop="restoreMessage({{ $message->id }})"
                                   title="Restore"></i>
                                <i class="fas fa-times-circle text-danger"
                                   wire:click.stop="permanentDelete({{ $message->id }})"
                                   onclick="return confirm('Permanently delete?')"
                                   title="Delete forever"></i>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-3">{{ $messages->links() }}</div>
            @endif
        @endif
    </div>
</div>

@include('livewire.accountant.mailbox.partials.styles')
