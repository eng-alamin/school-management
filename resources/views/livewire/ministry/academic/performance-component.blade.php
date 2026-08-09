{{-- resources/views/livewire/ministry/academic/performance-component.blade.php --}}

<div class="perf-wrap">

    {{-- ══ Header ══════════════════════════════════════════════════════════ --}}
    <div class="px-3 pt-3 pb-2">
        <h5 class="fw-bold mb-0 text-dark" data-en="Academic Performance &amp; Exam Monitoring" data-bn="একাডেমিক পারফরম্যান্স ও পরীক্ষা মনিটরিং">Academic Performance &amp; Exam Monitoring</h5>
        <p class="text-secondary mb-0" style="font-size:12px;" data-en="Nationwide exam result overview across all institutions" data-bn="দেশব্যাপী সকল প্রতিষ্ঠানের পরীক্ষার ফলাফল ওভারভিউ">Nationwide exam result overview across all institutions</p>
    </div>

    {{-- ══ Filters ═════════════════════════════════════════════════════════ --}}
    <div class="px-3 pt-3">
        <div class="perf-filter-card">
            <div class="row g-2 align-items-end">
                <div class="col-12 col-md-4">
                    <label class="perf-filter-label" data-en="Division" data-bn="বিভাগ">Division</label>
                    <select class="form-select form-select-sm" wire:model.live="division">
                        <option value="" data-en="All Divisions" data-bn="সকল বিভাগ">All Divisions</option>
                        @foreach ($divisions as $div)
                            <option value="{{ $div }}">{{ $div }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-4">
                    <label class="perf-filter-label" data-en="Academic Session" data-bn="একাডেমিক সেশন">Academic Session</label>
                    <select class="form-select form-select-sm" wire:model.live="academicSessionId">
                        <option value="" data-en="All Sessions" data-bn="সকল সেশন">All Sessions</option>
                        @foreach ($academicSessions as $session)
                            <option value="{{ $session->id }}">{{ $session->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- ══ Summary Cards ═══════════════════════════════════════════════════ --}}
    <div class="px-3 pt-3">
        <div class="row g-3">
            <div class="col-md-3 col-6">
                <div class="perf-stat-card">
                    <div class="perf-stat-icon"><span class="material-icons-round">fact_check</span></div>
                    <div class="perf-stat-value">{{ number_format($summary['total_exams']) }}</div>
                    <div class="perf-stat-label" data-en="Published Exams" data-bn="প্রকাশিত পরীক্ষা">Published Exams</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="perf-stat-card">
                    <div class="perf-stat-icon"><span class="material-icons-round">assignment_turned_in</span></div>
                    <div class="perf-stat-value">{{ number_format($summary['total_results']) }}</div>
                    <div class="perf-stat-label" data-en="Results Evaluated" data-bn="মূল্যায়িত ফলাফল">Results Evaluated</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="perf-stat-card">
                    <div class="perf-stat-icon success"><span class="material-icons-round">trending_up</span></div>
                    <div class="perf-stat-value text-success">{{ $summary['pass_rate'] }}%</div>
                    <div class="perf-stat-label" data-en="Overall Pass Rate" data-bn="সামগ্রিক পাসের হার">Overall Pass Rate</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="perf-stat-card">
                    <div class="perf-stat-icon"><span class="material-icons-round">school</span></div>
                    <div class="perf-stat-value">{{ $summary['avg_gpa'] ?? 'N/A' }}</div>
                    <div class="perf-stat-label" data-en="Average GPA" data-bn="গড় জিপিএ">Average GPA</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ══ Result Status Breakdown ═════════════════════════════════════════ --}}
    <div class="px-3 pt-3">
        <div class="perf-section-card">
            <div class="perf-section-title" data-en="Result Status Breakdown" data-bn="ফলাফলের অবস্থা বিভাজন">Result Status Breakdown</div>
            <div class="row g-3 text-center">
                <div class="col-md-3 col-6">
                    <div class="perf-mini-box success">
                        <div class="perf-mini-value">{{ number_format($summary['pass_count']) }}</div>
                        <div class="perf-mini-label" data-en="Pass" data-bn="পাস">Pass</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="perf-mini-box danger">
                        <div class="perf-mini-value">{{ number_format($summary['fail_count']) }}</div>
                        <div class="perf-mini-label" data-en="Fail" data-bn="ফেল">Fail</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="perf-mini-box warning">
                        <div class="perf-mini-value">{{ number_format($summary['absent_count']) }}</div>
                        <div class="perf-mini-label" data-en="Absent" data-bn="অনুপস্থিত">Absent</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="perf-mini-box neutral">
                        <div class="perf-mini-value">{{ number_format($summary['incomplete_count']) }}</div>
                        <div class="perf-mini-label" data-en="Incomplete" data-bn="অসম্পূর্ণ">Incomplete</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ══ Division-wise Breakdown ═════════════════════════════════════════ --}}
    <div class="px-3 pt-3">
        <div class="perf-table-card">
            <div class="perf-table-toolbar" data-en="Division-wise Performance" data-bn="বিভাগ অনুযায়ী পারফরম্যান্স">Division-wise Performance</div>
            <div class="table-responsive">
                <table class="table perf-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th data-en="Division" data-bn="বিভাগ">Division</th>
                            <th class="text-end" data-en="Total Results" data-bn="মোট ফলাফল">Total Results</th>
                            <th class="text-end" data-en="Pass" data-bn="পাস">Pass</th>
                            <th class="text-end" data-en="Fail" data-bn="ফেল">Fail</th>
                            <th class="text-end" data-en="Pass Rate" data-bn="পাসের হার">Pass Rate</th>
                            <th class="text-end" data-en="Avg GPA" data-bn="গড় জিপিএ">Avg GPA</th>
                            <th class="text-end" data-en="Avg %" data-bn="গড় %">Avg %</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($divisionBreakdown as $row)
                            <tr>
                                <td class="fw-semibold text-dark">{{ $row->division }}</td>
                                <td class="text-end text-secondary">{{ number_format($row->total_results) }}</td>
                                <td class="text-end text-secondary">{{ number_format($row->pass_count) }}</td>
                                <td class="text-end text-secondary">{{ number_format($row->fail_count) }}</td>
                                <td class="text-end">
                                    <span class="inv-badge {{ $row->is_flagged ? 'unpaid' : 'paid' }}">{{ $row->pass_rate }}%</span>
                                </td>
                                <td class="text-end text-secondary">{{ $row->avg_gpa ?? 'N/A' }}</td>
                                <td class="text-end text-secondary">{{ $row->avg_percentage !== null ? $row->avg_percentage.'%' : 'N/A' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-secondary py-4" style="font-size:13px;" data-en="No published results found for the selected filters." data-bn="নির্বাচিত ফিল্টারের জন্য কোনো প্রকাশিত ফলাফল পাওয়া যায়নি।">No published results found for the selected filters.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ══ Institution-wise Breakdown ══════════════════════════════════════ --}}
    <div class="px-3 pt-3 pb-4">
        <div class="perf-table-card">
            <div class="perf-table-toolbar" data-en="Institution-wise Performance" data-bn="প্রতিষ্ঠান অনুযায়ী পারফরম্যান্স">Institution-wise Performance</div>
            <div class="table-responsive">
                <table class="table perf-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th role="button" wire:click="sortByColumn('institution_name')" class="user-select-none">
                                <span data-en="Institution" data-bn="প্রতিষ্ঠান">Institution</span>
                                @if ($this->sortBy === 'institution_name')
                                    <span class="material-icons-round align-middle" style="font-size:14px;">
                                        {{ $this->sortDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}
                                    </span>
                                @endif
                            </th>
                            <th data-en="EIIN" data-bn="ইআইআইএন">EIIN</th>
                            <th data-en="Division" data-bn="বিভাগ">Division</th>
                            <th class="text-end user-select-none" role="button" wire:click="sortByColumn('total_results')">
                                <span data-en="Results" data-bn="ফলাফল">Results</span>
                                @if ($this->sortBy === 'total_results')
                                    <span class="material-icons-round align-middle" style="font-size:14px;">
                                        {{ $this->sortDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}
                                    </span>
                                @endif
                            </th>
                            <th class="text-end user-select-none" role="button" wire:click="sortByColumn('pass_rate')">
                                <span data-en="Pass Rate" data-bn="পাসের হার">Pass Rate</span>
                                @if ($this->sortBy === 'pass_rate')
                                    <span class="material-icons-round align-middle" style="font-size:14px;">
                                        {{ $this->sortDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}
                                    </span>
                                @endif
                            </th>
                            <th class="text-end" data-en="Avg GPA" data-bn="গড় জিপিএ">Avg GPA</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($institutionBreakdown as $row)
                            <tr class="{{ $row->is_flagged ? 'perf-flag-row' : '' }}">
                                <td class="fw-semibold text-dark">
                                    {{ $row->institution_name }}
                                    @if ($row->is_flagged)
                                        <span class="material-icons-round text-danger align-middle" style="font-size:14px;" title="Pass rate below {{ $passRateThreshold }}%">flag</span>
                                    @endif
                                </td>
                                <td class="text-secondary">{{ $row->eiin ?? '—' }}</td>
                                <td class="text-secondary">{{ $row->division }}</td>
                                <td class="text-end text-secondary">{{ number_format($row->total_results) }}</td>
                                <td class="text-end text-secondary">{{ $row->pass_rate }}%</td>
                                <td class="text-end text-secondary">{{ $row->avg_gpa ?? 'N/A' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-secondary py-4" style="font-size:13px;" data-en="No published results found for the selected filters." data-bn="নির্বাচিত ফিল্টারের জন্য কোনো প্রকাশিত ফলাফল পাওয়া যায়নি।">No published results found for the selected filters.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

@push('styles')
<style>
    .perf-wrap { background: var(--body-bg); min-height: 100vh; }

    .perf-filter-card {
        background: var(--card); border: 1px solid var(--border);
        border-radius: var(--radius-card); padding: 14px;
        box-shadow: var(--shadow);
    }
    .perf-filter-label { font-size: 11px; color: var(--lbl); margin-bottom: 2px; display: block; }

    .perf-stat-card {
        background: var(--card); border: 1px solid var(--border);
        border-radius: var(--radius-card); box-shadow: var(--shadow);
        padding: 16px; height: 100%;
    }
    .perf-stat-icon {
        width: 34px; height: 34px; border-radius: 9px;
        background: var(--primary-light); color: var(--primary);
        display: flex; align-items: center; justify-content: center;
        margin-bottom: 10px;
    }
    .perf-stat-icon .material-icons-round { font-size: 18px; }
    .perf-stat-icon.success { background: rgba(34,197,94,0.12); color: #22c55e; }
    .perf-stat-value { font-size: 22px; font-weight: 700; color: var(--val); line-height: 1.2; }
    .perf-stat-label { font-size: 12px; color: var(--lbl); margin-top: 2px; }

    .perf-section-card, .perf-table-card {
        background: var(--card); border: 1px solid var(--border);
        border-radius: var(--radius-card); box-shadow: var(--shadow);
        overflow: hidden;
    }
    .perf-section-card { padding: 16px; }
    .perf-section-title, .perf-table-toolbar {
        font-size: 13px; font-weight: 700; color: var(--val);
        padding-bottom: 12px;
    }
    .perf-table-toolbar { padding: 14px 16px 0; }

    .perf-mini-box { border-radius: 10px; padding: 14px 10px; }
    .perf-mini-box.success { background: rgba(34,197,94,0.08); }
    .perf-mini-box.danger  { background: rgba(239,68,68,0.08); }
    .perf-mini-box.warning { background: rgba(217,119,6,0.08); }
    .perf-mini-box.neutral { background: var(--section-bg); }
    .perf-mini-value { font-size: 20px; font-weight: 700; }
    .perf-mini-box.success .perf-mini-value { color: #22c55e; }
    .perf-mini-box.danger .perf-mini-value  { color: #ef4444; }
    .perf-mini-box.warning .perf-mini-value { color: #d97706; }
    .perf-mini-box.neutral .perf-mini-value { color: var(--val); }
    .perf-mini-label { font-size: 11px; color: var(--lbl); margin-top: 2px; }

    .perf-table thead th {
        font-size: 11px; text-transform: uppercase; letter-spacing: .03em;
        color: var(--lbl); border-bottom: 1px solid var(--border);
        padding: 10px 14px; white-space: nowrap;
    }
    .perf-table tbody td {
        padding: 10px 14px; border-bottom: 1px solid var(--border); font-size: 13px;
    }
    .perf-table tbody tr:last-child td { border-bottom: none; }
    .perf-flag-row { background: rgba(239,68,68,0.05); }

    .inv-badge {
        display: inline-block; padding: 3px 10px; border-radius: 4px;
        font-size: 11px; font-weight: 600; border: 1px solid transparent;
    }
    .inv-badge.paid    { background: transparent; border-color: #22c55e; color: #22c55e; }
    .inv-badge.unpaid  { background: transparent; border-color: #ef4444; color: #ef4444; }
</style>
@endpush