<div>
    <div class="card">

        <!-- floating header -->
        <div class="mat-card-header header-pink-gradient">
            <h5 id="cardHeaderTitleAllsections">Admit Card Templates</h5>
            <p id="cardHeaderSubtitle">A lightweight, extendable, dependency-free javascript HTML table plugin.</p>
        </div>

        <div class="card-header border-0">
            <!-- toolbar -->
            <div class="card-toolbar">
                {{-- Left side --}}
                <div class="card-toolbar-title">
                    <!-- search in table -->
                    <div style="position:relative;display:inline-flex;align-items:center">
                        <span class="material-icons-round" style="position:absolute;left:10px;font-size:17px;color:var(--muted);pointer-events:none">search</span>
                        <input type="text" wire:model.live.debounce.300ms="search" id="tableSearch" placeholder="Search" style="border:1px solid rgba(0,0,0,.1);border-radius:8px;padding:7px 12px 7px 32px;font-size:.78rem;font-family:inherit;color:var(--dark);outline:none;background:#f8f9fa;width:220px"/>
                    </div>
                </div>

                <!-- Right Side -->
                <div class="col-md-3">
                    <select class="form-select form-select-sm" wire:model.live="filterExamType">
                        <option value="">All Exam Types</option>
                        @foreach($examTypes as $k => $v)
                            <option value="{{ $k }}">{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select form-select-sm" wire:model.live="filterStatus">
                        <option value="">All Status</option>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select form-select-sm" wire:model.live="perPage">
                        <option value="10">10 / page</option>
                        <option value="25">25 / page</option>
                        <option value="50">50 / page</option>
                    </select>
                </div>
                <button class="btn-outline bg-dark text-white" wire:click="openCreate">
                    <span class="material-icons-round">add</span> <span id="newSectionBtn">Add Template</span>
                </button>

            </div>
        </div>


        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th id="th-sl">SL</th>
                            <th id="th-template-name">Template Name</th>
                            <th id="th-exam-type">Exam Type</th>
                            <th id="th-colors">Colors</th>
                            <th id="th-features">Features</th>
                            <th id="th-status">Status</th>
                            <th id="th-created">Created</th>
                            <th id="th-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($templates as $i => $t)
                        <tr>
                            <td class="text-muted">{{ $templates->firstItem() + $i }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    @if($t->logo_path)
                                        <img src="{{ asset($t->logo_path) }}" class="avatar" alt="">
                                    @else
                                        <div class="avatar-placeholder">{{ strtoupper(substr($t->name,0,1)) }}</div>
                                    @endif
                                    <div>
                                        <div class="fw-500 text-dark">{{ $t->name }}</div>
                                        @if($t->header_text)<small class="text-muted">{{ Str::limit($t->header_text, 30) }}</small>@endif
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge bg-secondary-subtle text-secondary">{{ $examTypes[$t->exam_type] ?? ucfirst($t->exam_type) }}</span></td>
                            <td>
                                <div class="d-flex gap-1">
                                    <span title="BG" style="width:18px;height:18px;border-radius:4px;background:{{ $t->background_color }};border:1px solid #e5e7eb;display:inline-block;"></span>
                                    <span title="Accent" style="width:18px;height:18px;border-radius:4px;background:{{ $t->accent_color }};border:1px solid #e5e7eb;display:inline-block;"></span>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <span title="Background" style="width:18px;height:18px;border-radius:4px;background:{{ $t->background_color }};border:1px solid #e5e7eb;display:inline-block;"></span>
                                    <span title="Text" style="width:18px;height:18px;border-radius:4px;background:{{ $t->text_color }};border:1px solid #e5e7eb;display:inline-block;"></span>
                                    <span title="Accent" style="width:18px;height:18px;border-radius:4px;background:{{ $t->accent_color }};border:1px solid #e5e7eb;display:inline-block;"></span>
                                </div>
                            </td>
                            <td>
                                <span class="badge rounded-pill {{ $t->is_active ? 'badge-active' : 'badge-inactive' }}" style="font-size:.72rem;">
                                    {{ $t->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="text-muted" style="font-size:.8rem;">{{ $t->created_at->format('d M Y') }}</td>
                            <td>
                                <div class="d-flex gap-1">
                                    <button class="act-btn view" title="View" wire:click="openView({{ $t->id }})">
                                        <span class="material-icons-round">visibility</span>
                                    </button>
                                    <button class="act-btn edit" title="Edit" wire:click="openEdit({{ $t->id }})">
                                        <span class="material-icons-round">drive_file_rename_outline</span>
                                    </button>
                                    <button class="act-btn status {{ $t->is_active ? 'btn-warning' : 'btn-success' }}" title="Toggle Status" wire:click="toggleStatus({{ $t->id }})">
                                        <span class="material-icons-round">{{ $t->is_active ? 'toggle_off' : 'toggle_on' }}</span>
                                    </button>
                                    <button class="act-btn delete" title="Delete" wire:click="confirmDeleteRecord({{ $t->id }})">
                                        <span class="material-icons-round">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-file-earmark-text display-5 d-block mb-2 opacity-25"></i>
                                No admit card templates found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="card-footer border-0 bg-white d-flex align-items-center justify-content-between flex-wrap gap-2 py-2 px-3">
            <small class="text-muted">Showing {{ $templates->firstItem() ?? 0 }}–{{ $templates->lastItem() ?? 0 }} of {{ $templates->total() }}</small>
           {{ $templates->links('vendor.pagination.custom') }}
        </div>

    </div>

    {{-- ===== CREATE/EDIT MODAL ===== --}}
    @if($showModal)
    <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5);" wire:ignore.self>
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-file-earmark-text me-2 text-danger"></i>{{ $editId ? 'Edit' : 'Create' }} Admit Card Template</h5>
                    <button class="btn-close" wire:click="$set('showModal',false)"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Template Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" wire:model.defer="name" placeholder="e.g. Annual Exam 2025 Template">
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Exam Type</label>
                            <select class="form-select" wire:model.defer="exam_type">
                                @foreach($examTypes as $k => $v)
                                    <option value="{{ $k }}">{{ $v }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Background Color</label>
                            <div class="color-input-wrap">
                                <input type="color" wire:model.defer="background_color">
                                <input type="text" class="form-control" wire:model.defer="background_color">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Text Color</label>
                            <div class="color-input-wrap">
                                <input type="color" wire:model.defer="text_color">
                                <input type="text" class="form-control" wire:model.defer="text_color">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Accent Color</label>
                            <div class="color-input-wrap">
                                <input type="color" wire:model.defer="accent_color">
                                <input type="text" class="form-control" wire:model.defer="accent_color">
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Logo</label>
                            <input type="file" class="form-control @error('logo') is-invalid @enderror" wire:model="logo" accept="image/*">
                            @error('logo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            @if($existingLogo)
                                <img src="{{ asset($existingLogo) }}" height="40" class="mt-2 rounded">
                            @endif
                        </div>
                        <div class="col-12">
                            <label class="form-label">Header Text</label>
                            <input type="text" class="form-control" wire:model.defer="header_text" placeholder="Institute name shown at top">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Exam Instructions</label>
                            <textarea class="form-control" rows="4" wire:model.defer="instructions"
                                placeholder="1. Candidates must report 30 minutes before the exam.&#10;2. Mobile phones are not allowed..."></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Footer Text</label>
                            <input type="text" class="form-control" wire:model.defer="footer_text" placeholder="Signature / authority text">
                        </div>
                        <div class="col-12">
                            <label class="form-label d-block mb-2">Features</label>
                            <div class="d-flex gap-3 flex-wrap">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" wire:model.defer="show_photo" id="ac_show_photo">
                                    <label class="form-check-label" for="ac_show_photo">Show Photo</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" wire:model.defer="show_signature" id="ac_show_sig">
                                    <label class="form-check-label" for="ac_show_sig">Show Signature</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" wire:model.defer="show_barcode" id="ac_show_bar">
                                    <label class="form-check-label" for="ac_show_bar">Show Barcode</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" wire:model.defer="is_active" id="ac_active">
                                    <label class="form-check-label" for="ac_active">Active</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-light" wire:click="$set('showModal',false)">Cancel</button>
                    <button class="btn btn-primary" wire:click="save" wire:loading.attr="disabled">
                        <span wire:loading wire:target="save" class="spinner-border spinner-border-sm me-1"></span>
                        {{ $editId ? 'Update' : 'Create' }} Template
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ===== VIEW MODAL ===== --}}
    @if($showViewModal && $viewRecord)
    <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5);" wire:ignore.self>
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">

                {{-- Header --}}
                <div class="modal-header" style="border-bottom:1px solid #e5e7eb;">
                    <h5 class="modal-title d-flex align-items-center gap-2">
                        <i class="bi bi-file-earmark-text text-danger"></i>
                        Admit Card Template Details
                    </h5>
                    <button class="btn-close" wire:click="$set('showViewModal', false)"></button>
                </div>

                <div class="modal-body p-0">

                    {{-- ── Admit Card Preview ── --}}
                    <div style="background:#f3f4f8; padding:20px; border-bottom:1px solid #e5e7eb;">
                        <p style="font-size:.7rem;font-weight:600;text-transform:uppercase;letter-spacing:.08em;color:#9ca3af;margin-bottom:12px;">
                            PREVIEW
                        </p>

                        {{-- Admit Card Paper --}}
                        <div style="
                            background:{{ $viewRecord->background_color }};
                            color:{{ $viewRecord->text_color }};
                            border-radius:10px;
                            overflow:hidden;
                            box-shadow:0 4px 20px rgba(0,0,0,.12);
                            max-width:560px;
                            margin:0 auto;
                            font-family:'Inter',sans-serif;
                            border:1px solid #e5e7eb;
                        ">
                            {{-- Card Header Band --}}
                            <div style="
                                background:{{ $viewRecord->accent_color }};
                                padding:16px 20px;
                                display:flex;
                                align-items:center;
                                gap:14px;
                            ">
                                @if($viewRecord->logo_path)
                                    <img src="{{ asset($viewRecord->logo_path) }}"
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
                                        {{ $viewRecord->header_text ?: 'Institute Name Here' }}
                                    </div>
                                    <div style="color:rgba(255,255,255,.75);font-size:.68rem;letter-spacing:.06em;margin-top:2px;">
                                        ADMIT CARD &nbsp;·&nbsp; {{ strtoupper(\App\Models\AdmitCardTemplate::getExamTypes()[$viewRecord->exam_type] ?? $viewRecord->exam_type) }}
                                    </div>
                                </div>
                            </div>

                            {{-- Card Body --}}
                            <div style="padding:16px 20px;display:flex;gap:16px;">

                                {{-- Photo placeholder --}}
                                @if($viewRecord->show_photo)
                                <div style="flex-shrink:0;">
                                    <div style="
                                        width:80px;height:96px;border-radius:8px;
                                        border:2px dashed {{ $viewRecord->accent_color }};
                                        display:flex;flex-direction:column;
                                        align-items:center;justify-content:center;
                                        gap:4px;background:rgba(0,0,0,.03);
                                    ">
                                        <i class="bi bi-person" style="font-size:1.8rem;color:{{ $viewRecord->accent_color }};opacity:.4;"></i>
                                        <span style="font-size:.58rem;color:{{ $viewRecord->accent_color }};opacity:.5;">PHOTO</span>
                                    </div>
                                </div>
                                @endif

                                {{-- Info fields --}}
                                <div style="flex:1;min-width:0;">
                                    <table style="width:100%;font-size:.73rem;border-collapse:collapse;">
                                        <tr>
                                            <td style="padding:3px 0;color:rgba(0,0,0,.45);width:38%;">Student Name</td>
                                            <td style="padding:3px 0;">
                                                <div style="height:10px;border-radius:3px;background:rgba(0,0,0,.07);width:85%;"></div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding:3px 0;color:rgba(0,0,0,.45);">Roll Number</td>
                                            <td style="padding:3px 0;">
                                                <div style="height:10px;border-radius:3px;background:{{ $viewRecord->accent_color }};opacity:.25;width:55%;"></div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding:3px 0;color:rgba(0,0,0,.45);">Father's Name</td>
                                            <td style="padding:3px 0;">
                                                <div style="height:10px;border-radius:3px;background:rgba(0,0,0,.07);width:70%;"></div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding:3px 0;color:rgba(0,0,0,.45);">Class / Section</td>
                                            <td style="padding:3px 0;">
                                                <div style="height:10px;border-radius:3px;background:rgba(0,0,0,.07);width:45%;"></div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding:3px 0;color:rgba(0,0,0,.45);">Exam Center</td>
                                            <td style="padding:3px 0;">
                                                <div style="height:10px;border-radius:3px;background:rgba(0,0,0,.07);width:75%;"></div>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            {{-- Subject Schedule mini preview --}}
                            <div style="margin:0 20px 14px;">
                                <div style="
                                    background:{{ $viewRecord->accent_color }};
                                    color:#fff;
                                    font-size:.65rem;
                                    font-weight:700;
                                    padding:5px 10px;
                                    border-radius:5px 5px 0 0;
                                    letter-spacing:.05em;
                                ">EXAM SCHEDULE</div>
                                <div style="border:1px solid rgba(0,0,0,.1);border-top:none;border-radius:0 0 5px 5px;overflow:hidden;">
                                    @foreach(['Subject Name', 'Another Subject', 'Third Subject'] as $si => $subj)
                                    <div style="
                                        display:flex;gap:0;
                                        font-size:.65rem;
                                        border-bottom:{{ $si < 2 ? '1px solid rgba(0,0,0,.07)' : 'none' }};
                                        background:{{ $si % 2 === 1 ? 'rgba(0,0,0,.025)' : 'transparent' }};
                                    ">
                                        <div style="width:28%;padding:5px 8px;color:rgba(0,0,0,.7);font-weight:500;">{{ $subj }}</div>
                                        <div style="width:22%;padding:5px 8px;color:rgba(0,0,0,.4);border-left:1px solid rgba(0,0,0,.07);">dd Mon YYYY</div>
                                        <div style="width:20%;padding:5px 8px;color:rgba(0,0,0,.4);border-left:1px solid rgba(0,0,0,.07);">10:00 AM</div>
                                        <div style="width:16%;padding:5px 8px;color:rgba(0,0,0,.4);border-left:1px solid rgba(0,0,0,.07);">3 hrs</div>
                                        <div style="width:14%;padding:5px 8px;color:rgba(0,0,0,.4);border-left:1px solid rgba(0,0,0,.07);">100</div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Instructions --}}
                            @if($viewRecord->instructions)
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
                                {{ Str::limit($viewRecord->instructions, 180) }}
                            </div>
                            @endif

                            {{-- Footer --}}
                            <div style="
                                padding:10px 20px;
                                border-top:1px solid rgba(0,0,0,.08);
                                display:flex;
                                align-items:center;
                                justify-content:space-between;
                            ">
                                {{-- Candidate Signature placeholder --}}
                                @if($viewRecord->show_signature)
                                <div style="text-align:center;">
                                    <div style="width:90px;border-bottom:1px solid rgba(0,0,0,.25);margin-bottom:3px;"></div>
                                    <div style="font-size:.6rem;color:rgba(0,0,0,.4);">Candidate's Signature</div>
                                </div>
                                @endif

                                {{-- Authority Signature --}}
                                <div style="text-align:center;">
                                    <div style="width:90px;border-bottom:1px solid rgba(0,0,0,.25);margin-bottom:3px;"></div>
                                    <div style="font-size:.6rem;color:rgba(0,0,0,.4);">
                                        {{ $viewRecord->footer_text ?: "Controller's Signature" }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ── Details Table ── --}}
                    <div style="padding:20px;">
                        <p style="font-size:.7rem;font-weight:600;text-transform:uppercase;letter-spacing:.08em;color:#9ca3af;margin-bottom:12px;">
                            TEMPLATE DETAILS
                        </p>

                        <div class="row g-3">
                            {{-- Left column --}}
                            <div class="col-md-6">
                                <table style="width:100%;font-size:.85rem;border-collapse:collapse;">
                                    <tr style="border-bottom:1px solid #f3f4f8;">
                                        <td style="padding:9px 0;color:#6b7280;width:45%;font-weight:500;">Template Name</td>
                                        <td style="padding:9px 0;font-weight:600;color:#1e2432;">{{ $viewRecord->name }}</td>
                                    </tr>
                                    <tr style="border-bottom:1px solid #f3f4f8;">
                                        <td style="padding:9px 0;color:#6b7280;font-weight:500;">Exam Type</td>
                                        <td style="padding:9px 0;">
                                            <span style="
                                                background:rgba(59,130,246,.1);
                                                color:#2563eb;
                                                padding:2px 10px;
                                                border-radius:20px;
                                                font-size:.78rem;
                                                font-weight:600;
                                            ">
                                                {{ \App\Models\AdmitCardTemplate::getExamTypes()[$viewRecord->exam_type] ?? ucfirst($viewRecord->exam_type) }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr style="border-bottom:1px solid #f3f4f8;">
                                        <td style="padding:9px 0;color:#6b7280;font-weight:500;">Status</td>
                                        <td style="padding:9px 0;">
                                            @if($viewRecord->is_active)
                                                <span style="background:rgba(34,197,94,.12);color:#16a34a;padding:2px 10px;border-radius:20px;font-size:.78rem;font-weight:600;">
                                                    <i class="bi bi-check-circle-fill me-1"></i>Active
                                                </span>
                                            @else
                                                <span style="background:rgba(107,114,128,.12);color:#6b7280;padding:2px 10px;border-radius:20px;font-size:.78rem;font-weight:600;">
                                                    <i class="bi bi-pause-circle-fill me-1"></i>Inactive
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr style="border-bottom:1px solid #f3f4f8;">
                                        <td style="padding:9px 0;color:#6b7280;font-weight:500;">Created</td>
                                        <td style="padding:9px 0;color:#374151;">{{ $viewRecord->created_at->format('d M Y, h:i A') }}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding:9px 0;color:#6b7280;font-weight:500;">Last Updated</td>
                                        <td style="padding:9px 0;color:#374151;">{{ $viewRecord->updated_at->format('d M Y, h:i A') }}</td>
                                    </tr>
                                </table>
                            </div>

                            {{-- Right column --}}
                            <div class="col-md-6">
                                {{-- Colors --}}
                                <p style="font-size:.75rem;font-weight:600;color:#9ca3af;margin-bottom:8px;">COLOR SCHEME</p>
                                <div style="display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap;">
                                    <div style="text-align:center;">
                                        <div style="
                                            width:48px;height:48px;border-radius:10px;
                                            background:{{ $viewRecord->background_color }};
                                            border:1px solid #e5e7eb;
                                            box-shadow:inset 0 0 0 1px rgba(0,0,0,.05);
                                            margin-bottom:4px;
                                        "></div>
                                        <div style="font-size:.62rem;color:#9ca3af;">Background</div>
                                        <div style="font-size:.65rem;font-weight:600;color:#374151;">{{ $viewRecord->background_color }}</div>
                                    </div>
                                    <div style="text-align:center;">
                                        <div style="
                                            width:48px;height:48px;border-radius:10px;
                                            background:{{ $viewRecord->text_color }};
                                            border:1px solid #e5e7eb;
                                            margin-bottom:4px;
                                        "></div>
                                        <div style="font-size:.62rem;color:#9ca3af;">Text</div>
                                        <div style="font-size:.65rem;font-weight:600;color:#374151;">{{ $viewRecord->text_color }}</div>
                                    </div>
                                    <div style="text-align:center;">
                                        <div style="
                                            width:48px;height:48px;border-radius:10px;
                                            background:{{ $viewRecord->accent_color }};
                                            border:1px solid #e5e7eb;
                                            margin-bottom:4px;
                                        "></div>
                                        <div style="font-size:.62rem;color:#9ca3af;">Accent</div>
                                        <div style="font-size:.65rem;font-weight:600;color:#374151;">{{ $viewRecord->accent_color }}</div>
                                    </div>
                                </div>

                                {{-- Features --}}
                                <p style="font-size:.75rem;font-weight:600;color:#9ca3af;margin-bottom:8px;">FEATURES</p>
                                <div style="display:flex;flex-wrap:wrap;gap:8px;">
                                    <div style="
                                        display:flex;align-items:center;gap:6px;
                                        padding:5px 12px;border-radius:20px;font-size:.78rem;
                                        {{ $viewRecord->show_photo ? 'background:rgba(59,130,246,.1);color:#2563eb;' : 'background:#f3f4f8;color:#9ca3af;text-decoration:line-through;' }}
                                    ">
                                        <i class="bi bi-{{ $viewRecord->show_photo ? 'camera-fill' : 'camera' }}"></i> Photo
                                    </div>
                                    <div style="
                                        display:flex;align-items:center;gap:6px;
                                        padding:5px 12px;border-radius:20px;font-size:.78rem;
                                        {{ $viewRecord->show_signature ? 'background:rgba(34,197,94,.1);color:#16a34a;' : 'background:#f3f4f8;color:#9ca3af;text-decoration:line-through;' }}
                                    ">
                                        <i class="bi bi-{{ $viewRecord->show_signature ? 'pen-fill' : 'pen' }}"></i> Signature
                                    </div>
                                    <div style="
                                        display:flex;align-items:center;gap:6px;
                                        padding:5px 12px;border-radius:20px;font-size:.78rem;
                                        {{ $viewRecord->show_barcode ? 'background:rgba(245,158,11,.1);color:#d97706;' : 'background:#f3f4f8;color:#9ca3af;text-decoration:line-through;' }}
                                    ">
                                        <i class="bi bi-{{ $viewRecord->show_barcode ? 'upc-scan' : 'upc' }}"></i> Barcode
                                    </div>
                                </div>

                                {{-- Header / Footer text --}}
                                @if($viewRecord->header_text)
                                <div style="margin-top:14px;">
                                    <p style="font-size:.7rem;color:#9ca3af;margin-bottom:3px;font-weight:600;">HEADER TEXT</p>
                                    <p style="font-size:.82rem;color:#374151;background:#f9fafb;padding:7px 10px;border-radius:6px;border-left:3px solid {{ $viewRecord->accent_color }};margin:0;">
                                        {{ $viewRecord->header_text }}
                                    </p>
                                </div>
                                @endif

                                @if($viewRecord->footer_text)
                                <div style="margin-top:10px;">
                                    <p style="font-size:.7rem;color:#9ca3af;margin-bottom:3px;font-weight:600;">FOOTER TEXT</p>
                                    <p style="font-size:.82rem;color:#374151;background:#f9fafb;padding:7px 10px;border-radius:6px;border-left:3px solid #e5e7eb;margin:0;">
                                        {{ $viewRecord->footer_text }}
                                    </p>
                                </div>
                                @endif
                            </div>

                            {{-- Instructions full --}}
                            @if($viewRecord->instructions)
                            <div class="col-12">
                                <p style="font-size:.7rem;font-weight:600;text-transform:uppercase;letter-spacing:.08em;color:#9ca3af;margin-bottom:8px;">
                                    EXAM INSTRUCTIONS
                                </p>
                                <div style="
                                    background:#fffbeb;
                                    border:1px solid #fde68a;
                                    border-radius:8px;
                                    padding:14px 16px;
                                    font-size:.82rem;
                                    color:#374151;
                                    line-height:1.7;
                                    white-space:pre-line;
                                ">{{ $viewRecord->instructions }}</div>
                            </div>
                            @endif

                            {{-- Logo preview --}}
                            @if($viewRecord->logo_path)
                            <div class="col-12">
                                <p style="font-size:.7rem;font-weight:600;text-transform:uppercase;letter-spacing:.08em;color:#9ca3af;margin-bottom:8px;">LOGO</p>
                                <img src="{{ asset($viewRecord->logo_path) }}"
                                    style="height:56px;border-radius:8px;border:1px solid #e5e7eb;background:#f9fafb;padding:4px;"
                                    alt="Template Logo">
                            </div>
                            @endif

                        </div>
                    </div>

                </div>

                {{-- Footer --}}
                <div class="modal-footer" style="border-top:1px solid #e5e7eb;">
                    <button type="button" class="btn btn-light" wire:click="$set('showViewModal', false)">
                        <i class="bi bi-x-lg me-1"></i>Close
                    </button>
                    <button type="button" class="btn btn-primary" wire:click="openEdit({{ $viewRecord->id }}); $set('showViewModal', false)">
                        <i class="bi bi-pencil me-1"></i>Edit Template
                    </button>
                </div>

            </div>
        </div>
    </div>
    @endif

    {{-- DELETE CONFIRM --}}
    @if($confirmDelete)
    <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5);">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-body text-center py-4">
                    <div style="width:56px;height:56px;border-radius:50%;background:#fee2e2;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                        <i class="bi bi-exclamation-triangle text-danger" style="font-size:1.5rem;"></i>
                    </div>
                    <h6 class="fw-700">Delete Template?</h6>
                    <p class="text-muted small">This action cannot be undone.</p>
                </div>
                <div class="modal-footer justify-content-center border-0 pt-0">
                    <button class="btn btn-light btn-sm" wire:click="$set('confirmDelete',false)">Cancel</button>
                    <button class="btn btn-danger btn-sm" wire:click="deleteRecord">Delete</button>
                </div>
            </div>
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

        /* ── CARD ── */
        .card { border: 1px solid var(--border); border-radius: 12px; box-shadow: 0 1px 4px rgba(0,0,0,.04); }
        .card-header { background: #fff; border-bottom: 1px solid var(--border); border-radius: 12px 12px 0 0 !important; padding: 16px 20px; }
        .card-header .card-title { font-size: .95rem; font-weight: 600; margin: 0; }
 
        /* ── TABLE ── */
        .table th { font-size: .75rem; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: var(--text-muted); border-bottom: 2px solid var(--border); }
        .table td { vertical-align: middle; font-size: .875rem; }
        .table > :not(caption) > * > * { padding: .7rem 1rem; }
 
        /* ── BADGES ── */
        .badge-active { background: rgba(34,197,94,.12); color: #16a34a; }
        .badge-inactive { background: rgba(107,114,128,.12); color: #6b7280; }
        .badge-expired, .badge-cancelled, .badge-suspended { background: rgba(239,68,68,.12); color: #dc2626; }
        .badge-used { background: rgba(59,130,246,.12); color: #2563eb; }
 
        /* ── AVATAR ── */
        .avatar { width: 38px; height: 38px; border-radius: 8px; object-fit: cover; }
        .avatar-placeholder {
            width: 38px; height: 38px; border-radius: 8px;
            background: var(--primary-light); color: var(--primary);
            display: inline-flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: .875rem;
        }
 
        /* ── MODAL ── */
        .modal-header { border-bottom: 1px solid var(--border); }
        .modal-footer { border-top: 1px solid var(--border); }
        .modal-title { font-weight: 600; font-size: 1rem; }
 
        /* ── FORM ── */
        .form-label { font-size: .8rem; font-weight: 600; color: var(--text-muted); margin-bottom: 4px; }
        .form-control, .form-select {
            border-radius: 8px; border: 1px solid var(--border);
            font-size: .875rem; padding: .45rem .75rem;
            transition: border-color .2s, box-shadow .2s;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-light);
        }
        .form-check-input:checked { background-color: var(--primary); border-color: var(--primary); }
 
        /* Color picker */
        .color-input-wrap { display: flex; align-items: center; gap: 8px; }
        .color-input-wrap input[type="color"] {
            width: 40px; height: 38px; padding: 2px; border-radius: 8px;
            cursor: pointer; border: 1px solid var(--border);
        }
 
        /* Buttons */
        .btn-primary { background: var(--primary); border-color: var(--primary); }
        .btn-primary:hover, .btn-primary:focus { background: #d63e3e; border-color: #d63e3e; }
        .btn-sm { font-size: .78rem; padding: .3rem .65rem; border-radius: 6px; }
        .btn-icon { width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border-radius: 7px; }
 
        /* Stat cards */
        .stat-card { border-radius: 12px; padding: 20px; display: flex; align-items: center; gap: 16px; }
        .stat-icon { width: 48px; height: 48px; border-radius: 10px; display: grid; place-items: center; font-size: 1.4rem; }
        .stat-label { font-size: .75rem; color: var(--text-muted); font-weight: 500; }
        .stat-value { font-size: 1.5rem; font-weight: 700; line-height: 1; }
 
        /* ID Card Preview */
        .id-card-preview {
            width: 325px; min-height: 200px; border-radius: 14px; overflow: hidden;
            box-shadow: 0 8px 32px rgba(0,0,0,.15); margin: 0 auto;
            position: relative; font-family: 'Inter', sans-serif;
        }
        .id-card-preview .card-header-band { padding: 16px; text-align: center; }
        .id-card-preview .card-body-area { padding: 14px 16px; display: flex; gap: 14px; }
        .id-card-preview .card-photo {
            width: 80px; height: 95px; border-radius: 8px;
            object-fit: cover; border: 3px solid rgba(255,255,255,.5);
        }
 
        /* Print */
        @media print {
            .sidebar, .topbar, .no-print { display: none !important; }
            .main-content { margin: 0; padding: 0; }
            .print-area { display: block !important; }
        }
 
        .alert { border-radius: 10px; font-size: .875rem; }
 
        /* Subject rows */
        .subject-row { background: var(--bg); border-radius: 8px; padding: 10px 12px; margin-bottom: 8px; }
 

        /* Pagination */
        .custom-pagination {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .custom-pagination li {
            list-style: none;
        }

        .custom-pagination button {
            min-width: 38px;
            height: 38px;
            border-radius: 10px;
            border: 1px solid #e0e0e0;
            background: #f5f5f5;
            color: #444;
            font-weight: 600;
            cursor: pointer;
            transition: all .2s ease;
        }

        /* Hover */
        .custom-pagination button:hover {
            background: #eee;
        }

        /* Active (Pink) */
        .custom-pagination button.active {
            background: linear-gradient(195deg, #ec407a, #d81b60);
            color: #fff;
            border: none;
            box-shadow: 0 4px 12px rgba(216,27,96,.4);
        }

        /* Disabled */
        .custom-pagination button:disabled {
            opacity: .5;
            cursor: not-allowed;
        }
    </style>
@endpush