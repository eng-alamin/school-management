{{-- resources/views/livewire/admin/attendance-report-component.blade.php --}}
<div>

    <div class="card">

        <!-- floating header -->
        <div class="mat-card-header header-primary-gradient">
            <h5 id="cardHeaderTitleAllsections">Attendance Report</h5>
            <p id="cardHeaderSubtitle">Student ও Employee attendance (biometric + manual) একসাথে দেখুন।</p>
        </div>

        <div class="card-body pt-3">

            {{-- Type toggle --}}
            <ul class="nav nav-pills mb-3 att-type-pills">
                <li class="nav-item">
                    <button class="nav-link {{ $type === 'student' ? 'active' : '' }}" wire:click="$set('type', 'student')">
                        <span class="material-icons-round" style="font-size:1rem;vertical-align:middle;">school</span>
                        Student
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link {{ $type === 'employee' ? 'active' : '' }}" wire:click="$set('type', 'employee')">
                        <span class="material-icons-round" style="font-size:1rem;vertical-align:middle;">badge</span>
                        Employee
                    </button>
                </li>
            </ul>

            {{-- Summary cards --}}
            <div class="row g-2 mb-3">
                <div class="col-6 col-md-3">
                    <div class="summary-card summary-present">
                        <span class="material-icons-round">check_circle</span>
                        <div class="count">{{ $summary['present'] ?? 0 }}</div>
                        <div class="label">Present</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="summary-card summary-absent">
                        <span class="material-icons-round">cancel</span>
                        <div class="count">{{ $summary['absent'] ?? 0 }}</div>
                        <div class="label">Absent</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="summary-card summary-late">
                        <span class="material-icons-round">schedule</span>
                        <div class="count">{{ $summary['late'] ?? 0 }}</div>
                        <div class="label">Late</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="summary-card summary-leave">
                        <span class="material-icons-round">event_busy</span>
                        <div class="count">{{ $summary['leave'] ?? 0 }}</div>
                        <div class="label">Leave</div>
                    </div>
                </div>
            </div>

            {{-- Toolbar / Filters --}}
            <div class="card-toolbar flex-wrap mb-2">
                <div class="col-md-2">
                    <label class="form-label">From</label>
                    <input type="date" class="form-control form-control-sm" wire:model.live="dateFrom">
                </div>
                <div class="col-md-2">
                    <label class="form-label">To</label>
                    <input type="date" class="form-control form-control-sm" wire:model.live="dateTo">
                </div>

                @if ($type === 'student')
                    <div class="col-md-2">
                        <label class="form-label">Class</label>
                        <select class="form-select form-select-sm" wire:model.live="classId">
                            <option value="all">All Classes</option>
                            @foreach ($classes as $class)
                                <option value="{{ $class->id }}">{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Section</label>
                        <select class="form-select form-select-sm" wire:model.live="sectionId" @if($classId === 'all') disabled @endif>
                            <option value="all">All Sections</option>
                            @foreach ($sections as $section)
                                <option value="{{ $section->id }}">{{ $section->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select class="form-select form-select-sm" wire:model.live="status">
                        <option value="all">All Status</option>
                        <option value="present">Present</option>
                        <option value="absent">Absent</option>
                        <option value="late">Late</option>
                        <option value="leave">Leave</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Search</label>
                    <div style="position:relative;display:inline-flex;align-items:center;width:100%">
                        <span class="material-icons-round" style="position:absolute;left:10px;font-size:16px;color:var(--muted);pointer-events:none">search</span>
                        <input type="text" wire:model.live.debounce.400ms="search" placeholder="নাম / ID..." class="form-control form-control-sm" style="padding-left:32px;">
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th id="th-sl">SL</th>
                            <th id="th-date" role="button" wire:click="sortBy('date')">
                                Date
                                @if($sortField === 'date')
                                    <span class="material-icons-round" style="font-size:.9rem;vertical-align:middle;">{{ $sortDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>
                                @endif
                            </th>
                            <th id="th-name">Name</th>
                            <th id="th-idno">ID No.</th>
                            @if ($type === 'student')
                                <th id="th-classsec">Class / Section</th>
                            @else
                                <th id="th-designation">Designation</th>
                            @endif
                            <th id="th-checkin" role="button" wire:click="sortBy('check_in')">Check In</th>
                            <th id="th-checkout" role="button" wire:click="sortBy('check_out')">Check Out</th>
                            <th id="th-status" role="button" wire:click="sortBy('status')">Status</th>
                            <th id="th-remarks">Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($records as $i => $record)
                        <tr wire:key="att-{{ $record->id }}">
                            <td class="text-muted">{{ $records->firstItem() + $i }}</td>

                            <td class="text-muted" style="font-size:.78rem;">
                                {{ \Carbon\Carbon::parse($record->date)->format('d M Y') }}
                            </td>

                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar-placeholder att-avatar-{{ $record->status }}">
                                        <span class="material-icons-round" style="font-size:1rem;">{{ $type === 'student' ? 'school' : 'badge' }}</span>
                                    </div>
                                    <div class="fw-500 text-dark">{{ $record->attendable?->name ?? '— (deleted)' }}</div>
                                </div>
                            </td>

                            <td class="text-muted" style="font-size:.78rem;">
                                {{ $type === 'student' ? $record->attendable?->student_id : $record->attendable?->employee_id }}
                            </td>

                            @if ($type === 'student')
                                <td class="text-muted" style="font-size:.78rem;">
                                    {{ $record->attendable?->academicClass?->name }}
                                    {{ $record->attendable?->academicSection?->name }}
                                </td>
                            @else
                                <td class="text-muted" style="font-size:.78rem;">{{ $record->attendable?->designation?->name ?? '—' }}</td>
                            @endif

                            <td class="text-muted" style="font-size:.78rem;">
                                {{ $record->check_in ? \Carbon\Carbon::parse($record->check_in)->format('h:i A') : '—' }}
                            </td>
                            <td class="text-muted" style="font-size:.78rem;">
                                {{ $record->check_out ? \Carbon\Carbon::parse($record->check_out)->format('h:i A') : '—' }}
                            </td>

                            <td>
                                @php
                                    $statusMap = [
                                        'present' => 'bg-success-subtle text-success',
                                        'absent'  => 'bg-danger-subtle text-danger',
                                        'late'    => 'bg-warning-subtle text-warning',
                                        'leave'   => 'bg-secondary-subtle text-secondary',
                                    ];
                                    $sc = $statusMap[$record->status] ?? 'bg-light text-dark';
                                @endphp
                                <span class="badge rounded-pill {{ $sc }}" style="font-size:.72rem;">{{ ucfirst($record->status) }}</span>
                            </td>

                            <td class="text-muted" style="font-size:.75rem;">
                                @if(str_contains((string) $record->remarks, 'Biometric'))
                                    <span class="material-icons-round" style="font-size:.85rem;vertical-align:middle;" title="Biometric">fingerprint</span>
                                @endif
                                {{ $record->remarks }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <span class="material-icons-round d-block mb-2" style="font-size:2.5rem;opacity:.25;">fact_check</span>
                                কোনো attendance record পাওয়া যায়নি।
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer border-0 bg-white d-flex align-items-center justify-content-between flex-wrap gap-2 py-2 px-3">
            <small class="text-muted">Showing {{ $records->firstItem() ?? 0 }}–{{ $records->lastItem() ?? 0 }} of {{ $records->total() }}</small>
            {{ $records->links('vendor.pagination.custom') }}
        </div>

    </div>

</div>


@push('styles')
    <style>
        /* ── TYPE TOGGLE PILLS ── */
        .att-type-pills .nav-link {
            border-radius: 8px;
            font-size: .82rem;
            font-weight: 600;
            color: var(--text-muted);
            padding: 6px 16px;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .att-type-pills .nav-link.active {
            background: var(--primary);
            color: #fff;
        }

        /* ── SUMMARY CARDS ── */
        .summary-card {
            border-radius: 10px;
            padding: 14px 16px;
            color: #fff;
            position: relative;
            overflow: hidden;
        }
        .summary-card .material-icons-round {
            position: absolute;
            right: 10px;
            top: 10px;
            font-size: 1.4rem;
            opacity: .35;
        }
        .summary-card .count { font-size: 1.6rem; font-weight: 700; line-height: 1; }
        .summary-card .label { font-size: .78rem; opacity: .9; margin-top: 2px; }
        .summary-present { background: linear-gradient(135deg, #16a34a, #22c55e); }
        .summary-absent  { background: linear-gradient(135deg, #dc2626, #ef4444); }
        .summary-late    { background: linear-gradient(135deg, #d97706, #f59e0b); }
        .summary-leave   { background: linear-gradient(135deg, #64748b, #94a3b8); }

        /* ── AVATAR ── */
        .avatar-placeholder {
            width: 34px; height: 34px; border-radius: 8px;
            background: var(--primary-light); color: var(--primary);
            display: inline-flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: .875rem;
        }
        .att-avatar-present { background: rgba(34,197,94,.12);  color: #16a34a; }
        .att-avatar-absent  { background: rgba(239,68,68,.12);  color: #dc2626; }
        .att-avatar-late    { background: rgba(245,158,11,.12); color: #d97706; }
        .att-avatar-leave   { background: rgba(107,114,128,.12);color: #6b7280; }

        /* ── FORM ── */
        .form-label { font-size: .75rem; font-weight: 600; color: var(--text-muted); margin-bottom: 4px; display: block; }
        .form-control, .form-select {
            border-radius: 8px; border: 1px solid var(--border);
            font-size: .8rem;
            transition: border-color .2s, box-shadow .2s;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-light);
        }
    </style>
@endpush