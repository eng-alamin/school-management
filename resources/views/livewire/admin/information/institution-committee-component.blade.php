{{-- resources/views/livewire/admin/information/committee-member-component.blade.php --}}
<div>

    <div class="card">

        <!-- floating header -->
        <div class="mat-card-header header-primary-gradient">
            <h5 id="cardHeaderTitleAllsections">Committee Member</h5>
            <p id="cardHeaderSubtitle">Manage school committee members, designations, and terms.</p>
        </div>

        <div class="card-header border-0">
            <!-- toolbar -->
            <div class="card-toolbar">
                {{-- Left side --}}
                <div class="card-toolbar-title">
                    <!-- search in table -->
                    <div style="position:relative;display:inline-flex;align-items:center">
                        <span class="material-icons-round" style="position:absolute;left:10px;font-size:17px;color:var(--muted);pointer-events:none">search</span>
                        <input type="text" wire:model.live.debounce.300ms="search" id="tableSearch" placeholder="Search by name or designation" class="tb-search"/>
                    </div>
                </div>

                <!-- Right Side -->
                <div class="col-md-2">
                    <select class="form-select form-select-sm" wire:model.live="filterStatus">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="former">Former</option>
                    </select>
                </div>
                @if($committees->total() > 10)
                    <div class="col-md-2">
                        <select class="form-select form-select-sm" wire:model.live="perPage">
                            <option value="10">10 / page</option>
                            <option value="25">25 / page</option>
                            <option value="50">50 / page</option>
                        </select>
                    </div>
                @endif
                <button class="btn btn-primary" wire:click="openAddModal">
                    <span class="material-icons-round">add</span> <span id="newSectionBtn">Add Member</span>
                </button>
            </div>
        </div>

        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th id="th-sl">SL</th>
                            <th id="th-member">Member</th>
                            <th id="th-contact">Contact</th>
                            <th id="th-term">Term</th>
                            <th id="th-status">Status</th>
                            <th id="th-order">Order</th>
                            <th id="th-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($committees as $i => $member)
                        <tr wire:key="member-{{ $member->id }}">
                            <td class="text-muted">{{ $committees->firstItem() + $i }}</td>

                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $member->photo ? asset('storage/' . $member->photo) : 'https://ui-avatars.com/api/?name=' . urlencode($member->name) . '&size=64&background=random' }}"
                                        alt="{{ $member->name }}"
                                        class="avatar-placeholder member-avatar-{{ $member->status }}"
                                        width="38" height="38" alt="{{ $member->name }}">
                                    <div>
                                        <div class="fw-500 text-dark">{{ $member->name }}</div>
                                        <small class="text-muted">{{ $member->designation }}</small>
                                    <div>
                                </div>
                            </td>

                            <td>
                                <div class="small">{{ $member->phone ?: '-' }}</div>
                                <div class="small text-muted">{{ $member->email ?: '-' }}</div>
                            </td>

                            <td class="text-muted" style="font-size:.78rem;">
                                @if ($member->term_start_date || $member->term_end_date)
                                    {{ $member->term_start_date?->format('d M Y') ?? '—' }}
                                    to
                                    {{ $member->term_end_date?->format('d M Y') ?? 'present' }}
                                @else
                                    —
                                @endif
                            </td>

                            <td>
                                <span class="badge rounded-pill {{ $member->status === 'active' ? 'badge-active' : 'badge-inactive' }}" style="font-size:.72rem;">
                                    {{ $member->status === 'active' ? 'Active' : 'Former' }}
                                </span>
                            </td>

                            <td>
                                <div class="d-flex gap-1">
                                    <button class="btn btn-sm btn-outline-secondary" wire:click="moveUp({{ $member->id }})" title="Move Up">
                                        <span class="material-icons-round" style="font-size:16px;">arrow_upward</span>
                                    </button>
                                    <button class="btn btn-sm btn-outline-secondary" wire:click="moveDown({{ $member->id }})" title="Move Down">
                                        <span class="material-icons-round" style="font-size:16px;">arrow_downward</span>
                                    </button>
                                </div>
                            </td>

                            <td>
                                <div class="d-flex gap-1">
                                    <button class="act-btn view" title="View" wire:click="openView({{ $member->id }})">
                                        <span class="material-icons-round">visibility</span>
                                    </button>
                                    <button class="act-btn edit" title="Edit" wire:click="openEditModal({{ $member->id }})">
                                        <span class="material-icons-round">drive_file_rename_outline</span>
                                    </button>
                                    <button class="act-btn status {{ $member->status === 'active' ? 'btn-warning' : 'btn-success' }}" title="Toggle Status" wire:click="toggleStatus({{ $member->id }})">
                                        <span class="material-icons-round">{{ $member->status === 'active' ? 'toggle_off' : 'toggle_on' }}</span>
                                    </button>
                                    <button class="act-btn delete" title="Delete" wire:click="confirmDeleteRecord({{ $member->id }})">
                                        <span class="material-icons-round">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-people display-5 d-block mb-2 opacity-25"></i>
                                No committee members found. <a href="#" wire:click.prevent="openAddModal">Add one now</a>.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer border-0 bg-white d-flex align-items-center justify-content-between flex-wrap gap-2 py-2 px-3">
            <small class="text-muted">Showing {{ $committees->firstItem() ?? 0 }}–{{ $committees->lastItem() ?? 0 }} of {{ $committees->total() }}</small>
            {{ $committees->links('vendor.pagination.custom') }}
        </div>

    </div>

    {{-- ===== CREATE/EDIT MODAL ===== --}}
    @if($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5);" wire:ignore.self>
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="bi bi-person-badge me-2 text-danger"></i>
                            {{ $editingId ? 'Edit' : 'Add' }} Committee Member
                        </h5>
                        <button type="button" class="btn-close" wire:click="$set('showModal',false)"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="save">
                            <div class="row g-3">

                                {{-- Name + Designation --}}
                                <div class="col-md-6">
                                    <label class="form-label">Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" wire:model.defer="name" placeholder="e.g. Md. Rahim Uddin">
                                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Designation <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('designation') is-invalid @enderror" wire:model.defer="designation" placeholder="e.g. President, Secretary, Member">
                                    @error('designation') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                {{-- Phone + Email --}}
                                <div class="col-md-6">
                                    <label class="form-label">Phone</label>
                                    <input type="text" class="form-control @error('phone') is-invalid @enderror" wire:model.defer="phone">
                                    @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" wire:model.defer="email">
                                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                {{-- Term dates --}}
                                <div class="col-md-6">
                                    <label class="form-label">Term Start Date</label>
                                    <input type="date" class="form-control @error('term_start_date') is-invalid @enderror" wire:model.defer="term_start_date">
                                    @error('term_start_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Term End Date <span class="text-muted" style="font-weight:400;">(optional)</span></label>
                                    <input type="date" class="form-control @error('term_end_date') is-invalid @enderror" wire:model.defer="term_end_date">
                                    @error('term_end_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                {{-- Photo --}}
                                <div class="col-md-8">
                                    <label class="form-label">Photo <span class="text-muted" style="font-weight:400;">(Image — max 2MB)</span></label>
                                    <input type="file" class="form-control @error('photo') is-invalid @enderror" wire:model="photo" accept="image/*">
                                    @error('photo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    <div wire:loading wire:target="photo" class="text-muted mt-1" style="font-size:.78rem;">
                                        <span class="material-icons-round" style="font-size:.85rem;vertical-align:middle;">hourglass_empty</span> Uploading...
                                    </div>
                                    @if($photo)
                                        <img src="{{ $photo->temporaryUrl() }}" class="mt-2 rounded" width="60">
                                    @elseif($existingPhoto)
                                        <img src="{{ asset('storage/' . $existingPhoto) }}" class="mt-2 rounded" width="60">
                                    @endif
                                </div>

                                {{-- Status --}}
                                <div class="col-12">
                                    <label class="form-label d-block mb-2">Status</label>
                                    <div class="d-flex gap-3 flex-wrap">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" wire:model.defer="status" value="active" id="status_active">
                                            <label class="form-check-label" for="status_active">Active</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" wire:model.defer="status" value="former" id="status_former">
                                            <label class="form-check-label" for="status_former">Former</label>
                                        </div>
                                    </div>
                                </div>

                                {{-- Address --}}
                                <div class="col-12">
                                    <label class="form-label">Address</label>
                                    <textarea class="form-control @error('address') is-invalid @enderror" wire:model.defer="address" rows="2"></textarea>
                                    @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" wire:click="$set('showModal',false)">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="save" wire:loading.attr="disabled">
                            <span wire:loading wire:target="save" class="spinner-border spinner-border-sm me-1"></span>
                            {{ $editingId ? 'Update' : 'Save' }} Member
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
                        <h5 class="modal-title">Committee Member Details</h5>
                        <button class="btn-close" wire:click="$set('showViewModal',false)"></button>
                    </div>
                    <div class="modal-body">

                        {{-- Status banner --}}
                        @php
                            $bannerColor = $viewRecord->status === 'active' ? '#16a34a' : '#6b7280';
                        @endphp
                        <div class="d-flex align-items-center gap-3" style="border-left:4px solid {{ $bannerColor }};padding:12px 16px;background:#f8f9fa;border-radius:0 8px 8px 0;margin-bottom:16px;">
                            <img src="{{ $viewRecord->photo_url }}" class="rounded-circle" width="52" height="52" style="object-fit:cover;">
                            <div>
                                <div style="font-size:.7rem;font-weight:600;color:{{ $bannerColor }};text-transform:uppercase;letter-spacing:.05em;">
                                    {{ $viewRecord->status === 'active' ? 'Active Member' : 'Former Member' }}
                                </div>
                                <div style="font-weight:700;font-size:.95rem;margin-top:2px;">{{ $viewRecord->name }}</div>
                                <div class="text-muted" style="font-size:.8rem;">{{ $viewRecord->designation }}</div>
                            </div>
                        </div>

                        @if($viewRecord->address)
                            <div style="font-size:.875rem;line-height:1.6;color:#374151;margin-bottom:16px;">
                                {!! nl2br(e($viewRecord->address)) !!}
                            </div>
                        @endif

                        <table class="table table-sm">
                            <tr>
                                <th class="text-muted" style="width:40%">Phone</th>
                                <td>{{ $viewRecord->phone ?: '—' }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Email</th>
                                <td>{{ $viewRecord->email ?: '—' }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Term Start</th>
                                <td>{{ $viewRecord->term_start_date?->format('d M Y') ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Term End</th>
                                <td>{{ $viewRecord->term_end_date?->format('d M Y') ?? 'Present' }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Status</th>
                                <td>
                                    <span class="badge rounded-pill {{ $viewRecord->status === 'active' ? 'badge-active' : 'badge-inactive' }}">
                                        {{ $viewRecord->status === 'active' ? 'Active' : 'Former' }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th class="text-muted">Added By</th>
                                <td>{{ $viewRecord->creator->name ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Added On</th>
                                <td>{{ $viewRecord->created_at->format('d M Y') }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-light" wire:click="$set('showViewModal',false)">Close</button>
                        <button class="btn btn-primary" wire:click="openEditModal({{ $viewRecord->id }}); $set('showViewModal',false)">
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
                        <h6 class="fw-700">Delete Committee Member?</h6>
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
        /* ── AVATAR (photo) ── */
        .avatar-placeholder {
            width: 38px; height: 38px; border-radius: 50%;
            object-fit: cover; border: 2px solid var(--border);
        }
        .member-avatar-active { border-color: #16a34a; }
        .member-avatar-former { border-color: #9ca3af; opacity: .85; }

        /* ── FORM ── */
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

    </style>
@endpush