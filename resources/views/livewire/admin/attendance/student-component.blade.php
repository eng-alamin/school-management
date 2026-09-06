<div>
    <div class="card">

        {{-- Floating Header --}}
        <div class="mat-card-header header-primary-gradient">
            <h5>Student Attendance</h5>
            <p>Mark or update student attendance</p>
        </div>

        {{-- Select Ground --}}
        <div class="form-section" style="padding-top:40px; padding-bottom:20px">
            <div class="section-heading">
                <span class="material-icons-round">school</span> Select Ground
            </div>
            <div class="row g-4">

                {{-- Class --}}
                <div class="col-md-4">
                    <div class="input-group input-group-outline">
                        <label class="form-label">Class <span class="text-danger">*</span></label>
                        <select wire:model.live="filterClass" class="form-select">
                            <option value="">Select Class</option>
                            @foreach ($classes as $item)
                                <option value="{{ $item->id }}">{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error('filterClass') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                {{-- Section: class-e section thakle "All Section" soho required dropdown,
                    na thakle same dropdown disabled thakbe --}}
                <div class="col-md-4">
                    <div class="input-group input-group-outline">
                        <label class="form-label">
                            Section
                            @if($selectedClassHasSection)
                                <span class="text-danger">*</span>
                            @endif
                        </label>
                        <select
                            wire:model.live="filterSection"
                            class="form-select @error('filterSection') is-invalid @enderror"
                            @disabled(!$filterClass || !$selectedClassHasSection)
                        >
                            @if(!$filterClass)
                                <option value="">Select class first</option>
                            @elseif(!$selectedClassHasSection)
                                <option value="">N/A — this class has no sections</option>
                            @else
                                <option value="">Select Section</option>
                                {{-- ✅ "All Section" — select korle ei class-er sob section-er data eksathe dekhabe --}}
                                <option value="all">All Section</option>
                                @foreach($availableSections as $s)
                                    <option value="{{ $s['id'] }}">{{ $s['name'] }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    @error('filterSection') <span class="text-danger small">{{ $message }}</span> @enderror
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

        {{-- Attendance Table --}}
        @if($hasAttendance)
        <div class="form-section">
            <div class="section-heading d-flex align-items-center justify-content-between">
                <span>Students Attendance</span>
                <span class="att-count-badge">{{ count($data) }} student(s)</span>
            </div>

            <div class="table-responsive mt-3">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th id="th-sl">SL</th>
                            <th id="th-name">Name</th>
                            <th id="th-section">Section</th>
                            <th id="th-roll">Roll</th>
                            <th id="th-status">Status</th>
                            <th id="th-actions" class="no-print">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data as $index => $item)
                        <tr wire:key="student-att-{{ $index }}">
                            <td class="text-muted fw-500">{{ $index + 1 }}</td>
                            <td>
                                <a href="{{ route('admin.student.overview', ['id' => $item['id']]) }}" target="_blank" class="d-flex align-items-center gap-2">
                                    <img src="{{ $item['photo'] ? asset('storage/' . $item['photo']) : asset('assets/img/boy.jpg') }}"
                                        style="width:36px;height:36px;border-radius:8px;object-fit:cover;" alt="">
                                    <span>
                                        <span class="fw-600 d-block" style="font-size:.85rem">{{ $item['name'] }}</span>
                                        <span class="fs-8 fw-bold">{{ $item['student_id'] }}</span>
                                    </span>
                                </a>
                            </td>
                            <td><span class="att-section-pill">{{ $item['section_name'] ?: '—' }}</span></td>
                            <td class="fw-500">{{ $item['roll_no'] }}</td>
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
                            <td class="no-print">
                                <div class="d-flex gap-1">
                                    <a href="{{ route('admin.student.attendance', ['id' => $item['id']]) }}" target="_blank"
                                        class="act-btn view" title="View">
                                        <span class="material-icons-round">visibility</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
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