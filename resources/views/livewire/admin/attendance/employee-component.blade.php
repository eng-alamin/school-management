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
                            class="btn-primary w-100 d-flex justify-content-center align-items-center"
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
    .schedule-input {
        border: 1px solid #e0e0e0;
        padding: 7px 11px;
        border-radius: 6px;
        font-size: 12px;
        outline: none;
        width: 100%;
        transition: border-color .2s, box-shadow .2s;
        background: #fafafa;
    }
    .schedule-input:focus {
        border-color: #e05252;
        box-shadow: 0 0 0 3px rgba(224,82,82,.12);
        background: #fff;
    }

    /* ── Attendance count badge ── */
    .att-count-badge {
        font-size: .72rem;
        font-weight: 600;
        color: #6a6a6a;
        background: #f2f2f2;
        padding: 4px 12px;
        border-radius: 20px;
        white-space: nowrap;
    }

    /* ── Table polish (desktop) ── */
    .att-table-wrap { border-radius: 10px; }
    .att-table thead th {
        font-size: .7rem;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: #9a9a9a;
        font-weight: 700;
        border-bottom: 2px solid #eee;
        padding: 10px 12px;
        white-space: nowrap;
    }
    .att-table tbody td {
        padding: 12px;
        vertical-align: middle;
        border-bottom: 1px solid #f0f0f0;
    }
    .att-table tbody tr { transition: background .15s; }
    .att-table tbody tr:hover { background: #fafafa; }
    .att-table tbody tr:last-child td { border-bottom: none; }

    /* ── Section / Roll pill (re-used for designation/department) ── */
    .att-section-pill, .att-roll-pill {
        font-size: .68rem;
        font-weight: 600;
        color: #4a4a4a;
        background: #f0f0f0;
        padding: 3px 9px;
        border-radius: 20px;
        white-space: nowrap;
    }

    /* ── Status radio -> pill button ── */
    .status-group {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
    }
    .status-opt {
        position: relative;
        display: inline-flex;
        align-items: center;
        cursor: pointer;
        margin: 0;
    }
    .status-opt input {
        position: absolute;
        opacity: 0;
        width: 0;
        height: 0;
    }
    .status-opt span {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: .72rem;
        font-weight: 600;
        padding: 6px 11px;
        border-radius: 20px;
        border: 1px solid #e5e5e5;
        color: #999;
        background: #fff;
        transition: all .15s;
        white-space: nowrap;
    }
    .status-ic { font-size: 15px !important; }
    .status-opt:hover span { border-color: #ccc; }

    .status-opt--present input:checked + span { background: #e8f8ee; border-color: #34c759; color: #1a9c46; }
    .status-opt--absent  input:checked + span { background: #fdecec; border-color: #e0524f; color: #c0362f; }
    .status-opt--late    input:checked + span { background: #fff6e0; border-color: #e0a52f; color: #9c6d05; }
    .status-opt--leave   input:checked + span { background: #e8f3fd; border-color: #3f9ce0; color: #1a6ea6; }

    /* ══════════ MOBILE CARD LIST ══════════ */
    .att-mobile-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    .att-mcard {
        border: 1px solid #eee;
        border-radius: 12px;
        padding: 12px;
        background: #fff;
        box-shadow: 0 1px 4px rgba(0,0,0,.04);
    }
    .att-mcard-top {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        margin-bottom: 10px;
    }
    .att-mcard-sl {
        font-size: .7rem;
        font-weight: 700;
        color: #b0b0b0;
        background: #f5f5f5;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        margin-top: 2px;
    }
    .att-mcard-info { min-width: 0; flex: 1; }

    /* On very small screens, allow status pills to take half width for neat 2-col wrap */
    .status-group--mobile .status-opt {
        flex: 1 1 calc(50% - 3px);
    }
    .status-group--mobile .status-opt span {
        width: 100%;
        justify-content: center;
    }

    /* ══════════ RESPONSIVE TWEAKS ══════════ */
    @media (max-width: 767.98px) {
        .form-section { padding-left: 14px !important; padding-right: 14px !important; }
        .section-heading { font-size: .85rem; }
        .att-count-badge { font-size: .68rem; padding: 3px 10px; }

        .form-footer {
            flex-direction: column-reverse;
            gap: 8px;
        }
        .form-footer .btn-primary,
        .form-footer .btn-outline {
            width: 100%;
            justify-content: center;
        }
    }

    @media (max-width: 380px) {
        .status-group--mobile .status-opt span { font-size: .66rem; padding: 6px 4px; }
        .status-ic { font-size: 13px !important; }
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