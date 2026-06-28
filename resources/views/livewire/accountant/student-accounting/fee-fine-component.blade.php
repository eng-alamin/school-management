<div>

    <div class="card">

        <div class="mat-card-header header-pink-gradient">
            <h5 id="cardHeaderTitleAllsections">Fee Fine</h5>
            <p id="cardHeaderSubtitle">Manage late fee fines for fee groups and fee items easily.</p>
        </div>

        <div class="card-header border-0">
            <div class="card-toolbar">
                <div class="card-toolbar-title">
                    <div style="position:relative;display:inline-flex;align-items:center">
                        <span class="material-icons-round" style="position:absolute;left:10px;font-size:17px;color:var(--muted);pointer-events:none">search</span>
                        <input type="text" wire:model.live.debounce.300ms="search" id="tableSearch" placeholder="Search" style="border:1px solid rgba(0,0,0,.1);border-radius:8px;padding:7px 12px 7px 32px;font-size:.78rem;font-family:inherit;color:var(--dark);outline:none;background:#f8f9fa;width:220px"/>
                    </div>
                </div>

                @if($feeFines->total() > 10)
                    <div class="col-md-2">
                        <select class="form-select form-select-sm" wire:model.live="perPage">
                            <option value="10">10 / page</option>
                            <option value="25">25 / page</option>
                            <option value="50">50 / page</option>
                        </select>
                    </div>
                @endif

                <button class="btn-outline bg-dark text-white" wire:click="openCreate">
                    <span class="material-icons-round">add</span> <span>Add Fee Fine</span>
                </button>
            </div>
        </div>

        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>SL</th>
                            <th wire:click="sortBy('fee_group_id')" style="cursor:pointer">
                                Fee Group @if($sortField === 'fee_group_id') {!! $sortDirection === 'asc' ? '↑' : '↓' !!} @endif
                            </th>
                            <th>Fee Item</th>
                            <th>Fine Type</th>
                            <th>Fine Value</th>
                            <th>Frequency</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($feeFines as $i => $fine)
                        <tr>
                            <td class="text-muted">{{ $feeFines->firstItem() + $i }}</td>
                            <td>{{ $fine->feeGroup->name ?? '—' }}</td>
                            <td>
                                @if($fine->feeGroupItem?->feeType?->name)
                                    {{ $fine->feeGroupItem->feeType->name }}
                                @else
                                    <span class="badge badge-inactive">All Items</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $fine->fine_type === 'fixed' ? 'badge-used' : 'badge-active' }}">
                                    {{ ucfirst($fine->fine_type) }}
                                </span>
                            </td>
                            <td>
                                @if($fine->fine_type === 'percentage')
                                    {{ $fine->fine_value }}%
                                @else
                                    ৳{{ number_format($fine->fine_value, 0) }}
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-secondary text-capitalize">
                                    {{ str_replace('_', ' ', $fine->late_fee_frequency) }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <button class="act-btn edit" title="Edit" wire:click="openEdit({{ $fine->id }})">
                                        <span class="material-icons-round">drive_file_rename_outline</span>
                                    </button>
                                    <button class="act-btn delete" title="Delete" wire:click="confirmDeleteRecord({{ $fine->id }})">
                                        <span class="material-icons-round">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox display-5 d-block mb-2 opacity-25"></i>
                                No fee fines found. <a href="#" wire:click.prevent="openCreate">Create one now</a>.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer border-0 bg-white d-flex align-items-center justify-content-between flex-wrap gap-2 py-2 px-3">
            <small class="text-muted">Showing {{ $feeFines->firstItem() ?? 0 }}–{{ $feeFines->lastItem() ?? 0 }} of {{ $feeFines->total() }}</small>
            {{ $feeFines->links('vendor.pagination.custom') }}
        </div>

    </div>

    {{-- ===== CREATE/EDIT MODAL ===== --}}
    @if($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5);" wire:ignore.self>
            <div class="modal-dialog modal-md modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header border-0">
                        <h5 class="modal-title">{{ $editId ? 'Edit' : 'Create' }} Fee Fine</h5>
                        <button type="button" class="btn-close" wire:click="$set('showModal',false)"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="save">
                            <div class="row g-3">

                                {{-- Fee Group --}}
                                <div class="col-md-12">
                                    <label class="form-label">Fee Group <span class="text-danger">*</span></label>
                                    <select class="form-select @error('fee_group_id') is-invalid @enderror"
                                            wire:model.live="fee_group_id">
                                        <option value="">— Select Fee Group —</option>
                                        @foreach($feeGroups as $group)
                                            <option value="{{ $group->id }}">{{ $group->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('fee_group_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                {{-- Fee Item (optional) --}}
                                <div class="col-md-12">
                                    <label class="form-label">Fee Item <span class="text-muted">(optional — leave blank to apply to all)</span></label>
                                    <select class="form-select @error('fee_group_item_id') is-invalid @enderror"
                                            wire:model.defer="fee_group_item_id"
                                            @if(!$fee_group_id) disabled @endif>
                                        <option value="">— All Items —</option>
                                        @foreach($groupItems as $gItem)
                                            <option value="{{ $gItem['id'] }}">{{ $gItem['fee_type']['name'] ?? '—' }}</option>
                                        @endforeach
                                    </select>
                                    @error('fee_group_item_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                {{-- Fine Type --}}
                                <div class="col-md-6">
                                    <label class="form-label">Fine Type <span class="text-danger">*</span></label>
                                    <select class="form-select @error('fine_type') is-invalid @enderror"
                                            wire:model.defer="fine_type">
                                        <option value="fixed">Fixed (৳)</option>
                                        <option value="percentage">Percentage (%)</option>
                                    </select>
                                    @error('fine_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                {{-- Fine Value --}}
                                <div class="col-md-6">
                                    <label class="form-label">Fine Value <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" min="0"
                                           class="form-control @error('fine_value') is-invalid @enderror"
                                           wire:model.defer="fine_value"
                                           placeholder="{{ $fine_type === 'percentage' ? 'e.g. 5' : 'e.g. 50.00' }}">
                                    @error('fine_value') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                {{-- Frequency --}}
                                <div class="col-md-12">
                                    <label class="form-label">Late Fee Frequency <span class="text-danger">*</span></label>
                                    <select class="form-select @error('late_fee_frequency') is-invalid @enderror"
                                            wire:model.defer="late_fee_frequency">
                                        <option value="one_time">One Time</option>
                                        <option value="daily">Daily</option>
                                        <option value="weekly">Weekly</option>
                                        <option value="monthly">Monthly</option>
                                        <option value="yearly">Yearly</option>
                                    </select>
                                    @error('late_fee_frequency') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                            </div>
                        </form>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light" wire:click="$set('showModal',false)">Cancel</button>
                        <button type="button" class="btn bg-dark text-white" wire:click="save" wire:loading.attr="disabled">
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
                        <h6 class="fw-700">Delete Fee Fine?</h6>
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
        :root {
            --primary: rgba(33, 37, 41);
            --primary-light: rgba(239,84,84,.12);
        }

        .card { border: 1px solid var(--border); border-radius: 12px; box-shadow: 0 1px 4px rgba(0,0,0,.04); }
        .card-header { background: #fff; border-bottom: 1px solid var(--border); border-radius: 12px 12px 0 0 !important; padding: 16px 20px; }

        .table th { font-size: .75rem; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: var(--text-muted); border-bottom: 2px solid var(--border); }
        .table td { vertical-align: middle; font-size: .875rem; }
        .table > :not(caption) > * > * { padding: .7rem 1rem; }

        .badge-active   { background: rgba(34,197,94,.12);  color: #16a34a; }
        .badge-inactive { background: rgba(107,114,128,.12); color: #6b7280; }
        .badge-used     { background: rgba(59,130,246,.12);  color: #2563eb; }

        .modal-title { font-weight: 600; font-size: 1rem; }

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
        .btn-sm { font-size: .78rem; padding: .3rem .65rem; border-radius: 6px; }

        .custom-pagination { display: flex; gap: 8px; align-items: center; }
        .custom-pagination li { list-style: none; }
        .custom-pagination button {
            min-width: 38px; height: 38px; border-radius: 10px;
            border: 1px solid #e0e0e0; background: #f5f5f5;
            color: #444; font-weight: 600; cursor: pointer; transition: all .2s ease;
        }
        .custom-pagination button:hover { background: #eee; }
        .custom-pagination button.active {
            background: linear-gradient(195deg, #ec407a, #d81b60);
            color: #fff; border: none; box-shadow: 0 4px 12px rgba(216,27,96,.4);
        }
        .custom-pagination button:disabled { opacity: .5; cursor: not-allowed; }
    </style>
@endpush