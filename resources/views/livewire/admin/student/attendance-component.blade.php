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