<div>
    <div class="card">

        <!-- Floating Header -->
        <div class="mat-card-header header-primary-gradient">
            <h5 id="purchase-add-title">
                Add Purchase
            </h5>
            <p id="purchase-add-subtitle">Create new purchase record</p>
        </div>

        <!-- ══ PURCHASE DETAILS ══ -->
        <div class="form-section">
            <div class="row g-4">

                <!-- Supplier -->
                <div class="col-md-6">
                    <div class="input-group input-group-outline">
                        <label class="form-label">Supplier</label>
                        <select wire:model="supplier_id" class="form-select">
                            <option value="">Select</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error('supplier_id') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <!-- Store -->
                <div class="col-md-6">
                    <div class="input-group input-group-outline">
                        <label class="form-label">Store</label>
                        <select wire:model="store_id" class="form-select">
                            <option value="">Select</option>
                            @foreach($stores as $store)
                                <option value="{{ $store->id }}">{{ $store->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error('store_id') <span class="text-danger">{{ $message }}</span> @enderror
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

                <!-- Purchase Status -->
                <div class="col-md-6">
                    <div class="input-group input-group-outline">
                        <label class="form-label">Purchase Status <span class="req">*</span></label>
                        <select wire:model="purchase_status" class="form-select">
                            <option value="">Select</option>
                            <option value="pending">Pending</option>
                            <option value="ordered">Ordered</option>
                            <option value="completed">Completed</option>
                            <option value="received">Received</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    @error('purchase_status') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <!-- Remarks -->
                <div class="col-md-6">
                    <div class="input-group input-group-outline">
                        <label class="form-label">Remarks</label>
                        <input type="text"
                            wire:model="remarks"
                            class="form-control"
                            placeholder=" "
                            onfocus="focused(this)"
                            onfocusout="defocused(this)">
                    </div>
                    @error('remarks') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

            </div>
        </div>

        <!-- ══ PURCHASE ITEMS ══ -->
        <div class="form-section" style="margin-top:8px">

            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
                <h6 style="margin:0;font-weight:600;font-size:.85rem;color:var(--muted)">
                    Purchase Items
                </h6>
                <button class="btn btn-primary"
                        type="button"
                        wire:click="addItem"
                        style="padding:5px 14px;font-size:.78rem">
                    <span class="material-icons-round" style="font-size:15px">add</span>
                    Add Item
                </button>
            </div>

            @error('items') <span class="text-danger d-block mb-2">{{ $message }}</span> @enderror

            <!-- Items Table -->
            <div style="overflow-x:auto">
                <table style="width:100%;border-collapse:collapse;font-size:.82rem">
                    <thead>
                        <tr style="border-bottom:2px solid var(--border,#e9ecef)">
                            <th id="padd-th-no" style="padding:8px 10px;text-align:left;color:var(--muted);font-weight:600;white-space:nowrap">#</th>
                            <th id="padd-th-product" style="padding:8px 10px;text-align:left;color:var(--muted);font-weight:600;white-space:nowrap">Product <span class="req">*</span></th>
                            <th id="padd-th-unit-price" style="padding:8px 10px;text-align:left;color:var(--muted);font-weight:600;white-space:nowrap">Unit Price <span class="req">*</span></th>
                            <th id="padd-th-qty" style="padding:8px 10px;text-align:left;color:var(--muted);font-weight:600;white-space:nowrap">Qty <span class="req">*</span></th>
                            <th id="padd-th-discount" style="padding:8px 10px;text-align:left;color:var(--muted);font-weight:600;white-space:nowrap">Discount</th>
                            <th id="padd-th-total" style="padding:8px 10px;text-align:right;color:var(--muted);font-weight:600;white-space:nowrap">Total</th>
                            <th style="padding:8px 10px;text-align:center;color:var(--muted);font-weight:600"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $index => $item)
                            <tr style="border-bottom:1px solid var(--border,#f0f0f0)">

                                <!-- Row # -->
                                <td style="padding:10px 10px;color:var(--muted)">{{ $index + 1 }}</td>

                                <!-- Product -->
                                <td style="padding:6px 10px;min-width:180px">
                                    <div class="input-group input-group-outline".self style="margin-bottom:0">
                                        <select wire:model.live="items.{{ $index }}.product_id"
                                                class="form-select form-select-sm"
                                                style="font-size:.8rem">
                                            <option value="">Select</option>
                                            @foreach($products as $product)
                                                <option value="{{ $product->id }}">{{ $product->name }}</option>
                                            @endforeach
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
                                <td style="padding:6px 10px;min-width:90px">
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
                                            placeholder="0.00"
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
                                    No items added yet. Click "Add Item" to begin.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                    <!-- Net Total Footer -->
                    @if(count($items) > 0)
                        <tfoot>
                            <tr style="border-top:2px solid var(--border,#e9ecef)">
                                <td colspan="5"
                                    style="padding:12px 10px;text-align:right;font-weight:700;font-size:.85rem">
                                    Net Total
                                </td>
                                <td style="padding:12px 10px;text-align:right;font-weight:700;font-size:.92rem;color:var(--primary,#e91e8c)">
                                    {{ number_format($net_total, 2) }}
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    @endif

                </table>
            </div>

        </div>

        <!-- FORM FOOTER -->
        <div class="form-footer">

            <button class="btn btn-primary"
                    type="button"
                    wire:click="resetForm">
                <span class="material-icons-round" style="font-size:16px">refresh</span>
                Reset
            </button>

            <button class="btn btn-primary"
                    type="button"
                    wire:click="save"
                    wire:loading.attr="disabled"
                    wire:target="save">

                <span wire:loading.remove wire:target="save" style="display: inline-flex;align-items: center;gap: 6px">
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
</div>