{{-- resources/views/livewire/super-admin/log/session-log-component.blade.php --}}
<div>
    <div class="card">

        <div class="mat-card-header header-pink-gradient">
            <h5 id="cardHeaderTitleAllsections">Active Sessions</h5>
            <p id="cardHeaderSubtitle">Users currently active in the last 5 minutes.</p>
        </div>

        <div class="card-body pt-3">

            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                <div>
                    <span class="fw-bold" style="font-size:.95rem;">
                        Online Now
                        <span class="badge bg-success ms-1">{{ $sessions->count() }}</span>
                    </span>
                    <div class="text-muted mt-1" style="font-size:.78rem;">
                        Sessions refresh on page reload
                    </div>
                </div>
                @if($sessions->where('is_current', false)->count() > 0)
                    <button wire:click="revokeAllOther"
                            wire:confirm="Revoke all other sessions?"
                            class="btn btn-sm btn-outline-danger">
                        <span wire:loading wire:target="revokeAllOther"
                              class="spinner-border spinner-border-sm me-1"></span>
                        Revoke All Others
                    </button>
                @endif
            </div>

            @forelse($sessions as $session)
                <div class="d-flex align-items-start gap-3 py-3 {{ !$loop->last ? 'border-bottom' : '' }}">

                    <div class="session-icon-wrap {{ $session->is_current ? 'session-icon-active' : '' }}">
                        <span class="material-icons-round">
                            {{ $session->device === 'Mobile' ? 'smartphone' : 'computer' }}
                        </span>
                    </div>

                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="fw-500" style="font-size:.9rem;">{{ $session->user_name }}</span>
                            <span class="badge rounded-pill bg-secondary" style="font-size:.68rem;">
                                {{ ucfirst($session->user_role) }}
                            </span>
                            @if($session->is_current)
                                <span class="badge rounded-pill"
                                      style="font-size:.68rem;background:#198754;color:#fff;">
                                    Current
                                </span>
                            @endif
                        </div>
                        <div class="d-flex flex-wrap gap-3" style="font-size:.8rem;color:#6c757d;">
                            <span class="d-flex align-items-center gap-1">
                                <span class="material-icons-round" style="font-size:1rem;">devices</span>
                                {{ $session->browser }} — {{ $session->os }}
                            </span>
                            <span class="d-flex align-items-center gap-1">
                                <span class="material-icons-round" style="font-size:1rem;">location_on</span>
                                {{ $session->ip_address }}
                            </span>
                            <span class="d-flex align-items-center gap-1">
                                <span class="material-icons-round" style="font-size:1rem;">schedule</span>
                                {{ $session->last_activity->diffForHumans() }}
                            </span>
                        </div>
                    </div>

                    @if(!$session->is_current)
                        <button wire:click="revokeSession('{{ $session->id }}')"
                                wire:confirm="Revoke this session?"
                                class="btn btn-sm btn-outline-secondary ms-auto flex-shrink-0"
                                style="font-size:.75rem;">
                            <span wire:loading wire:target="revokeSession('{{ $session->id }}')"
                                  class="spinner-border spinner-border-sm"></span>
                            <span wire:loading.remove wire:target="revokeSession('{{ $session->id }}')"
                                  class="material-icons-round"
                                  style="font-size:1rem;vertical-align:middle;">logout</span>
                            Revoke
                        </button>
                    @endif

                </div>
            @empty
                <div class="text-center py-5 text-muted">
                    <span class="material-icons-round d-block mb-2 opacity-25" style="font-size:3rem;">
                        devices_off
                    </span>
                    No active sessions found.
                </div>
            @endforelse

        </div>
    </div>
</div>

@push('styles')
<style>
    .fw-500 { font-weight: 500; }
    .session-icon-wrap {
        width: 42px; height: 42px; border-radius: 10px;
        background: var(--bs-light, #f8f9fa);
        border: 1px solid var(--border, #e5e7eb);
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0; color: #6c757d;
    }
    .session-icon-active {
        background: rgba(25,135,84,.1);
        border-color: rgba(25,135,84,.3);
        color: #198754;
    }
    .session-icon-wrap .material-icons-round { font-size: 1.3rem; }
</style>
@endpush