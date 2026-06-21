{{-- resources/views/livewire/super-admin/settings/pricing-rate-component.blade.php --}}
<div>

    <div class="card">

        <!-- floating header -->
        <div class="mat-card-header header-pink-gradient">
            <h5 id="cardHeaderTitleAllsections">Pricing Rates</h5>
            <p id="cardHeaderSubtitle">Set platform-wide billing rates (Student, SMS, etc.).</p>
        </div>

        <div class="card-header border-0">
            <!-- toolbar -->
            <div class="card-toolbar">
                {{-- Left side --}}
                <div class="card-toolbar-title">
                    <div style="position:relative;display:inline-flex;align-items:center">
                        <span class="material-icons-round" style="position:absolute;left:10px;font-size:17px;color:var(--muted);pointer-events:none">search</span>
                        <input type="text" wire:model.live.debounce.300ms="search" id="tableSearch" placeholder="Search" style="border:1px solid rgba(0,0,0,.1);border-radius:8px;padding:7px 12px 7px 32px;font-size:.78rem;font-family:inherit;color:var(--dark);outline:none;background:#f8f9fa;width:220px"/>
                    </div>
                </div>

                <!-- Right Side -->
                <div class="col-md-2">
                    <select class="form-select form-select-sm" wire:model.live="filterStatus">
                        <option value="">All Status</option>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
                @if($rates->total() > 10)
                    <div class="col-md-2">
                        <select class="form-select form-select-sm" wire:model.live="perPage">
                            <option value="10">10 / page</option>
                            <option value="25">25 / page</option>
                            <option value="50">50 / page</option>
                        </select>
                    </div>
                @endif
                <button class="btn-outline bg-dark text-white" wire:click="openCreate">
                    <span class="material-icons-round">add</span> <span id="newSectionBtn">Add Rate</span>
                </button>
            </div>
        </div>

        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>SL</th>
                            <th>Type</th>
                            <th>Label</th>
                            <th>Rate</th>
                            <th>Status</th>
                            <th>Updated</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rates as $i => $rate)
                        <tr>
                            <td class="text-muted">{{ $rates->firstItem() + $i }}</td>

                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar-placeholder rate-avatar-{{ $rate->type }}">
                                        <span class="material-icons-round" style="font-size:1rem;">
                                            @switch($rate->type)
                                                @case('sms') sms @break
                                                @case('attendance') event_available @break
                                                @default group @break
                                            @endswitch
                                        </span>
                                    </div>
                                    <span class="fw-500 text-dark text-capitalize">{{ $rate->type }}</span>
                                </div>
                            </td>

                            <td class="text-muted" style="font-size:.8rem;">{{ $rate->label }}</td>

                            <td class="fw-600">৳ {{ number_format($rate->rate, 2) }}</td>

                            <td>
                                <span class="badge rounded-pill {{ $rate->is_active ? 'badge-active' : 'badge-inactive' }}" style="font-size:.72rem;">
                                    {{ $rate->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>

                            <td class="text-muted" style="font-size:.78rem;">
                                {{ $rate->updated_at->format('d M Y') }}
                            </td>

                            <td>
                                <div class="d-flex gap-1">
                                    <button class="act-btn view" title="View" wire:click="openView({{ $rate->id }})">
                                        <span class="material-icons-round">visibility</span>
                                    </button>
                                    <button class="act-btn edit" title="Edit" wire:click="openEdit({{ $rate->id }})">
                                        <span class="material-icons-round">drive_file_rename_outline</span>
                                    </button>
                                    <button class="act-btn status {{ $rate->is_active ? 'btn-warning' : 'btn-success' }}" title="Toggle Status" wire:click="toggleStatus({{ $rate->id }})">
                                        <span class="material-icons-round">{{ $rate->is_active ? 'toggle_off' : 'toggle_on' }}</span>
                                    </button>
                                    <button class="act-btn delete" title="Delete" wire:click="confirmDeleteRecord({{ $rate->id }})">
                                        <span class="material-icons-round">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox display-5 d-block mb-2 opacity-25"></i>
                                No pricing rates found. <a href="#" wire:click.prevent="openCreate">Create one now</a>.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer border-0 bg-white d-flex align-items-center justify-content-between flex-wrap gap-2 py-2 px-3">
            <small class="text-muted">Showing {{ $rates->firstItem() ?? 0 }}–{{ $rates->lastItem() ?? 0 }} of {{ $rates->total() }}</small>
            {{ $rates->links('vendor.pagination.custom') }}
        </div>

    </div>

    {{-- ===== CREATE/EDIT MODAL ===== --}}
    @if($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5);" wire:ignore.self>
            <div class="modal-dialog modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="bi bi-cash-coin me-2 text-danger"></i>
                            {{ $editId ? 'Edit' : 'Create' }} Pricing Rate
                        </h5>
                        <button type="button" class="btn-close" wire:click="$set('showModal',false)"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="save">
                            <div class="row g-3">

                                {{-- Type --}}
                                <div class="col-12">
                                    <label class="form-label">Type <span class="text-danger">*</span></label>
                                    <select class="form-select @error('type') is-invalid @enderror" wire:model.defer="type" @if($editId) disabled @endif>
                                        <option value="">-- Select Type --</option>
                                        <option value="student">Student (Per Active Student / Month)</option>
                                        <option value="sms">SMS (Per SMS Sent)</option>
                                        <option value="attendance">Attendance (Per Record)</option>
                                    </select>
                                    @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    @if($editId)
                                        <small class="text-muted">Type পরিবর্তন করা যাবে না — কারণ billing system এই value reference করে।</small>
                                    @endif
                                </div>

                                {{-- Label --}}
                                <div class="col-12">
                                    <label class="form-label">Label <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('label') is-invalid @enderror" wire:model.defer="label" placeholder="e.g. Active Student Monthly Charge">
                                    @error('label') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                {{-- Rate --}}
                                <div class="col-12">
                                    <label class="form-label">Rate (৳) <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" min="0" class="form-control @error('rate') is-invalid @enderror" wire:model.defer="rate" placeholder="e.g. 5.00">
                                    @error('rate') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                {{-- Status --}}
                                <div class="col-12">
                                    <label class="form-label d-block mb-2">Status</label>
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
                                </div>

                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" wire:click="$set('showModal',false)">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="save" wire:loading.attr="disabled">
                            <span wire:loading wire:target="save" class="spinner-border spinner-border-sm me-1"></span>
                            {{ $editId ? 'Update' : 'Save' }} Rate
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
                        <h5 class="modal-title">Pricing Rate Details</h5>
                        <button class="btn-close" wire:click="$set('showViewModal',false)"></button>
                    </div>
                    <div class="modal-body">
                        <table class="table table-sm">
                            <tr>
                                <th class="text-muted" style="width:40%">Type</th>
                                <td class="text-capitalize">{{ $viewRecord->type }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Label</th>
                                <td>{{ $viewRecord->label }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Rate</th>
                                <td class="fw-600">৳ {{ number_format($viewRecord->rate, 2) }}</td>
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
                            <tr>
                                <th class="text-muted">Last Updated</th>
                                <td>{{ $viewRecord->updated_at->format('d M Y') }}</td>
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
                        <h6 class="fw-700">Delete Pricing Rate?</h6>
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
        .rate-avatar-student     { background: rgba(37,99,235,.12);  color: #2563eb; }
        .rate-avatar-sms         { background: rgba(124,58,237,.12); color: #7c3aed; }
        .rate-avatar-attendance  { background: rgba(217,119,6,.12);  color: #d97706; }

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