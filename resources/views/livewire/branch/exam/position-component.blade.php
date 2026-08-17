<div>
    <div class="card">

        {{-- Header --}}
        <div class="mat-card-header header-primary-gradient">
            <h5>Class Position</h5>
            <p>Generate and review student exam rank / position</p>
        </div>

        {{-- Select Ground --}}
        <div class="form-section" style="padding-top:40px; padding-bottom:20px">
            <div class="section-heading">
                <span class="material-icons-round">school</span> Select Ground
            </div>
            <div class="row g-4">

                {{-- Academic Year --}}
                <div class="col-md-6">
                    <div class="input-group input-group-outline">
                        <label class="form-label">Academic Year</label>
                        <select wire:model.live="academic_session_id" class="form-select">
                            <option value="">Select Year</option>
                            @foreach ($academicSessions as $item)
                                <option value="{{ $item->id }}" @if($item->is_current == true) selected @endif>{{ $item->name }}</option>
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
                                        — {{ $item->classAssign->academicClass->name ?? '' }}
                                        @if($item->classAssign->academicSection) ({{ $item->classAssign->academicSection->name }}) @endif
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @error('exam_setup_id') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>
                {{-- Filter Button --}}
                <div class="col-md-12 d-flex justify-content-end">
                    <button wire:click="filter"
                            wire:loading.attr="disabled"
                            wire:target="filter"
                            class="btn btn-primary d-flex align-items-center gap-1"
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

        {{-- Rank Table --}}
        @if($hasResults)
        <div class="form-section">
            <div class="section-heading">
                <span class="material-icons-round">groups</span>
                Student Exam Rank : {{ $exams->firstWhere('id', $exam_setup_id)->name ?? '' }}
            </div>

            <div class="table-responsive mt-3 position-table-wrap">
                <table class="position-table">
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
                            @if ($displayMode !== 'mark')
                                <th>GPA</th>
                                <th>Grade</th>
                            @endif
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
                            <td>{{ $row['registration_no'] }}</td>
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
                            @if ($displayMode !== 'mark')
                                <td>{{ $row['gpa'] !== null ? number_format($row['gpa'], 2) : '-' }}</td>
                                <td>{{ $row['grade'] ?? '-' }}</td>
                            @endif
                            <td>{{ $row['previous_position'] ?? '-' }}</td>
                            <td>
                                <input type="number" min="1"
                                    wire:model.defer="rows.{{ $studentId }}.position"
                                    class="position-input">
                                @error('rows.'.$studentId.'.position') <span class="text-danger small d-block">{{ $message }}</span> @enderror
                            </td>
                            <td>
                                <input type="text"
                                    wire:model.defer="rows.{{ $studentId }}.principal_comment"
                                    class="position-input" style="width:160px;text-align:left">
                            </td>
                            <td>
                                <input type="text"
                                    wire:model.defer="rows.{{ $studentId }}.teacher_comment"
                                    class="position-input" style="width:160px;text-align:left">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Footer --}}
        <div class="form-footer">
            <button class="btn btn-primary" type="button"
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
</div>

@push('styles')
<style>
    /* ── Table wrapper (matches EntryComponent design system) ── */
    .position-table-wrap {
        border: 1px solid rgba(0,0,0,.06);
        border-radius: 12px;
        overflow: hidden;
    }

    .position-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }
    .position-table thead th {
        padding: 12px 10px;
        text-align: left;
        font-weight: 700;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .4px;
        color: var(--muted, #999);
        white-space: nowrap;
        border-bottom: 1px solid rgba(0,0,0,.06);
    }
    .position-table tbody tr {
        border-bottom: 1px solid rgba(0,0,0,.04);
        transition: background .15s ease;
    }
    .position-table tbody tr:hover {
        background: rgba(224, 82, 82, .035);
    }
    .position-table tbody tr:last-child {
        border-bottom: none;
    }
    .position-table tbody td {
        padding: 8px 10px;
        vertical-align: middle;
        white-space: nowrap;
    }

    /* ── Position / comment inputs ── */
    .position-input {
        border: 1px solid rgba(0,0,0,.12);
        padding: 7px 10px;
        border-radius: 6px;
        font-size: 12.5px;
        font-weight: 600;
        outline: none;
        width: 80px;
        text-align: center;
        background: #fff;
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    .position-input:hover {
        border-color: rgba(224, 82, 82, .4);
    }
    .position-input:focus {
        border-color: #e05252;
        box-shadow: 0 0 0 3px rgba(224, 82, 82, .12);
    }

    /* ── Result badges ── */
    .badge-pill {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        color: #fff;
    }
    .badge-pass { background: #16a34a; }
    .badge-fail { background: #ef4444; }

    /* ── Dark mode support ── */
    [data-bs-theme="dark"] .position-table-wrap {
        border-color: rgba(255,255,255,.08);
    }
    [data-bs-theme="dark"] .position-table thead th {
        background: rgba(224, 82, 82, .08);
        border-bottom-color: rgba(255,255,255,.08);
    }
    [data-bs-theme="dark"] .position-table tbody tr {
        border-bottom-color: rgba(255,255,255,.05);
    }
    [data-bs-theme="dark"] .position-input {
        background: rgba(255,255,255,.03);
        border-color: rgba(255,255,255,.14);
        color: #eee;
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
            }, 0);
        });
    });
</script>
@endpush