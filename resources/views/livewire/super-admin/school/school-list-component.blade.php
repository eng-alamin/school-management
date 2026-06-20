{{-- resources/views/livewire/super-admin/school/school-list-component.blade.php --}}
<div>

    <div class="card">

        <!-- floating header -->
        <div class="mat-card-header header-pink-gradient">
            <h5 id="cardHeaderTitleAllsections">Manage Schools</h5>
            <p id="cardHeaderSubtitle">Manage all registered schools on the platform.</p>
        </div>

        <div class="card-header border-0">
            <div class="card-toolbar">
                <div class="card-toolbar-title">
                    <div style="position:relative;display:inline-flex;align-items:center">
                        <span class="material-icons-round" style="position:absolute;left:10px;font-size:17px;color:var(--muted);pointer-events:none">search</span>
                        <input type="text" wire:model.live.debounce.300ms="search" id="tableSearch" placeholder="Search" style="border:1px solid rgba(0,0,0,.1);border-radius:8px;padding:7px 12px 7px 32px;font-size:.78rem;font-family:inherit;color:var(--dark);outline:none;background:#f8f9fa;width:220px"/>
                    </div>
                </div>

                <div class="col-md-2">
                    <select class="form-select form-select-sm" wire:model.live="filterStatus">
                        <option value="">All Status</option>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>

                @if($schools->total() > 10)
                    <div class="col-md-2">
                        <select class="form-select form-select-sm" wire:model.live="perPage">
                            <option value="10">10 / page</option>
                            <option value="25">25 / page</option>
                            <option value="50">50 / page</option>
                        </select>
                    </div>
                @endif

                <button class="btn-outline bg-dark text-white" wire:click="openCreate">
                    <span class="material-icons-round">add</span> <span id="newSectionBtn">Add School</span>
                </button>
            </div>
        </div>

        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>SL</th>
                            <th>School</th>
                            <th>Admin</th>
                            <th>Address</th>
                            <th>Phone</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($schools as $i => $school)
                        <tr>
                            <td class="text-muted">{{ $schools->firstItem() + $i }}</td>

                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    @if($school->system_logo)
                                        <img src="{{ asset('storage/' . $school->system_logo) }}" alt="{{ $school->name }}" class="avatar-placeholder" style="object-fit:cover;">
                                    @else
                                        <div class="avatar-placeholder">
                                            <span class="material-icons-round" style="font-size:1rem;">school</span>
                                        </div>
                                    @endif
                                    <div>
                                        <div class="fw-500 text-dark">{{ $school->name }}</div>
                                        @if($school->email)
                                            <small class="text-muted">{{ Str::limit($school->email, 40) }}</small>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    @if($school->admin?->avarter)
                                        <img src="{{ asset('storage/' . $school->admin?->avarter) }}" alt="{{ $school->admin?->name }}" class="avatar-placeholder" style="object-fit:cover;">
                                    @else
                                        <div class="avatar-placeholder">
                                            <span class="material-icons-round" style="font-size:1rem;">account_circle</span>
                                        </div>
                                    @endif
                                    <div>
                                        <div class="fw-500 text-dark">{{ $school->admin?->name }}</div>
                                        @if($school->admin?->email)
                                            <small class="text-muted">{{ Str::limit($school->admin->email, 40) }}</small>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <td class="text-muted" style="font-size:.8rem;">{{ $school->address ? Str::limit($school->address, 40) : '-' }}</td>

                            <td class="text-muted" style="font-size:.8rem;">{{ $school->phone ?? '—' }}</td>

                            <td>
                                <span class="badge rounded-pill {{ $school->status ? 'badge-active' : 'badge-inactive' }}" style="font-size:.72rem;">
                                    {{ $school->status ? 'Active' : 'Inactive' }}
                                </span>
                            </td>

                            <td class="text-muted" style="font-size:.78rem;">
                                {{ $school->created_at->format('d M Y') }}
                            </td>

                            <td>
                                <div class="d-flex gap-1">
                                    <button class="act-btn view" title="View" wire:click="openView({{ $school->id }})">
                                        <span class="material-icons-round">visibility</span>
                                    </button>
                                    <button class="act-btn edit" title="Edit" wire:click="openEdit({{ $school->id }})">
                                        <span class="material-icons-round">drive_file_rename_outline</span>
                                    </button>
                                    <button class="act-btn status {{ $school->status ? 'btn-warning' : 'btn-success' }}" title="Toggle Status" wire:click="toggleStatus({{ $school->id }})">
                                        <span class="material-icons-round">{{ $school->status ? 'toggle_off' : 'toggle_on' }}</span>
                                    </button>
                                    <button class="act-btn delete" title="Delete" wire:click="confirmDeleteRecord({{ $school->id }})">
                                        <span class="material-icons-round">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox display-5 d-block mb-2 opacity-25"></i>
                                No schools found. <a href="#" wire:click.prevent="openCreate">Create one now</a>.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer border-0 bg-white d-flex align-items-center justify-content-between flex-wrap gap-2 py-2 px-3">
            <small class="text-muted">Showing {{ $schools->firstItem() ?? 0 }}–{{ $schools->lastItem() ?? 0 }} of {{ $schools->total() }}</small>
            {{ $schools->links('vendor.pagination.custom') }}
        </div>

    </div>

    {{-- ===== CREATE/EDIT MODAL ===== --}}
    @if($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5);" wire:ignore.self>
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="bi bi-building me-2 text-danger"></i>
                            {{ $editId ? 'Edit' : 'Create' }} School
                        </h5>
                        <button type="button" class="btn-close" wire:click="$set('showModal',false)"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="save">
                            <div class="row g-3">

                                {{-- Name --}}
                                <div class="col-12">
                                    <label class="form-label">School Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" wire:model.defer="name" placeholder="e.g. Green Valley High School">
                                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                {{-- Email + Phone --}}
                                <div class="col-md-6">
                                    <label class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" wire:model.defer="email" placeholder="school@example.com">
                                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Phone <span class="text-muted" style="font-weight:400;">(optional)</span></label>
                                    <input type="text" class="form-control @error('phone') is-invalid @enderror" wire:model.defer="phone" placeholder="+880...">
                                    @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                {{-- Address --}}
                                <div class="col-12">
                                    <label class="form-label">Address <span class="text-muted" style="font-weight:400;">(optional)</span></label>
                                    <textarea class="form-control @error('address') is-invalid @enderror" wire:model.defer="address" rows="3" placeholder="Full address..."></textarea>
                                    @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                {{-- Logo --}}
                                <div class="col-md-8">
                                    <label class="form-label">Logo <span class="text-muted" style="font-weight:400;">(Image — max 2MB)</span></label>
                                    <input type="file" class="form-control @error('logo') is-invalid @enderror" wire:model="logo" accept="image/*">
                                    @error('logo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    <div wire:loading wire:target="logo" class="text-muted mt-1" style="font-size:.78rem;">
                                        <span class="material-icons-round" style="font-size:.85rem;vertical-align:middle;">hourglass_empty</span> Uploading...
                                    </div>

                                    @if($logo)
                                        <div class="mt-2">
                                            <img src="{{ $logo->temporaryUrl() }}" style="height:48px;width:48px;object-fit:cover;border-radius:8px;border:1px solid var(--border);">
                                        </div>
                                    @elseif($existingLogo)
                                        <div class="d-flex align-items-center gap-2 mt-2 p-2" style="background:#f8f9fa;border-radius:8px;border:1px solid var(--border);">
                                            <img src="{{ asset('storage/' . $existingLogo) }}" style="height:32px;width:32px;object-fit:cover;border-radius:6px;">
                                            <span style="font-size:.78rem;flex:1;">Current logo</span>
                                            <button type="button" wire:click="removeLogo" class="btn btn-sm btn-link text-danger p-0">
                                                <span class="material-icons-round" style="font-size:.9rem;">close</span>
                                            </button>
                                        </div>
                                    @endif
                                </div>

                                {{-- Status --}}
                                <div class="col-12">
                                    <label class="form-label d-block mb-2">Status</label>
                                    <div class="d-flex gap-3 flex-wrap">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" wire:model.defer="status" value="1" id="status_active">
                                            <label class="form-check-label" for="status_active">Active</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" wire:model.defer="status" value="0" id="status_inactive">
                                            <label class="form-check-label" for="status_inactive">Inactive</label>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" wire:click="$set('showModal',false)">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="save" wire:loading.attr="disabled">
                            <span wire:loading wire:target="save" class="spinner-border spinner-border-sm me-1"></span>
                            {{ $editId ? 'Update' : 'Create' }} School
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ===== VIEW MODAL ===== --}}
    @if($showViewModal && $viewRecord)
        <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">School Details</h5>
                        <button class="btn-close" wire:click="$set('showViewModal',false)"></button>
                    </div>
                    <div class="modal-body">

                        <div class="d-flex align-items-center gap-3 mb-3">
                            @if($viewRecord->system_logo)
                                <img src="{{ asset('storage/' . $viewRecord->system_logo) }}" style="height:56px;width:56px;object-fit:cover;border-radius:10px;border:1px solid var(--border);">
                            @else
                                <div class="avatar-placeholder" style="width:56px;height:56px;">
                                    <span class="material-icons-round">school</span>
                                </div>
                            @endif
                            <div>
                                <div style="font-weight:700;font-size:.95rem;">{{ $viewRecord->name }}</div>
                                <span class="badge rounded-pill {{ $viewRecord->status ? 'badge-active' : 'badge-inactive' }}" style="font-size:.72rem;">
                                    {{ $viewRecord->status ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                        </div>

                        <table class="table table-sm">
                            <tr>
                                <th class="text-muted" style="width:40%">Email</th>
                                <td>{{ $viewRecord->email }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Phone</th>
                                <td>{{ $viewRecord->phone ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Address</th>
                                <td>{{ $viewRecord->address ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Created</th>
                                <td>{{ $viewRecord->created_at->format('d M Y') }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-light" wire:click="$set('showViewModal',false)">Close</button>
                        <button class="btn btn-primary" wire:click="openEdit({{ $viewRecord->id }}); $set('showViewModal',false)">
                            <i class="bi bi-pencil me-1"></i>Edit
                        </button>
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
                        <h6 class="fw-700">Delete School?</h6>
                        <p class="text-muted small">This action cannot be undone.</p>
                    </div>
                    <div class="modal-footer justify-content-center border-0 pt-0">
                        <button class="btn btn-light btn-sm" wire:click="$set('confirmDelete',false)">Cancel</button>
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


@push('styles')
    <style>
        .badge-active   { background: rgba(34,197,94,.12);  color: #16a34a; }
        .badge-inactive { background: rgba(107,114,128,.12); color: #6b7280; }

        .avatar-placeholder {
            width: 38px; height: 38px; border-radius: 8px;
            background: var(--primary-light); color: var(--primary);
            display: inline-flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: .875rem;
        }

        .form-label { font-size: .8rem; font-weight: 600; color: var(--text-muted); margin-bottom: 4px; }
        .form-control, .form-select {
            border-radius: 8px; border: 1px solid var(--border);
            font-size: .875rem; padding: .45rem .75rem;
            transition: border-color .2s, box-shadow .2s;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-light);
        }
        .form-check-input:checked { background-color: var(--primary); border-color: var(--primary); }

        .btn-primary { background: var(--primary); border-color: var(--primary); }
        .btn-primary:hover, .btn-primary:focus { background: #d63e3e; border-color: #d63e3e; }
        .btn-sm { font-size: .78rem; padding: .3rem .65rem; border-radius: 6px; }
    </style>
@endpush