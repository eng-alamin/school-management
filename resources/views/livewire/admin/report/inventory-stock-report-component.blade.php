{{-- resources/views/livewire/admin/report/inventory-stock-report-component.blade.php --}}
<div>

    <div class="card">

        <div class="mat-card-header header-primary-gradient">
            <h5 id="cardHeaderTitleAllsections">Stock Report</h5>
            <p id="cardHeaderSubtitle">Product-wise current stock (purchased − sold) এবং stock value দেখুন।</p>
        </div>

        <div class="card-body pt-3">

            @include('admin.report.inventory-nav')

            {{-- Summary cards --}}
            <div class="row g-2 mb-3">
                <div class="col-6 col-md-3">
                    <div class="summary-card summary-present">
                        <span class="material-icons-round">inventory_2</span>
                        <div class="count">{{ $summary['total_products'] }}</div>
                        <div class="label">Total Products</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="summary-card summary-late">
                        <span class="material-icons-round">payments</span>
                        <div class="count">৳{{ number_format($summary['total_stock_value'], 2) }}</div>
                        <div class="label">Stock Value</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="summary-card summary-leave">
                        <span class="material-icons-round">trending_down</span>
                        <div class="count">{{ $summary['low_stock'] }}</div>
                        <div class="label">Low Stock (≤{{ $lowStockThreshold }})</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="summary-card summary-absent">
                        <span class="material-icons-round">remove_shopping_cart</span>
                        <div class="count">{{ $summary['out_of_stock'] }}</div>
                        <div class="label">Out of Stock</div>
                    </div>
                </div>
            </div>

            {{-- Toolbar / Filters --}}
            <div class="card-toolbar flex-wrap mb-2">
                <div class="col-md-3">
                    <div class="input-group input-group-outline" wire:ignore>
                        <label class="form-label">Category</label>
                        <select class="form-select form-select-sm" wire:model.live="categoryId">
                            <option value="all">All Categories</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Search</label>
                    <div style="position:relative;display:inline-flex;align-items:center;width:100%">
                        <span class="material-icons-round" style="position:absolute;left:10px;font-size:16px;color:var(--muted);pointer-events:none">search</span>
                        <input type="text" wire:model.live.debounce.400ms="search" placeholder="Product name / code..." class="form-control form-control-sm" style="padding-left:32px;">
                    </div>
                </div>

                <div class="col-md-3 d-flex align-items-end">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="lowStockOnly" wire:model.live="lowStockOnly">
                        <label class="form-check-label" for="lowStockOnly" style="font-size:.85rem;">Low stock only</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th id="th-sl">SL</th>
                            <th id="th-name" role="button" wire:click="sortBy('name')">
                                Product
                                @if($sortField === 'name')
                                    <span class="material-icons-round" style="font-size:.9rem;vertical-align:middle;">{{ $sortDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>
                                @endif
                            </th>
                            <th id="th-category">Category</th>
                            <th id="th-purchased" role="button" wire:click="sortBy('purchased_qty')">
                                Purchased
                                @if($sortField === 'purchased_qty')
                                    <span class="material-icons-round" style="font-size:.9rem;vertical-align:middle;">{{ $sortDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>
                                @endif
                            </th>
                            <th id="th-sold" role="button" wire:click="sortBy('sold_qty')">
                                Sold
                                @if($sortField === 'sold_qty')
                                    <span class="material-icons-round" style="font-size:.9rem;vertical-align:middle;">{{ $sortDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>
                                @endif
                            </th>
                            <th id="th-stock" role="button" wire:click="sortBy('current_stock')">
                                Current Stock
                                @if($sortField === 'current_stock')
                                    <span class="material-icons-round" style="font-size:.9rem;vertical-align:middle;">{{ $sortDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>
                                @endif
                            </th>
                            <th id="th-value">Stock Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($records as $i => $record)
                        <tr wire:key="stock-report-{{ $record->id }}">
                            <td class="text-muted">{{ $records->firstItem() + $i }}</td>
                            <td>
                                <div class="fw-500 text-dark">{{ $record->name }}</div>
                                <small class="text-muted">{{ $record->code }}</small>
                            </td>
                            <td class="text-muted" style="font-size:.78rem;">{{ $record->category?->name ?? '—' }}</td>
                            <td class="text-muted" style="font-size:.78rem;">
                                {{ $record->purchased_qty }} {{ $record->purchaseUnit?->name }}
                            </td>
                            <td class="text-muted" style="font-size:.78rem;">
                                {{ $record->sold_qty }} {{ $record->salesUnit?->name }}
                            </td>
                            <td>
                                @php
                                    $stockClass = $record->current_stock <= 0
                                        ? 'bg-danger-subtle text-danger'
                                        : ($record->current_stock <= $lowStockThreshold ? 'bg-warning-subtle text-warning' : 'bg-success-subtle text-success');
                                @endphp
                                <span class="badge rounded-pill {{ $stockClass }}" style="font-size:.72rem;">{{ $record->current_stock }}</span>
                            </td>
                            <td class="text-dark" style="font-size:.78rem;">৳{{ number_format($record->stock_value, 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <span class="material-icons-round d-block mb-2" style="font-size:2.5rem;opacity:.25;">inventory_2</span>
                                কোনো product পাওয়া যায়নি।
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer border-0 bg-white d-flex align-items-center justify-content-between flex-wrap gap-2 py-2 px-3">
            <small class="text-muted">Showing {{ $records->firstItem() ?? 0 }}–{{ $records->lastItem() ?? 0 }} of {{ $records->total() }}</small>
            {{ $records->links('vendor.pagination.custom') }}
        </div>

    </div>

</div>