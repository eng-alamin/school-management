<div>
    <div class="card">

        {{-- Header --}}
        <div class="mat-card-header header-primary-gradient">
            <h5>My Result</h5>
            <p>View your exam results, GPA and subject-wise marks</p>
        </div>

        {{-- Select Ground --}}
        <div class="form-section no-print" style="padding-top:40px; padding-bottom:20px">
            <div class="section-heading">
                <span class="material-icons-round">school</span> Select Exam
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
                                    @if ($item->term) — {{ $item->term->name }} @endif
                                    @if ($item->type) ({{ $item->type->name }}) @endif
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
                            class="btn-primary d-flex align-items-center gap-1"
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

        {{-- Result --}}
        @if ($hasResults && $selectedPosition)
            @php
                $student = auth()->user()->student;

                $resultBadgeClass = [
                    'pass'       => 'badge-pass',
                    'fail'       => 'badge-fail',
                    'absent'     => 'badge-fail',
                    'incomplete' => 'badge-incomplete',
                ];
            @endphp

            <div id="myResultPrintable">

                {{-- Summary --}}
                <div class="form-section avoid-break">
                    <div class="section-heading">
                        <span class="material-icons-round">summarize</span>
                        Result Summary : {{ $exams->firstWhere('id', $exam_setup_id)->name ?? '' }}
                    </div>

                    <div class="row g-4 mt-1">
                        <div class="col-sm-4">
                            <small class="text-muted d-block">Student Name</small>
                            <div class="fw-semibold">{{ $student?->name }}</div>
                        </div>
                        <div class="col-sm-2">
                            <small class="text-muted d-block">Roll</small>
                            <div class="fw-semibold">{{ $student?->roll_no ?? '-' }}</div>
                        </div>
                        <div class="col-sm-3">
                            <small class="text-muted d-block">Registration No</small>
                            <div class="fw-semibold">{{ $student?->registration_no ?? '-' }}</div>
                        </div>
                        <div class="col-sm-3">
                            <small class="text-muted d-block">Class</small>
                            <div class="fw-semibold">
                                {{ $student?->academicClass?->name ?? '-' }}
                                @if ($student?->academicSection?->name)
                                    ({{ $student->academicSection->name }})
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive mt-3 result-table-wrap">
                        <table class="result-table">
                            <thead>
                                <tr>
                                    <th>Result</th>
                                    @if (in_array($displayMode, ['grade', 'both']))
                                        <th>Grade</th>
                                        <th>GPA</th>
                                    @endif
                                    @if (in_array($displayMode, ['mark', 'both']))
                                        <th>Obtained Marks</th>
                                        <th>Percentage</th>
                                    @endif
                                    <th>Position</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <span class="badge-pill {{ $resultBadgeClass[$selectedPosition->result] ?? 'badge-incomplete' }}">
                                            {{ strtoupper($selectedPosition->result) }}
                                        </span>
                                    </td>

                                    @if (in_array($displayMode, ['grade', 'both']))
                                        <td>
                                            {{ $selectedPosition->grade ?? '-' }}
                                            @if ($remark = $this->gradeRemark($selectedPosition->grade))
                                                <br><small class="text-muted">{{ $remark }}</small>
                                            @endif
                                        </td>
                                        <td>{{ $selectedPosition->gpa !== null ? number_format($selectedPosition->gpa, 2) : '-' }}</td>
                                    @endif

                                    @if (in_array($displayMode, ['mark', 'both']))
                                        <td>
                                            {{ rtrim(rtrim(number_format($selectedPosition->total_obtained, 2), '0'), '.') }}
                                            / {{ rtrim(rtrim(number_format($selectedPosition->total_full_mark, 2), '0'), '.') }}
                                        </td>
                                        <td>{{ number_format($selectedPosition->percentage, 2) }}%</td>
                                    @endif

                                    <td>
                                        {{ $selectedPosition->position ?? '-' }}
                                        @if ($selectedPosition->position)
                                            <small class="text-muted text-capitalize d-block">({{ $selectedPosition->rank_scope }})</small>
                                        @endif
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    @if ($selectedPosition->teacher_comment || $selectedPosition->principal_comment)
                        <div class="row g-3 mt-1">
                            @if ($selectedPosition->teacher_comment)
                                <div class="col-md-6">
                                    <small class="text-muted d-block">Teacher Comment</small>
                                    <div>{{ $selectedPosition->teacher_comment }}</div>
                                </div>
                            @endif
                            @if ($selectedPosition->principal_comment)
                                <div class="col-md-6">
                                    <small class="text-muted d-block">Principal Comment</small>
                                    <div>{{ $selectedPosition->principal_comment }}</div>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- Subject-wise marks --}}
                <div class="form-section avoid-break">
                    <div class="section-heading">
                        <span class="material-icons-round">menu_book</span>
                        Subject-wise Marks
                    </div>

                    <div class="table-responsive mt-3 result-table-wrap">
                        <table class="result-table">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Subject</th>
                                    @if (in_array($displayMode, ['mark', 'both']))
                                        <th>Full Mark</th>
                                        <th>Obtained</th>
                                    @endif
                                    @if (in_array($displayMode, ['grade', 'both']))
                                        <th>Grade</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($entries as $entry)
                                    @php
                                        $subject      = $entry->examSetupDetail?->classAssignDetail?->subject;
                                        $entryGrade   = $this->subjectGrade($entry);
                                    @endphp
                                    <tr wire:key="exam-entry-{{ $entry->id }}">
                                        <td>{{ $subject?->code ?? '-' }}</td>
                                        <td>{{ $subject?->name ?? '-' }}</td>

                                        @if (in_array($displayMode, ['mark', 'both']))
                                            <td>
                                                {{ rtrim(rtrim(number_format($entry->examSetupDetail?->full_mark ?? 0, 2), '0'), '.') }}
                                            </td>
                                            <td>
                                                @if ($entry->is_absent)
                                                    <span class="badge-pill badge-fail">ABSENT</span>
                                                @else
                                                    {{ rtrim(rtrim(number_format($entry->total_obtained ?? 0, 2), '0'), '.') }}
                                                @endif
                                            </td>
                                        @endif

                                        @if (in_array($displayMode, ['grade', 'both']))
                                            <td>
                                                @if ($entry->is_absent && $displayMode === 'grade')
                                                    <span class="badge-pill badge-fail">ABSENT</span>
                                                @else
                                                    {{ $entryGrade ?? '-' }}
                                                @endif
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            No subject marks found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Action Toolbar --}}
                    <div class="d-flex justify-content-end gap-2 my-3 no-print">
                        <button type="button" class="btn btn-dark btn-sm" onclick="printMyResult()">
                            <span class="material-icons-round align-middle" style="font-size:1rem;">print</span>
                            Print
                        </button>
                    </div>

                </div>

            </div>

        @elseif ($hasResults)
            <div class="form-section text-center text-muted py-4">
                No published result found for the selected exam.
            </div>
        @endif

    </div>
</div>

@push('styles')
<style>
    /* ── Table wrapper (matches PositionComponent design system) ── */
    .result-table-wrap {
        border: 1px solid rgba(0,0,0,.06);
        border-radius: 12px;
        overflow: hidden;
    }

    .result-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }
    .result-table thead th {
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
    .result-table tbody tr {
        border-bottom: 1px solid rgba(0,0,0,.04);
        transition: background .15s ease;
    }
    .result-table tbody tr:hover {
        background: rgba(224, 82, 82, .035);
    }
    .result-table tbody tr:last-child {
        border-bottom: none;
    }
    .result-table tbody td {
        padding: 10px;
        vertical-align: middle;
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
    .badge-pass       { background: #16a34a; }
    .badge-fail       { background: #ef4444; }
    .badge-incomplete { background: #f59e0b; }

    /* ── Dark mode support ── */
    [data-bs-theme="dark"] .result-table-wrap {
        border-color: rgba(255,255,255,.08);
    }
    [data-bs-theme="dark"] .result-table thead th {
        background: rgba(224, 82, 82, .08);
        border-bottom-color: rgba(255,255,255,.08);
    }
    [data-bs-theme="dark"] .result-table tbody tr {
        border-bottom-color: rgba(255,255,255,.05);
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

    function printMyResult() {
        const printableEl = document.getElementById('myResultPrintable');

        if (!printableEl) {
            return;
        }

        const printContent = printableEl.innerHTML;
        const printWindow = window.open('', '_blank', 'width=900,height=650');

        if (!printWindow) {
            alert('Print window block hoye গেছে। Browser-er popup blocker check korun.');
            return;
        }

        printWindow.document.write(`
            <html>
                <head>
                    <title>My Result</title>
                    <style>
                        * { box-sizing: border-box; }
                        body { font-family: Arial, Helvetica, sans-serif; padding: 28px; color: #222; }
                        .no-print { display: none !important; }

                        .row { display: flex; flex-wrap: wrap; margin: 0 -8px; }
                        .col-12 { width: 100%; padding: 6px 8px; }
                        .col-sm-2 { width: 16.666%; padding: 6px 8px; }
                        .col-sm-3 { width: 25%; padding: 6px 8px; }
                        .col-sm-4 { width: 33.333%; padding: 6px 8px; }
                        .col-md-6 { width: 50%; padding: 6px 8px; }

                        .d-flex { display: flex; }
                        .flex-wrap { flex-wrap: wrap; }
                        .align-items-center { align-items: center; }
                        .justify-content-between { justify-content: space-between; }
                        .gap-1 { gap: 4px; }
                        .gap-2 { gap: 8px; }
                        .gap-3 { gap: 12px; }
                        .gap-4 { gap: 16px; }
                        .mt-1 { margin-top: 4px; }
                        .mt-3 { margin-top: 12px; }
                        .mb-1 { margin-bottom: 4px; }

                        .text-muted { color: #6c757d !important; }
                        .fw-semibold { font-weight: 600; }
                        .small { font-size: .8rem; }
                        .d-block { display: block; }
                        .text-capitalize { text-transform: capitalize; }
                        .text-center { text-align: center; }

                        .form-section { margin-bottom: 18px; }
                        .section-heading {
                            font-weight: 700; font-size: 1rem; margin-bottom: 12px;
                            display: flex; align-items: center; gap: 6px;
                            border-bottom: 1px solid #eee; padding-bottom: 8px;
                        }

                        table.result-table { width: 100%; border-collapse: collapse; font-size: 13px; margin-bottom: 8px; }
                        .result-table thead th {
                            padding: 8px 6px; text-align: left; font-weight: 700;
                            font-size: 11px; text-transform: uppercase; border-bottom: 1px solid #ddd;
                        }
                        .result-table tbody td { padding: 8px 6px; border-bottom: 1px solid #eee; }

                        .badge-pill { display: inline-block; padding: 3px 10px; border-radius: 12px;
                                      font-size: 11px; font-weight: 700; color: #fff; }
                        .badge-pass       { background: #16a34a; }
                        .badge-fail       { background: #ef4444; }
                        .badge-incomplete { background: #f59e0b; }

                        .material-icons-round, .bi { display: none; } /* icon fonts aren't loaded here */

                        .avoid-break { break-inside: avoid; page-break-inside: avoid; }
                    </style>
                </head>
                <body>
                    ${printContent}
                </body>
            </html>
        `);

        printWindow.document.close();
        printWindow.focus();

        setTimeout(() => {
            printWindow.print();
            printWindow.close();
        }, 250);
    }
</script>
@endpush