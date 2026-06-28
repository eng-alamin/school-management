<div>

    <div class="card">

        <!-- floating header -->
        <div class="mat-card-header header-pink-gradient">
            <h5>Transactions</h5>
            <p>View all deposit and expense transactions with running balance.</p>
        </div>

        <div class="card-header border-0">
            <div class="card-toolbar">
                {{-- Left side --}}
                <div class="card-toolbar-title">
                    <div style="position:relative;display:inline-flex;align-items:center">
                        <span class="material-icons-round" style="position:absolute;left:10px;font-size:17px;color:var(--muted);pointer-events:none">search</span>
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search" style="border:1px solid rgba(0,0,0,.1);border-radius:8px;padding:7px 12px 7px 32px;font-size:.78rem;font-family:inherit;color:var(--dark);outline:none;background:#f8f9fa;width:220px"/>
                    </div>
                </div>

                {{-- Filter: Type --}}
                <select class="form-select form-select-sm" wire:model.live="filterType" style="width:130px">
                    <option value="">All Types</option>
                    <option value="Deposit">Deposit</option>
                    <option value="Expense">Expense</option>
                </select>

                {{-- Filter: Account --}}
                <select class="form-select form-select-sm" wire:model.live="filterAccount" style="width:160px">
                    <option value="">All Accounts</option>
                    @foreach($accounts as $account)
                        <option value="{{ $account->id }}">{{ $account->name }}</option>
                    @endforeach
                </select>

                {{-- Per page --}}
                @if($transactions->total() > 10)
                    <div class="col-md-2">
                        <select class="form-select form-select-sm" wire:model.live="perPage">
                            <option value="10">10 / page</option>
                            <option value="25">25 / page</option>
                            <option value="50">50 / page</option>
                        </select>
                    </div>
                @endif
            </div>
        </div>

        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th style="font-size:12px">SL</th>
                            <th wire:click="sortBy('account_id')" style="font-size:12px; cursor:pointer">
                                Account Name @if($sortField === 'account_id') {!! $sortDirection === 'asc' ? '↑' : '↓' !!} @endif
                            </th>
                            <th wire:click="sortBy('type')" style="font-size:12px; cursor:pointer">
                                Type @if($sortField === 'type') {!! $sortDirection === 'asc' ? '↑' : '↓' !!} @endif
                            </th>
                            <th style="font-size:12px">Voucher Head</th>
                            <th wire:click="sortBy('reference')" style="font-size:12px; cursor:pointer">
                                Ref No @if($sortField === 'reference') {!! $sortDirection === 'asc' ? '↑' : '↓' !!} @endif
                            </th>
                            <th style="font-size:12px">Description</th>
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
                                $key     = $tx->type . '_' . $tx->id;
                                $balance = $balanceMap[$key] ?? 0;
                            @endphp
                            <tr>
                                <td style="font-size:12px" class="text-muted">{{ $transactions->firstItem() + $i }}</td>
                                <td style="font-size:12px">
                                    @if($tx->attachment)
                                        <a href="{{ Storage::url($tx->attachment) }}" target="_blank" title="View Attachment">
                                            <span class="material-icons-round" style="font-size:15px;vertical-align:middle;margin-right:3px">attach_file</span>
                                        </a>
                                    @endif
                                    {{ \App\Models\OfficeAccount::find($tx->account_id)?->name ?? '—' }}
                                </td>
                                <td style="font-size:12px">
                                    <span class="badge rounded-pill {{ $tx->type === 'Deposit' ? 'badge-active' : 'badge-danger' }}">
                                        {{ $tx->type }}
                                    </span>
                                </td>
                                <td style="font-size:12px">{{ \App\Models\OfficeHead::find($tx->head_id)?->name ?? '—' }}</td>
                                <td style="font-size:12px">{{ $tx->reference ?? '—' }}</td>
                                <td style="font-size:12px" style="max-width:280px;white-space:normal;font-size:.8rem">
                                    {{ $tx->description ?? '—' }}
                                </td>
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
                                <td colspan="12" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox display-5 d-block mb-2 opacity-25"></i>
                                    No transactions found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer border-0 bg-white d-flex align-items-center justify-content-between flex-wrap gap-2 py-2 px-3">
            <small class="text-muted">Showing {{ $transactions->firstItem() ?? 0 }}–{{ $transactions->lastItem() ?? 0 }} of {{ $transactions->total() }}</small>
            {{ $transactions->links('vendor.pagination.custom') }}
        </div>

    </div>

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
        .badge-danger   { background: rgba(239,68,68,.12);   color: #dc2626; }

        .form-label { font-size: .8rem; font-weight: 600; color: var(--text-muted); margin-bottom: 4px; }
        .form-control, .form-select { border-radius: 8px; border: 1px solid var(--border); font-size: .875rem; padding: .45rem .75rem; transition: border-color .2s, box-shadow .2s; }
        .form-control:focus, .form-select:focus { border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-light); }

        .btn-sm { font-size: .78rem; padding: .3rem .65rem; border-radius: 6px; }

        .custom-pagination { display: flex; gap: 8px; align-items: center; }
        .custom-pagination li { list-style: none; }
        .custom-pagination button { min-width: 38px; height: 38px; border-radius: 10px; border: 1px solid #e0e0e0; background: #f5f5f5; color: #444; font-weight: 600; cursor: pointer; transition: all .2s ease; }
        .custom-pagination button:hover { background: #eee; }
        .custom-pagination button.active { background: linear-gradient(195deg, #ec407a, #d81b60); color: #fff; border: none; box-shadow: 0 4px 12px rgba(216,27,96,.4); }
        .custom-pagination button:disabled { opacity: .5; cursor: not-allowed; }
    </style>
@endpush