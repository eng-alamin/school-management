{{-- resources/views/livewire/ministry/geography/heatmap-component.blade.php --}}

<div class="geo-wrap">

    {{-- ══ Header ══════════════════════════════════════════════════════════ --}}
    <div class="px-3 pt-3 pb-2">
        <h5 class="fw-bold mb-0 text-dark" data-en="Geographic Heatmap" data-bn="ভৌগোলিক হিটম্যাপ">Geographic Heatmap</h5>
        <p class="text-secondary mb-0" style="font-size:12px;" data-en="Division & district-wise intensity overview" data-bn="বিভাগ ও জেলাভিত্তিক তীব্রতার সংক্ষিপ্ত চিত্র">Division &amp; district-wise intensity overview</p>
    </div>

    {{-- ══ Controls ════════════════════════════════════════════════════════ --}}
    <div class="px-3 pt-3">
        <div class="geo-filter-card">
            <div class="row g-2 align-items-end">
                <div class="col-6 col-md-3">
                    <label class="geo-filter-label" data-en="Metric" data-bn="মেট্রিক">Metric</label>
                    <select class="form-select form-select-sm" wire:model.live="metric">
                        <option value="institutions" data-en="Institution Count" data-bn="প্রতিষ্ঠান সংখ্যা">Institution Count</option>
                        <option value="students" data-en="Student Count" data-bn="শিক্ষার্থী সংখ্যা">Student Count</option>
                        <option value="pass_rate" data-en="Exam Pass Rate" data-bn="পরীক্ষার পাস রেট">Exam Pass Rate</option>
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <label class="geo-filter-label" data-en="Level" data-bn="লেভেল">Level</label>
                    <select class="form-select form-select-sm" wire:model.live="level">
                        <option value="division" data-en="Division" data-bn="বিভাগ">Division</option>
                        <option value="district" data-en="District" data-bn="জেলা">District</option>
                    </select>
                </div>
                @if ($level === 'district')
                    <div class="col-6 col-md-3">
                        <label class="geo-filter-label" data-en="Division" data-bn="বিভাগ">Division</label>
                        <select class="form-select form-select-sm" wire:model.live="division">
                            <option value="" data-en="All Divisions" data-bn="সকল বিভাগ">All Divisions</option>
                            @foreach ($divisions as $div)
                                <option value="{{ $div }}">{{ $div }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                @if ($metric === 'pass_rate')
                    <div class="col-6 col-md-3">
                        <label class="geo-filter-label" data-en="Academic Session" data-bn="একাডেমিক সেশন">Academic Session</label>
                        <select class="form-select form-select-sm" wire:model.live="academicSessionId">
                            <option value="" data-en="All Sessions" data-bn="সকল সেশন">All Sessions</option>
                            @foreach ($academicSessions as $session)
                                <option value="{{ $session->id }}">{{ $session->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ══ Heatmap Grid ════════════════════════════════════════════════════ --}}
    <div class="px-3 pt-3 pb-4">
        <div class="geo-panel-card">
            @php
                $baseColors = [
                    'institutions' => [13, 110, 253],
                    'students'     => [111, 66, 193],
                    'pass_rate'    => [25, 135, 84],
                ];
                [$r, $g, $b] = $baseColors[$metric] ?? [13, 110, 253];
            @endphp

            @if ($gridData->isEmpty())
                <p class="text-secondary text-center py-5 mb-0" style="font-size:13px;" data-en="No data available for the selected filters." data-bn="নির্বাচিত ফিল্টারের জন্য কোনো ডেটা পাওয়া যায়নি।">No data available for the selected filters.</p>
            @else
                <div class="row g-3">
                    @foreach ($gridData as $cell)
                        @php
                            $alpha = 0.15 + ($cell->intensity * 0.75);
                            $textDark = $cell->intensity < 0.55;
                        @endphp
                        <div class="col-md-3 col-sm-4 col-6">
                            <div
                                class="geo-cell {{ $textDark ? 'text-dark' : 'text-white' }}"
                                style="background-color: rgba({{ $r }}, {{ $g }}, {{ $b }}, {{ $alpha }});"
                            >
                                <div class="geo-cell-label">{{ $cell->label }}</div>
                                <div class="geo-cell-value">
                                    {{ $metric === 'pass_rate' ? $cell->value.'%' : number_format($cell->value) }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="geo-legend">
                    <span class="text-secondary" style="font-size:11px;" data-en="Low" data-bn="কম">Low</span>
                    <div class="geo-legend-bar" style="background: linear-gradient(to right, rgba({{ $r }}, {{ $g }}, {{ $b }}, 0.15), rgba({{ $r }}, {{ $g }}, {{ $b }}, 0.9));"></div>
                    <span class="text-secondary" style="font-size:11px;" data-en="High" data-bn="বেশি">High</span>
                </div>
            @endif
        </div>
    </div>

</div>

@push('styles')
<style>
    .geo-wrap { background: var(--body-bg); min-height: 100vh; }

    .geo-filter-card {
        background: var(--card); border: 1px solid var(--border);
        border-radius: var(--radius-card); padding: 14px;
        box-shadow: var(--shadow);
    }
    .geo-filter-label { font-size: 11px; color: var(--lbl); margin-bottom: 2px; display: block; }

    .geo-panel-card {
        background: var(--card); border: 1px solid var(--border);
        border-radius: var(--radius-card); box-shadow: var(--shadow);
        padding: 18px;
    }

    .geo-cell {
        border-radius: 12px; padding: 14px; min-height: 100px;
        display: flex; flex-direction: column; justify-content: space-between;
    }
    .geo-cell-label { font-size: 12px; font-weight: 600; }
    .geo-cell-value { font-size: 20px; font-weight: 700; margin-top: 8px; }

    .geo-legend { display: flex; align-items: center; gap: 8px; margin-top: 20px; }
    .geo-legend-bar { flex: 1; height: 10px; border-radius: 5px; }
</style>
@endpush