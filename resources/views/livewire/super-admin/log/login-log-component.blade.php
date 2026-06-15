{{-- resources/views/livewire/superadmin/login-log-component.blade.php --}}
<div>

    <div class="card">
        <div class="mat-card-header header-pink-gradient">
            <h5>Login Logs</h5>
        </div>

        <div class="container-xl mt-4">
            <div class="card-custom profile-card p-4 mb-4">

                {{-- Tabs --}}
                <ul class="nav nav-tabs mb-4">
                    <li class="nav-item">
                        <button class="nav-link {{ $activeTab === 'sessions' ? 'active' : '' }}"
                                wire:click="$set('activeTab', 'sessions')">
                            <span class="material-icons-round align-middle me-1"
                                  style="font-size:1rem">devices</span>
                            Active Sessions
                            <span class="badge bg-success ms-1">
                                {{ $onlineSessions->count() }}
                            </span>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link {{ $activeTab === 'login' ? 'active' : '' }}"
                                wire:click="$set('activeTab', 'login')">
                            <span class="material-icons-round align-middle me-1"
                                  style="font-size:1rem">manage_accounts</span>
                            Login Info
                            <span class="badge bg-secondary ms-1">
                                {{ $allUsers->total() }}
                            </span>
                        </button>
                    </li>
                </ul>

                <div class="tab-content">

                    {{-- ===== TAB 1: ACTIVE SESSIONS ===== --}}
                    <div class="tab-pane fade {{ $activeTab === 'sessions' ? 'show active' : '' }}">

                        <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                            <div>
                                <span class="fw-bold fs-5">Active Sessions</span>
                                <div class="text-muted mt-1" style="font-size:.8rem">
                                    Users active in the last 5 minutes
                                </div>
                            </div>
                            @if($onlineSessions->where('is_current', false)->count() > 0)
                                <button wire:click="revokeAllOther"
                                        wire:confirm="Revoke all other sessions?"
                                        class="btn btn-sm btn-outline-danger">
                                    <span wire:loading wire:target="revokeAllOther"
                                          class="spinner-border spinner-border-sm me-1"></span>
                                    Revoke All Others
                                </button>
                            @endif
                        </div>

                        @forelse($onlineSessions as $session)
                            <div class="d-flex align-items-start gap-3 py-3 {{ !$loop->last ? 'border-bottom' : '' }}">

                                <div class="session-icon-wrap {{ $session->is_current ? 'session-icon-active' : '' }}">
                                    <span class="material-icons-round">
                                        {{ $session->device === 'Mobile' ? 'smartphone' : 'computer' }}
                                    </span>
                                </div>

                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <span class="fw-semibold" style="font-size:.9rem">
                                            {{ $session->user_name }}
                                        </span>
                                        <span class="badge rounded-pill bg-secondary"
                                              style="font-size:.68rem">
                                            {{ ucfirst($session->user_role) }}
                                        </span>
                                        @if($session->is_current)
                                            <span class="badge rounded-pill"
                                                  style="font-size:.68rem;background:#198754;color:#fff">
                                                Current
                                            </span>
                                        @endif
                                    </div>
                                    <div class="d-flex flex-wrap gap-3"
                                         style="font-size:.8rem;color:#6c757d">
                                        <span class="d-flex align-items-center gap-1">
                                            <span class="material-icons-round"
                                                  style="font-size:1rem">devices</span>
                                            {{ $session->browser }} — {{ $session->os }}
                                        </span>
                                        <span class="d-flex align-items-center gap-1">
                                            <span class="material-icons-round"
                                                  style="font-size:1rem">location_on</span>
                                            {{ $session->ip_address }}
                                        </span>
                                        <span class="d-flex align-items-center gap-1">
                                            <span class="material-icons-round"
                                                  style="font-size:1rem">schedule</span>
                                            {{ $session->last_activity->diffForHumans() }}
                                        </span>
                                    </div>
                                </div>

                                @if(! $session->is_current)
                                    <button wire:click="revokeSession('{{ $session->id }}')"
                                            wire:confirm="Revoke this session?"
                                            class="btn btn-sm btn-outline-secondary ms-auto flex-shrink-0"
                                            style="font-size:.75rem">
                                        <span wire:loading
                                              wire:target="revokeSession('{{ $session->id }}')"
                                              class="spinner-border spinner-border-sm"></span>
                                        <span wire:loading.remove
                                              wire:target="revokeSession('{{ $session->id }}')"
                                              class="material-icons-round"
                                              style="font-size:1rem;vertical-align:middle">
                                            logout
                                        </span>
                                        Revoke
                                    </button>
                                @endif

                            </div>
                        @empty
                            <div class="text-center py-5 text-muted">
                                <span class="material-icons-round"
                                      style="font-size:2.5rem;display:block;margin-bottom:8px">
                                    devices_off
                                </span>
                                No active sessions found.
                            </div>
                        @endforelse

                    </div>

                    {{-- ===== TAB 2: LOGIN INFO ===== --}}
                    <div class="tab-pane fade {{ $activeTab === 'login' ? 'show active' : '' }}">

                        {{-- Filters --}}
                        <div class="d-flex gap-3 mb-4">
                            <input wire:model.live.debounce.300ms="search"
                                   type="text"
                                   placeholder="Search name, email, username..."
                                   class="form-control"
                                   style="max-width:300px" />

                            <select wire:model.live="role"
                                    class="form-select"
                                    style="max-width:180px">
                                <option value="">All Roles</option>
                                <option value="admin">Admin</option>
                                <option value="teacher">Teacher</option>
                                <option value="accountant">Accountant</option>
                                <option value="student">Student</option>
                                <option value="parent">Parent</option>
                            </select>
                        </div>

                        {{-- Table --}}
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>User</th>
                                        <th>Role</th>
                                        <th>Last Login</th>
                                        <th>Last IP</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($allUsers as $user)
                                        <tr>
                                            <td>{{ $allUsers->firstItem() + $loop->index }}</td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    @if($user->avatar)
                                                        <img src="{{ asset('storage/' . $user->avatar) }}"
                                                             class="rounded-circle"
                                                             width="32" height="32"
                                                             style="object-fit:cover">
                                                    @else
                                                        <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center text-white"
                                                             style="width:32px;height:32px;font-size:.75rem">
                                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                                        </div>
                                                    @endif
                                                    <div>
                                                        <div class="fw-semibold" style="font-size:.9rem">
                                                            {{ $user->name }}
                                                        </div>
                                                        <div class="text-muted" style="font-size:.75rem">
                                                            {{ $user->email ?? $user->username ?? '—' }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">
                                                    {{ ucfirst($user->role) }}
                                                </span>
                                            </td>
                                            <td style="font-size:.85rem">
                                                @if($user->last_login_at)
                                                    {{ $user->last_login_at->diffForHumans() }}
                                                    <div class="text-muted" style="font-size:.75rem">
                                                        {{ $user->last_login_at->format('d M Y, h:i A') }}
                                                    </div>
                                                @else
                                                    <span class="text-muted">Never</span>
                                                @endif
                                            </td>
                                            <td style="font-size:.85rem">
                                                {{ $user->last_login_ip ?? '—' }}
                                            </td>
                                            <td>
                                                @if($user->is_active)
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-danger">Inactive</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-4 text-muted">
                                                No users found.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- Pagination --}}
                        <div class="mt-3">
                            {{ $allUsers->links() }}
                        </div>

                    </div>

                </div>
            </div>
        </div>
    </div>

</div>

@push('styles')
<style>
    .card { border: 1px solid var(--border); border-radius: 12px; box-shadow: 0 1px 4px rgba(0,0,0,.04); }
    .nav-tabs .nav-link { color: #6c757d; font-size: .9rem; cursor: pointer; }
    .nav-tabs .nav-link.active { color: #198754; border-bottom-color: #198754; font-weight: 600; }
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