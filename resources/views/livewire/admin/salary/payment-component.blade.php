<div class="mat-card" style="padding-top:28px">

    {{-- ── Toast Notification ── --}}
    <div x-data="{ show: false, type: 'success', message: '' }"
         x-on:notify.window="show = true; type = $event.detail.type; message = $event.detail.message; setTimeout(() => show = false, 3500)"
         x-show="show"
         x-transition
         :class="type === 'success' ? 'alert-success' : 'alert-danger'"
         class="alert alert-dismissible position-fixed top-0 end-0 m-3 shadow"
         style="z-index:9999;min-width:260px;display:none">
        <span x-text="message"></span>
    </div>

    {{-- ── Floating Header ── --}}
    <div class="mat-card-header header-pink-gradient">
        <h5> Payroll</h5>
        <p>Process and manage employee salary payments by role and month</p>
    </div>

    {{-- ══════════════════════════════════════
         SELECT GROUND
    ══════════════════════════════════════ --}}
    <div class="form-section" style="padding-top:40px;padding-bottom:20px">
        <div class="section-heading">
            <span class="material-icons-round">tune</span> Select Ground
        </div>

        <div class="row g-4">

            {{-- Role --}}
            <div class="col-md-6">
                <div class="input-group input-group-outline" wire:ignore>
                    <label class="form-label">Role <span class="req">*</span></label>
                    <select wire:model.live="role" class="form-select">
                        <option value="">Select Role</option>
                        @foreach($this->roles as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                @error('role') <span class="text-danger small">{{ $message }}</span> @enderror
            </div>

            {{-- Month --}}
            <div class="col-md-6">
                <div class="input-group input-group-outline" wire:ignore>
                    <label class="form-label">Month <span class="req">*</span></label>
                    <input type="month"
                           wire:model.live="month"
                           class="form-control"
                           style="padding:10px 14px;">
                </div>
                @error('month') <span class="text-danger small">{{ $message }}</span> @enderror
            </div>

            {{-- Filter Button --}}
            <div class="col-md-12 d-flex justify-content-end">
                <button wire:click="filter"
                        wire:loading.attr="disabled"
                        wire:target="filter"
                        class="btn-pink d-flex align-items-center gap-1"
                        type="button">
                    <span wire:loading.remove wire:target="filter">
                        <span class="material-icons-round" style="font-size:16px;vertical-align:middle;margin-right:4px">filter_alt</span>
                        Filter
                    </span>
                    <span wire:loading wire:target="filter">
                        <span class="material-icons-round" style="font-size:16px;animation:spin .7s linear infinite">sync</span>
                        Filtering...
                    </span>
                </button>
            </div>

        </div>
    </div>

    {{-- ══════════════════════════════════════
         STAFF LIST
    ══════════════════════════════════════ --}}
    @if($hasFiltered)
    <div class="form-section">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
            <div class="section-heading mb-0">
                <span class="material-icons-round">people</span> Staff List
            </div>

            
                <div class="d-flex align-items-center gap-3">
                    @if($employees->total() > 10)
                        <select wire:model.live="perPage" class="form-select form-select-sm" style="width:90px">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    @endif
                    <div style="position:relative;display:inline-flex;align-items:center">
                        <span class="material-icons-round" style="position:absolute;left:10px;font-size:16px;color:var(--muted);pointer-events:none">search</span>
                        <input type="text"
                            wire:model.live.debounce.300ms="search"
                            placeholder="Search by name or ID"
                            style="border:1px solid rgba(0,0,0,.1);border-radius:8px;padding:6px 12px 6px 32px;font-size:.78rem;font-family:inherit;outline:none;background:#f8f9fa;width:220px" />
                    </div>
                </div>
        </div>

        @if($employees && $employees->count() > 0)
        <div class="table-responsive mt-3">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Staff Id</th>
                        <th>Name</th>
                        <th>Designation</th>
                        <th>Department</th>
                        <th>Mobile No</th>
                        <th>Salary Grade (GPA)</th>
                        <th>Basic Salary</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($employees as $employee)
                        @php
                            // FIX: Existence of a salary_payments row (salary_payment_id) is the
                            // reliable signal — NOT salary_status, which is null both when no
                            // row exists AND (theoretically) when a row exists with a null status.
                            $recordExists = !is_null($employee->salary_payment_id);
                        @endphp
                        <tr wire:key="emp-{{ $employee->id }}">
                            <td><a class="text-primary" href="{{ route('admin.employee.view', ['id' => $employee->id]) }}" target="_blank">{{ $employee['employee_id'] ?? '—' }}</a></td>
                            <td>{{ $employee->name }}</td>
                            <td>{{ $employee->designation?->name ?? '—' }}</td>
                            <td>{{ $employee->department?->name ?? '—' }}</td>
                            <td>{{ $employee->mobile ?? '—' }}</td>

                            {{-- Salary Grade (from salary_assigns via subquery) --}}
                            <td>{{ $employee->sa_grade ?? '—' }}</td>

                            {{-- Basic Salary: paid amount if a payment row exists, else assign amount --}}
                            <td>
                                @if($recordExists && $employee->salary_basic)
                                    ৳{{ number_format($employee->salary_basic, 2) }}
                                @elseif($employee->sa_basic)
                                    ৳{{ number_format($employee->sa_basic, 2) }}
                                @else
                                    —
                                @endif
                            </td>

                            {{-- Status Badge --}}
                            <td>
                                @if($recordExists)
                                    @if($employee->salary_status === 'paid')
                                        <span class="status-badge status-paid">Salary Paid</span>
                                    @elseif($employee->salary_status === 'partial')
                                        <span class="status-badge status-partial">Partial</span>
                                    @else
                                        <span class="status-badge status-unpaid">Salary Unpaid</span>
                                    @endif
                                @elseif($isSelectedMonthPast)
                                    {{-- Past month, no salary_payments row was ever generated for it --}}
                                    <span class="status-badge status-nodata">No Data Found</span>
                                @else
                                    {{-- Current/future month, payroll hasn't been run yet --}}
                                    <span class="status-badge status-pending">Invoice Not Generated Yet</span>
                                @endif
                            </td>

                            {{-- Actions --}}
                            <td>
                                @if($recordExists && $employee->salary_status === 'paid')
                                    <a class="action-btn btn-payslip" href="{{ route('admin.salary.invoice-payment', ['id' => $employee->id, 'month' => $this->month]) }}" target="_blank">
                                        <span class="material-icons-round" style="font-size:14px;vertical-align:middle">visibility</span>
                                        Payslip
                                    </a>
                                @elseif($recordExists)
                                    <a class="action-btn btn-paynow" href="{{ route('admin.salary.add-payment', ['id' => $employee->id, 'month' => $this->month]) }}" target="_blank">
                                        <span class="material-icons-round" style="font-size:14px;vertical-align:middle">credit_card</span>
                                        Pay Now
                                    </a>
                                @elseif($isSelectedMonthPast)
                                    {{-- Nothing to act on for a past month that was never processed --}}
                                    <span class="text-muted" style="font-size:12px">—</span>
                                @else
                                    <a class="action-btn btn-paynow" href="{{ route('admin.salary.add-payment', ['id' => $employee->id, 'month' => $this->month]) }}" target="_blank">
                                        <span class="material-icons-round" style="font-size:14px;vertical-align:middle">credit_card</span>
                                        Generate &amp; Pay
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination — Livewire's built-in paginator (matches list-template-component pattern),
             replacing the old hand-written previousPage/nextPage/gotoPage buttons.
             This removes duplicated logic Livewire already provides via WithPagination. --}}
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mt-3 px-1">
            <small class="text-muted">
                Showing {{ $employees->firstItem() ?? 0 }}–{{ $employees->lastItem() ?? 0 }} of {{ $employees->total() }} employees
            </small>
            {{ $employees->links('vendor.pagination.custom') }}
        </div>

        @else
        <div class="text-center py-5 text-muted">
            <span class="material-icons-round d-block mb-2" style="font-size:3rem;opacity:.2">inbox</span>
            No employees found for selected role/month.
        </div>
        @endif

        {{-- Footer Reset --}}
        <div class="form-footer">
            <button class="btn-outline" type="button" wire:click="resetForm">
                <span class="material-icons-round" style="font-size:16px">refresh</span> Reset
            </button>
        </div>
    </div>
    @endif

</div>{{-- /mat-card --}}


@push('styles')
<style>
    /* ── Status badges ── */
    .status-badge {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 600;
        border: 1px solid;
        white-space: nowrap;
    }
    .status-paid    { color: #16a34a; border-color: #16a34a; background: rgba(22,163,74,.08); }
    .status-unpaid  { color: #2563eb; border-color: #2563eb; background: rgba(37,99,235,.08); }
    .status-partial { color: #d97706; border-color: #d97706; background: rgba(217,119,6,.08);  }
    .status-nodata  { color: #6b7280; border-color: #6b7280; background: rgba(107,114,128,.08); }
    .status-pending { color: #7c3aed; border-color: #7c3aed; background: rgba(124,58,237,.08);  }

    /* ── Action buttons ── */
    .action-btn {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 5px 12px;
        border-radius: 5px;
        font-size: 12px;
        font-weight: 600;
        border: none;
        cursor: pointer;
        transition: opacity .15s;
        white-space: nowrap;
    }
    .action-btn:hover { opacity: .85; }
    .btn-payslip { background: #1f2937; color: #fff; }
    .btn-paynow  { background: var(--primary, #e74c3c); color: #fff; }

    /* ── Footer ── */
    .form-footer {
        display: flex;
        align-items: center;
        justify-content: flex-start;
        padding-top: 20px;
        margin-top: 16px;
        gap: 12px;
    }

    @keyframes spin { to { transform: rotate(360deg); } }
</style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('livewire:initialized', () => {

            setTimeout(() => initAllFields(), 100);

            Livewire.hook('morph.updated', ({ el }) => {
                setTimeout(() => initAllFields(), 0);
            });

            function initAllFields() {

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

                document.querySelectorAll('.input-group-outline .form-select').forEach(function(select) {
                    var old = select.parentNode.querySelector('.custom-select-wrapper');
                    if (old) old.remove();
                    select.style.display = '';
                    if (typeof buildCustomSelect === 'function') {
                        buildCustomSelect(select);
                    }
                });

                document.querySelectorAll('.input-group-outline input[type="date"]').forEach(function(input) {
                    if (input.dataset.dpInit === '1') return;
                    input.dataset.dpInit = '1';
                    input.addEventListener('change', function() {
                        input.dispatchEvent(new Event('input', { bubbles: true }));
                    });
                    if (typeof buildDatepicker === 'function') {
                        buildDatepicker(input);
                    }
                });
            }

        });
    </script>
@endpush