{{-- resources/views/livewire/student/notice/notice-component.blade.php --}}
<div>

    <div class="card">

        <!-- floating header -->
        <div class="mat-card-header header-primary-gradient">
            <h5 id="cardHeaderTitleAllsections">Notice Board</h5>
            <p id="cardHeaderSubtitle">View the latest notices and announcements.</p>
        </div>

        <div class="card-header border-0">
            <!-- toolbar -->
            <div class="card-toolbar">
                {{-- Left side --}}
                <div class="card-toolbar-title">
                    <!-- search in table -->
                    <div style="position:relative;display:inline-flex;align-items:center">
                        <span class="material-icons-round" style="position:absolute;left:10px;font-size:17px;color:var(--muted);pointer-events:none">search</span>
                        <input type="text" wire:model.live.debounce.300ms="search" id="tableSearch" placeholder="Search" class="tb-search"/>
                    </div>
                </div>

                <!-- Right Side -->
                <div class="col-md-2">
                    <select class="form-select form-select-sm" wire:model.live="filterPriority">
                        <option value="">All Priority</option>
                        <option value="urgent">Urgent</option>
                        <option value="high">High</option>
                        <option value="medium">Medium</option>
                        <option value="low">Low</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select form-select-sm" wire:model.live="filterStatus">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                @if($notices->total() > 10)
                    <div class="col-md-2">
                        <select class="form-select form-select-sm" wire:model.live="perPage">
                            <option value="10">10 / page</option>
                            <option value="25">25 / page</option>
                            <option value="50">50 / page</option>
                        </select>
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
                            <th id="th-tittle">Title</th>
                            <th id="th-audience">Audience</th>
                            <th id="th-priority">Priority</th>
                            <th id="th-published">Published</th>
                            <th id="th-expires">Expires</th>
                            <th id="th-status">Status</th>
                            <th id="th-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($notices as $i => $notice)
                        <tr wire:key="notice-{{ $notice->id }}">
                            <td class="text-muted">{{ $notices->firstItem() + $i }}</td>

                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar-placeholder notice-avatar-{{ $notice->priority }}">
                                        <span class="material-icons-round" style="font-size:1rem;">campaign</span>
                                    </div>
                                    <div>
                                        <div class="fw-500 text-dark">{{ $notice->title }}</div>
                                        <small class="text-muted">{{ Str::limit($notice->description, 40) }}</small>
                                        @if($notice->attachment)
                                            <span class="material-icons-round text-muted ms-1" style="font-size:.85rem;vertical-align:middle;" title="Has attachment">attach_file</span>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <td>
                                @php
                                    $audienceMap = [
                                        'all'     => ['label' => 'Everyone', 'color' => 'bg-primary-subtle text-primary'],
                                        'admin'   => ['label' => 'Admin',    'color' => 'bg-danger-subtle text-danger'],
                                        'teacher' => ['label' => 'Teacher',  'color' => 'bg-warning-subtle text-warning'],
                                        'student' => ['label' => 'Student',  'color' => 'bg-success-subtle text-success'],
                                    ];
                                    $ac = $audienceMap[$notice->audience] ?? $audienceMap['all'];
                                @endphp
                                <span class="badge {{ $ac['color'] }}">{{ $ac['label'] }}</span>
                            </td>

                            <td>
                                @php
                                    $priorityMap = [
                                        'urgent' => ['label' => 'Urgent', 'color' => 'bg-danger-subtle text-danger'],
                                        'high'   => ['label' => 'High',   'color' => 'bg-warning-subtle text-warning'],
                                        'medium' => ['label' => 'Medium', 'color' => 'bg-info-subtle text-info'],
                                        'low'    => ['label' => 'Low',    'color' => 'bg-secondary-subtle text-secondary'],
                                    ];
                                    $pc = $priorityMap[$notice->priority] ?? $priorityMap['medium'];
                                @endphp
                                <span class="badge {{ $pc['color'] }}">{{ $pc['label'] }}</span>
                            </td>

                            <td class="text-muted" style="font-size:.78rem;">
                                {{ $notice->published_at->format('d M Y') }}
                            </td>

                            <td class="text-muted" style="font-size:.78rem;">
                                @if($notice->expires_at)
                                    <span class="{{ $notice->is_expired ? 'text-danger' : '' }}">
                                        {{ $notice->expires_at->format('d M Y') }}
                                        @if($notice->is_expired)
                                            <br><span class="badge bg-danger-subtle text-danger" style="font-size:.65rem;">Expired</span>
                                        @endif
                                    </span>
                                @else
                                    —
                                @endif
                            </td>

                            <td>
                                <span class="badge rounded-pill {{ $notice->status === 'active' ? 'badge-active' : 'badge-inactive' }}" style="font-size:.72rem;">
                                    {{ $notice->status === 'active' ? 'Active' : 'Inactive' }}
                                </span>
                            </td>

                            <td>
                                <div class="d-flex gap-1">
                                    <button class="act-btn view" title="View" wire:click="openView({{ $notice->id }})">
                                        <span class="material-icons-round">visibility</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox display-5 d-block mb-2 opacity-25"></i>
                                No notices found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer border-0 bg-white d-flex align-items-center justify-content-between flex-wrap gap-2 py-2 px-3">
            <small class="text-muted">Showing {{ $notices->firstItem() ?? 0 }}–{{ $notices->lastItem() ?? 0 }} of {{ $notices->total() }}</small>
            {{ $notices->links('vendor.pagination.custom') }}
        </div>

    </div>

    {{-- ===== VIEW MODAL ===== --}}
    @if($showViewModal && $viewRecord)
        <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Notice Details</h5>
                        <button class="btn-close" wire:click="$set('showViewModal',false)"></button>
                    </div>
                    <div class="modal-body">

                        {{-- Priority banner --}}
                        @php
                            $priorityColors = [
                                'urgent' => '#dc2626',
                                'high'   => '#d97706',
                                'medium' => '#2563eb',
                                'low'    => '#6b7280',
                            ];
                            $bannerColor = $priorityColors[$viewRecord->priority] ?? '#2563eb';
                        @endphp
                        <div style="border-left:4px solid {{ $bannerColor }};padding:12px 16px;background:#f8f9fa;border-radius:0 8px 8px 0;margin-bottom:16px;">
                            <div style="font-size:.7rem;font-weight:600;color:{{ $bannerColor }};text-transform:uppercase;letter-spacing:.05em;">
                                {{ ucfirst($viewRecord->priority) }} Priority
                            </div>
                            <div style="font-weight:700;font-size:.95rem;margin-top:4px;">{{ $viewRecord->title }}</div>
                        </div>

                        {{-- Description --}}
                        <div style="font-size:.875rem;line-height:1.6;color:#374151;margin-bottom:16px;">
                            {!! nl2br(e($viewRecord->description)) !!}
                        </div>

                        {{-- Attachment --}}
                        @if($viewRecord->attachment)
                            <div class="d-flex align-items-center gap-2 p-2 mb-3" style="background:#f0f7ff;border-radius:8px;border:1px solid #bfdbfe;">
                                <span class="material-icons-round text-primary" style="font-size:1.1rem;">attach_file</span>
                                <span style="font-size:.8rem;flex:1;">{{ $viewRecord->attachment_name }}</span>
                                <a href="{{ Storage::url($viewRecord->attachment) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                    <span class="material-icons-round" style="font-size:.85rem;vertical-align:middle;">download</span> Download
                                </a>
                            </div>
                        @endif

                        <table class="table table-sm">
                            <tr>
                                <th class="text-muted" style="width:40%">Audience</th>
                                <td>{{ ucfirst($viewRecord->audience === 'all' ? 'Everyone' : $viewRecord->audience) }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Publish Date</th>
                                <td>{{ $viewRecord->published_at->format('d M Y') }}</td>
                            </tr>
                            @if($viewRecord->expires_at)
                            <tr>
                                <th class="text-muted">Expiry Date</th>
                                <td class="{{ $viewRecord->is_expired ? 'text-danger' : '' }}">
                                    {{ $viewRecord->expires_at->format('d M Y') }}
                                    @if($viewRecord->is_expired)
                                        <span class="badge bg-danger-subtle text-danger ms-1" style="font-size:.65rem;">Expired</span>
                                    @endif
                                </td>
                            </tr>
                            @endif
                            <tr>
                                <th class="text-muted">Created By</th>
                                <td>{{ $viewRecord->creator->name ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Created</th>
                                <td>{{ $viewRecord->created_at->format('d M Y') }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-light" wire:click="$set('showViewModal',false)">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>


@push('styles')
    <style>
        /* ── BADGES ── */
        .badge-active   { background: rgba(34,197,94,.12);  color: #16a34a; }
        .badge-inactive { background: rgba(107,114,128,.12); color: #6b7280; }

        /* ── AVATAR ── */
        .avatar-placeholder {
            width: 38px; height: 38px; border-radius: 8px;
            background: var(--primary-light); color: var(--primary);
            display: inline-flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: .875rem;
        }
        .notice-avatar-urgent { background: rgba(239,68,68,.12);  color: #dc2626; }
        .notice-avatar-high   { background: rgba(245,158,11,.12); color: #d97706; }
        .notice-avatar-medium { background: rgba(59,130,246,.12); color: #2563eb; }
        .notice-avatar-low    { background: rgba(107,114,128,.12);color: #6b7280; }

        /* Buttons */
        .btn-sm { font-size: .78rem; padding: .3rem .65rem; border-radius: 6px; }
    </style>
@endpush