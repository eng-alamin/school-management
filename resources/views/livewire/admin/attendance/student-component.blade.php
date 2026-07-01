<div class="mat-card" style="padding-top:28px">

    {{-- Floating Header --}}
    <div class="mat-card-header header-pink-gradient">
        <h5><span class="material-icons-round" style="font-size:18px;vertical-align:middle;margin-right:6px">event_note</span>Student Attendance</h5>
        <p>Mark or update student attendance</p>
    </div>

    {{-- Select Ground --}}
    <div class="form-section" style="padding-top:40px; padding-bottom:20px">
        <div class="section-heading">
            <span class="material-icons-round">tune</span> Select Ground
        </div>
        <div class="row g-4">

            {{-- Class --}}
            <div class="col-md-4">
                <div class="input-group input-group-outline">
                    <label class="form-label">Class</label>
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
            <div class="col-md-4">
                <div class="input-group input-group-outline">
                    <label class="form-label">Section</label>
                    <select wire:model.live="filterSection" class="form-select"
                        {{ empty($sections) ? 'disabled' : '' }}>
                        <option value="">{{ !$filterClass ? 'Select Class First' : 'Select Section' }}</option>
                        @if(!empty($sections))
                            <option value="all">All Section</option>
                            @foreach ($sections as $item)
                                <option value="{{ $item->id }}">{{ $item->name }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
            </div>

            {{-- Date --}}
            <div class="col-md-4">
                <div class="input-group input-group-outline" wire:ignore>
                    <label class="form-label">Date</label>
                    <input wire:model="filterDate" type="date" class="form-control" data-dp-value="{{ $filterDate }}"
                        onfocus="focused(this)" onfocusout="defocused(this)">
                </div>
                @error('filterDate') <span class="text-danger small">{{ $message }}</span> @enderror
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
            <span class="material-icons-round">groups</span> Students Attendance
        </div>

        <div class="table-responsive mt-3">
            <table class="schedule-table">
                <thead>
                    <tr>
                        <th id="th-sl">SL</th>
                        <th id="th-name">Name</th>
                        <th id="th-section">Section</th>
                        <th id="th-roll">Roll</th>
                        <th id="th-register-no">Register No</th>
                        <th id="th-status">Status</th>
                        <th id="th-remark">Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data as $index => $item)
                    <tr wire:key="student-att-{{ $index }}">
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item['name'] }}</td>
                        <td>{{ $item['section_name'] }}</td>
                        <td>{{ $item['roll_no'] }}</td>
                        <td>{{ $item['register_no'] }}</td>
                        <td>
                            <div class="status-group">
                                <label>
                                    <input type="radio" wire:model="data.{{ $index }}.status" value="present">
                                    <span class="text-success">Present</span>
                                </label>
                                <label>
                                    <input type="radio" wire:model="data.{{ $index }}.status" value="absent">
                                    <span class="text-danger">Absent</span>
                                </label>
                                <label>
                                    <input type="radio" wire:model="data.{{ $index }}.status" value="late">
                                    <span class="text-warning">Late</span>
                                </label>
                                <label>
                                    <input type="radio" wire:model="data.{{ $index }}.status" value="leave">
                                    <span class="text-info">Leave</span>
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
        vertical-align: middle;
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
    .status-group {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }
    .status-group label {
        display: flex;
        align-items: center;
        gap: 4px;
        cursor: pointer;
        font-size: 12px;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('livewire:initialized', () => {
        Livewire.hook('morph.updated', ({ el }) => {
            setTimeout(() => {
                el.querySelectorAll('.input-group-outline .form-select').forEach(function(select) {
                    if (!select.nextElementSibling || !select.nextElementSibling.classList.contains('custom-select-wrapper')) {
                        buildCustomSelect(select);
                    }
                });

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

                el.querySelectorAll('.input-group-outline input[type="date"]').forEach(function(input) {
                    if (!input._dpInit) { _initDatepickers(); }
                });
            }, 0);
        });
    });
</script>
@endpush