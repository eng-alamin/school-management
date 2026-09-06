<div>

    <div class="card">

        <!-- floating header -->
        <div class="mat-card-header header-primary-gradient">
            <h5>Transactions</h5>
            <p>View all deposit and expense transactions with running balance.</p>
        </div>

        <div class="card-header border-0">
            <div class="card-toolbar flex-wrap gap-2">
                {{-- Left side --}}
                <div class="card-toolbar-title">
                    <div style="position:relative;display:inline-flex;align-items:center">
                        <span class="material-icons-round" style="position:absolute;left:10px;font-size:17px;color:var(--muted);pointer-events:none">search</span>
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search" class="tb-search"/>
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="input-group input-group-outline">
                        <select class="form-select form-select-sm" wire:model.live="filterType">
                            <option value="">All Types</option>
                            <option value="Deposit">Deposit</option>
                            <option value="Expense">Expense</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="input-group input-group-outline">
                        <select class="form-select form-select-sm" wire:model.live="filterAccount">
                            <option value="">All Accounts</option>
                            @foreach($accounts as $account)
                                <option value="{{ $account->id }}">{{ $account->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Filter: Date From --}}
                <div class="col-md-2">
                    <div class="input-group input-group-outline" wire:ignore>
                        <label class="form-label">Date From</label>
                        <input type="date" wire:model.live="filterDateFrom" class="form-control" onfocus="focused(this)" onfocusout="defocused(this)">
                        @error('filterDateFrom') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- Filter: Date To --}}
                <div class="col-md-2">
                    <div class="input-group input-group-outline" wire:ignore>
                        <label class="form-label">Date To</label>
                        <input type="date" wire:model.live="filterDateTo" class="form-control" onfocus="focused(this)" onfocusout="defocused(this)">
                        @error('filterDateTo') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>

                @if($filterDateFrom || $filterDateTo)
                    <button type="button" class="btn btn-sm btn-secondary no-print" wire:click="clearDateFilter" title="Clear date filter">
                        <span class="material-icons-round" style="font-size:14px;vertical-align:middle">close</span>
                    </button>
                @endif

                {{-- Per page --}}
                @if($transactions->total() > 10)
                    <div class="col-md-2">
                        <select class="form-select form-select-sm no-print" wire:model.live="perPage">
                            <option value="10">10 / page</option>
                            <option value="25">25 / page</option>
                            <option value="50">50 / page</option>
                        </select>
                    </div>
                @endif

                {{-- Print --}}
                <button type="button" class="btn btn-sm btn-primary no-print ms-auto" onclick="printTransactions()">
                    <span class="material-icons-round" style="font-size:15px;vertical-align:middle;margin-right:3px">print</span>
                    Print
                </button>
            </div>
        </div>

        <div class="card-body pt-0">

            <div id="transactionsPrintable">

                @if($filterDateFrom || $filterDateTo)
                    <div class="mb-2 text-muted" style="font-size:12px">
                        <strong>Date Range:</strong>
                        {{ $filterDateFrom ? \Carbon\Carbon::parse($filterDateFrom)->format('d M Y') : 'Beginning' }}
                        &mdash;
                        {{ $filterDateTo ? \Carbon\Carbon::parse($filterDateTo)->format('d M Y') : 'Now' }}
                    </div>
                @endif

                {{-- ── Account-wise Balance Summary (latest/current balance, computed from the
                     full unfiltered-by-pagination ledger, so it's always up to date) ── --}}
                <div class="mb-4">
                    <h6 style="font-size:13px;font-weight:600;color:var(--dark);margin-bottom:12px">
                        <span class="material-icons-round" style="font-size:16px;vertical-align:middle;margin-right:4px">account_balance_wallet</span>
                        Account Balances
                    </h6>
                    <div class="row g-3">
                        @forelse($accountBalances as $ab)
                            <div class="col-md-3 col-sm-6">
                                <div style="border:1px solid rgba(0,0,0,.08);border-radius:10px;padding:12px 14px;background:#fff">
                                    <div style="font-size:11px;color:var(--muted);margin-bottom:4px">{{ $ab['name'] }}</div>
                                    <div style="font-size:15px;font-weight:700;color:{{ $ab['balance'] < 0 ? '#dc3545' : '#198754' }}">
                                        {{ number_format($ab['balance'], 0) }}
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12 text-muted" style="font-size:12px">No accounts found.</div>
                        @endforelse
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th style="font-size:12px">SL</th>
                                <th wire:click="sortBy('account_name')" class="no-print-cursor" style="font-size:12px; cursor:pointer">
                                    Account Name @if($sortField === 'account_name') {!! $sortDirection === 'asc' ? '↑' : '↓' !!} @endif
                                </th>
                                <th wire:click="sortBy('type')" style="font-size:12px; cursor:pointer">
                                    Type @if($sortField === 'type') {!! $sortDirection === 'asc' ? '↑' : '↓' !!} @endif
                                </th>
                                <th style="font-size:12px">Voucher No</th>
                                <th style="font-size:12px">Voucher Head</th>
                                <th wire:click="sortBy('reference')" style="font-size:12px; cursor:pointer">
                                    Ref No @if($sortField === 'reference') {!! $sortDirection === 'asc' ? '↑' : '↓' !!} @endif
                                </th>
                                <th wire:click="sortBy('pay_via')" style="font-size:12px; cursor:pointer">
                                    Pay Via @if($sortField === 'pay_via') {!! $sortDirection === 'asc' ? '↑' : '↓' !!} @endif
                                </th>
                                <th wire:click="sortBy('amount')" style="font-size:12px; cursor:pointer">
                                    Amount @if($sortField === 'amount') {!! $sortDirection === 'asc' ? '↑' : '↓' !!} @endif
                                </th>
                                <th style="font-size:12px">Dr</th>
                                <th style="font-size:12px">Cr</th>
                                <th style="font-size:12px">Balance</th>
                                <th wire:click="sortBy('date')" style="font-size:12px; cursor:pointer">
                                    Date @if($sortField === 'date') {!! $sortDirection === 'asc' ? '↑' : '↓' !!} @endif
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transactions as $i => $tx)
                                @php
                                    $key = $tx->type . '_' . $tx->id;
                                    $balance = $balanceMap[$key] ?? 0;
                                @endphp
                                <tr wire:key="tx-{{ $tx->type }}-{{ $tx->id }}">
                                    <td style="font-size:12px" class="text-muted">{{ $transactions->firstItem() + $i }}</td>
                                    <td style="font-size:12px">
                                        @if($tx->attachment)
                                            <a href="{{ Storage::url($tx->attachment) }}" target="_blank" title="View Attachment" class="no-print">
                                                <span class="material-icons-round" style="font-size:15px;vertical-align:middle;margin-right:3px">attach_file</span>
                                            </a>
                                        @endif
                                        {{ $tx->account_name ?? '—' }}
                                    </td>
                                    <td style="font-size:12px">
                                        <span class="badge rounded-pill {{ $tx->type === 'Deposit' ? 'badge-active' : 'badge-expired' }}">
                                            {{ $tx->type }}
                                        </span>
                                    </td>
                                    <td style="font-size:12px">{{ $tx->voucher_no ?? '—' }}</td>
                                    <td style="font-size:12px">{{ $tx->head_name ?? '—' }}</td>
                                    <td style="font-size:12px">{{ $tx->reference ?? '—' }}</td>
                                    <td style="font-size:12px">{{ $tx->pay_via ?? '—' }}</td>
                                    <td style="font-size:12px">
                                        <span class="badge rounded-pill badge-used">
                                            {{ number_format($tx->amount, 0) }}
                                        </span>
                                    </td>
                                    <td style="font-size:12px" class="text-danger">
                                        {{ number_format($tx->dr, 0) }}
                                    </td>
                                    <td style="font-size:12px" class="text-success">
                                        {{ number_format($tx->cr, 0) }}
                                    </td>
                                    <td style="font-size:12px">
                                        <strong>{{ number_format($balance, 0) }}</strong>
                                    </td>
                                    <td style="font-size:12px">{{ \Carbon\Carbon::parse($tx->date)->format('d.M.Y') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="13" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox display-5 d-block mb-2 opacity-25"></i>
                                        No transactions found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        {{-- ── Overall Total: Deposit (Cr) + Expense (Dr) + Net Balance ── --}}
                        <tfoot>
                            <tr style="background:#f8f9fa;border-top:2px solid rgba(0,0,0,.08)">
                                <td colspan="8" class="text-end" style="font-size:12px"><strong>Total</strong></td>
                                <td style="font-size:12px" class="text-danger">
                                    <strong>{{ number_format($totalExpense, 0) }}</strong>
                                </td>
                                <td style="font-size:12px" class="text-success">
                                    <strong>{{ number_format($totalDeposit, 0) }}</strong>
                                </td>
                                <td style="font-size:12px">
                                    <strong>{{ number_format($netBalance, 0) }}</strong>
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

            </div>
            {{-- /#transactionsPrintable --}}

        </div>

        <div class="card-footer border-0 bg-white d-flex align-items-center justify-content-between flex-wrap gap-2 py-2 px-3 no-print">
            <small class="text-muted">Showing {{ $transactions->firstItem() ?? 0 }}–{{ $transactions->lastItem() ?? 0 }} of {{ $transactions->total() }}</small>
            {{ $transactions->links('vendor.pagination.custom') }}
        </div>

    </div>

</div>

@push('scripts')
    <script>
        function printTransactions() {
            const printableEl = document.getElementById('transactionsPrintable');

            if (!printableEl) {
                return;
            }

            const printContent = printableEl.innerHTML;
            const printWindow = window.open('', '_blank', 'width=1100,height=700');

            if (!printWindow) {
                alert('Print window block hoye গেছে। Browser-er popup blocker check korun.');
                return;
            }

            printWindow.document.write(`
                <html>
                    <head>
                        <title>Transactions - {{ institution()->name }}</title>
                        <style>
                            * { box-sizing: border-box; }
                            body { font-family: Arial, Helvetica, sans-serif; padding: 24px; color: #222; }
                            .no-print { display: none !important; }

                            table { width: 100%; border-collapse: collapse; }
                            th, td { padding: 6px 8px; text-align: left; border-bottom: 1px solid #eee; }
                            th { border-bottom: 2px solid #ccc; }
                            tfoot td { border-top: 2px solid #999; font-weight: 700; }

                            .row { display: flex; flex-wrap: wrap; margin: 0 -8px; }
                            .col-md-3 { width: 25%; padding: 6px 8px; }
                            .col-sm-6 { width: 25%; padding: 6px 8px; }
                            .col-12 { width: 100%; padding: 6px 8px; }

                            .mb-1 { margin-bottom: 4px; }
                            .mb-2 { margin-bottom: 8px; }
                            .mb-4 { margin-bottom: 16px; }

                            .text-muted { color: #6c757d !important; }
                            .text-danger { color: #dc3545 !important; }
                            .text-success { color: #198754 !important; }
                            .fw-bold, strong { font-weight: 700; }

                            .badge { display: inline-block; padding: 3px 10px; border-radius: 12px;
                                     background: #212529; color: #fff; font-size: 11px; }

                            .material-icons-round, .bi { display: none; } /* icon fonts aren't loaded here */

                            h6 { margin: 0 0 12px 0; font-size: 13px; }
                            h5 { margin: 0 0 4px 0; }
                        </style>
                    </head>
                    <body>
                        <h5>{{ institution()->name }} — Transactions</h5>
                        ${printContent}
                    </body>
                </html>
            `);

            printWindow.document.close();
            printWindow.focus();

            setTimeout(() => {
                printWindow.print();
                printWindow.close();
            }, 250);
        }
    </script>
@endpush