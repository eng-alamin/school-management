{{-- resources/views/livewire/ministry/user/index-component.blade.php --}}

<div class="usr-wrap">

    {{-- ══ Header ══════════════════════════════════════════════════════════ --}}
    <div class="px-3 pt-3 pb-2">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h5 class="fw-bold mb-0 text-dark" data-en="Ministry User Management" data-bn="মিনিস্ট্রি ইউজার ব্যবস্থাপনা">
                    Ministry User Management
                </h5>
                <p class="text-secondary mb-0" style="font-size:12px;" data-en="Manage internal staff accounts for the Ministry Panel" data-bn="মিনিস্ট্রি প্যানেলের অভ্যন্তরীণ স্টাফ অ্যাকাউন্ট ব্যবস্থাপনা করুন">Manage internal staff accounts for the Ministry Panel</p>
            </div>
            <button type="button" class="usr-add-btn" wire:click="openCreateModal">
                <span class="material-icons-round" style="font-size:16px;">add</span>
                <span data-en="New User" data-bn="নতুন ইউজার">New User</span>
            </button>
        </div>
    </div>

    {{-- ══ Toolbar: Search / Filter / PerPage ═════════════════════════════ --}}
    <div class="px-3 pt-3">
        <div class="usr-filter-card">
            <div class="row g-2 align-items-end">
                <div class="col-12 col-md-4">
                    <label class="usr-filter-label" data-en="Search" data-bn="খুঁজুন">Search</label>
                    <input type="text" class="form-control form-control-sm"
                           data-en-ph="Search by name, username, email or phone..." data-bn-ph="নাম, ইউজারনেম, ইমেইল বা ফোন দিয়ে খুঁজুন..."
                           placeholder="Search by name, username, email or phone..."
                           wire:model.live.debounce.400ms="search">
                </div>
                <div class="col-6 col-md-3">
                    <label class="usr-filter-label" data-en="Role" data-bn="রোল">Role</label>
                    <select class="form-select form-select-sm no-custom-select" wire:model.live="roleFilter">
                        <option value="" data-en="All Roles" data-bn="সকল রোল">All Roles</option>
                        @foreach ($this->ministryRoles as $roleName)
                            <option value="{{ $roleName }}">{{ $roleName }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <label class="usr-filter-label" data-en="Status" data-bn="অবস্থা">Status</label>
                    <select class="form-select form-select-sm no-custom-select" wire:model.live="statusFilter">
                        <option value="" data-en="All Status" data-bn="সকল অবস্থা">All Status</option>
                        <option value="active" data-en="Active" data-bn="সক্রিয়">Active</option>
                        <option value="inactive" data-en="Inactive" data-bn="নিষ্ক্রিয়">Inactive</option>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="usr-filter-label" data-en="Per Page" data-bn="প্রতি পৃষ্ঠায়">Per Page</label>
                    <select class="form-select form-select-sm no-custom-select" wire:model.live="perPage">
                        <option value="10" data-en="10 / page" data-bn="১০ / পৃষ্ঠা">10 / page</option>
                        <option value="25" data-en="25 / page" data-bn="২৫ / পৃষ্ঠা">25 / page</option>
                        <option value="50" data-en="50 / page" data-bn="৫০ / পৃষ্ঠা">50 / page</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- ══ Table ═══════════════════════════════════════════════════════════ --}}
    <div class="px-3 pt-3 pb-4">
        <div class="usr-table-card">

            <div class="table-responsive">
                <table class="table usr-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th role="button" wire:click="sortBy('name')" data-en="Name" data-bn="নাম">Name</th>
                            <th role="button" wire:click="sortBy('username')" data-en="Username" data-bn="ইউজারনেম">Username</th>
                            <th data-en="Email / Phone" data-bn="ইমেইল / ফোন">Email / Phone</th>
                            <th data-en="Role" data-bn="রোল">Role</th>
                            <th role="button" wire:click="sortBy('is_active')" data-en="Status" data-bn="অবস্থা">Status</th>
                            <th class="text-end" data-en="Action" data-bn="অ্যাকশন">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr wire:key="ministry-user-{{ $user->id }}">
                                <td class="d-flex align-items-center gap-2">
                                    <div class="usr-avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
                                    <span class="fw-semibold text-dark" style="font-size:13px;">{{ $user->name }}</span>
                                </td>
                                <td class="text-secondary">{{ $user->username ?? '—' }}</td>
                                <td class="text-secondary">
                                    <div>{{ $user->email ?? '—' }}</div>
                                    <small class="text-secondary">{{ $user->phone ?? '' }}</small>
                                </td>
                                <td>
                                    @foreach ($user->roles as $role)
                                        <span class="inv-badge paid">{{ $role->name }}</span>
                                    @endforeach
                                </td>
                                <td>
                                    @if ($user->is_active)
                                        <span class="inv-badge paid" data-en="Active" data-bn="সক্রিয়">Active</span>
                                    @else
                                        <span class="inv-badge unpaid" data-en="Inactive" data-bn="নিষ্ক্রিয়">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <button type="button" class="usr-icon-btn" title="View"
                                            wire:click="openViewModal({{ $user->id }})">
                                        <span class="material-icons-round" style="font-size:15px;">visibility</span>
                                    </button>
                                    <button type="button" class="usr-icon-btn primary" title="Edit"
                                            wire:click="openEditModal({{ $user->id }})">
                                        <span class="material-icons-round" style="font-size:15px;">edit</span>
                                    </button>
                                    <button type="button" class="usr-icon-btn warning" title="Toggle Status"
                                            wire:click="toggleStatus({{ $user->id }})"
                                            wire:confirm="Change status?">
                                        <span class="material-icons-round" style="font-size:15px;">toggle_on</span>
                                    </button>
                                    <button type="button" class="usr-icon-btn danger" title="Delete"
                                            wire:click="confirmDelete({{ $user->id }})">
                                        <span class="material-icons-round" style="font-size:15px;">delete</span>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-secondary py-4" style="font-size:13px;" data-en="No ministry users found." data-bn="কোনো মিনিস্ট্রি ইউজার পাওয়া যায়নি।">No ministry users found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-3 py-3 border-top">
                {{ $users->links() }}
            </div>

        </div>
    </div>

    {{-- ══ Create / Edit Modal ═════════════════════════════════════════════ --}}
    @if ($showFormModal)
        <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5)">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" data-en="{{ $isEditMode ? 'Edit Ministry User' : 'New Ministry User' }}" data-bn="{{ $isEditMode ? 'মিনিস্ট্রি ইউজার সম্পাদনা' : 'নতুন মিনিস্ট্রি ইউজার' }}">{{ $isEditMode ? 'Edit Ministry User' : 'New Ministry User' }}</h5>
                        <button type="button" class="btn-close" wire:click="closeModals"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label" data-en="Name *" data-bn="নাম *">Name *</label>
                            <input type="text" class="form-control" wire:model="name">
                            @error('name') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label" data-en="Username *" data-bn="ইউজারনেম *">Username *</label>
                            <input type="text" class="form-control" wire:model="username">
                            @error('username') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label" data-en="Email *" data-bn="ইমেইল *">Email *</label>
                            <input type="email" class="form-control" wire:model="email">
                            @error('email') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label" data-en="Phone" data-bn="ফোন">Phone</label>
                            <input type="text" class="form-control" wire:model="phone">
                            @error('phone') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label" data-en="Role *" data-bn="রোল *">Role *</label>
                            <select class="form-select no-custom-select" wire:model="ministryRole">
                                <option value="" data-en="-- Select Role --" data-bn="-- রোল নির্বাচন করুন --">-- Select Role --</option>
                                @foreach ($this->ministryRoles as $roleName)
                                    <option value="{{ $roleName }}">{{ $roleName }}</option>
                                @endforeach
                            </select>
                            @error('ministryRole') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label" data-en="{{ $isEditMode ? 'New Password (optional)' : 'Password *' }}" data-bn="{{ $isEditMode ? 'নতুন পাসওয়ার্ড (ঐচ্ছিক)' : 'পাসওয়ার্ড *' }}">{{ $isEditMode ? 'New Password (optional)' : 'Password *' }}</label>
                                <input type="password" class="form-control" wire:model="password">
                                @error('password') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label" data-en="Confirm Password" data-bn="পাসওয়ার্ড নিশ্চিত করুন">Confirm Password</label>
                                <input type="password" class="form-control" wire:model="password_confirmation">
                            </div>
                        </div>

                        <div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input" role="switch" wire:model="is_active">
                            <label class="form-check-label" data-en="Active" data-bn="সক্রিয়">Active</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModals" data-en="Cancel" data-bn="বাতিল">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="save" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="save" data-en="Save" data-bn="সংরক্ষণ করুন">Save</span>
                            <span wire:loading wire:target="save" data-en="Please wait..." data-bn="অপেক্ষা করুন...">Please wait...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ══ View Modal ══════════════════════════════════════════════════════ --}}
    @if ($showViewModal && $viewingUser)
        <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5)">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" data-en="User Details" data-bn="ইউজারের বিস্তারিত">User Details</h5>
                        <button type="button" class="btn-close" wire:click="closeModals"></button>
                    </div>
                    <div class="modal-body">
                        <table class="table table-borderless mb-0">
                            <tr><th data-en="Name" data-bn="নাম">Name</th><td>{{ $viewingUser->name }}</td></tr>
                            <tr><th data-en="Username" data-bn="ইউজারনেম">Username</th><td>{{ $viewingUser->username ?? '—' }}</td></tr>
                            <tr><th data-en="Email" data-bn="ইমেইল">Email</th><td>{{ $viewingUser->email ?? '—' }}</td></tr>
                            <tr><th data-en="Phone" data-bn="ফোন">Phone</th><td>{{ $viewingUser->phone ?? '—' }}</td></tr>
                            <tr><th data-en="Role" data-bn="রোল">Role</th><td>
                                @foreach ($viewingUser->roles as $role)
                                    <span class="inv-badge paid">{{ $role->name }}</span>
                                @endforeach
                            </td></tr>
                            <tr><th data-en="Status" data-bn="অবস্থা">Status</th><td data-en="{{ $viewingUser->is_active ? 'Active' : 'Inactive' }}" data-bn="{{ $viewingUser->is_active ? 'সক্রিয়' : 'নিষ্ক্রিয়' }}">{{ $viewingUser->is_active ? 'Active' : 'Inactive' }}</td></tr>
                            <tr><th data-en="Last Login" data-bn="সর্বশেষ লগইন">Last Login</th><td>{{ $viewingUser->last_login_at?->format('d M Y, h:i A') ?? '—' }}</td></tr>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModals" data-en="Close" data-bn="বন্ধ করুন">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ══ Delete Confirmation Modal ═══════════════════════════════════════ --}}
    @if ($showDeleteModal)
        <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5)">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title text-danger" data-en="Confirm Delete" data-bn="মুছে ফেলা নিশ্চিত করুন">Confirm Delete</h5>
                        <button type="button" class="btn-close" wire:click="closeModals"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-0" data-en="Are you sure you want to delete this ministry user? This action will be difficult to undo." data-bn="আপনি কি নিশ্চিত এই মিনিস্ট্রি ইউজারকে মুছে ফেলতে চান? এই কাজটি পূর্বাবস্থায় ফেরানো কঠিন হবে।">Are you sure you want to delete this ministry user? This action will be difficult to undo.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModals" data-en="Cancel" data-bn="বাতিল">Cancel</button>
                        <button type="button" class="btn btn-danger" wire:click="delete" wire:loading.attr="disabled" data-en="Yes, Delete" data-bn="হ্যাঁ, মুছে ফেলুন">
                            Yes, Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @push('styles')
        <style>
            .usr-wrap { background: var(--body-bg); min-height: 100vh; }

            .usr-add-btn {
                background: linear-gradient(195deg, #444, #111); color: #fff; border: none;
                border-radius: var(--radius-btn); padding: 7px 14px; font-size: 12px; font-weight: 600;
                display: inline-flex; align-items: center; gap: 4px;
                box-shadow: 0 4px 14px var(--primary-shadow);
            }

            .usr-filter-card {
                background: var(--card); border: 1px solid var(--border);
                border-radius: var(--radius-card); padding: 14px;
                box-shadow: var(--shadow);
            }
            .usr-filter-label { font-size: 11px; color: var(--lbl); margin-bottom: 2px; display: block; }

            .usr-table-card {
                background: var(--card); border: 1px solid var(--border);
                border-radius: var(--radius-card); box-shadow: var(--shadow);
                overflow: hidden;
            }
            .usr-table thead th {
                font-size: 11px; text-transform: uppercase; letter-spacing: .03em;
                color: var(--lbl); border-bottom: 1px solid var(--border);
                padding: 10px 16px; white-space: nowrap;
            }
            .usr-table tbody td {
                padding: 10px 16px; border-bottom: 1px solid var(--border); font-size: 13px;
            }
            .usr-table tbody tr:last-child td { border-bottom: none; }

            .usr-avatar {
                width: 28px; height: 28px; border-radius: 8px;
                background: linear-gradient(135deg, var(--primary), #7ba3ff);
                color: #fff; font-size: 12px; font-weight: 700;
                display: flex; align-items: center; justify-content: center;
                flex-shrink: 0;
            }

            .usr-icon-btn {
                width: 28px; height: 28px; border-radius: 7px;
                border: 1px solid var(--border); background: transparent; color: var(--lbl);
                display: inline-flex; align-items: center; justify-content: center;
                margin-left: 2px;
            }
            .usr-icon-btn.primary { color: var(--primary); border-color: var(--primary); }
            .usr-icon-btn.warning { color: #d97706; border-color: #d97706; }
            .usr-icon-btn.danger  { color: #ef4444; border-color: #ef4444; }

            .inv-badge {
                display: inline-block; padding: 3px 10px; border-radius: 4px;
                font-size: 11px; font-weight: 600; border: 1px solid transparent;
            }
            .inv-badge.paid    { background: transparent; border-color: #22c55e; color: #22c55e; }
            .inv-badge.unpaid  { background: transparent; border-color: #ef4444; color: #ef4444; }
        </style>
    @endpush

    @push('scripts')
        <script>
            document.addEventListener('livewire:init', () => {
                Livewire.hook('morph.updated', () => {
                    setTimeout(() => {
                        if (window.jQuery && jQuery.fn.selectpicker) {
                            jQuery('.selectpicker').selectpicker('destroy');
                            jQuery('.selectpicker').selectpicker();
                        }
                    }, 50);
                });
            });
        </script>
    @endpush
</div>