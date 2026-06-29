{{-- resources/views/livewire/teacher/dashboard-component.blade.php --}}

<div class="dash-wrap">

    {{-- ══ Welcome Header ══════════════════════════════════════════════════ --}}
    <div class="dash-header px-3 pt-3 pb-2">
        <h5 class="fw-bold mb-0 text-dark">Welcome back, {{ $teacherName }}! 👋</h5>
        <p class="text-secondary mb-0" style="font-size:12px;">{{ now()->format('l, d F Y') }}</p>
    </div>

    {{-- ══ My Attendance Badge ═════════════════════════════════════════════ --}}
    <div class="px-3 pt-2">
        @php
            $attMap = [
                'present' => ['label' => 'You are marked Present today', 'color' => '#059669', 'bg' => '#d1fae5', 'icon' => 'check_circle'],
                'absent'  => ['label' => 'You are marked Absent today',  'color' => '#dc2626', 'bg' => '#fee2e2', 'icon' => 'cancel'],
                'leave'   => ['label' => 'You are on Leave today',        'color' => '#d97706', 'bg' => '#fef3c7', 'icon' => 'event_busy'],
            ];
            $att = $attMap[$myAttendanceToday] ?? null;
        @endphp
        @if($att)
            <div style="background:{{ $att['bg'] }};border-radius:12px;padding:10px 14px;display:flex;align-items:center;gap:8px;margin-bottom:4px;">
                <span class="material-icons-round" style="font-size:18px;color:{{ $att['color'] }}">{{ $att['icon'] }}</span>
                <span style="font-size:13px;font-weight:600;color:{{ $att['color'] }}">{{ $att['label'] }}</span>
            </div>
        @else
            <div style="background:#f3f4f6;border-radius:12px;padding:10px 14px;display:flex;align-items:center;gap:8px;margin-bottom:4px;">
                <span class="material-icons-round" style="font-size:18px;color:#9ca3af">schedule</span>
                <span style="font-size:13px;font-weight:500;color:#6b7280">Attendance not marked yet today</span>
            </div>
        @endif
    </div>

    {{-- ══ Stat Cards ══════════════════════════════════════════════════════ --}}
    <div class="px-3 pt-3">
        <div class="row g-3">

            {{-- My Classes --}}
            <div class="col-6 col-md-3">
                <div class="dash-stat-card">
                    <div class="dash-stat-icon" style="background:#eef2ff;">
                        <span class="material-icons-round" style="color:#4f46e5;">class</span>
                    </div>
                    <p class="dash-stat-label">My Classes</p>
                    <h4 class="dash-stat-value">{{ $myTotalClasses }}</h4>
                    <span class="dash-stat-badge text-secondary">Assigned</span>
                </div>
            </div>

            {{-- My Students --}}
            <div class="col-6 col-md-3">
                <div class="dash-stat-card">
                    <div class="dash-stat-icon" style="background:#d1fae5;">
                        <span class="material-icons-round" style="color:#059669;">school</span>
                    </div>
                    <p class="dash-stat-label">My Students</p>
                    <h4 class="dash-stat-value">{{ $myTotalStudents }}</h4>
                    <span class="dash-stat-badge text-secondary">Total</span>
                </div>
            </div>

            {{-- Students Present Today --}}
            <div class="col-6 col-md-3">
                <div class="dash-stat-card">
                    <div class="dash-stat-icon" style="background:#ede9fe;">
                        <span class="material-icons-round" style="color:#7c3aed;">how_to_reg</span>
                    </div>
                    <p class="dash-stat-label">Present Today</p>
                    <h4 class="dash-stat-value">{{ $myStudentsPresentToday }}</h4>
                    <span class="dash-stat-badge" style="color:#7c3aed;">{{ $myAttendancePercent }}%</span>
                </div>
            </div>

            {{-- Students Absent Today --}}
            <div class="col-6 col-md-3">
                <div class="dash-stat-card">
                    <div class="dash-stat-icon" style="background:#fef2f2;">
                        <span class="material-icons-round" style="color:#dc2626;">person_off</span>
                    </div>
                    <p class="dash-stat-label">Absent Today</p>
                    <h4 class="dash-stat-value">{{ $myStudentsAbsentToday }}</h4>
                    <span class="dash-stat-badge text-danger">Not present</span>
                </div>
            </div>

            {{-- Pending Leave --}}
            <div class="col-6 col-md-3">
                <div class="dash-stat-card">
                    <div class="dash-stat-icon" style="background:#fff7ed;">
                        <span class="material-icons-round" style="color:#ea580c;">pending_actions</span>
                    </div>
                    <p class="dash-stat-label">Leave Pending</p>
                    <h4 class="dash-stat-value">{{ $myPendingLeave }}</h4>
                    <span class="dash-stat-badge text-warning">Awaiting</span>
                </div>
            </div>

            {{-- Approved Leave --}}
            <div class="col-6 col-md-3">
                <div class="dash-stat-card">
                    <div class="dash-stat-icon" style="background:#d1fae5;">
                        <span class="material-icons-round" style="color:#059669;">event_available</span>
                    </div>
                    <p class="dash-stat-label">Leave Approved</p>
                    <h4 class="dash-stat-value">{{ $myApprovedLeave }}</h4>
                    <span class="dash-stat-badge text-success">Approved</span>
                </div>
            </div>

            {{-- Unread Messages --}}
            <div class="col-6 col-md-3">
                <div class="dash-stat-card">
                    <div class="dash-stat-icon" style="background:#fce7f3;">
                        <span class="material-icons-round" style="color:#db2777;">mark_email_unread</span>
                    </div>
                    <p class="dash-stat-label">Unread Messages</p>
                    <h4 class="dash-stat-value">{{ $unreadMessages }}</h4>
                    <span class="dash-stat-badge text-secondary">Inbox</span>
                </div>
            </div>

            {{-- Active Notices --}}
            <div class="col-6 col-md-3">
                <div class="dash-stat-card">
                    <div class="dash-stat-icon" style="background:#ecfeff;">
                        <span class="material-icons-round" style="color:#0891b2;">campaign</span>
                    </div>
                    <p class="dash-stat-label">Active Notices</p>
                    <h4 class="dash-stat-value">{{ $activeNotices }}</h4>
                    <span class="dash-stat-badge text-secondary">Published</span>
                </div>
            </div>

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
                    ['icon' => 'how_to_reg',     'label' => 'Attendance',    'color' => '#059669', 'bg' => '#d1fae5', 'href' => '#'],
                    ['icon' => 'assignment',      'label' => 'Homework',      'color' => '#ea580c', 'bg' => '#fff7ed', 'href' => '#'],
                    ['icon' => 'event_note',      'label' => 'Exam',          'color' => '#0891b2', 'bg' => '#ecfeff', 'href' => '#'],
                    ['icon' => 'pending_actions', 'label' => 'Apply Leave',   'color' => '#d97706', 'bg' => '#fef3c7', 'href' => '#'],
                    ['icon' => 'sms',             'label' => 'Messages',      'color' => '#7c3aed', 'bg' => '#ede9fe', 'href' => '#'],
                    ['icon' => 'campaign',        'label' => 'Notices',       'color' => '#db2777', 'bg' => '#fce7f3', 'href' => '#'],
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

    {{-- ══ My Recent Leave Applications ════════════════════════════════════ --}}
    <div class="px-3 mt-4">
        <div class="dash-section-card">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="dash-section-title mb-0">
                    <span class="material-icons-round text-warning" style="font-size:18px;">pending_actions</span>
                    My Leave Applications
                </div>
                <a href="#" class="dash-view-all">View all</a>
            </div>

            @php
                $leaveStatusMap = [
                    'approved'  => ['label' => 'Approved',  'color' => '#059669', 'bg' => '#d1fae5'],
                    'pending'   => ['label' => 'Pending',   'color' => '#d97706', 'bg' => '#fef3c7'],
                    'rejected'  => ['label' => 'Rejected',  'color' => '#dc2626', 'bg' => '#fee2e2'],
                    'cancelled' => ['label' => 'Cancelled', 'color' => '#6b7280', 'bg' => '#f3f4f6'],
                ];
            @endphp

            @forelse($myRecentLeaves as $leave)
                @php $ls = $leaveStatusMap[$leave->status] ?? ['label' => ucfirst($leave->status), 'color' => '#6b7280', 'bg' => '#f3f4f6']; @endphp
                <div class="dash-leave-row">
                    <div class="flex-grow-1 min-w-0">
                        <p class="mb-0 fw-600 text-truncate" style="font-size:13px;">{{ $leave->category ?? '—' }}</p>
                        <small class="text-secondary" style="font-size:11px;">
                            {{ \Carbon\Carbon::parse($leave->start_date)->format('d M') }}
                            —
                            {{ \Carbon\Carbon::parse($leave->end_date)->format('d M Y') }}
                            &nbsp;·&nbsp; {{ $leave->total_days }} day(s)
                        </small>
                    </div>
                    <span style="padding:2px 10px;border-radius:20px;font-size:.7rem;font-weight:600;background:{{ $ls['bg'] }};color:{{ $ls['color'] }};">
                        {{ $ls['label'] }}
                    </span>
                </div>
            @empty
                <p class="text-center text-secondary py-2 mb-0" style="font-size:13px;">
                    No leave applications yet.
                    <a href="#" class="text-decoration-none" style="color:#e94d82;">Apply now</a>
                </p>
            @endforelse
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
                        <small class="text-secondary" style="font-size:11px;">
                            {{ \Carbon\Carbon::parse($notice->published_at)->format('d M Y') }}
                        </small>
                    </div>
                    <span class="material-icons-round text-secondary" style="font-size:18px;">chevron_right</span>
                </a>
            @empty
                <p class="text-center text-secondary py-2 mb-0" style="font-size:13px;">No active notices</p>
            @endforelse
        </div>
    </div>

    {{-- ══ Recent Messages ══════════════════════════════════════════════════ --}}
    <div class="px-3 mt-4">
        <div class="dash-section-card">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="dash-section-title mb-0">
                    <span class="material-icons-round" style="font-size:18px;color:#7c3aed;">forum</span>
                    Recent Messages
                </div>
                <a href="#" class="dash-view-all">View all</a>
            </div>

            @forelse($recentMessages as $msg)
                <div class="dash-msg-row {{ $msg->is_read ? '' : 'dash-msg-unread' }}">
                    <div class="dash-msg-avatar">
                        {{ strtoupper(substr($msg->sender_name, 0, 1)) }}
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <p class="mb-0 text-truncate" style="font-size:13px;font-weight:{{ $msg->is_read ? '400' : '600' }};">
                            {{ $msg->subject ?? '(No subject)' }}
                        </p>
                        <small class="text-secondary" style="font-size:11px;">
                            From: {{ $msg->sender_name }}
                            &nbsp;·&nbsp; {{ \Carbon\Carbon::parse($msg->created_at)->diffForHumans() }}
                        </small>
                    </div>
                    @if(!$msg->is_read)
                        <span class="dash-unread-dot"></span>
                    @endif
                </div>
            @empty
                <p class="text-center text-secondary py-2 mb-0" style="font-size:13px;">No messages</p>
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
                        <span class="material-icons-round" style="font-size:16px;color:#6b7280;">{{ $act->icon ?? 'notifications' }}</span>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <p class="mb-0 text-dark text-truncate" style="font-size:13px;">{{ $act->description }}</p>
                    </div>
                    <small class="text-secondary text-nowrap ms-2" style="font-size:11px;">
                        {{ \Carbon\Carbon::parse($act->created_at)->diffForHumans() }}
                    </small>
                </div>
            @empty
                <p class="text-center text-secondary py-2 mb-0" style="font-size:13px;">No recent activity</p>
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
    .dash-wrap {
        background: #f5f6fa;
        min-height: 100vh;
        padding-bottom: 24px;
    }
    .dash-header { padding-top: 16px; }

    /* ── Stat Cards ──────────────────────────────────────────────── */
    .dash-stat-card {
        background: #fff;
        border-radius: 16px;
        padding: 14px;
        box-shadow: 0 1px 6px rgba(0,0,0,.06);
        height: 100%;
    }
    .dash-stat-icon {
        width: 38px; height: 38px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        margin-bottom: 10px;
    }
    .dash-stat-icon .material-icons-round { font-size: 20px; }
    .dash-stat-label  { font-size: 11px; color: #9ca3af; margin-bottom: 2px; }
    .dash-stat-value  { font-size: 20px; font-weight: 700; color: #111827; margin-bottom: 4px; }
    .dash-stat-badge  { font-size: 11px; font-weight: 500; }

    /* ── Section Cards ───────────────────────────────────────────── */
    .dash-section-card {
        background: #fff;
        border-radius: 16px;
        padding: 16px;
        box-shadow: 0 1px 6px rgba(0,0,0,.06);
    }
    .dash-section-title {
        font-size: 14px; font-weight: 600; color: #111827;
        display: flex; align-items: center; gap: 6px;
        margin-bottom: 12px;
    }
    .dash-view-all { font-size: 12px; color: #e94d82; font-weight: 500; text-decoration: none; }

    /* ── Leave Rows ──────────────────────────────────────────────── */
    .dash-leave-row {
        display: flex; align-items: center; gap: 10px;
        padding: 9px 0;
        border-bottom: 1px solid #f3f4f6;
    }
    .dash-leave-row:last-child { border-bottom: none; padding-bottom: 0; }

    /* ── Notices ─────────────────────────────────────────────────── */
    .dash-notice-row {
        display: flex; align-items: center;
        padding: 10px 12px;
        border-radius: 10px; background: #f9fafb;
        margin-bottom: 8px; gap: 8px;
    }
    .dash-notice-row:last-child { margin-bottom: 0; }

    /* ── Messages ────────────────────────────────────────────────── */
    .dash-msg-row {
        display: flex; align-items: center; gap: 10px;
        padding: 9px 0;
        border-bottom: 1px solid #f3f4f6;
    }
    .dash-msg-row:last-child { border-bottom: none; padding-bottom: 0; }
    .dash-msg-unread { background: #fafafa; border-radius: 8px; padding: 9px 8px; margin: 0 -8px; }
    .dash-msg-avatar {
        width: 34px; height: 34px; border-radius: 50%;
        background: linear-gradient(135deg, #7c3aed, #c4b5fd);
        color: #fff; font-size: 14px; font-weight: 700;
        display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    }
    .dash-unread-dot {
        width: 8px; height: 8px; border-radius: 50%;
        background: #e94d82; flex-shrink: 0;
    }

    /* ── Activity ────────────────────────────────────────────────── */
    .dash-activity-item {
        display: flex; align-items: center; gap: 10px;
        padding: 8px 0;
        border-bottom: 1px solid #f3f4f6;
    }
    .dash-activity-item:last-child { border-bottom: none; padding-bottom: 0; }
    .dash-activity-icon {
        width: 32px; height: 32px; border-radius: 8px;
        background: #f9fafb;
        display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    }

    /* ── Quick Actions ───────────────────────────────────────────── */
    .dash-quick-action {
        display: flex; flex-direction: column; align-items: center;
        gap: 6px; padding: 10px 4px;
        border-radius: 12px; background: #f9fafb;
        transition: background .15s;
    }
    .dash-quick-action:hover { background: #f3f4f6; }
    .dash-quick-icon {
        width: 48px; height: 48px; border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
    }
    .dash-quick-label {
        font-size: 11px; font-weight: 500; color: #374151;
        text-align: center; line-height: 1.3;
    }

    /* ── Birthdays ───────────────────────────────────────────────── */
    .dash-birthday-row {
        display: flex; align-items: center; gap: 12px;
        padding: 8px 0;
        border-bottom: 1px solid #f3f4f6;
    }
    .dash-birthday-row:last-child { border-bottom: none; padding-bottom: 0; }
    .dash-birthday-avatar {
        width: 38px; height: 38px; border-radius: 50%;
        background: linear-gradient(135deg, #e94d82, #f4a8c5);
        color: #fff; font-size: 15px; font-weight: 700;
        display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    }

    /* ── fw-600 ──────────────────────────────────────────────────── */
    .fw-600 { font-weight: 600; }

    /* ── Responsive ──────────────────────────────────────────────── */
    @media (min-width: 768px) {
        .dash-stat-value  { font-size: 22px; }
        .dash-quick-icon  { width: 52px; height: 52px; }
    }
</style>
@endpush