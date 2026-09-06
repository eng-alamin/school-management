{{-- resources/views/livewire/admin/report/inventory-sale-report-component.blade.php --}}
<div>

    <div class="card">

        <div class="mat-card-header header-primary-gradient">
            <h5 id="cardHeaderTitleAllsections">Sale Report</h5>
            <p id="cardHeaderSubtitle">Inventory sales, payment status ও due amount একসাথে দেখুন।</p>
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
                        <div class="count">৳{{ number_format($summary['total_net'], 2) }}</div>
                        <div class="label">Net Payable</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="summary-card summary-leave">
                        <span class="material-icons-round">account_balance_wallet</span>
                        <div class="count">৳{{ number_format($summary['total_received'], 2) }}</div>
                        <div class="label">Received</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="summary-card summary-absent">
                        <span class="material-icons-round">warning</span>
                        <div class="count">৳{{ number_format($summary['total_due'], 2) }}</div>
                        <div class="label">Due</div>
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
                        <label class="form-label">Payment Status</label>
                        <select class="form-select form-select-sm" wire:model.live="paymentStatus">
                            <option value="all">All Status</option>
                            <option value="paid">Paid</option>
                            <option value="partial">Partial</option>
                            <option value="due">Due</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="input-group input-group-outline" wire:ignore>
                        <label class="form-label">Pay Via</label>
                        <select class="form-select form-select-sm" wire:model.live="payVia">
                            <option value="all">All</option>
                            <option value="cash">Cash</option>
                            <option value="card">Card</option>
                            <option value="mobile_banking">Mobile Banking</option>
                            <option value="bank">Bank</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Search</label>
                    <div style="position:relative;display:inline-flex;align-items:center;width:100%">
                        <span class="material-icons-round" style="position:absolute;left:10px;font-size:16px;color:var(--muted);pointer-events:none">search</span>
                        <input type="text" wire:model.live.debounce.400ms="search" placeholder="Bill No / Customer..." class="form-control form-control-sm" style="padding-left:32px;">
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
                            <th id="th-customer">Customer</th>
                            <th id="th-items">Items</th>
                            <th id="th-netpayable" role="button" wire:click="sortBy('net_payable')">
                                Net Payable
                                @if($sortField === 'net_payable')
                                    <span class="material-icons-round" style="font-size:.9rem;vertical-align:middle;">{{ $sortDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>
                                @endif
                            </th>
                            <th id="th-received" role="button" wire:click="sortBy('received_amount')">
                                Received
                                @if($sortField === 'received_amount')
                                    <span class="material-icons-round" style="font-size:.9rem;vertical-align:middle;">{{ $sortDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>
                                @endif
                            </th>
                            <th id="th-due" role="button" wire:click="sortBy('due_amount')">
                                Due
                                @if($sortField === 'due_amount')
                                    <span class="material-icons-round" style="font-size:.9rem;vertical-align:middle;">{{ $sortDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>
                                @endif
                            </th>
                            <th id="th-status">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($records as $i => $record)
                        <tr wire:key="sale-report-{{ $record->id }}">
                            <td class="text-muted">{{ $records->firstItem() + $i }}</td>
                            <td class="fw-500 text-dark">{{ $record->bill_no }}</td>
                            <td class="text-muted" style="font-size:.78rem;">
                                {{ \Carbon\Carbon::parse($record->date)->format('d M Y') }}
                            </td>
                            <td class="text-muted" style="font-size:.78rem;">
                                {{ $record->saleable?->name ?? 'Walk-in Customer' }}
                            </td>
                            <td class="text-muted" style="font-size:.78rem;">{{ $record->items_count }}</td>
                            <td class="text-dark" style="font-size:.78rem;">৳{{ number_format($record->net_payable, 2) }}</td>
                            <td class="text-success" style="font-size:.78rem;">৳{{ number_format($record->received_amount, 2) }}</td>
                            <td class="text-danger" style="font-size:.78rem;">৳{{ number_format($record->due_amount, 2) }}</td>
                            <td>
                                @php
                                    $statusMap = [
                                        'paid'    => 'bg-success-subtle text-success',
                                        'partial' => 'bg-warning-subtle text-warning',
                                        'due'     => 'bg-danger-subtle text-danger',
                                    ];
                                    $sc = $statusMap[$record->payment_status] ?? 'bg-light text-dark';
                                @endphp
                                <span class="badge rounded-pill {{ $sc }}" style="font-size:.72rem;">{{ ucfirst($record->payment_status) }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <span class="material-icons-round d-block mb-2" style="font-size:2.5rem;opacity:.25;">receipt_long</span>
                                কোনো sale record পাওয়া যায়নি।
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