<div>
    <div class="card">

        {{-- Floating Header --}}
        <div class="mat-card-header header-primary-gradient">
            <h5>Fee Invoices</h5>
            <p>View and manage student fee invoices by class and section</p>
        </div>

        <div class="card-header border-0">
            <div class="card-toolbar">

                {{-- Search --}}
                <div class="card-toolbar-title">
                    <div style="position:relative;display:inline-flex;align-items:center">
                        <span class="material-icons-round" style="position:absolute;left:10px;font-size:17px;color:var(--muted);pointer-events:none">search</span>
                        <input type="text" wire:model.live.debounce.300ms="search"
                            placeholder="Search by name, roll or register no"
                            style="border:1px solid rgba(0,0,0,.1);border-radius:8px;padding:7px 12px 7px 32px;font-size:.78rem;font-family:inherit;color:var(--dark);outline:none;background:#f8f9fa;width:240px"/>
                    </div>
                </div>

                {{-- Class filter --}}
                <div class="col-md-2">
                    <div class="input-group input-group-outline">
                        <select wire:model.live="filterClass" class="form-select form-select-sm">
                            <option value="">All Classes</option>
                            @foreach ($classes as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Section filter --}}
                <div class="col-md-2">
                    <div class="input-group input-group-outline">
                        <select wire:model.live="filterSection" class="form-select form-select-sm"
                            {{ empty($availableSections) ? 'disabled' : '' }}>
                            <option value="">All Sections</option>
                            @if(!empty($availableSections))
                                @foreach ($availableSections as $s)
                                    <option value="{{ $s['id'] }}">{{ $s['name'] }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                </div>

                {{-- Per page --}}
                @if($students->total() > 10)
                    <div class="col-md-2">
                        <div class="input-group input-group-outline">
                            <select class="form-select form-select-sm" wire:model.live="perPage">
                                <option value="10">10 / page</option>
                                <option value="25">25 / page</option>
                                <option value="50">50 / page</option>
                            </select>
                        </div>
                    </div>
                @endif

            </div>
        </div>

        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th id="th-sl">SL</th>
                            <th id="th-name" wire:click="sortBy('name')" style="cursor:pointer">
                                Name @if($sortField === 'name') {!! $sortDir === 'asc' ? '↑' : '↓' !!} @endif
                            </th>
                            <th id="th-class">Class</th>
                            <th id="th-section">Section</th>
                            <th id="th-register-no">Register No</th>
                            <th id="th-roll-no" wire:click="sortBy('roll_no')" style="cursor:pointer">
                                Roll No @if($sortField === 'roll_no') {!! $sortDir === 'asc' ? '↑' : '↓' !!} @endif
                            </th>
                            <th id="th-fee-items">Fee Items</th>
                            <th id="th-total">Total</th>
                            <th id="th-status">Status</th>
                            <th id="th-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($students as $i => $student)
                        @php
                            $invoices     = $student->feeInvoices;
                            $totalAmount  = $invoices->sum('total_amount');
                            $allPaid      = $invoices->isNotEmpty() && $invoices->every(fn($inv) => $inv->payment_status === 'paid');
                            $nonePaid     = $invoices->isEmpty() || $invoices->every(fn($inv) => $inv->payment_status === 'unpaid');
                            $feeItemNames = $invoices->flatMap->items->map(fn($item) => $item->feeSetup?->feeType?->name ?? '—')->unique();
                        @endphp
                        <tr wire:key="student-{{ $student->id }}">
                            <td class="text-muted">{{ $students->firstItem() + $i }}</td>
                            <td>
                                <a href="{{ route($routePrefix . 'student.overview', ['id' => $student->id]) }}" class="text-decoration-none" title="View Student Overview">
                                    {{ $student->name }}
                                </a>
                            </td>
                            <td>{{ $student->class->name ?? '—' }}</td>
                            <td>{{ $student->section->name ?? '—' }}</td>
                            <td>{{ $student->student_id ?? '—' }}</td>
                            <td>{{ $student->roll_no ?? '—' }}</td>
                            <td>
                                @forelse ($feeItemNames as $name)
                                    <span class="fee-tag">{{ $name }}</span>
                                @empty
                                    <span class="text-muted">—</span>
                                @endforelse
                            </td>
                            <td>
                                @if ($invoices->isEmpty())
                                    <span class="text-muted">—</span>
                                @elseif ($allPaid)
                                    <span class="amount-text text-success">৳{{ number_format($totalAmount, 0) }}</span>
                                @elseif ($nonePaid)
                                    <span class="amount-text text-danger">৳{{ number_format($totalAmount, 0) }}</span>
                                @else
                                    <span class="amount-text text-warning">৳{{ number_format($totalAmount, 0) }}</span>
                                @endif
                            </td>
                            <td>
                                @if ($invoices->isEmpty())
                                    <span class="text-muted">—</span>
                                @elseif ($allPaid)
                                    <span class="status-badge paid">Paid</span>
                                @elseif ($nonePaid)
                                    <span class="status-badge unpaid">Unpaid</span>
                                @else
                                    <span class="status-badge partial">Partial</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ route($routePrefix . 'student.invoice', ['id' => $student->id]) }}"
                                       target="_blank"
                                       class="act-btn view"
                                       title="View Invoice">
                                        <span class="material-icons-round">open_in_new</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center py-5 text-muted">
                                <span class="material-icons-round d-block mb-2" style="font-size:2.5rem;opacity:.2">inbox</span>
                                No students with fee invoices found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer border-0 bg-white d-flex align-items-center justify-content-between flex-wrap gap-2 py-2 px-3">
            <small class="text-muted">Showing {{ $students->firstItem() ?? 0 }}–{{ $students->lastItem() ?? 0 }} of {{ $students->total() }}</small>
            {{ $students->links('vendor.pagination.custom') }}
        </div>

    </div>

</div>