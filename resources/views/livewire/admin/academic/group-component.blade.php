<div>

    <div class="card">

        <div class="mat-card-header header-primary-gradient">
            <h5 id="cardHeaderTitleAllgroups">All Groups</h5>
            <p id="cardHeaderSubtitle">Manage groups, create, update, and organize easily.</p>
        </div>

        <div class="card-header border-0">
            <div class="card-toolbar">
                <div class="card-toolbar-title">
                    <div style="position:relative;display:inline-flex;align-items:center">
                        <span class="material-icons-round" style="position:absolute;left:10px;font-size:17px;color:var(--muted);pointer-events:none">search</span>
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search" class="tb-search"/>
                    </div>
                </div>

                @if($groups->total() > 10)
                    <div class="col-md-2">
                        <div class="input-group input-group-outline">
                            <select class="form-select form-select-sm" wire:model.live="perPage">
                                <option value="10">10 / page</option>
                                <option value="25">25 / page</option>
                                <option value="50">50 / page</option>
                            </select>
                        </div>
                    </div>
                @endif

                <a href="{{ route($routePrefix . 'academic.classes') }}" class="btn btn-primary">
                    <span>
                        <span class="material-icons-round">flight_class</span>
                        <span>Class</span>
                    </span>
                </a>
                <a href="{{ route($routePrefix . 'academic.sections') }}" class="btn btn-primary">
                    <span>
                        <span class="material-icons-round">border_inner</span>
                        <span>Section</span>
                    </span>
                </a>
                <a href="{{ route($routePrefix . 'academic.subjects') }}" class="btn btn-primary">
                    <span>
                        <span class="material-icons-round">subject</span>
                        <span>Subjects</span>
                    </span>
                </a>
                <a href="{{ route($routePrefix . 'academic.groups') }}" class="btn btn-primary">
                    <span>
                        <span class="material-icons-round">group</span>
                        <span>Groups</span>
                    </span>
                </a>

                <button class="btn btn-primary" wire:click="openCreate">
                    <span>
                        <span class="material-icons-round">add</span>
                        <span>New Group</span>
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
                            <th id="th-name" wire:click="sortBy('name')" style="cursor:pointer">
                                Name @if($sortField === 'name') {!! $sortDirection === 'asc' ? '↑' : '↓' !!} @endif
                            </th>
                            <th id="th-current">Is Current</th>
                            <th id="th-status">Is Status</th>
                            <th id="th-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($groups as $i => $group)
                        <tr>
                            <td class="text-muted">{{ $groups->firstItem() + $i }}</td>
                            <td>{{ $group->name }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    @if($group->is_current)
                                        <span class="badge bg-success">Current</span>
                                    @else
                                     -
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="form-check form-switch mb-0" title="{{ $group->is_status ? 'Active' : 'Inactive' }}">
                                        <input class="form-check-input" type="checkbox"
                                            wire:click="toggleStatus({{ $group->id }})"
                                            wire:loading.attr="disabled" wire:target="toggleStatus({{ $group->id }})"
                                            @checked($group->is_status)>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <button class="act-btn edit" title="Edit" wire:click="openEdit({{ $group->id }})">
                                        <span class="material-icons-round">drive_file_rename_outline</span>
                                    </button>
                                    <button class="act-btn delete" title="Delete"
                                        wire:click="confirmDeleteRecord({{ $group->id }})"
                                        @if($group->is_current) disabled title="Current group cannot be deleted" @endif
                                        style="{{ $group->is_current ? 'opacity:.4;cursor:not-allowed;pointer-events:none;' : '' }}">
                                        <span class="material-icons-round">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox display-5 d-block mb-2 opacity-25"></i>
                                No groups found. <a href="#" wire:click.prevent="openCreate">Create one now</a>.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer border-0 bg-white d-flex align-items-center justify-content-between flex-wrap gap-2 py-2 px-3">
            <small class="text-muted">Showing {{ $groups->firstItem() ?? 0 }}–{{ $groups->lastItem() ?? 0 }} of {{ $groups->total() }}</small>
            {{ $groups->links('vendor.pagination.custom') }}
        </div>

    </div>

    {{-- ===== CREATE/EDIT MODAL ===== --}}
    @if($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5);" wire:ignore.self>
            <div class="modal-dialog modal-md modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header border-0">
                        <h5 class="modal-title">{{ $editId ? 'Edit' : 'Create' }} Group</h5>
                        <button type="button" class="btn-close" wire:click="$set('showModal', false)"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="save">
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <div class="input-group input-group-outline">
                                        <label class="form-label">Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror" wire:model.defer="name">
                                    </div>
                                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="isCurrentCheck" wire:model.defer="is_current">
                                        <label class="form-check-label" for="isCurrentCheck">Current</label>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light" wire:click="$set('showModal', false)">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="save" wire:loading.attr="disabled">
                            <span wire:loading wire:target="save" class="spinner-border spinner-border-sm me-1"></span>
                            {{ $editId ? 'Update' : 'Create' }}
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
                        <h6 class="fw-700">Delete group?</h6>
                        <p class="text-muted small">This action cannot be undone.</p>
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