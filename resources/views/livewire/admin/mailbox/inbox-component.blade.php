{{-- resources/views/livewire/admin/mailbox/inbox.blade.php --}}
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
                    <div class="message-view-actions">
                        <button wire:click="toggleImportant({{ $viewing->id }})"
                                class="btn btn-sm {{ $viewing->is_important ? 'btn-warning' : 'btn-outline-warning' }}"
                                title="{{ $viewing->is_important ? 'Remove from important' : 'Mark as important' }}">
                            <i class="fas fa-star"></i>
                        </button>
                        <button wire:click="trashMessage({{ $viewing->id }})" class="btn btn-sm btn-outline-danger" title="Trash">
                            <i class="fas fa-trash"></i>
                        </button>
                        <button wire:click="$toggle('showReply')" class="btn-sm btn bg-dark text-white">
                            <i class="fas fa-reply me-1"></i> Reply
                        </button>
                    </div>
                </div>

                <div class="message-view-body">
                    <h5 class="message-subject">{{ $viewing->subject }}</h5>

                    <div class="message-meta">
                        <div class="meta-avatar">{{ strtoupper(substr($viewing->sender->name, 0, 1)) }}</div>
                        <div>
                            <div class="meta-from">
                                <strong>{{ $viewing->sender->name }}</strong>
                                <span class="text-muted small ms-2">&lt;{{ $viewing->sender->email }}&gt;</span>
                            </div>
                            <div class="meta-date text-muted small">
                                {{ $viewing->created_at->format('D, d M Y, h:i A') }}
                            </div>
                        </div>
                    </div>

                    <hr>
                    <div class="message-body-text">{!! nl2br(e($viewing->body)) !!}</div>
                </div>

                {{-- Reply Form --}}
                @if($showReply)
                    <div class="reply-form mt-4">
                        <h6 class="fw-semibold mb-2">
                            <i class="fas fa-reply me-1 text-primary"></i>
                            Reply to {{ $viewing->sender->name }}
                        </h6>
                        <textarea
                            wire:model="replyBody"
                            class="form-control @error('replyBody') is-invalid @enderror"
                            rows="5"
                            placeholder="Write your reply..."
                        ></textarea>
                        @error('replyBody')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="mt-2 d-flex gap-2">
                            <button wire:click="sendReply" wire:loading.attr="disabled" class="btn bg-dark text-white btn-sm">
                                <span wire:loading.remove wire:target="sendReply"><i class="fas fa-paper-plane me-1"></i>Send Reply</span>
                                <span wire:loading wire:target="sendReply"><i class="fas fa-spinner fa-spin me-1"></i>Sending...</span>
                            </button>
                            <button wire:click="$set('showReply', false)" class="btn btn-light btn-sm">Cancel</button>
                        </div>
                    </div>
                @endif
            </div>

        @else
            {{-- ══ MESSAGE LIST ══════════════════════════════════════════════ --}}
            <div class="mailbox-header">
                <div>
                    <h4><i class="fas fa-inbox me-2"></i>Inbox</h4>
                    @if($unreadCount > 0)
                        <span class="text-muted small">{{ $unreadCount }} unread message{{ $unreadCount > 1 ? 's' : '' }}</span>
                    @endif
                </div>
                <div class="search-box">
                    <i class="fas fa-search search-icon"></i>
                    <input wire:model.live.debounce.400ms="search" type="text" class="form-control search-input" placeholder="Search messages...">
                </div>
            </div>

            @if($messages->isEmpty())
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <p>{{ $search ? 'No messages found for your search.' : 'Your inbox is empty.' }}</p>
                </div>
            @else
                <div class="message-list">
                    @foreach($messages as $message)
                        <div class="message-row {{ !$message->is_read ? 'unread' : '' }}" wire:click="viewMessage({{ $message->id }})">
                            <div class="msg-avatar">{{ strtoupper(substr($message->sender->name, 0, 1)) }}</div>
                            <div class="msg-info">
                                <div class="msg-top">
                                    <span class="msg-from">{{ $message->sender->name }}</span>
                                    <span class="msg-date">{{ $message->created_at->diffForHumans() }}</span>
                                </div>
                                <div class="msg-subject">{{ $message->subject }}</div>
                                <div class="msg-excerpt">{{ $message->excerpt }}</div>
                            </div>
                            <div class="msg-actions" onclick.stop>
                                @if($message->is_important)
                                    <i class="fas fa-star text-warning" wire:click.stop="toggleImportant({{ $message->id }})" title="Remove from important"></i>
                                @else
                                    <i class="far fa-star text-muted" wire:click.stop="toggleImportant({{ $message->id }})" title="Mark important"></i>
                                @endif
                                <i class="fas fa-trash text-danger ms-2" wire:click.stop="trashMessage({{ $message->id }})" title="Trash"></i>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-3">
                    {{ $messages->links() }}
                </div>
            @endif
        @endif
    </div>
</div>

@push('scripts')
<script>
    window.addEventListener('notify', e => {
        // Hook into your toast/notification library here
        // e.g. Toastr.success(e.detail.msg)
        console.log(e.detail.type, e.detail.msg);
    });
</script>
@endpush

@include('livewire.admin.mailbox.partials.styles')

@push('styles')
    <style>
        body.dark-mode .form-control{
   background: #ffffff !important;
    border-color: rgb(30 30 30 / 12%) !important;
    color: #e2e8f0 !important;
}
    </style>
@endpush
