<div>

    <div class="card">

        <div class="mat-card-header header-primary-gradient">
            <h5 id="cardHeaderTitleAllassigns">Exam Duty Assign</h5>
            <p id="cardHeaderSubtitle">Assign invigilator teachers to each exam schedule.</p>
        </div>

        <div class="card-header border-0">
            <div class="card-toolbar">
                <div class="card-toolbar-title">
                    <div style="position:relative;display:inline-flex;align-items:center">
                        <span class="material-icons-round" style="position:absolute;left:10px;font-size:17px;color:var(--muted);pointer-events:none">search</span>
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search by subject" class="tb-search"/>
                    </div>
                </div>

                <div class="col-md-3">
                    <select class="form-select form-select-sm" wire:model.live="examSetupFilter">
                        <option value="">All Exams</option>
                        @foreach($examSetups as $setup)
                            <option value="{{ $setup->id }}">
                                {{ $setup->name }}
                                @if($setup->classAssign?->academicClass)
                                    — {{ $setup->classAssign->academicClass->name }}{{ $setup->classAssign->academicSection ? ' - ' . $setup->classAssign->academicSection->name : '' }}
                                @endif
                                @if($setup->term)
                                    ({{ $setup->term->name }})
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>

                @if($schedules->total() > 10)
                    <div class="col-md-2">
                        <select class="form-select form-select-sm" wire:model.live="perPage">
                            <option value="10">10 / page</option>
                            <option value="25">25 / page</option>
                            <option value="50">50 / page</option>
                        </select>
                    </div>
                @endif
            </div>
        </div>

        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th id="th-sl" wire:click="sortBy('id')" style="cursor:pointer">
                                SL
                                @if($sortField === 'id')
                                    <span class="material-icons-round" style="font-size:14px;vertical-align:middle">
                                        {{ $sortDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}
                                    </span>
                                @endif
                            </th>
                            <th id="th-exam" wire:click="sortBy('exam_name')" style="cursor:pointer">
                                Exam
                                @if($sortField === 'exam_name')
                                    <span class="material-icons-round" style="font-size:14px;vertical-align:middle">
                                        {{ $sortDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}
                                    </span>
                                @endif
                            </th>
                            <th id="th-subject" wire:click="sortBy('subject_name')" style="cursor:pointer">
                                Subject
                                @if($sortField === 'subject_name')
                                    <span class="material-icons-round" style="font-size:14px;vertical-align:middle">
                                        {{ $sortDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}
                                    </span>
                                @endif
                            </th>
                            <th id="th-class">Class / Section</th>
                            <th id="th-date" wire:click="sortBy('exam_date')" style="cursor:pointer">
                                Date &amp; Time
                                @if($sortField === 'exam_date')
                                    <span class="material-icons-round" style="font-size:14px;vertical-align:middle">
                                        {{ $sortDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}
                                    </span>
                                @endif
                            </th>
                            <th id="th-room">Room</th>
                            <th id="th-duty">Invigilators</th>
                            <th id="th-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($schedules as $i => $schedule)
                        <tr>
                            <td class="text-muted">{{ $schedules->firstItem() + $i }}</td>
                            <td>
                                <span class="fw-semibold">{{ $schedule->examSetup?->name ?? '—' }}</span>
                                @if($schedule->examSetup?->term || $schedule->examSetup?->type)
                                    <br>
                                    <small class="text-muted">
                                        {{ $schedule->examSetup?->term?->name }}
                                        @if($schedule->examSetup?->term && $schedule->examSetup?->type)
                                            &middot;
                                        @endif
                                        {{ $schedule->examSetup?->type?->name }}
                                    </small>
                                @endif
                            </td>
                            <td>{{ $schedule->examSetupDetail?->classAssignDetail?->subject?->name ?? '—' }}</td>
                            <td>
                                {{ $schedule->examSetup?->classAssign?->academicClass?->name ?? '—' }}
                                {{ $schedule->examSetup?->classAssign?->academicSection ? ' - ' . $schedule->examSetup->classAssign->academicSection->name : '' }}
                            </td>
                            <td>
                                {{ $schedule->exam_date?->format('d M, Y') }}<br>
                                <small class="text-muted">{{ \Illuminate\Support\Carbon::parse($schedule->start_time)->format('h:i A') }} - {{ \Illuminate\Support\Carbon::parse($schedule->end_time)->format('h:i A') }}</small>
                            </td>
                            <td>{{ $schedule->class_room ?? '—' }}</td>
                            <td>
                                @php $duties = $dutyMap->get($schedule->id, collect()); @endphp
                                @if($duties->isNotEmpty())
                                    <div class="d-flex flex-column gap-1">
                                        @foreach($duties as $duty)
                                            <span class="badge bg-dark">{{ $duty->teacher?->name }}</span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-muted">No teacher assigned</span>
                                @endif
                            </td>
                            <td>
                                <button class="act-btn edit" title="Manage Duty" wire:click="openManageDuty({{ $schedule->id }})">
                                    <span class="material-icons-round">assignment_ind</span>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox display-5 d-block mb-2 opacity-25"></i>
                                No exam schedules found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer border-0 bg-white d-flex align-items-center justify-content-between flex-wrap gap-2 py-2 px-3">
            <small class="text-muted">Showing {{ $schedules->firstItem() ?? 0 }}–{{ $schedules->lastItem() ?? 0 }} of {{ $schedules->total() }}</small>
            {{ $schedules->links('vendor.pagination.custom') }}
        </div>

    </div>

    {{-- ===== MANAGE DUTY MODAL ===== --}}
    @if($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5);" wire:ignore.self>
            <div class="modal-dialog modal-md modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header border-0">
                        <h5 class="modal-title">Manage Invigilator Duty</h5>
                        <button type="button" class="btn-close" wire:click="$set('showModal', false)"></button>
                    </div>
                    <div class="modal-body">
                        <label class="form-label">Select Teachers <span class="text-danger">*</span></label>
                        <div class="border rounded p-2" style="max-height:320px;overflow-y:auto;">
                            @forelse($teachers as $teacherId => $teacherName)
                                <div class="form-check d-flex align-items-center gap-2 py-1 {{ !$loop->last ? 'border-bottom' : '' }}">
                                    <input
                                        class="form-check-input m-0"
                                        type="checkbox"
                                        wire:model="teacher_array"
                                        value="{{ $teacherId }}"
                                        id="duty-teacher-{{ $teacherId }}"
                                        style="cursor:pointer">
                                    <label class="form-check-label" for="duty-teacher-{{ $teacherId }}" style="cursor:pointer">
                                        {{ $teacherName }}
                                    </label>
                                </div>
                            @empty
                                <p class="text-muted small mb-0">No teachers found.</p>
                            @endforelse
                        </div>
                        @error('teacher_array') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        @error('teacher_array.*') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>
                    <div class="modal-footer border-0 mt-3">
                        <button type="button" class="btn btn-light" wire:click="$set('showModal', false)">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="save" wire:loading.attr="disabled" wire:target="save">
                            <span wire:loading wire:target="save" class="spinner-border spinner-border-sm me-1"></span>
                            Save Duty
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>