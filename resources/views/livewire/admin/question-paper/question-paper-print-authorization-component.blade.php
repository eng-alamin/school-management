{{-- resources/views/livewire/admin/question-paper/question-paper-print-authorization-component.blade.php --}}
<div>

    <div class="card">

        <!-- floating header -->
        <div class="mat-card-header header-primary-gradient">
            <h5 id="cardHeaderTitleAllsections">Print Authorizations</h5>
            <p id="cardHeaderSubtitle">Control who can print a question paper, and for how long.</p>
        </div>

        <div class="card-header border-0">
            <!-- toolbar -->
            <div class="card-toolbar">
                {{-- Left side --}}
                <div class="card-toolbar-title">
                    <!-- search in table -->
                    <div style="position:relative;display:inline-flex;align-items:center">
                        <span class="material-icons-round" style="position:absolute;left:10px;font-size:17px;color:var(--muted);pointer-events:none">search</span>
                        <input type="text" wire:model.live.debounce.400ms="search" id="tableSearch" placeholder="Search employee, subject..." class="tb-search"/>
                    </div>
                </div>

                <!-- Right Side -->
                @if($authorizations->total() > 10)
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
                <button class="btn btn-primary" wire:click="openAssignModal">
                    <span>
                        <span class="material-icons-round">add_circle</span>
                        <span id="newSectionBtn">Assign Printer</span>
                    </span>
                </button>
            </div>
        </div>

        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th id="th-employee">Employee</th>
                            <th id="th-exam">Exam</th>
                            <th id="th-class">Class</th>
                            <th id="th-subject">Subject</th>
                            <th id="th-window">Valid Window</th>
                            <th id="th-status">Status</th>
                            <th id="th-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($authorizations as $auth)
                            @php
                                $isExpired = !$auth->is_revoked && now()->greaterThan($auth->valid_until);
                                $isUpcoming = !$auth->is_revoked && now()->lessThan($auth->valid_from);
                                $isActive = !$auth->is_revoked && !$isExpired && !$isUpcoming;
                            @endphp
                            <tr wire:key="auth-{{ $auth->id }}">

                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar-placeholder">
                                            <span class="material-icons-round" style="font-size:1rem;">person</span>
                                        </div>
                                        <div class="fw-500 text-dark">{{ $auth->user->name ?? '—' }}</div>
                                    </div>
                                </td>

                                <td class="text-muted">{{ $auth->examSetup->name ?? '—' }}</td>

                                <td class="text-muted">{{ $auth->examSetup->classAssign->academicClass->name ?? '—' }}</td>

                                <td class="text-muted">{{ $auth->subject->name ?? '—' }}</td>

                                <td class="text-muted" style="font-size:.78rem;">
                                    {{ $auth->valid_from->format('d M Y, h:i A') }}
                                    <span class="d-block">&ndash; {{ $auth->valid_until->format('d M Y, h:i A') }}</span>
                                </td>

                                <td>
                                    @if($auth->is_revoked)
                                        <span class="badge bg-danger-subtle text-danger">
                                            <span class="material-icons-round" style="font-size:.8rem;vertical-align:middle;">block</span> Revoked
                                        </span>
                                    @elseif($isExpired)
                                        <span class="badge bg-secondary-subtle text-secondary">
                                            <span class="material-icons-round" style="font-size:.8rem;vertical-align:middle;">history</span> Expired
                                        </span>
                                    @elseif($isUpcoming)
                                        <span class="badge bg-warning-subtle text-warning">
                                            <span class="material-icons-round" style="font-size:.8rem;vertical-align:middle;">schedule</span> Scheduled
                                        </span>
                                    @else
                                        <span class="badge bg-success-subtle text-success">
                                            <span class="material-icons-round" style="font-size:.8rem;vertical-align:middle;">lock_open</span> Active
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    @if(!$auth->is_revoked)
                                        <button type="button" class="act-btn danger" title="Revoke"
                                                wire:click="revoke({{ $auth->id }})"
                                                onclick="return confirm('Revoke this print authorization? The employee will lose print access immediately.')">
                                            <span class="material-icons-round">block</span>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox display-5 d-block mb-2 opacity-25"></i>
                                    No print authorizations found. <a href="#" wire:click.prevent="openAssignModal">Assign one now</a>.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer border-0 bg-white d-flex align-items-center justify-content-between flex-wrap gap-2 py-2 px-3">
            <small class="text-muted">Showing {{ $authorizations->firstItem() ?? 0 }}–{{ $authorizations->lastItem() ?? 0 }} of {{ $authorizations->total() }}</small>
            {{ $authorizations->links('vendor.pagination.custom') }}
        </div>

    </div>

    {{-- ===== ASSIGN MODAL (cascading Exam -> Class -> Subject -> Employee -> Window) ===== --}}
    @if($showAssignModal)
        <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5);" wire:ignore.self wire:keydown.escape="closeAssignModal">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="bi bi-shield-lock me-2 text-primary"></i>
                            Assign Printer
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeAssignModal"></button>
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

                            {{-- STEP 4: Employee --}}
                            <div class="col-12">
                                <div class="input-group input-group-outline">
                                    <label class="form-label">Employee <span class="text-danger">*</span></label>
                                    <select class="form-select @error('newUserId') is-invalid @enderror" wire:model="newUserId">
                                        <option value="">-- Select --</option>
                                        @foreach($this->userOptions as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }} - {{ $user->role }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('newUserId') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            {{-- STEP 5: Valid window --}}
                            <div class="col-6">
                                <div class="input-group input-group-outline">
                                    <label class="form-label">Valid From <span class="text-danger">*</span></label>
                                    <input type="datetime-local" class="form-control @error('validFrom') is-invalid @enderror" wire:model="validFrom">
                                </div>
                                @error('validFrom') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-6">
                                <div class="input-group input-group-outline">
                                    <label class="form-label">Valid Until <span class="text-danger">*</span></label>
                                    <input type="datetime-local" class="form-control @error('validUntil') is-invalid @enderror" wire:model="validUntil">
                                </div>
                                @error('validUntil') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" wire:click="closeAssignModal">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="assign" wire:loading.attr="disabled">
                            <span wire:loading wire:target="assign" class="spinner-border spinner-border-sm me-1"></span>
                            <span class="material-icons-round" style="font-size:16px;vertical-align:middle;">check</span> Assign
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>