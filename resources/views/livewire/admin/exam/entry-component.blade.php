<div class="mat-card" style="padding-top:28px">

    {{-- Header --}}
    <div class="mat-card-header header-pink-gradient">
        <h5>
            Mark Entries
        </h5>
        <p>Enter or update student marks for an exam subject</p>
    </div>

    {{-- Select Ground --}}
    <div class="form-section" style="padding-top:40px; padding-bottom:20px">
        <div class="section-heading">
            <span class="material-icons-round">tune</span> Select Ground
        </div>
        <div class="row g-4">

            {{-- Exam --}}
            <div class="col-md-6">
                <div class="input-group input-group-outline">
                    <label class="form-label">Exam</label>
                    <select wire:model.live="exam_setup_id" class="form-select">
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
                @error('exam_setup_id') <span class="text-danger small">{{ $message }}</span> @enderror
            </div>

            {{-- Subject --}}
            <div class="col-md-6">
                <div class="input-group input-group-outline">
                    <label class="form-label">Subject</label>
                    <select wire:model.live="exam_setup_detail_id" class="form-select" {{ !$exam_setup_id ? 'disabled' : '' }}>
                        <option value="">{{ !$exam_setup_id ? 'Select Exam First' : 'Select Subject' }}</option>
                        @foreach ($subjects as $item)
                            <option value="{{ $item->id }}">{{ $item->classAssignDetail->subject->name ?? '—' }}</option>
                        @endforeach
                    </select>
                </div>
                @error('exam_setup_detail_id') <span class="text-danger small">{{ $message }}</span> @enderror
            </div>

            {{-- Filter Button --}}
             <div class="col-md-12 d-flex justify-content-end">
                <button wire:click="filter"
                        wire:loading.attr="disabled"
                        wire:target="filter"
                        class="btn-pink d-flex align-items-center gap-1"
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

    {{-- Mark Entries Table --}}
    @if($hasResults)
    <div class="form-section">
        <div class="section-heading d-flex align-items-center flex-wrap gap-2">
            <span class="material-icons-round">groups</span> Mark Entries

            <span class="mark-meta-badge mark-meta-badge-full">
                <span class="material-icons-round" style="font-size:13px">military_tech</span>
                Full: {{ $selectedDetail->full_mark }}
            </span>
            <span class="mark-meta-badge mark-meta-badge-pass">
                <span class="material-icons-round" style="font-size:13px">check_circle</span>
                Pass: {{ $selectedDetail->pass_mark }}
            </span>
            <span class="mark-meta-badge mark-meta-badge-count ms-auto">
                {{ count($students) }} Student{{ count($students) === 1 ? '' : 's' }}
            </span>
        </div>

        <div class="table-responsive mt-3 entry-table-wrap">
            <table class="entry-table">
                <thead>
                    <tr>
                        <th class="col-sl">SL</th>
                        <th class="col-name">Student Name</th>
                        <th>Roll</th>
                        <th>Register No</th>
                        <th>Category</th>
                        <th class="text-center">Is Absent</th>
                        @if($selectedDetail->practical_mark > 0)
                            <th class="text-center">Practical <span class="col-max">({{ $selectedDetail->practical_mark }})</span></th>
                        @endif
                        @if($selectedDetail->written_mark > 0)
                            <th class="text-center">Written <span class="col-max">({{ $selectedDetail->written_mark }})</span></th>
                        @endif
                        @if($selectedDetail->mcq_mark > 0)
                            <th class="text-center">MCQ <span class="col-max">({{ $selectedDetail->mcq_mark }})</span></th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($students as $i => $student)
                    @php $isAbsent = $entries[$student->id]['is_absent'] ?? false; @endphp
                    <tr wire:key="entry-row-{{ $student->id }}" class="{{ $isAbsent ? 'row-absent' : '' }}">
                        <td class="text-muted col-sl">{{ $i + 1 }}</td>
                        <td class="col-name">
                            <div class="student-cell">
                                <span class="student-avatar">{{ strtoupper(substr($student->name, 0, 1)) }}</span>
                                <span class="fw-semibold">{{ $student->name }}</span>
                            </div>
                        </td>
                        <td>{{ $student->roll_no }}</td>
                        <td class="text-muted">{{ $student->registration_no }}</td>
                        <td>
                            <span class="cat-badge">{{ $student->category ?? 'General' }}</span>
                        </td>
                        <td class="text-center">
                            <label class="absent-toggle">
                                <input type="checkbox" wire:model.live="entries.{{ $student->id }}.is_absent">
                                <span class="absent-toggle-slider"></span>
                            </label>
                        </td>

                        @if($selectedDetail->practical_mark > 0)
                        <td class="text-center">
                            <input type="number" min="0" max="{{ $selectedDetail->practical_mark }}"
                                wire:model.defer="entries.{{ $student->id }}.practical_obtained"
                                class="entry-input" {{ $isAbsent ? 'disabled' : '' }}
                                placeholder="0">
                            @error('entries.'.$student->id.'.practical_obtained') <span class="text-danger small d-block">{{ $message }}</span> @enderror
                        </td>
                        @endif

                        @if($selectedDetail->written_mark > 0)
                        <td class="text-center">
                            <input type="number" min="0" max="{{ $selectedDetail->written_mark }}"
                                wire:model.defer="entries.{{ $student->id }}.written_obtained"
                                class="entry-input" {{ $isAbsent ? 'disabled' : '' }}
                                placeholder="0">
                            @error('entries.'.$student->id.'.written_obtained') <span class="text-danger small d-block">{{ $message }}</span> @enderror
                        </td>
                        @endif

                        @if($selectedDetail->mcq_mark > 0)
                        <td class="text-center">
                            <input type="number" min="0" max="{{ $selectedDetail->mcq_mark }}"
                                wire:model.defer="entries.{{ $student->id }}.mcq_obtained"
                                class="entry-input" {{ $isAbsent ? 'disabled' : '' }}
                                placeholder="0">
                            @error('entries.'.$student->id.'.mcq_obtained') <span class="text-danger small d-block">{{ $message }}</span> @enderror
                        </td>
                        @endif

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
                <span class="material-icons-round">save</span> Save Marks
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
    /* ── Meta badges (Full / Pass / Count) ── */
    .mark-meta-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 11.5px;
        font-weight: 600;
        letter-spacing: .2px;
    }
    .mark-meta-badge-full  { background: rgba(94, 129, 244, .12); color: #5e81f4; }
    .mark-meta-badge-pass  { background: rgba(34, 197, 94, .12);  color: #16a34a; }
    .mark-meta-badge-count { background: rgba(0,0,0,.05); color: var(--muted, #888); }

    /* ── Table wrapper ── */
    .entry-table-wrap {
        border: 1px solid rgba(0,0,0,.06);
        border-radius: 12px;
        overflow: hidden;
    }

    .entry-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }
    .entry-table thead th {
        position: sticky;
        top: 0;
        z-index: 2;
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
    .entry-table thead th .col-max {
        font-weight: 400;
        text-transform: none;
        opacity: .7;
        letter-spacing: 0;
    }
    .entry-table thead th.col-sl { width: 46px; }
    .entry-table thead th.col-name { min-width: 180px; }

    .entry-table tbody tr {
        border-bottom: 1px solid rgba(0,0,0,.04);
        transition: background .15s ease;
    }
    .entry-table tbody tr:hover {
        background: rgba(224, 82, 82, .035);
    }
    .entry-table tbody tr:last-child {
        border-bottom: none;
    }
    .entry-table tbody td {
        padding: 9px 10px;
        vertical-align: middle;
    }

    /* ── Absent row highlighting ── */
    .entry-table tbody tr.row-absent {
        background: rgba(239, 68, 68, .045);
    }
    .entry-table tbody tr.row-absent:hover {
        background: rgba(239, 68, 68, .07);
    }

    /* ── Student name cell ── */
    .student-cell {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .student-avatar {
        flex-shrink: 0;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #e05252, #f28b8b);
        color: #fff;
        font-size: 11.5px;
        font-weight: 700;
    }

    /* ── Category badge ── */
    .cat-badge {
        display: inline-block;
        padding: 2px 10px;
        border-radius: 10px;
        font-size: 11px;
        font-weight: 600;
        background: rgba(0,0,0,.05);
        color: #666;
    }

    /* ── Absent toggle switch ── */
    .absent-toggle {
        position: relative;
        display: inline-block;
        width: 38px;
        height: 21px;
        vertical-align: middle;
    }
    .absent-toggle input {
        opacity: 0;
        width: 0;
        height: 0;
    }
    .absent-toggle-slider {
        position: absolute;
        cursor: pointer;
        inset: 0;
        background-color: rgba(0,0,0,.15);
        border-radius: 999px;
        transition: background-color .2s ease;
    }
    .absent-toggle-slider::before {
        content: "";
        position: absolute;
        height: 15px;
        width: 15px;
        left: 3px;
        bottom: 3px;
        background-color: #fff;
        border-radius: 50%;
        transition: transform .2s ease;
        box-shadow: 0 1px 3px rgba(0,0,0,.25);
    }
    .absent-toggle input:checked + .absent-toggle-slider {
        background-color: #ef4444;
    }
    .absent-toggle input:checked + .absent-toggle-slider::before {
        transform: translateX(17px);
    }

    /* ── Entry input ── */
    .entry-input {
        border: 1px solid rgba(0,0,0,.12);
        padding: 7px 10px;
        border-radius: 6px;
        font-size: 12.5px;
        font-weight: 600;
        outline: none;
        width: 84px;
        text-align: center;
        background: #fff;
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    .entry-input:hover:not(:disabled) {
        border-color: rgba(224, 82, 82, .4);
    }
    .entry-input:focus {
        border-color: #e05252;
        box-shadow: 0 0 0 3px rgba(224, 82, 82, .12);
    }
    .entry-input:disabled {
        opacity: .35;
        cursor: not-allowed;
        background: rgba(0,0,0,.03);
    }

    /* ── Dark mode support ── */
    [data-bs-theme="dark"] .entry-table-wrap {
        border-color: rgba(255,255,255,.08);
    }
    [data-bs-theme="dark"] .entry-table thead th {
        background: rgba(224, 82, 82, .08);
        border-bottom-color: rgba(255,255,255,.08);
    }
    [data-bs-theme="dark"] .entry-table tbody tr {
        border-bottom-color: rgba(255,255,255,.05);
    }
    [data-bs-theme="dark"] .entry-table tbody tr.row-absent {
        background: rgba(239, 68, 68, .08);
    }
    [data-bs-theme="dark"] .cat-badge {
        background: rgba(255,255,255,.08);
        color: #ccc;
    }
    [data-bs-theme="dark"] .entry-input {
        background: rgba(255,255,255,.03);
        border-color: rgba(255,255,255,.14);
        color: #eee;
    }
    [data-bs-theme="dark"] .absent-toggle-slider {
        background-color: rgba(255,255,255,.18);
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