{{-- resources/views/livewire/admin/dashboard-component.blade.php --}}

<div class="dash-wrap">

    {{-- ══ Skeleton Loading (shown only while Livewire request is in-flight) ══ --}}
    <div wire:loading.delay class="px-3 pt-3">
        <div class="skeleton skeleton-title" style="width:220px;height:20px;"></div>
        <div class="skeleton skeleton-text" style="width:320px;height:12px;margin-top:8px;"></div>
        <div class="row g-3 pt-3">
            @for($i = 0; $i < 8; $i++)
                <div class="col-6 col-md-3">
                    <div class="dash-stat-card">
                        <div class="skeleton skeleton-icon"></div>
                        <div class="skeleton skeleton-text" style="width:70%;margin-top:10px;"></div>
                        <div class="skeleton skeleton-title" style="width:50%;height:22px;"></div>
                    </div>
                </div>
            @endfor
        </div>
    </div>

    {{-- ══ Real Content (hidden while a Livewire request is in-flight) ══ --}}
    <div wire:loading.remove.delay>

    {{-- ══ Welcome Header ══════════════════════════════════════════════════ --}}
    <div class="dash-header px-3 pt-3 pb-2">
        <h5 class="fw-bold mb-0 text-dark">Welcome, Admin! 👋</h5>
        <p class="text-secondary mb-0" style="font-size:12px;">Here's what's happening at your <span style="color: var(--primary); font-weight: bold;">{{institution()->name}}</span> today</p>
    </div>

    {{-- ══ Stat Cards — 2 columns (mobile) / 4 columns (md+) ══════════════ --}}
    <div class="px-3 pt-2">
        <div class="row g-3">

            {{-- Fee Collected --}}
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
                    @include('livewire.admin.dashboard-trend-badge', ['trend' => $trendFeeCollected])
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
                    @include('livewire.admin.dashboard-trend-badge', ['trend' => $trendFeeToday])
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
                    @include('livewire.admin.dashboard-trend-badge', ['trend' => $trendFeeDue])
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

            {{-- Total Students --}}
            <div class="col-6 col-md-3">
                <div class="dash-stat-card">
                    <div class="dash-stat-icon" style="background:#eef2ff;">
                        <span class="material-icons-round" style="color:#4f46e5;">school</span>
                    </div>
                    <p class="dash-stat-label">Total Students</p>
                    <h4 class="dash-stat-value">{{ number_format($totalStudents) }}</h4>
                    @include('livewire.admin.dashboard-trend-badge', ['trend' => $trendStudents])
                </div>
            </div>

            {{-- Total Employees --}}
            <div class="col-6 col-md-3">
                <div class="dash-stat-card">
                    <div class="dash-stat-icon" style="background:#eef2ff;">
                        <span class="material-icons-round" style="color:#4f46e5;">people</span>
                    </div>
                    <p class="dash-stat-label">Total Staffs</p>
                    <h4 class="dash-stat-value">{{ number_format($totalEmployees) }}</h4>
                    @include('livewire.admin.dashboard-trend-badge', ['trend' => $trendStaffs])
                </div>
            </div>

            {{-- Total Teachers --}}
            <div class="col-6 col-md-3">
                <div class="dash-stat-card">
                    <div class="dash-stat-icon" style="background:#fef3c7;">
                        <span class="material-icons-round" style="color:#d97706;">badge</span>
                    </div>
                    <p class="dash-stat-label">Total Teachers</p>
                    <h4 class="dash-stat-value">{{ number_format($totalTeachers) }}</h4>
                    @include('livewire.admin.dashboard-trend-badge', ['trend' => $trendTeachers])
                </div>
            </div>

            {{-- Attendance --}}
            <div class="col-6 col-md-3">
                <div class="dash-stat-card">
                    <div class="dash-stat-icon" style="background:#ede9fe;">
                        <span class="material-icons-round" style="color:#7c3aed;">how_to_reg</span>
                    </div>
                    <p class="dash-stat-label">Attendance</p>
                    <h4 class="dash-stat-value">{{ $attendancePercent }}%</h4>
                    @include('livewire.admin.dashboard-trend-badge', ['trend' => $trendAttendance, 'suffix' => ' pts'])
                </div>
            </div>

            {{-- New Admissions --}}
            <div class="col-6 col-md-3">
                <div class="dash-stat-card">
                    <div class="dash-stat-icon" style="background:#fce7f3;">
                        <span class="material-icons-round" style="color:#db2777;">person_add</span>
                    </div>
                    <p class="dash-stat-label">New Admissions</p>
                    <h4 class="dash-stat-value">{{ $newAdmissionsThisMonth }}</h4>
                    @include('livewire.admin.dashboard-trend-badge', ['trend' => $trendAdmissions])
                </div>
            </div>

            {{-- Pending Homework --}}
            <div class="col-6 col-md-3">
                <div class="dash-stat-card">
                    <div class="dash-stat-icon" style="background:#fff7ed;">
                        <span class="material-icons-round" style="color:#ea580c;">assignment</span>
                    </div>
                    <p class="dash-stat-label">Pending Homework</p>
                    <h4 class="dash-stat-value">{{ $pendingHomework }}</h4>
                    @include('livewire.admin.dashboard-trend-badge', ['trend' => $trendHomework])
                </div>
            </div>

            {{-- Upcoming Exams --}}
            <div class="col-6 col-md-3">
                <div class="dash-stat-card">
                    <div class="dash-stat-icon" style="background:#ecfeff;">
                        <span class="material-icons-round" style="color:#0891b2;">event_note</span>
                    </div>
                    <p class="dash-stat-label">Upcoming Exams</p>
                    <h4 class="dash-stat-value">{{ $upcomingExams }}</h4>
                    <span class="dash-stat-badge text-secondary">Scheduled</span>
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
                <a href="#" class="dash-view-all">View all</a>
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
                    No Payment Collected Today
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
                <a href="#" class="dash-view-all">View all</a>
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
                   No Invoice
                </p>
            @endforelse
        </div>
    </div>

    {{-- ══ Recent Activity ══════════════════════════════════════════════════ --}}
    <div class="px-3 mt-4">
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
                    No activity yet.
                </p>
            @endforelse

        </div>
    </div>

    {{-- ══ Quick Actions ════════════════════════════════════════════════════ --}}
    <div class="px-3 mt-4">
        <div class="dash-section-card">
            <div class="dash-section-title">
                <span class="material-icons-round text-primary" style="font-size:18px;">flash_on</span>
                Quick Actions
            </div>
            <div class="row g-2 mt-1">
                @foreach([
                    ['icon'=>'person_add',  'label'=>'Add Student',  'color'=>'#4f46e5', 'bg'=>'#eef2ff', 'href'=>route('admin.student.add')],
                    ['icon'=>'how_to_reg',  'label'=>'Attendance',   'color'=>'#059669', 'bg'=>'#d1fae5', 'href'=>route('admin.attendance.students')],
                    ['icon'=>'receipt',     'label'=>'Fee Invoice',  'color'=>'#d97706', 'bg'=>'#fef3c7', 'href'=>route('admin.student-accounting.fee.invoices')],
                    ['icon'=>'campaign',    'label'=>'Notice',       'color'=>'#db2777', 'bg'=>'#fce7f3', 'href'=>route('admin.notices')],
                    ['icon'=>'event_note',  'label'=>'Add Exam',     'color'=>'#0891b2', 'bg'=>'#ecfeff', 'href'=>route('admin.exam.setups')],
                    ['icon'=>'sms',         'label'=>'Send SMS',     'color'=>'#7c3aed', 'bg'=>'#ede9fe', 'href'=>'#'],
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

    {{-- ══ Recent Notices ═══════════════════════════════════════════════════ --}}
    <div class="px-3 mt-4">
        <div class="dash-section-card">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="dash-section-title mb-0">
                    <span class="material-icons-round text-danger" style="font-size:18px;">campaign</span>
                    Recent Notices
                </div>
                <a href="{{route('admin.notices')}}" class="dash-view-all">View all</a>
            </div>

            @forelse($recentNotices as $notice)
                <button type="button"
                        wire:click="openViewNotice({{ $notice->id }})"
                        class="dash-notice-row dash-notice-row-btn w-100 border-0 bg-transparent text-start">
                    <div class="flex-grow-1 min-w-0">
                        <p class="mb-0 text-dark text-truncate" style="font-size:13px;font-weight:500;">
                            {{ $notice->title }}
                        </p>
                    </div>
                    <span class="material-icons-round text-secondary" style="font-size:18px;">chevron_right</span>
                </button>
            @empty
                <p class="text-secondary text-center py-2 mb-0" style="font-size:13px;">No recent notices</p>
            @endforelse
        </div>
    </div>

    {{-- ══ Today's Birthdays ════════════════════════════════════════════════ --}}
    <div class="px-3 mt-4 mb-4">
        <div class="dash-section-card">
            <div class="dash-section-title">
                <span class="material-icons-round" style="font-size:18px;color:#f59e0b;">cake</span>
                Today's Birthdays 🎂
            </div>

            @forelse($todayBirthdays as $person)
                <div class="dash-birthday-row">
                    <div class="dash-birthday-avatar">
                        {{ strtoupper(substr($person->name, 0, 1)) }}
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <p class="mb-0 fw-semibold text-truncate" style="font-size:13px;">{{ $person->name }}</p>
                        <small class="text-secondary" style="font-size:11px;">{{ $person->role }}</small>
                    </div>
                    <span class="material-icons-round" style="font-size:20px;color:#f59e0b;">cake</span>
                </div>
            @empty
                <p class="text-secondary text-center py-2 mb-0" style="font-size:13px;">No birthdays today 🎈</p>
            @endforelse
        </div>
    </div>

    </div>{{-- /wire:loading.remove wrapper --}}

    {{-- ═══════════════════════════════════════════════════════════════════
         VIEW NOTICE MODAL (NoticeComponent view-modal pattern reused here)
    ═══════════════════════════════════════════════════════════════════ --}}
    @if($showViewNoticeModal && $viewRecord)
        <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Notice Details</h5>
                        <button class="btn-close" wire:click="closeViewNoticeModal"></button>
                    </div>
                    <div class="modal-body">

                        {{-- Priority banner --}}
                        @php
                            $priorityColors = [
                                'urgent' => '#dc2626',
                                'high'   => '#d97706',
                                'medium' => '#2563eb',
                                'low'    => '#6b7280',
                            ];
                            $bannerColor = $priorityColors[$viewRecord->priority] ?? '#2563eb';
                        @endphp
                        <div style="border-left:4px solid {{ $bannerColor }};padding:12px 16px;background:#f8f9fa;border-radius:0 8px 8px 0;margin-bottom:16px;">
                            <div style="font-size:.7rem;font-weight:600;color:{{ $bannerColor }};text-transform:uppercase;letter-spacing:.05em;">
                                {{ ucfirst($viewRecord->priority) }} Priority
                            </div>
                            <div style="font-weight:700;font-size:.95rem;margin-top:4px;">{{ $viewRecord->title }}</div>
                        </div>

                        {{-- Description --}}
                        <div style="font-size:.875rem;line-height:1.6;color:#374151;margin-bottom:16px;">
                            {!! nl2br(e($viewRecord->description)) !!}
                        </div>

                        {{-- Attachment --}}
                        @if($viewRecord->attachment)
                            <div class="d-flex align-items-center gap-2 p-2 mb-3" style="background:#f0f7ff;border-radius:8px;border:1px solid #bfdbfe;">
                                <span class="material-icons-round text-primary" style="font-size:1.1rem;">attach_file</span>
                                <span style="font-size:.8rem;flex:1;">{{ $viewRecord->attachment_name }}</span>
                                <a href="{{ Storage::url($viewRecord->attachment) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                    <span class="material-icons-round" style="font-size:.85rem;vertical-align:middle;">download</span> Download
                                </a>
                            </div>
                        @endif

                        <table class="table table-sm">
                            <tr>
                                <th class="text-muted" style="width:40%">Audience</th>
                                <td>{{ ucfirst($viewRecord->audience === 'all' ? 'Everyone' : $viewRecord->audience) }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Publish Date</th>
                                <td>{{ $viewRecord->published_at->format('d M Y') }}</td>
                            </tr>
                            @if($viewRecord->expires_at)
                            <tr>
                                <th class="text-muted">Expiry Date</th>
                                <td class="{{ $viewRecord->is_expired ? 'text-danger' : '' }}">
                                    {{ $viewRecord->expires_at->format('d M Y') }}
                                    @if($viewRecord->is_expired)
                                        <span class="badge bg-danger-subtle text-danger ms-1" style="font-size:.65rem;">Expired</span>
                                    @endif
                                </td>
                            </tr>
                            @endif
                            <tr>
                                <th class="text-muted">Status</th>
                                <td>
                                    <span class="badge rounded-pill {{ $viewRecord->status === 'active' ? 'badge-active' : 'badge-inactive' }}">
                                        {{ $viewRecord->status === 'active' ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th class="text-muted">Created By</th>
                                <td>{{ $viewRecord->creator->name ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Created</th>
                                <td>{{ $viewRecord->created_at->format('d M Y') }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-light" wire:click="closeViewNoticeModal">Close</button>
                        <a href="{{ route('admin.notices') }}" class="btn btn-light">
                            <i class="bi bi-megaphone me-1"></i>Go to Notice Board
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>

{{-- ══ Scoped CSS ══════════════════════════════════════════════════════════ --}}
@push('styles')
<style>
    /* ── Wrapper ─────────────────────────────────────────────────── */
    .dash-wrap {
        background: var(--body-bg);
        min-height: 100vh;
        padding-bottom: 24px;
    }

    .dash-header {
        padding-top: 16px;
    }

    /* ── Stat Cards ──────────────────────────────────────────────── */
    .dash-stat-card {
        background: var(--card);
        border-radius: var(--radius-card);
        padding: 14px;
        box-shadow: var(--shadow);
        height: 100%;
        border: 1px solid var(--border);
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
        color: var(--lbl);
        margin-bottom: 2px;
    }

    .dash-stat-value {
        font-size: 20px;
        font-weight: 700;
        color: var(--val);
        margin-bottom: 4px;
    }

    .dash-stat-badge {
        font-size: 11px;
        font-weight: 500;
    }

    /* ── Section Cards ───────────────────────────────────────────── */
    .dash-section-card {
        background: var(--card);
        border-radius: var(--radius-card);
        padding: 16px;
        box-shadow: var(--shadow);
        border: 1px solid var(--border);
    }

    .dash-section-title {
        font-size: 14px;
        font-weight: 600;
        color: var(--val);
        display: flex;
        align-items: center;
        gap: 6px;
        margin-bottom: 12px;
    }

    .dash-view-all {
        font-size: 12px;
        color: var(--pink);
        font-weight: 500;
        text-decoration: none;
    }

    /* ── Activity ────────────────────────────────────────────────── */
    .dash-activity-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px 0;
        border-bottom: 1px solid var(--border);
    }

    .dash-activity-item:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .dash-activity-icon {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        background: var(--section-bg);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    /* ── Quick Actions ───────────────────────────────────────────── */
    .dash-quick-action {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 6px;
        padding: 10px 4px;
        border-radius: 12px;
        background: var(--section-bg);
        transition: background .15s, transform .15s;
    }

    .dash-quick-action:hover {
        background: var(--pink-light);
        transform: translateY(-2px);
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
        color: var(--val);
        text-align: center;
        line-height: 1.3;
    }

    /* ── Notices ─────────────────────────────────────────────────── */
    .dash-notice-row {
        display: flex;
        align-items: center;
        padding: 11px 12px;
        border-radius: 10px;
        background: var(--section-bg);
        margin-bottom: 8px;
        gap: 8px;
    }

    .dash-notice-row:last-child {
        margin-bottom: 0;
    }

    .dash-notice-row-btn {
        cursor: pointer;
        transition: background .15s;
    }

    .dash-notice-row-btn:hover {
        background: var(--pink-light, #fce7f3);
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

    /* ── Birthdays ───────────────────────────────────────────────── */
    .dash-birthday-row {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 8px 0;
        border-bottom: 1px solid var(--border);
    }

    .dash-birthday-row:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .dash-birthday-avatar {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--pink), #7ba3ff);
        color: #fff;
        font-size: 15px;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    /* ── Notice View Modal badges ────────────────────────────────── */
    .badge-active   { background: rgba(34,197,94,.12);  color: #16a34a; }
    .badge-inactive { background: rgba(107,114,128,.12); color: #6b7280; }

    /* ── Responsive tweaks ───────────────────────────────────────── */
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

    /* click cheye reveal — revealPrivacyAmount() JS function diye .revealed class add hoy */
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