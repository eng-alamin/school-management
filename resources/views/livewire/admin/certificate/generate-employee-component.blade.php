<div wire:init="$refresh">

    <div class="card no-print">

        {{-- Flash messages --}}
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show mb-3 mx-4 mt-3">
                <i class="bi bi-x-circle me-2"></i>{{ session('error') }}
                <button class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- floating header -->
        <div class="mat-card-header header-pink-gradient">
            <h5>Employee Certificate Generate</h5>
            <p>Select role and template, then generate certificates for employees.</p>
        </div>

        {{-- ── FILTER SECTION ── --}}
        <div class="row g-4 p-5">

            {{-- BUG FIX: wire:ignore removed from both selects --}}

            <div class="col-md-6">
                <div class="input-group input-group-outline" wire:ignore>
                    <label class="form-label">Role <span class="req">*</span></label>
                    <select wire:model.live="filterRole" class="form-select">
                        <option value="">Select Role</option>
                        @foreach($roles as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                @error('filterRole') <span class="text-danger small">{{ $message }}</span> @enderror
            </div>

            <div class="col-md-6">
                <div class="input-group input-group-outline" wire:ignore>
                    <label class="form-label">Certificate Template <span class="req">*</span></label>
                    <select wire:model.live="filterTemplate" class="form-select">
                        <option value="">Select Template</option>
                        @foreach($templates as $template)
                            <option value="{{ $template->id }}">{{ $template->certificate_name }}</option>
                        @endforeach
                    </select>
                </div>
                @error('filterTemplate') <span class="text-danger small">{{ $message }}</span> @enderror
            </div>

            <div class="col-md-12 text-center">
                <button class="btn-pink w-100 d-flex justify-content-center align-items-center"
                        wire:click="applyFilter"
                        wire:loading.attr="disabled">
                    <span wire:loading wire:target="applyFilter"
                          class="spinner-border spinner-border-sm me-2"></span>
                    <i class="bi bi-funnel me-1" wire:loading.remove wire:target="applyFilter"></i>
                    Filter Employees
                </button>
            </div>

        </div>

        {{-- ── EMPLOYEE TABLE (after filter) ── --}}
        @if($filtered)
            <div class="card-header bg-white border-0">
                <div class="card-toolbar">

                    {{-- Left --}}
                    <div class="card-toolbar-title d-flex align-items-center gap-2">
                        <span class="material-icons-round">people</span>
                        <h5 class="mb-0">Employee List</h5>
                    </div>

                    {{-- Right: Issue Date + Generate button --}}
                    <div class="d-flex align-items-end gap-3 flex-wrap">
                        <div class="date-field">
                            <label class="date-label">Issue Date</label>
                            <input type="date"
                                   class="form-control"
                                   style="width:200px;"
                                   wire:model.defer="issue_date">
                            @error('issue_date')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <button class="btn-outline bg-dark text-white"
                                wire:click="generateCertificates"
                                wire:loading.attr="disabled"
                                @if($employees->isEmpty() || empty($selectedIds)) disabled @endif>
                            <span wire:loading wire:target="generateCertificates"
                                  class="spinner-border spinner-border-sm me-1"></span>
                            <i class="bi bi-file-earmark-text me-1"
                               wire:loading.remove wire:target="generateCertificates"></i>
                            Generate
                        </button>
                    </div>

                </div>
            </div>

            <div class="card-body pt-0">

                @if($employees->isNotEmpty())
                    <div style="font-size:.8rem;color:#282c34;margin-bottom:10px;">
                        <span style="color:var(--primary);font-weight:600;">{{ count($selectedIds) }}</span> of
                        <span style="color:#697381;font-weight:600;">{{ $employees->count() }}</span> employees selected
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="mat-table">
                        <thead>
                            <tr>
                                <th class="check-col">
                                    <input type="checkbox"
                                           class="red-check"
                                           wire:model.live="selectAll"
                                           @if($employees->isEmpty()) disabled @endif>
                                </th>
                                <th id="th-sl">SL</th>
                                <th id="th-employee-name">Employee Name</th>
                                <th id="th-department">Department</th>
                                <th id="th-designation">Designation</th>
                                <th id="th-mobile-no">Mobile No</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($employees as $i => $employee)
                                <tr>
                                    <td class="check-col">
                                        <input type="checkbox"
                                               class="red-check"
                                               wire:model.live="selectedIds"
                                               value="{{ $employee->id }}">
                                    </td>
                                    <td class="text-muted">{{ $i + 1 }}</td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            {{-- BUG FIX: was $employee->logo_path, should be photo --}}
                                            @if($employee->photo)
                                                <img src="{{ asset('storage/' . $employee->photo) }}"
                                                     class="avatar" alt="">
                                            @else
                                                <div class="avatar-placeholder">
                                                    {{ strtoupper(substr($employee->name, 0, 1)) }}
                                                </div>
                                            @endif
                                            <div class="fw-500 text-dark">{{ $employee->name }}</div>
                                        </div>
                                    </td>
                                    <td>{{ $employee->department?->name ?? '—' }}</td>
                                    <td>{{ $employee->designation?->name ?? '—' }}</td>
                                    <td>{{ $employee->mobile ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <span class="material-icons-round d-block mb-2"
                                              style="font-size:2.5rem;opacity:.2">person_search</span>
                                        No employees found for the selected filters.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        @else
            <div class="gen-empty">
                <i class="bi bi-funnel"></i>
                <p>Please select <strong>Role</strong> and <strong>Template</strong>,
                   then click <strong>Filter Employees</strong>.</p>
            </div>
        @endif

    </div>{{-- /card --}}


    {{-- ════════════════════════════════════════
         CERTIFICATE PRINT PREVIEW MODAL
    ════════════════════════════════════════ --}}
    @if($showPrintPreview)
    <div class="modal fade show d-block no-print" tabindex="-1" style="background:rgba(0,0,0,.75);">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-file-earmark-text me-2 text-danger"></i>
                        Certificate Print Preview
                        <span class="badge bg-secondary ms-2" style="font-size:.7rem;">
                            {{ count($printCards) }} Certificates
                        </span>
                    </h5>
                    <button class="btn-close" wire:click="$set('showPrintPreview', false)"></button>
                </div>

                <div class="modal-body">
                    <div style="display:flex;flex-wrap:wrap;gap:24px;justify-content:flex-start;">

                        @foreach($printCards as $card)
                        @php
                            $tmpl = $card['template'];

                            $sizes = [
                                'a4_portrait'  => ['width' => '210mm', 'minHeight' => '297mm'],
                                'a4_landscape' => ['width' => '297mm', 'minHeight' => '210mm'],
                                'a5_portrait'  => ['width' => '148mm', 'minHeight' => '210mm'],
                                'a5_landscape' => ['width' => '210mm', 'minHeight' => '148mm'],
                            ];
                            $size = $sizes[$tmpl->page_layout] ?? $sizes['a4_portrait'];

                            $mt = $tmpl->margin_top    ?? 80;
                            $mr = $tmpl->margin_right  ?? 80;
                            $mb = $tmpl->margin_bottom ?? 80;
                            $ml = $tmpl->margin_left   ?? 80;
                        @endphp

                        <div class="cert-preview-wrap">

                            {{-- Certificate Sheet --}}
                            <div class="cert-sheet"
                                 style="width:{{ $size['width'] }};
                                        min-height:{{ $size['minHeight'] }};
                                        padding:{{ $mt }}px {{ $mr }}px {{ $mb }}px {{ $ml }}px;
                                        position:relative;overflow:hidden;">

                                {{-- Background image --}}
                                @if($tmpl->background_image)
                                    <img src="{{ asset('storage/' . $tmpl->background_image) }}"
                                         style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;z-index:0;opacity:.18;">
                                @endif

                                {{-- Content layer --}}
                                <div style="position:relative;z-index:1;">

                                    {{-- Logo --}}
                                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
                                        @if($tmpl->logo_image)
                                            <img src="{{ asset('storage/' . $tmpl->logo_image) }}"
                                                 style="height:64px;object-fit:contain;">
                                        @else
                                            <div style="width:64px;"></div>
                                        @endif
                                    </div>

                                    {{-- All placeholders already replaced in PHP --}}
                                    <div class="cert-content">
                                        {!! $card['content'] !!}
                                    </div>

                                    {{-- Signature --}}
                                    @if($tmpl->signature_image)
                                        <div style="margin-top:40px;text-align:right;">
                                            <img src="{{ asset('storage/' . $tmpl->signature_image) }}"
                                                 style="height:48px;object-fit:contain;">
                                            <div style="font-size:.72rem;color:#888;margin-top:4px;border-top:1px solid #ddd;padding-top:4px;">
                                                Authorized Signature
                                            </div>
                                        </div>
                                    @endif

                                    {{-- Issue date --}}
                                    <div style="margin-top:24px;font-size:.72rem;color:#999;text-align:left;">
                                        Issue Date:
                                        {{ \Carbon\Carbon::parse($card['issue_date'])->format('d M Y') }}
                                    </div>

                                </div>
                            </div>

                            <div style="text-align:center;margin-top:6px;font-size:.75rem;color:#6b7280;">
                                {{ $card['name'] }}
                                @if(!empty($card['designation'])) — {{ $card['designation'] }} @endif
                            </div>

                        </div>
                        @endforeach

                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-outline-secondary border-secondary"
                            wire:click="$set('showPrintPreview', false)">
                        <i class="bi bi-x-lg me-1"></i>Close
                    </button>
                    <button class="btn bg-dark text-white" onclick="window.print()">
                        <i class="bi bi-printer me-1"></i>Print All Certificates
                    </button>
                </div>

            </div>
        </div>
    </div>

    {{-- ── PRINT-ONLY LAYOUT ── --}}
    <div class="print-section">
        @foreach($printCards as $card)
        @php
            $tmpl  = $card['template'];
            $sizes = [
                'a4_portrait'  => ['width' => '210mm', 'minHeight' => '297mm'],
                'a4_landscape' => ['width' => '297mm', 'minHeight' => '210mm'],
                'a5_portrait'  => ['width' => '148mm', 'minHeight' => '210mm'],
                'a5_landscape' => ['width' => '210mm', 'minHeight' => '148mm'],
            ];
            $size  = $sizes[$tmpl->page_layout] ?? $sizes['a4_portrait'];
            $mt = $tmpl->margin_top    ?? 80;
            $mr = $tmpl->margin_right  ?? 80;
            $mb = $tmpl->margin_bottom ?? 80;
            $ml = $tmpl->margin_left   ?? 80;
        @endphp
        <div class="cert-sheet print-page"
             style="width:{{ $size['width'] }};min-height:{{ $size['minHeight'] }};
                    padding:{{ $mt }}px {{ $mr }}px {{ $mb }}px {{ $ml }}px;
                    position:relative;overflow:hidden;page-break-after:always;">

            @if($tmpl->background_image)
                <img src="{{ asset('storage/' . $tmpl->background_image) }}"
                     style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;z-index:0;opacity:.18;">
            @endif

            <div style="position:relative;z-index:1;">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
                    @if($tmpl->logo_image)
                        <img src="{{ asset('storage/' . $tmpl->logo_image) }}" style="height:64px;object-fit:contain;">
                    @else
                        <div style="width:64px;"></div>
                    @endif
                </div>

                <div class="cert-content">{!! $card['content'] !!}</div>

                @if($tmpl->signature_image)
                    <div style="margin-top:40px;text-align:right;">
                        <img src="{{ asset('storage/' . $tmpl->signature_image) }}" style="height:48px;object-fit:contain;">
                        <div style="font-size:.72rem;color:#888;margin-top:4px;border-top:1px solid #ddd;padding-top:4px;">
                            Authorized Signature
                        </div>
                    </div>
                @endif

                <div style="margin-top:24px;font-size:.72rem;color:#999;">
                    Issue Date: {{ \Carbon\Carbon::parse($card['issue_date'])->format('d M Y') }}
                </div>
            </div>
        </div>
        @endforeach
    </div>

    @endif

</div>


@push('styles')
<style>
    .gen-empty {
        text-align: center;
        padding: 48px 20px;
        color: #9ca3af;
    }
    .gen-empty i { font-size: 3rem; display: block; margin-bottom: 12px; }

    .date-field { display: flex; flex-direction: column; gap: 4px; }
    .date-label { font-size: .75rem; color: #9ca3af; }

    .red-check {
        width: 18px; height: 18px;
        accent-color: var(--primary);
        cursor: pointer;
    }

    .cert-preview-wrap { display: inline-block; }
    .cert-sheet {
        background: #fff;
        box-shadow: 0 4px 24px rgba(0,0,0,.15);
        border: 1px solid #e5e7eb;
        box-sizing: border-box;
    }
    .cert-content {
        font-size: .9rem;
        line-height: 1.8;
        color: #1f2937;
    }
    .cert-content p { margin-bottom: .6rem; }

    .print-section { display: none; }

    @media print {
        body { background: #fff !important; }
        .no-print  { display: none !important; }
        .print-section { display: block !important; }
        .print-page { box-shadow: none !important; border: none !important; }
    }

    .mat-table { width: 100%; border-collapse: collapse; }
    .mat-table thead tr { border-bottom: 2px solid var(--border, #e5e7eb); }
    .mat-table th {
        padding: 10px 14px;
        font-size: .72rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: var(--text-muted, #9ca3af);
    }
    .mat-table td {
        padding: 11px 14px;
        font-size: .85rem;
        border-bottom: 1px solid var(--border, #e5e7eb);
        vertical-align: middle;
    }
    .mat-table .check-col { width: 44px; text-align: center; }

    .avatar {
        width: 34px; height: 34px;
        border-radius: 50%;
        object-fit: cover;
        border: 1px solid #e5e7eb;
    }
    .avatar-placeholder {
        width: 34px; height: 34px;
        border-radius: 50%;
        background: var(--primary-light, #fee2e2);
        color: var(--primary, #e74c3c);
        display: flex; align-items: center; justify-content: center;
        font-size: .8rem; font-weight: 700;
    }
</style>
@endpush