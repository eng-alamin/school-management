<div>

    <div class="card">

        <!-- floating header -->
        <div class="mat-card-header header-pink-gradient">
            <h5>Database Backup</h5>
            <p>Manage database backups, create, restore, and organize backups easily.</p>
        </div>

        <div class="card-header border-0">
            <div class="card-toolbar">
                {{-- Left side --}}
                <div class="card-toolbar-title">
                    <ul class="nav nav-tabs border-0 gap-1">
                        <li class="nav-item">
                            <button class="nav-link d-flex align-items-center gap-1 {{ $activeTab === 'list' ? 'active' : '' }}"
                                wire:click="$set('activeTab','list')">
                                <span class="material-icons-round" style="font-size:16px">storage</span>
                                Database List
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link d-flex align-items-center gap-1 {{ $activeTab === 'restore' ? 'active' : '' }}"
                                wire:click="$set('activeTab','restore')">
                                <span class="material-icons-round" style="font-size:16px">upload</span>
                                Restore Database
                            </button>
                        </li>
                    </ul>
                </div>

                {{-- Right side --}}
                @if($activeTab === 'list')
                    @if($totalBackups > 10)
                        <div class="col-md-2">
                            <select class="form-select form-select-sm" wire:model.live="perPage">
                                <option value="10">10 / page</option>
                                <option value="25">25 / page</option>
                                <option value="50">50 / page</option>
                                <option value="100">100 / page</option>
                            </select>
                        </div>
                    @endif
                    <button class="btn-outline bg-dark text-white"
                        wire:click="createBackup"
                        wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="createBackup" class="d-flex align-items-center">
                            <span class="material-icons-round me-2">backup</span> Create Backup
                        </span>
                        <span wire:loading wire:target="createBackup">
                            <span class="spinner-border spinner-border-sm me-1"></span> Creating...
                        </span>
                    </button>
                @endif

            </div>
        </div>

        {{-- ===== DATABASE LIST TAB ===== --}}
        @if($activeTab === 'list')
            <div class="card-body pt-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>SL</th>
                                <th>Backup</th>
                                <th>Backup Size</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($backups as $i => $backup)
                            <tr>
                                <td class="text-muted">{{ $from + $i }}</td>
                                <td>
                                    <span class="material-icons-round text-muted me-1" style="font-size:15px;vertical-align:middle">folder_zip</span>
                                    {{ $backup['filename'] }}
                                </td>
                                <td><span class="badge bg-secondary">{{ $backup['size'] }}</span></td>
                                <td>{{ $backup['date'] }}</td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <button class="act-btn edit" title="Download"
                                            wire:click="downloadBackup('{{ $backup['filename'] }}')">
                                            <span class="material-icons-round">download</span>
                                        </button>
                                        <button class="act-btn delete" title="Delete"
                                            wire:click="confirmDeleteBackup('{{ $backup['filename'] }}')">
                                            <span class="material-icons-round">delete</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox display-5 d-block mb-2 opacity-25"></i>
                                    No backups found. <a href="#" wire:click.prevent="createBackup">Create one now</a>.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card-footer border-0 bg-white d-flex align-items-center justify-content-between flex-wrap gap-2 py-2 px-3">
                <small class="text-muted">Showing {{ $from }}–{{ $to }} of {{ $totalBackups }} entries</small>
                <div class="d-flex gap-1">
                    <button class="btn btn-sm btn-outline-secondary"
                        wire:click="previousPage"
                        @if($currentPage <= 1) disabled @endif>
                        <span class="material-icons-round" style="font-size:16px">chevron_left</span>
                    </button>
                    <button class="btn btn-sm btn-outline-secondary"
                        wire:click="nextPage"
                        @if($currentPage >= $totalPages) disabled @endif>
                        <span class="material-icons-round" style="font-size:16px">chevron_right</span>
                    </button>
                </div>
            </div>
        @endif

        {{-- ===== RESTORE TAB ===== --}}
        @if($activeTab === 'restore')
            <div class="card-body">
                <div class="row justify-content-center">
                    <div class="col-md-6">
                        <div class="border rounded-3 p-4 text-center" style="border-style:dashed!important">
                            <span class="material-icons-round text-muted d-block mb-2" style="font-size:48px">upload_file</span>
                            <h6 class="fw-600 mb-1">Upload Backup File</h6>
                            <p class="text-muted small mb-3">Supported formats: .sql, .gz, .zip</p>
                            <input type="file" wire:model="restoreFile" accept=".sql,.gz,.zip" class="form-control mb-3">
                            @error('restoreFile') <span class="text-danger small">{{ $message }}</span> @enderror
                            <button class="btn bg-dark text-white btn-sm d-flex align-items-center gap-1 mx-auto"
                                wire:click="restoreBackup"
                                wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="restoreBackup">
                                    <span class="material-icons-round" style="font-size:15px">restore</span>
                                    Restore Database
                                </span>
                                <span wire:loading wire:target="restoreBackup">
                                    <span class="spinner-border spinner-border-sm me-1"></span>
                                    Restoring...
                                </span>
                            </button>
                        </div>
                        <div class="alert alert-warning mt-3 small">
                            <span class="material-icons-round me-1" style="font-size:15px;vertical-align:middle">warning</span>
                            Restoring a backup will overwrite your current database. This action cannot be undone.
                        </div>
                    </div>
                </div>
            </div>
        @endif

    </div>

    {{-- ===== DELETE CONFIRM MODAL ===== --}}
    @if($confirmDelete)
        <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5);">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                    <div class="modal-body text-center py-4">
                        <div style="width:56px;height:56px;border-radius:50%;background:#fee2e2;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                            <i class="bi bi-exclamation-triangle text-danger" style="font-size:1.5rem;"></i>
                        </div>
                        <h6 class="fw-700">Delete Backup?</h6>
                        <p class="text-muted small mb-0">{{ $deleteTarget }}</p>
                        <p class="text-muted small">This action cannot be undone.</p>
                    </div>
                    <div class="modal-footer justify-content-center border-0 pt-0">
                        <button class="btn btn-light btn-sm" wire:click="$set('confirmDelete',false)">Cancel</button>
                        <button class="btn btn-danger btn-sm" wire:click="deleteBackup">
                            <span wire:loading wire:target="deleteBackup" class="spinner-border spinner-border-sm me-1"></span>
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

        .card { border: 1px solid var(--border); border-radius: 12px; box-shadow: 0 1px 4px rgba(0,0,0,.04); }
        .card-header { background: #fff; border-bottom: 1px solid var(--border); border-radius: 12px 12px 0 0 !important; padding: 16px 20px; }
        .card-header .card-title { font-size: .95rem; font-weight: 600; margin: 0; }

        .table th { font-size: .75rem; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: var(--text-muted); border-bottom: 2px solid var(--border); }
        .table td { vertical-align: middle; font-size: .875rem; }
        .table > :not(caption) > * > * { padding: .7rem 1rem; }

        .form-label { font-size: .8rem; font-weight: 600; color: var(--text-muted); margin-bottom: 4px; }
        .form-control, .form-select { border-radius: 8px; border: 1px solid var(--border); font-size: .875rem; padding: .45rem .75rem; transition: border-color .2s, box-shadow .2s; }
        .form-control:focus, .form-select:focus { border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-light); }

        .btn-sm { font-size: .78rem; padding: .3rem .65rem; border-radius: 6px; }

        .nav-tabs { border-bottom: none; }
        .nav-tabs .nav-link {
            font-size: .82rem;
            font-weight: 600;
            color: var(--muted);
            border: none;
            border-bottom: 2px solid transparent;
            border-radius: 0;
            padding: 6px 14px;
        }
        .nav-tabs .nav-link.active {
            color: #e74c3c;
            border-bottom-color: #e74c3c;
            background: transparent;
        }
        .nav-tabs .nav-link:hover:not(.active) {
            color: var(--dark);
            border-bottom-color: #dee2e6;
        }
    </style>
@endpush