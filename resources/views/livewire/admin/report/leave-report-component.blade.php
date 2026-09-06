{{-- resources/views/livewire/admin/report/leave-report-component.blade.php --}}
<div>

    <div class="card">

        <!-- floating header -->
        <div class="mat-card-header header-primary-gradient">
            <h5 id="cardHeaderTitleAllsections">Leave Report</h5>
            <p id="cardHeaderSubtitle">সকল role-এর leave application একসাথে filter করে দেখুন।</p>
        </div>

        <div class="card-body pt-3">

            {{-- Summary cards --}}
            <div class="row g-2 mb-3">
                <div class="col-6 col-md-3">
                    <div class="summary-card summary-late">
                        <span class="material-icons-round">schedule</span>
                        <div class="count">{{ $summary['pending'] ?? 0 }}</div>
                        <div class="label">Pending</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="summary-card summary-present">
                        <span class="material-icons-round">check_circle</span>
                        <div class="count">{{ $summary['approved'] ?? 0 }}</div>
                        <div class="label">Approved</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="summary-card summary-absent">
                        <span class="material-icons-round">cancel</span>
                        <div class="count">{{ $summary['rejected'] ?? 0 }}</div>
                        <div class="label">Rejected</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="summary-card summary-leave">
                        <span class="material-icons-round">event_busy</span>
                        <div class="count">{{ $summary['cancelled'] ?? 0 }}</div>
                        <div class="label">Cancelled</div>
                    </div>
                </div>
            </div>

            {{-- Toolbar / Filters --}}
            <div class="card-toolbar flex-wrap mb-2">
                <div class="col-md-2">
                    <div class="input-group input-group-outline" wire:ignore>
                        <label class="form-label">From</label>
                        <input type="date" class="form-control form-control-sm" wire:model.live="dateFrom" data-dp-value="{{ $dateFrom }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="input-group input-group-outline" wire:ignore>
                        <label class="form-label">To</label>
                        <input type="date" class="form-control form-control-sm" wire:model.live="dateTo" data-dp-value="{{ $dateTo }}">
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="input-group input-group-outline" wire:ignore>
                        <label class="form-label">Role</label>
                        <select class="form-select form-select-sm" wire:model.live="role">
                            <option value="all">All Roles</option>
                            <option value="teacher">Teacher</option>
                            <option value="accountant">Accountant</option>
                            <option value="staff">Staff</option>
                            <option value="student">Student</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="input-group input-group-outline" wire:ignore>
                        <label class="form-label">Category</label>
                        <select class="form-select form-select-sm" wire:model.live="leaveCategoryId">
                            <option value="all">All Categories</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="input-group input-group-outline" wire:ignore>
                        <label class="form-label">Status</label>
                        <select class="form-select form-select-sm" wire:model.live="status">
                            <option value="all">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Search</label>
                    <div style="position:relative;display:inline-flex;align-items:center;width:100%">
                        <span class="material-icons-round" style="position:absolute;left:10px;font-size:16px;color:var(--muted);pointer-events:none">search</span>
                        <input type="text" wire:model.live.debounce.400ms="search" placeholder="নাম / ID..." class="form-control form-control-sm" style="padding-left:32px;">
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
                            <th id="th-role">Role</th>
                            <th id="th-applicant">Applicant</th>
                            <th id="th-category">Leave Category</th>
                            <th id="th-startdate" role="button" wire:click="sortBy('start_date')">
                                Start Date
                                @if($sortField === 'start_date')
                                    <span class="material-icons-round" style="font-size:.9rem;vertical-align:middle;">{{ $sortDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>
                                @endif
                            </th>
                            <th id="th-enddate" role="button" wire:click="sortBy('end_date')">
                                End Date
                                @if($sortField === 'end_date')
                                    <span class="material-icons-round" style="font-size:.9rem;vertical-align:middle;">{{ $sortDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>
                                @endif
                            </th>
                            <th id="th-days" role="button" wire:click="sortBy('total_days')">
                                Days
                                @if($sortField === 'total_days')
                                    <span class="material-icons-round" style="font-size:.9rem;vertical-align:middle;">{{ $sortDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>
                                @endif
                            </th>
                            <th id="th-status" role="button" wire:click="sortBy('status')">
                                Status
                                @if($sortField === 'status')
                                    <span class="material-icons-round" style="font-size:.9rem;vertical-align:middle;">{{ $sortDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>
                                @endif
                            </th>
                            <th id="th-reviewedby">Reviewed By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($records as $i => $record)
                        <tr wire:key="leave-report-{{ $record->id }}">
                            <td class="text-muted">{{ $records->firstItem() + $i }}</td>

                            <td class="text-muted" style="font-size:.78rem;">
                                {{ ucfirst(optional($record->applicable)->role ?? class_basename($record->applicable_type)) }}
                            </td>

                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar-placeholder att-avatar-{{ $record->status }}">
                                        <span class="material-icons-round" style="font-size:1rem;">badge</span>
                                    </div>
                                    <div>
                                        <div class="fw-500 text-dark">{{ $record->applicable?->name ?? '— (deleted)' }}</div>
                                        <small class="text-muted">{{ $record->applicable?->employee_id ?? '—' }}</small>
                                    </div>
                                </div>
                            </td>

                            <td class="text-muted" style="font-size:.78rem;">
                                {{ $record->leaveCategory?->name ?? '—' }}
                            </td>

                            <td class="text-muted" style="font-size:.78rem;">
                                {{ $record->start_date->format('d M Y') }}
                            </td>
                            <td class="text-muted" style="font-size:.78rem;">
                                {{ $record->end_date->format('d M Y') }}
                            </td>
                            <td class="text-muted" style="font-size:.78rem;">
                                {{ $record->total_days }}
                            </td>

                            <td>
                                @php
                                    $statusMap = [
                                        'approved'  => 'bg-success-subtle text-success',
                                        'pending'   => 'bg-warning-subtle text-warning',
                                        'rejected'  => 'bg-danger-subtle text-danger',
                                        'cancelled' => 'bg-secondary-subtle text-secondary',
                                    ];
                                    $sc = $statusMap[$record->status] ?? 'bg-light text-dark';
                                @endphp
                                <span class="badge rounded-pill {{ $sc }}" style="font-size:.72rem;">{{ ucfirst($record->status) }}</span>
                            </td>

                            <td class="text-muted" style="font-size:.75rem;">
                                {{ $record->approvedByUser?->name ?? '—' }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <span class="material-icons-round d-block mb-2" style="font-size:2.5rem;opacity:.25;">fact_check</span>
                                কোনো leave record পাওয়া যায়নি।
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