<div>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Fee Setup</h5>
            <small class="text-muted">Set the amount, frequency এবং billing month প্রতিটা Class-Fee Type combination এর জন্য।</small>
        </div>

        <div class="card-body">

            @if ($classes->isEmpty())
                <div class="alert alert-warning">কোনো Class পাওয়া যায়নি। আগে Academic Class Setup করুন।</div>
            @elseif ($feeTypes->isEmpty())
                <div class="alert alert-warning">কোনো Fee Type পাওয়া যায়নি। আগে Fee Type যোগ করুন।</div>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="min-width:150px;">Class</th>
                                @foreach ($feeTypes as $feeType)
                                    <th wire:key="fee-type-head-{{ $feeType->id }}" style="min-width:220px;">
                                        {{ $feeType->name }}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($classes as $class)
                                <tr wire:key="class-row-{{ $class->id }}">
                                    <td class="fw-semibold">{{ $class->name }}</td>

                                    @foreach ($feeTypes as $feeType)
                                        @php
                                            $cellFrequency = $grid[$class->id][$feeType->id]['frequency'] ?? 'monthly';
                                        @endphp
                                        <td wire:key="cell-{{ $class->id }}-{{ $feeType->id }}">

                                            {{-- Amount + Status Toggle --}}
                                            <div class="input-group input-group-sm mb-1">
                                                <span class="input-group-text">৳</span>
                                                <input
                                                    type="number"
                                                    step="0.01"
                                                    min="0"
                                                    class="form-control @error('grid.'.$class->id.'.'.$feeType->id.'.amount') is-invalid @enderror"
                                                    wire:model.defer="grid.{{ $class->id }}.{{ $feeType->id }}.amount"
                                                    placeholder="0"
                                                >
                                                <button
                                                    type="button"
                                                    class="btn btn-sm {{ ($grid[$class->id][$feeType->id]['status'] ?? true) ? 'btn-outline-success' : 'btn-outline-secondary' }}"
                                                    wire:click="toggleStatus({{ $class->id }}, {{ $feeType->id }})"
                                                    title="On রাখলে এই Fee ঐ Class-এর জন্য Invoice-এ যাবে"
                                                >
                                                    <i class="bi {{ ($grid[$class->id][$feeType->id]['status'] ?? true) ? 'bi-check-circle' : 'bi-x-circle' }}"></i>
                                                </button>
                                            </div>
                                            @error('grid.'.$class->id.'.'.$feeType->id.'.amount')
                                                <small class="text-danger d-block mb-1">{{ $message }}</small>
                                            @enderror

                                            {{-- Frequency Dropdown --}}
                                            <select
                                                class="form-select form-select-sm mb-1 @error('grid.'.$class->id.'.'.$feeType->id.'.frequency') is-invalid @enderror"
                                                wire:model.live="grid.{{ $class->id }}.{{ $feeType->id }}.frequency"
                                            >
                                                @foreach ($frequencyOptions as $value => $label)
                                                    <option value="{{ $value }}">{{ $label }}</option>
                                                @endforeach
                                            </select>
                                            @error('grid.'.$class->id.'.'.$feeType->id.'.frequency')
                                                <small class="text-danger d-block mb-1">{{ $message }}</small>
                                            @enderror

                                            {{-- Billing Month — শুধু Yearly হলে দেখাবে --}}
                                            @if ($cellFrequency === 'yearly')
                                                <select
                                                    class="form-select form-select-sm @error('grid.'.$class->id.'.'.$feeType->id.'.billing_month') is-invalid @enderror"
                                                    wire:model.defer="grid.{{ $class->id }}.{{ $feeType->id }}.billing_month"
                                                >
                                                    <option value="">-- Billing Month --</option>
                                                    @foreach (\Carbon\Carbon::getDays() ? range(1, 12) : [] as $m)
                                                        <option value="{{ $m }}">
                                                            {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('grid.'.$class->id.'.'.$feeType->id.'.billing_month')
                                                    <small class="text-danger d-block">{{ $message }}</small>
                                                @enderror
                                            @endif

                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3 d-flex justify-content-end">
                    <button type="button" class="btn bg-dark text-white" wire:click="save" wire:loading.attr="disabled" wire:target="save">
                        <span wire:loading wire:target="save" class="spinner-border spinner-border-sm me-1"></span>
                        Save Setup
                    </button>
                </div>
            @endif

        </div>
    </div>
</div>