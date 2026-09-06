{{-- resources/views/livewire/admin/question-paper/question-paper-builder-component.blade.php --}}
<div class="qp-scope m-0">

    <!-- ================= HEADER ================= -->
    <div class="app-header no-print">
        <div class="d-flex align-items-start justify-content-between flex-wrap gap-3">
            <div>
                <div class="app-eyebrow">
                    <span>Build</span><span class="step">→</span><span>Preview</span><span class="step">→</span><span>Print</span>
                </div>
                <h1>
                    <span class="material-icons-round">edit_note</span> Question Paper Builder
                    @if($isLocked)
                        <span class="badge bg-secondary ms-2" style="font-size:.65rem;vertical-align:middle;">
                            <span class="material-icons-round" style="font-size:13px;">lock</span> Locked
                        </span>
                    @endif
                </h1>
                <p>{{ $examName ?: 'Untitled Exam' }} — {{ $subjectLabel ?: 'Subject' }} — {{ $classLabel ?: 'Class' }}</p>
            </div>
            <div class="header-actions">
                @unless($isLocked)
                    <button class="btn-chrome primary" type="button" wire:click="save" wire:loading.attr="disabled" wire:target="save">
                        <span wire:loading.remove wire:target="save" class="material-icons-round" style="font-size:16px;">save</span>
                        <span wire:loading wire:target="save" class="spinner-border spinner-border-sm"></span>
                        Save
                    </button>
                    @if($paperId)
                        <button class="btn-chrome danger" type="button" wire:click="lockPaper"
                                onclick="return confirm('Lock this paper? It cannot be edited after this.')">
                            <span class="material-icons-round" style="font-size:16px;">lock</span>
                            Lock &amp; Finalize
                        </button>
                    @endif
                @else
                    <span class="text-white-50" style="font-size:.8rem;">This paper is locked and can no longer be edited.</span>
                @endif
                <button class="btn-chrome" type="button" onclick="window.print()">
                    <span class="material-icons-round" style="font-size:16px;">print</span>
                    Print / PDF
                </button>
            </div>
        </div>
    </div>

    <div class="workspace">
        <div class="row g-4">

            <!-- ================= LEFT: BUILDER ================= -->
            <div class="col-lg-6 no-print">

                {{-- ---------- Paper Details ---------- --}}
                <div class="builder-card">
                    <div class="section-head" data-target="metaBody">
                        <h2>
                            <span class="head-icon"><span class="material-icons-round">description</span></span>
                            Paper Details
                        </h2>
                        <span class="material-icons-round toggle-icon">expand_less</span>
                    </div>
                    <div class="section-body meta-grid" id="metaBody">
                        <div class="row g-2">
                            <div class="col-12">
                                <div class="form-label-sm">Paper Language</div>
                                <select wire:model.live="language" class="form-select" @disabled($isLocked)>
                                    <option value="bn">বাংলা</option>
                                    <option value="en">English</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <div class="form-label-sm">Institute Name</div>
                                <input type="text" wire:model.blur="instituteName" class="form-control" @disabled($isLocked)>
                            </div>
                            <div class="col-12">
                                <div class="form-label-sm">Exam Name</div>
                                <input type="text" wire:model.blur="examName" class="form-control" @disabled($isLocked)>
                            </div>
                            <div class="col-6">
                                <div class="form-label-sm">Class</div>
                                <input type="text" wire:model.blur="classLabel" class="form-control" @disabled($isLocked)>
                            </div>
                            <div class="col-6">
                                <div class="form-label-sm">Subject</div>
                                <input type="text" wire:model.blur="subjectLabel" class="form-control" @disabled($isLocked)>
                            </div>
                            <div class="col-6">
                                <div class="form-label-sm">Full Marks</div>
                                <input type="number" step="0.5" wire:model.blur="fullMarks" class="form-control" @disabled($isLocked)>
                            </div>
                            <div class="col-6">
                                <div class="form-label-sm">Time</div>
                                <input type="text" wire:model.blur="timeLabel" class="form-control" placeholder="e.g. 3 Hours" @disabled($isLocked)>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ---------- Questions ---------- --}}
                <div class="builder-card">
                    <div class="section-head" data-target="qBody">
                        <h2>
                            <span class="head-icon"><span class="material-icons-round">quiz</span></span>
                            Questions <span class="section-badge">{{ count($questions) }}</span>
                        </h2>
                        <span class="material-icons-round toggle-icon">expand_less</span>
                    </div>
                    <div class="section-body" id="qBody">

                        @unless($isLocked)
                            <div class="dropdown mb-3">
                                <button class="btn btn-brand dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <span class="material-icons-round" style="font-size:17px;">addadd_circle</span> Add Question
                                </button>
                                <ul class="dropdown-menu" style="max-height: 360px; overflow-y: auto;">
                                    @foreach($typeDefs as $typeValue => $def)
                                        <li>
                                            <a class="dropdown-item" href="#" wire:click.prevent="addQuestion('{{ $typeValue }}')">
                                                {{ str($typeValue)->replace('_', ' ')->title() }}
                                                <small class="text-muted">({{ $def['section'] }})</small>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endunless

                        <div id="qBuilderList" wire:ignore.self
                             x-data
                             x-init="
                                Sortable.create($el, {
                                    handle: '.drag-handle',
                                    disabled: {{ $isLocked ? 'true' : 'false' }},
                                    animation: 150,
                                    onEnd: () => {
                                        const ids = Array.from($el.querySelectorAll('.q-block')).map(el => el.dataset.id);
                                        $wire.reorder(ids);
                                    }
                                });
                             ">
                            @foreach($questions as $qIndex => $q)
                                <div class="q-block" data-id="{{ $q['id'] }}" wire:key="q-{{ $q['id'] }}">
                                    <div class="q-block-head">
                                        <span class="q-block-title">
                                            @unless($isLocked)
                                                <span class="material-icons-round drag-handle">drag_indicator</span>
                                            @endunless
                                            Question <span>{{ $qIndex + 1 }}</span>
                                        </span>
                                        <div class="q-tools">
                                            @unless($isLocked)
                                                <button type="button" wire:click="removeQuestion({{ $qIndex }})" title="Delete">
                                                    <span class="material-icons-round" style="font-size:18px;">delete</span>
                                                </button>
                                            @endunless
                                        </div>
                                    </div>

                                    <div class="type-select-row">
                                        <strong style="font-size:.82rem;flex:1;">{{ str($q['type'])->replace('_', ' ')->title() }}</strong>
                                        <span class="type-pill" data-family="{{ $q['family'] }}">{{ str($q['family'])->replace('_', ' ') }}</span>
                                    </div>

                                    <div class="mb-2">
                                        <input type="text" class="form-control form-control-sm"
                                               list="sectionHeaderSuggestions"
                                               placeholder="Section / Chapter heading"
                                               wire:model.blur="questions.{{ $qIndex }}.section_header"
                                               @disabled($isLocked)>
                                    </div>

                                    @if($q['family'] === 'options')
                                        <textarea class="form-control mb-2 q-text" rows="2" placeholder="Type the question..."
                                                  wire:model.blur="questions.{{ $qIndex }}.question_text" @disabled($isLocked)></textarea>
                                        <div class="options-wrap">
                                            @foreach($q['options'] as $optIndex => $opt)
                                                <div class="opt-row">
                                                    <span class="part-label" style="flex:0 0 24px;height:24px;font-size:.75rem;">{{ chr(65 + $optIndex) }}</span>
                                                    <input type="text" class="form-control form-control-sm opt-text" placeholder="Option {{ $optIndex + 1 }}"
                                                           wire:model.blur="questions.{{ $qIndex }}.options.{{ $optIndex }}.text" @disabled($isLocked)>
                                                    <input type="checkbox" class="form-check-input mt-0" title="Correct answer"
                                                           wire:model="questions.{{ $qIndex }}.options.{{ $optIndex }}.is_correct" @disabled($isLocked)>
                                                </div>
                                            @endforeach
                                        </div>
                                        <div class="row g-2 mb-2">
                                            <div class="col-6">
                                                <div class="marks-side-label" style="text-align:left;">Marks</div>
                                                <input type="number" step="0.5" class="form-control form-control-sm"
                                                       wire:model.blur="questions.{{ $qIndex }}.marks" @disabled($isLocked)>
                                            </div>
                                        </div>

                                    @elseif($q['family'] === 'matching_pairs')
                                        <input type="text" class="form-control form-control-sm mb-2 q-text" placeholder="Match column A with column B"
                                               wire:model.blur="questions.{{ $qIndex }}.question_text" @disabled($isLocked)>
                                        <div class="match-wrap">
                                            @foreach($q['matches'] as $mIndex => $m)
                                                <div class="match-row">
                                                    <input type="text" class="form-control form-control-sm match-left" placeholder="Left item"
                                                           wire:model.blur="questions.{{ $qIndex }}.matches.{{ $mIndex }}.left" @disabled($isLocked)>
                                                    <span class="material-icons-round match-arrow">east</span>
                                                    <input type="text" class="form-control form-control-sm match-right" placeholder="Right item"
                                                           wire:model.blur="questions.{{ $qIndex }}.matches.{{ $mIndex }}.right" @disabled($isLocked)>
                                                </div>
                                            @endforeach
                                        </div>
                                        @unless($isLocked)
                                            <button type="button" class="btn btn-sm btn-outline-secondary mb-2" wire:click="addMatchRow({{ $qIndex }})">
                                                + Add pair
                                            </button>
                                        @endunless
                                        <div class="row g-2 mb-2">
                                            <div class="col-6">
                                                <div class="marks-side-label" style="text-align:left;">Marks</div>
                                                <input type="number" step="0.5" class="form-control form-control-sm"
                                                       wire:model.blur="questions.{{ $qIndex }}.marks" @disabled($isLocked)>
                                            </div>
                                        </div>

                                    @elseif($q['family'] === 'stimulus_parts')
                                        <div class="form-label-sm stimulus-label">Stimulus / passage</div>
                                        <textarea class="form-control mb-2 stimulus-text" rows="3" placeholder="Type the stimulus or passage..."
                                                  wire:model.blur="questions.{{ $qIndex }}.stimulus_text" @disabled($isLocked)></textarea>
                                        <div class="parts-wrap">
                                            @foreach($q['parts'] as $pIndex => $part)
                                                <div class="part-row">
                                                    <span class="part-label">{{ $part['label'] }}</span>
                                                    <textarea class="form-control part-text" rows="1" placeholder="Sub-question {{ $pIndex + 1 }}"
                                                              wire:model.blur="questions.{{ $qIndex }}.parts.{{ $pIndex }}.text" @disabled($isLocked)></textarea>
                                                    <div>
                                                        <input type="number" class="form-control marks-input part-marks"
                                                               wire:model.blur="questions.{{ $qIndex }}.parts.{{ $pIndex }}.marks" @disabled($isLocked)>
                                                        <div class="marks-side-label">marks</div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>

                                    @else
                                        <textarea class="form-control mb-2 q-text" rows="2" placeholder="Type the question..."
                                                  wire:model.blur="questions.{{ $qIndex }}.question_text" @disabled($isLocked)></textarea>
                                        <div class="row g-2 mb-2">
                                            <div class="col-6">
                                                <div class="marks-side-label" style="text-align:left;">Marks</div>
                                                <input type="number" step="0.5" class="form-control form-control-sm"
                                                       wire:model.blur="questions.{{ $qIndex }}.marks" @disabled($isLocked)>
                                            </div>
                                        </div>
                                    @endif

                                    {{-- ---------- Figure: Upload OR Draw (Geometry / Diagram) ---------- --}}
                                    @php
                                        $hasTempFigure = (bool) ($q['figure'] ?? null);
                                        $tempFigureUrl = $hasTempFigure ? $q['figure']->temporaryUrl() : '';
                                        $existingFigureUrl = $q['figure_path'] ? Storage::url($q['figure_path']) : '';
                                        $figureStateKey = $hasTempFigure ? 'temp' : ($q['figure_path'] ? 'saved' : 'none');
                                    @endphp
                                    <div class="mt-2"
                                         wire:key="figure-zone-{{ $q['id'] }}-{{ $figureStateKey }}"
                                         x-data="figurePanel({{ $qIndex }})"
                                         data-existing-figure="{{ $existingFigureUrl }}"
                                         data-temp-figure="{{ $tempFigureUrl }}"
                                         data-locked="{{ $isLocked ? '1' : '0' }}">

                                        {{-- No figure yet: small toggle button --}}
                                        <template x-if="!hasFigure && !panelOpen">
                                            <button type="button" class="add-figure-btn" @click="panelOpen = true" x-show="!locked">
                                                <span class="material-icons-round" style="font-size:15px;">architecture</span> Add Figure
                                            </button>
                                        </template>

                                        {{-- Panel open: Upload / Draw tabs --}}
                                        <template x-if="panelOpen && !hasFigure">
                                            <div class="figure-panel">
                                                <div class="figure-tabs">
                                                    <button type="button" class="figure-tab-btn" :class="{active: tab === 'upload'}" @click="tab = 'upload'">
                                                        Upload Image
                                                    </button>
                                                    <button type="button" class="figure-tab-btn" :class="{active: tab === 'draw'}" @click="tab = 'draw'">
                                                        Draw (Geometry / Diagram)
                                                    </button>
                                                </div>

                                                <div x-show="tab === 'upload'">
                                                    <input type="file" class="form-control form-control-sm" accept="image/*"
                                                           wire:model="questions.{{ $qIndex }}.figure">
                                                    <div wire:loading wire:target="questions.{{ $qIndex }}.figure" class="text-muted mt-1" style="font-size:.72rem;">
                                                        Uploading...
                                                    </div>
                                                </div>

                                                <div x-show="tab === 'draw'" x-cloak>
                                                    <canvas class="figure-canvas" width="460" height="220" x-ref="canvas" x-init="bindCanvas()"></canvas>
                                                    <div class="mt-2 d-flex gap-2">
                                                        <button type="button" class="btn btn-sm btn-outline-secondary" @click="clearCanvas">
                                                            Clear
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-brand" @click="saveDrawing" :disabled="saving">
                                                            <span x-show="!saving">Save Drawing</span>
                                                            <span x-show="saving">Saving...</span>
                                                        </button>
                                                    </div>
                                                </div>

                                                <button type="button" class="btn btn-sm btn-link text-muted mt-1 p-0" @click="panelOpen = false">
                                                    Cancel
                                                </button>
                                            </div>
                                        </template>

                                        {{-- Figure already set (uploaded, drawn, or previously saved): thumbnail + remove --}}
                                        <template x-if="hasFigure">
                                            <div class="figure-thumb-wrap">
                                                <img :src="thumbSrc" alt="Figure">
                                                <template x-if="!locked">
                                                    <button type="button" class="figure-remove-btn" @click="removeFigure" title="Remove figure">
                                                        <span class="material-icons-round" style="font-size:16px;">close</span>
                                                    </button>
                                                </template>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            @endforeach

                            @if(!count($questions))
                                <div class="empty-note">
                                    <span class="material-icons-round" style="font-size:32px;opacity:.4;">note_add</span><br>
                                    Click "Add Question", pick a type from the dropdown — the preview will appear on the right.
                                </div>
                            @endif
                        </div>

                        <datalist id="sectionHeaderSuggestions">
                            @foreach($this->sectionHeaders as $header)
                                <option value="{{ $header }}">
                            @endforeach
                        </datalist>
                    </div>
                </div>
            </div>

            <!-- ================= RIGHT: LIVE PREVIEW ================= -->
            <div class="col-lg-6">
                <div class="preview-wrap">
                    <div class="preview-toolbar no-print">
                        <span class="preview-label">
                            <span class="material-icons-round" style="font-size:17px;">visibility</span>
                            Live Preview
                        </span>
                        <span class="marks-pill">Total: {{ rtrim(rtrim(number_format($this->totalMarks, 1), '0'), '.') }} marks</span>
                    </div>

                    <div class="sheet" data-lang="{{ $language }}">
                        <div class="sheet-head">
                            <div class="inst-name">{{ $instituteName ?: 'Institute Name' }}</div>
                            <div class="exam-title">{{ $examName }}</div>
                        </div>
                        <div class="sheet-meta">
                            <span>{{ $language === 'bn' ? 'শ্রেণি' : 'Class' }}: {{ $classLabel ?: '—' }} &nbsp;|&nbsp; {{ $language === 'bn' ? 'বিষয়' : 'Subject' }}: {{ $subjectLabel ?: '—' }}</span>
                            <span>{{ $language === 'bn' ? 'পূর্ণমান' : 'Full Marks' }}: {{ $fullMarks }}</span>
                            <span>{{ $language === 'bn' ? 'সময়' : 'Time' }}: {{ $timeLabel ?: '—' }}</span>
                        </div>

                        @php
                            $grouped = [];
                            foreach ($questions as $q) {
                                $header = $q['section_header'] ?: ($typeDefs[$q['type']]['section'] ?? 'General');
                                $grouped[$header][] = $q;
                            }
                            $globalIndex = 0;
                        @endphp

                        @forelse($grouped as $headerName => $groupQuestions)
                            <div class="sheet-section-title">{{ $headerName }}</div>

                            @foreach($groupQuestions as $q)
                                @php
                                    $globalIndex++;
                                    // Show whichever figure is currently active for this block:
                                    // a not-yet-saved upload/drawing takes priority (via its
                                    // temporary URL) over an already-persisted figure_path.
                                    $figSrc = ($q['figure'] ?? null)
                                        ? $q['figure']->temporaryUrl()
                                        : ($q['figure_path'] ? Storage::url($q['figure_path']) : null);
                                @endphp

                                @if($q['family'] === 'options')
                                    <div class="mcq-item">
                                        <div class="cq-part" style="padding-left:0;">
                                            <span><span class="q-num">{{ $globalIndex }}.</span> {{ $q['question_text'] ?: '(question not written)' }}</span>
                                            <span class="marks-tag">{{ $q['marks'] }}</span>
                                        </div>
                                        @if($figSrc)
                                            <div class="pv-figure"><img src="{{ $figSrc }}" alt="Figure"></div>
                                        @endif
                                        <div class="mcq-opts">
                                            @foreach($q['options'] as $optIndex => $opt)
                                                <span>{{ chr(65 + $optIndex) }}) {{ $opt['text'] ?: '...........' }}</span>
                                            @endforeach
                                        </div>
                                    </div>

                                @elseif($q['family'] === 'matching_pairs')
                                    <div class="q-item">
                                        <div class="cq-part" style="padding-left:0;">
                                            <span><span class="q-num">{{ $globalIndex }}.</span> {{ $q['question_text'] ?: '(instruction not written)' }}</span>
                                            <span class="marks-tag">{{ $q['marks'] }}</span>
                                        </div>
                                        @if($figSrc)
                                            <div class="pv-figure"><img src="{{ $figSrc }}" alt="Figure"></div>
                                        @endif
                                        <div class="match-table">
                                            @foreach($q['matches'] as $mIndex => $m)
                                                <div>
                                                    <span>{{ $mIndex + 1 }}.</span>
                                                    <span>{{ $m['left'] ?: '...........' }}</span>
                                                    <span class="material-icons-round" style="font-size:14px;">east</span>
                                                    <span>{{ $m['right'] ?: '...........' }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                @elseif($q['family'] === 'stimulus_parts')
                                    <div class="q-item">
                                        <span class="q-num">{{ $globalIndex }}.</span>
                                        <div class="stimulus-box">{{ $q['stimulus_text'] ?: '(stimulus not written)' }}</div>
                                        @if($figSrc)
                                            <div class="pv-figure"><img src="{{ $figSrc }}" alt="Figure"></div>
                                        @endif
                                        @foreach($q['parts'] as $part)
                                            <div class="cq-part">
                                                <span>{{ $part['label'] }}) {{ $part['text'] ?: '...........' }}</span>
                                                <span class="marks-tag">{{ $part['marks'] }}</span>
                                            </div>
                                        @endforeach
                                    </div>

                                @else
                                    <div class="q-item">
                                        <div class="cq-part" style="padding-left:0;">
                                            <span><span class="q-num">{{ $globalIndex }}.</span> {{ $q['question_text'] ?: '(question not written)' }}</span>
                                            <span class="marks-tag">{{ $q['marks'] }}</span>
                                        </div>
                                        @if($figSrc)
                                            <div class="pv-figure"><img src="{{ $figSrc }}" alt="Figure"></div>
                                        @endif
                                    </div>
                                @endif
                            @endforeach
                        @empty
                            <div class="empty-note">
                                <span class="material-icons-round" style="font-size:32px;opacity:.4;">note_add</span><br>
                                Add a question on the left — the preview will appear here.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>
    </div>

</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
    // collapse/expand builder-card sections
    document.addEventListener('livewire:navigated', () => initQpSectionToggles());
    document.addEventListener('DOMContentLoaded', () => initQpSectionToggles());
    function initQpSectionToggles() {
        document.querySelectorAll('.qp-scope .section-head').forEach((head) => {
            if (head.dataset.qpBound) return;
            head.dataset.qpBound = '1';
            head.addEventListener('click', () => {
                const targetId = head.getAttribute('data-target');
                const body = document.getElementById(targetId);
                if (!body) return;
                body.classList.toggle('collapsed');
                const icon = head.querySelector('.toggle-icon');
                if (icon) icon.textContent = body.classList.contains('collapsed') ? 'expand_more' : 'expand_less';
            });
        });
    }
    initQpSectionToggles();

    // ---------------------------------------------------------------
    // Figure panel: Upload / Draw (Geometry & Diagram) — Alpine component
    // used per question block. Drawing is a plain freehand canvas pen;
    // "Save Drawing" rasterizes the canvas to PNG and hands it to
    // Livewire's upload() JS API, which lands it on the same `figure`
    // property WithFileUploads already validates/stores on save().
    // ---------------------------------------------------------------
    document.addEventListener('alpine:init', () => {
        Alpine.data('figurePanel', (qIndex) => ({
            qIndex,
            panelOpen: false,
            tab: 'upload',
            hasFigure: false,
            thumbSrc: '',
            locked: false,
            saving: false,
            _canvasBound: false,

            init() {
                this.locked = this.$el.dataset.locked === '1';
                const temp = this.$el.dataset.tempFigure;
                const existing = this.$el.dataset.existingFigure;
                if (temp) {
                    this.hasFigure = true;
                    this.thumbSrc = temp;
                } else if (existing) {
                    this.hasFigure = true;
                    this.thumbSrc = existing;
                }
                this.bindCanvas();
            },

            bindCanvas() {
                if (this._canvasBound || !this.$refs.canvas) return;
                this._canvasBound = true;

                const canvas = this.$refs.canvas;
                const ctx = canvas.getContext('2d');
                ctx.lineWidth = 2.5;
                ctx.lineCap = 'round';
                ctx.lineJoin = 'round';
                ctx.strokeStyle = '#231c15';

                let drawing = false;

                const pointFromEvent = (e) => {
                    const rect = canvas.getBoundingClientRect();
                    return {
                        x: (e.clientX - rect.left) * (canvas.width / rect.width),
                        y: (e.clientY - rect.top) * (canvas.height / rect.height),
                    };
                };

                canvas.addEventListener('pointerdown', (e) => {
                    drawing = true;
                    const p = pointFromEvent(e);
                    ctx.beginPath();
                    ctx.moveTo(p.x, p.y);
                    e.preventDefault();
                });
                canvas.addEventListener('pointermove', (e) => {
                    if (!drawing) return;
                    const p = pointFromEvent(e);
                    ctx.lineTo(p.x, p.y);
                    ctx.stroke();
                    e.preventDefault();
                });
                window.addEventListener('pointerup', () => { drawing = false; });
                canvas.addEventListener('pointerleave', () => { drawing = false; });
            },

            clearCanvas() {
                const canvas = this.$refs.canvas;
                if (!canvas) return;
                canvas.getContext('2d').clearRect(0, 0, canvas.width, canvas.height);
            },

            saveDrawing() {
                const canvas = this.$refs.canvas;
                if (!canvas || this.saving) return;

                this.saving = true;
                canvas.toBlob((blob) => {
                    if (!blob) {
                        this.saving = false;
                        return;
                    }
                    const file = new File([blob], 'drawing-' + Date.now() + '.png', { type: 'image/png' });
                    this.$wire.upload(
                        'questions.' + this.qIndex + '.figure',
                        file,
                        () => { this.saving = false; this.panelOpen = false; },
                        () => { this.saving = false; alert('Could not save the drawing. Please try again.'); },
                        () => {}
                    );
                }, 'image/png');
            },

            removeFigure() {
                if (this.locked) return;
                this.$wire.call('removeFigure', this.qIndex);
            },
        }));
    });
</script>
@endpush

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Inter:wght@400;500;600;700&family=Hind+Siliguri:wght@400;500;600;700&family=Noto+Serif+Bengali:wght@400;600;700&family=Amiri:wght@400;700&display=swap" rel="stylesheet">
<style>
  /* ===================================================================
     Theme: Bangladesh flag palette — ported from the Question Paper
     Studio prototype (green/red) to replace the previous navy/brass theme.
     All rules stay scoped under .qp-scope so nothing else on the page
     is affected.
  =================================================================== */
  .qp-scope {
    --navy: #006a4e;
    --navy-deep: #00402f;
    --navy-soft: #e3f1ec;
    --brass: #f42a41;
    --brass-deep: #c8102e;
    --brass-soft: #fbdbe0;
    --stamp: #c8102e;
    --slate-bg: #eef6f2;
    --panel: #ffffff;
    --line: #dbe8e2;
    --muted: #5b6472;
    --ink-strong: #1a2233;
    --paper: #fbf6ec;
    --ink: #231c15;

    --fam-options: #00402f;
    --fam-options-bg: #e3f1ec;
    --fam-matching_pairs: #c8102e;
    --fam-matching_pairs-bg: #fbdbe0;
    --fam-stimulus_parts: #8a3527;
    --fam-stimulus_parts-bg: #f5e3de;
    --fam-plain: #4b5563;
    --fam-plain-bg: #eef1f5;

    --radius-lg: 16px;
    --radius-md: 11px;
    --radius-sm: 8px;
    --shadow-sm: 0 1px 2px rgba(0,64,47,.06);
    --shadow-md: 0 10px 28px rgba(0,64,47,.12);

    font-family: "Hind Siliguri","Inter",sans-serif;
    color: var(--ink-strong);
    display: block;
    margin: -1.5rem -1.5rem 0;
  }
  .qp-scope .material-icons-round { vertical-align: middle; font-size: 20px; }

  .qp-scope .app-header {
    background:
      radial-gradient(1100px 220px at 12% -40%, rgba(244,42,65,.35), transparent 60%),
      linear-gradient(120deg, var(--navy-deep) 0%, var(--navy) 60%, #045b3f 100%);
    color: #f4f1ea;
    padding: 22px 28px 20px;
    box-shadow: 0 4px 22px rgba(0,64,47,.28);
    border-bottom: 3px solid var(--brass);
  }
  .qp-scope .app-eyebrow {
    font-family: "Inter",sans-serif; font-size: .68rem; font-weight: 700;
    letter-spacing: .16em; text-transform: uppercase; color: var(--brass-soft);
    opacity: .9; margin-bottom: 4px; display: flex; align-items: center; gap: 6px;
  }
  .qp-scope .app-eyebrow .step { opacity: .55; }
  .qp-scope .app-header h1 {
    font-family: "Fraunces","Noto Serif Bengali",serif; font-size: 1.55rem; font-weight: 600;
    margin: 0; display: flex; align-items: center; gap: 10px; letter-spacing: -0.01em; color: #fff;
  }
  .qp-scope .app-header h1 .material-icons-round { font-size: 22px; color: var(--brass-soft); }
  .qp-scope .app-header p { margin: 4px 0 0; font-size: .86rem; color: #d9ece4; }
  .qp-scope .header-actions { display: flex; gap: 8px; flex-wrap: wrap; }
  .qp-scope .btn-chrome {
    background: rgba(255,255,255,.06); border: 1px solid rgba(244,241,234,.28); color: #f4f1ea;
    font-size: .82rem; font-weight: 600; border-radius: 999px; padding: 7px 14px;
    display: inline-flex; align-items: center; gap: 6px;
    transition: background .15s,border-color .15s,transform .1s;
  }
  .qp-scope .btn-chrome:hover { background: rgba(244,241,234,.14); border-color: var(--brass-soft); color: #fff; }
  .qp-scope .btn-chrome:active { transform: translateY(1px); }
  .qp-scope .btn-chrome.danger:hover { background: rgba(244,42,65,.18); border-color: #f2a3ac; }
  .qp-scope .btn-chrome.primary { background: var(--brass); border-color: var(--brass); color: #fff5f5; }
  .qp-scope .btn-chrome.primary:hover { background: var(--brass-deep); border-color: var(--brass-deep); color: #fff; }

  .qp-scope .workspace { padding: 26px; background: var(--slate-bg); }

  .qp-scope .builder-card {
    background: var(--panel); border: 1px solid var(--line); border-radius: var(--radius-lg);
    box-shadow: var(--shadow-sm); overflow: hidden;
  }
  .qp-scope .builder-card + .builder-card { margin-top: 18px; }
  .qp-scope .section-head {
    display: flex; align-items: center; justify-content: space-between; padding: 14px 18px;
    border-bottom: 1px solid var(--line); cursor: pointer; user-select: none; background: #fcfdfe;
  }
  .qp-scope .section-head h2 {
    font-size: .92rem; font-weight: 700; margin: 0; display: flex; align-items: center; gap: 10px;
    color: var(--ink-strong); font-family: "Inter",sans-serif; letter-spacing: -0.005em;
  }
  .qp-scope .head-icon {
    width: 30px; height: 30px; border-radius: 9px; background: var(--brass-soft); color: var(--brass-deep);
    display: flex; align-items: center; justify-content: center; flex: 0 0 30px;
  }
  .qp-scope .head-icon .material-icons-round { font-size: 17px; }
  .qp-scope .section-badge {
    background: var(--navy); color: #f2f4fa; font-size: .7rem; font-weight: 700; padding: 2px 10px;
    border-radius: 999px; font-family: "Inter",sans-serif;
  }
  .qp-scope .section-body { padding: 18px; }
  .qp-scope .section-body.collapsed { display: none; }
  .qp-scope .toggle-icon { color: var(--muted); transition: transform .15s; }
  .qp-scope .form-label-sm {
    font-size: .76rem; font-weight: 600; color: var(--muted); margin-bottom: 4px;
    text-transform: uppercase; letter-spacing: .03em; font-family: "Inter",sans-serif;
  }
  .qp-scope .form-control, .qp-scope .form-select {
    border-color: var(--line); border-radius: var(--radius-sm); font-family: "Hind Siliguri","Inter",sans-serif;
  }
  .qp-scope .form-control:focus, .qp-scope .form-select:focus {
    border-color: var(--brass); box-shadow: 0 0 0 3px rgba(244,42,65,.15);
  }

  .qp-scope .q-block {
    background: #fbfbfc; border: 1px solid var(--line); border-left: 3px solid var(--brass);
    border-radius: var(--radius-md); padding: 14px 14px 14px 16px; margin-bottom: 12px;
    transition: opacity .15s, box-shadow .15s;
  }
  .qp-scope .q-block:hover { box-shadow: var(--shadow-sm); }
  .qp-scope .q-block-head { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 6px; margin-bottom: 10px; }
  .qp-scope .q-block-title { font-size: .8rem; font-weight: 700; color: var(--ink-strong); display: flex; align-items: center; gap: 6px; font-family: "Inter",sans-serif; }
  .qp-scope .drag-handle { cursor: grab; color: var(--muted); font-size: 19px; touch-action: none; }
  .qp-scope .drag-handle:active { cursor: grabbing; }
  .qp-scope .q-tools button { border: none; background: transparent; color: var(--muted); width: 28px; height: 28px; border-radius: 7px; }
  .qp-scope .q-tools button:hover { background: var(--brass-soft); color: var(--brass-deep); }

  .qp-scope .type-select-row { display: flex; gap: 8px; margin-bottom: 10px; align-items: center; flex-wrap: wrap; }
  .qp-scope .type-pill {
    font-size: .66rem; font-weight: 700; letter-spacing: .04em; text-transform: uppercase;
    background: var(--fam-plain-bg); color: var(--fam-plain); padding: 4px 10px; border-radius: 999px;
    font-family: "Inter",sans-serif; border: 1px solid rgba(0,0,0,.04);
  }
  .qp-scope .type-pill[data-family="options"] { background: var(--fam-options-bg); color: var(--fam-options); }
  .qp-scope .type-pill[data-family="matching_pairs"] { background: var(--fam-matching_pairs-bg); color: var(--fam-matching_pairs); }
  .qp-scope .type-pill[data-family="stimulus_parts"] { background: var(--fam-stimulus_parts-bg); color: var(--fam-stimulus_parts); }
  .qp-scope .type-pill[data-family="plain"] { background: var(--fam-plain-bg); color: var(--fam-plain); }

  .qp-scope .part-row { display: flex; align-items: flex-start; gap: 8px; margin-bottom: 8px; }
  .qp-scope .part-label {
    flex: 0 0 30px; height: 32px; display: flex; align-items: center; justify-content: center;
    background: var(--brass-soft); color: var(--brass-deep); font-weight: 700; font-size: .85rem;
    border-radius: 7px; margin-top: 2px;
  }
  .qp-scope .marks-input { width: 64px; font-size: .85rem; text-align: center; flex: 0 0 64px; }
  .qp-scope .marks-side-label { font-size: .68rem; color: var(--muted); text-align: center; margin-top: 2px; font-family: "Inter",sans-serif; }

  .qp-scope .opt-row { display: flex; align-items: center; gap: 8px; margin-bottom: 6px; }
  .qp-scope .opt-row .part-label { background: var(--navy-soft); color: var(--navy); }
  .qp-scope .match-row { display: flex; align-items: center; gap: 8px; margin-bottom: 6px; }
  .qp-scope .match-arrow { color: var(--brass-deep); font-size: 16px; flex: 0 0 18px; text-align: center; }

  .qp-scope .btn-brand { background: var(--brass); border-color: var(--brass); color: #fff5f5; font-weight: 600; }
  .qp-scope .btn-brand:hover { background: var(--brass-deep); border-color: var(--brass-deep); color: #fff; }

  .qp-scope .empty-note { text-align: center; color: var(--muted); font-family: "Hind Siliguri",sans-serif; font-size: .9rem; padding: 40px 0; }
  .qp-scope .empty-note .material-icons-round { color: var(--brass); }

  /* ---------- figure: upload / draw ---------- */
  .qp-scope .figure-panel {
    border: 1px dashed var(--line); border-radius: var(--radius-sm); padding: 10px; background: #fff; margin-top: 8px;
  }
  .qp-scope .figure-tabs { display: flex; gap: 6px; margin-bottom: 8px; }
  .qp-scope .figure-tab-btn {
    font-size: .76rem; padding: 4px 11px; border-radius: 999px; border: 1px solid var(--line);
    background: #fff; color: var(--muted); font-family: "Inter",sans-serif; font-weight: 600;
  }
  .qp-scope .figure-tab-btn.active { background: var(--brass-soft); color: var(--brass-deep); border-color: var(--brass); }
  .qp-scope .figure-canvas {
    border: 1px solid var(--line); border-radius: 6px; background: #fff; touch-action: none;
    cursor: crosshair; width: 100%; display: block;
  }
  .qp-scope .figure-thumb-wrap { position: relative; display: inline-block; }
  .qp-scope .figure-thumb-wrap img { max-width: 100%; max-height: 180px; border-radius: 6px; border: 1px solid var(--line); }
  .qp-scope .figure-remove-btn {
    position: absolute; top: 4px; right: 4px; background: #fff; border: 1px solid var(--line);
    border-radius: 50%; width: 24px; height: 24px; padding: 0; color: var(--stamp); display: flex;
    align-items: center; justify-content: center;
  }
  .qp-scope .add-figure-btn {
    font-size: .76rem; border: 1px solid var(--navy); color: var(--navy); background: var(--navy-soft);
    border-radius: 7px; padding: 4px 11px; font-weight: 600; font-family: "Inter",sans-serif;
    display: inline-flex; align-items: center; gap: 4px;
  }
  .qp-scope .add-figure-btn:hover { background: #d3ece1; }

  /* ---------- preview / sheet ---------- */
  .qp-scope .preview-wrap { position: sticky; top: 16px; }
  .qp-scope .preview-toolbar {
    display: flex; align-items: center; justify-content: space-between; background: var(--navy); color: #f2f4fa;
    border-radius: var(--radius-lg) var(--radius-lg) 0 0; padding: 12px 18px;
  }
  .qp-scope .preview-label { font-size: .8rem; font-weight: 600; font-family: "Inter",sans-serif; display: flex; align-items: center; gap: 7px; color: #dfe4f0; }
  .qp-scope .marks-pill {
    background: transparent; color: #fbf6ec; font-weight: 700; font-size: .78rem; padding: 6px 14px;
    border-radius: 999px; border: 1.5px dashed var(--brass-soft); font-family: "Inter",sans-serif; letter-spacing: .02em;
  }
  .qp-scope .sheet {
    background:
      radial-gradient(circle at 18% 12%, rgba(35,28,21,.02), transparent 42%),
      radial-gradient(circle at 82% 88%, rgba(35,28,21,.02), transparent 42%),
      var(--paper);
    border: 1px solid var(--line); border-top: 6px solid var(--navy); position: relative;
    border-radius: 0 0 var(--radius-lg) var(--radius-lg); padding: 42px 44px 46px; min-height: 400px;
    box-shadow: var(--shadow-md); font-family: "Noto Serif Bengali",serif; color: var(--ink); line-height: 1.85;
  }
  .qp-scope .sheet::before {
    content: ""; position: absolute; top: 6px; left: 0; right: 0; height: 2px; background: var(--brass);
  }
  .qp-scope .sheet[data-lang="en"] { font-family: "Fraunces","Georgia",serif; }
  .qp-scope .sheet[data-lang="en"] .sheet-meta,
  .qp-scope .sheet[data-lang="en"] .mcq-opts,
  .qp-scope .sheet[data-lang="en"] .match-table,
  .qp-scope .sheet[data-lang="en"] .cq-part .marks-tag { font-family: "Inter",sans-serif; }
  .qp-scope .sheet-head { text-align: center; border-bottom: 2px solid var(--ink); padding-bottom: 12px; margin-bottom: 16px; }
  .qp-scope .sheet-head .inst-name { font-size: 1.18rem; font-weight: 700; letter-spacing: .3px; }
  .qp-scope .sheet-head .exam-title { font-size: .95rem; margin-top: 3px; color: #4a3f33; }
  .qp-scope .sheet-meta { display: flex; justify-content: space-between; font-size: .88rem; margin-bottom: 18px; font-family: "Hind Siliguri",sans-serif; color: #4a3f33; }
  .qp-scope .sheet-section-title { font-weight: 700; font-size: 1rem; text-align: center; margin: 24px 0 12px; position: relative; color: var(--ink); }
  .qp-scope .sheet-section-title::after { content:""; display:block; width:64px; height:2px; background: var(--stamp); margin:6px auto 0; opacity:.55; }
  .qp-scope .q-item { margin-bottom: 16px; }
  .qp-scope .q-item .q-num { font-weight: 700; }
  .qp-scope .stimulus-box { background:#f3ecdf; border-left:3px solid var(--brass); padding:8px 12px; margin:6px 0 8px 22px; font-size:.94rem; border-radius:0 6px 6px 0; }
  .qp-scope .cq-part { display: flex; justify-content: space-between; gap: 10px; padding-left: 22px; margin-bottom: 3px; font-size: .94rem; }
  .qp-scope .cq-part .marks-tag { white-space: nowrap; font-family: "Hind Siliguri",sans-serif; font-size: .82rem; color: #5a4c40; }
  .qp-scope .mcq-item { font-size: .94rem; margin-bottom: 10px; }
  .qp-scope .mcq-opts { display: grid; grid-template-columns: 1fr 1fr; gap: 2px 14px; padding-left: 22px; font-size: .9rem; margin-top: 2px; font-family: "Hind Siliguri",sans-serif; }
  .qp-scope .match-table { padding-left: 22px; font-size: .9rem; margin-top: 4px; font-family: "Hind Siliguri",sans-serif; }
  .qp-scope .match-table div { display: flex; gap: 10px; margin-bottom: 2px; }
  .qp-scope .match-table span:first-child { width: 20px; font-weight: 700; }
  .qp-scope .pv-figure { margin: 8px 0 8px 22px; }
  .qp-scope .pv-figure img { max-width: 260px; max-height: 200px; border: 1px solid var(--line); border-radius: 4px; }

  @media print {
    .no-print { display: none !important; }
    .qp-scope { margin: 0; }
    .qp-scope .workspace { padding: 0; background: #fff; }
    .qp-scope .sheet { box-shadow: none; border: none; border-top: none; padding: 0; }
    .qp-scope .sheet::before { display: none; }
    .qp-scope .preview-wrap { position: static; }
  }
  @media (max-width: 991px) {
    .qp-scope .preview-wrap { position: static; margin-top: 22px; }
    .qp-scope .app-header { padding: 18px 18px 16px; }
  }
  @media (max-width: 575px) {
    .qp-scope .header-actions { width: 100%; }
    .qp-scope .btn-chrome { flex: 1; justify-content: center; }
  }
</style>
@endpush