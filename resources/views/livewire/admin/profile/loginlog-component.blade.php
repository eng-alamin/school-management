<div>
    <div class="card">

        <!-- floating header -->
        <div class="mat-card-header header-pink-gradient">
            <h5 id="cardHeaderTitleProfileActivity">Profile Activity</h5>
        </div>

        <div class="container-xl mt-4">

            @include('livewire.admin.profile.navbar', ['user' => $user])

            <!-- START CONTENT -->

            <div class="card profile-card p-4 mb-4">

                <!-- Header -->
                <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                    <div>
                        <span class="fw-bold fs-5">Active Sessions</span>
                        <div class="text-muted mt-1" style="font-size:.8rem">
                            Devices currently logged in to your account
                        </div>
                    </div>
                    @if($sessions->where('is_current', false)->count() > 0)
                        <button wire:click="revokeAllOther"
                                wire:confirm="Revoke all other sessions?"
                                class="btn btn-sm btn-outline-danger">
                            <span wire:loading wire:target="revokeAllOther" class="spinner-border spinner-border-sm me-1"></span>
                            Revoke All Others
                        </button>
                    @endif
                </div>

                <!-- Session List -->
                @forelse($sessions as $session)
                    <div class="d-flex align-items-start gap-3 py-3 {{ !$loop->last ? 'border-bottom' : '' }}">

                        <!-- Device Icon -->
                        <div class="session-icon-wrap {{ $session->is_current ? 'session-icon-active' : '' }}">
                            @if($session->device === 'Mobile')
                                <span class="material-icons-round">smartphone</span>
                            @else
                                <span class="material-icons-round">computer</span>
                            @endif
                        </div>

                        <!-- Info -->
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="fw-semibold" style="font-size:.9rem">
                                    {{ $session->browser }} &mdash; {{ $session->os }}
                                </span>
                                @if($session->is_current)
                                    <span class="badge rounded-pill"
                                          style="font-size:.68rem;font-weight:500;background:#198754;color:#fff">
                                        Current
                                    </span>
                                @endif
                            </div>
                            <div class="d-flex flex-wrap gap-3" style="font-size:.8rem;color:var(--text-muted,#6c757d)">
                                <span class="d-flex align-items-center gap-1">
                                    <span class="material-icons-round" style="font-size:1rem">devices</span>
                                    {{ $session->device }}
                                </span>
                                <span class="d-flex align-items-center gap-1">
                                    <span class="material-icons-round" style="font-size:1rem">location_on</span>
                                    {{ $session->ip_address }}
                                </span>
                                <span class="d-flex align-items-center gap-1">
                                    <span class="material-icons-round" style="font-size:1rem">schedule</span>
                                    {{ $session->last_activity->diffForHumans() }}
                                </span>
                            </div>
                        </div>

                        <!-- Revoke -->
                        @if(! $session->is_current)
                            <button wire:click="revokeSession('{{ $session->id }}')"
                                    wire:confirm="Revoke this session?"
                                    class="btn btn-sm btn-outline-secondary ms-auto flex-shrink-0"
                                    style="font-size:.75rem">
                                <span wire:loading wire:target="revokeSession('{{ $session->id }}')"
                                      class="spinner-border spinner-border-sm"></span>
                                <span wire:loading.remove wire:target="revokeSession('{{ $session->id }}')"
                                      class="material-icons-round" style="font-size:1rem;vertical-align:middle">
                                    logout
                                </span>
                                Revoke
                            </button>
                        @endif

                    </div>
                @empty
                    <div class="text-center py-5 text-muted">
                        <span class="material-icons-round" style="font-size:2.5rem;display:block;margin-bottom:8px">devices_off</span>
                        No active sessions found.
                    </div>
                @endforelse

            </div>

            <!-- END CONTENT -->

        </div>

    </div>
</div>

@push('styles')
<style>
    .card { border: 1px solid var(--border); border-radius: 12px; box-shadow: 0 1px 4px rgba(0,0,0,.04); }

    .session-icon-wrap {
        width: 42px; height: 42px; border-radius: 10px;
        background: var(--bs-light, #f8f9fa);
        border: 1px solid var(--border, #e5e7eb);
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
        color: #6c757d;
    }
    .session-icon-active {
        background: rgba(25,135,84,.1);
        border-color: rgba(25,135,84,.3);
        color: #198754;
    }
    .session-icon-wrap .material-icons-round { font-size: 1.3rem; }
</style>
@endpush