{{-- resources/views/livewire/ministry/reports/index-component.blade.php --}}

<div class="rpt-wrap">

    {{-- ══ Header ══════════════════════════════════════════════════════════ --}}
    <div class="px-3 pt-3 pb-2">
        <h5 class="fw-bold mb-0 text-dark" data-en="Reports & Analytics" data-bn="রিপোর্ট ও অ্যানালিটিক্স">Reports &amp; Analytics</h5>
        <p class="text-secondary mb-0" style="font-size:12px;" data-en="Generate and download nationwide reports" data-bn="দেশব্যাপী রিপোর্ট তৈরি ও ডাউনলোড করুন">Generate and download nationwide reports</p>
    </div>

    {{-- ══ Filters ═════════════════════════════════════════════════════════ --}}
    <div class="px-3 pt-3 pb-4">
        <div class="rpt-filter-card">
            <div class="row g-2 align-items-end">

                <div class="col-12 col-md-4">
                    <label class="rpt-filter-label" data-en="Report Type" data-bn="রিপোর্টের ধরন">Report Type</label>
                    <select wire:model.live="reportType" class="form-select form-select-sm">
                        @foreach (\App\Livewire\Ministry\Reports\IndexComponent::REPORT_TYPES as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-md-3">
                    <label class="rpt-filter-label" data-en="Division" data-bn="বিভাগ">Division</label>
                    <select wire:model.live="division" class="form-select form-select-sm">
                        <option value="">{{ __('All') }}</option>
                        @foreach ($this->divisions as $div)
                            <option value="{{ $div }}">{{ $div }}</option>
                        @endforeach
                    </select>
                </div>

                @if (in_array($reportType, ['academic_performance', 'ranking']))
                    <div class="col-6 col-md-3">
                        <label class="rpt-filter-label" data-en="Academic Session" data-bn="একাডেমিক সেশন">Academic Session</label>
                        <select wire:model.live="academicSessionId" class="form-select form-select-sm">
                            <option value="">{{ __('All') }}</option>
                            @foreach ($this->academicSessions as $session)
                                <option value="{{ $session->id }}">{{ $session->name ?? $session->id }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                @if ($reportType === 'institution_list')
                    <div class="col-6 col-md-3">
                        <label class="rpt-filter-label" data-en="Verification Status" data-bn="যাচাইকরণ অবস্থা">Verification Status</label>
                        <select wire:model.live="verificationStatus" class="form-select form-select-sm">
                            <option value="">{{ __('All') }}</option>
                            @foreach (\App\Models\Institution::VERIFICATION_STATUSES as $status)
                                <option value="{{ $status }}">{{ \App\Models\Institution::VERIFICATION_LABELS[$status] ?? $status }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="col-6 col-md-2">
                    <label class="rpt-filter-label" data-en="Format" data-bn="ফরম্যাট">Format</label>
                    <select wire:model="format" class="form-select form-select-sm">
                        <option value="pdf">PDF</option>
                        <option value="excel">Excel</option>
                    </select>
                </div>

            </div>

            <div class="mt-3">
                <button wire:click="generate" class="rpt-generate-btn" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="generate" class="d-inline-flex align-items-center gap-1">
                        <span class="material-icons-round" style="font-size:16px;">download</span>
                        <span data-en="Generate & Download" data-bn="তৈরি করুন ও ডাউনলোড করুন">Generate &amp; Download</span>
                    </span>
                    <span wire:loading wire:target="generate">{{ __('Generating...') }}</span>
                </button>
            </div>
        </div>
    </div>

</div>

@push('styles')
<style>
    .rpt-wrap { background: var(--body-bg); min-height: 100vh; }

    .rpt-filter-card {
        background: var(--card); border: 1px solid var(--border);
        border-radius: var(--radius-card); padding: 16px;
        box-shadow: var(--shadow);
    }
    .rpt-filter-label { font-size: 11px; color: var(--lbl); margin-bottom: 2px; display: block; }

    .rpt-generate-btn {
        background: linear-gradient(195deg, #444, #111); color: #fff; border: none;
        border-radius: var(--radius-btn); padding: 9px 18px; font-size: 13px; font-weight: 600;
        box-shadow: 0 4px 14px var(--primary-shadow);
    }
</style>
@endpush