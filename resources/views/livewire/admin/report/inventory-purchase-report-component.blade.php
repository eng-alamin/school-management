{{-- resources/views/livewire/admin/report/inventory-purchase-report-component.blade.php --}}
<div>

    <div class="card">

        <div class="mat-card-header header-primary-gradient">
            <h5 id="cardHeaderTitleAllsections">Purchase Report</h5>
            <p id="cardHeaderSubtitle">Supplier ও store অনুযায়ী inventory purchase history দেখুন।</p>
        </div>

        <div class="card-body pt-3">

            @include('admin.report.inventory-nav')
            
            {{-- Summary cards --}}
            <div class="row g-2 mb-3">
                <div class="col-6 col-md-3">
                    <div class="summary-card summary-present">
                        <span class="material-icons-round">receipt_long</span>
                        <div class="count">{{ $summary['total_bills'] }}</div>
                        <div class="label">Total Bills</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="summary-card summary-late">
                        <span class="material-icons-round">payments</span>
                        <div class="count">৳{{ number_format($summary['total_amount'], 2) }}</div>
                        <div class="label">Total Amount</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="summary-card summary-leave">
                        <span class="material-icons-round">pending_actions</span>
                        <div class="count">{{ $summary['pending'] }}</div>
                        <div class="label">Pending</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="summary-card summary-absent">
                        <span class="material-icons-round">inventory</span>
                        <div class="count">{{ $summary['received'] }}</div>
                        <div class="label">Received</div>
                    </div>
                </div>
            </div>

            {{-- Toolbar / Filters --}}
            <div class="card-toolbar flex-wrap mb-2">
                <div class="col-md-2">
                    <div class="input-group input-group-outline" wire:ignore>
                        <label class="form-label">From</label>
                        <input type="date" class="form-control form-control-sm" wire:model.live="dateFrom" data-dp-value="{{ $dateFrom }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="input-group input-group-outline" wire:ignore>
                        <label class="form-label">To</label>
                        <input type="date" class="form-control form-control-sm" wire:model.live="dateTo" data-dp-value="{{ $dateTo }}">
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="input-group input-group-outline" wire:ignore>
                        <label class="form-label">Status</label>
                        <select class="form-select form-select-sm" wire:model.live="purchaseStatus">
                            <option value="all">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="ordered">Ordered</option>
                            <option value="completed">Completed</option>
                            <option value="received">Received</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="input-group input-group-outline" wire:ignore>
                        <label class="form-label">Supplier</label>
                        <select class="form-select form-select-sm" wire:model.live="supplierId">
                            <option value="all">All Suppliers</option>
                            @foreach ($suppliers as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="input-group input-group-outline" wire:ignore>
                        <label class="form-label">Store</label>
                        <select class="form-select form-select-sm" wire:model.live="storeId">
                            <option value="all">All Stores</option>
                            @foreach ($stores as $store)
                                <option value="{{ $store->id }}">{{ $store->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Search</label>
                    <div style="position:relative;display:inline-flex;align-items:center;width:100%">
                        <span class="material-icons-round" style="position:absolute;left:10px;font-size:16px;color:var(--muted);pointer-events:none">search</span>
                        <input type="text" wire:model.live.debounce.400ms="search" placeholder="Bill No..." class="form-control form-control-sm" style="padding-left:32px;">
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th id="th-sl">SL</th>
                            <th id="th-billno" role="button" wire:click="sortBy('bill_no')">
                                Bill No
                                @if($sortField === 'bill_no')
                                    <span class="material-icons-round" style="font-size:.9rem;vertical-align:middle;">{{ $sortDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>
                                @endif
                            </th>
                            <th id="th-date" role="button" wire:click="sortBy('date')">
                                Date
                                @if($sortField === 'date')
                                    <span class="material-icons-round" style="font-size:.9rem;vertical-align:middle;">{{ $sortDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>
                                @endif
                            </th>
                            <th id="th-supplier">Supplier</th>
                            <th id="th-store">Store</th>
                            <th id="th-items">Items</th>
                            <th id="th-nettotal" role="button" wire:click="sortBy('net_total')">
                                Net Total
                                @if($sortField === 'net_total')
                                    <span class="material-icons-round" style="font-size:.9rem;vertical-align:middle;">{{ $sortDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>
                                @endif
                            </th>
                            <th id="th-status" role="button" wire:click="sortBy('purchase_status')">
                                Status
                                @if($sortField === 'purchase_status')
                                    <span class="material-icons-round" style="font-size:.9rem;vertical-align:middle;">{{ $sortDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>
                                @endif
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($records as $i => $record)
                        <tr wire:key="purchase-report-{{ $record->id }}">
                            <td class="text-muted">{{ $records->firstItem() + $i }}</td>
                            <td class="fw-500 text-dark">{{ $record->bill_no }}</td>
                            <td class="text-muted" style="font-size:.78rem;">
                                {{ \Carbon\Carbon::parse($record->date)->format('d M Y') }}
                            </td>
                            <td class="text-muted" style="font-size:.78rem;">{{ $record->supplier?->name ?? '—' }}</td>
                            <td class="text-muted" style="font-size:.78rem;">{{ $record->store?->name ?? '—' }}</td>
                            <td class="text-muted" style="font-size:.78rem;">{{ $record->items_count }}</td>
                            <td class="text-dark" style="font-size:.78rem;">৳{{ number_format($record->net_total, 2) }}</td>
                            <td>
                                @php
                                    $statusMap = [
                                        'pending'   => 'bg-secondary-subtle text-secondary',
                                        'ordered'   => 'bg-info-subtle text-info',
                                        'completed' => 'bg-primary-subtle text-primary',
                                        'received'  => 'bg-success-subtle text-success',
                                        'cancelled' => 'bg-danger-subtle text-danger',
                                    ];
                                    $sc = $statusMap[$record->purchase_status] ?? 'bg-light text-dark';
                                @endphp
                                <span class="badge rounded-pill {{ $sc }}" style="font-size:.72rem;">{{ ucfirst($record->purchase_status) }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <span class="material-icons-round d-block mb-2" style="font-size:2.5rem;opacity:.25;">receipt_long</span>
                                কোনো purchase record পাওয়া যায়নি।
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer border-0 bg-white d-flex align-items-center justify-content-between flex-wrap gap-2 py-2 px-3">
            <small class="text-muted">Showing {{ $records->firstItem() ?? 0 }}–{{ $records->lastItem() ?? 0 }} of {{ $records->total() }}</small>
            {{ $records->links('vendor.pagination.custom') }}
        </div>

    </div>

</div>