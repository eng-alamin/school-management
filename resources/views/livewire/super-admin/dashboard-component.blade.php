{{-- resources/views/livewire/super-admin/dashboard-component.blade.php --}}

<div class="dash-wrap">

    {{-- ══ Welcome Header ══════════════════════════════════════════════════ --}}
    <div class="dash-header px-3 pt-3 pb-2">
        <h5 class="fw-bold mb-0 text-dark">Super Admin Panel 🛡️</h5>
        <p class="text-secondary mb-0" style="font-size:12px;">Platform-wide overview — all schools</p>
    </div>

    {{-- ══ Stat Cards ══════════════════════════════════════════════════════ --}}
    <div class="px-3 pt-2">
        <div class="row g-3">

            {{-- Total Schools --}}
            <div class="col-6 col-md-3">
                <div class="dash-stat-card">
                    <div class="dash-stat-icon" style="background:#eef2ff;">
                        <span class="material-icons-round" style="color:#4f46e5;">account_balance</span>
                    </div>
                    <p class="dash-stat-label">Total Schools</p>
                    <h4 class="dash-stat-value">{{ number_format($totalSchools) }}</h4>
                    <span class="dash-stat-badge text-success">
                        <span class="material-icons-round" style="font-size:11px;vertical-align:middle;">arrow_upward</span>
                        Platform total
                    </span>
                </div>
            </div>

            {{-- Active Schools --}}
            <div class="col-6 col-md-3">
                <div class="dash-stat-card">
                    <div class="dash-stat-icon" style="background:#d1fae5;">
                        <span class="material-icons-round" style="color:#059669;">check_circle</span>
                    </div>
                    <p class="dash-stat-label">Active Schools</p>
                    <h4 class="dash-stat-value">{{ number_format($activeSchools) }}</h4>
                    <span class="dash-stat-badge text-secondary">
                        @if($totalSchools > 0)
                            {{ round(($activeSchools / $totalSchools) * 100) }}% active
                        @else
                            0% active
                        @endif
                    </span>
                </div>
            </div>

            {{-- Total Students --}}
            <div class="col-6 col-md-3">
                <div class="dash-stat-card">
                    <div class="dash-stat-icon" style="background:#fef3c7;">
                        <span class="material-icons-round" style="color:#d97706;">school</span>
                    </div>
                    <p class="dash-stat-label">Total Students</p>
                    <h4 class="dash-stat-value">{{ number_format($totalStudents) }}</h4>
                    <span class="dash-stat-badge text-success">
                        <span class="material-icons-round" style="font-size:11px;vertical-align:middle;">arrow_upward</span>
                        All schools
                    </span>
                </div>
            </div>

            {{-- Total Teachers --}}
            <div class="col-6 col-md-3">
                <div class="dash-stat-card">
                    <div class="dash-stat-icon" style="background:#ede9fe;">
                        <span class="material-icons-round" style="color:#7c3aed;">badge</span>
                    </div>
                    <p class="dash-stat-label">Total Teachers</p>
                    <h4 class="dash-stat-value">{{ number_format($totalTeachers) }}</h4>
                    <span class="dash-stat-badge text-success">
                        <span class="material-icons-round" style="font-size:11px;vertical-align:middle;">arrow_upward</span>
                        All schools
                    </span>
                </div>
            </div>

            {{-- Total Revenue --}}
            <div class="col-6 col-md-3">
                <div class="dash-stat-card">
                    <div class="dash-stat-icon" style="background:#fce7f3;">
                        <span class="material-icons-round" style="color:#db2777;">payments</span>
                    </div>
                    <p class="dash-stat-label">Total Revenue</p>
                    <h4 class="dash-stat-value">
                        @php
                            $rev = $totalRevenue;
                            echo $rev >= 100000
                                ? '৳' . number_format($rev / 100000, 1) . 'L'
                                : '৳' . number_format($rev);
                        @endphp
                    </h4>
                    <span class="dash-stat-badge text-success">
                        <span class="material-icons-round" style="font-size:11px;vertical-align:middle;">arrow_upward</span>
                        Lifetime
                    </span>
                </div>
            </div>

            {{-- Revenue This Month --}}
            <div class="col-6 col-md-3">
                <div class="dash-stat-card">
                    <div class="dash-stat-icon" style="background:#ecfeff;">
                        <span class="material-icons-round" style="color:#0891b2;">calendar_month</span>
                    </div>
                    <p class="dash-stat-label">Revenue This Month</p>
                    <h4 class="dash-stat-value">
                        @php
                            $rm = $revenueThisMonth;
                            echo $rm >= 100000
                                ? '৳' . number_format($rm / 100000, 1) . 'L'
                                : '৳' . number_format($rm);
                        @endphp
                    </h4>
                    <span class="dash-stat-badge text-secondary">Current month</span>
                </div>
            </div>

            {{-- Pending Invoices --}}
            <div class="col-6 col-md-3">
                <div class="dash-stat-card">
                    <div class="dash-stat-icon" style="background:#fff7ed;">
                        <span class="material-icons-round" style="color:#ea580c;">receipt_long</span>
                    </div>
                    <p class="dash-stat-label">Pending Invoices</p>
                    <h4 class="dash-stat-value">{{ $pendingInvoices }}</h4>
                    <span class="dash-stat-badge text-danger">
                        @php
                            $pa = $pendingAmount;
                            echo $pa >= 100000
                                ? '৳' . number_format($pa / 100000, 1) . 'L due'
                                : '৳' . number_format($pa) . ' due';
                        @endphp
                    </span>
                </div>
            </div>

            {{-- Inactive Schools --}}
            <div class="col-6 col-md-3">
                <div class="dash-stat-card">
                    <div class="dash-stat-icon" style="background:#fef2f2;">
                        <span class="material-icons-round" style="color:#dc2626;">warning_amber</span>
                    </div>
                    <p class="dash-stat-label">Inactive Schools</p>
                    <h4 class="dash-stat-value">{{ $inactiveSchools }}</h4>
                    <span class="dash-stat-badge text-danger">Needs attention</span>
                </div>
            </div>

        </div>
    </div>

    {{-- ══ Recent Schools ═══════════════════════════════════════════════════ --}}
    <div class="px-3 mt-4">
        <div class="dash-section-card">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="dash-section-title mb-0">
                    <span class="material-icons-round text-primary" style="font-size:18px;">account_balance</span>
                    Recent Schools
                </div>
                <a href="{{ route('superadmin.schools.index') }}" class="dash-view-all">View all</a>
            </div>

            @forelse($recentSchools as $school)
                <div class="dash-school-row">
                    <div class="dash-school-avatar">
                        {{ strtoupper(substr($school->name, 0, 1)) }}
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <p class="mb-0 fw-semibold text-truncate" style="font-size:13px;">
                            {{ $school->name }}
                        </p>
                        <small class="text-secondary" style="font-size:11px;">
                            {{ $school->email }}
                        </small>
                    </div>
                    @if($school->status)
                        <span class="dash-status-badge dash-badge-active">Active</span>
                    @else
                        <span class="dash-status-badge dash-badge-inactive">Inactive</span>
                    @endif
                </div>
            @empty
                <p class="text-center text-secondary py-2 mb-0" style="font-size:13px;">
                    কোনো school নেই এখনো
                </p>
            @endforelse
        </div>
    </div>

    {{-- ══ Recent Invoices ══════════════════════════════════════════════════ --}}
    <div class="px-3 mt-4">
        <div class="dash-section-card">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="dash-section-title mb-0">
                    <span class="material-icons-round text-warning" style="font-size:18px;">receipt_long</span>
                    Recent Invoices
                </div>
                <a href="{{ route('superadmin.invoices.index') }}" class="dash-view-all">View all</a>
            </div>

            @forelse($recentInvoices as $inv)
                <div class="dash-school-row">
                    <div class="flex-grow-1 min-w-0">
                        <p class="mb-0 fw-semibold text-truncate" style="font-size:13px;">
                            {{ $inv->school_name }}
                        </p>
                        <small class="text-secondary" style="font-size:11px;">
                            {{ $inv->invoice_no }} · Due: {{ \Carbon\Carbon::parse($inv->due_date)->format('d M Y') }}
                        </small>
                    </div>
                    <div class="text-end ms-2" style="flex-shrink:0;">
                        <p class="mb-0 fw-semibold" style="font-size:13px;">
                            ৳{{ number_format($inv->total_amount) }}
                        </p>
                        @if($inv->status === 'paid')
                            <span class="dash-status-badge dash-badge-active">Paid</span>
                        @else
                            <span class="dash-status-badge dash-badge-inactive">Unpaid</span>
                        @endif
                    </div>
                </div>
            @empty
                <p class="text-center text-secondary py-2 mb-0" style="font-size:13px;">
                    কোনো invoice নেই এখনো
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

@push('styles')
<style>
    .dash-wrap { background: #f5f6fa; min-height: 100vh; padding-bottom: 24px; }
    .dash-header { padding-top: 16px; }

    .dash-stat-card {
        background: #ffffff; border-radius: 16px; padding: 14px;
        box-shadow: 0 1px 6px rgba(0,0,0,.06); height: 100%;
    }
    .dash-stat-icon {
        width: 38px; height: 38px; border-radius: 10px;
        display: flex; align-items: center; justify-content: center; margin-bottom: 10px;
    }
    .dash-stat-icon .material-icons-round { font-size: 20px; }
    .dash-stat-label { font-size: 11px; color: #9ca3af; margin-bottom: 2px; }
    .dash-stat-value { font-size: 20px; font-weight: 700; color: #111827; margin-bottom: 4px; }
    .dash-stat-badge { font-size: 11px; font-weight: 500; }

    .dash-section-card {
        background: #ffffff; border-radius: 16px;
        padding: 16px; box-shadow: 0 1px 6px rgba(0,0,0,.06);
    }
    .dash-section-title {
        font-size: 14px; font-weight: 600; color: #111827;
        display: flex; align-items: center; gap: 6px; margin-bottom: 12px;
    }
    .dash-view-all { font-size: 12px; color: #e94d82; font-weight: 500; text-decoration: none; }

    .dash-school-row {
        display: flex; align-items: center; gap: 10px;
        padding: 10px 12px; border-radius: 10px;
        background: #f9fafb; margin-bottom: 8px;
    }
    .dash-school-row:last-child { margin-bottom: 0; }

    .dash-school-avatar {
        width: 36px; height: 36px; border-radius: 10px;
        background: linear-gradient(135deg, #e94d82, #f4a8c5);
        color: #fff; font-size: 14px; font-weight: 700;
        display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    }

    .dash-status-badge {
        font-size: 11px; padding: 3px 10px;
        border-radius: 20px; font-weight: 500; flex-shrink: 0;
    }
    .dash-badge-active   { background: #d1fae5; color: #065f46; }
    .dash-badge-inactive { background: #fef2f2; color: #991b1b; }

    .dash-activity-item {
        display: flex; align-items: center; gap: 10px;
        padding: 8px 0; border-bottom: 1px solid #f3f4f6;
    }
    .dash-activity-item:last-child { border-bottom: none; padding-bottom: 0; }
    .dash-activity-icon {
        width: 32px; height: 32px; border-radius: 8px;
        background: #f9fafb; display: flex;
        align-items: center; justify-content: center; flex-shrink: 0;
    }

    @media (min-width: 768px) {
        .dash-stat-value { font-size: 22px; }
    }
</style>
@endpush