{{-- resources/views/livewire/accountant/mailbox/sent.blade.php --}}
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
                        <button wire:click="trashMessage({{ $viewing->id }})" class="btn btn-sm btn-outline-warning">
                            <i class="fas fa-trash me-1"></i> Move to Trash
                        </button>
                        <button wire:click="deleteMessage({{ $viewing->id }})"
                                class="btn btn-sm btn-outline-danger"
                                onclick="return confirm('Permanently delete this message?')">
                            <i class="fas fa-times-circle me-1"></i> Delete
                        </button>
                    </div>
                </div>

                <div class="message-view-body">
                    <h5 class="message-subject">{{ $viewing->subject }}</h5>

                    <div class="message-meta">
                        <div class="meta-avatar">{{ strtoupper(substr($viewing->receiver->name, 0, 1)) }}</div>
                        <div>
                            <div class="meta-from">
                                <span class="text-muted small">To:</span>
                                <strong class="ms-1">{{ $viewing->receiver->name }}</strong>
                                <span class="text-muted small ms-2">&lt;{{ $viewing->receiver->email }}&gt;</span>
                            </div>
                            <div class="meta-date text-muted small">
                                Sent: {{ $viewing->created_at->format('D, d M Y, h:i A') }}
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
                <h4><i class="fas fa-paper-plane me-2"></i>Sent</h4>
                <div class="search-box">
                    <i class="fas fa-search search-icon"></i>
                    <input wire:model.live.debounce.400ms="search" type="text" class="form-control search-input" placeholder="Search sent mail...">
                </div>
            </div>

            @if($messages->isEmpty())
                <div class="empty-state">
                    <i class="fas fa-paper-plane"></i>
                    <p>{{ $search ? 'No messages found.' : 'No sent messages yet.' }}</p>
                </div>
            @else
                <div class="message-list">
                    @foreach($messages as $message)
                        <div class="message-row" wire:click="viewMessage({{ $message->id }})">
                            <div class="msg-avatar sent-avatar">{{ strtoupper(substr($message->receiver->name, 0, 1)) }}</div>
                            <div class="msg-info">
                                <div class="msg-top">
                                    <span class="msg-from">
                                        <span class="text-muted me-1" style="font-size:.8rem">To:</span>
                                        {{ $message->receiver->name }}
                                    </span>
                                    <span class="msg-date">{{ $message->created_at->diffForHumans() }}</span>
                                </div>
                                <div class="msg-subject">{{ $message->subject }}</div>
                                <div class="msg-excerpt">{{ $message->excerpt }}</div>
                            </div>
                            <div class="msg-actions" onclick.stop>
                                <i class="fas fa-trash text-danger" wire:click.stop="trashMessage({{ $message->id }})" title="Trash"></i>
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
