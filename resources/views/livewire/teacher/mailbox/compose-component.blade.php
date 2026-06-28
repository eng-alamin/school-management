{{-- resources/views/livewire/admin/mailbox/compose.blade.php --}}
<div class="mailbox-wrapper">
    @include('livewire.admin.mailbox.partials.sidebar')

    <div class="mailbox-content">
        {{-- Page Header --}}
        <div class="mailbox-header">
            <h4><i class="fas fa-pen me-2"></i>Compose Message</h4>
        </div>

        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="compose-form-card">
            {{-- To Field with autocomplete --}}
            <div class="form-group mb-3 position-relative">
                <label class="form-label fw-semibold">To <span class="text-danger">*</span></label>

                @if($receiver_id)
                    <div class="selected-user-badge">
                        <i class="fas fa-user me-1"></i>
                        {{ $receiverName }}
                        <button wire:click="clearReceiver" type="button" class="btn-clear-user">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                @else
                    <input
                        wire:model.live.debounce.300ms="searchUser"
                        type="text"
                        class="form-control @error('receiver_id') is-invalid @enderror"
                        placeholder="Search by name or email..."
                        autocomplete="off"
                    />
                    @error('receiver_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror

                    {{-- Suggestions dropdown --}}
                    @if(count($userSuggestions) > 0)
                        <div class="suggestions-dropdown">
                            @foreach($userSuggestions as $user)
                                <div class="suggestion-item" wire:click="selectUser({{ $user['id'] }}, '{{ addslashes($user['name']) }}')">
                                    <div class="suggestion-avatar">
                                        {{ strtoupper(substr($user['name'], 0, 1)) }}
                                    </div>
                                    <div class="suggestion-info">
                                        <span class="suggestion-name">{{ $user['name'] }}</span>
                                        <span class="suggestion-email">{{ $user['email'] }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                @endif
            </div>

            {{-- Subject --}}
            <div class="form-group mb-3">
                <label class="form-label fw-semibold">Subject <span class="text-danger">*</span></label>
                <input
                    wire:model="subject"
                    type="text"
                    class="form-control @error('subject') is-invalid @enderror"
                    placeholder="Enter subject..."
                />
                @error('subject')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Body --}}
            <div class="form-group mb-4">
                <label class="form-label fw-semibold">Message <span class="text-danger">*</span></label>
                <textarea
                    wire:model="body"
                    class="form-control @error('body') is-invalid @enderror"
                    rows="10"
                    placeholder="Write your message here..."
                ></textarea>
                @error('body')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Actions --}}
            <div class="compose-actions">
                <button wire:click="send" wire:loading.attr="disabled" class="btn bg-dark text-white btn-send">
                    <span wire:loading.remove wire:target="send">
                        <i class="fas fa-paper-plane me-1"></i> Send Message
                    </span>
                    <span wire:loading wire:target="send">
                        <i class="fas fa-spinner fa-spin me-1"></i> Sending...
                    </span>
                </button>

                <a href="{{ route('admin.mailbox.inbox') }}" class="btn btn-light ms-2">
                    <i class="fas fa-times me-1"></i> Discard
                </a>
            </div>
        </div>
    </div>
</div>
@include('livewire.admin.mailbox.partials.styles')