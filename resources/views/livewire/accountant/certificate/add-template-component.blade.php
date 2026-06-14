<div class="mat-card" style="padding-top:28px">

    <!-- Floating Header -->
    <div class="mat-card-header header-pink-gradient">
        <h5>
            <span class="material-icons-round" style="font-size:18px;vertical-align:middle;margin-right:6px">
                workspace_premium
            </span>
            Add Certificate Template
        </h5>
        <p>Design a new certificate layout</p>
    </div>

    <!-- ══ BASIC INFO ══ -->
    <div class="form-section">
        <div class="section-title mb-2">Basic Information</div>
        <div class="row g-4">

            <!-- Certificate Name -->
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

            <!-- Applicable User -->
            <div class="col-md-6">
                <div class="input-group input-group-outline" wire:ignore>
                    <label class="form-label">Applicable For <span class="req">*</span></label>
                    <select wire:model="applicable_user" class="form-select">
                        <option value="">Select</option>
                        <option value="student">Student</option>
                        <option value="employee">Employee</option>
                    </select>
                </div>
                @error('applicable_user') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <!-- Page Layout -->
            <div class="col-md-6">
                <div class="input-group input-group-outline" wire:ignore>
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

            <!-- QR Code Text -->
            <div class="col-md-6">
                <div class="input-group input-group-outline" wire:ignore>
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

            <!-- Photo Style -->
            <div class="col-md-6">
                <div class="input-group input-group-outline" wire:ignore>
                    <label class="form-label">Photo Style <span class="req">*</span></label>
                    <select wire:model="photo_style" class="form-select">
                        <option value="square">Square</option>
                        <option value="circle">Circle</option>
                    </select>
                </div>
                @error('photo_style') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <!-- Photo Size -->
            <div class="col-md-6">
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

            <!-- Margin Top -->
            <div class="col-md-6 col-lg-3">
                <div class="input-group input-group-outline">
                    <label class="form-label">Top</label>
                    <input type="number"
                           wire:model="margin_top"
                           class="form-control"
                           min="0"
                           max="300"
                           placeholder=" "
                           onfocus="focused(this)"
                           onfocusout="defocused(this)">
                </div>
                @error('margin_top') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <!-- Margin Right -->
            <div class="col-md-6 col-lg-3">
                <div class="input-group input-group-outline">
                    <label class="form-label">Right</label>
                    <input type="number"
                           wire:model="margin_right"
                           class="form-control"
                           min="0"
                           max="300"
                           placeholder=" "
                           onfocus="focused(this)"
                           onfocusout="defocused(this)">
                </div>
                @error('margin_right') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <!-- Margin Bottom -->
            <div class="col-md-6 col-lg-3">
                <div class="input-group input-group-outline">
                    <label class="form-label">Bottom</label>
                    <input type="number"
                           wire:model="margin_bottom"
                           class="form-control"
                           min="0"
                           max="300"
                           placeholder=" "
                           onfocus="focused(this)"
                           onfocusout="defocused(this)">
                </div>
                @error('margin_bottom') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <!-- Margin Left -->
            <div class="col-md-6 col-lg-3">
                <div class="input-group input-group-outline">
                    <label class="form-label">Left</label>
                    <input type="number"
                           wire:model="margin_left"
                           class="form-control"
                           min="0"
                           max="300"
                           placeholder=" "
                           onfocus="focused(this)"
                           onfocusout="defocused(this)">
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
                    <span style="font-weight:400;margin-left:8px;color:#bbb">
                        @verbatim— Use placeholders like <code>{{name}}</code>, <code>{{roll_no}}</code>, <code>{{class}}</code>@endverbatim
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

           <!-- Logo Image -->
            <div class="col-md-4">
                <label style="font-size:.73rem;font-weight:600;color:var(--muted);display:block;margin-bottom:8px">
                    School Logo
                </label>
                <div class="photo-upload-box">
                    @if($logo_image)
                        @if($this->safePreviewUrl($logo_image))
                            <img src="{{ $this->safePreviewUrl($logo_image) }}" alt="Preview"
                                style="max-height:80px;max-width:100%;object-fit:contain;margin-bottom:6px">
                        @else
                            <span class="material-icons-round">check_circle</span>
                            <span class="lbl">File selected</span>
                        @endif
                    @else
                        <span class="material-icons-round">corporate_fare</span>
                        <span class="lbl">Click to upload logo</span>
                    @endif
                    <small style="color:#bbb;font-size:.7rem">JPG, PNG up to 2MB</small>
                    <input type="file" wire:model="logo_image" accept="image/*">
                </div>
                @error('logo_image') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <!-- Signature Image -->
            <div class="col-md-4">
                <label style="font-size:.73rem;font-weight:600;color:var(--muted);display:block;margin-bottom:8px">
                    Signature
                </label>
                <div class="photo-upload-box">
                    @if($signature_image)
                        @if($this->safePreviewUrl($signature_image))
                            <img src="{{ $this->safePreviewUrl($signature_image) }}" alt="Preview"
                                style="max-height:80px;max-width:100%;object-fit:contain;margin-bottom:6px">
                        @else
                            <span class="material-icons-round">check_circle</span>
                            <span class="lbl">File selected</span>
                        @endif
                    @else
                        <span class="material-icons-round">draw</span>
                        <span class="lbl">Click to upload signature</span>
                    @endif
                    <small style="color:#bbb;font-size:.7rem">JPG, PNG up to 2MB</small>
                    <input type="file" wire:model="signature_image" accept="image/*">
                </div>
                @error('signature_image') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <!-- Background Image -->
            <div class="col-md-4">
                <label style="font-size:.73rem;font-weight:600;color:var(--muted);display:block;margin-bottom:8px">
                    Background
                </label>
                <div class="photo-upload-box">
                    @if($background_image)
                        @if($this->safePreviewUrl($background_image))
                            <img src="{{ $this->safePreviewUrl($background_image) }}" alt="Preview"
                                style="max-height:80px;max-width:100%;object-fit:contain;margin-bottom:6px">
                        @else
                            <span class="material-icons-round">check_circle</span>
                            <span class="lbl">File selected</span>
                        @endif
                    @else
                        <span class="material-icons-round">wallpaper</span>
                        <span class="lbl">Click to upload background</span>
                    @endif
                    <small style="color:#bbb;font-size:.7rem">JPG, PNG up to 2MB</small>
                    <input type="file" wire:model="background_image" accept="image/*">
                </div>
                @error('background_image') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

        </div>
    </div>

    <!-- FORM FOOTER -->
    <div class="form-footer">

        <button class="btn-outline"
                type="button"
                wire:click="resetForm">
            <span class="material-icons-round" style="font-size:16px">refresh</span>
            Reset
        </button>

        <button class="btn-pink"
                type="button"
                wire:click="save"
                wire:loading.attr="disabled"
                wire:target="save">

            <span wire:loading.remove wire:target="save">
                <span class="material-icons-round">save</span>
                Save Template
            </span>

            <span wire:loading wire:target="save">
                <span class="material-icons-round"
                      style="font-size:16px;animation:spin .7s linear infinite">
                    sync
                </span>
                Saving...
            </span>

        </button>
    </div>

</div>


@push('scripts')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-lite.min.css">

<script>
(function loadSummernote() {

    function attachSummernote() {
        var el = document.getElementById('certificateContent');
        if (!el) return;
        if ($(el).data('summernote-init')) return;
        $(el).data('summernote-init', true);

        $(el).summernote({
            height: 300,
            placeholder: 'Write certificate content here...',
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
                    var component = Livewire.find(
                        document.querySelector('[wire\\:id]').getAttribute('wire:id')
                    );
                    component.set('certificate_content', contents);
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
        });

        // also try immediately in case livewire:initialized already fired
        setTimeout(attachSummernote, 300);
    }

    var jqSrc   = 'https://code.jquery.com/jquery-3.7.1.min.js';
    var snSrc   = 'https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-lite.min.js';

    if (typeof jQuery === 'undefined') {
        loadScript(jqSrc, function () {
            loadScript(snSrc, initWhenReady);
        });
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

            // ── 1. Text / Textarea is-filled ──
            document.querySelectorAll('.input-group-outline input, .input-group-outline textarea').forEach(function (input) {
                var group = input.closest('.input-group');
                if (!group) return;
                if (input.value && input.value.trim() !== '') {
                    group.classList.add('is-filled');
                } else {
                    group.classList.remove('is-filled');
                }
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

            // ── 2. Select is-filled ──
            document.querySelectorAll('.input-group-outline select').forEach(function (select) {
                var group = select.closest('.input-group');
                if (!group) return;
                if (select.value && select.value !== '') {
                    group.classList.add('is-filled');
                } else {
                    group.classList.remove('is-filled');
                }
                if (select._materialInit) return;
                select._materialInit = true;
                select.addEventListener('change', function () {
                    group.classList.toggle('is-filled', !!select.value);
                });
                select.addEventListener('focus', function () { group.classList.add('is-focused'); });
                select.addEventListener('blur', function () { group.classList.remove('is-focused'); });
            });

            // ── 3. Custom Select rebuild ──
            document.querySelectorAll('.input-group-outline .form-select').forEach(function (select) {
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