{{-- livewire/admin/branch/edit-component.blade.php --}}

<div>

    <div class="card">

        <div class="mat-card-header header-primary-gradient">
            <h5 id="branch-edit-header-title">Branch Registration</h5>
            <p id="branch-edit-header-subtitle">Update existing branch record</p>
        </div>

        <!-- ══ BRANCH DETAILS ══ -->
        <div class="form-section" style="padding-top:40px">
            <div class="section-heading">
                <span class="material-icons-round">store</span> Branch Details
            </div>

            <div class="row g-4">

                <div class="col-md-6">
                    <div class="input-group input-group-outline">
                        <label class="form-label"><span id="branch-edit-lbl-name">Branch Name</span> <span class="req">*</span></label>
                        <input type="text" wire:model.live.debounce.500ms="name" class="form-control" placeholder=" " autocomplete="off" onfocus="focused(this)" onfocusout="defocused(this)">
                    </div>
                    @error('name') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-6">
                    <div class="input-group input-group-outline">
                        <label class="form-label"><span id="branch-edit-lbl-code">Branch Code</span> <span class="req">*</span></label>
                        <input type="text" wire:model.live.debounce.500ms="code" class="form-control" placeholder=" " autocomplete="off" onfocus="focused(this)" onfocusout="defocused(this)">
                    </div>
                    @error('code') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-6">
                    <div class="input-group input-group-outline">
                        <label class="form-label" id="branch-edit-lbl-phone">Phone</label>
                        <input type="text" wire:model="phone" class="form-control" placeholder=" " onfocus="focused(this)" onfocusout="defocused(this)">
                    </div>
                    @error('phone') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-6">
                    <div class="input-group input-group-outline">
                        <label class="form-label" id="branch-edit-lbl-email">Email</label>
                        <input type="email" wire:model="email" class="form-control" placeholder=" " onfocus="focused(this)" onfocusout="defocused(this)">
                    </div>
                    @error('email') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="col-12">
                    <div class="input-group input-group-outline">
                        <label class="form-label" id="branch-edit-lbl-address">Address</label>
                        <textarea wire:model="address" class="form-control" placeholder=" " style="min-height:90px" onfocus="focused(this)" onfocusout="defocused(this)"></textarea>
                    </div>
                    @error('address') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-6">
                    <div class="input-group input-group-outline" wire:ignore>
                        <label class="form-label" id="branch-edit-lbl-status">Status</label>
                        <select wire:model="is_active" class="form-select" @if($is_main) disabled @endif>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                    @if($is_main)
                        <small class="text-muted d-block mt-1">
                            <span class="material-icons-round align-middle" style="font-size:14px">info</span>
                            Main branch is always kept active.
                        </small>
                    @endif
                    @error('is_active') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                @if($is_main)
                    <div class="col-md-6 d-flex align-items-end">
                        <span class="badge bg-dark">Main Branch</span>
                    </div>
                @endif

            </div>
        </div>

        <!-- FORM FOOTER -->
        <div class="form-footer">
            <button type="button" class="btn btn-secondary" onclick="history.back()">
                <span>
                    <span class="material-icons-round">arrow_back</span>
                    <span>Back</span>
                </span>
            </button>

            <button class="btn btn-primary" type="button" wire:click="update" wire:loading.attr="disabled" wire:target="update">
                <span wire:loading.remove wire:target="update">
                    <span class="material-icons-round">save</span>
                    <span>Update</span>
                </span>

                <span wire:loading wire:target="update">
                    <span class="material-icons-round" style="animation:spin .7s linear infinite">sync</span>
                    <span>Updating...</span>
                </span>
            </button>
        </div>

    </div>

</div>

@push('scripts')
    <script>
        (function () {

            function initAllFields() {

                // ── 1. Text/Textarea is-filled ──
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

            document.addEventListener('livewire:initialized', () => {
                setTimeout(() => initAllFields(), 100);
            });

            document.addEventListener('livewire:navigated', () => {
                setTimeout(() => initAllFields(), 100);
            });

            document.addEventListener('livewire:init', () => {
                Livewire.hook('morph.updated', ({ el }) => {
                    setTimeout(() => initAllFields(), 50);
                });
            });

        })();
    </script>
@endpush