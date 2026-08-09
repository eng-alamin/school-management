{{-- resources/views/livewire/ministry/statistics/student-component.blade.php --}}

<div class="mstat-wrap">

    {{-- ══ Header ══════════════════════════════════════════════════════════ --}}
    <div class="px-3 pt-3 pb-2">
        <h5 class="fw-bold mb-0 text-dark">Student Statistics</h5>
        <p class="text-secondary mb-0" style="font-size:12px;">Nationwide student count and distribution overview</p>
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

    {{-- ══ Overview Cards ══════════════════════════════════════════════════ --}}
    <div class="px-3 pt-3">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="mstat-card">
                    <div class="mstat-card-label">Total Students</div>
                    <div class="mstat-card-value">{{ number_format($totalStudents) }}</div>
                </div>
            </div>

            @foreach ($genderBreakdown as $row)
                <div class="col-md-4">
                    <div class="mstat-card">
                        <div class="mstat-card-label">{{ ucfirst($row->gender ?? 'Unspecified') }}</div>
                        <div class="mstat-card-value">{{ number_format($row->total) }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- ══ Breakdown Tables ════════════════════════════════════════════════ --}}
    <div class="px-3 pt-3 pb-4">
        <div class="row g-3">
            <div class="col-lg-6">
                <div class="mstat-table-card">
                    <div class="mstat-table-toolbar">Class-wise Student Breakdown</div>
                    <div class="table-responsive">
                        <table class="table mstat-table mb-0">
                            <thead>
                                <tr>
                                    <th>Class</th>
                                    <th class="text-end">Total Student</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($classWiseBreakdown as $row)
                                    <tr>
                                        <td class="text-dark fw-semibold">{{ $row->name }}</td>
                                        <td class="text-end text-secondary">{{ number_format($row->total) }}</td>
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
                    <div class="mstat-table-toolbar">Division-wise Student Breakdown</div>
                    <div class="table-responsive">
                        <table class="table mstat-table mb-0">
                            <thead>
                                <tr>
                                    <th>Division</th>
                                    <th class="text-end">Total Student</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($divisionWiseBreakdown as $row)
                                    <tr>
                                        <td class="text-dark fw-semibold">{{ $divisions[$row->division] ?? $row->division }}</td>
                                        <td class="text-end text-secondary">{{ number_format($row->total) }}</td>
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
</style>
@endpush
