{{-- resources/views/livewire/admin/question-paper/print-question-paper-component.blade.php --}}
<div>

    <div class="card">
        <div class="mat-card-header header-primary-gradient">
            <h5>Print Question Paper</h5>
            <p>Each print is uniquely watermarked and logged — this cannot be undone.</p>
        </div>

        <div class="card-body">
            @if ($this->paper)
                <div class="row g-2 mb-4">
                    <div class="col-md-4">
                        <div class="text-muted small">Exam</div>
                        <div class="fw-500">{{ $this->paper->exam_name ?: '—' }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small">Class</div>
                        <div class="fw-500">{{ $this->paper->class_label ?: '—' }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small">Subject</div>
                        <div class="fw-500">{{ $this->paper->subject_label ?: '—' }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small">Total Questions</div>
                        <div class="fw-500">{{ $this->paper->questions_count }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small">Full Marks</div>
                        <div class="fw-500">{{ number_format($this->paper->full_marks, 0) }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small">Status</div>
                        <div>
                            @if ($this->paper->is_locked)
                                <span class="badge bg-secondary-subtle text-secondary">
                                    <span class="material-icons-round" style="font-size:.8rem;vertical-align:middle;">lock</span> Locked
                                </span>
                            @else
                                <span class="badge bg-danger-subtle text-danger">Not locked yet — cannot print</span>
                            @endif
                        </div>
                    </div>
                </div>

                <hr>

                @if ($this->isWindowOpen)
                    <div class="alert alert-success d-flex align-items-center gap-2">
                        <span class="material-icons-round">lock_open</span>
                        <div>
                            Your print window is open until
                            <strong>{{ $authorization->valid_until->format('d M Y, h:i A') }}</strong>.
                        </div>
                    </div>

                    @unless ($this->paper->is_locked)
                        <p class="text-danger">This paper hasn't been locked/finalized yet — ask the paper setter to lock it before it can be printed.</p>
                    @else
                        <button type="button" class="btn btn-primary" wire:click="print" wire:loading.attr="disabled"
                                onclick="return confirm('Print now? A unique watermarked copy will be generated and logged under your name.')">
                            <span wire:loading.remove wire:target="print">
                                <span class="material-icons-round" style="vertical-align:middle;">print</span> Print Question Paper
                            </span>
                            <span wire:loading wire:target="print">
                                <span class="spinner-border spinner-border-sm me-1"></span> Generating watermarked copy...
                            </span>
                        </button>
                    @endunless

                @elseif ($authorization)
                    <div class="alert alert-warning d-flex align-items-center gap-2">
                        <span class="material-icons-round">lock_clock</span>
                        <div>
                            Printing is not available right now. Your allowed window is
                            <strong>{{ $authorization->valid_from->format('d M Y, h:i A') }}</strong>
                            &ndash;
                            <strong>{{ $authorization->valid_until->format('d M Y, h:i A') }}</strong>.
                        </div>
                    </div>
                @else
                    {{-- Defensive fallback: mount() aborts with a 403 before this
                         view ever renders when there's no authorization at all, so
                         this branch should be unreachable in normal operation. It
                         exists only so a future change to that guard fails safe
                         with a clear message instead of a raw null-property error. --}}
                    <div class="alert alert-danger d-flex align-items-center gap-2">
                        <span class="material-icons-round">block</span>
                        <div>You don't have a print authorization for this exam/subject.</div>
                    </div>
                @endif
            @else
                <div class="text-center text-muted py-5">
                    No question paper has been created yet for this exam/subject.
                </div>
            @endif
        </div>
    </div>

</div>
