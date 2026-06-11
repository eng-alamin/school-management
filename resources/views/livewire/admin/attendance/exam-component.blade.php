<div class="mat-card" style="padding-top:28px">

    {{-- Floating Header --}}
    <div class="mat-card-header header-pink-gradient">
        <h5><span class="material-icons-round" style="font-size:18px;vertical-align:middle;margin-right:6px">event_note</span>Exam Attendance</h5>
        <p>Create or update exam attendance</p>
    </div>

    {{-- Select Ground --}}
    <div class="form-section" style="padding-top:40px; padding-bottom:20px">
        <div class="section-heading">
            <span class="material-icons-round">tune</span> Select Ground
        </div>
        <div class="row g-4">

            {{-- Exam --}}
            <div class="col-md-3">
                <div class="input-group input-group-outline">
                    <label class="form-label">Exam <span class="req">*</span></label>
                    <select wire:model.live="filterExam" class="form-select">
                        <option value="">Select Exam</option>
                        @foreach ($exams as $item)
                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                        @endforeach
                    </select>
                </div>
                @error('filterExam') <span class="text-danger small">{{ $message }}</span> @enderror
            </div>

            {{-- Class --}}
            <div class="col-md-3">
                <div class="input-group input-group-outline">
                    <label class="form-label">Class <span class="req">*</span></label>
                    <select wire:model.live="filterClass" class="form-select">
                        <option value="">Select Class</option>
                        @foreach ($classes as $item)
                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                        @endforeach
                    </select>
                </div>
                @error('filterClass') <span class="text-danger small">{{ $message }}</span> @enderror
            </div>

            {{-- Section --}}
            <div class="col-md-3">
                <div wire:ignore.self class="input-group input-group-outline">
                    <label class="form-label">Section <span class="req">*</span></label>
                    <select wire:model.live="filterSection" class="form-select">
                        <option value="">Select Section</option>
                        @foreach ($sections as $item)
                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                        @endforeach
                    </select>
                </div>
                @error('filterSection') <span class="text-danger small">{{ $message }}</span> @enderror
            </div>

            {{-- Subject --}}
            <div class="col-md-3">
                <div wire:ignore.self class="input-group input-group-outline">
                    <label class="form-label">Subject <span class="req">*</span></label>
                    <select wire:model="filterSubject" class="form-select">
                        <option value="">Select Subject</option>
                        @foreach ($subjects as $item)
                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                        @endforeach
                    </select>
                </div>
                @error('filterSubject') <span class="text-danger small">{{ $message }}</span> @enderror
            </div>

            {{-- Filter Button --}}
            <div class="col-md-12 text-center">
                <button wire:click="filter"
                        wire:loading.attr="disabled"
                        wire:target="filter"
                        class="btn-pink w-100 d-flex justify-content-center align-items-center"
                        type="button">
                    <span wire:loading.remove wire:target="filter">
                        <span class="material-icons-round" style="font-size:16px;vertical-align:middle;margin-right:4px">filter_alt</span> Filter
                    </span>
                    <span wire:loading wire:target="filter">
                        <span class="material-icons-round" style="font-size:16px;animation:spin .7s linear infinite">sync</span> Filtering...
                    </span>
                </button>
            </div>

        </div>
    </div>

    {{-- Attendance Table --}}
    @if($hasAttendance)
    <div class="form-section">
        <div class="section-heading">
            <span class="material-icons-round">groups</span> Exams Attendance
        </div>

        <div class="table-responsive mt-3">
            <table class="schedule-table">

                <thead>
                    <tr>
                        <th>SL</th>
                        <th>Name</th>
                        <th>Roll</th>
                        <th>Register No</th>
                        <th>Status</th>
                        <th>Remarks</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($data as $index => $item)
                    <tr wire:key="exam-att-{{ $index }}">
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item['name'] }}</td>
                        <td>{{ $item['roll_no'] }}</td>
                        <td>{{ $item['register_no'] }}</td>
                        <td>
                            <div class="status-group">

                                <label>
                                    <input type="radio"
                                        wire:model="data.{{ $index }}.status"
                                        value="present">
                                    <span class="text-success">Present</span>
                                </label>

                                <label>
                                    <input type="radio"
                                        wire:model="data.{{ $index }}.status"
                                        value="absent">
                                    <span class="text-danger">Absent</span>
                                </label>

                                <label>
                                    <input type="radio"
                                        wire:model="data.{{ $index }}.status"
                                        value="late">
                                    <span class="text-warning">Late</span>
                                </label>

                            </div>
                        </td>
                        <td>
                            <input type="text"
                                wire:model="data.{{ $index }}.remarks"
                                class="schedule-input"
                                placeholder="Remarks">
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
    .marks-sub-header {
        display: flex;
        gap: 4px;
        margin-top: 3px;
        font-size: 10px;
        color: #666;
        font-weight: 400;
    }
    .marks-sub-header span {
        width: 64px;
        text-align: center;
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
    .schedule-time:focus {
        border-color: transparent;
    }
    input[type="time"]::-webkit-calendar-picker-indicator {
        display: none;
    }
    .schedule-number {
        width: 64px;
        text-align: center;
        -moz-appearance: textfield;
    }
    .schedule-number::-webkit-outer-spin-button,
    .schedule-number::-webkit-inner-spin-button {
        -webkit-appearance: none;
    }
    /* .schedule-remove-btn {
        background: transparent;
        border: 1px solid #3d3d3d;
        color: #e05252;
        border-radius: 4px;
        padding: 5px 7px;
        cursor: pointer;
        display: flex;
        align-items: center;
        transition: background 0.15s, border-color 0.15s;
    }
    .schedule-remove-btn:hover {
        background: #3a2020;
        border-color: #e05252;
    }
    .schedule-remove-btn .material-icons-round {
        font-size: 16px;
    } */
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('livewire:initialized', () => {
        Livewire.hook('morph.updated', ({ el }) => {
            setTimeout(() => {
                // Re-init custom selects
                el.querySelectorAll('.input-group-outline .form-select').forEach(function(select) {
                    if (!select.nextElementSibling || !select.nextElementSibling.classList.contains('custom-select-wrapper')) {
                        buildCustomSelect(select);
                    }
                });

                // Re-init text/time inputs
                el.querySelectorAll('.input-group-outline input').forEach(function(input) {
                    var group = input.closest('.input-group');
                    if (!group) return;
                    if (input.value && input.value.trim() !== '') {
                        group.classList.add('is-filled');
                    } else {
                        group.classList.remove('is-filled');
                    }
                    if (input._materialInit) return;
                    input._materialInit = true;
                    input.addEventListener('focus', function() { group.classList.add('is-focused'); });
                    input.addEventListener('blur', function() {
                        group.classList.remove('is-focused');
                        group.classList.toggle('is-filled', !!input.value.trim());
                    });
                    input.addEventListener('input', function() {
                        group.classList.toggle('is-filled', !!input.value.trim());
                    });
                });
            }, 50);
        });
    });
</script>
@endpush