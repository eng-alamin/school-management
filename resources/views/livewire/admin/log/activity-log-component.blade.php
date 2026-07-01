{{-- resources/views/livewire/admin/activity-log-component.blade.php --}}
<div>

    <div class="card">

        {{-- floating header --}}
        <div class="mat-card-header header-pink-gradient">
            <h5 id="cardHeaderTitleAllsections">Activity Log</h5>
            <p id="cardHeaderSubtitle">Track all actions performed across the system.</p>
        </div>

        <div class="card-header border-0">
            {{-- toolbar --}}
            <div class="card-toolbar">

                {{-- Left side: Search --}}
                <div class="card-toolbar-title">
                    <div style="position:relative;display:inline-flex;align-items:center">
                        <span class="material-icons-round" style="position:absolute;left:10px;font-size:17px;color:var(--muted);pointer-events:none">search</span>
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="search"
                            placeholder="Search activity..."
                            style="border:1px solid rgba(0,0,0,.1);border-radius:8px;padding:7px 12px 7px 32px;font-size:.78rem;font-family:inherit;color:var(--dark);outline:none;background:#f8f9fa;width:220px"
                        />
                    </div>
                </div>

                {{-- Right side: Filter --}}
                <div class="col-md-2">
                    <select class="form-select form-select-sm" wire:model.live="filterType">
                        <option value="">All Types</option>
                        <option value="student">Student</option>
                        <option value="fee">Fee</option>
                        <option value="exam">Exam</option>
                        <option value="notice">Notice</option>
                        <option value="event">Event</option>
                        <option value="attendance">Attendance</option>
                        <option value="general">General</option>
                    </select>
                </div>

                @if($logs->total() > 10)
                    <div class="col-md-2">
                        <select class="form-select form-select-sm" wire:model.live="perPage">
                            <option value="10">10 / page</option>
                            <option value="25">25 / page</option>
                            <option value="50">50 / page</option>
                        </select>
                    </div>
                @endif

            </div>
        </div>

        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th id="th-sl">SL</th>
                            <th id="th-activity">Activity</th>
                            <th id="th-type">Type</th>
                            <th id="th-done">Done By</th>
                            <th id="th-model">Model</th>
                            <th id="th-time">Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $i => $log)
                            @php
                                $props = is_string($log->properties)
                                    ? json_decode($log->properties, true)
                                    : $log->properties->toArray();

                                $icon = $props['icon'] ?? 'notifications';
                                $type = $props['type'] ?? 'general';

                                $typeColorMap = [
                                    'student'    => ['bg' => 'rgba(79,70,229,.12)',  'color' => '#4f46e5'],
                                    'fee'        => ['bg' => 'rgba(5,150,105,.12)',  'color' => '#059669'],
                                    'exam'       => ['bg' => 'rgba(8,145,178,.12)',  'color' => '#0891b2'],
                                    'notice'     => ['bg' => 'rgba(219,39,119,.12)', 'color' => '#db2777'],
                                    'event'      => ['bg' => 'rgba(217,119,6,.12)',  'color' => '#d97706'],
                                    'attendance' => ['bg' => 'rgba(124,58,237,.12)', 'color' => '#7c3aed'],
                                    'general'    => ['bg' => 'rgba(107,114,128,.12)','color' => '#6b7280'],
                                ];

                                $tc = $typeColorMap[$type] ?? $typeColorMap['general'];
                            @endphp
                            <tr>
                                {{-- SL --}}
                                <td class="text-muted">{{ $logs->firstItem() + $i }}</td>

                                {{-- Activity --}}
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar-placeholder" style="background:{{ $tc['bg'] }};color:{{ $tc['color'] }};">
                                            <span class="material-icons-round" style="font-size:1rem;">{{ $icon }}</span>
                                        </div>
                                        <div>
                                            <div class="fw-500 text-dark" style="font-size:.85rem;">{{ $log->description }}</div>
                                            <small class="text-muted">{{ $log->created_at->format('d M Y, h:i A') }}</small>
                                        </div>
                                    </div>
                                </td>

                                {{-- Type Badge --}}
                                <td>
                                    <span class="badge" style="background:{{ $tc['bg'] }};color:{{ $tc['color'] }};font-size:.72rem;">
                                        {{ ucfirst($type) }}
                                    </span>
                                </td>

                                {{-- Done By --}}
                                <td>
                                    @if($log->causer)
                                        <div class="fw-500" style="font-size:.82rem;">{{ $log->causer->name }}</div>
                                        <small class="text-muted">{{ $log->causer->email }}</small>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>

                                {{-- Model --}}
                                <td class="text-muted" style="font-size:.78rem;">
                                    @if($log->subject_type)
                                        {{ class_basename($log->subject_type) }}
                                        @if($log->subject_id)
                                            <span class="badge bg-secondary-subtle text-secondary ms-1">#{{ $log->subject_id }}</span>
                                        @endif
                                    @else
                                        —
                                    @endif
                                </td>

                                {{-- Time Ago --}}
                                <td class="text-muted" style="font-size:.78rem;white-space:nowrap;">
                                    {{ $log->created_at->diffForHumans() }}
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <span class="material-icons-round d-block mb-2 opacity-25" style="font-size:3rem;">history</span>
                                    No activity found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer border-0 bg-white d-flex align-items-center justify-content-between flex-wrap gap-2 py-2 px-3">
            <small class="text-muted">
                Showing {{ $logs->firstItem() ?? 0 }}–{{ $logs->lastItem() ?? 0 }} of {{ $logs->total() }}
            </small>
            {{ $logs->links('vendor.pagination.custom') }}
        </div>

    </div>

</div>

@push('styles')
<style>
    /* ── AVATAR ── */
    .avatar-placeholder {
        width: 38px; height: 38px; border-radius: 8px;
        display: inline-flex; align-items: center; justify-content: center;
        font-weight: 700; font-size: .875rem; flex-shrink: 0;
    }

    /* ── TABLE ── */
    .fw-500 { font-weight: 500; }

    .form-label { font-size: .8rem; font-weight: 600; color: var(--text-muted); margin-bottom: 4px; }
    .form-control, .form-select {
        border-radius: 8px; border: 1px solid var(--border);
        font-size: .875rem; padding: .45rem .75rem;
        transition: border-color .2s, box-shadow .2s;
    }
    .form-control:focus, .form-select:focus {
        border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-light);
    }
</style>
@endpush