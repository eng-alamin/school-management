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
                        {{ \Carbon\Carbon::createFromFormat('Y-m', $month)->format('F') }}
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
                            <td class="text-end">${{ number_format($row['amount'], 2) }}</td>
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
                            <td class="text-end">${{ number_format($row['amount'], 2) }}</td>
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
                    <span class="ps-sum-value">${{ number_format($payment['basic_salary'] ?? 0, 2) }}</span>
                </div>
                <div class="ps-summary-row">
                    <span class="ps-sum-label">Total Allowance :</span>
                    <span class="ps-sum-value">${{ number_format($payment['total_allowance'] ?? 0, 2) }}</span>
                </div>
                @if(($payment['overtime_amount'] ?? 0) > 0)
                <div class="ps-summary-row">
                    <span class="ps-sum-label">Overtime Amount :</span>
                    <span class="ps-sum-value">${{ number_format($payment['overtime_amount'], 2) }}</span>
                </div>
                @endif
                <div class="ps-summary-row">
                    <span class="ps-sum-label">Total Deduction :</span>
                    <span class="ps-sum-value">${{ number_format($payment['total_deduction'] ?? 0, 2) }}</span>
                </div>
                <div class="ps-summary-row ps-net-row">
                    <span class="ps-sum-label">Net Salary :</span>
                    <span class="ps-sum-value ps-net-value">${{ number_format($payment['net_salary'] ?? 0, 2) }}</span>
                </div>
                <div class="ps-words">
                    {{ $this->numberToWords((float) ($payment['net_salary'] ?? 0)) }}
                </div>
            </div>
        </div>

    </div>{{-- /payslip-wrapper --}}


    {{-- ── Print Button (outside printable area) ── --}}
    <div class="no-print d-flex justify-content-end mt-3 gap-2">
        <a href="{{ route('admin.salary.payment') }}"
        class="btn-outline d-inline-flex align-items-center gap-1" style="text-decoration:none">
            <span class="material-icons-round" style="font-size:16px">arrow_back</span>
            Back
        </a>
        <button onclick="printPayslip()" class="btn-pink d-inline-flex align-items-center gap-1">
            <span class="material-icons-round" style="font-size:16px;vertical-align:middle">print</span>
            Print Payslip
        </button>
    </div>

</div>

@push('styles')
<style>
    /* ── Wrapper ── */
    .payslip-wrapper {
        background: var(--card-bg, #ffffff);
        border: 1px solid var(--border, #333);
        border-radius: 10px;
        padding: 32px 36px;
        font-size: 13px;
        color: inherit;
    }

    /* ── Header ── */
    .ps-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        margin-bottom: 20px;
    }
    .ps-logo {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .ps-logo img {
        height: 48px;
        width: auto;
        object-fit: contain;
    }
    .ps-logo-placeholder {
        width: 48px;
        height: 48px;
        border: 2px solid var(--primary, #e74c3c);
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .ps-school-name {
        font-size: 20px;
        font-weight: 800;
        color: var(--primary, #e74c3c);
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    /* Payslip meta (top-right) */
    .ps-meta {
        text-align: right;
    }
    .ps-meta-row {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 6px;
        line-height: 1.8;
    }
    .ps-meta-row:first-child .ps-meta-value {
        font-size: 15px;
        font-weight: 700;
    }
    .ps-meta-label { color: var(--text-muted, #9ca3af); font-size: 12px; }
    .ps-meta-value { font-weight: 600; font-size: 12px; }

    /* ── Divider ── */
    .ps-divider {
        height: 1px;
        background: var(--primary, #e74c3c);
        margin: 16px 0 20px;
        opacity: .5;
    }

    /* ── Address row ── */
    .ps-address-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 28px;
        gap: 20px;
    }
    .ps-address-block { flex: 1; }
    .ps-from         { text-align: right; }
    .ps-address-label {
        font-size: 11px;
        color: var(--text-muted, #9ca3af);
        text-transform: uppercase;
        letter-spacing: .06em;
        margin-bottom: 4px;
    }
    .ps-address-name {
        font-weight: 700;
        font-size: 14px;
        margin-bottom: 3px;
    }
    .ps-address-line {
        font-size: 12px;
        color: var(--text-muted, #9ca3af);
        line-height: 1.7;
    }

    /* ── Tables row ── */
    .ps-tables-row {
        display: flex;
        gap: 20px;
        margin-bottom: 28px;
    }
    .ps-table-box {
        flex: 1;
        border: 1px solid var(--border, #333);
        border-radius: 8px;
        overflow: hidden;
    }
    .ps-table-title {
        padding: 10px 14px;
        font-weight: 700;
        font-size: 13px;
        border-bottom: 2px solid var(--primary, #e74c3c);
    }
    .ps-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }
    .ps-table thead th {
        padding: 8px 12px;
        font-size: 12px;
        font-weight: 700;
        border-bottom: 1px solid var(--border, #333);
        text-align: left;
    }
    .ps-table thead th.text-end { text-align: right; }
    .ps-table tbody td {
        padding: 9px 12px;
        border-bottom: 1px solid var(--border, #2a2a2a);
        font-weight: 600;
    }
    .ps-table tbody td.text-end { text-align: right; }
    .ps-table tbody tr:last-child td { border-bottom: none; }
    .ps-empty {
        text-align: center;
        padding: 16px !important;
        color: var(--primary, #e74c3c);
        font-style: italic;
        font-weight: 400 !important;
    }

    /* ── Summary ── */
    .ps-summary-wrap {
        display: flex;
        justify-content: flex-end;
    }
    .ps-summary {
        min-width: 300px;
    }
    .ps-summary-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 5px 0;
        border-bottom: 1px solid var(--border, #2a2a2a);
        gap: 24px;
    }
    .ps-summary-row:last-of-type { border-bottom: none; }
    .ps-sum-label { color: var(--text-muted, #9ca3af); font-size: 12px; }
    .ps-sum-value { font-weight: 600; font-size: 12px; }
    .ps-net-row   { padding-top: 10px; }
    .ps-net-value {
        font-weight: 700;
        font-size: 14px;
        color: var(--primary, #e74c3c);
    }
    .ps-words {
        font-size: 11px;
        color: var(--text-muted, #9ca3af);
        text-align: right;
        margin-top: 4px;
        font-style: italic;
    }

    /* ── Print ── */
    @media print {
        .no-print  { display: none !important; }
        body       { background: #fff !important; color: #000 !important; }
        .payslip-wrapper {
            border: none;
            padding: 0;
            background: #fff;
            color: #000;
        }
        .ps-divider     { background: #e74c3c; }
        .ps-table-box   { border-color: #ddd; }
        .ps-table thead th,
        .ps-table tbody td { border-color: #ddd; }
        .ps-summary-row { border-color: #ddd; }
    }
</style>
@endpush

@push('scripts')
<script>
    function printPayslip() {
        window.print();
    }
</script>
@endpush