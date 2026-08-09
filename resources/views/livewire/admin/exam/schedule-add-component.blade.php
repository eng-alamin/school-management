<div>
    <div class="card">

        <div class="mat-card-header header-primary-gradient">
            <h5>Exam Schedule</h5>
            <p>Create or update the date, time and room for each subject.</p>
        </div>

        {{-- Select Ground --}}
        <div class="form-section" style="padding-top:40px; padding-bottom:20px">
            <div class="section-heading">
                <span class="material-icons-round">school</span> Select Ground
            </div>
            <div class="row g-4">

                {{-- Select Exam --}}
                <div class="col-md-6 offset-md-3">
                    <div class="input-group input-group-outline" wire:ignore>
                        <label class="form-label">Exam</label>
                        <select wire:model.live="filterExam" class="form-select">
                            <option value="">Select Exam</option>
                            @foreach ($exams as $item)
                                <option value="{{ $item->id }}">
                                    {{ $item->name }}
                                    @if($item->classAssign)
                                        — {{ $item->classAssign->academicClass->name ?? '' }}
                                        @if($item->classAssign->academicSection)
                                            ({{ $item->classAssign->academicSection->name }})
                                        @endif
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @error('filterExam') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                {{-- Filter Button --}}
                <div class="col-md-12 text-center">
                    <button wire:click="filter"
                            wire:loading.attr="disabled"
                            wire:target="filter"
                            class="btn-primary w-100 d-flex justify-content-center align-items-center"
                            type="button">
                        <span wire:loading.remove wire:target="filter">
                            <span class="material-icons-round" style="font-size:16px;vertical-align:middle;margin-right:4px">filter_alt</span> Filter
                        </span>
                        <span wire:loading wire:target="filter">
                            <span class="material-icons-round" style="font-size:16px;animation:spin .7s linear infinite">sync</span> Loading...
                        </span>
                    </button>
                </div>

            </div>
        </div>

        {{-- Schedule Table --}}
        @if($hasSchedule)
        <div class="card-body px-4 pt-0 pb-4">

            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                <div class="d-flex align-items-center gap-2">
                    <span class="fw-semibold">Subject Schedule</span>
                    @if($selectedClassLabel)
                        <span class="badge schedule-class-badge">{{ $selectedClassLabel }}</span>
                    @endif
                </div>
                <span class="text-muted small">{{ count($rows) }} subject(s)</span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th style="min-width:150px">Subject</th>
                            <th style="min-width:100px">Full / Pass</th>
                            <th style="min-width:160px">Date <span class="req">*</span></th>
                            <th style="min-width:140px">Start <span class="req">*</span></th>
                            <th style="min-width:140px">End <span class="req">*</span></th>
                            <th style="min-width:130px">Room</th>
                            <th style="min-width:160px">Remarks</th>
                            <th style="min-width:80px" class="text-center">Publish</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rows as $index => $row)
                        <tr wire:key="schedule-row-{{ $row['exam_setup_detail_id'] }}">

                            <td>
                                <strong>{{ $row['subject_name'] }}</strong>
                            </td>

                            <td class="text-muted" style="white-space:nowrap">
                                <span class="badge schedule-mark-badge">{{ $row['full_mark'] }} / {{ $row['pass_mark'] }}</span>
                            </td>

                            <td>
                                <input type="date" wire:model="rows.{{ $index }}.exam_date"
                                    class="form-control form-control-sm @error('rows.'.$index.'.exam_date') is-invalid @enderror">
                                @error('rows.'.$index.'.exam_date')
                                    <div class="invalid-feedback d-block small">{{ $message }}</div>
                                @enderror
                            </td>

                            <td>
                                <input type="time" wire:model="rows.{{ $index }}.start_time"
                                    class="form-control form-control-sm @error('rows.'.$index.'.start_time') is-invalid @enderror">
                                @error('rows.'.$index.'.start_time')
                                    <div class="invalid-feedback d-block small">{{ $message }}</div>
                                @enderror
                            </td>

                            <td>
                                <input type="time" wire:model="rows.{{ $index }}.end_time"
                                    class="form-control form-control-sm @error('rows.'.$index.'.end_time') is-invalid @enderror">
                                @error('rows.'.$index.'.end_time')
                                    <div class="invalid-feedback d-block small">{{ $message }}</div>
                                @enderror
                            </td>

                            <td>
                                <input type="text" wire:model="rows.{{ $index }}.class_room"
                                    class="form-control form-control-sm @error('rows.'.$index.'.class_room') is-invalid @enderror"
                                    placeholder="Room no.">
                                @error('rows.'.$index.'.class_room')
                                    <div class="invalid-feedback d-block small">{{ $message }}</div>
                                @enderror
                            </td>

                            <td>
                                <input type="text" wire:model="rows.{{ $index }}.remarks"
                                    class="form-control form-control-sm" placeholder="Optional">
                            </td>

                            <td class="text-center">
                                <label class="toggle-switch">
                                    <input type="checkbox" wire:model="rows.{{ $index }}.is_published">
                                    <span class="toggle-slider"></span>
                                </label>
                            </td>

                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Footer --}}
        <div class="card-footer border-0 bg-transparent px-4 pb-4 pt-0 d-flex justify-content-end gap-2">
            <button class="btn-outline" type="button" wire:click="resetForm">
                <span class="material-icons-round" style="font-size:16px">refresh</span> Reset
            </button>
            <button class="btn-primary" type="button"
                    wire:click="save"
                    wire:loading.attr="disabled"
                    wire:target="save">
                <span wire:loading.remove wire:target="save" style="display: inline-flex;align-items: center;gap: 6px">
                    <span class="material-icons-round">save</span> Save
                </span>
                <span wire:loading wire:target="save">
                    <span class="material-icons-round" style="font-size:16px;animation:spin .7s linear infinite">sync</span> Saving...
                </span>
            </button>
        </div>
        @endif

    </div>
</div>

@push('styles')
<style>
    .schedule-header-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: linear-gradient(135deg, var(--schedule-accent), var(--schedule-accent-dark));
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        flex-shrink: 0;
    }

    .btn-schedule-primary {
        background: linear-gradient(135deg, var(--schedule-accent), var(--schedule-accent-dark));
        color: #fff;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        transition: filter .15s ease, transform .05s ease;
    }
    .btn-schedule-primary:hover,
    .btn-schedule-primary:focus {
        filter: brightness(1.08);
        color: #fff;
    }
    .btn-schedule-primary:active { transform: translateY(1px); }
    .btn-schedule-primary:disabled { opacity: .65; }

    .schedule-class-badge {
        background: color-mix(in srgb, var(--schedule-accent) 15%, transparent);
        color: var(--schedule-accent-dark);
        font-weight: 600;
        font-size: .72rem;
        padding: .4em .7em;
        border-radius: 999px;
    }

    .schedule-mark-badge {
        background: var(--bs-secondary-bg, #f1f3f5);
        color: var(--bs-body-color);
        font-weight: 500;
        font-size: .75rem;
        padding: .35em .6em;
        border-radius: 6px;
    }

    /* Toggle */
    .toggle-switch { position: relative; display: inline-block; width: 40px; height: 20px; vertical-align: middle; }
    .toggle-switch input { opacity: 0; width: 0; height: 0; }
    .toggle-slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #d1d5db; transition: .2s; border-radius: 999px; }
    .toggle-slider::before { position: absolute; content: ""; height: 14px; width: 14px; left: 3px; bottom: 3px; background-color: #fff; transition: .2s; border-radius: 50%; box-shadow: 0 1px 3px rgba(0,0,0,.3); }
    .toggle-switch input:checked + .toggle-slider { background: linear-gradient(195deg, var(--schedule-accent), var(--schedule-accent-dark)); }
    .toggle-switch input:checked + .toggle-slider::before { transform: translateX(20px); }
</style>
@endpush