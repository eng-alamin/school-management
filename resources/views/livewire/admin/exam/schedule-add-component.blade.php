<div class="mat-card" style="padding-top:28px">

    {{-- Floating Header --}}
    <div class="mat-card-header header-pink-gradient">
        <h5><span class="material-icons-round" style="font-size:18px;vertical-align:middle;margin-right:6px">event_note</span>Add Schedule</h5>
        <p>Create or update exam schedule</p>
    </div>

    {{-- Select Ground --}}
    <div class="form-section" style="padding-top:40px; padding-bottom:20px">
        <div class="section-heading">
            <span class="material-icons-round">tune</span> Select Exam
        </div>
        <div class="row g-4">

            {{-- Exam --}}
            <div class="col-md-6 offset-md-3">
                <div class="input-group input-group-outline" wire:ignore>
                    <label class="form-label">Exam Name</label>
                    <select wire:model.live="filterExam" class="form-select">
                        <option value="">Select Exam</option>
                        @foreach ($exams as $item)
                            <option value="{{ $item->id }}">
                                {{ $item->name }}
                                @if($item->classAssign)
                                    — {{ $item->classAssign->class->name ?? '' }}
                                    @if($item->classAssign->section) ({{ $item->classAssign->section->name }}) @endif
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>
                @error('filterExam') <span class="text-danger small">{{ $message }}</span> @enderror
            </div>

            {{-- Class (read-only display) --}}
            <div class="col-md-6 d-flex align-items-center">
                @if($selectedClassLabel)
                    <span class="badge bg-info-subtle text-dark" style="font-size:.85rem;padding:8px 14px">
                        <span class="material-icons-round" style="font-size:15px;vertical-align:middle">class</span>
                        Class: {{ $selectedClassLabel }}
                    </span>
                @endif
            </div>

            {{-- Filter Button --}}
            <div class="col-md-12 text-center">
                <button wire:click="filter"
                        wire:loading.attr="disabled"
                        wire:target="filter"
                        class="btn-pink w-100 d-flex justify-content-center align-items-center"
                        type="button">
                    <span wire:loading.remove wire:target="filter">
                        <span class="material-icons-round" style="font-size:16px;vertical-align:middle;margin-right:4px">filter_alt</span> Load Subjects
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
    <div class="form-section">
        <div class="section-heading">
            <span class="material-icons-round">table_chart</span> Schedule Details
        </div>

        <div class="table-responsive mt-3">
            <table class="schedule-table">
                <thead>
                    <tr>
                        <th id="th-subject">Subject</th>
                        <th id="th-marks">Full / Pass</th>
                        <th id="th-date">Date <span class="req">*</span></th>
                        <th id="th-starting-time">Starting Time <span class="req">*</span></th>
                        <th id="th-ending-time">Ending Time <span class="req">*</span></th>
                        <th id="th-hall-room">Class Room</th>
                        <th id="th-remarks">Remarks</th>
                        <th id="th-publish">Publish</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rows as $index => $row)
                    <tr wire:key="schedule-row-{{ $row['exam_setup_detail_id'] }}">

                        <td>
                            <strong>{{ $row['subject_name'] }}</strong>
                        </td>

                        <td class="text-muted" style="white-space:nowrap">
                            {{ $row['full_mark'] }} / {{ $row['pass_mark'] }}
                        </td>

                        <td>
                            <input type="date" wire:model="rows.{{ $index }}.exam_date"
                                class="schedule-input schedule-date">
                            @error('rows.'.$index.'.exam_date') <span class="text-danger small d-block">{{ $message }}</span> @enderror
                        </td>

                        <td>
                            <div class="schedule-time-wrap">
                                <span class="material-icons-round schedule-time-icon">schedule</span>
                                <input type="time" wire:model="rows.{{ $index }}.start_time"
                                    class="schedule-input schedule-time">
                            </div>
                            @error('rows.'.$index.'.start_time') <span class="text-danger small d-block">{{ $message }}</span> @enderror
                        </td>

                        <td>
                            <div class="schedule-time-wrap">
                                <span class="material-icons-round schedule-time-icon">schedule</span>
                                <input type="time" wire:model="rows.{{ $index }}.end_time"
                                    class="schedule-input schedule-time">
                            </div>
                            @error('rows.'.$index.'.end_time') <span class="text-danger small d-block">{{ $message }}</span> @enderror
                        </td>

                        <td>
                            <input type="text" wire:model="rows.{{ $index }}.class_room"
                                class="schedule-input" placeholder="Room No.">
                            @error('rows.'.$index.'.class_room') <span class="text-danger small d-block">{{ $message }}</span> @enderror
                        </td>

                        <td>
                            <input type="text" wire:model="rows.{{ $index }}.remarks"
                                class="schedule-input" placeholder="Optional">
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
    <div class="form-footer">
        <button class="btn-outline" type="button" wire:click="resetForm">
            <span class="material-icons-round" style="font-size:16px">refresh</span> Reset
        </button>
        <button class="btn-pink" type="button"
                wire:click="save"
                wire:loading.attr="disabled"
                wire:target="save">
            <span wire:loading.remove wire:target="save">
                <span class="material-icons-round">save</span> Save
            </span>
            <span wire:loading wire:target="save">
                <span class="material-icons-round" style="font-size:16px;animation:spin .7s linear infinite">sync</span> Saving...
            </span>
        </button>
    </div>
    @endif

</div>

@push('styles')
<style>
    .schedule-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }
    .schedule-table thead th {
        padding: 10px 10px;
        text-align: left;
        font-weight: 600;
        font-size: 12px;
        color: #aaa;
        white-space: nowrap;
    }
    .schedule-table tbody td {
        padding: 7px 8px;
        vertical-align: top;
    }
    .schedule-input {
        border: 1px solid #3d3d3d;
        padding: 6px 10px;
        border-radius: 4px;
        font-size: 12px;
        outline: none;
        width: 100%;
        transition: border-color 0.2s;
    }
    .schedule-input:focus {
        border-color: #e05252;
    }
    .schedule-date {
        width: 140px;
        cursor: pointer;
    }
    .schedule-date::-webkit-calendar-picker-indicator {
        filter: invert(0.6);
        cursor: pointer;
    }
    .schedule-time-wrap {
        display: flex;
        align-items: center;
        gap: 6px;
        border: 1px solid #3d3d3d;
        border-radius: 4px;
        padding: 5px 8px;
        width: 148px;
        transition: border-color 0.2s;
    }
    .schedule-time-wrap:focus-within {
        border-color: #e05252;
    }
    .schedule-time-icon {
        font-size: 15px !important;
        color: #888;
        flex-shrink: 0;
    }
    .schedule-time {
        background: transparent;
        border: none;
        padding: 0;
        width: 100%;
    }
    input[type="time"]::-webkit-calendar-picker-indicator {
        display: none;
    }

    /* Toggle */
    .toggle-switch { position: relative; display: inline-block; width: 40px; height: 20px; vertical-align: middle; }
    .toggle-switch input { opacity: 0; width: 0; height: 0; }
    .toggle-slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #d1d5db; transition: .2s; border-radius: 999px; }
    .toggle-slider::before { position: absolute; content: ""; height: 14px; width: 14px; left: 3px; bottom: 3px; background-color: #fff; transition: .2s; border-radius: 50%; box-shadow: 0 1px 3px rgba(0,0,0,.3); }
    .toggle-switch input:checked + .toggle-slider { background: linear-gradient(195deg, #ec407a, #d81b60); }
    .toggle-switch input:checked + .toggle-slider::before { transform: translateX(20px); }
</style>
@endpush