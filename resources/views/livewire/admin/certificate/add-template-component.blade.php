<div>
    <div class="card tpl-wizard">

        <!-- Floating Header -->
        <div class="mat-card-header header-primary-gradient">
            <h5>
                <span class="material-icons-round" style="font-size:18px;vertical-align:middle;margin-right:6px">
                    workspace_premium
                </span>
                New Certificate Template
            </h5>
            <p>Design your certificate in {{ $totalSteps }} simple steps</p>
        </div>

        <!-- ══ STEP PROGRESS BAR (mobile-friendly, always visible) ══ -->
        <div class="tpl-steps">
            @php
                $stepLabels = [
                    1 => ['Name & Type', 'badge'],
                    2 => ['Pick a Design', 'palette'],
                    3 => ['Photos & Settings', 'tune'],
                    4 => ['Content & Preview', 'edit_note'],
                ];
            @endphp

            <div class="tpl-steps-track">
                @foreach($stepLabels as $num => [$label, $icon])
                    <button type="button"
                            class="tpl-step-dot {{ $step == $num ? 'active' : '' }} {{ $step > $num ? 'done' : '' }}"
                            wire:click="goToStep({{ $num }})">
                        <span class="dot-circle">
                            @if($step > $num)
                                <span class="material-icons-round" style="font-size:16px">check</span>
                            @else
                                <span class="material-icons-round" style="font-size:16px">{{ $icon }}</span>
                            @endif
                        </span>
                        <span class="dot-label">{{ $num }}. {{ $label }}</span>
                    </button>
                @endforeach
            </div>
            <div class="tpl-steps-bar">
                <div class="tpl-steps-bar-fill" style="width: {{ (($step - 1) / ($totalSteps - 1)) * 100 }}%"></div>
            </div>
        </div>

        <!-- ══ STEP 1 — Name & Type ══ -->
        <div class="form-section {{ $step == 1 ? '' : 'd-none' }}">
            <div class="section-title mb-1">What should we call this template?</div>
            <p class="section-help">Just a name for you to recognize it later, e.g. "Class 8 Certificate"</p>

            <div class="row g-4">
                <div class="col-12">
                    <div class="input-group input-group-outline">
                        <label class="form-label">Template Name <span class="req">*</span></label>
                        <input type="text"
                            wire:model="certificate_name"
                            class="form-control"
                            placeholder=" "
                            onfocus="focused(this)"
                            onfocusout="defocused(this)">
                    </div>
                    @error('certificate_name') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="col-12">
                    <label class="form-label d-block mb-2">Who is this for? <span class="req">*</span></label>
                    <div class="tpl-choice-row">
                        <button type="button"
                                class="tpl-choice-card {{ $applicable_user === 'student' ? 'selected' : '' }}"
                                wire:click="$set('applicable_user', 'student')">
                            <span class="material-icons-round">school</span>
                            <span class="tpl-choice-title">Student</span>
                        </button>
                        <button type="button"
                                class="tpl-choice-card {{ $applicable_user === 'employee' ? 'selected' : '' }}"
                                wire:click="$set('applicable_user', 'employee')">
                            <span class="material-icons-round">badge</span>
                            <span class="tpl-choice-title">Employee</span>
                        </button>
                    </div>
                    @error('applicable_user') <span class="text-danger d-block mt-2">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        <!-- ══ STEP 2 — Pick a Design (visual cards, no plain dropdown) ══ -->
        <div class="form-section {{ $step == 2 ? '' : 'd-none' }}">
            <div class="section-title mb-1">Choose a ready-made design</div>
            <p class="section-help">Click a card to load it instantly. You can still edit the text afterwards.</p>

            <div class="tpl-design-grid">
                @foreach($designs as $key => $html)
                    @php $meta = $designMeta[$key] ?? ['title' => ucfirst($key), 'sub' => '', 'color' => '#999']; @endphp
                    <button type="button"
                            class="tpl-design-card {{ $selectedDesign === $key ? 'selected' : '' }}"
                            style="--accent: {{ $meta['color'] }}"
                            onclick="selectDesignInEditor('{{ $key }}')"
                            wire:click="$set('selectedDesign', '{{ $key }}')">
                        <span class="tpl-design-thumb">
                            <span class="tpl-thumb-line" style="width:70%"></span>
                            <span class="tpl-thumb-line" style="width:40%"></span>
                            <span class="tpl-thumb-title">Aa</span>
                            <span class="tpl-thumb-line" style="width:90%"></span>
                            <span class="tpl-thumb-line" style="width:85%"></span>
                            <span class="tpl-thumb-line" style="width:60%"></span>
                        </span>
                        <span class="tpl-design-title">{{ $meta['title'] }}</span>
                        <span class="tpl-design-sub">{{ $meta['sub'] }}</span>
                        @if($selectedDesign === $key)
                            <span class="tpl-design-check material-icons-round">check_circle</span>
                        @endif
                    </button>
                @endforeach
            </div>

            <div class="tpl-note mt-3">
                <span class="material-icons-round" style="font-size:16px">info</span>
                No worries — you can skip this and write your own content in Step 4 instead.
            </div>
        </div>

        <!-- ══ STEP 3 — Photos, page & spacing (presets, no raw px/enum) ══ -->
        <div class="form-section {{ $step == 3 ? '' : 'd-none' }}">
            <div class="section-title mb-1">Photo & Page Settings</div>
            <p class="section-help">You can change these later too — the defaults work fine if you're not sure.</p>

            <div class="row g-4">

                <!-- Paper size -->
                <div class="col-md-6">
                    <label class="form-label d-block mb-2">Paper Size</label>
                    <div class="tpl-choice-row">
                        <button type="button" class="tpl-choice-card sm {{ $paperSize === 'a4' ? 'selected' : '' }}"
                                wire:click="$set('paperSize', 'a4')">
                            <span class="material-icons-round">description</span>
                            <span class="tpl-choice-title">A4 (Large)</span>
                        </button>
                        <button type="button" class="tpl-choice-card sm {{ $paperSize === 'a5' ? 'selected' : '' }}"
                                wire:click="$set('paperSize', 'a5')">
                            <span class="material-icons-round">note</span>
                            <span class="tpl-choice-title">A5 (Small)</span>
                        </button>
                    </div>
                </div>

                <!-- Orientation -->
                <div class="col-md-6">
                    <label class="form-label d-block mb-2">Orientation</label>
                    <div class="tpl-choice-row">
                        <button type="button" class="tpl-choice-card sm {{ $orientation === 'portrait' ? 'selected' : '' }}"
                                wire:click="$set('orientation', 'portrait')">
                            <span class="material-icons-round" style="transform:rotate(0deg)">crop_portrait</span>
                            <span class="tpl-choice-title">Portrait</span>
                        </button>
                        <button type="button" class="tpl-choice-card sm {{ $orientation === 'landscape' ? 'selected' : '' }}"
                                wire:click="$set('orientation', 'landscape')">
                            <span class="material-icons-round">crop_landscape</span>
                            <span class="tpl-choice-title">Landscape</span>
                        </button>
                    </div>
                </div>

                <!-- Spacing preset -->
                <div class="col-md-6">
                    <label class="form-label d-block mb-2">Space Around the Edges</label>
                    <div class="tpl-choice-row three">
                        <button type="button" class="tpl-choice-card sm {{ $spacing === 'compact' ? 'selected' : '' }}"
                                wire:click="$set('spacing', 'compact')">
                            <span class="tpl-choice-title">Compact</span>
                        </button>
                        <button type="button" class="tpl-choice-card sm {{ $spacing === 'normal' ? 'selected' : '' }}"
                                wire:click="$set('spacing', 'normal')">
                            <span class="tpl-choice-title">Normal</span>
                        </button>
                        <button type="button" class="tpl-choice-card sm {{ $spacing === 'spacious' ? 'selected' : '' }}"
                                wire:click="$set('spacing', 'spacious')">
                            <span class="tpl-choice-title">Spacious</span>
                        </button>
                    </div>
                </div>

                <!-- Photo size preset -->
                <div class="col-md-6">
                    <label class="form-label d-block mb-2">Photo Size</label>
                    <div class="tpl-choice-row three">
                        <button type="button" class="tpl-choice-card sm {{ $photoSizePreset === 'small' ? 'selected' : '' }}"
                                wire:click="$set('photoSizePreset', 'small')">
                            <span class="tpl-choice-title">Small</span>
                        </button>
                        <button type="button" class="tpl-choice-card sm {{ $photoSizePreset === 'medium' ? 'selected' : '' }}"
                                wire:click="$set('photoSizePreset', 'medium')">
                            <span class="tpl-choice-title">Medium</span>
                        </button>
                        <button type="button" class="tpl-choice-card sm {{ $photoSizePreset === 'large' ? 'selected' : '' }}"
                                wire:click="$set('photoSizePreset', 'large')">
                            <span class="tpl-choice-title">Large</span>
                        </button>
                    </div>
                </div>

                <!-- Photo shape -->
                <div class="col-md-6">
                    <label class="form-label d-block mb-2">Photo Shape</label>
                    <div class="tpl-choice-row">
                        <button type="button" class="tpl-choice-card sm {{ $photo_style === 'square' ? 'selected' : '' }}"
                                wire:click="$set('photo_style', 'square')">
                            <span class="tpl-shape-preview square"></span>
                            <span class="tpl-choice-title">Square</span>
                        </button>
                        <button type="button" class="tpl-choice-card sm {{ $photo_style === 'circle' ? 'selected' : '' }}"
                                wire:click="$set('photo_style', 'circle')">
                            <span class="tpl-shape-preview circle"></span>
                            <span class="tpl-choice-title">Circle</span>
                        </button>
                    </div>
                </div>

                <!-- QR code content -->
                <div class="col-md-6">
                    <div class="input-group input-group-outline">
                        <label class="form-label">What should the QR code show?</label>
                        <select wire:model="qr_code_text" class="form-select">
                            <option value="registration_no">Registration No.</option>
                            <option value="roll_no">Roll No.</option>
                            <option value="name">Name</option>
                            <option value="email">Email</option>
                            <option value="mobile">Mobile Number</option>
                        </select>
                    </div>
                </div>
            </div>

            <hr class="tpl-divider">

            <div class="section-title mb-2">Upload Images <span class="text-muted fw-normal" style="font-size:.75rem">(optional — you can skip these)</span></div>
            <div class="row g-4">

                <!-- Logo Image -->
                <div class="col-md-4 col-6">
                    <label class="tpl-upload-label">School Logo</label>
                    <label class="photo-upload-box" style="cursor:pointer">
                        @if($logo_image)
                            <img src="{{ $logo_image->temporaryUrl() }}" class="tpl-upload-preview" alt="Logo preview">
                            <span class="lbl">Image selected ✓</span>
                        @else
                            <span class="material-icons-round">corporate_fare</span>
                            <span class="lbl">Click to upload logo</span>
                        @endif
                        <small style="color:#bbb;font-size:.7rem">JPG, PNG up to 2MB</small>
                        <input type="file" wire:model="logo_image" accept="image/jpeg,image/png">
                    </label>
                    @error('logo_image') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <!-- Signature Image -->
                <div class="col-md-4 col-6">
                    <label class="tpl-upload-label">Signature</label>
                    <label class="photo-upload-box" style="cursor:pointer">
                        @if($signature_image)
                            <img src="{{ $signature_image->temporaryUrl() }}" class="tpl-upload-preview" alt="Signature preview">
                            <span class="lbl">Image selected ✓</span>
                        @else
                            <span class="material-icons-round">draw</span>
                            <span class="lbl">Click to upload signature</span>
                        @endif
                        <small style="color:#bbb;font-size:.7rem">JPG, PNG up to 2MB</small>
                        <input type="file" wire:model="signature_image" accept="image/jpeg,image/png">
                    </label>
                    @error('signature_image') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <!-- Background Image -->
                <div class="col-md-4 col-12">
                    <label class="tpl-upload-label">Background</label>
                    <label class="photo-upload-box" style="cursor:pointer">
                        @if($background_image)
                            <img src="{{ $background_image->temporaryUrl() }}" class="tpl-upload-preview" alt="Background preview">
                            <span class="lbl">Image selected ✓</span>
                        @else
                            <span class="material-icons-round">wallpaper</span>
                            <span class="lbl">Click to upload background</span>
                        @endif
                        <small style="color:#bbb;font-size:.7rem">JPG, PNG up to 2MB</small>
                        <input type="file" wire:model="background_image" accept="image/jpeg,image/png">
                    </label>
                    @error('background_image') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        <!-- ══ STEP 4 — Content & Preview ══ -->
        <div class="form-section {{ $step == 4 ? '' : 'd-none' }}">
            <div class="section-title mb-1">Write the Content</div>
            <p class="section-help">Click any button below to insert that field (e.g. name, roll) — it will show the real data when printed.</p>

            @php
                $placeholderGroups = match($applicable_user) {
                    'student' => [
                        'Institute Info'   => ['{institute_name}', '{institute_email}', '{institute_mobile}', '{institute_address}', '{logo}'],
                        'Student Info'     => ['{name}', '{class}', '{section}', '{group}', '{roll}', '{registration_no}', '{student_id}', '{gender}', '{blood}', '{birthday}', '{religion}', '{father_name}', '{mother_name}', '{photo}'],
                        'Date & QR'        => ['{admission_date}', '{print_date}', '{session_year}', '{qr_code}'],
                    ],
                    'employee' => [
                        'Institute Info'   => ['{institute_name}', '{institute_email}', '{institute_mobile}', '{institute_address}', '{logo}'],
                        'Employee Info'    => ['{name}', '{designation}', '{department}', '{employee_id}', '{gender}', '{blood}', '{birthday}', '{religion}', '{qualification}', '{experience_detail}', '{total_experience}', '{photo}'],
                        'Date & QR'        => ['{joining_date}', '{print_date}', '{qr_code}'],
                    ],
                    default => [],
                };
            @endphp

            @if(count($placeholderGroups))
                <div class="placeholder-box mb-3">
                    @foreach($placeholderGroups as $groupLabel => $items)
                        <div class="placeholder-group-label">{{ $groupLabel }}</div>
                        <div class="d-flex flex-wrap gap-2 mb-2">
                            @foreach($items as $ph)
                                <button type="button"
                                        class="placeholder-chip"
                                        onclick="insertPlaceholder('{{ $ph }}')">
                                    {{ $ph }}
                                </button>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            @else
                <div class="tpl-note mb-3">
                    <span class="material-icons-round" style="font-size:16px">arrow_upward</span>
                    Go back to Step 1 and select "Student" or "Employee" to see these fields.
                </div>
            @endif

            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <label style="font-size:1rem;font-weight:600;color:var(--muted);display:block;margin-bottom:8px">
                        Content Area <span class="req">*</span>
                    </label>
                    <button type="button" class="btn-outline btn-sm-tpl" wire:click="goToStep(2)">
                        <span class="material-icons-round" style="font-size:15px">palette</span> Change Design
                    </button>
                </div>

                <div wire:ignore>
                    <textarea id="certificateContent"></textarea>
                </div>
                @error('certificate_content') <span class="text-danger mt-1">{{ $message }}</span> @enderror
            </div>
        </div>

        <!-- ══ WIZARD FOOTER — Back / Next / Save ══ -->
        <div class="form-footer tpl-wizard-footer">
            @if($step > 1)
                <button class="btn-outline" type="button" wire:click="prevStep">
                    <span class="material-icons-round" style="font-size:16px">arrow_back</span> Back
                </button>
            @else
                <button class="btn-outline" type="button" wire:click="resetForm">
                    <span class="material-icons-round" style="font-size:16px">refresh</span> Reset
                </button>
            @endif

            @if($step < $totalSteps)
                <button class="btn-primary" type="button" wire:click="nextStep">
                    Next <span class="material-icons-round" style="font-size:16px">arrow_forward</span>
                </button>
            @else
                <button class="btn-primary" type="button"
                        wire:click="save"
                        wire:loading.attr="disabled"
                        wire:target="save">
                    <span wire:loading.remove wire:target="save">
                        <span class="material-icons-round">save</span> Save Template
                    </span>
                    <span wire:loading wire:target="save">
                        <span class="material-icons-round" style="font-size:16px;animation:spin .7s linear infinite">sync</span> Saving...
                    </span>
                </button>
            @endif
        </div>

    </div>
</div>

@push('styles')
<style>
    /* ── Section helper text ── */
    .section-help { font-size: .78rem; color: #9ca3af; margin-bottom: 14px; }
    .tpl-divider { border-color: #eee; margin: 24px 0; }

    /* ── Step progress bar ── */
    .tpl-steps { padding: 14px 18px 6px; }
    .tpl-steps-track {
        display: flex;
        justify-content: space-between;
        gap: 4px;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
    }
    .tpl-steps-track::-webkit-scrollbar { display: none; }
    .tpl-step-dot {
        background: none;
        border: none;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 4px;
        flex: 1 0 auto;
        min-width: 70px;
        padding: 4px;
        cursor: pointer;
        opacity: .55;
    }
    .tpl-step-dot.active, .tpl-step-dot.done { opacity: 1; }
    .dot-circle {
        width: 30px; height: 30px;
        border-radius: 50%;
        background: #f1f1f1;
        display: flex; align-items: center; justify-content: center;
        color: #9ca3af;
        transition: all .2s ease;
    }
    .tpl-step-dot.active .dot-circle { background: #d81b60; color: #fff; }
    .tpl-step-dot.done .dot-circle { background: #16a34a; color: #fff; }
    .dot-label { font-size: .66rem; font-weight: 600; color: #6b7280; text-align: center; white-space: nowrap; }
    .tpl-step-dot.active .dot-label { color: #d81b60; }
    .tpl-steps-bar { height: 4px; background: #f1f1f1; border-radius: 4px; margin: 10px 2px 0; overflow: hidden; }
    .tpl-steps-bar-fill { height: 100%; background: #d81b60; transition: width .25s ease; }

    /* ── Big tappable choice cards (Step 1 & 3) ── */
    .tpl-choice-row { display: flex; gap: 12px; flex-wrap: wrap; }
    .tpl-choice-row.three .tpl-choice-card { flex-basis: calc(33.333% - 8px); }
    .tpl-choice-card {
        flex: 1 1 140px;
        min-height: 76px;
        background: #fff;
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 4px;
        padding: 12px 8px;
        cursor: pointer;
        transition: all .15s ease;
    }
    .tpl-choice-card.sm { min-height: 60px; flex: 1 1 110px; }
    .tpl-choice-card .material-icons-round { font-size: 22px; color: #9ca3af; }
    .tpl-choice-card.selected { border-color: #d81b60; background: #fdf2f6; }
    .tpl-choice-card.selected .material-icons-round { color: #d81b60; }
    .tpl-choice-title { font-size: .82rem; font-weight: 700; color: #374151; }
    .tpl-choice-sub { font-size: .68rem; color: #9ca3af; }
    .tpl-shape-preview { display: block; width: 22px; height: 22px; background: #d1d5db; }
    .tpl-shape-preview.circle { border-radius: 50%; }
    .tpl-shape-preview.square { border-radius: 4px; }
    .tpl-choice-card.selected .tpl-shape-preview { background: #d81b60; }

    /* ── Design picker cards (Step 2) ── */
    .tpl-design-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
        gap: 14px;
    }
    .tpl-design-card {
        position: relative;
        background: #fff;
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        padding: 10px;
        cursor: pointer;
        text-align: left;
        transition: all .15s ease;
    }
    .tpl-design-card.selected { border-color: var(--accent, #d81b60); box-shadow: 0 0 0 3px rgba(216,27,96,.12); }
    .tpl-design-thumb {
        display: block;
        background: #f9fafb;
        border: 1px solid #eee;
        border-radius: 8px;
        height: 90px;
        padding: 10px;
        margin-bottom: 8px;
        overflow: hidden;
    }
    .tpl-thumb-line { display: block; height: 4px; background: #e5e7eb; border-radius: 2px; margin-bottom: 5px; }
    .tpl-thumb-title { display: block; font-weight: 700; font-size: .8rem; color: var(--accent, #999); margin: 4px 0; }
    .tpl-design-title { display: block; font-size: .8rem; font-weight: 700; color: #374151; }
    .tpl-design-sub { display: block; font-size: .68rem; color: #9ca3af; }
    .tpl-design-check {
        position: absolute; top: 8px; right: 8px;
        color: var(--accent, #d81b60);
        font-size: 20px;
        background: #fff;
        border-radius: 50%;
    }

    /* ── Placeholder chips grouped ── */
    .placeholder-box {
        background: #f8f9fa;
        border: 1px dashed #e0e0e0;
        border-radius: 8px;
        padding: 12px 14px;
    }
    .placeholder-group-label {
        font-size: .7rem;
        font-weight: 700;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: .03em;
        margin-bottom: 6px;
    }
    .placeholder-chip {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        padding: 6px 10px;
        font-size: .74rem;
        font-family: 'Courier New', monospace;
        color: #d81b60;
        cursor: pointer;
        transition: all .15s ease;
        min-height: 32px;
    }
    .placeholder-chip:hover { background: #fce4ec; border-color: #d81b60; color: #ad1457; }
    .tpl-note {
        display: flex; align-items: center; gap: 6px;
        font-size: .76rem; color: #6b7280;
        background: #f0f9ff; border: 1px solid #e0f2fe;
        border-radius: 8px; padding: 8px 12px;
    }
    .no-custom-select { display: block !important; opacity: 1 !important; position: static !important; }

    /* ── Upload boxes with preview thumbnail ── */
    .tpl-upload-label { font-size: .73rem; font-weight: 600; color: var(--muted); display: block; margin-bottom: 8px; }
    .tpl-upload-preview { width: 44px; height: 44px; object-fit: cover; border-radius: 6px; margin-bottom: 4px; }
    .btn-sm-tpl { font-size: .72rem; padding: 6px 10px; }

    /* ── Wizard footer always sticky-friendly on mobile ── */
    .tpl-wizard-footer { display: flex; justify-content: space-between; gap: 10px; flex-wrap: wrap; }

    /* ── Mobile responsiveness ── */
    @media (max-width: 576px) {
        .tpl-choice-row.three .tpl-choice-card { flex-basis: calc(50% - 6px); }
        .tpl-design-grid { grid-template-columns: repeat(2, 1fr); }
        .dot-label { display: none; }
        .tpl-step-dot { min-width: 40px; }
        .tpl-wizard-footer button { flex: 1 1 45%; justify-content: center; }
    }
</style>
@endpush

@push('scripts')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-lite.min.css">

<script>
/**
 * All 5 certificate designs, pushed once from the server into a plain JS object.
 * This lets the design-picker cards update Summernote synchronously on click,
 * exactly like insertPlaceholder() does for placeholder chips — no Livewire
 * event round-trip, no timing race with morph/re-render.
 */
var certificateDesigns = @js($designs);

/**
 * Inserts a placeholder token at the current cursor position inside the
 * Summernote editor, then syncs the updated content back to Livewire.
 */
function insertPlaceholder(code) {
    var el = $('#certificateContent');
    if (!el.length || !$(el).data('summernote-init')) return;

    el.summernote('editor.focus');
    el.summernote('editor.insertText', code);

    // Ensure Livewire state stays in sync even if onChange doesn't fire for API inserts
    @this.set('certificate_content', el.summernote('code'));
}

/**
 * Fired when a design card is clicked. Loads the chosen design's HTML
 * straight into Summernote and syncs it back to Livewire — same direct
 * pattern as insertPlaceholder(), so there's no dependency on dispatch/listener timing.
 */
function selectDesignInEditor(key) {
    if (!key || !certificateDesigns[key]) return;

    var el = $('#certificateContent');
    if (!el.length || !$(el).data('summernote-init')) return;

    el.summernote('code', certificateDesigns[key]);

    @this.set('certificate_content', certificateDesigns[key]);
}

(function loadSummernote() {

    function attachSummernote() {
        var el = document.getElementById('certificateContent');
        if (!el) return;
        if ($(el).data('summernote-init')) return;
        $(el).data('summernote-init', true);

        $(el).summernote({
            height: 300,
            placeholder: 'Write the certificate content here...',
            toolbar: [
                ['style',    ['style']],
                ['font',     ['bold', 'underline', 'italic', 'strikethrough', 'clear']],
                ['fontname', ['fontname']],
                ['fontsize', ['fontsize']],
                ['color',    ['color']],
                ['para',     ['ul', 'ol', 'paragraph']],
                ['table',    ['table']],
                ['insert',   ['link', 'picture', 'hr']],
                ['view',     ['fullscreen', 'codeview', 'help']],
            ],
            callbacks: {
                onChange: function (contents) {
                    @this.set('certificate_content', contents);
                },
                onInit: function () {
                    var existing = @js($certificate_content ?? '');
                    if (existing) {
                        $('#certificateContent').summernote('code', existing);
                    }
                }
            }
        });
    }

    function loadScript(src, cb) {
        if (document.querySelector('script[src="' + src + '"]')) { cb(); return; }
        var s = document.createElement('script');
        s.src = src;
        s.onload = cb;
        document.head.appendChild(s);
    }

    function initWhenReady() {
        document.addEventListener('livewire:initialized', function () {
            setTimeout(attachSummernote, 150);

            Livewire.hook('morph.updated', function () {
                setTimeout(attachSummernote, 100);
            });

            Livewire.on('resetSummernote', function () {
                var el = $('#certificateContent');
                if (el.length && $(el).data('summernote-init')) {
                    el.summernote('code', '');
                }
            });

            Livewire.on('resetSelects', function () {
                document.querySelectorAll('.input-group-outline select').forEach(function (select) {
                    if (select.classList.contains('no-custom-select')) return;
                    var group = select.closest('.input-group');
                    if (group) {
                        group.classList.toggle('is-filled', !!select.value);
                    }
                    var old = select.parentNode.querySelector('.custom-select-wrapper');
                    if (old) old.remove();
                    select.style.display = '';
                    if (typeof buildCustomSelect === 'function') {
                        buildCustomSelect(select);
                    }
                });
            });
        });

        setTimeout(attachSummernote, 300);
    }

    var jqSrc = 'https://code.jquery.com/jquery-3.7.1.min.js';
    var snSrc = 'https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-lite.min.js';

    if (typeof jQuery === 'undefined') {
        loadScript(jqSrc, function () { loadScript(snSrc, initWhenReady); });
    } else if (typeof $.fn.summernote === 'undefined') {
        loadScript(snSrc, initWhenReady);
    } else {
        initWhenReady();
    }

})();
</script>

<script>
    document.addEventListener('livewire:initialized', () => {

        setTimeout(() => initAllFields(), 100);

        Livewire.hook('morph.updated', ({ el }) => {
            setTimeout(() => initAllFields(), 0);
        });

        function initAllFields() {

            document.querySelectorAll('.input-group-outline input, .input-group-outline textarea').forEach(function (input) {
                var group = input.closest('.input-group');
                if (!group) return;
                group.classList.toggle('is-filled', !!(input.value && input.value.trim()));
                if (input._materialInit) return;
                input._materialInit = true;
                input.addEventListener('focus', function () { group.classList.add('is-focused'); });
                input.addEventListener('blur', function () {
                    group.classList.remove('is-focused');
                    group.classList.toggle('is-filled', !!input.value.trim());
                });
                input.addEventListener('input', function () {
                    group.classList.toggle('is-filled', !!input.value.trim());
                });
            });

            document.querySelectorAll('.input-group-outline select').forEach(function (select) {
                var group = select.closest('.input-group');
                if (!group) return;
                group.classList.toggle('is-filled', !!(select.value && select.value !== ''));
                if (select._materialInit) return;
                select._materialInit = true;
                select.addEventListener('change', function () {
                    group.classList.toggle('is-filled', !!select.value);
                });
                select.addEventListener('focus', function () { group.classList.add('is-focused'); });
                select.addEventListener('blur', function () { group.classList.remove('is-focused'); });
            });

            document.querySelectorAll('.input-group-outline .form-select').forEach(function (select) {
                if (select.classList.contains('no-custom-select')) return;
                var old = select.parentNode.querySelector('.custom-select-wrapper');
                if (old) old.remove();
                select.style.display = '';
                if (typeof buildCustomSelect === 'function') {
                    buildCustomSelect(select);
                }
            });
        }
    });
</script>
@endpush