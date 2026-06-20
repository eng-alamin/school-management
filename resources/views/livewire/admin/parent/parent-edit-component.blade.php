<div class="mat-card" style="padding-top:28px">

    <!-- Floating Header -->
    <div class="mat-card-header header-pink-gradient">
        <h5>
            <span class="material-icons-round" style="font-size:18px;vertical-align:middle;margin-right:6px">
                badge
            </span>
            Parent Registration
        </h5>
        <p>Update existing parent record</p>
    </div>

    <!-- ══ GUARDIAN DETAILS ══ -->
    <div class="form-section">
        <div class="row g-4">
            <div class="col-md-6">
                <div class="input-group input-group-outline">
                    <label class="form-label">Name <span class="req">*</span></label>
                    <input type="text" wire:model="name" class="form-control" placeholder=" " onfocus="focused(this)" onfocusout="defocused(this)">
                    @error('name') <span class="text-danger">{{ $message }}</span> @enderror     
                </div>
            </div>
            <div class="col-md-6">
                <div class="input-group input-group-outline">
                    <label class="form-label">Relation <span class="req">*</span></label>
                    <input type="text" wire:model="relation" class="form-control" placeholder=" " onfocus="focused(this)" onfocusout="defocused(this)">
                    @error('relation') <span class="text-danger">{{ $message }}</span> @enderror     
                </div>
            </div>
            <div class="col-md-6">
                <div class="input-group input-group-outline">
                    <label class="form-label">Father Name</label>
                    <input type="text" wire:model="father_name" class="form-control" placeholder=" " onfocus="focused(this)" onfocusout="defocused(this)">
                    @error('father_name') <span class="text-danger">{{ $message }}</span> @enderror     
                </div>
            </div>
            <div class="col-md-6">
                <div class="input-group input-group-outline">
                    <label class="form-label">Mother Name</label>
                    <input type="text" wire:model="mother_name" class="form-control" placeholder=" " onfocus="focused(this)" onfocusout="defocused(this)">
                </div>
            </div>
            <div class="col-md-4">
                <div class="input-group input-group-outline">
                    <label class="form-label">Occupation</label>
                    <input type="text" wire:model="occupation" class="form-control" placeholder=" " onfocus="focused(this)" onfocusout="defocused(this)">
                    @error('occupation') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="col-md-4">
                <div class="input-group input-group-outline">
                    <label class="form-label">Income</label>
                    <input type="text" wire:model="income" class="form-control" placeholder=" " onfocus="focused(this)" onfocusout="defocused(this)">
                    @error('income') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="col-md-4">
                <div class="input-group input-group-outline">
                    <label class="form-label">Education</label>
                    <input type="text" wire:model="education" class="form-control" placeholder=" " onfocus="focused(this)" onfocusout="defocused(this)">
                    @error('education') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="input-group input-group-outline">
                    <label class="form-label">Mobile No <span class="req">*</span></label>
                    <input type="tel" wire:model="mobile" class="form-control" placeholder=" " onfocus="focused(this)" onfocusout="defocused(this)">
                    @error('mobile') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="input-group input-group-outline">
                    <label class="form-label">Email</label>
                    <input type="email" wire:model="email" class="form-control" placeholder=" " onfocus="focused(this)" onfocusout="defocused(this)">
                    @error('email') <span class="text-danger">{{ $message }}</span> @enderror  
                </div>
            </div>
            <div class="col-12">
                <div class="input-group input-group-outline">
                    <label class="form-label">Address</label>
                    <textarea wire:model="address" class="form-control" placeholder=" " onfocus="focused(this)" onfocusout="defocused(this)"></textarea>
                </div>
            </div>
            <div class="col-md-12">
                <label style="font-size:.73rem;font-weight:600;color:var(--muted);display:block;margin-bottom:8px">
                    Photo
                </label>
                    <div class="photo-upload-box">
                        @if($photo_upload)
                            <img src="{{ $photo_upload->temporaryUrl() }}" alt="Preview"
                                style="max-height:80px;max-width:100%;object-fit:contain;margin-bottom:6px">
                        @elseif($photo)
                            <img src="{{ asset('storage/' . $photo) }}" alt="Photo"
                                style="max-height:80px;max-width:100%;object-fit:contain;margin-bottom:6px">
                        @else
                            <span class="material-icons-round">image</span>
                            <span class="lbl">Click to upload</span>
                        @endif
                        <small style="color:#bbb;font-size:.7rem">PNG, JPG up to 2MB</small>
                        <input type="file" wire:model="photo_upload" accept="image/*">
                    </div>
                @error('photo_upload') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
        </div>
    </div>

    <!-- ══ LOGIN DETAILS ══ -->
    <div class="form-section">
        <div class="section-heading">
            <span class="material-icons-round">lock</span> Login Details
        </div>

        <div class="row g-4">
            <div class="col-md-6">
                <div class="input-group input-group-outline">
                    <label class="form-label">Username <span class="req">*</span></label>
                    <input type="text" wire:model="username" class="form-control" placeholder=" " onfocus="focused(this)" onfocusout="defocused(this)">
                </div>
                @error('username') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <div class="col-md-6">
                <div class="input-group input-group-outline">
                    <label class="form-label">Password</label>
                    <input type="password" wire:model="password" class="form-control" placeholder=" " onfocus="focused(this)" onfocusout="defocused(this)">
                </div>
                @error('password') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

        </div>
    </div>


    <!-- FORM FOOTER -->
    <div class="form-footer">
        <button type="button" class="btn-outline" onclick="history.back()">
            <span class="material-icons-round" style="font-size:16px">arrow_back</span>
            Back
        </button>

        <button class="btn-pink" type="button" wire:click="update" wire:loading.attr="disabled" wire:target="update">
            <span wire:loading.remove wire:target="update"  style="display: inline-flex;align-items: center;gap: 6px">
                <span class="material-icons-round">save</span>
                Update
            </span>

            <span wire:loading wire:target="update">
                <span class="material-icons-round" style="font-size:16px;animation:spin .7s linear infinite">sync</span>
                Updating...
            </span>
        </button>
    </div>

</div>


@push('scripts')
    <script>
        document.addEventListener('livewire:initialized', () => {

            // ✅ Initial load এ সব ঠিক করো
            setTimeout(() => initAllFields(), 100);

            // ✅ Livewire update এর পর
            Livewire.hook('morph.updated', ({ el }) => {
                setTimeout(() => initAllFields(), 50);
            });

            function initAllFields() {

                // ── 1. Text/Textarea is-filled ──
                document.querySelectorAll('.input-group-outline input, .input-group-outline textarea').forEach(function(input) {
                    var group = input.closest('.input-group');
                    if (!group) return;

                    // value থাকলে is-filled দাও
                    if (input.value && input.value.trim() !== '') {
                        group.classList.add('is-filled');
                    } else {
                        group.classList.remove('is-filled');
                    }

                    if (input._materialInit) return;
                    input._materialInit = true;

                    input.addEventListener('focus', function() {
                        group.classList.add('is-focused');
                    });
                    input.addEventListener('blur', function() {
                        group.classList.remove('is-focused');
                        group.classList.toggle('is-filled', !!input.value.trim());
                    });
                    input.addEventListener('input', function() {
                        group.classList.toggle('is-filled', !!input.value.trim());
                    });
                });

                // ── 2. Select is-filled + value set ──
                document.querySelectorAll('.input-group-outline select').forEach(function(select) {
                    var group = select.closest('.input-group');
                    if (!group) return;

                    // selected value থাকলে is-filled দাও
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
                    select.addEventListener('focus', function() {
                        group.classList.add('is-focused');
                    });
                    select.addEventListener('blur', function() {
                        group.classList.remove('is-focused');
                    });
                });

                // ── 3. Custom Select rebuild ──
                document.querySelectorAll('.input-group-outline .form-select').forEach(function(select) {
                    // পুরনো custom wrapper থাকলে remove করো
                    var old = select.parentNode.querySelector('.custom-select-wrapper');
                    if (old) old.remove();
                    select.style.display = '';

                    if (typeof buildCustomSelect === 'function') {
                        buildCustomSelect(select);
                    }
                });

                // ── 4. Datepicker ──
                if (typeof _initDatepickers === 'function') {
                    _initDatepickers();
                }
            }

        });
    </script>
@endpush