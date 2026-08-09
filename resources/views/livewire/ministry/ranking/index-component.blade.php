{{-- resources/views/livewire/ministry/ranking/index-component.blade.php --}}

<div class="rank-wrap">

    {{-- ══ Header ══════════════════════════════════════════════════════════ --}}
    <div class="px-3 pt-3 pb-2">
        <h5 class="fw-bold mb-0 text-dark" data-en="Institution Ranking" data-bn="প্রতিষ্ঠান র‍্যাঙ্কিং">Institution Ranking</h5>
        <p class="text-secondary mb-0" style="font-size:12px;" data-en="National ranking based on academic performance" data-bn="একাডেমিক পারফরম্যান্স অনুযায়ী জাতীয় র‍্যাঙ্কিং">National ranking based on academic performance</p>
    </div>

    {{-- ══ Notice ══════════════════════════════════════════════════════════ --}}
    <div class="px-3 pt-2">
        <div class="rank-notice">
            <span class="material-icons-round">info</span>
            <div>
                <strong data-en="Provisional Ranking:" data-bn="সাময়িক র‍্যাঙ্কিং:">Provisional Ranking:</strong>
                <span data-en=" Currently based on Academic score only (100% weight). Compliance & Inspection scoring is not implemented yet — once it is, it will be factored in and rankings may shift." data-bn=" বর্তমানে শুধুমাত্র একাডেমিক স্কোরের ভিত্তিতে (১০০% ওয়েট)। কমপ্লায়েন্স ও ইন্সপেকশন স্কোরিং এখনো যোগ করা হয়নি — যোগ হলে তা হিসাবে যুক্ত হবে এবং র‍্যাঙ্কিং পরিবর্তন হতে পারে।">
                Compliance &amp; Inspection scoring is not implemented yet — once it is, it will be factored in
                and rankings may shift.</span>
            </div>
        </div>
    </div>

    {{-- ══ Filters ═════════════════════════════════════════════════════════ --}}
    <div class="px-3 pt-3">
        <div class="rank-filter-card">
            <div class="row g-2 align-items-end">
                <div class="col-12 col-md-4">
                    <label class="rank-filter-label" data-en="Division" data-bn="বিভাগ">Division</label>
                    <select class="form-select form-select-sm" wire:model.live="division">
                        <option value="" data-en="All Divisions" data-bn="সকল বিভাগ">All Divisions</option>
                        @foreach ($divisions as $div)
                            <option value="{{ $div }}">{{ $div }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-4">
                    <label class="rank-filter-label" data-en="Academic Session" data-bn="একাডেমিক সেশন">Academic Session</label>
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

    {{-- ══ Ranking Table ═══════════════════════════════════════════════════ --}}
    <div class="px-3 pt-3 pb-4">
        <div class="rank-table-card">
            <div class="rank-table-toolbar">
                <span data-en="National Ranking" data-bn="জাতীয় র‍্যাঙ্কিং">National Ranking</span>
                <span class="text-secondary" style="font-size:11px;" data-en="Institutions with fewer than {{ $minResults }} evaluated results are excluded" data-bn="{{ $minResults }}টির কম মূল্যায়িত ফলাফল আছে এমন প্রতিষ্ঠান বাদ দেওয়া হয়েছে">Institutions with fewer than {{ $minResults }} evaluated results are excluded</span>
            </div>
            <div class="table-responsive">
                <table class="table rank-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th style="width: 60px;" data-en="Rank" data-bn="র‍্যাঙ্ক">Rank</th>
                            <th data-en="Institution" data-bn="প্রতিষ্ঠান">Institution</th>
                            <th data-en="EIIN" data-bn="EIIN">EIIN</th>
                            <th data-en="Division" data-bn="বিভাগ">Division</th>
                            <th class="text-end" data-en="Results" data-bn="ফলাফল">Results</th>
                            <th class="text-end" data-en="Pass Rate" data-bn="পাস রেট">Pass Rate</th>
                            <th class="text-end" data-en="Avg GPA" data-bn="গড় জিপিএ">Avg GPA</th>
                            <th class="text-end" data-en="Academic Score" data-bn="একাডেমিক স্কোর">Academic Score</th>
                            <th class="text-end" data-en="Final Score" data-bn="চূড়ান্ত স্কোর">Final Score</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($ranking as $row)
                            <tr>
                                <td>
                                    @if ($row->rank <= 3)
                                        <span class="rank-top-badge">#{{ $row->rank }}</span>
                                    @else
                                        <span class="text-secondary">#{{ $row->rank }}</span>
                                    @endif
                                </td>
                                <td class="fw-semibold text-dark">{{ $row->institution_name }}</td>
                                <td class="text-secondary">{{ $row->eiin ?? '—' }}</td>
                                <td class="text-secondary">{{ $row->division }}</td>
                                <td class="text-end text-secondary">{{ number_format($row->total_results) }}</td>
                                <td class="text-end text-secondary">{{ $row->pass_rate }}%</td>
                                <td class="text-end text-secondary">{{ $row->avg_gpa }}</td>
                                <td class="text-end text-secondary">{{ $row->academic_score }}</td>
                                <td class="text-end fw-semibold text-dark">{{ $row->final_score }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-secondary py-4" style="font-size:13px;" data-en="No institutions meet the minimum result threshold for the selected filters." data-bn="নির্বাচিত ফিল্টারের জন্য কোনো প্রতিষ্ঠান ন্যূনতম ফলাফল থ্রেশহোল্ড পূরণ করেনি।">No institutions meet the minimum result threshold for the selected filters.</td>
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
    .rank-wrap { background: var(--body-bg); min-height: 100vh; }

    .rank-notice {
        display: flex; align-items: flex-start; gap: 10px;
        background: rgba(217,119,6,0.08); border: 1px solid rgba(217,119,6,0.25);
        color: var(--val); border-radius: var(--radius-card);
        padding: 12px 14px; font-size: 12px;
    }
    .rank-notice .material-icons-round { color: #d97706; font-size: 18px; flex-shrink: 0; }

    .rank-filter-card {
        background: var(--card); border: 1px solid var(--border);
        border-radius: var(--radius-card); padding: 14px;
        box-shadow: var(--shadow);
    }
    .rank-filter-label { font-size: 11px; color: var(--lbl); margin-bottom: 2px; display: block; }

    .rank-table-card {
        background: var(--card); border: 1px solid var(--border);
        border-radius: var(--radius-card); box-shadow: var(--shadow);
        overflow: hidden;
    }
    .rank-table-toolbar {
        display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 8px;
        font-size: 13px; font-weight: 700; color: var(--val);
        padding: 14px 16px; border-bottom: 1px solid var(--border);
    }
    .rank-table thead th {
        font-size: 11px; text-transform: uppercase; letter-spacing: .03em;
        color: var(--lbl); border-bottom: 1px solid var(--border);
        padding: 10px 14px; white-space: nowrap;
    }
    .rank-table tbody td {
        padding: 10px 14px; border-bottom: 1px solid var(--border); font-size: 13px;
    }
    .rank-table tbody tr:last-child td { border-bottom: none; }

    .rank-top-badge {
        display: inline-block; background: var(--primary); color: #fff;
        border-radius: 999px; padding: 2px 10px; font-size: 11px; font-weight: 700;
    }
</style>
@endpush