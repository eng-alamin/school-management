<div>
    @push('styles')
    <style>
        .setup-wizard-card {
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,.08);
        }

        .header-pink-gradient {
            /* background: linear-gradient(135deg, #ec4899 0%, #8b5cf6 100%); */
            color: #fff;
            padding: 32px 28px;
        }

        .header-pink-gradient h4 {
            font-size: 1.4rem;
        }

        .wizard-progress-track {
            height: 8px;
            border-radius: 20px;
            background: rgba(255,255,255,.28);
            overflow: hidden;
            margin-top: 18px;
        }

        .wizard-progress-fill {
            height: 100%;
            border-radius: 20px;
            background: #fff;
            transition: width .4s ease;
        }

        .wizard-body {
            padding: 28px;
            background: #fbfbfd;
        }

        .setup-category-title {
            font-weight: 700;
            font-size: .75rem;
            letter-spacing: .06em;
            text-transform: uppercase;
            color: #8b5cf6;
            margin: 26px 0 12px;
        }

        .setup-category-title:first-child {
            margin-top: 0;
        }

        .step-card {
            background: #fff;
            border: 1px solid #ececf2;
            border-radius: 12px;
            padding: 16px 18px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
            transition: box-shadow .2s, border-color .2s;
        }

        .step-card.completed {
            background: #f0fdf4;
            border-color: #bbf7d0;
        }

        .step-card:hover {
            box-shadow: 0 3px 14px rgba(0,0,0,.06);
        }

        .step-left {
            display: flex;
            align-items: center;
            gap: 14px;
            min-width: 0;
        }

        .step-icon-circle {
            width: 40px;
            height: 40px;
            min-width: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f3f4f6;
            color: #9ca3af;
        }

        .step-icon-circle .material-icons-round {
            font-size: 20px;
            line-height: 1;
        }

        .step-card.completed .step-icon-circle {
            background: #22c55e;
            color: #fff;
        }

        .step-text {
            min-width: 0;
        }

        .step-title-text {
            font-weight: 600;
            font-size: .92rem;
            color: #1f2937;
            line-height: 1.3;
        }

        .step-desc-text {
            font-size: .76rem;
            color: #9ca3af;
            margin-top: 2px;
        }

        .step-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-shrink: 0;
        }

        .step-actions .btn {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            white-space: nowrap;
        }

        .step-actions .material-icons-round {
            font-size: 16px;
            line-height: 1;
        }

        @media (max-width: 767px) {
            .step-card {
                flex-direction: column;
                align-items: stretch;
            }
            .step-actions {
                justify-content: flex-end;
            }
        }
    </style>
    @endpush

    <div class="container-fluid py-4">
        <div class="row justify-content-center">
            <div class="col-12 col-xl-9">

                <div class="setup-wizard-card bg-white mb-4">

                    {{-- HEADER --}}
                    <div class="header-pink-gradient">
                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                            <div>
                                <h4 class="mb-1 fw-bold" data-en="Setup Checklist" data-bn="সেটআপ চেকলিস্ট"></h4>
                                <p class="mb-0 small opacity-90"
                                   data-en="Complete the steps below to fully set up your institution."
                                   data-bn="আপনার প্রতিষ্ঠান সম্পূর্ণভাবে চালু করতে নিচের ধাপগুলো সম্পন্ন করুন।"></p>
                            </div>
                            <button
                                type="button"
                                class="btn btn-sm btn-light fw-semibold"
                                wire:click="skipWizard"
                                wire:confirm="Apni ki sure? Wizard skip korle apni Settings theke abar dekhte parben."
                                data-en="Skip for now"
                                data-bn="এখন বাদ দিন"
                            ></button>
                        </div>

                        <div class="wizard-progress-track">
                            <div class="wizard-progress-fill" style="width: {{ $progressPercent }}%"></div>
                        </div>
                        <div class="small mt-2 opacity-90">
                            {{ $progressPercent }}%
                            <span data-en="Complete" data-bn="সম্পন্ন হয়েছে"></span>
                        </div>
                    </div>

                    {{-- BODY --}}
                    <div class="wizard-body">

                        @foreach ($groupedSteps as $category => $stepsInCategory)
                            <div wire:key="category-{{ Str::slug($category) }}">
                                <div class="setup-category-title">{{ $category }}</div>

                                @foreach ($stepsInCategory as $stepKey => $meta)
                                    @php $isDone = $institution->isStepCompleted($stepKey); @endphp

                                    <div class="step-card {{ $isDone ? 'completed' : '' }}" wire:key="step-{{ $stepKey }}">

                                        <div class="step-left">
                                            <div class="step-icon-circle">
                                                @if ($isDone)
                                                    <span class="material-icons-round">check</span>
                                                @else
                                                    <span class="material-icons-round">radio_button_unchecked</span>
                                                @endif
                                            </div>
                                            <div class="step-text">
                                                <div class="step-title-text"
                                                     data-en="{{ $meta['title_en'] }}"
                                                     data-bn="{{ $meta['title_bn'] }}"></div>
                                                <div class="step-desc-text">{{ $category }}</div>
                                            </div>
                                        </div>

                                        <div class="step-actions">
                                            @if (\Illuminate\Support\Facades\Route::has($meta['route']))
                                                <a href="{{ route($meta['route']) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                    <span data-en="Go" data-bn="যান"></span>
                                                    <span class="material-icons-round">open_in_new</span>
                                                </a>
                                            @endif

                                            @if ($isDone)
                                                <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="markIncomplete('{{ $stepKey }}')">
                                                    <span class="material-icons-round">replay</span>
                                                </button>
                                            @else
                                                <button type="button" class="btn btn-sm btn-success" wire:click="markComplete('{{ $stepKey }}')"
                                                        data-en="Done" data-bn="সম্পন্ন"></button>
                                            @endif
                                        </div>

                                    </div>
                                @endforeach
                            </div>
                        @endforeach

                        {{-- FOOTER ACTION --}}
                        @if ($progressPercent === 100)
                            <div class="d-flex justify-content-end mt-4 pt-3 border-top">
                                <button
                                    type="button"
                                    class="btn bg-dark text-white px-4"
                                    wire:click="finishWizard"
                                    wire:loading.attr="disabled"
                                    wire:target="finishWizard"
                                >
                                    <span wire:loading.remove wire:target="finishWizard">
                                        <span data-en="Go to Dashboard" data-bn="ড্যাশবোর্ডে যান"></span>
                                        <span class="material-icons-round" style="font-size:16px;vertical-align:middle;">arrow_forward</span>
                                    </span>
                                    <span wire:loading wire:target="finishWizard">
                                        <span class="spinner-border spinner-border-sm me-2"></span>
                                        <span data-en="Please wait..." data-bn="অপেক্ষা করুন..."></span>
                                    </span>
                                </button>
                            </div>
                        @else
                            <div class="d-flex justify-content-end mt-4 pt-3 border-top">
                                <span class="text-muted small">
                                    <span data-en="Complete all steps to continue to dashboard" data-bn="ড্যাশবোর্ডে যেতে সব ধাপ সম্পন্ন করুন"></span>
                                </span>
                            </div>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>

    @push('scripts')
        <script type="module" src="{{ asset('assets/js/lang/setupwizard.js') }}"></script>
    @endpush
</div>