{{-- resources/views/livewire/super-admin/log/login-log-component.blade.php --}}
<div>
    <div class="card">

        <div class="mat-card-header header-pink-gradient">
            <h5 id="cardHeaderTitleAllsections">Login Log</h5>
            <p id="cardHeaderSubtitle">Track all user login history across the system.</p>
        </div>

        <div class="card-header border-0">
            <div class="card-toolbar">

                {{-- Search --}}
                <div class="card-toolbar-title">
                    <div style="position:relative;display:inline-flex;align-items:center">
                        <span class="material-icons-round"
                              style="position:absolute;left:10px;font-size:17px;color:var(--muted);pointer-events:none">
                            search
                        </span>
                        <input type="text"
                               wire:model.live.debounce.300ms="search"
                               placeholder="Search user..."
                               style="border:1px solid rgba(0,0,0,.1);border-radius:8px;padding:7px 12px 7px 32px;font-size:.78rem;font-family:inherit;color:var(--dark);outline:none;background:#f8f9fa;width:220px" />
                    </div>
                </div>

                {{-- Role Filter --}}
                <div class="col-md-2">
                    <select class="form-select form-select-sm" wire:model.live="role">
                        <option value="">All Roles</option>
                        <option value="admin">Admin</option>
                        <option value="teacher">Teacher</option>
                        <option value="accountant">Accountant</option>
                        <option value="student">Student</option>
                        <option value="parent">Parent</option>
                        <option value="staff">Staff</option>
                    </select>
                </div>

                {{-- Per Page --}}
                @if($logs->total() > 10)
                    <div class="col-md-2">
                        <select class="form-select form-select-sm" wire:model.live="perPage">
                            <option value="10">10 / page</option>
                            <option value="25">25 / page</option>
                            <option value="50">50 / page</option>
                        </select>
                    </div>
                @endif

            </div>
        </div>

        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>SL</th>
                            <th>User</th>
                            <th>Role</th>
                            <th>Last Login</th>
                            <th>Last IP</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $i => $user)
                            @php
                                $roleColorMap = [
                                    'admin'      => ['bg' => 'rgba(79,70,229,.12)',  'color' => '#4f46e5'],
                                    'teacher'    => ['bg' => 'rgba(5,150,105,.12)',  'color' => '#059669'],
                                    'accountant' => ['bg' => 'rgba(8,145,178,.12)',  'color' => '#0891b2'],
                                    'student'    => ['bg' => 'rgba(217,119,6,.12)',  'color' => '#d97706'],
                                    'parent'     => ['bg' => 'rgba(219,39,119,.12)', 'color' => '#db2777'],
                                    'staff'      => ['bg' => 'rgba(107,114,128,.12)','color' => '#6b7280'],
                                ];
                                $rc = $roleColorMap[$user->role] ?? $roleColorMap['staff'];
                            @endphp
                            <tr>
                                <td class="text-muted">{{ $logs->firstItem() + $i }}</td>

                                {{-- User --}}
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        @if($user->avatar)
                                            <img src="{{ asset('storage/' . $user->avatar) }}"
                                                 class="rounded-circle" width="38" height="38"
                                                 style="object-fit:cover;">
                                        @else
                                            <div class="avatar-placeholder"
                                                 style="background:{{ $rc['bg'] }};color:{{ $rc['color'] }};">
                                                {{ strtoupper(substr($user->name, 0, 1)) }}
                                            </div>
                                        @endif
                                        <div>
                                            <div class="fw-500 text-dark" style="font-size:.85rem;">
                                                {{ $user->name }}
                                            </div>
                                            <small class="text-muted">
                                                {{ $user->email ?? $user->username ?? '—' }}
                                            </small>
                                        </div>
                                    </div>
                                </td>

                                {{-- Role --}}
                                <td>
                                    <span class="badge"
                                          style="background:{{ $rc['bg'] }};color:{{ $rc['color'] }};font-size:.72rem;">
                                        {{ ucfirst($user->role) }}
                                    </span>
                                </td>

                                {{-- Last Login --}}
                                <td style="font-size:.85rem;">
                                    @if($user->last_login_at)
                                        <div class="fw-500 text-dark">
                                            {{ optional($user->last_login_at)->diffForHumans() }}
                                        </div>
                                        <small class="text-muted">
                                            {{ optional($user->last_login_at)->format('d M Y, h:i A') }}
                                        </small>
                                    @else
                                        <span class="text-muted" style="font-size:.8rem;">Never logged in</span>
                                    @endif
                                </td>

                                {{-- Last IP --}}
                                <td class="text-muted" style="font-size:.85rem;">
                                    {{ $user->last_login_ip ?? '—' }}
                                </td>

                                {{-- Status --}}
                                <td>
                                    @if($user->is_active)
                                        <span class="badge"
                                              style="background:rgba(5,150,105,.12);color:#059669;font-size:.72rem;">
                                            Active
                                        </span>
                                    @else
                                        <span class="badge"
                                              style="background:rgba(220,38,38,.12);color:#dc2626;font-size:.72rem;">
                                            Inactive
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <span class="material-icons-round d-block mb-2 opacity-25"
                                          style="font-size:3rem;">manage_accounts</span>
                                    No login history found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer border-0 bg-white d-flex align-items-center justify-content-between flex-wrap gap-2 py-2 px-3">
            <small class="text-muted">
                Showing {{ $logs->firstItem() ?? 0 }}–{{ $logs->lastItem() ?? 0 }} of {{ $logs->total() }}
            </small>
            {{ $logs->links('vendor.pagination.custom') }}
        </div>

    </div>
</div>

@push('styles')
<style>
    .avatar-placeholder {
        width: 38px; height: 38px; border-radius: 8px;
        display: inline-flex; align-items: center; justify-content: center;
        font-weight: 700; font-size: .875rem; flex-shrink: 0;
    }
    .fw-500 { font-weight: 500; }
    .form-control, .form-select {
        border-radius: 8px; border: 1px solid var(--border);
        font-size: .875rem; padding: .45rem .75rem;
        transition: border-color .2s, box-shadow .2s;
    }
    .form-control:focus, .form-select:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px var(--primary-light);
    }
</style>
@endpush