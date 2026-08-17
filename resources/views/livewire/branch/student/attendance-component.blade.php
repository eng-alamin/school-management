<div>
    
    <div class="card border-0 bg-transparent">

        <div class="container-xl mt-4">

            @include('livewire.admin.student.student-navbar', ['student' => $student])

            {{-- =========================================================
                TOP: RING GLASS CARD + STATS + MONTH NAVIGATOR
            ========================================================== --}}
            <div class="att-hero">

                <div class="att-ring-card">
                    <div class="att-ring att-ring-{{ $ringLevel }}" style="--pct: {{ $attendancePercentage }};">
                        <svg viewBox="0 0 120 120">
                            <circle class="att-ring-track" cx="60" cy="60" r="52"></circle>
                            <circle class="att-ring-fill" cx="60" cy="60" r="52"></circle>
                        </svg>
                        <div class="att-ring-label">
                            <span class="att-ring-num">{{ $attendancePercentage }}<small>%</small></span>
                            <span class="att-ring-sub">Attendance</span>
                        </div>
                    </div>
                </div>

                <div class="att-hero-right">
                    <div class="att-month-nav">
                        <button type="button" class="att-nav-btn" wire:click="previousMonth" title="Previous month">
                            <span class="material-icons-round">chevron_left</span>
                        </button>
                        <span class="att-month-label">
                            {{ \Illuminate\Support\Carbon::createFromFormat('Y-m', $filterMonth)->format('F Y') }}
                        </span>
                        <button type="button" class="att-nav-btn" wire:click="nextMonth" @disabled($isCurrentMonth) title="Next month">
                            <span class="material-icons-round">chevron_right</span>
                        </button>
                    </div>

                    <div class="att-chips">
                        <div class="att-chip att-chip-success">
                            <span class="material-icons-round">check_circle</span>
                            <div><strong>{{ $summary['present'] }}</strong><small>Present</small></div>
                        </div>
                        <div class="att-chip att-chip-danger">
                            <span class="material-icons-round">cancel</span>
                            <div><strong>{{ $summary['absent'] }}</strong><small>Absent</small></div>
                        </div>
                        <div class="att-chip att-chip-warning">
                            <span class="material-icons-round">schedule</span>
                            <div><strong>{{ $summary['late'] }}</strong><small>Late</small></div>
                        </div>
                        <div class="att-chip att-chip-info">
                            <span class="material-icons-round">logout</span>
                            <div><strong>{{ $summary['leave'] }}</strong><small>Leave</small></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- =========================================================
                CALENDAR
            ========================================================== --}}
            <div class="att-cal-header mx-2">
                <span class="att-cal-title">
                    Daily Breakdown
                </span>
                <div class="att-legend">
                    <span><i class="att-dot bg-success"></i>Present</span>
                    <span><i class="att-dot bg-danger"></i>Absent</span>
                    <span><i class="att-dot bg-warning"></i>Late</span>
                    <span><i class="att-dot bg-info"></i>Leave</span>
                </div>
            </div>

            @if(count($calendarWeeks) > 0)
                <div class="att-cal mb-4">
                    <div class="att-cal-row att-cal-labels">
                        @foreach(['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $wd)
                            <div class="att-cal-label">{{ $wd }}</div>
                        @endforeach
                    </div>

                    @foreach($calendarWeeks as $wIndex => $week)
                        <div class="att-cal-row" wire:key="att-week-{{ $wIndex }}">
                            @foreach($week as $dIndex => $cell)
                                @php
                                    $cls = $cell['status'] ? 'att-cell-'.$cell['status'] : '';
                                @endphp
                                <div
                                    class="att-cell
                                        {{ !$cell['inMonth'] ? 'att-cell-outside' : '' }}
                                        {{ $cell['isToday'] ? 'att-cell-today' : '' }}
                                        {{ $cell['isWeekend'] && $cell['inMonth'] && !$cell['status'] ? 'att-cell-weekend' : '' }}
                                        {{ $cls }}
                                    "
                                    wire:key="att-cell-{{ $wIndex }}-{{ $dIndex }}"
                                    @if($cell['inMonth'] && $cell['status'])
                                        title="{{ ucfirst($cell['status']) }}{{ $cell['remarks'] ? ' - '.$cell['remarks'] : '' }}"
                                    @endif
                                >
                                    @if($cell['isToday'])
                                        <span class="att-cell-pulse"></span>
                                    @endif
                                    <span class="att-cell-num">{{ $cell['date']->day }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center text-muted py-5">
                    <span class="material-icons-round d-block mb-2 fs-1 text-secondary">event_busy</span>
                    No attendance record found for this month.
                </div>
            @endif

        </div>

    </div>

</div>

@push('styles')
<style>
    /* ---------- Hero section ---------- */
    .att-hero {
        display: flex;
        align-items: center;
        gap: 1.75rem;
        flex-wrap: wrap;
        padding: 1.25rem;
        border-radius: 1rem;
        margin-bottom: 1.5rem;
        background: #fff;
    }

    .att-ring-card { flex-shrink: 0; }

    .att-ring {
        position: relative;
        width: 118px;
        height: 118px;
        filter: drop-shadow(0 4px 10px rgba(0,0,0,0.08));
    }
    .att-ring svg { width: 100%; height: 100%; transform: rotate(-90deg); }
    .att-ring-track { fill: none; stroke: var(--bs-tertiary-bg); stroke-width: 9; }
    .att-ring-fill {
        fill: none;
        stroke-width: 9;
        stroke-linecap: round;
        stroke-dasharray: 326.7;
        stroke-dashoffset: calc(326.7 - (326.7 * var(--pct, 0) / 100));
        transition: stroke-dashoffset 0.7s cubic-bezier(.4,0,.2,1), stroke 0.3s ease;
    }
    .att-ring-high .att-ring-fill { stroke: var(--bs-success); }
    .att-ring-mid .att-ring-fill  { stroke: var(--bs-warning); }
    .att-ring-low .att-ring-fill  { stroke: var(--bs-danger); }

    .att-ring-label {
        position: absolute;
        inset: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }
    .att-ring-num { font-size: 1.5rem; font-weight: 800; color: var(--bs-body-color); line-height: 1; }
    .att-ring-num small { font-size: 0.9rem; font-weight: 600; }
    .att-ring-sub { font-size: 0.65rem; color: var(--bs-secondary-color); margin-top: 4px; text-transform: uppercase; letter-spacing: 0.04em; }

    .att-hero-right { flex: 1; min-width: 260px; display: flex; flex-direction: column; gap: 0.9rem; }

    /* ---------- Month navigator ---------- */
    .att-month-nav {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    .att-nav-btn {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        border: none;
        background: var(--bs-body-bg);
        box-shadow: 0 1px 4px rgba(0,0,0,0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--bs-body-color);
        transition: background 0.15s ease, transform 0.1s ease;
    }
    .att-nav-btn:hover:not(:disabled) { background: var(--bs-primary); color: #fff; transform: scale(1.06); }
    .att-nav-btn:disabled { opacity: 0.35; cursor: not-allowed; }
    .att-nav-btn .material-icons-round { font-size: 20px; }
    .att-month-label { font-weight: 700; font-size: 1.05rem; color: var(--bs-body-color); min-width: 140px; text-align: center; }

    /* ---------- Stat chips ---------- */
    .att-chips { display: flex; gap: 0.6rem; flex-wrap: wrap; }
    .att-chip {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 0.8rem;
        border-radius: 0.7rem;
        background: var(--bs-body-bg);
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    .att-chip strong { display: block; font-size: 1rem; line-height: 1; color: var(--bs-body-color); }
    .att-chip small { color: var(--bs-secondary-color); font-size: 0.68rem; }
    .att-chip .material-icons-round { font-size: 1.3rem; }

    .att-chip-success .material-icons-round { color: var(--bs-success); }
    .att-chip-danger .material-icons-round { color: var(--bs-danger); }
    .att-chip-warning .material-icons-round { color: var(--bs-warning); }
    .att-chip-info .material-icons-round { color: var(--bs-info); }

    /* ---------- Calendar header ---------- */
    .att-cal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-bottom: 0.85rem;
    }
    .att-cal-title {
        display: flex;
        align-items: center;
        gap: 6px;
        font-weight: 700;
        font-size: 0.95rem;
        color: var(--bs-body-color);
    }
    .att-cal-title .material-icons-round { font-size: 20px; color: var(--bs-secondary-color); }
    .att-legend { display: flex; gap: 0.9rem; flex-wrap: wrap; font-size: 0.72rem; color: var(--bs-secondary-color); }
    .att-legend span { display: flex; align-items: center; gap: 5px; }
    .att-dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; }


    /* ---------- Calendar grid (no scroll) ---------- */
    .att-cal { width: 100%;background: #fff;padding: 16px;border-radius: 1rem;box-shadow: 0 1px 4px rgba(0,0,0,0.08); }
    .att-cal-row { display: grid; grid-template-columns: repeat(7, 1fr); gap: 6px; margin-bottom: 6px; }
    .att-cal-labels { margin-bottom: 8px; }
    .att-cal-label {
        text-align: center;
        font-size: 0.65rem;
        font-weight: 700;
        color: var(--bs-secondary-color);
        text-transform: uppercase;
    }

    .att-cell {
        position: relative;
        aspect-ratio: 1 / 1;
        max-height: 50px;
        border-radius: 12px;
        background: var(--bs-tertiary-bg);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.82rem;
        font-weight: 600;
        color: var(--bs-body-color);
        transition: transform 0.15s ease, box-shadow 0.15s ease;
        overflow: visible;
    }
    .att-cell:hover { transform: translateY(-3px) scale(1.04); box-shadow: 0 6px 14px rgba(0,0,0,0.1); z-index: 2; }

    .att-cell-outside { background: transparent; opacity: 0.18; }
    .att-cell-weekend { background: color-mix(in srgb, var(--bs-secondary) 8%, var(--bs-tertiary-bg)); }

    .att-cell-today { box-shadow: inset 0 0 0 2px var(--bs-primary); }
    .att-cell-pulse {
        position: absolute;
        inset: -4px;
        border-radius: 14px;
        border: 2px solid var(--bs-primary);
        opacity: 0.6;
        animation: att-pulse 1.8s ease-out infinite;
        pointer-events: none;
    }
    @keyframes att-pulse {
        0%   { transform: scale(1); opacity: 0.55; }
        100% { transform: scale(1.25); opacity: 0; }
    }

    .att-cell-present {
        background: linear-gradient(150deg, color-mix(in srgb, var(--bs-success) 80%, white), var(--bs-success));
        color: #fff;
        box-shadow: 0 3px 8px color-mix(in srgb, var(--bs-success) 35%, transparent);
    }
    .att-cell-absent {
        background: linear-gradient(150deg, color-mix(in srgb, var(--bs-danger) 80%, white), var(--bs-danger));
        color: #fff;
        box-shadow: 0 3px 8px color-mix(in srgb, var(--bs-danger) 35%, transparent);
    }
    .att-cell-late {
        background: linear-gradient(150deg, color-mix(in srgb, var(--bs-warning) 80%, white), var(--bs-warning));
        color: #3b2a00;
        box-shadow: 0 3px 8px color-mix(in srgb, var(--bs-warning) 35%, transparent);
    }
    .att-cell-leave {
        background: linear-gradient(150deg, color-mix(in srgb, var(--bs-info) 80%, white), var(--bs-info));
        color: #fff;
        box-shadow: 0 3px 8px color-mix(in srgb, var(--bs-info) 35%, transparent);
    }

    @media (max-width: 576px) {
        .att-hero { padding: 1rem; gap: 1rem; }
        .att-ring { width: 90px; height: 90px; }
        .att-ring-num { font-size: 1.15rem; }
        .att-month-label { min-width: auto; font-size: 0.95rem; }
        .att-cell { max-height: 38px; border-radius: 9px; font-size: 0.72rem; }
        .att-cal-row { gap: 4px; }
    }

    body.dark-mode .att-hero, body.dark-mode .att-cal {
        background: var(--bs-dark-card-bg);
    }
    body.dark-mode .att-cal-title, body.dark-mode .att-month-label, body.dark-mode .att-cal-label, body.dark-mode .att-ring-num, body.dark-mode .att-ring-sub {
        color: #fff;
    }
</style>
@endpush