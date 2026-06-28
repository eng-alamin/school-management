<div class="mat-card" style="padding-top:28px">

    <!-- Floating Header -->
    <div class="mat-card-header header-pink-gradient">
        <h5>
            <span class="material-icons-round" style="font-size:18px;vertical-align:middle;margin-right:6px">
                account_balance_wallet
            </span>
            Add Deposit
        </h5>
        <p>Create new deposit record</p>
    </div>

    <!-- ══ DEPOSIT DETAILS ══ -->
    <div class="form-section">
        <div class="row g-4">

            <!-- Account -->
            <div class="col-md-12">
                <div class="input-group input-group-outline" wire:ignore>
                    <label class="form-label">Account <span class="req">*</span></label>
                    <select wire:model="account_id" class="form-select">
                        <option value="">Select</option>
                        @foreach($accounts as $account)
                            <option value="{{ $account->id }}">{{ $account->name }}</option>
                        @endforeach
                    </select>
                </div>
                @error('account_id') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <!-- Head -->
            <div class="col-md-12">
                <div class="input-group input-group-outline" wire:ignore>
                    <label class="form-label">Head</label>
                    <select wire:model="head_id" class="form-select">
                        <option value="">Select (Optional)</option>
                        @foreach($heads as $head)
                            <option value="{{ $head->id }}">{{ $head->name }}</option>
                        @endforeach
                    </select>
                </div>
                @error('head_id') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <!-- Pay Via -->
            <div class="col-md-6">
                <div class="input-group input-group-outline" wire:ignore>
                    <label class="form-label">Pay Via</label>
                    <select wire:model="pay_via" class="form-select">
                        <option value="">Select</option>
                        <option value="Cash">Cash</option>
                        <option value="Bank Transfer">Bank Transfer</option>
                        <option value="Cheque">Cheque</option>
                        <option value="Mobile Banking">Mobile Banking</option>
                        <option value="Card">Card</option>
                    </select>
                </div>
                @error('pay_via') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <!-- Reference -->
            <div class="col-md-6">
                <div class="input-group input-group-outline">
                    <label class="form-label">Reference</label>
                    <input type="text"
                           wire:model="reference"
                           class="form-control"
                           placeholder=" "
                           onfocus="focused(this)"
                           onfocusout="defocused(this)">
                </div>
                @error('reference') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <!-- Amount -->
            <div class="col-md-6">
                <div class="input-group input-group-outline">
                    <label class="form-label">Amount <span class="req">*</span></label>
                    <input type="number"
                           wire:model="amount"
                           class="form-control"
                           placeholder=" "
                           step="0.01"
                           min="0"
                           onfocus="focused(this)"
                           onfocusout="defocused(this)">
                </div>
                @error('amount') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <!-- Date -->
            <div class="col-md-6">
                <div>
                    <div class="input-group input-group-outline" wire:ignore>
                        <label class="form-label">Date <span class="req">*</span></label>
                        <input type="date"
                                wire:model="date"
                                data-dp-value="{{ $date }}"
                            class="form-control">
                    </div>
                    @error('date') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
            </div>

            <!-- Description -->
            <div class="col-12">
                <div class="input-group input-group-outline">
                    <label class="form-label">Description</label>
                    <textarea wire:model="description"
                              class="form-control"
                              style="min-height:120px"
                              placeholder=" "
                              onfocus="focused(this)"
                              onfocusout="defocused(this)"></textarea>
                </div>
                @error('description') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <!-- Attachment -->
            <div class="col-12">
                <label style="font-size:.73rem;font-weight:600;color:var(--muted);display:block;margin-bottom:8px">
                    Attachment
                </label>
                <div class="photo-upload-box">
                    <span class="material-icons-round">attach_file</span>
                    <span class="lbl">Click to upload attachment</span>
                    <small style="color:#bbb;font-size:.7rem">PDF, JPG, PNG up to 2MB</small>
                    <input type="file" wire:model="attachment" accept=".pdf,image/*">
                </div>
                @error('attachment') <span class="text-danger">{{ $message }}</span> @enderror
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
                Save
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
    <script>
        document.addEventListener('livewire:initialized', () => {

            setTimeout(() => initAllFields(), 100);

            Livewire.hook('morph.updated', ({ el }) => {
                setTimeout(() => initAllFields(), 0);
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