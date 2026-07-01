<div>

    <div class="card">

        {{-- Floating Header --}}
        <div class="mat-card-header header-pink-gradient">
            <h5><span class="material-icons-round" style="font-size:18px;vertical-align:middle;margin-right:6px">assignment</span>Fee Allocation</h5>
            <p>Allocate fee groups to students by class and section</p>
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
                        <label class="form-label">Class </label>
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
                            @if (!empty($sections))
                                <option value="all">All Section</option>
                                @foreach ($sections as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                </div>

                {{-- Fee Group --}}
                <div class="col-md-4">
                    <div class="input-group input-group-outline">
                        <label class="form-label">Fee Group </label>
                        <select wire:model.live="fee_group_id" class="form-select">
                            <option value="">Select Fee Group</option>
                            @foreach ($feeGroups as $group)
                                <option value="{{ $group->id }}">{{ $group->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error('fee_group_id') <span class="text-danger small">{{ $message }}</span> @enderror
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

        {{-- Student List --}}
        @if ($hasFiltered)
        <div class="form-section">
            <div class="section-heading">
                <span class="material-icons-round">format_list_bulleted</span> Student List
                <span class="badge-count">{{ count($students) }} Students</span>
            </div>

            @if (count($students) > 0)

            {{-- Select All Toolbar --}}
            <div class="alloc-toolbar">
                <label class="alloc-select-all">
                    <input type="checkbox"
                           class="alloc-checkbox"
                           wire:model.live="selectAll">
                    <span>Select All</span>
                </label>
                <span class="alloc-counter">
                    <span class="material-icons-round" style="font-size:15px;vertical-align:middle">people</span>
                    {{ count($selectedStudents) }} / {{ count($students) }} selected
                </span>
            </div>

            <div class="table-responsive mt-2">
                <table class="table-loader">
                    <thead>
                        <tr>
                            <th style="width:44px"></th>
                            <th id="th-sl">SL</th>
                            <th id="th-name">Name</th>
                            <th id="th-section">Section</th>
                            <th id="th-register-no">Register No</th>
                            <th id="th-roll-no">Roll No</th>
                            <th id="th-gender">Gender</th>
                            <th id="th-mobile">Mobile</th>
                            <th id="th-guardian">Guardian</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($students as $i => $student)
                        <tr wire:key="student-{{ $student['id'] }}"
                            class="{{ in_array($student['id'], $selectedStudents) ? 'row-selected' : '' }}">
                            <td>
                                <input type="checkbox"
                                       class="alloc-checkbox"
                                       wire:model.live="selectedStudents"
                                       value="{{ $student['id'] }}">
                            </td>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $student['name'] }}</td>
                            <td>{{ $student['section']['name'] ?? '—' }}</td>
                            <td>{{ $student['student_id'] ?? '—' }}</td>
                            <td>{{ $student['roll_no'] ?? '—' }}</td>
                            <td>{{ $student['gender'] ?? '—' }}</td>
                            <td>{{ $student['mobile'] ?? '—' }}</td>
                            <td>{{ collect($student['guardians'] ?? [])->pluck('name')->join(', ') ?: '—' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Footer --}}
            <div class="form-footer">
                <button class="btn-outline" type="button" wire:click="resetForm">
                    <span class="material-icons-round" style="font-size:16px">refresh</span> Reset
                </button>
                <button class="btn-pink" type="button"
                        wire:click="save"
                        wire:loading.attr="disabled"
                        wire:target="save"
                        @if(count($selectedStudents) === 0) disabled @endif>
                    <span wire:loading.remove wire:target="save">
                        <span class="material-icons-round">save</span> Allocate & Save
                    </span>
                    <span wire:loading wire:target="save">
                        <span class="material-icons-round" style="font-size:16px;animation:spin .7s linear infinite">sync</span> Saving...
                    </span>
                </button>
            </div>

            @else
            {{-- Empty State --}}
            <div class="empty-state">
                <span class="material-icons-round empty-icon">inbox</span>
                <p>No students found for selected class/section.</p>
            </div>
            @endif
        </div>
        @endif

    </div>
</div>

@push('styles')
<style>
    /* ── Table ── */
    .table-loader {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }
    .table-loader thead th {
        padding: 10px 10px;
        text-align: left;
        font-weight: 600;
        font-size: 12px;
        color: #aaa;
        white-space: nowrap;
    }
    .table-loader tbody td {
        padding: 8px 10px;
        vertical-align: middle;
        font-size: 13px;
    }
    .table-loader tbody tr {
        transition: background .15s;
        cursor: pointer;
    }
    .table-loader tbody tr:hover {
        background: rgba(255,255,255,.03);
    }
    .row-selected {
        background: rgba(224, 82, 82, .10) !important;
    }

    /* ── Checkbox ── */
    .alloc-checkbox {
        width: 16px;
        height: 16px;
        cursor: pointer;
        accent-color: #e05252;
    }

    /* ── Toolbar ── */
    .alloc-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 12px;
        background: rgba(255,255,255,.03);
        border-radius: 6px;
        border: 1px solid rgba(255,255,255,.06);
        margin-bottom: 4px;
    }
    .alloc-select-all {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        margin: 0;
    }
    .alloc-counter {
        font-size: 12px;
        color: #aaa;
    }

    /* ── Badge count ── */
    .section-heading {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        font-weight: 600;
        color: #bbb;
        text-transform: uppercase;
        letter-spacing: .5px;
        margin-bottom: 16px;
    }
    .badge-count {
        margin-left: auto;
        background: rgba(224, 82, 82, .15);
        color: #e05252;
        font-size: 11px;
        font-weight: 600;
        padding: 2px 10px;
        border-radius: 20px;
        text-transform: none;
    }

    /* ── Empty State ── */
    .empty-state {
        text-align: center;
        padding: 48px 20px;
        color: #666;
    }
    .empty-icon {
        font-size: 48px;
        opacity: 0.2;
        display: block;
        margin-bottom: 10px;
    }
    .empty-state p {
        font-size: 13px;
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
            }, 0);
        });
    });
</script>
@endpush