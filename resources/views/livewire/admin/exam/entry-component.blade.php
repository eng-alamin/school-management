<div class="mat-card" style="padding-top:28px">

    {{-- Header --}}
    <div class="mat-card-header header-pink-gradient">
        <h5><span class="material-icons-round" style="font-size:18px;vertical-align:middle;margin-right:6px">edit_note</span>Mark Entries</h5>
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

    {{-- Mark Entries Table --}}
    @if($hasResults)
    <div class="form-section">
        <div class="section-heading">
            <span class="material-icons-round">groups</span> Mark Entries
            <span class="badge bg-secondary ms-2">
                Full: {{ $selectedDetail->full_mark }} | Pass: {{ $selectedDetail->pass_mark }}
            </span>
        </div>

        <div class="table-responsive mt-3">
            <table class="entry-table">
                <thead>
                    <tr>
                        <th>SL</th>
                        <th>Student Name</th>
                        <th>Roll</th>
                        <th>Register No</th>
                        <th>Category</th>
                        <th>Is Absent</th>
                        @if($selectedDetail->practical_mark > 0)
                            <th>Practical ({{ $selectedDetail->practical_mark }})</th>
                        @endif
                        @if($selectedDetail->written_mark > 0)
                            <th>Written ({{ $selectedDetail->written_mark }})</th>
                        @endif
                        @if($selectedDetail->mcq_mark > 0)
                            <th>MCQ ({{ $selectedDetail->mcq_mark }})</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($students as $i => $student)
                    <tr wire:key="entry-row-{{ $student->id }}">
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $student->name }}</td>
                        <td>{{ $student->roll_no }}</td>
                        <td>{{ $student->register_no }}</td>
                        <td>{{ $student->category ?? 'General' }}</td>
                        <td class="text-center">
                            <input type="checkbox" wire:model.live="entries.{{ $student->id }}.is_absent">
                        </td>

                        @if($selectedDetail->practical_mark > 0)
                        <td>
                            <input type="number" min="0" max="{{ $selectedDetail->practical_mark }}"
                                wire:model.defer="entries.{{ $student->id }}.practical_obtained"
                                class="entry-input" {{ ($entries[$student->id]['is_absent'] ?? false) ? 'disabled' : '' }}>
                            @error('entries.'.$student->id.'.practical_obtained') <span class="text-danger small d-block">{{ $message }}</span> @enderror
                        </td>
                        @endif

                        @if($selectedDetail->written_mark > 0)
                        <td>
                            <input type="number" min="0" max="{{ $selectedDetail->written_mark }}"
                                wire:model.defer="entries.{{ $student->id }}.written_obtained"
                                class="entry-input" {{ ($entries[$student->id]['is_absent'] ?? false) ? 'disabled' : '' }}>
                            @error('entries.'.$student->id.'.written_obtained') <span class="text-danger small d-block">{{ $message }}</span> @enderror
                        </td>
                        @endif

                        @if($selectedDetail->mcq_mark > 0)
                        <td>
                            <input type="number" min="0" max="{{ $selectedDetail->mcq_mark }}"
                                wire:model.defer="entries.{{ $student->id }}.mcq_obtained"
                                class="entry-input" {{ ($entries[$student->id]['is_absent'] ?? false) ? 'disabled' : '' }}>
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
    .entry-input:disabled {
        opacity: .4;
        cursor: not-allowed;
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