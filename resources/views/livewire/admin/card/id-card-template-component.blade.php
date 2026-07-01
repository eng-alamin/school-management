{{-- resources/views/livewire/admin/card/id-card-template-component.blade.php --}}
<div>

    <div class="card">

      <!-- floating header -->
      <div class="mat-card-header header-pink-gradient">
        <h5 id="cardHeaderTitleAllsections">ID Card Templates</h5>
        <p id="cardHeaderSubtitle">A lightweight, extendable, dependency-free javascript HTML table plugin.</p>
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
                    <select class="form-select form-select-sm" wire:model.live="filterType">
                        <option value="">All Types</option>
                        @foreach($types as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select form-select-sm" wire:model.live="filterStatus">
                        <option value="">All Status</option>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
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
                    <span class="material-icons-round">add</span> <span id="newSectionBtn">Add Template</span>
                </button>

            </div>
        </div>

        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th id="th-sl">SL</th>
                            <th id="th-template-name">Template Name</th>
                            <th id="th-type">Type</th>
                            <th id="th-colors">Colors</th>
                            <th id="th-size">Size</th>
                            <th id="th-features">Features</th>
                            <th id="th-status">Status</th>
                            <th id="th-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($templates as $i => $t)
                        <tr>
                            <td class="text-muted">{{ $templates->firstItem() + $i }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    @if($t->logo_path)
                                        <img src="{{ asset('storage/'.$t->logo_path) }}" class="avatar" alt="">
                                    @else
                                        <div class="avatar-placeholder">{{ strtoupper(substr($t->name,0,1)) }}</div>
                                    @endif
                                    <div>
                                        <div class="fw-500 text-dark">{{ $t->name }}</div>
                                        @if($t->header_text)
                                            <small class="text-muted">{{ Str::limit($t->header_text, 30) }}</small>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge bg-secondary-subtle text-secondary">{{ ucfirst($t->type) }}</span></td>
                            <td>
                                <div class="d-flex gap-1">
                                    <span title="Background" style="width:18px;height:18px;border-radius:4px;background:{{ $t->background_color }};border:1px solid #e5e7eb;display:inline-block;"></span>
                                    <span title="Text" style="width:18px;height:18px;border-radius:4px;background:{{ $t->text_color }};border:1px solid #e5e7eb;display:inline-block;"></span>
                                    <span title="Accent" style="width:18px;height:18px;border-radius:4px;background:{{ $t->accent_color }};border:1px solid #e5e7eb;display:inline-block;"></span>
                                </div>
                            </td>
                            <td class="text-muted" style="font-size:.78rem;">{{ $t->card_width }} × {{ $t->card_height }}</td>
                            <td>
                                <div class="d-flex gap-1 flex-wrap">
                                    @if($t->show_photo) <span class="badge bg-info-subtle text-info" style="font-size:.65rem;">Photo</span> @endif
                                    @if($t->show_barcode) <span class="badge bg-warning-subtle text-warning" style="font-size:.65rem;">Barcode</span> @endif
                                    @if($t->show_qrcode) <span class="badge bg-success-subtle text-success" style="font-size:.65rem;">QR</span> @endif
                                </div>
                            </td>
                            <td>
                                <span class="badge rounded-pill {{ $t->is_active ? 'badge-active' : 'badge-inactive' }}" style="font-size:.72rem;">
                                    {{ $t->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <button class="act-btn view" title="View" wire:click="openView({{ $t->id }})">
                                        <span class="material-icons-round">visibility</span>
                                    </button>
                                    <button class="act-btn edit" title="Edit" wire:click="openEdit({{ $t->id }})">
                                        <span class="material-icons-round">drive_file_rename_outline</span>
                                    </button>
                                    <button class="act-btn status {{ $t->is_active ? 'btn-warning' : 'btn-success' }}" title="Toggle Status" wire:click="toggleStatus({{ $t->id }})">
                                        <span class="material-icons-round">{{ $t->is_active ? 'toggle_off' : 'toggle_on' }}</span>
                                    </button>
                                    <button class="act-btn delete" title="Delete" wire:click="confirmDeleteRecord({{ $t->id }})">
                                        <span class="material-icons-round">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox display-5 d-block mb-2 opacity-25"></i>
                                No templates found. <a href="#" wire:click.prevent="openCreate">Create one now</a>.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer border-0 bg-white d-flex align-items-center justify-content-between flex-wrap gap-2 py-2 px-3">
            <small class="text-muted">Showing {{ $templates->firstItem() ?? 0 }}–{{ $templates->lastItem() ?? 0 }} of {{ $templates->total() }}</small>
           {{ $templates->links('vendor.pagination.custom') }}
        </div>
        
    </div>

    {{-- ===== CREATE/EDIT MODAL ===== --}}
    @if($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5);" wire:ignore.self>
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="bi bi-layout-text-window me-2 text-danger"></i>
                            {{ $editId ? 'Edit' : 'Create' }} ID Card Template
                        </h5>
                        <button type="button" class="btn-close" wire:click="$set('showModal',false)"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="save">
                            <div class="row g-3">
                                <div class="col-md-8">
                                    <label class="form-label">Template Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" wire:model.defer="name" placeholder="e.g. Modern Blue Template">
                                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Type <span class="text-danger">*</span></label>
                                    <select class="form-select @error('type') is-invalid @enderror" wire:model.defer="type">
                                        @foreach($types as $key => $label)
                                            <option value="{{ $key }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Background Color</label>
                                    <div class="color-input-wrap">
                                        <input type="color" wire:model.defer="background_color">
                                        <input type="text" class="form-control" wire:model.defer="background_color" placeholder="#ffffff">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Text Color</label>
                                    <div class="color-input-wrap">
                                        <input type="color" wire:model.defer="text_color">
                                        <input type="text" class="form-control" wire:model.defer="text_color" placeholder="#000000">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Accent Color</label>
                                    <div class="color-input-wrap">
                                        <input type="color" wire:model.defer="accent_color">
                                        <input type="text" class="form-control" wire:model.defer="accent_color" placeholder="#007bff">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Card Width</label>
                                    <input type="text" class="form-control" wire:model.defer="card_width" placeholder="85.6mm">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Card Height</label>
                                    <input type="text" class="form-control" wire:model.defer="card_height" placeholder="54mm">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Logo</label>
                                    <input type="file" class="form-control @error('logo') is-invalid @enderror" wire:model="logo" accept="image/*">
                                    @error('logo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    @if($existingLogo)
                                        <img src="{{ asset('storage/'.$existingLogo) }}" height="40" class="mt-2 rounded">
                                    @endif
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Header Text</label>
                                    <input type="text" class="form-control" wire:model.defer="header_text" placeholder="Institute name or title shown at top">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Footer Text</label>
                                    <input type="text" class="form-control" wire:model.defer="footer_text" placeholder="Text shown at bottom of card">
                                </div>
                                <div class="col-12">
                                    <label class="form-label d-block mb-2">Features</label>
                                    <div class="d-flex gap-3 flex-wrap">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" wire:model.defer="show_photo" id="show_photo">
                                            <label class="form-check-label" for="show_photo">Show Photo</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" wire:model.defer="show_barcode" id="show_barcode">
                                            <label class="form-check-label" for="show_barcode">Show Barcode</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" wire:model.defer="show_qrcode" id="show_qrcode">
                                            <label class="form-check-label" for="show_qrcode">Show QR Code</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" wire:model.defer="is_active" id="is_active_tmpl">
                                            <label class="form-check-label" for="is_active_tmpl">Active</label>
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
                            {{ $editId ? 'Update' : 'Create' }} Template
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
                        <h5 class="modal-title">Template Details</h5>
                        <button class="btn-close" wire:click="$set('showViewModal',false)"></button>
                    </div>
                    <div class="modal-body">
                        {{-- Card Preview --}}
                        <div class="id-card-preview mb-4" style="background:{{ $viewRecord->background_color }};color:{{ $viewRecord->text_color }};">
                            <div class="card-header-band" style="background:{{ $viewRecord->accent_color }};color:#fff;">
                                @if($viewRecord->logo_path)
                                    <img src="{{ asset('storage/'.$viewRecord->logo_path) }}" height="30" class="mb-1">
                                @else
                                    <i class="bi bi-building" style="font-size:1.5rem;"></i>
                                @endif
                                <div style="font-weight:700;font-size:.85rem;">{{ $viewRecord->header_text ?: 'Institute Name' }}</div>
                            </div>
                            <div class="card-body-area">
                                @if($viewRecord->show_photo)
                                <div style="width:70px;height:85px;border-radius:8px;background:rgba(0,0,0,.1);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                    <i class="bi bi-person" style="font-size:2rem;opacity:.3;"></i>
                                </div>
                                @endif
                                <div style="flex:1;">
                                    <div style="font-weight:700;font-size:.9rem;">Student / Employee Name</div>
                                    <div style="font-size:.72rem;opacity:.7;margin-top:2px;">ID: XXX-2024-0001</div>
                                    <div style="font-size:.72rem;opacity:.7;">Class/Designation</div>
                                    <div style="font-size:.72rem;opacity:.7;">Contact Number</div>
                                    <div style="margin-top:8px;padding-top:8px;border-top:1px solid rgba(0,0,0,.1);font-size:.65rem;opacity:.6;">
                                        Valid Until: 31 Dec 2025
                                    </div>
                                </div>
                            </div>
                            <div style="padding:8px 16px;border-top:1px solid rgba(0,0,0,.1);text-align:center;font-size:.65rem;opacity:.7;">
                                {{ $viewRecord->footer_text ?: 'Footer text here' }}
                            </div>
                        </div>

                        <table class="table table-sm">
                            <tr><th class="text-muted" style="width:40%">Name</th><td>{{ $viewRecord->name }}</td></tr>
                            <tr><th class="text-muted">Type</th><td>{{ ucfirst($viewRecord->type) }}</td></tr>
                            <tr><th class="text-muted">Size</th><td>{{ $viewRecord->card_width }} × {{ $viewRecord->card_height }}</td></tr>
                            <tr><th class="text-muted">Status</th><td><span class="badge {{ $viewRecord->is_active ? 'badge-active' : 'badge-inactive' }}">{{ $viewRecord->is_active ? 'Active' : 'Inactive' }}</span></td></tr>
                            <tr><th class="text-muted">Created</th><td>{{ $viewRecord->created_at->format('d M Y') }}</td></tr>
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
                        <h6 class="fw-700">Delete Template?</h6>
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
        .badge-active { background: rgba(34,197,94,.12); color: #16a34a; }
        .badge-inactive { background: rgba(107,114,128,.12); color: #6b7280; }
        .badge-expired, .badge-cancelled, .badge-suspended { background: rgba(239,68,68,.12); color: #dc2626; }
        .badge-used { background: rgba(59,130,246,.12); color: #2563eb; }
 
        /* ── AVATAR ── */
        .avatar { width: 38px; height: 38px; border-radius: 8px; object-fit: cover; }
        .avatar-placeholder {
            width: 38px; height: 38px; border-radius: 8px;
            background: var(--primary-light); color: var(--primary);
            display: inline-flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: .875rem;
        }
 
        /* ── MODAL ── */
        .modal-header { border-bottom: 1px solid var(--border); }
        .modal-footer { border-top: 1px solid var(--border); }
        .modal-title { font-weight: 600; font-size: 1rem; }
 
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
 
        /* Color picker */
        .color-input-wrap { display: flex; align-items: center; gap: 8px; }
        .color-input-wrap input[type="color"] {
            width: 40px; height: 38px; padding: 2px; border-radius: 8px;
            cursor: pointer; border: 1px solid var(--border);
        }
 
        /* Buttons */
        .btn-primary { background: var(--primary); border-color: var(--primary); }
        .btn-primary:hover, .btn-primary:focus { background: #d63e3e; border-color: #d63e3e; }
        .btn-sm { font-size: .78rem; padding: .3rem .65rem; border-radius: 6px; }
        .btn-icon { width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border-radius: 7px; }
 
        /* Stat cards */
        .stat-card { border-radius: 12px; padding: 20px; display: flex; align-items: center; gap: 16px; }
        .stat-icon { width: 48px; height: 48px; border-radius: 10px; display: grid; place-items: center; font-size: 1.4rem; }
        .stat-label { font-size: .75rem; color: var(--text-muted); font-weight: 500; }
        .stat-value { font-size: 1.5rem; font-weight: 700; line-height: 1; }
 
        /* ID Card Preview */
        .id-card-preview {
            width: 325px; min-height: 200px; border-radius: 14px; overflow: hidden;
            box-shadow: 0 8px 32px rgba(0,0,0,.15); margin: 0 auto;
            position: relative; font-family: 'Inter', sans-serif;
        }
        .id-card-preview .card-header-band { padding: 16px; text-align: center; }
        .id-card-preview .card-body-area { padding: 14px 16px; display: flex; gap: 14px; }
        .id-card-preview .card-photo {
            width: 80px; height: 95px; border-radius: 8px;
            object-fit: cover; border: 3px solid rgba(255,255,255,.5);
        }
 
        /* Print */
        @media print {
            .sidebar, .topbar, .no-print { display: none !important; }
            .main-content { margin: 0; padding: 0; }
            .print-area { display: block !important; }
        }
 
        .alert { border-radius: 10px; font-size: .875rem; }
 
        /* Subject rows */
        .subject-row { background: var(--bg); border-radius: 8px; padding: 10px 12px; margin-bottom: 8px; }
 

        /* Pagination */
        .custom-pagination {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .custom-pagination li {
            list-style: none;
        }

        .custom-pagination button {
            min-width: 38px;
            height: 38px;
            border-radius: 10px;
            border: 1px solid #e0e0e0;
            background: #f5f5f5;
            color: #444;
            font-weight: 600;
            cursor: pointer;
            transition: all .2s ease;
        }

        /* Hover */
        .custom-pagination button:hover {
            background: #eee;
        }

        /* Active (Pink) */
        .custom-pagination button.active {
            background: linear-gradient(195deg, #ec407a, #d81b60);
            color: #fff;
            border: none;
            box-shadow: 0 4px 12px rgba(216,27,96,.4);
        }

        /* Disabled */
        .custom-pagination button:disabled {
            opacity: .5;
            cursor: not-allowed;
        }
    </style>
@endpush