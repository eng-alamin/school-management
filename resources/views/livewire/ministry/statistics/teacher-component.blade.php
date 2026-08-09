{{-- resources/views/livewire/ministry/statistics/teacher-component.blade.php --}}

<div class="mstat-wrap">

    {{-- ══ Header ══════════════════════════════════════════════════════════ --}}
    <div class="px-3 pt-3 pb-2">
        <h5 class="fw-bold mb-0 text-dark">Teacher Statistics</h5>
        <p class="text-secondary mb-0" style="font-size:12px;">Nationwide teacher count and distribution overview</p>
    </div>

    {{-- ══ Filter Bar ══════════════════════════════════════════════════════ --}}
    <div class="px-3 pt-3">
        <div class="mstat-filter-card">
            <div class="row g-2 align-items-end">
                <div class="col-12 col-md-4">
                    <label class="mstat-filter-label">Division</label>
                    <select wire:model.live="division" class="form-select form-select-sm">
                        <option value="">All Division</option>
                        @foreach ($divisions as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                @if ($division !== '')
                    <div class="col-auto">
                        <button type="button" wire:click="resetFilters" class="mstat-reset-btn">
                            <span class="material-icons-round" style="font-size:14px;vertical-align:middle;">close</span>
                            Reset filters
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ══ Overview Card ═══════════════════════════════════════════════════ --}}
    <div class="px-3 pt-3">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="mstat-card">
                    <div class="mstat-card-label">Total Teachers</div>
                    <div class="mstat-card-value">{{ number_format($totalTeachers) }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ══ Breakdown Tables ════════════════════════════════════════════════ --}}
    <div class="px-3 pt-3 pb-4">
        <div class="row g-3">
            <div class="col-lg-6">
                <div class="mstat-table-card">
                    <div class="mstat-table-toolbar">Subject-wise Teacher Distribution</div>
                    <div class="table-responsive">
                        <table class="table mstat-table mb-0">
                            <thead>
                                <tr>
                                    <th>Subject</th>
                                    <th class="text-end">Teacher Count</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($subjectWiseDistribution as $row)
                                    <tr>
                                        <td class="text-dark fw-semibold">{{ $row->name }}</td>
                                        <td class="text-end text-secondary">{{ number_format($row->teacher_count) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center text-secondary py-3" style="font-size:13px;">No data found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="mstat-table-card">
                    <div class="mstat-table-toolbar">Teacher-Student Ratio per Institution</div>
                    <div class="table-responsive mstat-scroll-table">
                        <table class="table mstat-table mb-0">
                            <thead>
                                <tr>
                                    <th>Institution</th>
                                    <th class="text-end">Student</th>
                                    <th class="text-end">Teacher</th>
                                    <th class="text-end">Ratio</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($institutionRatios as $row)
                                    <tr class="{{ $row->is_flagged ? 'mstat-flag-row' : '' }}">
                                        <td class="text-dark fw-semibold">{{ $row->institution_name }}</td>
                                        <td class="text-end text-secondary">{{ number_format($row->student_total) }}</td>
                                        <td class="text-end text-secondary">{{ number_format($row->teacher_total) }}</td>
                                        <td class="text-end">
                                            @if ($row->ratio === null)
                                                <span class="inv-badge pending">No Teacher</span>
                                            @else
                                                <span class="inv-badge {{ $row->is_flagged ? 'unpaid' : 'paid' }}">1 : {{ $row->ratio }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-secondary py-3" style="font-size:13px;">No data found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

@push('styles')
<style>
    .mstat-wrap { background: var(--body-bg); min-height: 100vh; }

    .mstat-filter-card {
        background: var(--card); border: 1px solid var(--border);
        border-radius: var(--radius-card); padding: 14px;
        box-shadow: var(--shadow);
    }
    .mstat-filter-label { font-size: 11px; color: var(--lbl); margin-bottom: 2px; display: block; }
    .mstat-reset-btn {
        background: transparent; border: none; color: #ef4444;
        font-size: 12px; font-weight: 500; padding: 8px 0 0;
    }

    .mstat-card {
        background: var(--card); border: 1px solid var(--border);
        border-radius: var(--radius-card); box-shadow: var(--shadow);
        padding: 16px;
    }
    .mstat-card-label { font-size: 12px; color: var(--lbl); margin-bottom: 4px; }
    .mstat-card-value { font-size: 22px; font-weight: 700; color: var(--val); }

    .mstat-table-card {
        background: var(--card); border: 1px solid var(--border);
        border-radius: var(--radius-card); box-shadow: var(--shadow);
        overflow: hidden; height: 100%;
    }
    .mstat-table-toolbar {
        font-size: 13px; font-weight: 700; color: var(--val);
        padding: 14px 16px; border-bottom: 1px solid var(--border);
    }
    .mstat-table thead th {
        font-size: 11px; text-transform: uppercase; letter-spacing: .03em;
        color: var(--lbl); border-bottom: 1px solid var(--border);
        padding: 10px 16px; white-space: nowrap;
    }
    .mstat-table tbody td {
        padding: 10px 16px; border-bottom: 1px solid var(--border); font-size: 13px;
    }
    .mstat-table tbody tr:last-child td { border-bottom: none; }
    .mstat-flag-row { background: rgba(239,68,68,0.05); }
    .mstat-scroll-table { max-height: 320px; overflow-y: auto; }

    .inv-badge {
        display: inline-block; padding: 3px 10px; border-radius: 4px;
        font-size: 11px; font-weight: 600; border: 1px solid transparent;
    }
    .inv-badge.paid    { background: transparent; border-color: #22c55e; color: #22c55e; }
    .inv-badge.unpaid  { background: transparent; border-color: #ef4444; color: #ef4444; }
    .inv-badge.pending { background: transparent; border-color: #8a95ab; color: #8a95ab; }
</style>
@endpush
