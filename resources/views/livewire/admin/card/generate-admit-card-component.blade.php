<div wire:init="$refresh">

    <div class="card no-print">

        {{-- Flash messages --}}
        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-3">
            <i class="bi bi-x-circle me-2"></i>{{ session('error') }}
            <button class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <!-- floating header -->
        <div class="mat-card-header header-pink-gradient">
            <h5 id="cardHeaderTitleAllsections">Admit Card Generate</h5>
            <p id="cardHeaderSubtitle">A lightweight, extendable, dependency-free javascript HTML table plugin.</p>
        </div>

        <div class="row g-4 p-5">

            <div class="col-md-3">
                <div class="input-group input-group-outline">
                    <label class="form-label">Class</label>
                    <select wire:model.live="filterClass" class="form-select">
                        <option value="">Select Class</option>
                        @foreach ($classes as $item)
                            <option value="{{ $item['id'] }}">{{ $item['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                @error('filterClass') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <div class="col-md-3">
                <div class="input-group input-group-outline">
                    <label class="form-label">Section</label>
                    <select wire:model.live="filterSection" class="form-select"
                        {{ empty($sections) ? 'disabled' : '' }}>
                        <option value="">{{ !$filterClass ? 'Select Class First' : 'Select Section' }}</option>
                        @if(!empty($sections))
                            <option value="all">All Section</option>
                            @foreach ($sections as $item)
                                <option value="{{ $item['id'] }}">{{ $item['name'] }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
                @error('filterSection') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <div class="col-md-3">
                <div wire:ignore class="input-group input-group-outline">
                    <label class="form-label">Exam</label>
                    <select wire:model.lazy="filterExam" class="form-select">
                        <option value="">Select Exam</option>
                        @foreach($exams as $exam)
                            <option value="{{ $exam->id }}">{{ $exam->name }}</option>
                        @endforeach
                    </select>
                </div>
                @error('filterExam') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <div class="col-md-3">
                <div wire:ignore class="input-group input-group-outline">
                    <label class="form-label">Template</label>
                    <select wire:model.lazy="filterTemplate" class="form-select">
                        <option value="">Select Template</option>
                        @foreach($templates as $template)
                            <option value="{{ $template->id }}">{{ $template->name }}</option>
                        @endforeach
                    </select>
                </div>
                @error('filterTemplate') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <div class="col-md-12 text-center">
                <button class="btn-pink w-100 d-flex justify-content-center align-items-center"
                        wire:click="applyFilter"
                        wire:loading.attr="disabled">
                    <span wire:loading wire:target="applyFilter" class="spinner-border spinner-border-sm"></span>
                    <i class="bi bi-funnel" wire:loading.remove wire:target="applyFilter"></i>
                    Filter
                </button>
            </div>
        </div>

        @if($filtered)
            <div class="card-header bg-white border-0">
                <div class="card-toolbar">
                    {{-- Left side --}}
                    <div class="card-toolbar-title d-flex align-items-center gap-2">
                        <span class="material-icons-round">people</span>
                        <h5>Student List</h5>
                    </div>

                    <!-- Right Side -->
                    <div class="date-row mb-4">
                        <div class="date-field">
                            <label class="date-label">Print Date</label>
                            <input type="date" class="form-control" style="width:200px;" wire:model.defer="print_date">
                            @error('print_date')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="date-field">
                            <label class="date-label">Expiry Date</label>
                            <input type="date" class="form-control" style="width:200px;" wire:model.defer="expiry_date">
                            @error('expiry_date')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                    </div>

                    <button class="btn-outline bg-dark text-white"
                            wire:click="generateCards"
                            wire:loading.attr="disabled"
                            @if(!$filtered || $students->isEmpty()) disabled @endif>
                        <span wire:loading wire:target="generateCards" class="spinner-border spinner-border-sm"></span>
                        <i class="bi bi-printer" wire:loading.remove wire:target="generateCards"></i>
                        Generate
                    </button>
                </div>
            </div>

            <div class="card-body pt-0">
                @if($students->isNotEmpty())
                <div style="font-size:.8rem;color:#282c34;margin-bottom:10px;">
                    <span style="color:var(--primary);font-weight:600;">{{ count($selectedIds) }}</span> of
                    <span style="color:#697381;font-weight:600;">{{ $students->count() }}</span> students selected
                </div>
                @endif
                <div class="table-responsive">
                    <table class="mat-table">
                        <thead>
                            <tr>
                                <th class="check-col">
                                    <input type="checkbox" class="red-check" wire:model.live="selectAll"
                                        @if($students->isEmpty()) disabled @endif>
                                </th>
                                <th>SL</th>
                                <th>Student Name</th>
                                <th>Class</th>
                                <th>Section</th>
                                <th>Group</th>
                                <th>Register No</th>
                                <th>Roll No</th>
                                <th>Mobile No</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($students as $i => $student)
                            <tr>
                                <td class="check-col">
                                    <input type="checkbox"
                                        class="red-check"
                                        wire:model.live="selectedIds"
                                        value="{{ $student->id }}">
                                </td>
                                <td class="text-muted">{{ $i + 1 }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        @if($student->logo_path)
                                            <img src="{{ asset($student->logo_path) }}" class="avatar" alt="">
                                        @else
                                            <div class="avatar-placeholder">{{ strtoupper(substr($student->name, 0, 1)) }}</div>
                                        @endif
                                        <div>
                                            <div class="fw-500 text-dark">{{ $student->full_name }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $student->class?->name }}</td>
                                <td>{{ $student->section?->name }}</td>
                                <td>{{ $student->group?->name }}</td>
                                <td>{{ $student->register_no }}</td>
                                <td>{{ $student->roll_no }}</td>
                                <td>{{ $student->mobile }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox display-5 d-block mb-2 opacity-25"></i>
                                    No students found.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        @else
            <div class="gen-empty">
                <i class="bi bi-funnel"></i>
                <p>Please select Class, Section and Template, then click <strong>Filter</strong> to load students.</p>
            </div>
        @endif
    </div>

    {{-- ===== PRINT PREVIEW MODAL ===== --}}
    @if($showPrintPreview)
    <div class="modal fade show d-block no-print" tabindex="-1" style="background:rgba(0,0,0,.75);">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-printer me-2 text-danger"></i>
                        Admit Card Print Preview
                        <span class="badge bg-secondary ms-2" style="font-size:.7rem;">{{ count($printCards) }} Cards</span>
                    </h5>
                    <button class="btn-close" wire:click="$set('showPrintPreview', false)"></button>
                </div>

                <div class="modal-body">
                    @foreach($printCards as $card)
                    @php
                        // ── Template fields come from $selectedTemplate (Eloquent model) ──
                        // $card is a plain array (->toArray()) — never use $card->property
                        $tmpl        = $selectedTemplate;
                        $accentColor = $tmpl?->accent_color   ?? '#007bff';
                        $showPhoto   = $tmpl?->show_photo     ?? true;
                        $showSig     = $tmpl?->show_signature ?? true;
                        $footerText  = $tmpl?->footer_text    ?? "Controller's Signature";
                        $instructions= $tmpl?->instructions   ?? '';

                        // ── EXAM SCHEDULE ──────────────────────────────────────────────────
                        // Stored as JSON in admit_cards.exam_schedules.
                        // AdmitCard casts it to array, so after ->toArray() it becomes a
                        // nested PHP array: $card['exam_schedules'] = [ [...], [...] ]
                        //
                        // Each row keys (from exam_schedules.data JSON):
                        //   subject | exam_date | start_time | duration | full_marks
                        // ──────────────────────────────────────────────────────────────────
                        $examSchedule = $card['exam_schedules'] ?? [];
                        // Safety: if it somehow came back as a JSON string, decode it
                        if (is_string($examSchedule)) {
                            $examSchedule = json_decode($examSchedule, true) ?? [];
                        }
                    @endphp

                    <div style="background:#f3f4f8; padding:20px; border-bottom:1px solid #e5e7eb;">

                        {{-- ── Admit Card Paper ── --}}
                        <div style="
                            background:{{ $tmpl?->background_color ?? '#fff' }};
                            color:{{ $tmpl?->text_color ?? '#000' }};
                            border-radius:10px;
                            overflow:hidden;
                            box-shadow:0 4px 20px rgba(0,0,0,.12);
                            max-width:560px;
                            margin:0 auto;
                            font-family:'Inter',sans-serif;
                            border:1px solid #e5e7eb;
                        ">
                            {{-- ── Header Band ── --}}
                            <div style="
                                background:{{ $accentColor }};
                                padding:16px 20px;
                                display:flex;
                                align-items:center;
                                gap:14px;
                            ">
                                @if($tmpl?->logo_path)
                                    <img src="{{ asset($tmpl->logo_path) }}"
                                        style="height:44px;border-radius:6px;background:#fff;padding:2px;">
                                @else
                                    <div style="
                                        width:44px;height:44px;border-radius:8px;
                                        background:rgba(255,255,255,.2);
                                        display:flex;align-items:center;justify-content:center;
                                    ">
                                        <i class="bi bi-building" style="font-size:1.3rem;color:#fff;"></i>
                                    </div>
                                @endif
                                <div>
                                    <div style="color:#fff;font-weight:700;font-size:.95rem;line-height:1.2;">
                                        {{ $tmpl?->header_text ?: ($card['institute_name'] ?? 'Institute Name') }}
                                    </div>
                                    <div style="color:rgba(255,255,255,.75);font-size:.68rem;letter-spacing:.06em;margin-top:2px;">
                                        ADMIT CARD
                                    </div>
                                </div>
                            </div>

                            {{-- ── Card Body ── --}}
                            <div style="padding:16px 20px;display:flex;gap:16px;">

                                {{-- Photo --}}
                                @if($showPhoto)
                                <div style="flex-shrink:0;">
                                    <div style="
                                        width:80px;height:96px;border-radius:8px;
                                        border:2px dashed {{ $accentColor }};
                                        overflow:hidden;
                                        display:flex;flex-direction:column;
                                        align-items:center;justify-content:center;
                                        gap:4px;background:rgba(0,0,0,.03);
                                    ">
                                        @if(!empty($card['photo']))
                                            <img src="{{ asset($card['photo']) }}"
                                                style="width:100%;height:100%;object-fit:cover;">
                                        @else
                                            <i class="bi bi-person" style="font-size:1.8rem;color:{{ $accentColor }};opacity:.4;"></i>
                                            <span style="font-size:.58rem;color:{{ $accentColor }};opacity:.5;">PHOTO</span>
                                        @endif
                                    </div>
                                </div>
                                @endif

                                {{-- Student info — all from $card array --}}
                                <div style="flex:1;min-width:0;">
                                    <table style="width:100%;font-size:.73rem;border-collapse:collapse;">
                                        <tr>
                                            <td style="padding:3px 0;color:rgba(0,0,0,.45);width:40%;">Student Name</td>
                                            <td style="padding:3px 0;font-weight:600;">{{ $card['name'] ?? '—' }}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding:3px 0;color:rgba(0,0,0,.45);">Roll Number</td>
                                            <td style="padding:3px 0;font-weight:600;color:{{ $accentColor }};">{{ $card['roll_no'] ?? '—' }}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding:3px 0;color:rgba(0,0,0,.45);">Register No</td>
                                            <td style="padding:3px 0;">{{ $card['register_no'] ?? '—' }}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding:3px 0;color:rgba(0,0,0,.45);">Class / Section</td>
                                            <td style="padding:3px 0;">{{ $card['class'] ?? '—' }} / {{ $card['section'] ?? '—' }}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding:3px 0;color:rgba(0,0,0,.45);">Date of Birth</td>
                                            <td style="padding:3px 0;">
                                                {{ !empty($card['dob']) ? \Carbon\Carbon::parse($card['dob'])->format('d M Y') : '—' }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding:3px 0;color:rgba(0,0,0,.45);">Mobile</td>
                                            <td style="padding:3px 0;">{{ $card['mobile'] ?? '—' }}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding:3px 0;color:rgba(0,0,0,.45);">Blood Group</td>
                                            <td style="padding:3px 0;color:#dc2626;font-weight:600;">{{ $card['blood_group'] ?? '—' }}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding:3px 0;color:rgba(0,0,0,.45);">Session</td>
                                            <td style="padding:3px 0;">{{ $card['session'] ?? '—' }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            {{-- ── EXAM SCHEDULE ──
                                 Source: $card['exam_schedules'] (cast array from admit_cards table)
                                 Original data stored from exam_schedules.data JSON column.
                                 Keys per row: subject | exam_date | start_time | duration | full_marks
                            --}}
                            <div style="margin:0 20px 14px;">
                                <div style="
                                    background:{{ $accentColor }};
                                    color:#fff;
                                    font-size:.65rem;
                                    font-weight:700;
                                    padding:5px 10px;
                                    border-radius:5px 5px 0 0;
                                    letter-spacing:.05em;
                                ">EXAM SCHEDULE</div>

                                <div style="border:1px solid rgba(0,0,0,.1);border-top:none;border-radius:0 0 5px 5px;overflow:hidden;">

                                    {{-- Table header --}}
                                    <div style="display:flex;font-size:.62rem;font-weight:700;
                                                background:rgba(0,0,0,.04);border-bottom:1px solid rgba(0,0,0,.1);">
                                        <div style="width:30%;padding:5px 8px;color:rgba(0,0,0,.6);">Subject</div>
                                        <div style="width:24%;padding:5px 8px;color:rgba(0,0,0,.6);border-left:1px solid rgba(0,0,0,.07);">Date</div>
                                        <div style="width:18%;padding:5px 8px;color:rgba(0,0,0,.6);border-left:1px solid rgba(0,0,0,.07);">Time</div>
                                        <div style="width:14%;padding:5px 8px;color:rgba(0,0,0,.6);border-left:1px solid rgba(0,0,0,.07);">Duration</div>
                                        <div style="width:14%;padding:5px 8px;color:rgba(0,0,0,.6);border-left:1px solid rgba(0,0,0,.07);">Marks</div>
                                    </div>

                                    @forelse($examSchedule as $si => $row)
                                    <div style="
                                        display:flex;
                                        font-size:.65rem;
                                        border-bottom:{{ !$loop->last ? '1px solid rgba(0,0,0,.07)' : 'none' }};
                                        background:{{ $si % 2 === 1 ? 'rgba(0,0,0,.025)' : 'transparent' }};
                                    ">
                                        {{-- DB stores key as 'subject' (from image), not 'subject_name' --}}
                                        <div style="width:30%;padding:5px 8px;color:rgba(0,0,0,.75);font-weight:500;">
                                            {{ $row['subject'] ?? $row['subject_name'] ?? '—' }}
                                        </div>
                                        <div style="width:24%;padding:5px 8px;color:rgba(0,0,0,.5);border-left:1px solid rgba(0,0,0,.07);">
                                            {{ !empty($row['exam_date']) ? \Carbon\Carbon::parse($row['exam_date'])->format('d M Y') : '—' }}
                                        </div>
                                        <div style="width:18%;padding:5px 8px;color:rgba(0,0,0,.5);border-left:1px solid rgba(0,0,0,.07);">
                                            {{ $row['start_time'] ?? '—' }}
                                        </div>
                                        <div style="width:14%;padding:5px 8px;color:rgba(0,0,0,.5);border-left:1px solid rgba(0,0,0,.07);">
                                            {{ $row['duration'] ?? '—' }}
                                        </div>
                                        <div style="width:14%;padding:5px 8px;color:rgba(0,0,0,.5);border-left:1px solid rgba(0,0,0,.07);">
                                            {{ $row['full_marks'] ?? '—' }}
                                        </div>
                                    </div>
                                    @empty
                                    <div style="padding:10px 12px;font-size:.68rem;color:rgba(0,0,0,.4);text-align:center;">
                                        No exam schedule found for this exam.
                                    </div>
                                    @endforelse
                                </div>
                            </div>

                            {{-- ── Instructions (from template) ── --}}
                            @if($instructions)
                            <div style="
                                margin:0 20px 14px;
                                background:rgba(255,235,59,.15);
                                border:1px solid rgba(255,193,7,.3);
                                border-radius:6px;
                                padding:10px 12px;
                                font-size:.68rem;
                                color:rgba(0,0,0,.65);
                                line-height:1.6;
                            ">
                                <strong style="display:block;margin-bottom:4px;color:rgba(0,0,0,.75);">
                                    <i class="bi bi-info-circle me-1"></i>Instructions:
                                </strong>
                                {{ Str::limit($instructions, 180) }}
                            </div>
                            @endif

                            {{-- ── Footer ── --}}
                            <div style="
                                padding:10px 20px;
                                border-top:1px solid rgba(0,0,0,.08);
                                display:flex;
                                align-items:center;
                                justify-content:space-between;
                            ">
                                {{-- Validity dates --}}
                                <div style="font-size:.62rem;color:rgba(0,0,0,.45);line-height:1.8;">
                                    <div>Issue: {{ !empty($card['issue_date']) ? \Carbon\Carbon::parse($card['issue_date'])->format('d M Y') : '—' }}</div>
                                    <div>Valid Till: {{ !empty($card['expiry_date']) ? \Carbon\Carbon::parse($card['expiry_date'])->format('d M Y') : '—' }}</div>
                                </div>

                                @if($showSig)
                                <div style="text-align:center;">
                                    <div style="width:90px;border-bottom:1px solid rgba(0,0,0,.25);margin-bottom:3px;"></div>
                                    <div style="font-size:.6rem;color:rgba(0,0,0,.4);">Candidate's Signature</div>
                                </div>
                                @endif

                                <div style="text-align:center;">
                                    <div style="width:90px;border-bottom:1px solid rgba(0,0,0,.25);margin-bottom:3px;"></div>
                                    <div style="font-size:.6rem;color:rgba(0,0,0,.4);">{{ $footerText }}</div>
                                </div>
                            </div>

                        </div>{{-- /card paper --}}
                    </div>
                    @endforeach
                </div>{{-- /modal-body --}}

                <div class="modal-footer">
                    <button class="btn btn-outline-secondary border-secondary"
                            wire:click="$set('showPrintPreview', false)">
                        <i class="bi bi-x-lg me-1"></i>Close
                    </button>
                    <button class="btn bg-dark text-white" onclick="window.print()">
                        <i class="bi bi-printer me-1"></i>Print All Cards
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Print-only layout ── --}}
    <div class="print-section">
        <style>
            @media print {
                body { background: #fff !important; }
                .no-print { display: none !important; }
                .print-section { display: block !important; }
                .print-cards-grid {
                    display: flex;
                    flex-wrap: wrap;
                    gap: 16px;
                    padding: 16px;
                }
            }
        </style>
        <div class="print-cards-grid">
            @foreach($printCards as $card)
            @php
                $tmpl         = $selectedTemplate;
                $accentColor  = $tmpl?->accent_color   ?? '#007bff';
                $showPhoto    = $tmpl?->show_photo      ?? true;
                $showSig      = $tmpl?->show_signature  ?? true;
                $footerText   = $tmpl?->footer_text     ?? "Controller's Signature";
                $instructions = $tmpl?->instructions    ?? '';

                $printSchedule = $card['exam_schedules'] ?? [];
                if (is_string($printSchedule)) {
                    $printSchedule = json_decode($printSchedule, true) ?? [];
                }
            @endphp

            <div class="id-card-print" style="
                background: {{ $tmpl?->background_color ?? '#fff' }};
                color: {{ $tmpl?->text_color ?? '#000' }};
                border-radius: 10px;
                overflow: hidden;
                box-shadow: 0 4px 20px rgba(0,0,0,.12);
                width: 560px;
                font-family: 'Inter', sans-serif;
                border: 1px solid #e5e7eb;
                page-break-inside: avoid;
            ">

                {{-- ── Header Band ── --}}
                <div style="
                    background: {{ $accentColor }};
                    padding: 16px 20px;
                    display: flex;
                    align-items: center;
                    gap: 14px;
                ">
                    @if($tmpl?->logo_path)
                        <img src="{{ asset($tmpl->logo_path) }}"
                            style="height:44px;border-radius:6px;background:#fff;padding:2px;">
                    @else
                        <div style="
                            width:44px;height:44px;border-radius:8px;
                            background:rgba(255,255,255,.2);
                            display:flex;align-items:center;justify-content:center;
                        ">
                            <i class="bi bi-building" style="font-size:1.3rem;color:#fff;"></i>
                        </div>
                    @endif
                    <div>
                        <div style="color:#fff;font-weight:700;font-size:.95rem;line-height:1.2;">
                            {{ $tmpl?->header_text ?: ($card['institute_name'] ?? 'Institute Name') }}
                        </div>
                        <div style="color:rgba(255,255,255,.75);font-size:.68rem;letter-spacing:.06em;margin-top:2px;">
                            ADMIT CARD
                        </div>
                    </div>
                </div>

                {{-- ── Card Body ── --}}
                <div style="padding:16px 20px;display:flex;gap:16px;">

                    {{-- Photo --}}
                    @if($showPhoto)
                    <div style="flex-shrink:0;">
                        <div style="
                            width:80px;height:96px;border-radius:8px;
                            border:2px dashed {{ $accentColor }};
                            overflow:hidden;
                            display:flex;flex-direction:column;
                            align-items:center;justify-content:center;
                            gap:4px;background:rgba(0,0,0,.03);
                        ">
                            @if(!empty($card['photo']))
                                <img src="{{ asset($card['photo']) }}"
                                    style="width:100%;height:100%;object-fit:cover;">
                            @else
                                <i class="bi bi-person" style="font-size:1.8rem;color:{{ $accentColor }};opacity:.4;"></i>
                                <span style="font-size:.58rem;color:{{ $accentColor }};opacity:.5;">PHOTO</span>
                            @endif
                        </div>
                    </div>
                    @endif

                    {{-- Student Info --}}
                    <div style="flex:1;min-width:0;">
                        <table style="width:100%;font-size:.73rem;border-collapse:collapse;">
                            <tr>
                                <td style="padding:3px 0;color:rgba(0,0,0,.45);width:40%;">Student Name</td>
                                <td style="padding:3px 0;font-weight:600;">{{ $card['name'] ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td style="padding:3px 0;color:rgba(0,0,0,.45);">Roll Number</td>
                                <td style="padding:3px 0;font-weight:600;color:{{ $accentColor }};">{{ $card['roll_no'] ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td style="padding:3px 0;color:rgba(0,0,0,.45);">Register No</td>
                                <td style="padding:3px 0;">{{ $card['register_no'] ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td style="padding:3px 0;color:rgba(0,0,0,.45);">Class / Section</td>
                                <td style="padding:3px 0;">{{ $card['class'] ?? '—' }} / {{ $card['section'] ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td style="padding:3px 0;color:rgba(0,0,0,.45);">Date of Birth</td>
                                <td style="padding:3px 0;">
                                    {{ !empty($card['dob']) ? \Carbon\Carbon::parse($card['dob'])->format('d M Y') : '—' }}
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:3px 0;color:rgba(0,0,0,.45);">Mobile</td>
                                <td style="padding:3px 0;">{{ $card['mobile'] ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td style="padding:3px 0;color:rgba(0,0,0,.45);">Blood Group</td>
                                <td style="padding:3px 0;color:#dc2626;font-weight:600;">{{ $card['blood_group'] ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td style="padding:3px 0;color:rgba(0,0,0,.45);">Session</td>
                                <td style="padding:3px 0;">{{ $card['session'] ?? '—' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                {{-- ── Exam Schedule ── --}}
                <div style="margin:0 20px 14px;">
                    <div style="
                        background: {{ $accentColor }};
                        color:#fff;font-size:.65rem;font-weight:700;
                        padding:5px 10px;border-radius:5px 5px 0 0;
                        letter-spacing:.05em;
                    ">EXAM SCHEDULE</div>

                    <div style="border:1px solid rgba(0,0,0,.1);border-top:none;border-radius:0 0 5px 5px;overflow:hidden;">

                        {{-- Table Header --}}
                        <div style="display:flex;font-size:.62rem;font-weight:700;
                                    background:rgba(0,0,0,.04);border-bottom:1px solid rgba(0,0,0,.1);">
                            <div style="width:30%;padding:5px 8px;color:rgba(0,0,0,.6);">Subject</div>
                            <div style="width:24%;padding:5px 8px;color:rgba(0,0,0,.6);border-left:1px solid rgba(0,0,0,.07);">Date</div>
                            <div style="width:18%;padding:5px 8px;color:rgba(0,0,0,.6);border-left:1px solid rgba(0,0,0,.07);">Time</div>
                            <div style="width:14%;padding:5px 8px;color:rgba(0,0,0,.6);border-left:1px solid rgba(0,0,0,.07);">Duration</div>
                            <div style="width:14%;padding:5px 8px;color:rgba(0,0,0,.6);border-left:1px solid rgba(0,0,0,.07);">Marks</div>
                        </div>

                        @forelse($printSchedule as $si => $row)
                        <div style="
                            display:flex;font-size:.65rem;
                            border-bottom:{{ !$loop->last ? '1px solid rgba(0,0,0,.07)' : 'none' }};
                            background:{{ $si % 2 === 1 ? 'rgba(0,0,0,.025)' : 'transparent' }};
                        ">
                            <div style="width:30%;padding:5px 8px;color:rgba(0,0,0,.75);font-weight:500;">
                                {{ $row['subject'] ?? $row['subject_name'] ?? '—' }}
                            </div>
                            <div style="width:24%;padding:5px 8px;color:rgba(0,0,0,.5);border-left:1px solid rgba(0,0,0,.07);">
                                {{ !empty($row['exam_date']) ? \Carbon\Carbon::parse($row['exam_date'])->format('d M Y') : '—' }}
                            </div>
                            <div style="width:18%;padding:5px 8px;color:rgba(0,0,0,.5);border-left:1px solid rgba(0,0,0,.07);">
                                {{ $row['start_time'] ?? '—' }}
                            </div>
                            <div style="width:14%;padding:5px 8px;color:rgba(0,0,0,.5);border-left:1px solid rgba(0,0,0,.07);">
                                {{ $row['duration'] ?? '—' }}
                            </div>
                            <div style="width:14%;padding:5px 8px;color:rgba(0,0,0,.5);border-left:1px solid rgba(0,0,0,.07);">
                                {{ $row['full_marks'] ?? '—' }}
                            </div>
                        </div>
                        @empty
                        <div style="padding:10px 12px;font-size:.68rem;color:rgba(0,0,0,.4);text-align:center;">
                            No exam schedule found for this exam.
                        </div>
                        @endforelse
                    </div>
                </div>

                {{-- ── Instructions ── --}}
                @if($instructions)
                <div style="
                    margin:0 20px 14px;
                    background:rgba(255,235,59,.15);
                    border:1px solid rgba(255,193,7,.3);
                    border-radius:6px;padding:10px 12px;
                    font-size:.68rem;color:rgba(0,0,0,.65);line-height:1.6;
                ">
                    <strong style="display:block;margin-bottom:4px;color:rgba(0,0,0,.75);">
                        <i class="bi bi-info-circle me-1"></i>Instructions:
                    </strong>
                    {{ Str::limit($instructions, 180) }}
                </div>
                @endif

                {{-- ── Footer ── --}}
                <div style="
                    padding:10px 20px;
                    border-top:1px solid rgba(0,0,0,.08);
                    display:flex;align-items:center;justify-content:space-between;
                ">
                    <div style="font-size:.62rem;color:rgba(0,0,0,.45);line-height:1.8;">
                        <div>Issue: {{ !empty($card['issue_date']) ? \Carbon\Carbon::parse($card['issue_date'])->format('d M Y') : '—' }}</div>
                        <div>Valid Till: {{ !empty($card['expiry_date']) ? \Carbon\Carbon::parse($card['expiry_date'])->format('d M Y') : '—' }}</div>
                    </div>

                    @if($showSig)
                    <div style="text-align:center;">
                        <div style="width:90px;border-bottom:1px solid rgba(0,0,0,.25);margin-bottom:3px;"></div>
                        <div style="font-size:.6rem;color:rgba(0,0,0,.4);">Candidate's Signature</div>
                    </div>
                    @endif

                    <div style="text-align:center;">
                        <div style="width:90px;border-bottom:1px solid rgba(0,0,0,.25);margin-bottom:3px;"></div>
                        <div style="font-size:.6rem;color:rgba(0,0,0,.4);">{{ $footerText }}</div>
                    </div>
                </div>

            </div>{{-- /id-card-print --}}
            @endforeach
        </div>
    </div>
    @endif

</div>
@push('styles')
    <style>
        :root {
            --primary: rgba(33, 37, 41);
            --primary-light: rgba(239,84,84,.12);
        }

        .card { border: 1px solid var(--border); border-radius: 12px; box-shadow: 0 1px 4px rgba(0,0,0,.04); }
        .card-header { background: #fff; border-bottom: 1px solid var(--border); border-radius: 12px 12px 0 0 !important; padding: 16px 20px; }
        .card-header .card-title { font-size: .95rem; font-weight: 600; margin: 0; }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-light);
        }

        .btn-sm { font-size: .78rem; padding: .3rem .65rem; border-radius: 6px; }

    </style>
@endpush
@push('styles')
<style>
    .gen-section {
        background: #fbfcff;
        border-radius: 10px;
        border: 1px solid #2e3a4e;
        margin-bottom: 20px;
        overflow: hidden;
    }
    .gen-section-header {
        padding: 14px 20px;
        border-bottom: 2px solid var(--primary);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .gen-section-header h6 {
        color: #020202;
        font-weight: 600;
        font-size: .9rem;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .gen-section-body { padding: 20px; }

    .gen-label {
        font-size: .75rem;
        font-weight: 500;
        color: #9ca3af;
        margin-bottom: 6px;
        display: block;
    }
    .gen-label .req { color: var(--primary); }

    .gen-select, .gen-input {
        width: 100%;
        background: #1e2432;
        border: 1px solid #3a4558;
        color: #e2e8f0;
        border-radius: 8px;
        padding: 10px 14px;
        font-size: .875rem;
        appearance: none;
        -webkit-appearance: none;
        transition: border-color .2s;
        font-family: 'Inter', sans-serif;
    }
    .gen-select {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%239ca3af' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 12px center;
        padding-right: 36px;
        cursor: pointer;
    }
    .gen-select:focus, .gen-input:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(239,84,84,.15);
    }
    .gen-select option { background: #1e2432; color: #e2e8f0; }

    .btn-filter {
        background: #2e3a4e;
        border: 1px solid #3a4558;
        color: #e2e8f0;
        padding: 10px 20px;
        border-radius: 8px;
        font-size: .875rem;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all .2s;
    }
    .btn-filter:hover { background: #3a4558; border-color: var(--primary); color: var(--primary); }

    .btn-generate {
        background: var(--primary);
        border: none;
        color: #fff;
        padding: 8px 20px;
        border-radius: 8px;
        font-size: .875rem;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: background .2s;
    }
    .btn-generate:hover { background: #d63e3e; }
    .btn-generate:disabled { opacity: .6; cursor: not-allowed; }

    .date-row { display: flex; gap: 20px; margin-bottom: 16px; flex-wrap: wrap; }
    .date-field { display: flex; flex-direction: column; gap: 4px; }
    .date-label { font-size: .75rem; color: #9ca3af; }

    .gen-table { width: 100%; border-collapse: collapse; }
    .gen-table thead tr { border-bottom: 1px solid #2e3a4e; }
    .gen-table th {
        padding: 10px 14px;
        font-size: .72rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: #9ca3af;
        background: #1e2a3a;
    }
    .gen-table td {
        padding: 11px 14px;
        font-size: .85rem;
        color: #cbd5e1;
        border-bottom: 1px solid #2e3a4e;
    }
    .gen-table tbody tr:hover { background: rgba(255,255,255,.03); }
    .gen-table .check-col { width: 44px; text-align: center; }

    .red-check {
        width: 18px; height: 18px;
        accent-color: var(--primary);
        cursor: pointer;
        border-radius: 4px;
    }

    .gen-empty {
        text-align: center;
        padding: 48px 20px;
        color: #4b5563;
    }
    .gen-empty i { font-size: 3rem; display: block; margin-bottom: 12px; }

    .id-card-print {
        width: 325px;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0,0,0,.2);
        font-family: 'Inter', sans-serif;
        break-inside: avoid;
        display: inline-block;
        margin: 8px;
    }

    .print-section { display: none; }

    @media print {
        .no-print { display: none !important; }
        .print-section { display: block !important; }
        .id-card-print { box-shadow: none; border: 1px solid #e5e7eb; }
    }

    @media (max-width: 576px) {
        .date-row { flex-direction: column; }
        .gen-section-body .row > div { margin-bottom: 12px; }
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