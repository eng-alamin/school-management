<div>
    <div class="payslip-wrapper" id="payslip-area">

        {{-- ══════════════════════════════════════════
            HEADER: Logo | Payslip Info
        ══════════════════════════════════════════ --}}
        <div class="ps-header">
            <div class="ps-logo">
                @if($schoolLogo)
                    <img src="{{ asset('storage/' . $schoolLogo) }}" alt="Logo">
                @else
                    <div class="ps-logo-placeholder">
                        <span class="material-icons-round" style="font-size:32px;color:var(--primary,#e74c3c)">school</span>
                    </div>
                @endif
                @if($schoolName)
                    <span class="ps-school-name">{{ $schoolName }}</span>
                @endif
            </div>

            <div class="ps-meta">
                <div class="ps-meta-row">
                    <span class="ps-meta-label">Payslip No</span>
                    <span class="ps-meta-value">#{{ str_pad($payment['id'] ?? 0, 4, '0', STR_PAD_LEFT) }}</span>
                </div>
                <div class="ps-meta-row">
                    <span class="ps-meta-label">Date :</span>
                    <span class="ps-meta-value">
                        {{ $payment['payment_date']
                            ? \Carbon\Carbon::parse($payment['payment_date'])->format('d.M.Y')
                            : \Carbon\Carbon::now()->format('d.M.Y') }}
                    </span>
                </div>
                <div class="ps-meta-row">
                    <span class="ps-meta-label">Salary Month :</span>
                    <span class="ps-meta-value">
                        {{--
                            FIX: previously only showed month name (e.g. "May"),
                            missing the year. An official payslip must show the
                            year too, otherwise the same month across different
                            years becomes indistinguishable in payment history.
                        --}}
                        {{ \Carbon\Carbon::createFromFormat('Y-m', $month)->format('F Y') }}
                    </span>
                </div>
            </div>
        </div>

        <div class="ps-divider"></div>

        {{-- ══════════════════════════════════════════
            TO / FROM
        ══════════════════════════════════════════ --}}
        <div class="ps-address-row">

            {{-- To: Employee --}}
            <div class="ps-address-block">
                <div class="ps-address-label">To :</div>
                <div class="ps-address-name">{{ $employee['name'] ?? '—' }}</div>
                <div class="ps-address-line">Department : {{ $employee['department']['name'] ?? '—' }}</div>
                <div class="ps-address-line">Designation : {{ $employee['designation']['name'] ?? '—' }}</div>
                @if(!empty($employee['mobile']))
                <div class="ps-address-line">Mobile No : {{ $employee['mobile'] }}</div>
                @endif
            </div>

            {{-- From: School --}}
            <div class="ps-address-block ps-from">
                <div class="ps-address-label text-end">From :</div>
                @if($schoolName)
                <div class="ps-address-name text-end">{{ $schoolName }}</div>
                @endif
                @if($schoolAddress)
                <div class="ps-address-line text-end">{{ $schoolAddress }}</div>
                @endif
                @if($schoolPhone)
                <div class="ps-address-line text-end">{{ $schoolPhone }}</div>
                @endif
                @if($schoolEmail)
                <div class="ps-address-line text-end">{{ $schoolEmail }}</div>
                @endif
            </div>

        </div>

        {{-- ══════════════════════════════════════════
            ALLOWANCES & DEDUCTIONS TABLES
        ══════════════════════════════════════════ --}}
        <div class="ps-tables-row">

            {{-- Allowances --}}
            <div class="ps-table-box">
                <div class="ps-table-title">Allowances</div>
                <table class="ps-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th class="text-end">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($allowances as $row)
                        <tr>
                            <td>{{ $row['name'] }}</td>
                            <td class="text-end">৳{{ number_format($row['amount'], 0) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="2" class="ps-empty">No Information Available</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Deductions --}}
            <div class="ps-table-box">
                <div class="ps-table-title">Deductions</div>
                <table class="ps-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th class="text-end">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($deductions as $row)
                        <tr>
                            <td>{{ $row['name'] }}</td>
                            <td class="text-end">৳{{ number_format($row['amount'], 0) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="2" class="ps-empty">No Information Available</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>

        {{-- ══════════════════════════════════════════
            SALARY SUMMARY (right-aligned)
        ══════════════════════════════════════════ --}}
        <div class="ps-summary-wrap">
            <div class="ps-summary">
                <div class="ps-summary-row">
                    <span class="ps-sum-label">Basic Salary :</span>
                    <span class="ps-sum-value">৳{{ number_format($payment['basic_salary'] ?? 0, 0) }}</span>
                </div>
                <div class="ps-summary-row">
                    <span class="ps-sum-label">Total Allowance :</span>
                    <span class="ps-sum-value">৳{{ number_format($payment['total_allowance'] ?? 0, 0) }}</span>
                </div>
                @if(($payment['overtime_amount'] ?? 0) > 0)
                <div class="ps-summary-row">
                    <span class="ps-sum-label">Overtime Amount :</span>
                    <span class="ps-sum-value">৳{{ number_format($payment['overtime_amount'], 0) }}</span>
                </div>
                @endif
                <div class="ps-summary-row">
                    <span class="ps-sum-label">Total Deduction :</span>
                    <span class="ps-sum-value">৳{{ number_format($payment['total_deduction'] ?? 0, 0) }}</span>
                </div>
                @if(($payment['advance_deduction'] ?? 0) > 0)
                <div class="ps-summary-row">
                    <span class="ps-sum-label">Advance Deduction :</span>
                    <span class="ps-sum-value">৳{{ number_format($payment['advance_deduction'], 0) }}</span>
                </div>
                @endif
                <div class="ps-summary-row ps-net-row">
                    <span class="ps-sum-label">Net Salary :</span>
                    <span class="ps-sum-value ps-net-value">৳{{ number_format($payment['net_salary'] ?? 0, 0) }}</span>
                </div>
                <div class="ps-words">
                    {{ $this->numberToWords((float) ($payment['net_salary'] ?? 0)) }}
                </div>
            </div>
        </div>

    </div>{{-- /payslip-wrapper --}}


    {{-- ── Print Button (outside printable area) ── --}}
    <div class="no-print d-flex justify-content-end mt-3 gap-2">
        <button type="button" class="btn btn-primary" onclick="history.back()">
            <span class="material-icons-round" style="font-size:16px">arrow_back</span>
            Back
        </button>
        <button onclick="printPayslip()" class="btn btn-primary d-inline-flex align-items-center gap-1">
            <span class="material-icons-round" style="font-size:16px;vertical-align:middle">print</span>
            Print Payslip
        </button>
    </div>

</div>

@push('scripts')
<script>
    function printPayslip() {
        window.print();
    }
</script>
@endpush