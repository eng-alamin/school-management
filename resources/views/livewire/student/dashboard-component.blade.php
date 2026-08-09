{{-- resources/views/livewire/student/dashboard-component.blade.php --}}

<div class="dash-wrap">

    {{-- ══ Skeleton Loading (shown only while Livewire request is in-flight) ══ --}}
    <div wire:loading.delay class="px-3 pt-3">
        <div class="skeleton skeleton-title" style="width:220px;height:20px;"></div>
        <div class="skeleton skeleton-text" style="width:320px;height:12px;margin-top:8px;"></div>
        <div class="row g-3 pt-3">
            @for($i = 0; $i < 4; $i++)
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

    {{-- ══ Welcome Header (Card) ══════════════════════════════════════════ --}}
    <div class="px-3 pt-3">
        <div class="std-welcome-card">
            <div class="std-avatar">
                @if($student?->photo)
                    <img src="{{ asset('storage/' . $student->photo) }}" alt="{{ $student->name }}">
                @else
                    {{ strtoupper(substr($student->name ?? auth()->user()->name, 0, 1)) }}
                @endif
            </div>
            <div class="flex-grow-1 min-w-0">
                <h5 class="fw-bold mb-0 text-dark">Welcome, {{ $student->name ?? auth()->user()->name }} 👋</h5>
                <p class="text-secondary mb-0" style="font-size:12px;">
                    {{ optional($assign?->class)->name ?? 'No class assigned' }}
                    @if($assign?->section) ({{ $assign->section->name }}) @endif
                </p>
                <div class="mt-2">
                    @if($student?->roll_no)
                        <span class="std-meta-badge">
                            <span class="material-icons-round" style="font-size:13px">badge</span>
                            Roll: {{ $student->roll_no }}
                        </span>
                    @endif
                    @if($student?->registration_no)
                        <span class="std-meta-badge">
                            <span class="material-icons-round" style="font-size:13px">confirmation_number</span>
                            Reg: {{ $student->registration_no }}
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ══ Stat Cards — 2 columns (mobile) / 4 columns (md+) ══════════════ --}}
    <div class="px-3 pt-3">
        <div class="row g-3">

            {{-- Subjects --}}
            <div class="col-6 col-md-3">
                <div class="dash-stat-card">
                    <div class="dash-stat-icon" style="background:#eef2ff;">
                        <span class="material-icons-round" style="color:#4f46e5;">menu_book</span>
                    </div>
                    <p class="dash-stat-label">Subjects</p>
                    <h4 class="dash-stat-value">{{ $subjectCount }}</h4>
                </div>
            </div>

            {{-- Upcoming Exams --}}
            <div class="col-6 col-md-3">
                <div class="dash-stat-card">
                    <div class="dash-stat-icon" style="background:#fce7f3;">
                        <span class="material-icons-round" style="color:#db2777;">event</span>
                    </div>
                    <p class="dash-stat-label">Upcoming Exams</p>
                    <h4 class="dash-stat-value">{{ $upcomingExams->count() }}</h4>
                </div>
            </div>

            {{-- Fee Due --}}
            <div class="col-6 col-md-3">
                <div class="dash-stat-card">
                    <div class="dash-stat-icon" style="background:#fef2f2;">
                        <span class="material-icons-round" style="color:#dc2626;">payments</span>
                    </div>
                    <p class="dash-stat-label">Fee Due</p>
                    <h4 class="dash-stat-value privacy-amount">৳{{ number_format($totalDue, 2) }}</h4>
                </div>
            </div>

            {{-- Attendance --}}
            <div class="col-6 col-md-3">
                <div class="dash-stat-card">
                    <div class="dash-stat-icon" style="background:#d1fae5;">
                        <span class="material-icons-round" style="color:#059669;">fact_check</span>
                    </div>
                    <p class="dash-stat-label">Attendance</p>
                    <h4 class="dash-stat-value">—</h4>
                </div>
            </div>

        </div>
    </div>

    {{-- ══ Upcoming Exams ═══════════════════════════════════════════════════ --}}
    <div class="px-3 mt-4">
        <div class="dash-section-card">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="dash-section-title mb-0">
                    <span class="material-icons-round text-danger" style="font-size:18px;">event</span>
                    Upcoming Exams
                </div>
                @if(Route::has('student.exams'))
                    <a href="{{ route('student.exams') }}" class="dash-view-all">View all</a>
                @endif
            </div>

            @forelse($upcomingExams as $exam)
                <div class="dash-notice-row">
                    <div class="flex-grow-1 min-w-0">
                        <p class="mb-0 text-dark text-truncate" style="font-size:13px;font-weight:500;">
                            {{ $exam->examSetupDetail?->classAssignDetail?->subject?->name ?? '—' }}
                        </p>
                        <small class="text-secondary" style="font-size:11px;">
                            {{ $exam->examSetup?->name ?? '—' }}
                        </small>
                    </div>
                    <div class="text-end">
                        <p class="mb-0 text-dark" style="font-size:13px;font-weight:500;">
                            {{ $exam->exam_date?->format('d M Y') ?? '—' }}
                        </p>
                        <small class="text-secondary" style="font-size:11px;">
                            {{ \Carbon\Carbon::parse($exam->start_time)->format('h:i A') }}
                        </small>
                    </div>
                </div>
            @empty
                <p class="text-center text-secondary py-2 mb-0" style="font-size:13px;">
                    No upcoming exams found.
                </p>
            @endforelse
        </div>
    </div>

    {{-- ══ Recent Fee Invoices ═════════════════════════════════════════════ --}}
    <div class="px-3 mt-4">
        <div class="dash-section-card">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="dash-section-title mb-0">
                    <span class="material-icons-round text-warning" style="font-size:18px;">receipt_long</span>
                    Recent Fee Invoices
                </div>
                @if(Route::has('student.fees'))
                    <a href="{{ route('student.fees') }}" class="dash-view-all">View all</a>
                @endif
            </div>

            @forelse($recentInvoices as $invoice)
                <div class="dash-notice-row">
                    <div class="flex-grow-1 min-w-0">
                        <p class="mb-0 text-dark text-truncate" style="font-size:13px;font-weight:500;">
                            {{ $invoice->invoice_no ?? '—' }}
                        </p>
                        <small class="text-secondary" style="font-size:11px;">
                            {{ $invoice->invoice_date ? \Carbon\Carbon::parse($invoice->invoice_date)->format('d M Y') : '—' }}
                        </small>
                    </div>
                    <div class="text-end">
                        <p class="mb-0 text-dark privacy-amount" style="font-size:13px;font-weight:500;">
                            ৳{{ number_format($invoice->total_amount ?? 0, 2) }}
                        </p>
                        @if(($invoice->payment_status ?? 'unpaid') === 'paid')
                            <span class="inv-badge paid">Paid</span>
                        @elseif(($invoice->payment_status ?? 'unpaid') === 'partial')
                            <span class="inv-badge partial">Partial</span>
                        @else
                            <span class="inv-badge unpaid">Unpaid</span>
                        @endif
                    </div>
                </div>
            @empty
                <p class="text-center text-secondary py-2 mb-0" style="font-size:13px;">
                    No fee invoices found.
                </p>
            @endforelse
        </div>
    </div>

    {{-- ══ Quick Links ══════════════════════════════════════════════════════ --}}
    <div class="px-3 mt-4 mb-4">
        <div class="dash-section-card">
            <div class="dash-section-title">
                <span class="material-icons-round text-primary" style="font-size:18px;">flash_on</span>
                Quick Links
            </div>
            <div class="row g-2 mt-1">
                @php
                    $quickLinks = collect([
                        Route::has('student.subjects') ? ['icon'=>'menu_book','label'=>'My Subjects','color'=>'#4f46e5','bg'=>'#eef2ff','href'=>route('student.subjects')] : null,
                        Route::has('student.exams') ? ['icon'=>'event','label'=>'Exam Schedule','color'=>'#db2777','bg'=>'#fce7f3','href'=>route('student.exams')] : null,
                        Route::has('student.fees') ? ['icon'=>'payments','label'=>'Fee & Payments','color'=>'#d97706','bg'=>'#fef3c7','href'=>route('student.fees')] : null,
                        Route::has('student.profile') ? ['icon'=>'person','label'=>'My Profile','color'=>'#0891b2','bg'=>'#ecfeff','href'=>route('student.profile')] : null,
                    ])->filter();
                @endphp

                @forelse($quickLinks as $action)
                    <div class="col-4 col-md-3">
                        <a href="{{ $action['href'] }}" class="text-decoration-none">
                            <div class="dash-quick-action">
                                <div class="dash-quick-icon" style="background:{{ $action['bg'] }};">
                                    <span class="material-icons-round" style="color:{{ $action['color'] }};font-size:22px;">{{ $action['icon'] }}</span>
                                </div>
                                <span class="dash-quick-label">{{ $action['label'] }}</span>
                            </div>
                        </a>
                    </div>
                @empty
                    <p class="text-center text-secondary py-2 mb-0" style="font-size:13px;">No quick links available.</p>
                @endforelse
            </div>
        </div>
    </div>

    </div>{{-- /wire:loading.remove wrapper --}}

</div>

{{-- ══ Scoped CSS (matches admin dashboard theme tokens for dark mode support) ══ --}}
@push('styles')
<style>
    /* ── Wrapper ─────────────────────────────────────────────────── */
    .dash-wrap {
        background: var(--body-bg);
        min-height: 100vh;
        padding-bottom: 24px;
    }

    /* ── Welcome Card ────────────────────────────────────────────── */
    .std-welcome-card {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: var(--radius-card);
        box-shadow: var(--shadow);
        padding: 16px;
        display: flex;
        align-items: center;
        gap: 14px;
        flex-wrap: wrap;
    }

    .std-avatar {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        overflow: hidden;
        background: linear-gradient(135deg, var(--primary), #7ba3ff);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.15rem;
        font-weight: 700;
        flex-shrink: 0;
    }

    .std-avatar img { width: 100%; height: 100%; object-fit: cover; }

    .std-meta-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        background: var(--section-bg);
        color: var(--val);
        padding: 3px 10px;
        border-radius: 999px;
        font-size: 11px;
        margin-right: 6px;
        border: 1px solid var(--border);
    }

    /* ── Stat Cards (same as admin) ──────────────────────────────── */
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

    /* ── Section Cards (same as admin) ───────────────────────────── */
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
        color: var(--primary);
        font-weight: 500;
        text-decoration: none;
    }

    /* ── Notice / List Rows (same as admin) ──────────────────────── */
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

    /* ── Quick Actions (same as admin) ───────────────────────────── */
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
        background: var(--primary-light);
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

    /* ── Privacy Mode (same as admin) ────────────────────────────── */
    body.privacy-mode .privacy-amount {
        filter: blur(7px);
        user-select: none;
        cursor: pointer;
        transition: filter .15s;
    }

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