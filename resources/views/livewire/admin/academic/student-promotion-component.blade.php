<div>
    <div class="mat-card" style="padding-top:28px">

        <div class="mat-card-header header-pink-gradient">
            <h5>Student Promotion</h5>
            <p>Promote students to the next session/class</p>
        </div>

        {{-- ===== SELECT GROUND ===== --}}
        <div class="form-section" style="padding:32px 28px 20px">
            <div class="section-heading" style="font-weight:700;font-size:.85rem;margin-bottom:16px;padding-bottom:8px;border-bottom:2px solid var(--pink,#e91e63)">
                Select Ground
            </div>
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="input-group input-group-outline">
                        <label class="form-label">Class <span class="req">*</span></label>
                        <select wire:model.live="class_id" class="form-select">
                            <option value="">Select Class</option>
                            @foreach($classes as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error('class_id') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-6">
                    <div class="input-group input-group-outline">
                        <label class="form-label">Section</label>
                        <select wire:model="section_id" class="form-select" @disabled(empty($availableSections))>
                            <option value="">{{ empty($availableSections) ? 'Select class first' : 'All Section' }}</option>
                            @if(!empty($availableSections) && $availableSections->count())
                                <option value="all">All Section</option>
                                @foreach($availableSections as $s)
                                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    @error('section_id') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-12 d-flex justify-content-end">
                    <button wire:click="filter" class="btn-pink d-flex align-items-center gap-1" type="button" wire:loading.attr="disabled">
                        <span wire:loading wire:target="filter" class="spinner-border spinner-border-sm"></span>
                        <span class="material-icons-round" style="font-size:16px">filter_alt</span> Filter
                    </button>
                </div>
            </div>
        </div>

        @if($hasStudents)

        {{-- ===== NOTICE ===== --}}
        <div style="margin:0 28px 20px;padding:16px 20px;background:#fff8e1;border-left:4px solid #f59e0b;border-radius:6px">
            <div style="font-weight:700;font-size:.82rem;margin-bottom:8px">Instructions :</div>
            <ol style="font-size:.78rem;color:#555;margin:0;padding-left:18px;line-height:1.8">
                <li>The Roll field shows the previous roll and you can manually add new roll for promoted session.</li>
                <li>Roll number is unique, so carefully enter the roll number. Automatically generate a roll when your entered roll exists.</li>
                <li>Please double check and Fill-up all fields carefully Then click Promotion button.</li>
                <li>If you Unchecked "Carry Forward Due in Next Session" the due fees will not be transferred to the next session.</li>
                <li>If you select "Running" in the class section, only the session of that student will change and will exist in the running class.</li>
                <li>If you want to add a student to the alumni list, then "Leave / Add Alumni" status should be checked.</li>
            </ol>
        </div>

        {{-- ===== PROMOTION SETTINGS ===== --}}
        <div style="margin:0 28px 20px">

            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" wire:model="carryForwardDue" id="carryForward">
                <label class="form-check-label" for="carryForward" style="font-size:.82rem;font-weight:600">
                    Carry Forward Due in Next Session
                </label>
            </div>

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label" style="font-size:.78rem;font-weight:600">Promote To Session <span class="text-danger">*</span></label>
                    <select class="form-select form-select-sm @error('to_session_id') is-invalid @enderror" wire:model="to_session_id">
                        <option value="">Select Session</option>
                        @foreach($sessions as $session)
                            <option value="{{ $session->id }}">{{ $session->name }}</option>
                        @endforeach
                    </select>
                    @error('to_session_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label" style="font-size:.78rem;font-weight:600">Promote To Class <span class="text-danger">*</span></label>
                    <select class="form-select form-select-sm @error('to_class_id') is-invalid @enderror" wire:model.live="to_class_id">
                        <option value="">Select Class</option>
                        @foreach($classes as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                    @error('to_class_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label" style="font-size:.78rem;font-weight:600">Promote To Section</label>
                    <select class="form-select form-select-sm @error('to_section_id') is-invalid @enderror" wire:model="to_section_id" @disabled(empty($toAvailableSections))>
                        <option value="">{{ empty($toAvailableSections) ? 'Select class first' : 'All Section' }}</option>
                        @if(!empty($toAvailableSections) && $toAvailableSections->count())
                            <option value="all">All Section</option>
                            @foreach($toAvailableSections as $s)
                                <option value="{{ $s->id }}">{{ $s->name }}</option>
                            @endforeach
                        @endif
                    </select>
                    @error('to_section_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        {{-- ===== STUDENTS TABLE ===== --}}
        <div style="margin:0 28px 28px;overflow-x:auto">
            <table class="table table-hover mb-0" style="font-size:.78rem;min-width:900px">
                <thead style="background:#1a1a1a;color:#fff">
                    <tr>
                        <th style="width:40px">
                            <input type="checkbox" wire:model.live="selectAll" class="form-check-input">
                        </th>
                        <th id="th-sl">SL</th>
                        <th id="th-student-name">Student Name</th>
                        <th id="th-registration-no">Registration No</th>
                        <th id="th-guardian-name">Guardian Name</th>
                        <th id="th-class">Class</th>
                        <th id="th-role">Roll</th>
                        <th id="th-current-due-amount-with-fine">Current Due Amount (With Fine)</th>
                        <th id="th-status">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($students as $studentId => $student)
                    <tr wire:key="promote-row-{{ $studentId }}">
                        <td>
                            <input type="checkbox"
                                class="form-check-input"
                                value="{{ $studentId }}"
                                wire:model.live="selectedStudents">
                        </td>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $student['name'] }}</td>
                        <td>{{ $student['registration_no'] }}</td>
                        <td>{{ $student['guardian_name'] }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <label class="d-flex align-items-center gap-1" style="cursor:pointer;font-size:.75rem">
                                    <input type="radio"
                                        wire:model="students.{{ $studentId }}.status"
                                        value="running"> Running
                                </label>
                                <label class="d-flex align-items-center gap-1" style="cursor:pointer;font-size:.75rem">
                                    <input type="radio"
                                        wire:model="students.{{ $studentId }}.status"
                                        value="promoted">
                                    <span style="width:8px;height:8px;border-radius:50%;background:#22c55e;display:inline-block"></span>
                                    Promoted
                                </label>
                            </div>
                        </td>
                        <td>
                            <input type="text"
                                class="form-control form-control-sm"
                                wire:model.defer="students.{{ $studentId }}.roll"
                                style="width:80px">
                        </td>
                        <td>{{ $student['due_amount'] }}</td>
                        <td>
                            <div class="form-check d-flex align-items-center gap-1">
                                <input class="form-check-input" type="checkbox"
                                    wire:model="students.{{ $studentId }}.is_alumni"
                                    id="alumni_{{ $studentId }}">
                                <label class="form-check-label" for="alumni_{{ $studentId }}" style="font-size:.72rem;white-space:nowrap">
                                    Leave / Add Alumni
                                </label>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- ===== FOOTER ===== --}}
        <div class="form-footer d-flex justify-content-end" style="padding:16px 28px;border-top:1px solid var(--border)">
            <button class="btn bg-dark text-white d-flex align-items-center gap-1"
                wire:click="promote"
                wire:loading.attr="disabled"
                wire:target="promote">
                <span wire:loading wire:target="promote" class="spinner-border spinner-border-sm"></span>
                <span class="material-icons-round" style="font-size:16px">verified</span>
                Promotion
            </button>
        </div>

        @endif

    </div>
</div>

@push('styles')
<style>
    .form-section { padding: 20px 28px; }
    .section-heading { font-weight: 700; font-size: .85rem; margin-bottom: 16px; padding-bottom: 8px; border-bottom: 2px solid #e91e63; display: flex; align-items: center; gap: 8px; }
    .form-footer { background: #fff; border-top: 1px solid var(--border); padding: 16px 28px; }
    .form-check-input:checked { background-color: #212529; border-color: #212529; }
    thead th { font-size: .72rem; font-weight: 600; letter-spacing: .03em; padding: 10px 12px; }
    tbody td { padding: 8px 12px; vertical-align: middle; }
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

                    input.addEventListener('focus', function() {
                        group.classList.add('is-focused');
                    });
                    input.addEventListener('blur', function() {
                        group.classList.remove('is-focused');
                        group.classList.toggle('is-filled', !!input.value.trim());
                    });
                    input.addEventListener('input', function() {
                        group.classList.toggle('is-filled', !!input.value.trim());
                    });
                });

                el.querySelectorAll('.input-group-outline input[type="date"]').forEach(function(input) {
                    if (!input._dpInit) {
                        _initDatepickers();
                    }
                });

            }, 0);
        });
    });
</script>
@endpush