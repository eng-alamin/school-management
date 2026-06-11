<div>

    <div class="card">

        <div class="mat-card-header header-pink-gradient">
            <h5>My Homeworks</h5>
            <p>View homework assigned to your class.</p>
        </div>

        @if(empty($homeworks))
        <div class="text-center py-5 text-muted">
            <i class="bi bi-inbox display-5 d-block mb-2 opacity-25"></i>
            No homework found for your class.
        </div>
        @else

        <div class="table-card">
            <div class="card-toolbar">
                <div class="card-toolbar-title">
                    <div style="position:relative;display:inline-flex;align-items:center">
                        <span class="material-icons-round" style="position:absolute;left:10px;font-size:17px;color:var(--muted);pointer-events:none">search</span>
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search…"
                               style="border:1px solid rgba(0,0,0,.1);border-radius:8px;padding:7px 12px 7px 32px;font-size:.78rem;font-family:inherit;color:var(--dark);outline:none;background:#f8f9fa;width:220px"/>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="mat-table">
                    <thead>
                        <tr>
                            <th>SL</th>
                            <th>Subject</th>
                            <th>Title</th>
                            <th>Homework Date</th>
                            <th>Submission Date</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($filteredHomeworks as $i => $hw)
                        @php
                            $statusMap = [
                                'active'   => ['label' => 'Active',   'color' => '#28a745'],
                                'inactive' => ['label' => 'Inactive', 'color' => '#dc3545'],
                                'pending'  => ['label' => 'Pending',  'color' => '#fd7e14'],
                            ];
                            $s = $statusMap[strtolower($hw['status'] ?? '')] ?? ['label' => ucfirst($hw['status'] ?? '—'), 'color' => '#6c757d'];
                        @endphp
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $hw['subject']['name'] ?? '—' }}</td>
                            <td>{{ $hw['title'] ?? '—' }}</td>
                            <td>{{ $hw['homework_date'] ?? '—' }}</td>
                            <td>{{ $hw['submission_date'] ?? '—' }}</td>
                            <td>
                                <span style="display:inline-block;padding:2px 10px;border-radius:4px;border:1px solid {{ $s['color'] }};color:{{ $s['color'] }};font-size:.75rem;font-weight:600;">
                                    {{ $s['label'] }}
                                </span>
                            </td>
                            <td>
                                <button class="act-btn edit" title="View" wire:click="openDetail({{ $hw['id'] }})">
                                    <span class="material-icons-round">visibility</span>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox display-5 d-block mb-2 opacity-25"></i>
                                No homework matches your search.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div style="padding:14px 18px;font-size:.75rem;color:var(--muted)">
                Showing {{ count($filteredHomeworks) }} of {{ count($homeworks) }} homework(s)
            </div>
        </div>

        @endif

    </div>


    {{-- ===== DETAIL VIEW MODAL ===== --}}
    @if($showDetail)
    <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5);" wire:ignore.self>
        <div class="modal-dialog modal-md modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title">
                        <span class="material-icons-round me-1" style="vertical-align:middle;font-size:1.1rem">menu_book</span>
                        Homework Details
                    </h5>
                    <button type="button" class="btn-close" wire:click="$set('showDetail', false)"></button>
                </div>
                <div class="modal-body">
                    @php
                        $statusMap = [
                            'active'   => ['label' => 'Active',   'color' => '#28a745'],
                            'inactive' => ['label' => 'Inactive', 'color' => '#dc3545'],
                            'pending'  => ['label' => 'Pending',  'color' => '#fd7e14'],
                        ];
                        $ms = $statusMap[strtolower($detail['status'] ?? '')] ?? ['label' => ucfirst($detail['status'] ?? '—'), 'color' => '#6c757d'];
                    @endphp
                    <table class="table table-borderless mb-0" style="font-size:.875rem;">
                        <tbody>
                            <tr>
                                <td class="fw-600 text-nowrap" style="width:160px">Subject :</td>
                                <td>{{ $detail['subject']['name'] ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td class="fw-600">Class :</td>
                                <td>{{ $detail['class']['name'] ?? '—' }} / {{ $detail['section']['name'] ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td class="fw-600">Title :</td>
                                <td>{{ $detail['title'] ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td class="fw-600">Homework Date :</td>
                                <td>{{ $detail['homework_date'] ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td class="fw-600">Submission Date :</td>
                                <td>{{ $detail['submission_date'] ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td class="fw-600">Status :</td>
                                <td>
                                    <span style="display:inline-block;padding:2px 10px;border-radius:4px;border:1px solid {{ $ms['color'] }};color:{{ $ms['color'] }};font-size:.75rem;font-weight:600;">
                                        {{ $ms['label'] }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-600 align-top">Description :</td>
                                <td style="white-space:pre-line">{{ $detail['description'] ?? '—' }}</td>
                            </tr>
                            @if(!empty($detail['file_path']))
                            <tr>
                                <td class="fw-600">Attachment :</td>
                                <td>
                                    <a href="{{ asset('storage/' . $detail['file_path']) }}" target="_blank"
                                       class="btn btn-sm btn-outline-secondary" style="font-size:.75rem;border-radius:6px;">
                                        <span class="material-icons-round" style="font-size:13px;vertical-align:middle">download</span>
                                        Download
                                    </a>
                                </td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light btn-sm" wire:click="$set('showDetail', false)">Close</button>
                </div>
            </div>
        </div>
    </div>
    @endif

</div>

@push('styles')
<style>
    .card { border: 1px solid var(--border); border-radius: 12px; box-shadow: 0 1px 4px rgba(0,0,0,.04); }
    .card-header { background: #fff; border-bottom: 1px solid var(--border); border-radius: 12px 12px 0 0 !important; padding: 16px 20px; }
    
    .table-card { border-top: 1px solid var(--border); }
    .card-toolbar { display:flex;align-items:center;gap:12px;padding:14px 18px; }
    .mat-table { width:100%;border-collapse:collapse; }
    .mat-table th { background:#f8f9fa;font-size:.75rem;font-weight:600;color:var(--muted);padding:10px 14px;text-align:left;border-bottom:1px solid var(--border); }
    .mat-table td { font-size:.82rem;padding:10px 14px;border-bottom:1px solid rgba(0,0,0,.05);vertical-align:middle; }
    .mat-table tbody tr:hover { background:#fafafa; }
    .fw-600 { font-weight:600; }
    .modal-title { font-weight:600;font-size:1rem; }
</style>
@endpush