<div class="mat-card" style="padding-top:28px">

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
                <div wire:ignore class="input-group input-group-outline">
                    <label class="form-label">Class <span class="req">*</span></label>
                    <select wire:model.live="class_id" class="form-select">
                        <option value="">Select Class</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}">{{ $class->name }}</option>
                        @endforeach
                    </select>
                </div>
                @error('class_id') <span class="text-danger small">{{ $message }}</span> @enderror
            </div>

            {{-- Section --}}
            <div class="col-md-4">
                <div class="input-group input-group-outline">
                    <label class="form-label">Section <span class="req">*</span></label>
                    <select wire:model.live="section_id"
                            class="form-select"
                            @if(!$class_id) disabled @endif>
                        <option value="">Select Section</option>
                        @if ($this->class_id)
                            <option value="all">All Section</option>
                        @endif
                        @foreach($sections as $section)
                            <option value="{{ $section['id'] }}">{{ $section['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                @error('section_id') <span class="text-danger small">{{ $message }}</span> @enderror
            </div>

            {{-- Fee Group --}}
            <div class="col-md-4">
                <div wire:ignore class="input-group input-group-outline">
                    <label class="form-label">Fee Group <span class="req">*</span></label>
                    <select wire:model="fee_group_id" class="form-select">
                        <option value="">Select Group</option>
                        @foreach($feeGroups as $group)
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
    @if($hasFiltered)
    <div class="form-section">
        <div class="section-heading">
            <span class="material-icons-round">format_list_bulleted</span> Student List
        </div>

        @if(count($students) > 0)
        <div class="table-responsive mt-3">
            <table class="table-loader">
                <thead>
                    <tr>
                        <th style="width:48px">
                            <input type="checkbox" class="alloc-checkbox" wire:model.live="selectAll">
                        </th>
                        <th>Sl</th>
                        <th>Name</th>
                        <th>Register No</th>
                        <th>Roll No</th>
                        <th>Gender</th>
                        <th>Mobile No</th>
                        <th>Email</th>
                        <th>Guardian Name</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($students as $i => $student)
                    <tr wire:key="student-{{ $student['id'] }}"
                        class="{{ in_array($student['id'], $selectedStudents) ? 'row-selected' : '' }}">
                        <td>
                            <input type="checkbox" class="alloc-checkbox"
                                   wire:model.live="selectedStudents"
                                   value="{{ $student['id'] }}">
                        </td>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $student['name'] }}</td>
                        <td>{{ $student['student_id'] ?? '—' }}</td>
                        <td>{{ $student['roll_no'] ?? '—' }}</td>
                        <td>{{ $student['gender'] ?? '—' }}</td>
                        <td>{{ $student['mobile'] ?? '—' }}</td>
                        <td>{{ $student['email'] ?? '—' }}</td>
                        <td>{{ collect($student['guardians'])->pluck('name')->join(', ') ?: '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Footer --}}
        <div class="form-footer">
            <div class="text-muted small">
                <span class="material-icons-round" style="font-size:15px;vertical-align:middle">people</span>
                {{ count($selectedStudents) }} of {{ count($students) }} selected
            </div>
            <div class="d-flex gap-2">
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
        </div>

        @else
        <div class="text-center py-5 text-muted">
            <i class="bi bi-inbox display-5 d-block mb-2 opacity-25"></i>
            No students found for selected class/section.
        </div>
        @endif
    </div>
    @endif

</div>

@push('styles')
<style>
    .table-loader {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }
    .table-loader thead th {
        padding: 10px;
        text-align: left;
        font-weight: 600;
        font-size: 12px;
        white-space: nowrap;
    }
    .table-loader tbody td {
        padding: 8px 10px;
        vertical-align: middle;
        font-size: 13px;
    }
    .table-loader tbody tr {
        transition: background .15s;
    }
    .table-loader tbody tr:hover {
        background: rgba(255,255,255,.03);
    }
    .row-selected {
        background: rgba(224, 82, 82, .08) !important;
    }
    .alloc-checkbox {
        width: 16px;
        height: 16px;
        cursor: pointer;
        accent-color: #e05252;
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
        width: 180px;
        cursor: pointer;
    }
    .schedule-date::-webkit-calendar-picker-indicator {
        filter: invert(0.6);
        cursor: pointer;
    }
    .form-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-top: 20px;
        margin-top: 16px;
        flex-wrap: wrap;
        gap: 12px;
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