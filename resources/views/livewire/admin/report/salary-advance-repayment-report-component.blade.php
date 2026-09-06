{{-- resources/views/livewire/admin/report/salary-advance-repayment-report-component.blade.php --}}
<div>

    <div class="card">

        <div class="mat-card-header header-primary-gradient">
            <h5 id="cardHeaderTitleAllsections">Advance Repayment Report</h5>
            <p id="cardHeaderSubtitle">কোন salary payment থেকে কোন advance কত deduct হয়েছে তার audit trail।</p>
        </div>

        <div class="card-body pt-3">

            @include('admin.report.salary-nav')

            {{-- Summary cards --}}
            <div class="row g-2 mb-3">
                <div class="col-6 col-md-6">
                    <div class="summary-card summary-present">
                        <span class="material-icons-round">receipt_long</span>
                        <div class="count">{{ $summary['total_repayments'] }}</div>
                        <div class="label">Total Repayments</div>
                    </div>
                </div>
                <div class="col-6 col-md-6">
                    <div class="summary-card summary-late">
                        <span class="material-icons-round">payments</span>
                        <div class="count">৳{{ number_format($summary['total_amount'], 2) }}</div>
                        <div class="label">Total Deducted</div>
                    </div>
                </div>
            </div>

            {{-- Toolbar / Filters --}}
            <div class="card-toolbar flex-wrap mb-2">
                <div class="col-md-3">
                    <div class="input-group input-group-outline" wire:ignore>
                        <label class="form-label">From</label>
                        <input type="date" class="form-control form-control-sm" wire:model.live="dateFrom" data-dp-value="{{ $dateFrom }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="input-group input-group-outline" wire:ignore>
                        <label class="form-label">To</label>
                        <input type="date" class="form-control form-control-sm" wire:model.live="dateTo" data-dp-value="{{ $dateTo }}">
                    </div>
                </div>

                <div class="col-md-3">
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
                            <th id="th-date" role="button" wire:click="sortBy('deducted_date')">
                                Deducted Date
                                @if($sortField === 'deducted_date')
                                    <span class="material-icons-round" style="font-size:.9rem;vertical-align:middle;">{{ $sortDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>
                                @endif
                            </th>
                            <th id="th-employee">Employee</th>
                            <th id="th-salarymonth">Salary Month</th>
                            <th id="th-amount" role="button" wire:click="sortBy('amount')">
                                Amount
                                @if($sortField === 'amount')
                                    <span class="material-icons-round" style="font-size:.9rem;vertical-align:middle;">{{ $sortDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>
                                @endif
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($records as $i => $record)
                        <tr wire:key="advance-repayment-{{ $record->id }}">
                            <td class="text-muted">{{ $records->firstItem() + $i }}</td>
                            <td class="text-muted" style="font-size:.78rem;">
                                {{ \Carbon\Carbon::parse($record->deducted_date)->format('d M Y') }}
                            </td>
                            <td>
                                <div class="fw-500 text-dark">{{ $record->salaryAdvance?->employee?->name ?? '— (deleted)' }}</div>
                                <small class="text-muted">{{ $record->salaryAdvance?->employee?->employee_id }}</small>
                            </td>
                            <td class="text-muted" style="font-size:.78rem;">
                                {{ $record->salaryPayment?->month ? \Carbon\Carbon::parse($record->salaryPayment->month)->format('M Y') : '— (manual)' }}
                            </td>
                            <td class="text-dark" style="font-size:.78rem;">৳{{ number_format($record->amount, 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted">
                                <span class="material-icons-round d-block mb-2" style="font-size:2.5rem;opacity:.25;">history</span>
                                কোনো repayment record পাওয়া যায়নি।
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
