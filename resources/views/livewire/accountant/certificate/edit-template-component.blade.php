<div class="mat-card" style="padding-top:28px">

    <!-- Floating Header -->
    <div class="mat-card-header header-pink-gradient">
        <h5>
            <span class="material-icons-round" style="font-size:18px;vertical-align:middle;margin-right:6px">
                workspace_premium
            </span>
            Edit Certificate Template
        </h5>
        <p>Update existing certificate layout</p>
    </div>

    <!-- ══ BASIC INFO ══ -->
    <div class="form-section">
        <div class="section-title mb-2">Basic Information</div>
        <div class="row g-4">

            <div class="col-md-12">
                <div class="input-group input-group-outline">
                    <label class="form-label">Certificate Name <span class="req">*</span></label>
                    <input type="text"
                           wire:model="certificate_name"
                           class="form-control"
                           placeholder=" "
                           onfocus="focused(this)"
                           onfocusout="defocused(this)">
                </div>
                @error('certificate_name') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <div class="col-md-6">
                <div class="input-group input-group-outline">
                    <label class="form-label">Applicable For <span class="req">*</span></label>
                    <select wire:model="applicable_user" class="form-select">
                        <option value="">Select</option>
                        <option value="student">Student</option>
                        <option value="employee">Employee</option>
                    </select>
                </div>
                @error('applicable_user') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <div class="col-md-6">
                <div class="input-group input-group-outline">
                    <label class="form-label">Page Layout <span class="req">*</span></label>
                    <select wire:model="page_layout" class="form-select">
                        <option value="a4_portrait">A4 Portrait</option>
                        <option value="a4_landscape">A4 Landscape</option>
                        <option value="a5_portrait">A5 Portrait</option>
                        <option value="a5_landscape">A5 Landscape</option>
                    </select>
                </div>
                @error('page_layout') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

        </div>
    </div>

    <!-- ══ PHOTO & QR OPTIONS ══ -->
    <div class="form-section">
        <div class="section-title mb-2">Photo & QR Code Settings</div>
        <div class="row g-4">

            <div class="col-md-4">
                <div class="input-group input-group-outline">
                    <label class="form-label">QR Code Content <span class="req">*</span></label>
                    <select wire:model="qr_code_text" class="form-select">
                        <option value="register_no">Register No</option>
                        <option value="roll_no">Roll No</option>
                        <option value="name">Name</option>
                        <option value="email">Email</option>
                        <option value="mobile">Mobile</option>
                    </select>
                </div>
                @error('qr_code_text') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <div class="col-md-4">
                <div class="input-group input-group-outline">
                    <label class="form-label">Photo Style <span class="req">*</span></label>
                    <select wire:model="photo_style" class="form-select">
                        <option value="square">Square</option>
                        <option value="circle">Circle</option>
                    </select>
                </div>
                @error('photo_style') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <div class="col-md-4">
                <div class="input-group input-group-outline">
                    <label class="form-label">Photo Size (px) <span class="req">*</span></label>
                    <input type="number"
                           wire:model="photo_size"
                           class="form-control"
                           min="50"
                           max="300"
                           placeholder=" "
                           onfocus="focused(this)"
                           onfocusout="defocused(this)">
                </div>
                @error('photo_size') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

        </div>
    </div>

    <!-- ══ PAGE MARGINS ══ -->
    <div class="form-section">
        <div class="section-title mb-2">Page Margins (px)</div>
        <div class="row g-4">

            <div class="col-md-6 col-lg-3">
                <div class="input-group input-group-outline">
                    <label class="form-label">Top</label>
                    <input type="number" wire:model="margin_top" class="form-control"
                           min="0" max="300" placeholder=" "
                           onfocus="focused(this)" onfocusout="defocused(this)">
                </div>
                @error('margin_top') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="input-group input-group-outline">
                    <label class="form-label">Right</label>
                    <input type="number" wire:model="margin_right" class="form-control"
                           min="0" max="300" placeholder=" "
                           onfocus="focused(this)" onfocusout="defocused(this)">
                </div>
                @error('margin_right') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="input-group input-group-outline">
                    <label class="form-label">Bottom</label>
                    <input type="number" wire:model="margin_bottom" class="form-control"
                           min="0" max="300" placeholder=" "
                           onfocus="focused(this)" onfocusout="defocused(this)">
                </div>
                @error('margin_bottom') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="input-group input-group-outline">
                    <label class="form-label">Left</label>
                    <input type="number" wire:model="margin_left" class="form-control"
                           min="0" max="300" placeholder=" "
                           onfocus="focused(this)" onfocusout="defocused(this)">
                </div>
                @error('margin_left') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

        </div>
    </div>

    <!-- ══ CERTIFICATE CONTENT ══ -->
    <div class="form-section">
        <div class="section-title mb-2">Certificate Content</div>
        <div class="row g-4">
            <div class="col-12">
                <label style="font-size:.73rem;font-weight:600;color:var(--muted);display:block;margin-bottom:8px">
                    Content Body <span class="req">*</span>
                    <span style="font-weight:600;margin-left:8px;color:#bbb">
                        — Use placeholders like <code>{institute_name}<c/ode>, <code>{institute_email}</code>,<code>{institute_mobile}</code><code>{institute_address}</code> 
                        <code>{register_no}</code>,<code>{student_photo}</code>,<code>{admission_date}</code>,
                        <code>{name}</code>,<code>{gender}</code>,<code>{roll}</code>,<code>{class}</code>, <code>{category}</code>, <code>{birthday}</code>
                    </span>
                </label>
                <div wire:ignore>
                    <textarea id="certificateContent"></textarea>
                </div>
                @error('certificate_content') <span class="text-danger mt-1">{{ $message }}</span> @enderror
            </div>
        </div>
    </div>

    <!-- ══ IMAGES ══ -->
    <div class="form-section">
        <div class="section-title mb-2">Images</div>
        <div class="row g-4">

            <!-- ══ IMAGES ══ -->
            <div class="form-section">
                <div class="section-title mb-2">Images</div>
                <div class="row g-4">

                    {{-- ── LOGO IMAGE ── --}}
                    <div class="col-md-4">
                        <label class="upload-label">School Logo</label>
                        @if($existing_logo_image)
                            <div class="existing-image-preview">
                                <img src="{{ asset($existing_logo_image) }}" class="preview-img" alt="Logo">
                                <div class="d-flex gap-2 mt-2">
                                    <button type="button" wire:click="removeImage('logo_image')" class="btn-remove-image">
                                        <span class="material-icons-round" style="font-size:14px">delete</span> Remove
                                    </button>
                                </div>
                            </div>
                        @else
                            <div class="photo-upload-box" onclick="document.getElementById('logoUpload').click()">
                                <span class="material-icons-round">corporate_fare</span>
                                <span class="lbl">Click to upload logo</span>
                                <small>JPG, PNG up to 2MB</small>
                            </div>
                            <input type="file" id="logoUpload" wire:model="logo_image" accept="image/*" style="display:none">
                            @if($logo_image)
                                @if($this->safePreviewUrl($logo_image))
                                    <div class="new-upload-preview mt-2">
                                        <img src="{{ $this->safePreviewUrl($logo_image) }}" class="preview-img" alt="Logo preview">
                                        <span class="preview-badge">New — not saved yet</span>
                                    </div>
                                @else
                                    <div class="new-upload-preview mt-2">
                                        <span class="material-icons-round">check_circle</span>
                                        <span class="preview-badge">File selected</span>
                                    </div>
                                @endif
                            @endif
                        @endif
                        @error('logo_image') <span class="text-danger d-block mt-1">{{ $message }}</span> @enderror
                    </div>

                    {{-- ── SIGNATURE IMAGE ── --}}
                    <div class="col-md-4">
                        <label class="upload-label">Signature</label>
                        @if($existing_signature_image)
                            <div class="existing-image-preview">
                                <img src="{{ asset($existing_signature_image) }}" class="preview-img" alt="Signature">
                                <div class="d-flex gap-2 mt-2">
                                    <button type="button" wire:click="removeImage('signature_image')" class="btn-remove-image">
                                        <span class="material-icons-round" style="font-size:14px">delete</span> Remove
                                    </button>
                                </div>
                            </div>
                        @else
                            <div class="photo-upload-box" onclick="document.getElementById('signatureUpload').click()">
                                <span class="material-icons-round">draw</span>
                                <span class="lbl">Click to upload signature</span>
                                <small>JPG, PNG up to 2MB</small>
                            </div>
                            <input type="file" id="signatureUpload" wire:model="signature_image" accept="image/*" style="display:none">
                            @if($signature_image)
                                @if($this->safePreviewUrl($signature_image))
                                    <div class="new-upload-preview mt-2">
                                        <img src="{{ $this->safePreviewUrl($signature_image) }}" class="preview-img" alt="Signature preview">
                                        <span class="preview-badge">New — not saved yet</span>
                                    </div>
                                @else
                                    <div class="new-upload-preview mt-2">
                                        <span class="material-icons-round">check_circle</span>
                                        <span class="preview-badge">File selected</span>
                                    </div>
                                @endif
                            @endif
                        @endif
                        @error('signature_image') <span class="text-danger d-block mt-1">{{ $message }}</span> @enderror
                    </div>

                    {{-- ── BACKGROUND IMAGE ── --}}
                    <div class="col-md-4">
                        <label class="upload-label">Background</label>
                        @if($existing_background_image)
                            <div class="existing-image-preview">
                                <img src="{{ asset($existing_background_image) }}" class="preview-img" alt="Background">
                                <div class="d-flex gap-2 mt-2">
                                    <button type="button" wire:click="removeImage('background_image')" class="btn-remove-image">
                                        <span class="material-icons-round" style="font-size:14px">delete</span> Remove
                                    </button>
                                </div>
                            </div>
                        @else
                            <div class="photo-upload-box" onclick="document.getElementById('backgroundUpload').click()">
                                <span class="material-icons-round">wallpaper</span>
                                <span class="lbl">Click to upload background</span>
                                <small>JPG, PNG up to 2MB</small>
                            </div>
                            <input type="file" id="backgroundUpload" wire:model="background_image" accept="image/*" style="display:none">
                            @if($background_image)
                                @if($this->safePreviewUrl($background_image))
                                    <div class="new-upload-preview mt-2">
                                        <img src="{{ $this->safePreviewUrl($background_image) }}" class="preview-img" alt="Background preview">
                                        <span class="preview-badge">New — not saved yet</span>
                                    </div>
                                @else
                                    <div class="new-upload-preview mt-2">
                                        <span class="material-icons-round">check_circle</span>
                                        <span class="preview-badge">File selected</span>
                                    </div>
                                @endif
                            @endif
                        @endif
                        @error('background_image') <span class="text-danger d-block mt-1">{{ $message }}</span> @enderror
                    </div>

                </div>
            </div>

        </div>
    </div>

    <!-- FORM FOOTER -->
    <div class="form-footer">
        <button type="button" class="btn-outline" onclick="history.back()">
            <span class="material-icons-round" style="font-size:16px">arrow_back</span>
            Back
        </button>

        <button class="btn-pink"
                type="button"
                wire:click="update"
                wire:loading.attr="disabled"
                wire:target="update">
            <span wire:loading.remove wire:target="update">
                <span class="material-icons-round">save</span>
                Save Template
            </span>
            <span wire:loading wire:target="update">
                <span class="material-icons-round" style="font-size:16px;animation:spin .7s linear infinite">sync</span>
                Saving...
            </span>
        </button>
    </div>

</div>


@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-lite.min.css">
<style>
    /* ── Upload label ── */
    .upload-label {
        font-size: .73rem;
        font-weight: 600;
        color: var(--muted, #6b7280);
        display: block;
        margin-bottom: 8px;
    }

    /* ── Upload box ── */
    .photo-upload-box {
        border: 2px dashed #e5e7eb;
        border-radius: 10px;
        padding: 24px 16px;
        text-align: center;
        cursor: pointer;
        transition: border-color .2s, background .2s;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 6px;
    }
    .photo-upload-box:hover {
        border-color: var(--primary, #e74c3c);
        background: #fff5f5;
    }
    .photo-upload-box .material-icons-round {
        font-size: 2rem;
        color: #d1d5db;
    }
    .photo-upload-box .lbl {
        font-size: .8rem;
        font-weight: 600;
        color: #6b7280;
    }
    .photo-upload-box small {
        color: #bbb;
        font-size: .7rem;
    }

    /* ── Existing image preview ── */
    .existing-image-preview {
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        padding: 12px;
    }
    .preview-img {
        max-height: 80px;
        max-width: 100%;
        border-radius: 6px;
        border: 1px solid #eee;
        display: block;
        object-fit: contain;
    }

    /* ── New upload preview ── */
    .new-upload-preview {
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        border-radius: 10px;
        padding: 10px;
        text-align: center;
    }
    .preview-badge {
        display: inline-block;
        margin-top: 6px;
        font-size: .68rem;
        background: #dcfce7;
        color: #16a34a;
        border-radius: 4px;
        padding: 2px 8px;
        font-weight: 600;
    }

    /* ── Remove button ── */
    .btn-remove-image {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: .72rem;
        font-weight: 600;
        color: #e74c3c;
        background: #fff0f0;
        border: 1px solid #f5c6c6;
        border-radius: 6px;
        padding: 3px 10px;
        cursor: pointer;
        transition: background .2s;
    }
    .btn-remove-image:hover { background: #ffe0e0; }

    /* ── Spin animation ── */
    @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
</style>
@endpush


@push('scripts')
<script>
(function loadSummernote() {

    function attachSummernote() {
        var el = document.getElementById('certificateContent');
        if (!el) return;
        if ($(el).data('summernote-init')) return;
        $(el).data('summernote-init', true);

        $(el).summernote({
            height: 320,
            placeholder: 'Write certificate content here... Use {name}, {institute_name}, {roll_no} etc.',
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
                    var id  = document.querySelector('[wire\\:id]').getAttribute('wire:id');
                    Livewire.find(id).set('certificate_content', contents);
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
        var s    = document.createElement('script');
        s.src    = src;
        s.onload = cb;
        document.head.appendChild(s);
    }

    function initWhenReady() {
        document.addEventListener('livewire:initialized', function () {
            setTimeout(attachSummernote, 150);
            Livewire.hook('morph.updated', function () {
                setTimeout(attachSummernote, 100);
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
                setTimeout(() => initAllFields(), 1);
            });

            function initAllFields() {

                // ── 1. Text/Textarea/Number is-filled ──
                document.querySelectorAll('.input-group-outline input, .input-group-outline textarea').forEach(function(input) {
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

                // ── 2. Select is-filled ──
                document.querySelectorAll('.input-group-outline select').forEach(function(select) {
                    var group = select.closest('.input-group');
                    if (!group) return;
                    if (select.value && select.value !== '') {
                        group.classList.add('is-filled');
                    } else {
                        group.classList.remove('is-filled');
                    }
                    if (select._materialInit) return;
                    select._materialInit = true;
                    select.addEventListener('change', function() {
                        group.classList.toggle('is-filled', !!select.value);
                    });
                    select.addEventListener('focus', function() { group.classList.add('is-focused'); });
                    select.addEventListener('blur', function() { group.classList.remove('is-focused'); });
                });

                // ── 3. Custom Select rebuild ──
                document.querySelectorAll('.input-group-outline .form-select').forEach(function(select) {
                    var old = select.parentNode.querySelector('.custom-select-wrapper');
                    if (old) old.remove();
                    select.style.display = '';
                    if (typeof buildCustomSelect === 'function') {
                        buildCustomSelect(select);
                    }
                });

                // ── 4. Datepicker ──
                Livewire.on('date-updated', function (event) {
                    var input = document.querySelector('.input-group-outline input[type="date"]');
                    if (!input) return;
                    var newDate = event.date || '';
                    if (newDate) {
                        input.value = newDate;
                        input.dataset.dpValue = newDate;
                        if (input._dpTriggerSync) {
                            input._dpTriggerSync(newDate);
                        }
                    }
                });
            }

        });
    </script>
@endpush