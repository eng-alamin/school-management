<div>

    <div class="card">

        <div class="mat-card-header header-pink-gradient">
            <h5>Salary Advance</h5>
            <p>Issue salary advances to employees and track outstanding balances.</p>
        </div>

        <div class="card-header border-0">
            <div class="card-toolbar">
                <div class="card-toolbar-title">
                    <div style="position:relative;display:inline-flex;align-items:center">
                        <span class="material-icons-round" style="position:absolute;left:10px;font-size:17px;color:var(--muted);pointer-events:none">search</span>
                        <input type="text"
                               wire:model.live.debounce.300ms="search"
                               placeholder="Search by employee name/ID"
                               style="border:1px solid rgba(0,0,0,.1);border-radius:8px;padding:7px 12px 7px 32px;font-size:.78rem;font-family:inherit;color:var(--dark);outline:none;background:#f8f9fa;width:240px" />
                    </div>
                </div>

                @if($advances->total() > 10)
                    <div class="col-md-2">
                        <select class="form-select form-select-sm" wire:model.live="perPage">
                            <option value="10">10 / page</option>
                            <option value="25">25 / page</option>
                            <option value="50">50 / page</option>
                        </select>
                    </div>
                @endif

                <button type="button" class="btn-sm btn-outline bg-dark text-white" wire:click="openCreate">
                    <span class="material-icons-round">add</span> Issue Advance
                </button>
            </div>
        </div>

        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>SL</th>
                            <th>Employee</th>
                            <th>Advance Date</th>
                            <th>Amount</th>
                            <th>Installment</th>
                            <th>Remaining</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($advances as $i => $advance)
                            <tr>
                                <td class="text-muted">{{ $advances->firstItem() + $i }}</td>
                                <td>{{ $advance->employee->name ?? 'N/A' }}</td>
                                <td>{{ $advance->advance_date->format('d M, Y') }}</td>
                                <td>${{ number_format($advance->amount, 2) }}</td>
                                <td>
                                    @if($advance->installment_amount)
                                        ${{ number_format($advance->installment_amount, 2) }} / month
                                    @else
                                        <span class="text-muted">Full (next payment)</span>
                                    @endif
                                </td>
                                <td>
                                    <span style="font-weight:600;{{ $advance->remaining_amount > 0 ? 'color:#d97706' : 'color:#16a34a' }}">
                                        ${{ number_format($advance->remaining_amount, 2) }}
                                    </span>
                                </td>
                                <td>
                                    @if($advance->status === 'active')
                                        <span class="badge" style="background:#fef3c7;color:#92400e;font-weight:600;font-size:.68rem;padding:5px 8px;border-radius:6px;">Active</span>
                                    @else
                                        <span class="badge" style="background:#dcfce7;color:#166534;font-weight:600;font-size:.68rem;padding:5px 8px;border-radius:6px;">Settled</span>
                                    @endif
                                </td>
                                <td>
                                    <button class="act-btn delete" title="Delete" wire:click="confirmDeleteRecord({{ $advance->id }})">
                                        <span class="material-icons-round">delete</span>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox display-5 d-block mb-2 opacity-25"></i>
                                    No salary advances found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer border-0 bg-white d-flex align-items-center justify-content-between flex-wrap gap-2 py-2 px-3">
            <small class="text-muted">Showing {{ $advances->firstItem() ?? 0 }}–{{ $advances->lastItem() ?? 0 }} of {{ $advances->total() }}</small>
            {{ $advances->links('vendor.pagination.custom') }}
        </div>

    </div>

    {{-- ═════════════ ADD MODAL ═════════════ --}}
    @if($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5);" wire:ignore.self>
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header border-0">
                        <h5 class="modal-title">Issue Salary Advance</h5>
                        <button type="button" class="btn-close" wire:click="$set('showModal', false)"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Employee <span class="text-danger">*</span></label>
                                <select wire:model="employee_id" class="form-select form-select-sm">
                                    <option value="">-- Select Employee --</option>
                                    @foreach($employees as $emp)
                                        <option value="{{ $emp->id }}">{{ $emp->name }} ({{ $emp->employee_id ?? '—' }})</option>
                                    @endforeach
                                </select>
                                @error('employee_id') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Advance Date <span class="text-danger">*</span></label>
                                <input type="date" wire:model="advance_date" class="form-control form-control-sm">
                                @error('advance_date') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Amount <span class="text-danger">*</span></label>
                                <input type="number" min="1" step="0.01" wire:model="amount" class="form-control form-control-sm">
                                @error('amount') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Monthly Installment <span class="text-muted">(optional)</span></label>
                                <input type="number" min="0.01" step="0.01" wire:model="installment_amount" class="form-control form-control-sm" placeholder="Leave empty to deduct full amount at once">
                                @error('installment_amount') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">Reason</label>
                                <textarea wire:model="reason" class="form-control form-control-sm" rows="2"></textarea>
                                @error('reason') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light" wire:click="$set('showModal', false)">Cancel</button>
                        <button type="button" class="btn btn-pink" wire:click="save" wire:loading.attr="disabled" wire:target="save">
                            <span wire:loading.remove wire:target="save">Issue Advance</span>
                            <span wire:loading wire:target="save">
                                <span class="material-icons-round" style="font-size:16px;animation:spin .7s linear infinite;vertical-align:middle">sync</span>
                                Saving...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ═════════════ DELETE CONFIRM ═════════════ --}}
    @if($confirmDelete)
        <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5);">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                    <div class="modal-body text-center py-4">
                        <div style="width:56px;height:56px;border-radius:50%;background:#fee2e2;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                            <i class="bi bi-exclamation-triangle text-danger" style="font-size:1.5rem;"></i>
                        </div>
                        <h6 class="fw-700">Delete Salary Advance?</h6>
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