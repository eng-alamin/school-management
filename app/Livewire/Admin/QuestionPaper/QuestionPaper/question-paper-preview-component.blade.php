{{-- resources/views/livewire/admin/question-paper/question-paper-preview-component.blade.php --}}
<div class="qpv-scope">

    {{-- ================= TOP BAR (screen only) ================= --}}
    <div class="qpv-topbar no-print">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <span class="qpv-topbar-icon">
                    <span class="material-icons-round">visibility</span>
                </span>
                <div>
                    <h1 class="qpv-topbar-title">Question Paper Preview</h1>
                    <p class="qpv-topbar-sub">{{ $paper->exam_name }} — {{ $paper->subject_label }}</p>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('admin.question-papers.index') }}" class="btn btn-sm qpv-btn-outline">
                    <span class="material-icons-round" style="font-size:16px;">arrow_back</span>
                    <span>Back to List</span>
                </a>
                {{-- Intentionally no "Print / PDF" button here. This is a
                     preview-only screen; the only sanctioned way to obtain a
                     physical/PDF copy is the authorized print pipeline
                     (locked paper + valid authorization window + watermark
                     + access log). Adding a print trigger here would let
                     anyone who can open this URL bypass all of that. --}}
                @if($paper->is_locked)
                    <a href="{{ route('admin.question-papers.print', ['examId' => $paper->exam_id, 'subjectId' => $paper->subject_id]) }}" class="btn btn-sm qpv-btn-primary">
                        <span class="material-icons-round" style="font-size:16px;">print</span>
                        <span>Go to authorized print</span>
                    </a>
                @endif
            </div>
        </div>
    </div>

    {{-- ================= SHEET ================= --}}
    <div class="qpv-wrap">
        <div class="qpv-sheet">

            <div class="qpv-sheet-head text-center">
                <div class="qpv-inst-name">{{ $paper->institute_name ?: 'Institute Name' }}</div>
                <div class="qpv-exam-title">{{ $paper->exam_name }}</div>
            </div>

            <div class="qpv-sheet-meta">
                <span><span class="qpv-meta-lbl">Class:</span> {{ $paper->class_label ?: '—' }}</span>
                <span><span class="qpv-meta-lbl">Subject:</span> {{ $paper->subject_label ?: '—' }}</span>
                <span><span class="qpv-meta-lbl">Full Marks:</span> {{ number_format($paper->full_marks, 0) }}</span>
                <span><span class="qpv-meta-lbl">Time:</span> {{ $paper->time_label ?: '—' }}</span>
            </div>

            @forelse ($groupedQuestions as $header => $group)
                <div class="qpv-section-title">{{ $group['label_bn'] }}</div>

                @php $displayIndex = 0; @endphp
                @foreach ($group['questions'] as $question)
                    @php $displayIndex++; @endphp

                    <div class="qpv-q-item">
                        @if ($question->family === 'options')
                            <div class="qpv-q-row">
                                <span class="qpv-q-text">
                                    <span class="qpv-q-num">{{ $displayIndex }}.</span>
                                    {{ $question->question_text }}
                                </span>
                                <span class="qpv-marks-tag">{{ rtrim(rtrim(number_format($question->marks, 1), '0'), '.') }}</span>
                            </div>

                            @if ($question->figure_path)
                                <div class="qpv-figure">
                                    <img src="{{ Storage::url($question->figure_path) }}" alt="Figure">
                                </div>
                            @endif

                            <div class="qpv-mcq-opts">
                                @foreach ($question->options as $i => $opt)
                                    <span>{{ chr(65 + $i) }}) {{ $opt->option_text ?: '...........' }}</span>
                                @endforeach
                            </div>

                        @elseif ($question->family === 'matching_pairs')
                            <div class="qpv-q-row">
                                <span class="qpv-q-text">
                                    <span class="qpv-q-num">{{ $displayIndex }}.</span>
                                    {{ $question->question_text }}
                                </span>
                                <span class="qpv-marks-tag">{{ rtrim(rtrim(number_format($question->marks, 1), '0'), '.') }}</span>
                            </div>

                            @if ($question->figure_path)
                                <div class="qpv-figure">
                                    <img src="{{ Storage::url($question->figure_path) }}" alt="Figure">
                                </div>
                            @endif

                            <div class="qpv-match-table">
                                @foreach ($question->matches as $i => $m)
                                    <div class="qpv-match-row">
                                        <span class="qpv-match-idx">{{ $i + 1 }}.</span>
                                        <span>{{ $m->left_text ?: '...........' }}</span>
                                        <span class="material-icons-round qpv-match-arrow">east</span>
                                        <span>{{ $m->right_text ?: '...........' }}</span>
                                    </div>
                                @endforeach
                            </div>

                        @elseif ($question->family === 'stimulus_parts')
                            <div class="qpv-q-num mb-1">{{ $displayIndex }}.</div>
                            <div class="qpv-stimulus-box">{{ $question->stimulus_text }}</div>

                            @if ($question->figure_path)
                                <div class="qpv-figure">
                                    <img src="{{ Storage::url($question->figure_path) }}" alt="Figure">
                                </div>
                            @endif

                            @foreach ($question->parts as $part)
                                <div class="qpv-part-row">
                                    <span>{{ $part->part_label }}) {{ $part->part_text ?: '...........' }}</span>
                                    <span class="qpv-marks-tag">{{ rtrim(rtrim(number_format($part->marks, 1), '0'), '.') }}</span>
                                </div>
                            @endforeach

                        @else
                            <div class="qpv-q-row">
                                <span class="qpv-q-text">
                                    <span class="qpv-q-num">{{ $displayIndex }}.</span>
                                    {{ $question->question_text }}
                                </span>
                                <span class="qpv-marks-tag">{{ rtrim(rtrim(number_format($question->marks, 1), '0'), '.') }}</span>
                            </div>

                            @if ($question->figure_path)
                                <div class="qpv-figure">
                                    <img src="{{ Storage::url($question->figure_path) }}" alt="Figure">
                                </div>
                            @endif
                        @endif
                    </div>
                @endforeach
            @empty
                <div class="qpv-empty">
                    <span class="material-icons-round">note_add</span>
                    <p>No questions have been added to this paper yet.</p>
                </div>
            @endforelse

        </div>
    </div>

</div>

@push('styles')
<style>
    /* ═══ scoped to this page only; reuses theme.css tokens ═══ */
    .qpv-scope { display: block; }

    /* ---------- top bar ---------- */
    .qpv-topbar {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: var(--radius-card);
        box-shadow: var(--shadow-sm);
        padding: 16px 20px;
        margin-bottom: 20px;
    }
    .qpv-topbar-icon {
        width: 38px; height: 38px; border-radius: 10px;
        background: var(--primary-100); color: var(--primary);
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .qpv-topbar-title {
        font-size: 1rem; font-weight: 700; color: var(--heading); margin: 0;
    }
    .qpv-topbar-sub {
        font-size: 0.78rem; color: var(--muted); margin: 2px 0 0;
    }
    .qpv-btn-outline {
        border: 1px solid var(--border-strong);
        background: transparent;
        color: var(--heading);
        border-radius: var(--radius-btn);
        font-weight: 600;
        display: inline-flex; align-items: center; gap: 6px;
        padding: 6px 14px;
    }
    .qpv-btn-outline:hover { background: var(--hover-tint-soft); color: var(--heading); }
    .qpv-btn-primary {
        border: none;
        background: var(--primary);
        color: #fff;
        border-radius: var(--radius-btn);
        font-weight: 600;
        display: inline-flex; align-items: center; gap: 6px;
        padding: 6px 14px;
    }
    .qpv-btn-primary:hover { background: var(--primary-dark); color: #fff; }

    /* ---------- sheet wrapper ---------- */
    .qpv-wrap {
        display: flex;
        justify-content: center;
    }
    .qpv-sheet {
        background: var(--card);
        border: 1px solid var(--border);
        border-top: 6px solid var(--primary);
        border-radius: var(--radius-card);
        box-shadow: var(--shadow-md);
        width: 100%;
        max-width: 800px;
        padding: 42px 48px 50px;
        color: var(--ink, var(--heading));
        line-height: 1.85;
    }

    .qpv-sheet-head {
        border-bottom: 2px solid var(--heading);
        padding-bottom: 12px;
        margin-bottom: 16px;
    }
    .qpv-inst-name {
        font-size: 1.2rem;
        font-weight: 800;
        color: var(--heading);
        letter-spacing: 0.2px;
    }
    .qpv-exam-title {
        font-size: 0.95rem;
        color: var(--muted);
        margin-top: 4px;
    }

    .qpv-sheet-meta {
        display: flex;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 6px 18px;
        font-size: 0.86rem;
        color: var(--muted);
        margin-bottom: 22px;
        padding-bottom: 14px;
        border-bottom: 1px dashed var(--border-strong);
    }
    .qpv-meta-lbl { color: var(--heading); font-weight: 600; }

    .qpv-section-title {
        font-weight: 700;
        font-size: 0.95rem;
        text-align: center;
        color: var(--heading);
        margin: 26px 0 14px;
        position: relative;
    }
    .qpv-section-title::after {
        content: "";
        display: block;
        width: 60px;
        height: 2px;
        background: var(--primary);
        opacity: 0.5;
        margin: 6px auto 0;
    }

    .qpv-q-item { margin-bottom: 16px; }
    .qpv-q-row {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
        font-size: 0.92rem;
    }
    .qpv-q-num { font-weight: 700; margin-right: 4px; }
    .qpv-q-text { flex: 1; }
    .qpv-marks-tag {
        flex-shrink: 0;
        font-size: 0.76rem;
        font-weight: 700;
        /* color: var(--primary);
        background: var(--primary-100); */
        border-radius: 20px;
        padding: 2px 10px;
        white-space: nowrap;
    }

    .qpv-figure { margin: 10px 0 6px 22px; }
    .qpv-figure img {
        max-width: 260px;
        max-height: 200px;
        border: 1px solid var(--border);
        border-radius: 6px;
    }

    .qpv-mcq-opts {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 3px 16px;
        padding-left: 22px;
        margin-top: 4px;
        font-size: 0.88rem;
        color: var(--heading);
    }

    .qpv-match-table {
        padding-left: 22px;
        margin-top: 6px;
        font-size: 0.88rem;
    }
    .qpv-match-row {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 3px;
    }
    .qpv-match-idx { font-weight: 700; width: 18px; }
    .qpv-match-arrow { font-size: 14px !important; color: var(--primary); }

    .qpv-stimulus-box {
        background: var(--surface-2);
        border-left: 3px solid var(--primary);
        border-radius: 0 8px 8px 0;
        padding: 10px 14px;
        margin: 4px 0 10px 22px;
        font-size: 0.9rem;
        color: var(--heading);
    }
    .qpv-part-row {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        padding-left: 22px;
        margin-bottom: 4px;
        font-size: 0.9rem;
    }

    .qpv-empty {
        text-align: center;
        color: var(--muted);
        padding: 48px 0;
    }
    .qpv-empty .material-icons-round { font-size: 34px; opacity: 0.4; }
    .qpv-empty p { margin-top: 8px; font-size: 0.9rem; }

    /* ---------- print ----------
       A user can still trigger the browser's native print (Ctrl+P) even
       without an on-screen button. Since this preview is unwatermarked and
       unlogged, we must not let @media print render the actual sheet — only
       the authorized print pipeline (PrintQuestionPaperComponent) may
       produce printable/downloadable output. */
    @media print {
        .no-print { display: none !important; }
        .qpv-wrap { display: none !important; }
        body::after {
            content: "This preview cannot be printed directly. Use the authorized Print flow from the Question Papers list to get a watermarked copy.";
            display: block;
            padding: 40px;
            font-family: sans-serif;
            font-size: 14px;
        }
    }

    @media (max-width: 576px) {
        .qpv-sheet { padding: 26px 20px 30px; }
        .qpv-mcq-opts { grid-template-columns: 1fr; }
    }
</style>
@endpush