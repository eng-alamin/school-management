{{-- resources/views/livewire/ministry/role/index-component.blade.php --}}

<div class="role-wrap">

    {{-- ══ Header ══════════════════════════════════════════════════════════ --}}
    <div class="px-3 pt-3 pb-2">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h5 class="fw-bold mb-0 text-dark" data-en="Role & Permission Management" data-bn="রোল ও পারমিশন ব্যবস্থাপনা">
                    Role &amp; Permission Management
                </h5>
                <p class="text-secondary mb-0" style="font-size:12px;" data-en="Dynamically manage roles and permissions for the Ministry Panel" data-bn="মিনিস্ট্রি প্যানেলের জন্য রোল ও পারমিশন ডাইনামিকভাবে ব্যবস্থাপনা করুন">Dynamically manage roles and permissions for the Ministry Panel</p>
            </div>
            <div>
                @if ($activeTab === 'roles')
                    <button type="button" class="role-add-btn" wire:click="openCreateRoleModal">
                        <span class="material-icons-round" style="font-size:16px;">add</span>
                        <span data-en="New Role" data-bn="নতুন রোল">New Role</span>
                    </button>
                @else
                    <button type="button" class="role-add-btn" wire:click="openCreatePermissionModal">
                        <span class="material-icons-round" style="font-size:16px;">add</span>
                        <span data-en="New Permission" data-bn="নতুন পারমিশন">New Permission</span>
                    </button>
                @endif
            </div>
        </div>
    </div>

    {{-- ══ Tab Pills ═══════════════════════════════════════════════════════ --}}
    <div class="px-3 pt-2">
        <div class="role-pill-row">
            <button type="button" class="role-pill {{ $activeTab === 'roles' ? 'active' : '' }}" wire:click="setActiveTab('roles')">
                <span class="material-icons-round" style="font-size:15px;">groups</span>
                <span data-en="Roles" data-bn="রোল">Roles</span>
            </button>
            <button type="button" class="role-pill {{ $activeTab === 'permissions' ? 'active' : '' }}" wire:click="setActiveTab('permissions')">
                <span class="material-icons-round" style="font-size:15px;">key</span>
                <span data-en="Permissions" data-bn="পারমিশন">Permissions</span>
            </button>
        </div>
    </div>

    {{-- ══ Roles Tab ═══════════════════════════════════════════════════════ --}}
    @if ($activeTab === 'roles')
        <div class="px-3 pt-3 pb-4">
            <div class="role-table-card">
                <div class="table-responsive">
                    <table class="table role-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th data-en="Role Name" data-bn="রোলের নাম">Role Name</th>
                                <th data-en="Permission Count" data-bn="পারমিশন সংখ্যা">Permission Count</th>
                                <th data-en="User Count" data-bn="ইউজার সংখ্যা">User Count</th>
                                <th class="text-end" data-en="Action" data-bn="অ্যাকশন">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($this->roles as $role)
                                <tr wire:key="role-{{ $role->id }}">
                                    <td class="fw-semibold text-dark">
                                        {{ $role->name }}
                                        @if ($role->name === 'Ministry Super Admin')
                                            <span class="inv-badge pending ms-1" data-en="Protected" data-bn="সুরক্ষিত">Protected</span>
                                        @endif
                                    </td>
                                    <td><span class="inv-badge paid">{{ $role->permissions_count }}</span></td>
                                    <td><span class="inv-badge pending">{{ $role->users_count }}</span></td>
                                    <td class="text-end">
                                        <button type="button" class="role-icon-btn" title="View"
                                                wire:click="openViewRoleModal({{ $role->id }})">
                                            <span class="material-icons-round" style="font-size:15px;">visibility</span>
                                        </button>
                                        <button type="button" class="role-icon-btn primary" title="Edit"
                                                wire:click="openEditRoleModal({{ $role->id }})">
                                            <span class="material-icons-round" style="font-size:15px;">edit</span>
                                        </button>
                                        @if ($role->name !== 'Ministry Super Admin')
                                            <button type="button" class="role-icon-btn danger" title="Delete"
                                                    wire:click="confirmDeleteRole({{ $role->id }})">
                                                <span class="material-icons-round" style="font-size:15px;">delete</span>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-secondary py-4" style="font-size:13px;" data-en="No roles found." data-bn="কোনো রোল পাওয়া যায়নি।">No roles found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    {{-- ══ Permissions Tab ═════════════════════════════════════════════════ --}}
    @if ($activeTab === 'permissions')
        <div class="px-3 pt-3 pb-4">
            @foreach ($this->groupedPermissions as $module => $permissions)
                <div class="role-table-card mb-3">
                    <div class="role-module-title">{{ $module }}</div>
                    <div class="table-responsive">
                        <table class="table role-table mb-0">
                            <tbody>
                                @foreach ($permissions as $permission)
                                    <tr wire:key="permission-{{ $permission->id }}">
                                        <td class="text-dark">{{ $permission->name }}</td>
                                        <td class="text-end" style="width:60px;">
                                            <button type="button" class="role-icon-btn danger" title="Delete"
                                                    wire:click="confirmDeletePermission({{ $permission->id }})">
                                                <span class="material-icons-round" style="font-size:15px;">delete</span>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach

            @if ($this->allPermissions->isEmpty())
                <p class="text-center text-secondary py-4" style="font-size:13px;" data-en="No permissions found." data-bn="কোনো পারমিশন পাওয়া যায়নি।">No permissions found.</p>
            @endif
        </div>
    @endif

    {{-- ══ Role Create/Edit Modal ══════════════════════════════════════════ --}}
    @if ($showRoleModal)
        <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5)">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" data-en="{{ $isRoleEditMode ? 'Edit Role' : 'New Role' }}" data-bn="{{ $isRoleEditMode ? 'রোল সম্পাদনা' : 'নতুন রোল' }}">{{ $isRoleEditMode ? 'Edit Role' : 'New Role' }}</h5>
                        <button type="button" class="btn-close" wire:click="closeModals"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label" data-en="Role Name *" data-bn="রোলের নাম *">Role Name *</label>
                            <div class="input-group">
                                <span class="input-group-text" data-en="Ministry" data-bn="মিনিস্ট্রি">Ministry</span>
                                <input type="text" class="form-control" wire:model="roleName"
                                       data-en-ph="e.g. Textbook Officer" data-bn-ph="যেমন: টেক্সটবুক অফিসার"
                                       placeholder="e.g. Textbook Officer"
                                       @if($isRoleEditMode && $roleId && $this->roles->firstWhere('id', $roleId)?->name === 'Ministry Super Admin') disabled @endif>
                            </div>
                            @error('roleName') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>

                        <label class="form-label d-block" data-en="Permissions" data-bn="পারমিশন">Permissions</label>
                        <div class="role-permission-box">
                            @foreach ($this->groupedPermissions as $module => $permissions)
                                <div class="mb-2">
                                    <strong class="text-uppercase small text-secondary">{{ $module }}</strong>
                                    <div class="row">
                                        @foreach ($permissions as $permission)
                                            <div class="col-md-6">
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input"
                                                           id="perm-{{ $permission->id }}"
                                                           value="{{ $permission->name }}"
                                                           wire:model="selectedPermissions">
                                                    <label class="form-check-label small" for="perm-{{ $permission->id }}">
                                                        {{ $permission->name }}
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @error('selectedPermissions') <div class="text-danger small">{{ $message }}</div> @enderror
                        {{-- Array-indexed rule failures land under keys like
                             'selectedPermissions.0', not 'selectedPermissions' —
                             the wildcard below is required to surface them,
                             otherwise a failed selection fails validate()
                             silently with no visible error. --}}
                        @error('selectedPermissions.*') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModals" data-en="Cancel" data-bn="বাতিল">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="saveRole" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="saveRole" data-en="Save" data-bn="সংরক্ষণ করুন">Save</span>
                            <span wire:loading wire:target="saveRole" data-en="Please wait..." data-bn="অপেক্ষা করুন...">Please wait...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ══ Role View Modal ═════════════════════════════════════════════════ --}}
    @if ($showRoleViewModal && $viewingRole)
        <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5)">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $viewingRole->name }}</h5>
                        <button type="button" class="btn-close" wire:click="closeModals"></button>
                    </div>
                    <div class="modal-body">
                        <p><strong data-en="Total Users:" data-bn="মোট ইউজার:">Total Users:</strong> {{ $viewingRole->users_count }}</p>
                        <p><strong data-en="Permissions:" data-bn="পারমিশন:">Permissions:</strong></p>
                        @forelse ($viewingRole->permissions as $permission)
                            <span class="inv-badge paid me-1 mb-1">{{ $permission->name }}</span>
                        @empty
                            <span class="text-secondary" style="font-size:13px;" data-en="No permissions assigned." data-bn="কোনো পারমিশন নির্ধারণ করা হয়নি।">No permissions assigned.</span>
                        @endforelse
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModals" data-en="Close" data-bn="বন্ধ করুন">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ══ Role Delete Confirmation ════════════════════════════════════════ --}}
    @if ($showRoleDeleteModal)
        <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5)">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title text-danger" data-en="Confirm Delete" data-bn="মুছে ফেলা নিশ্চিত করুন">Confirm Delete</h5>
                        <button type="button" class="btn-close" wire:click="closeModals"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-0" data-en="Are you sure you want to delete this role?" data-bn="আপনি কি নিশ্চিত এই রোলটি মুছে ফেলতে চান?">Are you sure you want to delete this role?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModals" data-en="Cancel" data-bn="বাতিল">Cancel</button>
                        <button type="button" class="btn btn-danger" wire:click="deleteRole" wire:loading.attr="disabled" data-en="Yes, Delete" data-bn="হ্যাঁ, মুছে ফেলুন">
                            Yes, Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ══ Permission Create Modal ═════════════════════════════════════════ --}}
    @if ($showPermissionModal)
        <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5)">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" data-en="New Permission" data-bn="নতুন পারমিশন">New Permission</h5>
                        <button type="button" class="btn-close" wire:click="closeModals"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label" data-en="Module *" data-bn="মডিউল *">Module *</label>
                                <input type="text" class="form-control" wire:model="permissionModule"
                                       data-en-ph="e.g. mpo" data-bn-ph="যেমন: mpo"
                                       placeholder="e.g. mpo">
                                <small class="text-secondary" data-en="Lowercase letters, numbers, and hyphens only" data-bn="শুধুমাত্র ছোট হাতের অক্ষর, সংখ্যা এবং হাইফেন ব্যবহার করুন">Lowercase letters, numbers, and hyphens only</small>
                                @error('permissionModule') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label" data-en="Action *" data-bn="অ্যাকশন *">Action *</label>
                                <input type="text" class="form-control" wire:model="permissionAction"
                                       data-en-ph="e.g. approve" data-bn-ph="যেমন: approve"
                                       placeholder="e.g. approve">
                                @error('permissionAction') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <p class="text-secondary small mb-0">
                            {{-- Preview now includes the 'ministry.' prefix that
                                 will actually be saved, so the admin isn't
                                 surprised by the stored name. --}}
                            <span data-en="Permission name will be:" data-bn="পারমিশনের নাম হবে:">Permission name will be:</span> <code>ministry.{{ strtolower($permissionModule ?: 'module') }}.{{ strtolower($permissionAction ?: 'action') }}</code>
                        </p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModals" data-en="Cancel" data-bn="বাতিল">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="savePermission" wire:loading.attr="disabled" data-en="Save" data-bn="সংরক্ষণ করুন">
                            Save
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ══ Permission Delete Confirmation ══════════════════════════════════ --}}
    @if ($showPermissionDeleteModal)
        <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5)">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title text-danger" data-en="Confirm Delete" data-bn="মুছে ফেলা নিশ্চিত করুন">Confirm Delete</h5>
                        <button type="button" class="btn-close" wire:click="closeModals"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-0" data-en="Are you sure you want to delete this permission?" data-bn="আপনি কি নিশ্চিত এই পারমিশনটি মুছে ফেলতে চান?">Are you sure you want to delete this permission?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModals" data-en="Cancel" data-bn="বাতিল">Cancel</button>
                        <button type="button" class="btn btn-danger" wire:click="deletePermission" wire:loading.attr="disabled" data-en="Yes, Delete" data-bn="হ্যাঁ, মুছে ফেলুন">
                            Yes, Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @push('styles')
        <style>
            .role-wrap { background: var(--body-bg); min-height: 100vh; }

            .role-add-btn {
                background: linear-gradient(195deg, #444, #111); color: #fff; border: none;
                border-radius: var(--radius-btn); padding: 7px 14px; font-size: 12px; font-weight: 600;
                display: inline-flex; align-items: center; gap: 4px;
                box-shadow: 0 4px 14px var(--primary-shadow);
            }

            .role-pill-row { display: flex; flex-wrap: wrap; gap: 8px; }
            .role-pill {
                display: inline-flex; align-items: center; gap: 6px;
                background: var(--card); border: 1px solid var(--border);
                border-radius: 999px; padding: 6px 14px;
                font-size: 12px; font-weight: 500; color: var(--val);
                cursor: pointer;
            }
            .role-pill.active { background: var(--primary); border-color: var(--primary); color: #fff; }

            .role-table-card {
                background: var(--card); border: 1px solid var(--border);
                border-radius: var(--radius-card); box-shadow: var(--shadow);
                overflow: hidden;
            }
            .role-module-title {
                font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: .03em;
                color: var(--lbl); padding: 12px 16px 0;
            }
            .role-table thead th {
                font-size: 11px; text-transform: uppercase; letter-spacing: .03em;
                color: var(--lbl); border-bottom: 1px solid var(--border);
                padding: 10px 16px; white-space: nowrap;
            }
            .role-table tbody td {
                padding: 10px 16px; border-bottom: 1px solid var(--border); font-size: 13px;
            }
            .role-table tbody tr:last-child td { border-bottom: none; }

            .role-icon-btn {
                width: 28px; height: 28px; border-radius: 7px;
                border: 1px solid var(--border); background: transparent; color: var(--lbl);
                display: inline-flex; align-items: center; justify-content: center;
                margin-left: 2px;
            }
            .role-icon-btn.primary { color: var(--primary); border-color: var(--primary); }
            .role-icon-btn.danger { color: #ef4444; border-color: #ef4444; }

            .role-permission-box {
                border: 1px solid var(--border); border-radius: 10px; padding: 10px;
                max-height: 320px; overflow-y: auto; background: var(--section-bg);
            }

            .inv-badge {
                display: inline-block; padding: 3px 10px; border-radius: 4px;
                font-size: 11px; font-weight: 600; border: 1px solid transparent;
            }
            .inv-badge.paid    { background: transparent; border-color: #22c55e; color: #22c55e; }
            .inv-badge.unpaid  { background: transparent; border-color: #ef4444; color: #ef4444; }
            .inv-badge.pending { background: transparent; border-color: #d97706; color: #d97706; }
        </style>
    @endpush
</div>