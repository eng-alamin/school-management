<div>
    <div class="card">

        {{-- ── Floating Header ── --}}
        <div class="mat-card-header header-pink-gradient">
            <h5>Salary History</h5>
            <p>View your monthly salary payment records</p>
        </div>

        {{-- ══════════════════════════════════════
            SALARY LIST
        ══════════════════════════════════════ --}}
        <div class="form-section" style="padding-top:40px;padding-bottom:20px">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                <div class="section-heading mb-0">
                    <span class="material-icons-round">payments</span> Payment History
                </div>

                @if($salaries->total() > 10)
                    <select wire:model.live="perPage" class="form-select form-select-sm" style="width:90px">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                @endif
            </div>

            @if($salaries->count() > 0)
            <div class="table-responsive mt-3">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th>Basic Salary</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($salaries as $salary)
                        <tr wire:key="sal-{{ $salary->id }}">
                            <td>{{ \Carbon\Carbon::parse($salary->month)->format('F, Y') }}</td>
                            <td>${{ number_format($salary->basic_salary, 2) }}</td>
                            <td>
                                @if($salary->status === 'paid')
                                    <span class="status-badge status-paid">Paid</span>
                                @elseif($salary->status === 'partial')
                                    <span class="status-badge status-partial">Partial</span>
                                @else
                                    <span class="status-badge status-unpaid">Unpaid</span>
                                @endif
                            </td>
                            <td>
                                @if($salary->status === 'paid')
                                    <a class="action-btn btn-payslip" href="{{ route('teacher.salary.payslip', ['id' => $salary->employee_id, 'month' => \Carbon\Carbon::parse($salary->month)->format('Y-m')]) }}">
                                        <span class="material-icons-round" style="font-size:14px;vertical-align:middle">visibility</span>
                                        Payslip
                                    </a>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mt-3 px-1">
                <small class="text-muted">
                    Showing {{ $salaries->firstItem() ?? 0 }}–{{ $salaries->lastItem() ?? 0 }} of {{ $salaries->total() }} records
                </small>
                {{ $salaries->links('vendor.pagination.custom') }}
            </div>

            @else
            <div class="text-center py-5 text-muted">
                <span class="material-icons-round d-block mb-2" style="font-size:3rem;opacity:.2">inbox</span>
                No salary records found.
            </div>
            @endif
        </div>

    </div>
</div>

@push('styles')
<style>
    .status-badge {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 600;
        border: 1px solid;
        white-space: nowrap;
    }
    .status-paid    { color: #16a34a; border-color: #16a34a; background: rgba(22,163,74,.08); }
    .status-unpaid  { color: #2563eb; border-color: #2563eb; background: rgba(37,99,235,.08); }
    .status-partial { color: #d97706; border-color: #d97706; background: rgba(217,119,6,.08);  }

    .action-btn {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 5px 12px;
        border-radius: 5px;
        font-size: 12px;
        font-weight: 600;
        border: none;
        cursor: pointer;
        transition: opacity .15s;
        white-space: nowrap;
    }
    .action-btn:hover { opacity: .85; }
    .btn-payslip { background: #1f2937; color: #fff; }
</style>
@endpush