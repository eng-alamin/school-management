{{-- resources/views/livewire/admin/notice/notice-component.blade.php --}}
<div>

    <div class="card">

        <!-- floating header -->
        <div class="mat-card-header header-pink-gradient">
            <h5 id="cardHeaderTitleAllsections">Notice Board</h5>
            <p id="cardHeaderSubtitle">Manage and publish notices for students, teachers, and admins.</p>
        </div>

        <div class="card-header border-0">
            <!-- toolbar -->
            <div class="card-toolbar">
                {{-- Left side --}}
                <div class="card-toolbar-title">
                    <!-- search in table -->
                    <div style="position:relative;display:inline-flex;align-items:center">
                        <span class="material-icons-round" style="position:absolute;left:10px;font-size:17px;color:var(--muted);pointer-events:none">search</span>
                        <input type="text" wire:model.live.debounce.300ms="search" id="tableSearch" placeholder="Search" style="border:1px solid rgba(0,0,0,.1);border-radius:8px;padding:7px 12px 7px 32px;font-size:.78rem;font-family:inherit;color:var(--dark);outline:none;background:#f8f9fa;width:220px"/>
                    </div>
                </div>

                <!-- Right Side -->
                <div class="col-md-3">
                    <select class="form-select form-select-sm" wire:model.live="filterAudience">
                        <option value="">All Audience</option>
                        <option value="all">Everyone</option>
                        <option value="admin">Admin</option>
                        <option value="teacher">Teacher</option>
                        <option value="student">Student</option>
                    </select>
                </div>
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
                <div class="col-md-2">
                    <select class="form-select form-select-sm" wire:model.live="perPage">
                        <option value="10">10 / page</option>
                        <option value="25">25 / page</option>
                        <option value="50">50 / page</option>
                    </select>
                </div>
                <button class="btn-outline bg-dark text-white" wire:click="openCreate">
                    <span class="material-icons-round">add</span> <span id="newSectionBtn">Add Notice</span>
                </button>
            </div>
        </div>

        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>SL</th>
                            <th>Title</th>
                            <th>Audience</th>
                            <th>Priority</th>
                            <th>Published</th>
                            <th>Expires</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($notices as $i => $notice)
                        <tr>
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
                                    <button class="act-btn edit" title="Edit" wire:click="openEdit({{ $notice->id }})">
                                        <span class="material-icons-round">drive_file_rename_outline</span>
                                    </button>
                                    <button class="act-btn status {{ $notice->status === 'active' ? 'btn-warning' : 'btn-success' }}" title="Toggle Status" wire:click="toggleStatus({{ $notice->id }})">
                                        <span class="material-icons-round">{{ $notice->status === 'active' ? 'toggle_off' : 'toggle_on' }}</span>
                                    </button>
                                    <button class="act-btn delete" title="Delete" wire:click="confirmDeleteRecord({{ $notice->id }})">
                                        <span class="material-icons-round">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox display-5 d-block mb-2 opacity-25"></i>
                                No notices found. <a href="#" wire:click.prevent="openCreate">Create one now</a>.
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

    {{-- ===== CREATE/EDIT MODAL ===== --}}
    @if($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5);" wire:ignore.self>
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="bi bi-megaphone me-2 text-danger"></i>
                            {{ $editId ? 'Edit' : 'Create' }} Notice
                        </h5>
                        <button type="button" class="btn-close" wire:click="$set('showModal',false)"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="save">
                            <div class="row g-3">

                                {{-- Title --}}
                                <div class="col-12">
                                    <label class="form-label">Title <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('title') is-invalid @enderror" wire:model.defer="title" placeholder="e.g. Annual Sports Day Announcement">
                                    @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                {{-- Description --}}
                                <div class="col-12">
                                    <label class="form-label">Description <span class="text-danger">*</span></label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" wire:model.defer="description" rows="5" placeholder="Write notice details..."></textarea>
                                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                {{-- Audience + Priority --}}
                                <div class="col-md-6">
                                    <label class="form-label">Target Audience <span class="text-danger">*</span></label>
                                    <select class="form-select @error('audience') is-invalid @enderror" wire:model.defer="audience">
                                        <option value="all">Everyone</option>
                                        <option value="admin">Admin Only</option>
                                        <option value="teacher">Teacher Only</option>
                                        <option value="student">Student Only</option>
                                    </select>
                                    @error('audience') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Priority <span class="text-danger">*</span></label>
                                    <select class="form-select @error('priority') is-invalid @enderror" wire:model.defer="priority">
                                        <option value="low">Low</option>
                                        <option value="medium">Medium</option>
                                        <option value="high">High</option>
                                        <option value="urgent">Urgent</option>
                                    </select>
                                    @error('priority') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                {{-- Publish Date + Expiry Date --}}
                                <div class="col-md-6">
                                    <label class="form-label">Publish Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('published_at') is-invalid @enderror" wire:model.defer="published_at">
                                    @error('published_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Expiry Date <span class="text-muted" style="font-weight:400;">(optional)</span></label>
                                    <input type="date" class="form-control @error('expires_at') is-invalid @enderror" wire:model.defer="expires_at">
                                    @error('expires_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                {{-- Attachment --}}
                                <div class="col-md-8">
                                    <label class="form-label">Attachment <span class="text-muted" style="font-weight:400;">(PDF, DOC, Image — max 5MB)</span></label>
                                    <input type="file" class="form-control @error('attachment') is-invalid @enderror" wire:model="attachment" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                                    @error('attachment') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    <div wire:loading wire:target="attachment" class="text-muted mt-1" style="font-size:.78rem;">
                                        <span class="material-icons-round" style="font-size:.85rem;vertical-align:middle;">hourglass_empty</span> Uploading...
                                    </div>
                                    @if($existingAttachment)
                                        <div class="d-flex align-items-center gap-2 mt-2 p-2" style="background:#f8f9fa;border-radius:8px;border:1px solid var(--border);">
                                            <span class="material-icons-round text-primary" style="font-size:1rem;">attach_file</span>
                                            <span style="font-size:.78rem;flex:1;">{{ $existingAttachmentName }}</span>
                                            <button type="button" wire:click="removeAttachment" class="btn btn-sm btn-link text-danger p-0">
                                                <span class="material-icons-round" style="font-size:.9rem;">close</span>
                                            </button>
                                        </div>
                                    @endif
                                </div>

                                {{-- Features / toggles --}}
                                <div class="col-12">
                                    <label class="form-label d-block mb-2">Status</label>
                                    <div class="d-flex gap-3 flex-wrap">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" wire:model.defer="status" value="active" id="status_active">
                                            <label class="form-check-label" for="status_active">Active</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" wire:model.defer="status" value="inactive" id="status_inactive">
                                            <label class="form-check-label" for="status_inactive">Inactive</label>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" wire:click="$set('showModal',false)">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="save" wire:loading.attr="disabled">
                            <span wire:loading wire:target="save" class="spinner-border spinner-border-sm me-1"></span>
                            {{ $editId ? 'Update' : 'Publish' }} Notice
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

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
                                <a href="{{ asset('storage/' . $viewRecord->attachment) }}" target="_blank" class="btn btn-sm btn-outline-primary">
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
                                <th class="text-muted">Status</th>
                                <td>
                                    <span class="badge rounded-pill {{ $viewRecord->status === 'active' ? 'badge-active' : 'badge-inactive' }}">
                                        {{ $viewRecord->status === 'active' ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                            </tr>
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
                        <button class="btn btn-primary" wire:click="openEdit({{ $viewRecord->id }}); $set('showViewModal',false)">
                            <i class="bi bi-pencil me-1"></i>Edit
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ===== DELETE CONFIRM ===== --}}
    @if($confirmDelete)
        <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5);">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                    <div class="modal-body text-center py-4">
                        <div style="width:56px;height:56px;border-radius:50%;background:#fee2e2;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                            <i class="bi bi-exclamation-triangle text-danger" style="font-size:1.5rem;"></i>
                        </div>
                        <h6 class="fw-700">Delete Notice?</h6>
                        <p class="text-muted small">This action cannot be undone.</p>
                    </div>
                    <div class="modal-footer justify-content-center border-0 pt-0">
                        <button class="btn btn-light btn-sm" wire:click="$set('confirmDelete',false)">Cancel</button>
                        <button class="btn btn-danger btn-sm" wire:click="deleteRecord">
                            <span wire:loading wire:target="deleteRecord" class="spinner-border spinner-border-sm me-1"></span>
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>


@push('styles')
    <style>
        :root {
            --primary: rgba(33, 37, 41);
            --primary-light: rgba(239,84,84,.12);
        }

        /* ── CARD ── */
        .card { border: 1px solid var(--border); border-radius: 12px; box-shadow: 0 1px 4px rgba(0,0,0,.04); }
        .card-header { background: #fff; border-bottom: 1px solid var(--border); border-radius: 12px 12px 0 0 !important; padding: 16px 20px; }
        .card-header .card-title { font-size: .95rem; font-weight: 600; margin: 0; }

        /* ── TABLE ── */
        .table th { font-size: .75rem; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: var(--text-muted); border-bottom: 2px solid var(--border); }
        .table td { vertical-align: middle; font-size: .875rem; }
        .table > :not(caption) > * > * { padding: .7rem 1rem; }

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

        /* ── MODAL ── */
        .modal-header { border-bottom: 1px solid var(--border); }
        .modal-footer { border-top: 1px solid var(--border); }
        .modal-title  { font-weight: 600; font-size: 1rem; }

        /* ── FORM ── */
        .form-label { font-size: .8rem; font-weight: 600; color: var(--text-muted); margin-bottom: 4px; }
        .form-control, .form-select {
            border-radius: 8px; border: 1px solid var(--border);
            font-size: .875rem; padding: .45rem .75rem;
            transition: border-color .2s, box-shadow .2s;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-light);
        }
        .form-check-input:checked { background-color: var(--primary); border-color: var(--primary); }

        /* Buttons */
        .btn-primary { background: var(--primary); border-color: var(--primary); }
        .btn-primary:hover, .btn-primary:focus { background: #d63e3e; border-color: #d63e3e; }
        .btn-sm { font-size: .78rem; padding: .3rem .65rem; border-radius: 6px; }

        /* Pagination */
        .custom-pagination { display: flex; gap: 8px; align-items: center; }
        .custom-pagination li { list-style: none; }
        .custom-pagination button {
            min-width: 38px; height: 38px; border-radius: 10px;
            border: 1px solid #e0e0e0; background: #f5f5f5;
            color: #444; font-weight: 600; cursor: pointer; transition: all .2s ease;
        }
        .custom-pagination button:hover  { background: #eee; }
        .custom-pagination button.active {
            background: linear-gradient(195deg, #ec407a, #d81b60);
            color: #fff; border: none; box-shadow: 0 4px 12px rgba(216,27,96,.4);
        }
        .custom-pagination button:disabled { opacity: .5; cursor: not-allowed; }
    </style>
@endpush