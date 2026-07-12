<div>
    <div class="card">

        {{-- Floating Header --}}
        <div class="mat-card-header header-pink-gradient">
            <h5><span class="material-icons-round" style="font-size:18px;vertical-align:middle;margin-right:6px">event_note</span>My Attendance</h5>
            <p>View your attendance record</p>
        </div>

        <div class="card-body">

            {{-- =========================================================
                FILTER TOOLBAR
            ========================================================== --}}
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
                <div class="d-flex align-items-center gap-2 fw-semibold text-body">
                    <span class="material-icons-round text-secondary">insights</span>
                    Summary
                </div>
                <div style="max-width: 220px;">
                    <input wire:model.live="filterMonth" type="month" class="form-control form-control-sm">
                </div>
            </div>

            {{-- =========================================================
                SUMMARY CARDS
            ========================================================== --}}
            <div class="row g-3 mb-4">
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm border-start border-success border-4 h-100">
                        <div class="card-body d-flex align-items-center gap-3 py-3">
                            <span class="material-icons-round text-success fs-3">check_circle</span>
                            <div>
                                <h4 class="mb-0 fw-bold">{{ $summary['present'] }}</h4>
                                <small class="text-muted">Present</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm border-start border-danger border-4 h-100">
                        <div class="card-body d-flex align-items-center gap-3 py-3">
                            <span class="material-icons-round text-danger fs-3">cancel</span>
                            <div>
                                <h4 class="mb-0 fw-bold">{{ $summary['absent'] }}</h4>
                                <small class="text-muted">Absent</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm border-start border-warning border-4 h-100">
                        <div class="card-body d-flex align-items-center gap-3 py-3">
                            <span class="material-icons-round text-warning fs-3">schedule</span>
                            <div>
                                <h4 class="mb-0 fw-bold">{{ $summary['late'] }}</h4>
                                <small class="text-muted">Late</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm border-start border-info border-4 h-100">
                        <div class="card-body d-flex align-items-center gap-3 py-3">
                            <span class="material-icons-round text-info fs-3">logout</span>
                            <div>
                                <h4 class="mb-0 fw-bold">{{ $summary['leave'] }}</h4>
                                <small class="text-muted">Leave</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- =========================================================
                ATTENDANCE TABLE
            ========================================================== --}}
            <div class="d-flex align-items-center gap-2 fw-semibold text-body mb-3">
                <span class="material-icons-round text-secondary">calendar_month</span>
                Daily Record
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:60px;">SL</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $index => $item)
                        <tr wire:key="my-att-{{ $index }}">
                            <td>{{ $index + 1 }}</td>
                            <td>{{ \Illuminate\Support\Carbon::parse($item['date'])->format('d M, Y (D)') }}</td>
                            <td>
                                @php
                                    $statusMap = [
                                        'present' => ['label' => 'Present', 'badge' => 'success'],
                                        'absent'  => ['label' => 'Absent',  'badge' => 'danger'],
                                        'late'    => ['label' => 'Late',    'badge' => 'warning'],
                                        'leave'   => ['label' => 'Leave',   'badge' => 'info'],
                                    ];
                                    $st = $statusMap[$item['status']] ?? ['label' => ucfirst($item['status']), 'badge' => 'secondary'];
                                @endphp
                                <span class="badge bg-{{ $st['badge'] }}-subtle text-{{ $st['badge'] }}-emphasis rounded-pill px-3 py-2">
                                    {{ $st['label'] }}
                                </span>
                            </td>
                            <td class="text-muted">{{ $item['remarks'] ?: '-' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-5">
                                <span class="material-icons-round d-block mb-2 fs-1 text-secondary">event_busy</span>
                                No attendance record found for this month.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>