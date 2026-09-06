{{-- resources/views/livewire/admin/question-paper/question-paper-index-component.blade.php --}}
<div>

    <div class="card">

        <!-- floating header -->
        <div class="mat-card-header header-primary-gradient">
            <h5 id="cardHeaderTitleAllsections">Question Papers</h5>
            <p id="cardHeaderSubtitle">Create, manage, and print exam question papers.</p>
        </div>

        <div class="card-header border-0">
            <!-- toolbar -->
            <div class="card-toolbar">
                {{-- Left side --}}
                <div class="card-toolbar-title">
                    <!-- search in table -->
                    <div style="position:relative;display:inline-flex;align-items:center">
                        <span class="material-icons-round" style="position:absolute;left:10px;font-size:17px;color:var(--muted);pointer-events:none">search</span>
                        <input type="text" wire:model.live.debounce.400ms="search" id="tableSearch" placeholder="Search exam, subject, class..." class="tb-search"/>
                    </div>
                </div>

                <!-- Right Side -->
                @if($papers->total() > 10)
                    <div class="col-md-2">
                        <div class="input-group input-group-outline">
                            <select class="form-select form-select-sm" wire:model.live="perPage">
                                <option value="10">10 / page</option>
                                <option value="15">15 / page</option>
                                <option value="25">25 / page</option>
                                <option value="50">50 / page</option>
                            </select>
                        </div>
                    </div>
                @endif
                <button class="btn btn-primary" wire:click="openCreateModal">
                    <span>
                        <span class="material-icons-round">add</span>
                        <span id="newSectionBtn">Add Question Paper</span>
                    </span>
                </button>
            </div>
        </div>

        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th id="th-exam" role="button" wire:click="sortBy('exam_name')">
                                Exam
                                @if ($sortField === 'exam_name') <span class="material-icons-round" style="font-size:14px;vertical-align:middle;">{{ $sortDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span> @endif
                            </th>
                            <th id="th-subject" role="button" wire:click="sortBy('subject_label')">
                                Subject
                                @if ($sortField === 'subject_label') <span class="material-icons-round" style="font-size:14px;vertical-align:middle;">{{ $sortDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span> @endif
                            </th>
                            <th id="th-class" role="button" wire:click="sortBy('class_label')">
                                Class
                                @if ($sortField === 'class_label') <span class="material-icons-round" style="font-size:14px;vertical-align:middle;">{{ $sortDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span> @endif
                            </th>
                            <th id="th-questions">Questions</th>
                            <th id="th-marks" role="button" wire:click="sortBy('full_marks')">
                                Full Marks
                                @if ($sortField === 'full_marks') <span class="material-icons-round" style="font-size:14px;vertical-align:middle;">{{ $sortDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span> @endif
                            </th>
                            <th id="th-status">Status</th>
                            <th id="th-created" role="button" wire:click="sortBy('created_at')">
                                Created
                                @if ($sortField === 'created_at') <span class="material-icons-round" style="font-size:14px;vertical-align:middle;">{{ $sortDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span> @endif
                            </th>
                            <th id="th-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($papers as $paper)
                        <tr wire:key="paper-{{ $paper->id }}" role="button"
                            onclick="window.location='{{ route('admin.question-papers.preview', $paper->id) }}'">

                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar-placeholder">
                                        <span class="material-icons-round" style="font-size:1rem;">description</span>
                                    </div>
                                    <div class="fw-500 text-dark">{{ $paper->exam_name ?: '—' }}</div>
                                </div>
                            </td>

                            <td class="text-muted">{{ $paper->subject_label ?: '—' }}</td>

                            <td class="text-muted">{{ $paper->class_label ?: '—' }}</td>

                            <td class="text-muted">{{ $paper->questions_count }}</td>

                            <td class="text-muted">{{ $paper->full_marks }}</td>

                            <td>
                                @if($paper->is_locked)
                                    <span class="badge bg-secondary-subtle text-secondary">
                                        <span class="material-icons-round" style="font-size:.8rem;vertical-align:middle;">lock</span> Locked
                                    </span>
                                @else
                                    <span class="badge bg-warning-subtle text-warning">Draft</span>
                                @endif
                            </td>

                            <td class="text-muted" style="font-size:.78rem;">
                                {{ $paper->created_at->format('d M Y') }}
                            </td>

                            <td onclick="event.stopPropagation()">
                                <div class="d-flex gap-1">
                                    <a href="{{ route('admin.question-papers.preview', $paper->id) }}" class="act-btn view" title="View">
                                        <span class="material-icons-round">visibility</span>
                                    </a>
                                    @unless($paper->is_locked)
                                        <a href="{{ route('admin.question-papers.builder.edit', ['examId' => $paper->exam_id, 'subjectId' => $paper->subject_id, 'paperId' => $paper->id]) }}" class="act-btn edit" title="Edit">
                                            <span class="material-icons-round">drive_file_rename_outline</span>
                                        </a>
                                    @endunless
                                    @if($paper->is_locked)
                                        <a href="{{ route('admin.question-papers.print', ['examId' => $paper->exam_id, 'subjectId' => $paper->subject_id]) }}" class="act-btn status btn-success" title="Print">
                                            <span class="material-icons-round">print</span>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox display-5 d-block mb-2 opacity-25"></i>
                                No question papers found. <a href="#" wire:click.prevent="openCreateModal">Create one now</a>.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer border-0 bg-white d-flex align-items-center justify-content-between flex-wrap gap-2 py-2 px-3">
            <small class="text-muted">Showing {{ $papers->firstItem() ?? 0 }}–{{ $papers->lastItem() ?? 0 }} of {{ $papers->total() }}</small>
            {{ $papers->links('vendor.pagination.custom') }}
        </div>

    </div>

    {{-- ===== CREATE MODAL (cascading Exam -> Class -> Subject) ===== --}}
    @if($showCreateModal)
        <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5);" wire:ignore.self wire:keydown.escape="closeCreateModal">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="bi bi-file-earmark-text me-2 text-primary"></i>
                            Start New Question Paper
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeCreateModal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">

                            {{-- STEP 1: Exam --}}
                            <div class="col-12">
                                <div class="input-group input-group-outline">
                                    <label class="form-label">Exam <span class="text-danger">*</span></label>
                                    <select class="form-select @error('newExamName') is-invalid @enderror" wire:model.live="newExamName">
                                        <option value="">-- Select --</option>
                                        @foreach($this->examNameOptions as $name)
                                            <option value="{{ $name }}">{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('newExamName') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            {{-- STEP 2: Class — enabled only after an exam is picked --}}
                            <div class="col-12">
                                <div class="input-group input-group-outline">
                                    <label class="form-label">Class <span class="text-danger">*</span></label>
                                    <select class="form-select @error('newExamSetupId') is-invalid @enderror" wire:model.live="newExamSetupId" @disabled(!$newExamName)>
                                        <option value="">-- Select --</option>
                                        @foreach($this->classOptions as $class)
                                            <option value="{{ $class['id'] }}">{{ $class['label'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('newExamSetupId') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                @if($newExamName && $this->classOptions->isEmpty())
                                    <small class="text-muted d-block mt-1">No class setup found for this exam.</small>
                                @endif
                            </div>

                            {{-- STEP 3: Subject — enabled only after a class is picked --}}
                            <div class="col-12">
                                <div class="input-group input-group-outline">
                                    <label class="form-label">Subject <span class="text-danger">*</span></label>
                                    <select class="form-select @error('newSubjectId') is-invalid @enderror" wire:model="newSubjectId" @disabled(!$newExamSetupId)>
                                        <option value="">-- Select --</option>
                                        @foreach($this->subjectOptions as $subject)
                                            <option value="{{ $subject['id'] }}">{{ $subject['label'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('newSubjectId') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                @if($newExamSetupId && $this->subjectOptions->isEmpty())
                                    <small class="text-muted d-block mt-1">No subject found for this class.</small>
                                @endif
                            </div>

                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" wire:click="closeCreateModal">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="createNew" wire:loading.attr="disabled">
                            <span wire:loading wire:target="createNew" class="spinner-border spinner-border-sm me-1"></span>
                            <span class="material-icons-round" style="font-size:16px;vertical-align:middle;">arrow_forward</span> Continue
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>