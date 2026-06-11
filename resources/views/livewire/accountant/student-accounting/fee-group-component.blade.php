<div>

    <div class="card">

        <div class="mat-card-header header-pink-gradient">
            <h5 id="cardHeaderTitleAllsections">Fee Group</h5>
            <p id="cardHeaderSubtitle">Manage fee groups with fee types and amounts easily.</p>
        </div>

        <div class="card-header border-0">
            <div class="card-toolbar">
                <div class="card-toolbar-title">
                    <div style="position:relative;display:inline-flex;align-items:center">
                        <span class="material-icons-round" style="position:absolute;left:10px;font-size:17px;color:var(--muted);pointer-events:none">search</span>
                        <input type="text" wire:model.live.debounce.300ms="search" id="tableSearch" placeholder="Search" style="border:1px solid rgba(0,0,0,.1);border-radius:8px;padding:7px 12px 7px 32px;font-size:.78rem;font-family:inherit;color:var(--dark);outline:none;background:#f8f9fa;width:220px"/>
                    </div>
                </div>

                @if($feeGroups->total() > 10)
                    <div class="col-md-2">
                        <select class="form-select form-select-sm" wire:model.live="perPage">
                            <option value="10">10 / page</option>
                            <option value="25">25 / page</option>
                            <option value="50">50 / page</option>
                        </select>
                    </div>
                @endif

                <button class="btn-outline bg-dark text-white" wire:click="openCreate">
                    <span class="material-icons-round">add</span> <span>Add Fee Group</span>
                </button>
            </div>
        </div>

        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>SL</th>
                            <th wire:click="sortBy('name')" style="cursor:pointer">
                                Group Name @if($sortField === 'name') {!! $sortDirection === 'asc' ? '↑' : '↓' !!} @endif
                            </th>
                            <th>Fee Items</th>
                            <th>Description</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($feeGroups as $i => $feeGroup)
                        <tr>
                            <td class="text-muted">{{ $feeGroups->firstItem() + $i }}</td>
                            <td>{{ $feeGroup->name }}</td>
                            <td>
                                @if($feeGroup->items->count())
                                    <div class="fee-items-list">
                                        @foreach($feeGroup->items as $item)
                                            <div class="fee-item-row">
                                                <span class="fee-item-name">{{ $item->feeType->name }}</span>
                                                <span class="fee-item-amount">- ৳{{ number_format($item->amount, 2) }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>{{ $feeGroup->description ?? '—' }}</td>
                            <td>
                                <div class="d-flex gap-1">
                                    <button class="act-btn edit" title="Edit" wire:click="openEdit({{ $feeGroup->id }})">
                                        <span class="material-icons-round">drive_file_rename_outline</span>
                                    </button>
                                    <button class="act-btn delete" title="Delete" wire:click="confirmDeleteRecord({{ $feeGroup->id }})">
                                        <span class="material-icons-round">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox display-5 d-block mb-2 opacity-25"></i>
                                No fee groups found. <a href="#" wire:click.prevent="openCreate">Create one now</a>.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer border-0 bg-white d-flex align-items-center justify-content-between flex-wrap gap-2 py-2 px-3">
            <small class="text-muted">Showing {{ $feeGroups->firstItem() ?? 0 }}–{{ $feeGroups->lastItem() ?? 0 }} of {{ $feeGroups->total() }}</small>
            {{ $feeGroups->links('vendor.pagination.custom') }}
        </div>

    </div>

    {{-- ===== CREATE/EDIT MODAL ===== --}}
    @if($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5);" wire:ignore.self>
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header border-0">
                        <h5 class="modal-title">{{ $editId ? 'Edit' : 'Create' }} Fee Group</h5>
                        <button type="button" class="btn-close" wire:click="$set('showModal',false)"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="save">
                            <div class="row g-3">

                                <div class="col-md-12">
                                    <label class="form-label">Group Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                           wire:model.defer="name" placeholder="e.g. Monthly Fee">
                                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label">Description</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror"
                                              wire:model.defer="description" rows="2" placeholder="Optional description..."></textarea>
                                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                {{-- ── Fee Items ── --}}
                                <div class="col-md-12">
                                    <label class="form-label mb-2">Fee Items</label>
                                    <div class="table-responsive">
                                        <table class="table table-sm mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th style="width:40px">
                                                        <input type="checkbox" class="form-check-input"
                                                            wire:model.live="selectAll">
                                                    </th>
                                                    <th>Fee Type</th>
                                                    <th style="width:200px">Amount (৳) <span class="text-danger">*</span></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($items as $index => $item)
                                                <tr wire:key="item-{{ $index }}">
                                                    <td class="align-middle text-center">
                                                        <input type="checkbox" class="form-check-input"
                                                            wire:model="selectedItems"
                                                            value="{{ $item['fee_type_id'] }}">
                                                    </td>
                                                    <td class="align-middle">
                                                        <span>{{ $item['fee_type_name'] }}</span>
                                                        <input type="hidden" wire:model.defer="items.{{ $index }}.fee_type_id">
                                                        <input type="hidden" wire:model.defer="items.{{ $index }}.fee_type_name">
                                                    </td>
                                                    <td>
                                                        <input type="number" step="0.01" min="0"
                                                            class="form-control form-control-sm @error('items.'.$index.'.amount') is-invalid @enderror"
                                                            wire:model.defer="items.{{ $index }}.amount"
                                                            placeholder="0.00"
                                                            @if(!in_array($item['fee_type_id'], $selectedItems)) @endif>
                                                        @error('items.'.$index.'.amount')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
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
                        <h6 class="fw-700">Delete Fee Group?</h6>
                        <p class="text-muted small">This will also remove all fee items. This action cannot be undone.</p>
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

        .badge-active { background: rgba(34,197,94,.12); color: #16a34a; }
        .badge-inactive { background: rgba(107,114,128,.12); color: #6b7280; }

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