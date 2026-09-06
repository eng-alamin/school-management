<div>

    <div class="card">

        <!-- floating header -->
        <div class="mat-card-header header-primary-gradient">
            <h5>Deposits</h5>
            <p>Manage office deposits, create, update, and track all deposit records easily.</p>
        </div>

        <div class="card-header border-0">
            <div class="card-toolbar">
                {{-- Left side --}}
                <div class="card-toolbar-title">
                    <div style="position:relative;display:inline-flex;align-items:center">
                        <span class="material-icons-round" style="position:absolute;left:10px;font-size:17px;color:var(--muted);pointer-events:none">search</span>
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search" class="tb-search"/>
                    </div>
                </div>

                {{-- Right side --}}
                @if($deposits->total() > 10)
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

                <a href="{{route($routePrefix . 'office-accounting.deposit.add') }}" class="btn btn-primary">
                    <span>
                        <span class="material-icons-round">add</span>
                        <span>Add Deposit</span>
                    </span>
                </a>
            </div>
        </div>

        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>SL</th>
                            <th>Voucher No</th>
                            <th>Account</th>
                            <th>Head</th>
                            <th wire:click="sortBy('pay_via')" style="cursor:pointer">
                                Pay Via @if($sortField === 'pay_via') {!! $sortDirection === 'asc' ? '↑' : '↓' !!} @endif
                            </th>
                            <th>Reference</th>
                            <th wire:click="sortBy('amount')" style="cursor:pointer">
                                Amount @if($sortField === 'amount') {!! $sortDirection === 'asc' ? '↑' : '↓' !!} @endif
                            </th>
                            <th wire:click="sortBy('date')" style="cursor:pointer">
                                Date @if($sortField === 'date') {!! $sortDirection === 'asc' ? '↑' : '↓' !!} @endif
                            </th>
                            <th>Attachment</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($deposits as $i => $deposit)
                            <tr wire:key="deposit-{{ $deposit->id }}">
                                <td class="text-muted">{{ $deposits->firstItem() + $i }}</td>
                                <td>{{ $deposit->voucher_no ?? '—' }}</td>
                                <td>{{ $deposit->account->name ?? '—' }}</td>
                                <td>{{ $deposit->head->name ?? '—' }}</td>
                                <td>
                                    @if($deposit->pay_via)
                                        <span class="badge rounded-pill badge-used">{{ $deposit->pay_via }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>{{ $deposit->reference ?? '—' }}</td>
                                <td>
                                    <span class="badge rounded-pill badge-active">
                                        {{ number_format($deposit->amount, 0) }}
                                    </span>
                                </td>
                                <td>{{ \Carbon\Carbon::parse($deposit->date)->format('d M Y') }}</td>
                                <td>
                                    @if($deposit->attachment)
                                        <a href="{{ Storage::url($deposit->attachment) }}"
                                           target="_blank"
                                           class="act-btn edit"
                                           title="View Attachment">
                                            <span class="material-icons-round">attach_file</span>
                                        </a>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('admin.office-accounting.deposit.edit', ['id' => $deposit->id]) }}"
                                           class="act-btn edit" title="Edit">
                                            <span class="material-icons-round">drive_file_rename_outline</span>
                                        </a>
                                        <button class="act-btn delete" title="Delete"
                                                wire:click="confirmDeleteRecord({{ $deposit->id }})">
                                            <span class="material-icons-round">delete</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox display-5 d-block mb-2 opacity-25"></i>
                                    No deposits found.
                                    <a href="{{ route('admin.office-accounting.deposit.add') }}">Create one now</a>.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer border-0 bg-white d-flex align-items-center justify-content-between flex-wrap gap-2 py-2 px-3">
            <small class="text-muted">Showing {{ $deposits->firstItem() ?? 0 }}–{{ $deposits->lastItem() ?? 0 }} of {{ $deposits->total() }}</small>
            {{ $deposits->links('vendor.pagination.custom') }}
        </div>

    </div>

    {{-- ===== DELETE CONFIRM ===== --}}
    @if($confirmDelete)
        <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5);">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                    <div class="modal-body text-center py-4">
                        <div style="width:56px;height:56px;border-radius:50%;background:#fee2e2;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                            <i class="bi bi-exclamation-triangle text-danger" style="font-size:1.5rem;"></i>
                        </div>
                        <h6 class="fw-700">Delete Deposit?</h6>
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