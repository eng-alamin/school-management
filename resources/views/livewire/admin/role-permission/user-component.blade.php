{{-- livewire/admin/role-permission/user-component.blade.php --}}

<div>

    <div class="card">

        <div class="mat-card-header header-primary-gradient">
            <h5 id="user-list-header-title">User Role Assignment</h5>
            <p id="user-list-header-subtitle">Assign roles to users and manage access.</p>
        </div>

        <div class="card-header border-0">
            <div class="card-toolbar">
                <div class="card-toolbar-title">
                    <div style="position:relative;display:inline-flex;align-items:center">
                        <span class="material-icons-round" style="position:absolute;left:10px;font-size:17px;color:var(--muted);pointer-events:none">search</span>
                        <input type="text" wire:model.live.debounce.300ms="search" id="tableSearch" placeholder="Search" class="tb-search"/>
                    </div>
                </div>

                @if($users->total() > 10)
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
                            <th id="th-sl">SL</th>
                            <th id="th-user" wire:click="sortBy('name')" style="cursor:pointer">
                                User @if($sortField === 'name') {!! $sortDirection === 'asc' ? '↑' : '↓' !!} @endif
                            </th>
                            <th id="th-roles">Roles</th>
                            <th id="th-status">Status</th>
                            <th id="th-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $i => $user)
                        <tr wire:key="user-{{ $user->id }}">
                            <td class="text-muted">{{ $users->firstItem() + $i }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $user->photo ? asset('storage/' . $user->photo) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&size=64&background=random' }}"
                                        alt="{{ $user->name }}"
                                        style="width:32px;height:32px;border-radius:50%;object-fit:cover;"/>
                                    <span>{{ $user->name }}</span>
                                </div>
                            </td>
                            <td>
                                @forelse($user->roles as $role)
                                    <span class="badge bg-secondary">{{ ucwords(str_replace('_', ' ', $role->name)) }}</span>
                                @empty
                                    <span class="text-muted">No role assigned</span>
                                @endforelse
                            </td>
                            <td>
                                @php
                                    $statusColors = [
                                        'active'    => 'bg-success',
                                        'inactive'  => 'bg-secondary',
                                        'suspended' => 'bg-danger',
                                    ];
                                    $status = $user->status ?? 'active';
                                @endphp
                                <span class="badge {{ $statusColors[$status] ?? 'bg-secondary' }} text-capitalize">
                                    {{ $status }}
                                </span>
                            </td>
                            <td>
                                @if ($user->role !== 'admin' && $user->role !== 'branch')
                                    <div class="d-flex gap-1">
                                        <button class="act-btn edit" title="Assign Role" wire:click="openRoleModal({{ $user->id }})">
                                            <span class="material-icons-round">admin_panel_settings</span>
                                        </button>
                                    </div>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox display-5 d-block mb-2 opacity-25"></i>
                                No users found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer border-0 bg-white d-flex align-items-center justify-content-between flex-wrap gap-2 py-2 px-3">
            <small class="text-muted">Showing {{ $users->firstItem() ?? 0 }}–{{ $users->lastItem() ?? 0 }} of {{ $users->total() }}</small>
            {{ $users->links('vendor.pagination.custom') }}
        </div>

    </div>

    {{-- ===== ASSIGN ROLE MODAL ===== --}}
    @if($showRoleModal)
        <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="mb-0" id="role-assign-title">Assign Roles</h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeRoleModal"></button>
                    </div>

                    <div class="modal-body">
                        <div class="row g-3">
                            @forelse($roles as $role)
                                <div class="col-md-6">
                                    <div class="form-check" style="padding:10px 12px 10px 40px;border:1px solid #eef0f2;border-radius:10px;">
                                        <input class="form-check-input" type="checkbox" value="{{ $role->name }}"
                                               wire:model="selectedRoles" id="role-{{ $role->id }}">
                                        <label class="form-check-label" for="role-{{ $role->id }}">
                                            {{ $role->name }}
                                        </label>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12">
                                    <p class="text-muted mb-0">
                                        Kono role toiri kora nai. <a href="{{ route('admin.role-permission.create') }}" class="text-primary" wire:navigate>New role</a> shobar age.
                                    </p>
                                </div>
                            @endforelse
                        </div>
                        @error('selectedRoles') <span class="text-danger d-block mt-2">{{ $message }}</span> @enderror
                    </div>

                    <div class="modal-footer justify-content-end border-0 pt-0">
                        <button class="btn btn-light btn-sm" wire:click="closeRoleModal">Cancel</button>
                        <button class="btn btn-primary btn-sm" wire:click="assignRoles" wire:loading.attr="disabled" wire:target="assignRoles">
                            <span wire:loading wire:target="assignRoles" class="spinner-border spinner-border-sm me-1"></span>
                            Assign
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>