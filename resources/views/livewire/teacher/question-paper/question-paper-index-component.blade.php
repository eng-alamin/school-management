{{-- resources/views/livewire/teacher/question-paper/question-paper-index-component.blade.php --}}
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
                        <tr>

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
                                    @if($paper->is_locked)
                                        <a href="{{ route('teacher.question-papers.print', ['examId' => $paper->exam_id, 'subjectId' => $paper->subject_id]) }}" class="act-btn status btn-success" title="Print">
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
                                No question papers found.
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

</div>