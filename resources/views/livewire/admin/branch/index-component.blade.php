{{-- resources/views/livewire/admin/branch/branch-component.blade.php --}}
<div>

    <div class="card">

        <!-- floating header -->
        <div class="mat-card-header header-primary-gradient">
            <h5 id="cardHeaderTitleAllsections">Branches</h5>
            <p id="cardHeaderSubtitle">Manage your institution's branches / campuses.</p>
        </div>

        <div class="card-header border-0">
            <!-- toolbar -->
            <div class="card-toolbar">
                {{-- Left side --}}
                <div class="card-toolbar-title">
                    <!-- search in table -->
                    <div style="position:relative;display:inline-flex;align-items:center">
                        <span class="material-icons-round" style="position:absolute;left:10px;font-size:17px;color:var(--muted);pointer-events:none">search</span>
                        <input type="text" wire:model.live.debounce.300ms="search" id="tableSearch" placeholder="Search name or code" class="tb-search"/>
                    </div>
                </div>

                <!-- Right Side -->
                <div class="col-md-2">
                    <select class="form-select form-select-sm" wire:model.live="filterStatus">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                @if($branches->total() > 10)
                    <div class="col-md-2">
                        <select class="form-select form-select-sm" wire:model.live="perPage">
                            <option value="10">10 / page</option>
                            <option value="25">25 / page</option>
                            <option value="50">50 / page</option>
                        </select>
                    </div>
                @endif
                <button class="btn btn-primary" wire:click="openCreate">
                    <span class="material-icons-round">add_circle</span> <span id="newSectionBtn">Add Branch</span>
                </button>
            </div>
        </div>

        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th id="th-sl">SL</th>
                            <th id="th-name">Branch</th>
                            <th id="th-code">Code</th>
                            <th id="th-contact">Contact</th>
                            <th id="th-status">Status</th>
                            <th id="th-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($branches as $i => $branch)
                        <tr wire:key="branch-{{ $branch->id }}">
                            <td class="text-muted">{{ $branches->firstItem() + $i }}</td>

                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar-placeholder {{ $branch->is_main ? 'branch-avatar-main' : 'branch-avatar-default' }}">
                                        <span class="material-icons-round" style="font-size:1rem;">store</span>
                                    </div>
                                    <div>
                                        <div class="fw-500 text-dark">
                                            {{ $branch->name }}
                                            @if($branch->is_main)
                                                <span class="badge bg-primary-subtle text-primary ms-1" style="font-size:.65rem;">Main</span>
                                            @endif
                                        </div>
                                        <small class="text-muted">{{ Str::limit($branch->address, 40) ?: '—' }}</small>
                                    </div>
                                </div>
                            </td>

                            <td class="text-muted" style="font-size:.8rem;">{{ $branch->code }}</td>

                            <td class="text-muted" style="font-size:.78rem;">
                                {{ $branch->phone ?: '—' }}
                                @if($branch->email)
                                    <br><span>{{ $branch->email }}</span>
                                @endif
                            </td>

                            <td>
                                <span class="badge rounded-pill {{ $branch->is_active ? 'badge-active' : 'badge-inactive' }}" style="font-size:.72rem;">
                                    {{ $branch->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>

                            <td>
                                <div class="d-flex gap-1">
                                    <button class="act-btn view" title="View" wire:click="openView({{ $branch->id }})">
                                        <span class="material-icons-round">visibility</span>
                                    </button>
                                    <button class="act-btn edit" title="Edit" wire:click="openEdit({{ $branch->id }})">
                                        <span class="material-icons-round">drive_file_rename_outline</span>
                                    </button>
                                    <button
                                        class="act-btn status {{ $branch->is_active ? 'btn-warning' : 'btn-success' }}"
                                        title="{{ $branch->is_main ? 'Main Branch cannot be deactivated' : 'Toggle Status' }}"
                                        wire:click="toggleStatus({{ $branch->id }})"
                                        @if($branch->is_main) disabled @endif
                                    >
                                        <span class="material-icons-round">{{ $branch->is_active ? 'toggle_off' : 'toggle_on' }}</span>
                                    </button>
                                    <button
                                        class="act-btn delete"
                                        title="{{ $branch->is_main ? 'Main Branch cannot be deleted' : 'Delete' }}"
                                        wire:click="confirmDeleteRecord({{ $branch->id }})"
                                        @if($branch->is_main) disabled @endif
                                    >
                                        <span class="material-icons-round">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox display-5 d-block mb-2 opacity-25"></i>
                                No branches found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer border-0 bg-white d-flex align-items-center justify-content-between flex-wrap gap-2 py-2 px-3">
            <small class="text-muted">Showing {{ $branches->firstItem() ?? 0 }}–{{ $branches->lastItem() ?? 0 }} of {{ $branches->total() }}</small>
            {{ $branches->links('vendor.pagination.custom') }}
        </div>

    </div>

    {{-- ===== CREATE/EDIT MODAL ===== --}}
    @if($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5);" wire:ignore.self>
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="bi bi-shop me-2 text-danger"></i>
                            {{ $editId ? 'Edit' : 'Create' }} Branch
                        </h5>
                        <button type="button" class="btn-close" wire:click="$set('showModal',false)"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="save">
                            <div class="row g-3">

                                {{-- Name + Code --}}
                                <div class="col-md-8">
                                    <label class="form-label">Branch Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" wire:model.defer="name" placeholder="e.g. Mirpur Branch">
                                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Branch Code <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('code') is-invalid @enderror" wire:model.defer="code" placeholder="e.g. MIR" style="text-transform:uppercase;">
                                    @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                {{-- Address --}}
                                <div class="col-12">
                                    <label class="form-label">Address <span class="text-muted" style="font-weight:400;">(optional)</span></label>
                                    <textarea class="form-control @error('address') is-invalid @enderror" wire:model.defer="address" rows="2" placeholder="Branch address"></textarea>
                                    @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                {{-- Phone + Email --}}
                                <div class="col-md-6">
                                    <label class="form-label">Phone <span class="text-muted" style="font-weight:400;">(optional)</span></label>
                                    <input type="text" class="form-control @error('phone') is-invalid @enderror" wire:model.defer="phone" placeholder="01XXXXXXXXX">
                                    @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Email <span class="text-muted" style="font-weight:400;">(optional)</span></label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" wire:model.defer="email" placeholder="branch@school.com">
                                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                {{-- Status --}}
                                <div class="col-12">
                                    <label class="form-label d-block mb-2">Status</label>
                                    @if($editIsMain)
                                        <span class="badge bg-primary-subtle text-primary">Main Branch is always Active</span>
                                    @else
                                        <div class="d-flex gap-3 flex-wrap">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" wire:model.defer="is_active" value="1" id="status_active">
                                                <label class="form-check-label" for="status_active">Active</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" wire:model.defer="is_active" value="0" id="status_inactive">
                                                <label class="form-check-label" for="status_inactive">Inactive</label>
                                            </div>
                                        </div>
                                    @endif
                                </div>

                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" wire:click="$set('showModal',false)">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="save" wire:loading.attr="disabled">
                            <span wire:loading wire:target="save" class="spinner-border spinner-border-sm me-1"></span>
                            {{ $editId ? 'Update' : 'Create' }} Branch
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
                        <h5 class="modal-title">Branch Details</h5>
                        <button class="btn-close" wire:click="$set('showViewModal',false)"></button>
                    </div>
                    <div class="modal-body">

                        <div style="border-left:4px solid #2563eb;padding:12px 16px;background:#f8f9fa;border-radius:0 8px 8px 0;margin-bottom:16px;">
                            <div style="font-size:.7rem;font-weight:600;color:#2563eb;text-transform:uppercase;letter-spacing:.05em;">
                                {{ $viewRecord->code }}
                            </div>
                            <div style="font-weight:700;font-size:.95rem;margin-top:4px;">
                                {{ $viewRecord->name }}
                                @if($viewRecord->is_main)
                                    <span class="badge bg-primary-subtle text-primary ms-1" style="font-size:.65rem;">Main</span>
                                @endif
                            </div>
                        </div>

                        <table class="table table-sm">
                            <tr>
                                <th class="text-muted" style="width:40%">Address</th>
                                <td>{{ $viewRecord->address ?: '—' }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Phone</th>
                                <td>{{ $viewRecord->phone ?: '—' }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Email</th>
                                <td>{{ $viewRecord->email ?: '—' }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Status</th>
                                <td>
                                    <span class="badge rounded-pill {{ $viewRecord->is_active ? 'badge-active' : 'badge-inactive' }}">
                                        {{ $viewRecord->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
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
                        <h6 class="fw-700">Delete Branch?</h6>
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