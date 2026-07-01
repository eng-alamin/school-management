<div class="mat-card" style="padding-top:28px">

    {{-- Header --}}
    <div class="mat-card-header header-pink-gradient">
        <h5><span class="material-icons-round" style="font-size:18px;vertical-align:middle;margin-right:6px">military_tech</span>Class Position</h5>
        <p>Generate and review student exam rank / position</p>
    </div>

    {{-- Select Ground --}}
    <div class="form-section" style="padding-top:40px; padding-bottom:20px">
        <div class="section-heading">
            <span class="material-icons-round">tune</span> Select Ground
        </div>
        <div class="row g-4">

            {{-- Academic Year --}}
            <div class="col-md-6">
                <div class="input-group input-group-outline">
                    <label class="form-label">Academic Year</label>
                    <select wire:model.live="academic_session_id" class="form-select">
                        <option value="">Select Year</option>
                        @foreach ($academicSessions as $item)
                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                        @endforeach
                    </select>
                </div>
                @error('academic_session_id') <span class="text-danger small">{{ $message }}</span> @enderror
            </div>

            {{-- Exam --}}
            <div class="col-md-6">
                <div class="input-group input-group-outline">
                    <label class="form-label">Exam</label>
                    <select wire:model.live="exam_setup_id" class="form-select" {{ !$academic_session_id ? 'disabled' : '' }}>
                        <option value="">{{ !$academic_session_id ? 'Select Year First' : 'Select Exam' }}</option>
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
                @error('exam_setup_id') <span class="text-danger small">{{ $message }}</span> @enderror
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
                        <span class="material-icons-round" style="font-size:16px;animation:spin .7s linear infinite">sync</span> Loading...
                    </span>
                </button>
            </div>

        </div>
    </div>

    {{-- Rank Table --}}
    @if($hasResults)
    <div class="form-section">
        <div class="section-heading">
            <span class="material-icons-round">groups</span>
            Student Exam Rank : {{ $exams->firstWhere('id', $exam_setup_id)->name ?? '' }}
        </div>

        @if($alreadyGenerated)
        <div class="alert-banner" style="background:#3a1f22;color:#f08a94;padding:14px 18px;border-radius:8px;margin:16px 0;text-align:center;font-size:14px;">
            The position has already been generated.
        </div>
        @endif

        <div class="table-responsive mt-3">
            <table class="entry-table">
                <thead>
                    <tr>
                        <th>Students</th>
                        <th>Register No</th>
                        <th>Class</th>
                        <th>Section</th>
                        <th>Roll</th>
                        <th>Total Marks</th>
                        <th>Percentage</th>
                        <th>Result</th>
                        <th>Previous Position</th>
                        <th>Position <span class="text-danger">*</span></th>
                        <th>Principal Comments</th>
                        <th>Teacher Comments</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rows as $studentId => $row)
                    <tr wire:key="position-row-{{ $studentId }}">
                        <td>{{ $row['student_name'] }}</td>
                        <td>{{ $row['register_no'] }}</td>
                        <td>{{ $row['class_name'] }}</td>
                        <td>{{ $row['section_name'] }}</td>
                        <td>{{ $row['roll_no'] }}</td>
                        <td>
                            {{ rtrim(rtrim(number_format($row['total_obtained'], 2), '0'), '.') }}
                            / {{ rtrim(rtrim(number_format($row['total_full_mark'], 2), '0'), '.') }}
                        </td>
                        <td>{{ number_format($row['percentage'], 2) }} %</td>
                        <td>
                            @if($row['result'] === 'pass')
                                <span class="badge-pill badge-pass">PASS</span>
                            @elseif($row['result'] === 'fail')
                                <span class="badge-pill badge-fail">FAIL</span>
                            @else
                                <span class="text-muted small">All marks not registered</span>
                            @endif
                        </td>
                        <td>{{ $row['previous_position'] ?? '-' }}</td>
                        <td>
                            <input type="number" min="1"
                                wire:model.defer="rows.{{ $studentId }}.position"
                                class="entry-input">
                            @error('rows.'.$studentId.'.position') <span class="text-danger small d-block">{{ $message }}</span> @enderror
                        </td>
                        <td>
                            <input type="text"
                                wire:model.defer="rows.{{ $studentId }}.principal_comment"
                                class="entry-input" style="width:160px">
                        </td>
                        <td>
                            <input type="text"
                                wire:model.defer="rows.{{ $studentId }}.teacher_comment"
                                class="entry-input" style="width:160px">
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Footer --}}
    <div class="form-footer">
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
    .entry-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }
    .entry-table thead th {
        padding: 10px 10px;
        text-align: left;
        font-weight: 600;
        font-size: 12px;
        color: #aaa;
        white-space: nowrap;
    }
    .entry-table tbody td {
        padding: 7px 8px;
        vertical-align: middle;
        white-space: nowrap;
    }
    .entry-input {
        border: 1px solid #3d3d3d;
        padding: 6px 10px;
        border-radius: 4px;
        font-size: 12px;
        outline: none;
        width: 80px;
        text-align: center;
        transition: border-color 0.2s;
    }
    .entry-input:focus {
        border-color: #e05252;
    }
    .badge-pill {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 700;
        color: #fff;
    }
    .badge-pass { background: #e05252; }
    .badge-fail { background: #6c757d; }
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
            }, 0);
        });
    });
</script>
@endpush