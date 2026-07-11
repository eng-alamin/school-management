<div class="mat-card" style="padding-top:28px">

    {{-- Floating Header --}}
    <div class="mat-card-header header-pink-gradient">
        <h5><span class="material-icons-round" style="font-size:18px;vertical-align:middle;margin-right:6px">event_note</span>My Attendance</h5>
        <p>View your attendance record</p>
    </div>

    {{-- Month Filter --}}
    <div class="form-section" style="padding-top:40px; padding-bottom:20px">
        <div class="section-heading">
            <span class="material-icons-round">tune</span> Select Month
        </div>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="input-group input-group-outline" wire:ignore.self>
                    {{-- <label class="form-label">Month</label> --}}
                    <input wire:model.live="filterMonth" type="month" class="form-control">
                </div>
            </div>
        </div>
    </div>

    {{-- Summary --}}
    <div class="form-section">
        <div class="section-heading">
            <span class="material-icons-round">insights</span> Summary
        </div>
        <div class="row g-3 mt-1">
            <div class="col-6 col-md-3">
                <div class="summary-box text-success">
                    <span class="material-icons-round">check_circle</span>
                    <div>
                        <strong>{{ $summary['present'] }}</strong>
                        <p>Present</p>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="summary-box text-danger">
                    <span class="material-icons-round">cancel</span>
                    <div>
                        <strong>{{ $summary['absent'] }}</strong>
                        <p>Absent</p>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="summary-box text-warning">
                    <span class="material-icons-round">schedule</span>
                    <div>
                        <strong>{{ $summary['late'] }}</strong>
                        <p>Late</p>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="summary-box text-info">
                    <span class="material-icons-round">logout</span>
                    <div>
                        <strong>{{ $summary['leave'] }}</strong>
                        <p>Leave</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Attendance Table --}}
    <div class="form-section">
        <div class="section-heading">
            <span class="material-icons-round">calendar_month</span> Daily Record
        </div>

        <div class="table-responsive mt-3">
            <table class="schedule-table">
                <thead>
                    <tr>
                        <th id="th-sl">SL</th>
                        <th id="th-date">Date</th>
                        <th id="th-status">Status</th>
                        <th id="th-remark">Remarks</th>
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
                                    'present' => ['label' => 'Present', 'class' => 'text-success'],
                                    'absent'  => ['label' => 'Absent',  'class' => 'text-danger'],
                                    'late'    => ['label' => 'Late',    'class' => 'text-warning'],
                                    'leave'   => ['label' => 'Leave',   'class' => 'text-info'],
                                ];
                                $st = $statusMap[$item['status']] ?? ['label' => ucfirst($item['status']), 'class' => ''];
                            @endphp
                            <span class="{{ $st['class'] }}">{{ $st['label'] }}</span>
                        </td>
                        <td>{{ $item['remarks'] ?: '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">No attendance record found for this month.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

@push('styles')
<style>
    .schedule-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }
    .schedule-table thead th {
        padding: 10px 10px;
        text-align: left;
        font-weight: 600;
        font-size: 12px;
        color: #aaa;
        white-space: nowrap;
    }
    .schedule-table tbody td {
        padding: 7px 8px;
        vertical-align: middle;
    }
    .summary-box {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 14px;
        border-radius: 8px;
        background: rgba(255,255,255,0.03);
        border: 1px solid #3d3d3d;
    }
    .summary-box strong {
        font-size: 18px;
        display: block;
    }
    .summary-box p {
        margin: 0;
        font-size: 12px;
        color: #aaa;
    }
</style>
@endpush