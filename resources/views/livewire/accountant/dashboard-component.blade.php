{{-- resources/views/livewire/accountant/dashboard-component.blade.php --}}

<div class="dash-wrap">

    {{-- ══ Welcome Header ══════════════════════════════════════════════════ --}}
    <div class="dash-header px-3 pt-3 pb-2">
        <h5 class="fw-bold mb-0 text-dark">Welcome back, Accountant! 👋</h5>
        <p class="text-secondary mb-0" style="font-size:12px;">Here's your financial overview for today</p>
    </div>

    {{-- ══ Stat Cards ══════════════════════════════════════════════════════ --}}
    <div class="px-3 pt-2">
        <div class="row g-3">

            {{-- Fee Collected (Total) --}}
            <div class="col-6 col-md-3">
                <div class="dash-stat-card">
                    <div class="dash-stat-icon" style="background:#d1fae5;">
                        <span class="material-icons-round" style="color:#059669;">payments</span>
                    </div>
                    <p class="dash-stat-label">Fee Collected</p>
                    <h4 class="dash-stat-value privacy-amount">
                        @php
                            $fc = $totalFeeCollected;
                            echo $fc >= 100000 ? '৳'.number_format($fc/100000, 1).'L' : '৳'.number_format($fc);
                        @endphp
                    </h4>
                </div>
            </div>

            {{-- Fee Collected Today --}}
            <div class="col-6 col-md-3">
                <div class="dash-stat-card">
                    <div class="dash-stat-icon" style="background:#eef2ff;">
                        <span class="material-icons-round" style="color:#4f46e5;">today</span>
                    </div>
                    <p class="dash-stat-label">Collected Today</p>
                    <h4 class="dash-stat-value privacy-amount">
                        @php
                            $ft = $totalFeeToday;
                            echo $ft >= 100000 ? '৳'.number_format($ft/100000, 1).'L' : '৳'.number_format($ft);
                        @endphp
                    </h4>
                </div>
            </div>

            {{-- Due Fees --}}
            <div class="col-6 col-md-3">
                <div class="dash-stat-card">
                    <div class="dash-stat-icon" style="background:#fef2f2;">
                        <span class="material-icons-round" style="color:#dc2626;">warning_amber</span>
                    </div>
                    <p class="dash-stat-label">Due Fees</p>
                    <h4 class="dash-stat-value privacy-amount">
                        @php
                            $due = $totalFeeDue;
                            echo $due >= 100000 ? '৳'.number_format($due/100000, 1).'L' : '৳'.number_format($due);
                        @endphp
                    </h4>
                </div>
            </div>

            {{-- Total Students (Read-only) --}}
            <div class="col-6 col-md-3">
                <div class="dash-stat-card">
                    <div class="dash-stat-icon" style="background:#ede9fe;">
                        <span class="material-icons-round" style="color:#7c3aed;">school</span>
                    </div>
                    <p class="dash-stat-label">Total Students</p>
                    <h4 class="dash-stat-value">{{ number_format($totalStudents) }}</h4>
                </div>
            </div>

            {{-- Account Balance --}}
            <div class="col-6 col-md-3">
                <div class="dash-stat-card">
                    <div class="dash-stat-icon" style="background:#fce7f3;">
                        <span class="material-icons-round" style="color:#db2777;">account_balance_wallet</span>
                    </div>
                    <p class="dash-stat-label">Account Balance</p>
                    <h4 class="dash-stat-value privacy-amount">
                        @php
                            $bal = $accountBalance;
                            echo $bal >= 100000 ? '৳'.number_format($bal/100000, 1).'L' : '৳'.number_format($bal);
                        @endphp
                    </h4>
                </div>
            </div>

            {{-- Total Deposits --}}
            <div class="col-6 col-md-3">
                <div class="dash-stat-card">
                    <div class="dash-stat-icon" style="background:#ecfeff;">
                        <span class="material-icons-round" style="color:#0891b2;">north_east</span>
                    </div>
                    <p class="dash-stat-label">Total Deposits</p>
                    <h4 class="dash-stat-value privacy-amount">
                        @php
                            $dep = $totalDeposits;
                            echo $dep >= 100000 ? '৳'.number_format($dep/100000, 1).'L' : '৳'.number_format($dep);
                        @endphp
                    </h4>
                </div>
            </div>

            {{-- Total Expenses --}}
            <div class="col-6 col-md-3">
                <div class="dash-stat-card">
                    <div class="dash-stat-icon" style="background:#fff7ed;">
                        <span class="material-icons-round" style="color:#ea580c;">south_west</span>
                    </div>
                    <p class="dash-stat-label">Total Expenses</p>
                    <h4 class="dash-stat-value privacy-amount">
                        @php
                            $exp = $totalExpenses;
                            echo $exp >= 100000 ? '৳'.number_format($exp/100000, 1).'L' : '৳'.number_format($exp);
                        @endphp
                    </h4>
                </div>
            </div>

            {{-- Salary Unpaid --}}
            <div class="col-6 col-md-3">
                <div class="dash-stat-card">
                    <div class="dash-stat-icon" style="background:#fef3c7;">
                        <span class="material-icons-round" style="color:#d97706;">badge</span>
                    </div>
                    <p class="dash-stat-label">Salary Unpaid</p>
                    <h4 class="dash-stat-value privacy-amount">
                        @php
                            $su = $salaryUnpaidThisMonth;
                            echo $su >= 100000 ? '৳'.number_format($su/100000, 1).'L' : '৳'.number_format($su);
                        @endphp
                    </h4>
                </div>
            </div>

        </div>
    </div>

    {{-- ══ Recent Fee Payments ═════════════════════════════════════════════ --}}
    <div class="px-3 mt-4">
        <div class="dash-section-card">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="dash-section-title mb-0">
                    <span class="material-icons-round text-success" style="font-size:18px;">payments</span>
                    Recent Fee Payments
                </div>
                <a href="{{ route('accountant.student-accounting.fee.invoices') ?? '#' }}" class="dash-view-all">View all</a>
            </div>

            @forelse($recentPayments as $payment)
                <div class="dash-notice-row">
                    <div class="flex-grow-1 min-w-0">
                        <p class="mb-0 text-dark text-truncate" style="font-size:13px;font-weight:500;">
                            {{ $payment->student_name }}
                        </p>
                        <small class="text-secondary" style="font-size:11px;">
                            {{ ucfirst($payment->payment_method) }} • {{ \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') }}
                        </small>
                    </div>
                    <span class="fw-semibold text-success privacy-amount" style="font-size:13px;">
                        ৳{{ number_format($payment->amount, 0) }}
                    </span>
                </div>
            @empty
                <p class="text-center text-secondary py-2 mb-0" style="font-size:13px;">
                    আজ কোনো Payment Collect হয়নি
                </p>
            @endforelse
        </div>
    </div>

    {{-- ══ Recent Invoices ═════════════════════════════════════════════════ --}}
    <div class="px-3 mt-4">
        <div class="dash-section-card">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="dash-section-title mb-0">
                    <span class="material-icons-round text-warning" style="font-size:18px;">receipt_long</span>
                    Recent Invoices
                </div>
                <a href="{{ route('accountant.student-accounting.fee.invoices') ?? '#' }}" class="dash-view-all">View all</a>
            </div>

            @forelse($recentInvoices as $invoice)
                <div class="dash-notice-row">
                    <div class="flex-grow-1 min-w-0">
                        <p class="mb-0 text-dark text-truncate" style="font-size:13px;font-weight:500;">
                            {{ $invoice->student_name }} — #{{ $invoice->invoice_no }}
                        </p>
                        <small class="text-secondary privacy-amount" style="font-size:11px;">
                            Due: ৳{{ number_format($invoice->due_amount, 0) }}
                        </small>
                    </div>
                    @if($invoice->payment_status === 'paid')
                        <span class="inv-badge paid">Paid</span>
                    @elseif($invoice->payment_status === 'partial')
                        <span class="inv-badge partial">Partial</span>
                    @else
                        <span class="inv-badge unpaid">Unpaid</span>
                    @endif
                </div>
            @empty
                <p class="text-center text-secondary py-2 mb-0" style="font-size:13px;">
                    কোনো Invoice নেই
                </p>
            @endforelse
        </div>
    </div>

    {{-- ══ Quick Actions (Accountant relevant only) ═══════════════════════════ --}}
    <div class="px-3 mt-4">
        <div class="dash-section-card">
            <div class="dash-section-title">
                <span class="material-icons-round text-primary" style="font-size:18px;">flash_on</span>
                Quick Actions
            </div>
            <div class="row g-2 mt-1">
                @foreach([
                    ['icon'=>'receipt',              'label'=>'Fee Invoice',   'color'=>'#d97706', 'bg'=>'#fef3c7', 'href'=>route('accountant.student-accounting.fee.invoices') ?? '#'],
                    ['icon'=>'payments',             'label'=>'Fee Payment',   'color'=>'#059669', 'bg'=>'#d1fae5', 'href'=>route('accountant.student-accounting.fee.invoices') ?? '#'],
                    ['icon'=>'north_east',           'label'=>'Office Deposit','color'=>'#0891b2', 'bg'=>'#ecfeff', 'href'=>route('accountant.office-accounting.deposit.list') ?? '#'],
                    ['icon'=>'south_west',            'label'=>'Office Expense','color'=>'#ea580c', 'bg'=>'#fff7ed', 'href'=>route('accountant.office-accounting.expense.list') ?? '#'],
                    ['icon'=>'badge',                 'label'=>'Salary',        'color'=>'#7c3aed', 'bg'=>'#ede9fe', 'href'=>route('accountant.salary.payment') ?? '#'],
                    ['icon'=>'campaign',              'label'=>'Notices',  'color'=>'#db2777', 'bg'=>'#fce7f3', 'href'=>route('accountant.notices') ?? '#'],
                ] as $action)
                    <div class="col-4">
                        <a href="{{ $action['href'] }}" class="text-decoration-none">
                            <div class="dash-quick-action">
                                <div class="dash-quick-icon" style="background:{{ $action['bg'] }};">
                                    <span class="material-icons-round" style="color:{{ $action['color'] }};font-size:22px;">{{ $action['icon'] }}</span>
                                </div>
                                <span class="dash-quick-label">{{ $action['label'] }}</span>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ══ Recent Notices (Read-only) ══════════════════════════════════════ --}}
    <div class="px-3 mt-4">
        <div class="dash-section-card">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="dash-section-title mb-0">
                    <span class="material-icons-round text-danger" style="font-size:18px;">campaign</span>
                    Recent Notices
                </div>
                <a href="{{ route('accountant.notices') ?? '#' }}" class="dash-view-all">View all</a>
            </div>

            @forelse($recentNotices as $notice)
                <div class="dash-notice-row">
                    <div class="flex-grow-1 min-w-0">
                        <p class="mb-0 text-dark text-truncate" style="font-size:13px;font-weight:500;">
                            {{ $notice->title }}
                        </p>
                    </div>
                    <span class="material-icons-round text-secondary" style="font-size:18px;">chevron_right</span>
                </div>
            @empty
                <p class="text-center text-secondary py-2 mb-0" style="font-size:13px;">
                    কোনো Notice নেই
                </p>
            @endforelse
        </div>
    </div>

    {{-- ══ Recent Activity ══════════════════════════════════════════════════ --}}
    <div class="px-3 mt-4 mb-4">
        <div class="dash-section-card">
            <div class="dash-section-title">
                <span class="material-icons-round text-warning" style="font-size:18px;">bolt</span>
                Recent Activity
            </div>

            @forelse($recentActivities as $act)
                <div class="dash-activity-item">
                    <div class="dash-activity-icon">
                        <span class="material-icons-round" style="font-size:16px;color:#6b7280;">
                            {{ $act->icon ?? 'notifications' }}
                        </span>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <p class="mb-0 text-dark text-truncate" style="font-size:13px;">
                            {{ $act->description }}
                        </p>
                    </div>
                    <small class="text-secondary text-nowrap ms-2" style="font-size:11px;">
                        {{ \Carbon\Carbon::parse($act->created_at)->diffForHumans() }}
                    </small>
                </div>
            @empty
                <p class="text-center text-secondary py-2 mb-0" style="font-size:13px;">
                    কোনো activity নেই এখনো
                </p>
            @endforelse
        </div>
    </div>

</div>

{{-- ══ Scoped CSS ══════════════════════════════════════════════════════════ --}}
@push('styles')
<style>
    .dash-wrap {
        background: #f5f6fa;
        min-height: 100vh;
        padding-bottom: 24px;
    }

    .dash-header {
        padding-top: 16px;
    }

    .dash-stat-card {
        background: #ffffff;
        border-radius: 16px;
        padding: 14px;
        box-shadow: 0 1px 6px rgba(0,0,0,.06);
        height: 100%;
    }

    .dash-stat-icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 10px;
    }

    .dash-stat-icon .material-icons-round {
        font-size: 20px;
    }

    .dash-stat-label {
        font-size: 11px;
        color: #9ca3af;
        margin-bottom: 2px;
    }

    .dash-stat-value {
        font-size: 20px;
        font-weight: 700;
        color: #111827;
        margin-bottom: 4px;
    }

    .dash-section-card {
        background: #ffffff;
        border-radius: 16px;
        padding: 16px;
        box-shadow: 0 1px 6px rgba(0,0,0,.06);
    }

    .dash-section-title {
        font-size: 14px;
        font-weight: 600;
        color: #111827;
        display: flex;
        align-items: center;
        gap: 6px;
        margin-bottom: 12px;
    }

    .dash-view-all {
        font-size: 12px;
        color: #e94d82;
        font-weight: 500;
        text-decoration: none;
    }

    .dash-activity-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px 0;
        border-bottom: 1px solid #f3f4f6;
    }

    .dash-activity-item:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .dash-activity-icon {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        background: #f9fafb;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .dash-quick-action {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 6px;
        padding: 10px 4px;
        border-radius: 12px;
        background: #f9fafb;
        transition: background .15s;
    }

    .dash-quick-action:hover {
        background: #f3f4f6;
    }

    .dash-quick-icon {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .dash-quick-label {
        font-size: 11px;
        font-weight: 500;
        color: #374151;
        text-align: center;
        line-height: 1.3;
    }

    .dash-notice-row {
        display: flex;
        align-items: center;
        padding: 11px 12px;
        border-radius: 10px;
        background: #f9fafb;
        margin-bottom: 8px;
        gap: 8px;
    }

    .dash-notice-row:last-child {
        margin-bottom: 0;
    }

    .inv-badge {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 600;
        border: 1px solid transparent;
        flex-shrink: 0;
    }
    .inv-badge.paid    { background: transparent; border-color: #22c55e; color: #22c55e; }
    .inv-badge.partial { background: transparent; border-color: #f59e0b; color: #f59e0b; }
    .inv-badge.unpaid  { background: transparent; border-color: #ef4444; color: #ef4444; }

    @media (min-width: 768px) {
        .dash-stat-value {
            font-size: 22px;
        }
        .dash-quick-icon {
            width: 52px;
            height: 52px;
        }
    }

    /* ── Privacy Mode ────────────────────────────────────────────── */
    /* body.privacy-mode toggled globally via togglePrivacy() in settings JS */
    body.privacy-mode .privacy-amount {
        filter: blur(7px);
        user-select: none;
        cursor: pointer;
        transition: filter .15s;
    }

    /* click cheye reveal — inline script diye .revealed class add hoy */
    body.privacy-mode .privacy-amount.revealed {
        filter: blur(0);
    }
</style>
@endpush

@push('scripts')
<script>
    // Privacy amount ekta ekta click korle 2 second-er jonno dekhabe
    document.addEventListener('click', function (e) {
        const el = e.target.closest('.privacy-amount');
        if (!el || !document.body.classList.contains('privacy-mode')) return;

        el.classList.add('revealed');
        clearTimeout(el._privacyTimer);
        el._privacyTimer = setTimeout(() => el.classList.remove('revealed'), 2000);
    });
</script>
@endpush