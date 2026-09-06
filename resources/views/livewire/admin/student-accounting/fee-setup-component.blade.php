<div>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Fee Setup</h5>
            <small class="text-muted">Set the amount, frequency এবং billing month প্রতিটা Class-Fee Type combination এর জন্য।</small>
        </div>

        <div class="card-body">

            @if ($classes->isEmpty())
                <div class="alert alert-warning">কোনো Class পাওয়া যায়নি। আগে Academic Class Assign করুন।</div>
            @elseif ($feeTypes->isEmpty())
                <div class="alert alert-warning">কোনো Fee Type পাওয়া যায়নি। আগে Fee Type যোগ করুন।</div>
            @else
                <div class="fee-setup-table-wrapper">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="sticky-col" style="min-width:150px;">Class</th>
                                @foreach ($feeTypes as $feeType)
                                    <th wire:key="fee-type-head-{{ $feeType->id }}" style="min-width:190px;">
                                        {{ $feeType->name }}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($classes as $class)
                                <tr wire:key="class-row-{{ $class->id }}">
                                    <td class="fw-semibold sticky-col">{{ $class->name }}</td>

                                    @foreach ($feeTypes as $feeType)
                                        @php
                                            $cellFrequency = $grid[$class->id][$feeType->id]['frequency'] ?? 'monthly';
                                            $cellMonth     = $grid[$class->id][$feeType->id]['billing_month'] ?? '';
                                        @endphp
                                        <td
                                            wire:key="cell-{{ $class->id }}-{{ $feeType->id }}"
                                            x-data="{
                                                frequency: '{{ $cellFrequency }}',
                                                billingMonth: '{{ $cellMonth }}'
                                            }"
                                            x-init="$watch('frequency', value => { if (value !== 'yearly') { billingMonth = '' } })"
                                        >

                                            {{-- Amount --}}
                                            <div class="input-group input-group-md mb-1">
                                                <span class="input-group-text">৳</span>
                                                <input
                                                    type="number"
                                                    step="1"
                                                    min="0"
                                                    class="form-control @error('grid.'.$class->id.'.'.$feeType->id.'.amount') is-invalid @enderror"
                                                    wire:model="grid.{{ $class->id }}.{{ $feeType->id }}.amount"
                                                    placeholder="0"
                                                >
                                            </div>
                                            @error('grid.'.$class->id.'.'.$feeType->id.'.amount')
                                                <small class="text-danger d-block mb-1">{{ $message }}</small>
                                            @enderror

                                            {{-- Frequency Dropdown — Alpine এখন এইটার state track করে, Livewire এ শুধু Save-এর সময় value যায় --}}
                                            <div>
                                                <div class="input-group input-group-outline mb-1">
                                                    <select
                                                        class="form-select form-select-sm mb-1 @error('grid.'.$class->id.'.'.$feeType->id.'.frequency') is-invalid @enderror"
                                                        wire:model="grid.{{ $class->id }}.{{ $feeType->id }}.frequency"
                                                        x-model="frequency"
                                                    >
                                                        @foreach ($frequencyOptions as $value => $label)
                                                            <option value="{{ $value }}">{{ $label }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                @error('grid.'.$class->id.'.'.$feeType->id.'.frequency')
                                                    <small class="text-danger d-block mb-1">{{ $message }}</small>
                                                @enderror
                                            </div>

                                            {{-- Billing Month — এখন সম্পূর্ণ Alpine x-show দিয়ে toggle হয়, কোনো server round-trip লাগে না --}}
                                            <div x-show="frequency === 'yearly'" x-cloak>
                                                <div class="input-group input-group-outline">
                                                    <select
                                                        class="form-select form-select-sm @error('grid.'.$class->id.'.'.$feeType->id.'.billing_month') is-invalid @enderror"
                                                        wire:model="grid.{{ $class->id }}.{{ $feeType->id }}.billing_month"
                                                        x-model="billingMonth"
                                                    >
                                                        <option value="">-- Billing Month --</option>
                                                        @foreach (range(1, 12) as $m)
                                                            <option value="{{ $m }}">
                                                                {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                @error('grid.'.$class->id.'.'.$feeType->id.'.billing_month')
                                                    <small class="text-danger d-block">{{ $message }}</small>
                                                @enderror
                                            </div>

                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3 d-flex justify-content-end">
                    <button type="button" class="btn btn-primary" wire:click="save" wire:loading.attr="disabled" wire:target="save">
                        <span wire:loading wire:target="save" class="spinner-border spinner-border-sm me-1"></span>
                        Save Setup
                    </button>
                </div>
            @endif

        </div>
    </div>

    @push('styles')
        <style>
            .fee-setup-table-wrapper {
                overflow-x: auto;
                position: relative;
            }

            .fee-setup-table-wrapper table thead th.sticky-col,
            .fee-setup-table-wrapper table tbody td.sticky-col {
                position: sticky;
                left: 0;
                z-index: 2;
                background-color: #fff;
            }

            .fee-setup-table-wrapper table thead th.sticky-col {
                z-index: 3;
                background-color: #f8f9fa; /* matches table-light */
            }

            .fee-setup-table-wrapper table tbody td.sticky-col {
                box-shadow: 2px 0 3px -1px rgba(0, 0, 0, 0.08);
            }

            .fee-setup-table-wrapper table thead th.sticky-col {
                box-shadow: 2px 0 3px -1px rgba(0, 0, 0, 0.08);
            }

            [data-bs-theme="dark"] .fee-setup-table-wrapper table thead th.sticky-col {
                background-color: #212529;
            }

            [data-bs-theme="dark"] .fee-setup-table-wrapper table tbody td.sticky-col {
                background-color: #1e2124;
            }
        </style>
    @endpush
</div>