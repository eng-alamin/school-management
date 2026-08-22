{{-- resources/views/livewire/admin/information/institution-facility-component.blade.php --}}
<div>

    <div class="card">

        <!-- floating header -->
        <div class="mat-card-header header-primary-gradient">
            <h5 id="cardHeaderTitleAllsections">Institution Facilities</h5>
            <p id="cardHeaderSubtitle">Manage the list of facilities available at your institution.</p>
        </div>

        <div class="card-header border-0">
            <!-- toolbar -->
            <div class="card-toolbar">
                {{-- Left side --}}
                <div class="card-toolbar-title">
                    <!-- search in table -->
                    <div style="position:relative;display:inline-flex;align-items:center">
                        <span class="material-icons-round" style="position:absolute;left:10px;font-size:17px;color:var(--muted);pointer-events:none">search</span>
                        <input type="text" wire:model.live.debounce.300ms="search" id="tableSearch" placeholder="Search" class="tb-search"/>
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
                @if($facilities->total() > 10)
                    <div class="col-md-2">
                        <select class="form-select form-select-sm" wire:model.live="perPage">
                            <option value="10">10 / page</option>
                            <option value="25">25 / page</option>
                            <option value="50">50 / page</option>
                        </select>
                    </div>
                @endif
                <button class="btn btn-primary" wire:click="openCreate">
                    <span>
                        <span class="material-icons-round">add</span>
                        <span id="newSectionBtn">Add Facility</span>
                    </span>
                </button>
            </div>
        </div>

        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th id="th-sl">SL</th>
                            <th id="th-name">Facility Name</th>
                            <th id="th-status">Status</th>
                            <th id="th-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($facilities as $i => $facility)
                        <tr wire:key="facility-{{ $facility->id }}">
                            <td class="text-muted">{{ $facilities->firstItem() + $i }}</td>

                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="fw-500 text-dark">{{ $facility->name }}</div>
                                </div>
                            </td>

                            <td>
                                <span class="badge rounded-pill {{ $facility->status === 'active' ? 'badge-active' : 'badge-inactive' }}" style="font-size:.72rem;">
                                    {{ $facility->status === 'active' ? 'Active' : 'Inactive' }}
                                </span>
                            </td>

                            <td>
                                <div class="d-flex gap-1">
                                    <button class="act-btn edit" title="Edit" wire:click="openEdit({{ $facility->id }})">
                                        <span class="material-icons-round">drive_file_rename_outline</span>
                                    </button>
                                    <button class="act-btn status {{ $facility->status === 'active' ? 'btn-warning' : 'btn-success' }}" title="Toggle Status" wire:click="toggleStatus({{ $facility->id }})">
                                        <span class="material-icons-round">{{ $facility->status === 'active' ? 'toggle_off' : 'toggle_on' }}</span>
                                    </button>
                                    <button class="act-btn delete" title="Delete" wire:click="confirmDeleteRecord({{ $facility->id }})">
                                        <span class="material-icons-round">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox display-5 d-block mb-2 opacity-25"></i>
                                No facilities found. <a href="#" wire:click.prevent="openCreate">Create one now</a>.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer border-0 bg-white d-flex align-items-center justify-content-between flex-wrap gap-2 py-2 px-3">
            <small class="text-muted">Showing {{ $facilities->firstItem() ?? 0 }}–{{ $facilities->lastItem() ?? 0 }} of {{ $facilities->total() }}</small>
            {{ $facilities->links('vendor.pagination.custom') }}
        </div>

    </div>

    {{-- ===== CREATE/EDIT MODAL ===== --}}
    @if($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5);" wire:ignore.self>
            <div class="modal-dialog modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="bi bi-building me-2 text-danger"></i>
                            {{ $editId ? 'Edit' : 'Create' }} Facility
                        </h5>
                        <button type="button" class="btn-close" wire:click="$set('showModal',false)"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="save">
                            <div class="row g-3">

                                {{-- Name --}}
                                <div class="col-12">
                                    <label class="form-label">Facility Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" wire:model.defer="name" placeholder="e.g. Science Laboratory">
                                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
                                            <input class="form-check-input" type="radio" wire:model.defer="status" value="inactive" id="status_inactive">
                                            <label class="form-check-label" for="status_inactive">Inactive</label>
                                        </div>
                                    </div>
                                    @error('status') <div class="text-danger" style="font-size:.78rem;">{{ $message }}</div> @enderror
                                </div>

                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" wire:click="$set('showModal',false)">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="save" wire:loading.attr="disabled">
                            <span wire:loading wire:target="save" class="spinner-border spinner-border-sm me-1"></span>
                            {{ $editId ? 'Update' : 'Save' }} Facility
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
                        <h6 class="fw-700">Delete Facility?</h6>
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