{{-- resources/views/livewire/super-admin/log/system-log-component.blade.php --}}
<div>

    <div class="card">

        {{-- floating header --}}
        <div class="mat-card-header header-primary-gradient">
            <h5 id="cardHeaderTitleAllsections">System Error Log</h5>
            <p id="cardHeaderSubtitle">Track application errors and exceptions across all institutions.</p>
        </div>

        <div class="card-header border-0">
            {{-- toolbar --}}
            <div class="card-toolbar">

                {{-- Left side: Search --}}
                <div class="card-toolbar-title">
                    <div style="position:relative;display:inline-flex;align-items:center">
                        <span class="material-icons-round" style="position:absolute;left:10px;font-size:17px;color:var(--muted);pointer-events:none">search</span>
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="search"
                            placeholder="Search exception, message, component..."
                            class="tb-search"
                        />
                    </div>
                </div>

                {{-- Institution Filter --}}
                <div class="col-md-2">
                    <div class="input-group input-group-outline">
                        <select class="form-select form-select-sm" wire:model.live="filterInstitutionId">
                            <option value="">All Institutions</option>
                            @foreach($institutions as $inst)
                                <option value="{{ $inst->id }}">{{ $inst->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Panel Filter --}}
                <div class="col-md-2">
                    <div class="input-group input-group-outline">
                        <select class="form-select form-select-sm" wire:model.live="filterPanel">
                            <option value="">All Panels</option>
                            <option value="admin">Admin</option>
                            <option value="ministry">Ministry</option>
                            <option value="branch">Branch</option>
                            <option value="teacher">Teacher</option>
                            <option value="student">Student</option>
                            <option value="accountant">Accountant</option>
                            <option value="parent">Parent</option>
                            <option value="global">Global</option>
                        </select>
                    </div>
                </div>

                {{-- Status Filter --}}
                <div class="col-md-2">
                    <div class="input-group input-group-outline">
                        <select class="form-select form-select-sm" wire:model.live="filterStatus">
                            <option value="">All Status</option>
                            <option value="new">New</option>
                            <option value="reviewed">Reviewed</option>
                            <option value="resolved">Resolved</option>
                        </select>
                    </div>
                </div>

                @if($logs->total() > 10)
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
                            <th>SL</th>
                            <th>Exception</th>
                            <th>Status</th>
                            <th>Institution / Branch</th>
                            <th>User</th>
                            <th>Panel / Component</th>
                            <th>Time</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $i => $log)
                            @php
                                $statusColorMap = [
                                    'new'      => ['bg' => 'rgba(220,38,38,.12)',  'color' => '#dc2626'],
                                    'reviewed' => ['bg' => 'rgba(217,119,6,.12)',  'color' => '#d97706'],
                                    'resolved' => ['bg' => 'rgba(5,150,105,.12)',  'color' => '#059669'],
                                ];

                                $sc = $statusColorMap[$log->status] ?? $statusColorMap['new'];
                            @endphp
                            <tr>
                                {{-- SL --}}
                                <td class="text-muted">{{ $logs->firstItem() + $i }}</td>

                                {{-- Exception --}}
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar-placeholder" style="background:rgba(220,38,38,.12);color:#dc2626;">
                                            <span class="material-icons-round" style="font-size:1rem;">bug_report</span>
                                        </div>
                                        <div>
                                            <div class="fw-500 text-dark" style="font-size:.85rem;">{{ class_basename($log->exception_class) }}</div>
                                            <small class="text-muted">{{ \Illuminate\Support\Str::limit($log->message, 60) }}</small>
                                        </div>
                                    </div>
                                </td>

                                {{-- Status Badge --}}
                                <td>
                                    <span class="badge" style="background:{{ $sc['bg'] }};color:{{ $sc['color'] }};font-size:.72rem;">
                                        {{ ucfirst($log->status) }}
                                    </span>
                                </td>

                                {{-- Institution / Branch --}}
                                <td style="font-size:.82rem;">
                                    {{ $log->institution?->name ?? '—' }}
                                    @if($log->branch)
                                        <br><small class="text-muted">{{ $log->branch->name }}</small>
                                    @endif
                                </td>

                                {{-- User --}}
                                <td>
                                    @if($log->user)
                                        <div class="fw-500" style="font-size:.82rem;">{{ $log->user->name }}</div>
                                        <small class="text-muted">{{ $log->user_role }}</small>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>

                                {{-- Panel / Component --}}
                                <td class="text-muted" style="font-size:.78rem;">
                                    {{ $log->panel ?? '—' }}
                                    @if($log->component)
                                        <br><span class="badge bg-secondary-subtle text-secondary">{{ class_basename($log->component) }}</span>
                                    @endif
                                </td>

                                {{-- Time --}}
                                <td class="text-muted" style="font-size:.78rem;white-space:nowrap;">
                                    {{ $log->created_at->format('d M Y, h:i A') }}
                                    <br>{{ $log->created_at->diffForHumans() }}
                                </td>

                                {{-- Action --}}
                                <td class="text-end" style="white-space:nowrap;">
                                    <button type="button" class="btn btn-sm btn-outline-primary" wire:click="view({{ $log->id }})" title="View">
                                        <span class="material-icons-round" style="font-size:16px;">visibility</span>
                                    </button>
                                    @if($log->status !== 'resolved')
                                        <button type="button" class="btn btn-sm btn-outline-success" wire:click="markResolved({{ $log->id }})" title="Mark Resolved">
                                            <span class="material-icons-round" style="font-size:16px;">check_circle</span>
                                        </button>
                                    @endif
                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                            wire:click="delete({{ $log->id }})"
                                            wire:confirm="আপনি কি নিশ্চিত এই লগটি ডিলিট করতে চান?"
                                            title="Delete">
                                        <span class="material-icons-round" style="font-size:16px;">delete</span>
                                    </button>
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <span class="material-icons-round d-block mb-2 opacity-25" style="font-size:3rem;">check_circle</span>
                                    No error logs found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer border-0 bg-white d-flex align-items-center justify-content-between flex-wrap gap-2 py-2 px-3">
            <small class="text-muted">
                Showing {{ $logs->firstItem() ?? 0 }}–{{ $logs->lastItem() ?? 0 }} of {{ $logs->total() }}
            </small>
            {{ $logs->links('vendor.pagination.custom') }}
        </div>

    </div>

    {{-- View Modal --}}
    <div wire:ignore.self class="modal fade" id="viewSystemLogModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title">Error Detail #{{ $viewingLog?->id }}</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @if($viewingLog)
                        <dl class="row small">
                            <dt class="col-3">Exception</dt>
                            <dd class="col-9 text-danger">{{ $viewingLog->exception_class }}</dd>

                            <dt class="col-3">Message</dt>
                            <dd class="col-9">{{ $viewingLog->message }}</dd>

                            <dt class="col-3">File : Line</dt>
                            <dd class="col-9">{{ $viewingLog->file }} : {{ $viewingLog->line }}</dd>

                            <dt class="col-3">Institution</dt>
                            <dd class="col-9">{{ $viewingLog->institution?->name ?? '—' }}</dd>

                            <dt class="col-3">Branch</dt>
                            <dd class="col-9">{{ $viewingLog->branch?->name ?? '—' }}</dd>

                            <dt class="col-3">User</dt>
                            <dd class="col-9">{{ $viewingLog->user?->name ?? '—' }} ({{ $viewingLog->user_role ?? '—' }})</dd>

                            <dt class="col-3">Panel / Component</dt>
                            <dd class="col-9">{{ $viewingLog->panel }} / {{ $viewingLog->component }}</dd>

                            <dt class="col-3">URL</dt>
                            <dd class="col-9 text-break">{{ $viewingLog->url }}</dd>

                            <dt class="col-3">IP</dt>
                            <dd class="col-9">{{ $viewingLog->ip }}</dd>

                            <dt class="col-3">Context</dt>
                            <dd class="col-9"><pre class="small bg-light p-2 rounded">{{ json_encode($viewingLog->context, JSON_PRETTY_PRINT) }}</pre></dd>

                            <dt class="col-3">Trace</dt>
                            <dd class="col-9"><pre class="small bg-light p-2 rounded" style="max-height:300px; overflow:auto;">{{ $viewingLog->trace }}</pre></dd>
                        </dl>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('livewire:init', () => {
                Livewire.on('open-view-modal', () => {
                    new bootstrap.Modal(document.getElementById('viewSystemLogModal')).show();
                });
            });
        </script>
    @endpush

</div>