<div>

    <div class="card">

        <!-- floating header -->
        <div class="mat-card-header header-pink-gradient">
            <h5 id="sale-list-title">Sales</h5>
            <p id="sale-list-subtitle">Manage sales, track bills, and monitor inventory sales easily.</p>
        </div>

        <div class="card-header border-0">
            <div class="card-toolbar">
                {{-- Left side --}}
                <div class="card-toolbar-title">
                    <div style="position:relative;display:inline-flex;align-items:center">
                        <span class="material-icons-round" style="position:absolute;left:10px;font-size:17px;color:var(--muted);pointer-events:none">search</span>
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search bill no, role…" style="border:1px solid rgba(0,0,0,.1);border-radius:8px;padding:7px 12px 7px 32px;font-size:.78rem;font-family:inherit;color:var(--dark);outline:none;background:#f8f9fa;width:220px"/>
                    </div>
                </div>

                {{-- Right side --}}
                @if($sales->total() > 10)
                    <div class="col-md-2">
                        <select class="form-select form-select-sm" wire:model.live="perPage">
                            <option value="10">10 / page</option>
                            <option value="25">25 / page</option>
                            <option value="50">50 / page</option>
                        </select>
                    </div>
                @endif

                <a href="{{ route('admin.inventory.sale.add') }}" class="btn-sm btn-outline bg-dark text-white">
                    <span class="material-icons-round">add</span> Add Sale
                </a>
            </div>
        </div>

        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th id="slist-th-sl">SL</th>
                            <th wire:click="sortBy('bill_no')" style="cursor:pointer">
                                Bill No @if($sortField === 'bill_no') {!! $sortDirection === 'asc' ? '↑' : '↓' !!} @endif
                            </th>
                            <th wire:click="sortBy('role')" style="cursor:pointer">
                                Role @if($sortField === 'role') {!! $sortDirection === 'asc' ? '↑' : '↓' !!} @endif
                            </th>
                            <th id="slist-th-sale-to">Sale To</th>
                            <th wire:click="sortBy('date')" style="cursor:pointer">
                                Date @if($sortField === 'date') {!! $sortDirection === 'asc' ? '↑' : '↓' !!} @endif
                            </th>
                            <th id="slist-th-payment">Payment</th>
                            <th wire:click="sortBy('net_payable')" style="cursor:pointer">
                                Net Payable @if($sortField === 'net_payable') {!! $sortDirection === 'asc' ? '↑' : '↓' !!} @endif
                            </th>
                            <th id="slist-th-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sales as $i => $sale)
                            <tr>
                                <td class="text-muted">{{ $sales->firstItem() + $i }}</td>

                                <td>{{ $sale->bill_no }}</td>

                                <td>
                                    <span class="badge rounded-pill
                                        @switch($sale->role)
                                            @case('student')  badge-used     @break
                                            @case('employee') badge-received @break
                                            @default          badge-pending
                                        @endswitch">
                                        {{ ucfirst($sale->role) }}
                                    </span>
                                </td>

                                <td>
                                    @if($sale->role === 'student')
                                        {{ $sale->saleable->name ?? '—' }}
                                    @else
                                        {{ $sale->saleable->name ?? '—' }}
                                    @endif
                                </td>

                                <td>{{ \Carbon\Carbon::parse($sale->date)->format('d M Y') }}</td>

                                <td>
                                    <span class="badge rounded-pill
                                        @switch($sale->payment_status)
                                            @case('paid')     badge-active   @break
                                            @case('partial')  badge-pending  @break
                                            @case('due')      badge-inactive @break
                                        @endswitch">
                                        {{ ucfirst($sale->payment_status) }}
                                    </span>
                                </td>

                                <td style="font-weight:600">
                                    {{ number_format($sale->net_payable, 0) }}
                                </td>

                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('admin.inventory.sale.edit', ['id' => $sale->id]) }}"
                                           class="act-btn edit" title="Edit">
                                            <span class="material-icons-round">drive_file_rename_outline</span>
                                        </a>
                                        <button class="act-btn delete" title="Delete"
                                                wire:click="confirmDeleteRecord({{ $sale->id }})">
                                            <span class="material-icons-round">delete</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox display-5 d-block mb-2 opacity-25"></i>
                                    No sales found.
                                    <a href="{{ route('admin.inventory.sale.add') }}">Create one now</a>.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer border-0 bg-white d-flex align-items-center justify-content-between flex-wrap gap-2 py-2 px-3">
            <small class="text-muted">Showing {{ $sales->firstItem() ?? 0 }}–{{ $sales->lastItem() ?? 0 }} of {{ $sales->total() }}</small>
            {{ $sales->links('vendor.pagination.custom') }}
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
                        <h6 id="slist-delete-title" class="fw-700">Delete Sale?</h6>
                        <p id="slist-delete-msg" class="text-muted small">This will also delete all associated items. This action cannot be undone.</p>
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
        .card { border: 1px solid var(--border); border-radius: 12px; box-shadow: 0 1px 4px rgba(0,0,0,.04); }
        .card-header { background: #fff; border-bottom: 1px solid var(--border); border-radius: 12px 12px 0 0 !important; padding: 16px 20px; }

        .badge-active   { background: rgba(34,197,94,.12);   color: #16a34a; }
        .badge-inactive { background: rgba(107,114,128,.12);  color: #6b7280; }
        .badge-used     { background: rgba(59,130,246,.12);   color: #2563eb; }
        .badge-pending  { background: rgba(245,158,11,.12);   color: #d97706; }
        .badge-received { background: rgba(139,92,246,.12);   color: #7c3aed; }

        .btn-sm { font-size: .78rem; padding: .3rem .65rem; border-radius: 6px; }
    </style>
@endpush