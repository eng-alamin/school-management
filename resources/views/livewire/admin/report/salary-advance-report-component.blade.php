{{-- resources/views/livewire/admin/report/salary-advance-report-component.blade.php --}}
<div>

    <div class="card">

        <div class="mat-card-header header-primary-gradient">
            <h5 id="cardHeaderTitleAllsections">Salary Advance Report</h5>
            <p id="cardHeaderSubtitle">Employee advance, remaining balance ও settlement status দেখুন।</p>
        </div>

        <div class="card-body pt-3">

            @include('admin.report.salary-nav')

            {{-- Summary cards --}}
            <div class="row g-2 mb-3">
                <div class="col-6 col-md-3">
                    <div class="summary-card summary-present">
                        <span class="material-icons-round">receipt_long</span>
                        <div class="count">{{ $summary['total_advances'] }}</div>
                        <div class="label">Total Advances</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="summary-card summary-late">
                        <span class="material-icons-round">payments</span>
                        <div class="count">৳{{ number_format($summary['total_amount'], 2) }}</div>
                        <div class="label">Total Given</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="summary-card summary-absent">
                        <span class="material-icons-round">account_balance_wallet</span>
                        <div class="count">৳{{ number_format($summary['total_remaining'], 2) }}</div>
                        <div class="label">Remaining</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="summary-card summary-leave">
                        <span class="material-icons-round">pending_actions</span>
                        <div class="count">{{ $summary['active_count'] }}</div>
                        <div class="label">Active</div>
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
                        <select class="form-select form-select-sm" wire:model.live="status">
                            <option value="all">All Status</option>
                            <option value="active">Active</option>
                            <option value="settled">Settled</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Search</label>
                    <div style="position:relative;display:inline-flex;align-items:center;width:100%">
                        <span class="material-icons-round" style="position:absolute;left:10px;font-size:16px;color:var(--muted);pointer-events:none">search</span>
                        <input type="text" wire:model.live.debounce.400ms="search" placeholder="নাম / ID..." class="form-control form-control-sm" style="padding-left:32px;">
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
                            <th id="th-date" role="button" wire:click="sortBy('advance_date')">
                                Date
                                @if($sortField === 'advance_date')
                                    <span class="material-icons-round" style="font-size:.9rem;vertical-align:middle;">{{ $sortDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>
                                @endif
                            </th>
                            <th id="th-employee">Employee</th>
                            <th id="th-amount" role="button" wire:click="sortBy('amount')">
                                Amount
                                @if($sortField === 'amount')
                                    <span class="material-icons-round" style="font-size:.9rem;vertical-align:middle;">{{ $sortDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>
                                @endif
                            </th>
                            <th id="th-installment">Installment</th>
                            <th id="th-remaining" role="button" wire:click="sortBy('remaining_amount')">
                                Remaining
                                @if($sortField === 'remaining_amount')
                                    <span class="material-icons-round" style="font-size:.9rem;vertical-align:middle;">{{ $sortDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>
                                @endif
                            </th>
                            <th id="th-repayments">Repayments</th>
                            <th id="th-reason">Reason</th>
                            <th id="th-status" role="button" wire:click="sortBy('status')">
                                Status
                                @if($sortField === 'status')
                                    <span class="material-icons-round" style="font-size:.9rem;vertical-align:middle;">{{ $sortDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>
                                @endif
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($records as $i => $record)
                        <tr wire:key="salary-advance-{{ $record->id }}">
                            <td class="text-muted">{{ $records->firstItem() + $i }}</td>
                            <td class="text-muted" style="font-size:.78rem;">
                                {{ \Carbon\Carbon::parse($record->advance_date)->format('d M Y') }}
                            </td>
                            <td>
                                <div class="fw-500 text-dark">{{ $record->employee?->name ?? '— (deleted)' }}</div>
                                <small class="text-muted">{{ $record->employee?->employee_id }}</small>
                            </td>
                            <td class="text-dark" style="font-size:.78rem;">৳{{ number_format($record->amount, 2) }}</td>
                            <td class="text-muted" style="font-size:.78rem;">
                                {{ $record->installment_amount ? '৳' . number_format($record->installment_amount, 2) : 'Lump sum' }}
                            </td>
                            <td class="text-danger" style="font-size:.78rem;">৳{{ number_format($record->remaining_amount, 2) }}</td>
                            <td class="text-muted" style="font-size:.78rem;">{{ $record->repayments_count }}</td>
                            <td class="text-muted" style="font-size:.75rem;">{{ \Illuminate\Support\Str::limit($record->reason, 30) ?: '—' }}</td>
                            <td>
                                @php
                                    $statusMap = [
                                        'active'  => 'bg-warning-subtle text-warning',
                                        'settled' => 'bg-success-subtle text-success',
                                    ];
                                    $sc = $statusMap[$record->status] ?? 'bg-light text-dark';
                                @endphp
                                <span class="badge rounded-pill {{ $sc }}" style="font-size:.72rem;">{{ ucfirst($record->status) }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <span class="material-icons-round d-block mb-2" style="font-size:2.5rem;opacity:.25;">account_balance_wallet</span>
                                কোনো advance record পাওয়া যায়নি।
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
