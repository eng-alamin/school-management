<div>
    <div class="card">

        <div class="mat-card-header header-primary-gradient">
            <h5> Mark Entries</h5>
            <p>Enter or update student marks for an exam subject</p>
        </div>

        {{-- Select Ground --}}
        <div class="form-section" style="padding-top:40px; padding-bottom:20px">
            <div class="section-heading">
                <span class="material-icons-round">school</span> Select Ground
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
                                        — {{ $item->classAssign->academicClass->name ?? '' }}
                                        @if($item->classAssign->academicSection) ({{ $item->classAssign->academicSection->name }}) @endif
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
            <button class="btn btn-primary" type="button"
                    wire:click="save"
                    wire:loading.attr="disabled"
                    wire:target="save">
                <span wire:loading.remove wire:target="save" style="display: inline-flex;align-items: center;gap: 6px">
                    <span class="material-icons-round">save</span> Save
                </span>
                <span wire:loading wire:target="save">
                    <span class="material-icons-round" style="animation:spin .7s linear infinite">sync</span> Saving...
                </span>
            </button>
        </div>
        @endif
    </div>
</div>

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