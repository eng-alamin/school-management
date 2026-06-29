{{-- resources/views/livewire/teacher/dashboard-component.blade.php --}}

<div class="dash-wrap">

    {{-- ══ Welcome Header ══════════════════════════════════════════════════ --}}
    <div class="dash-header px-3 pt-3 pb-2">
        <h5 class="fw-bold mb-0 text-dark">Welcome back, Teacher! 👋</h5>
        <p class="text-secondary mb-0" style="font-size:12px;">Here's what's happening at your school today</p>
    </div>

    {{-- ══ Stat Cards — 2 columns (mobile) / 4 columns (md+) ══════════════ --}}
    <div class="px-3 pt-2">
        <div class="row g-3">

            {{-- Total Students --}}
            <div class="col-6 col-md-3">
                <div class="dash-stat-card">
                    <div class="dash-stat-icon" style="background:#eef2ff;">
                        <span class="material-icons-round" style="color:#4f46e5;">school</span>
                    </div>
                    <p class="dash-stat-label">Total Students</p>
                    <h4 class="dash-stat-value">{{ number_format($totalStudents) }}</h4>
                    <span class="dash-stat-badge text-success">
                        <span class="material-icons-round" style="font-size:11px;vertical-align:middle;">arrow_upward</span>+12%
                    </span>
                </div>
            </div>

            {{-- Total Teachers --}}
            <div class="col-6 col-md-3">
                <div class="dash-stat-card">
                    <div class="dash-stat-icon" style="background:#fef3c7;">
                        <span class="material-icons-round" style="color:#d97706;">badge</span>
                    </div>
                    <p class="dash-stat-label">Total Teachers</p>
                    <h4 class="dash-stat-value">{{ number_format($totalEmployees) }}</h4>
                    <span class="dash-stat-badge text-success">
                        <span class="material-icons-round" style="font-size:11px;vertical-align:middle;">arrow_upward</span>+3%
                    </span>
                </div>
            </div>

            {{-- Fee Collected --}}
            <div class="col-6 col-md-3">
                <div class="dash-stat-card">
                    <div class="dash-stat-icon" style="background:#d1fae5;">
                        <span class="material-icons-round" style="color:#059669;">payments</span>
                    </div>
                    <p class="dash-stat-label">Fee Collected</p>
                    <h4 class="dash-stat-value">
                        @php
                            $fc = $totalFeeCollected;
                            echo $fc >= 100000 ? '৳'.number_format($fc/100000, 1).'L' : '৳'.number_format($fc);
                        @endphp
                    </h4>
                    <span class="dash-stat-badge text-success">
                        <span class="material-icons-round" style="font-size:11px;vertical-align:middle;">arrow_upward</span>+8%
                    </span>
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
                    <span class="dash-stat-badge text-danger">
                        <span class="material-icons-round" style="font-size:11px;vertical-align:middle;">arrow_downward</span>-2%
                    </span>
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
                    <span class="dash-stat-badge text-success">
                        <span class="material-icons-round" style="font-size:11px;vertical-align:middle;">arrow_upward</span>+5%
                    </span>
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
                    <span class="dash-stat-badge text-danger">
                        <span class="material-icons-round" style="font-size:11px;vertical-align:middle;">arrow_downward</span>-4%
                    </span>
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

            {{-- Due Fees --}}
            <div class="col-6 col-md-3">
                <div class="dash-stat-card">
                    <div class="dash-stat-icon" style="background:#fef2f2;">
                        <span class="material-icons-round" style="color:#dc2626;">warning_amber</span>
                    </div>
                    <p class="dash-stat-label">Due Fees</p>
                    <h4 class="dash-stat-value">
                        @php
                            $due = $totalFeeDue;
                            echo $due >= 100000 ? '৳'.number_format($due/100000, 1).'L' : '৳'.number_format($due);
                        @endphp
                    </h4>
                    <span class="dash-stat-badge text-danger">
                        <span class="material-icons-round" style="font-size:11px;vertical-align:middle;">arrow_upward</span>+2%
                    </span>
                </div>
            </div>

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
                    কোনো activity নেই এখনো
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
                    ['icon'=>'person_add',  'label'=>'Add Student',  'color'=>'#4f46e5', 'bg'=>'#eef2ff', 'href'=>'#'],
                    ['icon'=>'how_to_reg',  'label'=>'Attendance',   'color'=>'#059669', 'bg'=>'#d1fae5', 'href'=>'#'],
                    ['icon'=>'receipt',     'label'=>'Fee Invoice',  'color'=>'#d97706', 'bg'=>'#fef3c7', 'href'=>'#'],
                    ['icon'=>'campaign',    'label'=>'Notice',       'color'=>'#db2777', 'bg'=>'#fce7f3', 'href'=>'#'],
                    ['icon'=>'event_note',  'label'=>'Add Exam',     'color'=>'#0891b2', 'bg'=>'#ecfeff', 'href'=>'#'],
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
                <a href="#" class="dash-view-all">View all</a>
            </div>

            @forelse($recentNotices as $notice)
                <a href="#" class="dash-notice-row text-decoration-none">
                    <div class="flex-grow-1 min-w-0">
                        <p class="mb-0 text-dark text-truncate" style="font-size:13px;font-weight:500;">
                            {{ $notice->title }}
                        </p>
                    </div>
                    <span class="material-icons-round text-secondary" style="font-size:18px;">chevron_right</span>
                </a>
            @empty
                @foreach([
                    'Annual Sports Day - May 10',
                    'Exam Schedule Published',
                    'Fee Deadline - May 15',
                    'Parent-Teacher Meeting - May 8',
                ] as $n)
                    <div class="dash-notice-row">
                        <div class="flex-grow-1 min-w-0">
                            <p class="mb-0 text-dark text-truncate" style="font-size:13px;font-weight:500;">{{ $n }}</p>
                        </div>
                        <span class="material-icons-round text-secondary" style="font-size:18px;">chevron_right</span>
                    </div>
                @endforeach
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

</div>

{{-- ══ Scoped CSS ══════════════════════════════════════════════════════════ --}}
@push('styles')
<style>
    /* ── Wrapper ─────────────────────────────────────────────────── */
    .dash-wrap {
        background: #f5f6fa;
        min-height: 100vh;
        padding-bottom: 24px;
    }

    .dash-header {
        padding-top: 16px;
    }

    /* ── Stat Cards ──────────────────────────────────────────────── */
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

    .dash-stat-badge {
        font-size: 11px;
        font-weight: 500;
    }

    /* ── Section Cards ───────────────────────────────────────────── */
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

    /* ── Activity ────────────────────────────────────────────────── */
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

    /* ── Quick Actions ───────────────────────────────────────────── */
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

    /* ── Notices ─────────────────────────────────────────────────── */
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

    /* ── Birthdays ───────────────────────────────────────────────── */
    .dash-birthday-row {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 8px 0;
        border-bottom: 1px solid #f3f4f6;
    }

    .dash-birthday-row:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .dash-birthday-avatar {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background: linear-gradient(135deg, #e94d82, #f4a8c5);
        color: #fff;
        font-size: 15px;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

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
</style>
@endpush