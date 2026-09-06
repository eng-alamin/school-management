<div>
    <div class="card">

        {{-- Floating Header --}}
        <div class="mat-card-header header-primary-gradient">
            <h5>Employee Attendance</h5>
            <p>Mark or update employee attendance</p>
        </div>

        {{-- Select Ground --}}
        <div class="form-section" style="padding-top:40px; padding-bottom:20px">
            <div class="section-heading">
                <span class="material-icons-round">school</span> Select Ground
            </div>

            <div class="row g-4">

                {{-- Role --}}
                <div class="col-12 col-md-6">
                    <div class="input-group input-group-outline">
                        <label class="form-label">Role</label>
                        <select wire:model.live="filterRole" class="form-select">
                            <option value="">Select Role</option>
                            <option value="teacher">Teacher</option>
                            <option value="accountant">Accountant</option>
                            <option value="staff">Staff</option>
                        </select>
                    </div>
                    @error('filterRole') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                {{-- Date --}}
                <div class="col-12 col-md-6">
                    <div class="input-group input-group-outline" wire:ignore>
                        <label class="form-label">Date</label>
                        <input wire:model="filterDate" type="date" class="form-control" data-dp-value="{{ $filterDate }}"
                            onfocus="focused(this)" onfocusout="defocused(this)">
                    </div>
                    @error('filterDate') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                {{-- Filter Button --}}
                <div class="col-12 text-center">
                    <button wire:click="filter"
                            wire:loading.attr="disabled"
                            wire:target="filter"
                            class="btn btn-primary w-100 d-flex justify-content-center align-items-center"
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

        {{-- Attendance List --}}
        @if($hasAttendance)
        <div class="form-section">
            <div class="section-heading d-flex align-items-center justify-content-between flex-wrap gap-2">
                <span>Employees Attendance</span>
                <span class="att-count-badge">{{ count($data) }} employee(s)</span>
            </div>

            {{-- ══ DESKTOP/TABLET: Table view (hidden on mobile) ══ --}}
            <div class="table-responsive mt-3 att-table-wrap d-none d-md-block">
                <table class="table table-hover mb-0 att-table">
                    <thead>
                        <tr>
                            <th id="th-sl">SL</th>
                            <th id="th-name">Name</th>
                            <th id="th-designation">Designation</th>
                            <th id="th-department">Department</th>
                            <th id="th-status">Status</th>
                            <th id="th-remarks">Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data as $index => $item)
                        <tr wire:key="employee-att-row-{{ $index }}">
                            <td class="text-muted fw-500">{{ $index + 1 }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $item['photo'] ? asset('storage/' . $item['photo']) : asset('assets/img/boy.jpg') }}"
                                        style="width:36px;height:36px;border-radius:8px;object-fit:cover;" alt="">
                                    <span>
                                        <span class="fw-600 d-block" style="font-size:.85rem">{{ $item['name'] }}</span>
                                        <span class="fs-8 fw-bold">{{ $item['employee_id'] }}</span>
                                    </span>
                                </div>
                            </td>
                            <td><span class="att-section-pill">{{ $item['designation'] ?: '—' }}</span></td>
                            <td><span class="att-section-pill">{{ $item['department'] ?: '—' }}</span></td>
                            <td>
                                <div class="status-group">
                                    <label class="status-opt status-opt--present">
                                        <input type="radio" wire:model="data.{{ $index }}.status" value="present">
                                        <span><span class="material-icons-round status-ic">check_circle</span>Present</span>
                                    </label>
                                    <label class="status-opt status-opt--absent">
                                        <input type="radio" wire:model="data.{{ $index }}.status" value="absent">
                                        <span><span class="material-icons-round status-ic">cancel</span>Absent</span>
                                    </label>
                                    <label class="status-opt status-opt--late">
                                        <input type="radio" wire:model="data.{{ $index }}.status" value="late">
                                        <span><span class="material-icons-round status-ic">schedule</span>Late</span>
                                    </label>
                                    <label class="status-opt status-opt--leave">
                                        <input type="radio" wire:model="data.{{ $index }}.status" value="leave">
                                        <span><span class="material-icons-round status-ic">event_busy</span>Leave</span>
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

            {{-- ══ MOBILE: Card view (hidden on desktop/tablet) ══ --}}
            <div class="att-mobile-list d-md-none mt-3">
                @foreach($data as $index => $item)
                <div class="att-mcard" wire:key="employee-att-card-{{ $index }}">
                    <div class="att-mcard-top">
                        <span class="att-mcard-sl">{{ $index + 1 }}</span>
                        <div class="att-mcard-info">
                            <span class="fw-600 d-block" style="font-size:.85rem">{{ $item['name'] }}</span>
                            <div class="d-flex align-items-center gap-1 flex-wrap mt-1">
                                <span class="att-section-pill">{{ $item['designation'] ?: '—' }}</span>
                                <span class="att-roll-pill">{{ $item['department'] ?: '—' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="status-group status-group--mobile">
                        <label class="status-opt status-opt--present">
                            <input type="radio" wire:model="data.{{ $index }}.status" value="present">
                            <span><span class="material-icons-round status-ic">check_circle</span>Present</span>
                        </label>
                        <label class="status-opt status-opt--absent">
                            <input type="radio" wire:model="data.{{ $index }}.status" value="absent">
                            <span><span class="material-icons-round status-ic">cancel</span>Absent</span>
                        </label>
                        <label class="status-opt status-opt--late">
                            <input type="radio" wire:model="data.{{ $index }}.status" value="late">
                            <span><span class="material-icons-round status-ic">schedule</span>Late</span>
                        </label>
                        <label class="status-opt status-opt--leave">
                            <input type="radio" wire:model="data.{{ $index }}.status" value="leave">
                            <span><span class="material-icons-round status-ic">event_busy</span>Leave</span>
                        </label>
                    </div>

                    <input type="text"
                        wire:model="data.{{ $index }}.remarks"
                        class="schedule-input mt-2"
                        placeholder="Remarks">
                </div>
                @endforeach
            </div>
        </div>

        {{-- Footer --}}
        <div class="form-footer">
            <button class="btn btn-secondary" type="button" wire:click="resetForm">
                <span class="material-icons-round" style="font-size:16px">refresh</span> Reset
            </button>

            <button class="btn btn-primary" type="button"
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