<div class="mat-card" style="padding-top:28px">

    <!-- Floating Header -->
    <div class="mat-card-header header-pink-gradient">
        <h5>
            <span class="material-icons-round" style="font-size:18px;vertical-align:middle;margin-right:6px">
                point_of_sale
            </span>
            Edit Sale
        </h5>
        <p>Update existing sale bill record</p>
    </div>

    <!-- ══ SALE DETAILS ══ -->
    <div class="form-section">
        <div class="row g-4">

            <!-- Role -->
            <div class="col-md-6">
                <div class="input-group input-group-outline" wire:ignore>
                    <label class="form-label">Role <span class="req">*</span></label>
                    <select wire:model.live="role" class="form-select">
                        <option value="">Select</option>
                        <option value="student">Student</option>
                        <option value="employee">Employee</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                @error('role') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <!-- Class (only when role = student) -->
            @if($role === 'student')
                <div class="col-md-6">
                    <div class="input-group input-group-outline" wire:ignore>
                        <label class="form-label">Class <span class="req">*</span></label>
                        <select wire:model.live="class_id" class="form-select">
                            <option value="">Select</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}">{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error('class_id') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
            @endif

            <!-- Sale To (saleable_id) -->
            <div class="col-md-6">
                <div class="input-group input-group-outline">
                    <label class="form-label">Sale To <span class="req">*</span></label>
                    <select wire:model="saleable_id" class="form-select">
                        <option value="">Select</option>
                        @foreach($saleables as $saleable)
                            @if($role === 'student')
                                <option value="{{ $saleable->id }}">{{ $saleable->full_name }}</option>
                            @else
                                <option value="{{ $saleable->id }}">{{ $saleable->name }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
                @error('saleable_id') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <!-- Bill No -->
            <div class="col-md-6">
                <div class="input-group input-group-outline">
                    <label class="form-label">Bill No <span class="req">*</span></label>
                    <input type="text"
                           wire:model="bill_no"
                           class="form-control"
                           placeholder=" "
                           onfocus="focused(this)"
                           onfocusout="defocused(this)">
                </div>
                @error('bill_no') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <!-- Date -->
            <div class="col-md-6">
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
    </div>

    <!-- ══ SALE ITEMS ══ -->
    <div class="form-section" style="margin-top:8px">

        @error('items') <span class="text-danger d-block mb-2">{{ $message }}</span> @enderror

        <!-- Items Table -->
        <div style="overflow-x:auto">
            <table style="width:100%;border-collapse:collapse;font-size:.82rem">
                <thead>
                    <tr style="border-bottom:2px solid var(--border,#e9ecef)">
                        <th style="padding:8px 10px;text-align:left;color:var(--muted);font-weight:600;white-space:nowrap">Category <span class="req">*</span></th>
                        <th style="padding:8px 10px;text-align:left;color:var(--muted);font-weight:600;white-space:nowrap">Product <span class="req">*</span></th>
                        <th style="padding:8px 10px;text-align:left;color:var(--muted);font-weight:600;white-space:nowrap">Unit Price</th>
                        <th style="padding:8px 10px;text-align:left;color:var(--muted);font-weight:600;white-space:nowrap">Quantity <span class="req">*</span></th>
                        <th style="padding:8px 10px;text-align:left;color:var(--muted);font-weight:600;white-space:nowrap">Discount</th>
                        <th style="padding:8px 10px;text-align:right;color:var(--muted);font-weight:600;white-space:nowrap">Total Price</th>
                        <th style="padding:8px 10px;text-align:center;color:var(--muted);font-weight:600"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $index => $item)
                        <tr style="border-bottom:1px solid var(--border,#f0f0f0)">

                            <!-- Category -->
                            <td style="padding:6px 10px;min-width:170px">
                                <div class="input-group input-group-outline" wire:ignore.self style="margin-bottom:0">
                                    <select wire:model.live="items.{{ $index }}.category_id" class="form-select">
                                        <option value="" disabled>Select category</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}"
                                                {{ (string)($item['category_id'] ?? '') === (string)$category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @error("items.{$index}.category_id") <span class="text-danger" style="font-size:.72rem">{{ $message }}</span> @enderror
                            </td>

                            <!-- Product (filtered by category) -->
                            <td style="padding:6px 10px;min-width:180px">
                                <div class="input-group input-group-outline" wire:ignore.self style="margin-bottom:0">
                                    <select wire:model.live="items.{{ $index }}.product_id" class="form-select">
                                        <option value="">
                                            {{ empty($item['category_id']) ? 'First Select The Category' : 'Select' }}
                                        </option>
                                        @if(!empty($item['category_id']))
                                            @foreach($categories->find($item['category_id'])?->products ?? [] as $product)
                                                <option value="{{ $product->id }}"
                                                    {{ (string)($item['product_id'] ?? '') === (string)$product->id ? 'selected' : '' }}>
                                                    {{ $product->name }}
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                                @error("items.{$index}.product_id") <span class="text-danger" style="font-size:.72rem">{{ $message }}</span> @enderror
                            </td>

                            <!-- Unit Price -->
                            <td style="padding:6px 10px;min-width:120px">
                                <div class="input-group input-group-outline" style="margin-bottom:0">
                                    <input type="number"
                                           wire:model.live="items.{{ $index }}.unit_price"
                                           class="form-control form-control-sm"
                                           style="font-size:.8rem"
                                           placeholder="0.00"
                                           min="0"
                                           step="0.01"
                                           onfocus="focused(this)"
                                           onfocusout="defocused(this)">
                                </div>
                                @error("items.{$index}.unit_price") <span class="text-danger" style="font-size:.72rem">{{ $message }}</span> @enderror
                            </td>

                            <!-- Quantity -->
                            <td style="padding:6px 10px;min-width:100px">
                                <div class="input-group input-group-outline" style="margin-bottom:0">
                                    <input type="number"
                                           wire:model.live="items.{{ $index }}.quantity"
                                           class="form-control form-control-sm"
                                           style="font-size:.8rem"
                                           placeholder="1"
                                           min="1"
                                           step="1"
                                           onfocus="focused(this)"
                                           onfocusout="defocused(this)">
                                </div>
                                @error("items.{$index}.quantity") <span class="text-danger" style="font-size:.72rem">{{ $message }}</span> @enderror
                            </td>

                            <!-- Discount -->
                            <td style="padding:6px 10px;min-width:110px">
                                <div class="input-group input-group-outline" style="margin-bottom:0">
                                    <input type="number"
                                           wire:model.live="items.{{ $index }}.discount"
                                           class="form-control form-control-sm"
                                           style="font-size:.8rem"
                                           placeholder="0"
                                           min="0"
                                           step="0.01"
                                           onfocus="focused(this)"
                                           onfocusout="defocused(this)">
                                </div>
                                @error("items.{$index}.discount") <span class="text-danger" style="font-size:.72rem">{{ $message }}</span> @enderror
                            </td>

                            <!-- Total Price (read-only) -->
                            <td style="padding:10px 10px;text-align:right;font-weight:600;white-space:nowrap">
                                {{ number_format($item['total_price'] ?? 0, 2) }}
                            </td>

                            <!-- Remove -->
                            <td style="padding:6px 10px;text-align:center">
                                @if(count($items) > 1)
                                    <button type="button"
                                            wire:click="removeItem({{ $index }})"
                                            style="background:none;border:none;cursor:pointer;color:#e74c3c;padding:4px"
                                            title="Remove">
                                        <span class="material-icons-round" style="font-size:18px">delete_outline</span>
                                    </button>
                                @endif
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="padding:24px;text-align:center;color:var(--muted)">
                                No items added yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Add Rows button -->
        <div style="margin-top:12px">
            <button class="btn-outline"
                    type="button"
                    wire:click="addItem"
                    style="padding:5px 14px;font-size:.78rem">
                <span class="material-icons-round" style="font-size:15px">add</span>
                Add Rows
            </button>
        </div>

    </div>

    <!-- ══ BILL SUMMARY ══ -->
    <div class="form-section" style="margin-top:8px">
        <div class="row justify-content-end">
            <div class="col-md-6">

                <div style="border:1px solid var(--border,#e9ecef);border-radius:12px;padding:24px;background:var(--card-bg,#fff)">
                    <h6 style="font-weight:700;font-size:.9rem;margin-bottom:20px">
                        <span class="material-icons-round" style="font-size:16px;vertical-align:middle;margin-right:6px;color:var(--muted)">receipt_long</span>
                        Bill Summary
                    </h6>

                    <!-- Sub Total -->
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px">
                        <span style="font-size:.83rem;color:var(--muted);font-weight:600">Sub Total</span>
                        <div style="display:flex;align-items:center;gap:6px">
                            {{-- <span style="font-size:.8rem;color:var(--muted);font-weight:600">$</span> --}}
                            <div style="background:var(--input-bg,#f8f9fa);border:1px solid var(--border,#e9ecef);border-radius:8px;padding:6px 12px;min-width:120px;text-align:right;font-size:.83rem;font-weight:600">
                                {{ number_format($sub_total, 2) }}
                            </div>
                        </div>
                    </div>

                    <!-- Discount -->
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px">
                        <span style="font-size:.83rem;color:var(--muted);font-weight:600">Discount ( - )</span>
                        <div style="display:flex;align-items:center;gap:6px">
                            {{-- <span style="font-size:.8rem;color:var(--muted);font-weight:600">$</span> --}}
                            <div style="background:var(--input-bg,#f8f9fa);border:1px solid var(--border,#e9ecef);border-radius:8px;padding:6px 12px;min-width:120px;text-align:right;font-size:.83rem;font-weight:600">
                                {{ number_format($total_discount, 2) }}
                            </div>
                        </div>
                    </div>

                    <!-- Net Payable -->
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;padding-top:10px;border-top:1px solid var(--border,#e9ecef)">
                        <span style="font-size:.85rem;font-weight:700">Net Payable</span>
                        <div style="display:flex;align-items:center;gap:6px">
                            {{-- <span style="font-size:.8rem;color:var(--muted);font-weight:600">$</span> --}}
                            <div style="background:var(--input-bg,#f8f9fa);border:1px solid var(--border,#e9ecef);border-radius:8px;padding:6px 12px;min-width:120px;text-align:right;font-size:.85rem;font-weight:700;color:var(--primary,#e91e8c)">
                                {{ number_format($net_payable, 2) }}
                            </div>
                        </div>
                    </div>

                    <!-- Received Amount -->
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px">
                        <span style="font-size:.83rem;color:var(--muted);font-weight:600">Received Amount</span>
                        <div class="input-group input-group-outline" style="max-width:160px;margin-bottom:0">
                            <input type="number"
                                   wire:model.live="received_amount"
                                   class="form-control form-control-sm"
                                   style="font-size:.82rem;text-align:right"
                                   placeholder="Enter Payment Amount"
                                   min="0"
                                   step="0.01"
                                   onfocus="focused(this)"
                                   onfocusout="defocused(this)">
                        </div>
                    </div>
                    @error('received_amount') <div class="text-danger text-end mb-2" style="font-size:.75rem">{{ $message }}</div> @enderror

                    <!-- Pay Via -->
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px">
                        <span style="font-size:.83rem;color:var(--muted);font-weight:600">Pay Via</span>
                        <div class="input-group input-group-outline" wire:ignore style="max-width:160px;margin-bottom:0">
                            <select wire:model="pay_via" class="form-select form-select-sm" style="font-size:.82rem">
                                <option value="">Select</option>
                                <option value="cash">Cash</option>
                                <option value="bank">Bank Transfer</option>
                                <option value="card">Card</option>
                                <option value="mobile_banking">Mobile Banking</option>
                                <option value="cheque">Cheque</option>
                            </select>
                        </div>
                    </div>
                    @error('pay_via') <div class="text-danger text-end mb-2" style="font-size:.75rem">{{ $message }}</div> @enderror

                    <!-- Remarks -->
                    <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:4px">
                        <span style="font-size:.83rem;color:var(--muted);font-weight:600;padding-top:8px">Remarks</span>
                        <div class="input-group input-group-outline" style="max-width:160px;margin-bottom:0">
                            <textarea wire:model="remarks"
                                      class="form-control form-control-sm"
                                      style="font-size:.82rem;min-height:60px;resize:none"
                                      placeholder="Write Your Remarks"
                                      onfocus="focused(this)"
                                      onfocusout="defocused(this)"></textarea>
                        </div>
                    </div>
                    @error('remarks') <div class="text-danger text-end mb-2" style="font-size:.75rem">{{ $message }}</div> @enderror

                </div>

            </div>
        </div>
    </div>

    <!-- FORM FOOTER -->
    <div class="form-footer">

        <a href="{{ route('admin.inventory.sale.list') }}"
           class="btn-outline">
            <span class="material-icons-round" style="font-size:16px">arrow_back</span>
            Back
        </a>

        <button class="btn-pink"
                type="button"
                wire:click="save"
                wire:loading.attr="disabled"
                wire:target="save">

            <span wire:loading.remove wire:target="save">
                <span class="material-icons-round">save</span>
                Update Bill
            </span>

            <span wire:loading wire:target="save">
                <span class="material-icons-round"
                      style="font-size:16px;animation:spin .7s linear infinite">
                    sync
                </span>
                Updating...
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