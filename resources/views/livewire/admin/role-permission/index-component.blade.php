{{-- livewire/admin/role-permission/index-component.blade.php --}}

<div>

    <div class="card">

        <div class="mat-card-header header-primary-gradient">
            <h5 id="role-list-header-title">Roles &amp; Permissions</h5>
            <p id="role-list-header-subtitle">Manage roles, assign permissions, and organize easily.</p>
        </div>

        <div class="card-header border-0">
            <div class="card-toolbar">
                <div class="card-toolbar-title">
                    <div style="position:relative;display:inline-flex;align-items:center">
                        <span class="material-icons-round" style="position:absolute;left:10px;font-size:17px;color:var(--muted);pointer-events:none">search</span>
                        <input type="text" wire:model.live.debounce.300ms="search" id="tableSearch" placeholder="Search" class="tb-search"/>
                    </div>
                </div>

                @if($roles->total() > 10)
                    <div class="col-md-2">
                        <select class="form-select form-select-sm" wire:model.live="perPage">
                            <option value="10">10 / page</option>
                            <option value="25">25 / page</option>
                            <option value="50">50 / page</option>
                        </select>
                    </div>
                @endif

                <a href="{{ route('admin.role-permission.create') }}" wire:navigate class="btn btn-primary">
                    <span>
                        <span class="material-icons-round">add</span>
                        <span>New Role</span>
                    </span>
                </a>
            </div>
        </div>

        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th id="th-sl">SL</th>
                            <th wire:click="sortBy('name')" style="cursor:pointer">
                                <span id="th-name">Role Name</span> @if($sortField === 'name') {!! $sortDirection === 'asc' ? '↑' : '↓' !!} @endif
                            </th>
                            <th id="th-permissions">Permissions</th>
                            <th wire:click="sortBy('created_at')" style="cursor:pointer">
                                <span id="th-created">Created At</span> @if($sortField === 'created_at') {!! $sortDirection === 'asc' ? '↑' : '↓' !!} @endif
                            </th>
                            <th id="th-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($roles as $i => $role)
                        <tr wire:key="role-{{ $role->id }}">
                            <td class="text-muted">{{ $roles->firstItem() + $i }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="material-icons-round" style="font-size:20px;color:var(--muted)">admin_panel_settings</span>
                                    <span>{{ $role->name }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-secondary">{{ $role->permissions_count }} permissions</span>
                            </td>
                            <td>{{ $role->created_at?->format('d M Y') ?? '—' }}</td>
                            <td>
                                <div class="d-flex gap-1">
                                    <button class="act-btn view" title="View"
                                            wire:click="openViewModal({{ $role->id }})">
                                        <span class="material-icons-round">visibility</span>
                                    </button>
                                    <a href="{{ route('admin.role-permission.edit', $role->id) }}" wire:navigate class="act-btn edit" title="Edit">
                                        <span class="material-icons-round">drive_file_rename_outline</span>
                                    </a>
                                    <button class="act-btn delete" title="Delete"
                                            wire:click="confirmDeleteRecord({{ $role->id }})">
                                        <span class="material-icons-round">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox display-5 d-block mb-2 opacity-25"></i>
                                No roles found.
                                <a href="{{ route('admin.role-permission.create') }}" wire:navigate>Add one now</a>.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer border-0 bg-white d-flex align-items-center justify-content-between flex-wrap gap-2 py-2 px-3">
            <small class="text-muted">Showing {{ $roles->firstItem() ?? 0 }}–{{ $roles->lastItem() ?? 0 }} of {{ $roles->total() }}</small>
            {{ $roles->links('vendor.pagination.custom') }}
        </div>

    </div>

    {{-- ===== VIEW MODAL ===== --}}
    @if($showViewModal && $viewingRole)
        <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5);">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $viewingRole->name }}</h5>
                        <button class="btn-close" wire:click="$set('showViewModal',false)"></button>
                    </div>
                    <div class="modal-body">
                        @forelse($viewingRole->permissions as $permission)
                            <span class="badge bg-light text-dark border mb-1">{{ $permission->name }}</span>
                        @empty
                            <p class="text-muted mb-0">No permissions assigned.</p>
                        @endforelse
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-light" wire:click="$set('showViewModal',false)">Close</button>
                        <a href="{{route('admin.role-permission.edit', $this->viewingRoleId)}}" class="btn btn-primary">
                            <i class="bi bi-pencil me-1"></i>Edit
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ===== DELETE CONFIRM ===== --}}
    @if($confirmDelete)
        <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5);">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                    <div class="modal-body text-center py-4">
                        <div style="width:56px;height:56px;border-radius:50%;background:#fee2e2;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                            <i class="bi bi-exclamation-triangle text-danger" style="font-size:1.5rem;"></i>
                        </div>
                        <h6 class="fw-700" id="role-delete-title">Delete Role?</h6>
                        <p class="text-muted small" id="role-delete-msg">This action cannot be undone.</p>
                    </div>
                    <div class="modal-footer justify-content-center border-0 pt-0">
                        <button class="btn btn-light btn-sm" wire:click="$set('confirmDelete', false)">Cancel</button>
                        <button class="btn btn-danger btn-sm" wire:click="deleteRecord">
                            <span wire:loading wire:target="deleteRecord" class="spinner-border spinner-border-sm me-1"></span>
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>