{{-- resources/views/livewire/admin/report/salary-payment-report-component.blade.php --}}
<div>

    <div class="card">

        <div class="mat-card-header header-primary-gradient">
            <h5 id="cardHeaderTitleAllsections">Salary Payment Report</h5>
            <p id="cardHeaderSubtitle">Employee-wise মাসিক salary payment status ও amount দেখুন।</p>
        </div>

        <div class="card-body pt-3">

            @include('admin.report.salary-nav')

            {{-- Summary cards --}}
            <div class="row g-2 mb-3">
                <div class="col-6 col-md-3">
                    <div class="summary-card summary-present">
                        <span class="material-icons-round">receipt_long</span>
                        <div class="count">{{ $summary['total_payslips'] }}</div>
                        <div class="label">Total Payslips</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="summary-card summary-late">
                        <span class="material-icons-round">payments</span>
                        <div class="count">৳{{ number_format($summary['total_net'], 2) }}</div>
                        <div class="label">Net Salary</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="summary-card summary-leave">
                        <span class="material-icons-round">check_circle</span>
                        <div class="count">{{ $summary['paid_count'] }}</div>
                        <div class="label">Paid</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="summary-card summary-absent">
                        <span class="material-icons-round">pending_actions</span>
                        <div class="count">{{ $summary['pending_count'] }}</div>
                        <div class="label">Unpaid / Partial</div>
                    </div>
                </div>
            </div>

            {{-- Toolbar / Filters --}}
            <div class="card-toolbar flex-wrap mb-2">
                <div class="col-md-2">
                    <div class="input-group input-group-outline" wire:ignore>
                        <label class="form-label">Month From</label>
                        <input type="month" class="form-control form-control-sm" wire:model.live="monthFrom" data-dp-value="{{ $monthFrom }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="input-group input-group-outline" wire:ignore>
                        <label class="form-label">Month To</label>
                        <input type="month" class="form-control form-control-sm" wire:model.live="monthTo" data-dp-value="{{ $monthTo }}">
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="input-group input-group-outline" wire:ignore>
                        <label class="form-label">Designation</label>
                        <select class="form-select form-select-sm" wire:model.live="designationId">
                            <option value="all">All Designations</option>
                            @foreach ($designations as $designation)
                                <option value="{{ $designation->id }}">{{ $designation->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="input-group input-group-outline" wire:ignore>
                        <label class="form-label">Pay Method</label>
                        <select class="form-select form-select-sm" wire:model.live="paymentMethod">
                            <option value="all">All Methods</option>
                            <option value="cash">Cash</option>
                            <option value="card">Card</option>
                            <option value="bank">Bank</option>
                            <option value="cheque">Cheque</option>
                            <option value="mobile_banking">Mobile Banking</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="input-group input-group-outline" wire:ignore>
                        <label class="form-label">Status</label>
                        <select class="form-select form-select-sm" wire:model.live="status">
                            <option value="all">All Status</option>
                            <option value="paid">Paid</option>
                            <option value="unpaid">Unpaid</option>
                            <option value="partial">Partial</option>
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
                            <th id="th-month" role="button" wire:click="sortBy('month')">
                                Month
                                @if($sortField === 'month')
                                    <span class="material-icons-round" style="font-size:.9rem;vertical-align:middle;">{{ $sortDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>
                                @endif
                            </th>
                            <th id="th-employee">Employee</th>
                            <th id="th-designation">Designation</th>
                            <th id="th-gross" role="button" wire:click="sortBy('gross_salary')">
                                Gross
                                @if($sortField === 'gross_salary')
                                    <span class="material-icons-round" style="font-size:.9rem;vertical-align:middle;">{{ $sortDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>
                                @endif
                            </th>
                            <th id="th-advance">Advance Deduct</th>
                            <th id="th-net" role="button" wire:click="sortBy('net_salary')">
                                Net Salary
                                @if($sortField === 'net_salary')
                                    <span class="material-icons-round" style="font-size:.9rem;vertical-align:middle;">{{ $sortDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>
                                @endif
                            </th>
                            <th id="th-paydate" role="button" wire:click="sortBy('payment_date')">
                                Payment Date
                                @if($sortField === 'payment_date')
                                    <span class="material-icons-round" style="font-size:.9rem;vertical-align:middle;">{{ $sortDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>
                                @endif
                            </th>
                            <th id="th-method">Method</th>
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
                        <tr wire:key="salary-payment-{{ $record->id }}">
                            <td class="text-muted">{{ $records->firstItem() + $i }}</td>
                            <td class="text-muted" style="font-size:.78rem;">
                                {{ \Carbon\Carbon::parse($record->month)->format('M Y') }}
                            </td>
                            <td>
                                <div class="fw-500 text-dark">{{ $record->employee?->name ?? '— (deleted)' }}</div>
                                <small class="text-muted">{{ $record->employee?->employee_id }}</small>
                            </td>
                            <td class="text-muted" style="font-size:.78rem;">{{ $record->employee?->designation?->name ?? '—' }}</td>
                            <td class="text-dark" style="font-size:.78rem;">৳{{ number_format($record->gross_salary, 2) }}</td>
                            <td class="text-danger" style="font-size:.78rem;">৳{{ number_format($record->advance_deduction, 2) }}</td>
                            <td class="fw-500 text-dark" style="font-size:.78rem;">৳{{ number_format($record->net_salary, 2) }}</td>
                            <td class="text-muted" style="font-size:.78rem;">
                                {{ $record->payment_date ? \Carbon\Carbon::parse($record->payment_date)->format('d M Y') : '—' }}
                            </td>
                            <td class="text-muted" style="font-size:.78rem;">{{ ucfirst(str_replace('_', ' ', $record->payment_method)) }}</td>
                            <td>
                                @php
                                    $statusMap = [
                                        'paid'    => 'bg-success-subtle text-success',
                                        'partial' => 'bg-warning-subtle text-warning',
                                        'unpaid'  => 'bg-danger-subtle text-danger',
                                    ];
                                    $sc = $statusMap[$record->status] ?? 'bg-light text-dark';
                                @endphp
                                <span class="badge rounded-pill {{ $sc }}" style="font-size:.72rem;">{{ ucfirst($record->status) }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <span class="material-icons-round d-block mb-2" style="font-size:2.5rem;opacity:.25;">payments</span>
                                কোনো salary payment record পাওয়া যায়নি।
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
