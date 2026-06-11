<div wire:init="$refresh">

    <div class="card no-print">

        {{-- Flash messages --}}
        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-3">
            <i class="bi bi-x-circle me-2"></i>{{ session('error') }}
            <button class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <!-- floating header -->
        <div class="mat-card-header header-pink-gradient">
            <h5 id="cardHeaderTitleAllsections">Student Id Card Generate</h5>
            <p id="cardHeaderSubtitle">A lightweight, extendable, dependency-free javascript HTML table plugin.</p>
        </div>

        <div class="row g-4 p-5">

            <div class="col-md-4">
                <div wire:ignore class="input-group input-group-outline">
                    <label class="form-label">Class</label>
                    <select wire:model.lazy="filterClass" class="form-select">
                        <option value="">Select Class</option>
                        @foreach($classes as $class)
                            <option value="{{ $class['id'] }}">{{ $class['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                @error('filterClass') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
            
            <div class="col-md-4">
            <div wire:ignore class="input-group input-group-outline">
                <label class="form-label">Section</label>
                <select wire:model.lazy="filterSection" class="form-select">
                    <option value="">Select Section</option>
                    <option value="all">All Sections</option>
                    @foreach($sections as $section)
                        <option value="{{ $section['id'] }}">{{ $section['name'] }}</option>
                    @endforeach
                </select>
            </div>
            @error('filterSection') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <div class="col-md-4">
            <div wire:ignore class="input-group input-group-outline">
                <label class="form-label">Template</label>
                <select wire:model.lazy="filterTemplate" class="form-select">
                    <option value="">Select Template</option>
                    @foreach($templates as $template)
                        <option value="{{ $template->id }}">{{ $template->name }}</option>
                    @endforeach
                </select>
            </div>
            @error('filterTemplate') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <div class="col-md-12 text-center">
                <button class="btn-pink w-100 d-flex justify-content-center align-items-center" wire:click="applyFilter" wire:loading.attr="disabled">
                    <span wire:loading wire:target="applyFilter" class="spinner-border spinner-border-sm"></span>
                    <i class="bi bi-funnel" wire:loading.remove wire:target="applyFilter"></i>
                    Filter
                </button>
            </div>
        </div>

        @if($filtered)
            <div class="card-header bg-white border-0">
                <!-- toolbar -->
                <div class="card-toolbar">
                    {{-- Left side --}}
                    <div class="card-toolbar-title d-flex align-items-center gap-2">
                        <span class="material-icons-round">people</span>
                        <h5 id="cardHeaderTitleAllsections">Student List</h5>
                    </div>

                    <!-- Right Side -->
                    <div class="date-row mb-4">
                        <div class="date-field">
                            <label class="date-label">Print Date</label>
                            <input type="date" class="form-control" style="width:200px;" wire:model.defer="print_date">
                            @error('print_date')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="date-field">
                            <label class="date-label">Expiry Date</label>
                            <input type="date" class="form-control" style="width:200px;" wire:model.defer="expiry_date">
                            @error('expiry_date')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                    </div>

                    <button class="btn-outline bg-dark text-white"
                            wire:click="generateCards"
                            wire:loading.attr="disabled"
                            @if(!$filtered || $students->isEmpty()) disabled @endif>
                        <span wire:loading wire:target="generateCards" class="spinner-border spinner-border-sm"></span>
                        <i class="bi bi-printer" wire:loading.remove wire:target="generateCards"></i>
                        Generate
                    </button>

                </div>
            </div>

            <div class="card-body pt-0">
                @if($students->isNotEmpty())
                <div style="font-size:.8rem;color:#282c34;margin-bottom:10px;">
                    <span style="color:var(--primary);font-weight:600;">{{ count($selectedIds) }}</span> of
                    <span style="color:#697381;font-weight:600;">{{ $students->count() }}</span> students selected
                </div>
                @endif
                <div class="table-responsive">
                    <table class="mat-table">
                        <thead>
                            <tr>
                                <th class="check-col"><input type="checkbox" class="red-check" wire:model.live="selectAll" @if($students->isEmpty()) disabled @endif></th>
                                <th>SL</th>
                                <th>Student Name</th>
                                <th>Class</th>
                                <th>Section</th>
                                <th>Category</th>
                                <th>Register No</th>
                                <th>Roll No</th>
                                <th>Mobile No</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($students as $i => $student)
                            <tr>
                                <td class="check-col">
                                        <input type="checkbox"
                                            class="red-check"
                                            wire:model.live="selectedIds"
                                            value="{{ $student->id }}">
                                    </td>
                                <td class="text-muted">{{ $i + 1 }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        @if($student->logo_path)
                                            <img src="{{ asset('storage/' . $student->logo_path) }}" class="avatar" alt="">
                                        @else
                                            <div class="avatar-placeholder">{{ strtoupper(substr($student->name,0,1)) }}</div>
                                        @endif
                                        <div>
                                            <div class="fw-500 text-dark">{{ $student->full_name }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>{{$student->class?->name}}</td>
                                <td>{{$student->section?->name}}</td>
                                <td>{{$student->category?->name}}</td>
                                <td>{{$student->register_no}}</td>
                                <td>{{$student->roll_no}}</td>
                                <td>{{$student->mobile}}</td>
                                
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
        
        @else
            <div class="gen-empty">
                <i class="bi bi-funnel"></i>
                <p>Please select Class, Section and Template, then click <strong>Filter</strong> to load students.</p>
            </div>
        @endif
    </div>

    {{-- ===== PRINT PREVIEW MODAL ===== --}}
    @if($showPrintPreview)
    <div class="modal fade show d-block no-print" tabindex="-1" style="background:rgba(0,0,0,.75);">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-printer me-2 text-danger"></i>
                        ID Card Print Preview
                        <span class="badge bg-secondary ms-2" style="font-size:.7rem;">{{ count($printCards) }} Cards</span>
                    </h5>
                    <button class="btn-close" wire:click="$set('showPrintPreview',false)"></button>
                </div>
                <div class="modal-body">
                    <div style="display:flex;flex-wrap:wrap;justify-content:flex-start;gap:16px;">
                        @foreach($printCards as $card)
                        @php $tmpl = $selectedTemplate; @endphp
                        <div class="id-card-print" style="background:{{ $tmpl?->background_color ?? '#fff' }};color:{{ $tmpl?->text_color ?? '#000' }};">
                            {{-- Header Band --}}
                            <div style="background:{{ $tmpl?->accent_color ?? '#007bff' }};padding:12px 14px;text-align:center;">
                                @if($tmpl?->logo_path)
                                    <img src="{{ asset('storage/' . $tmpl->logo_path) }}" height="28" style="margin-bottom:4px;">
                                @else
                                    <i class="bi bi-mortarboard" style="font-size:1.4rem;color:rgba(255,255,255,.8);display:block;margin-bottom:2px;"></i>
                                @endif
                                <div style="font-weight:700;font-size:.78rem;line-height:1.2;">
                                    {{ $tmpl?->header_text ?: ($card['institute_name'] ?? 'Institute Name') }}
                                </div>
                                <div style="font-size:.62rem;margin-top:2px;letter-spacing:.05em;">STUDENT ID CARD</div>
                            </div>

                            {{-- Body --}}
                            <div style="padding:12px 14px;display:flex;gap:10px;">
                                {{-- Photo --}}
                                @if($tmpl?->show_photo ?? true)
                                <div style="flex-shrink:0;">
                                    @if(!empty($card['photo']))
                                        <img src="{{ asset('storage/' . $card['photo']) }}"
                                            style="width:72px;height:88px;object-fit:cover;border-radius:6px;border:2px solid {{ $tmpl?->accent_color ?? '#007bff' }};">
                                    @else
                                        <div style="width:72px;height:88px;border-radius:6px;background:rgba(0,0,0,.06);
                                                    display:flex;align-items:center;justify-content:center;
                                                    border:2px solid {{ $tmpl?->accent_color ?? '#007bff' }};">
                                            <i class="bi bi-person" style="font-size:2rem;opacity:.3;color:{{ $tmpl?->text_color ?? '#000' }};"></i>
                                        </div>
                                    @endif
                                </div>
                                @endif

                                {{-- Info --}}
                                <div style="flex:1;min-width:0;">
                                    <div style="font-weight:700;font-size:.85rem;line-height:1.2;margin-bottom:4px;color:{{ $tmpl?->text_color ?? '#000' }};">
                                        {{ $card['name'] }}
                                    </div>
                                    <div style="font-size:.68rem;color:{{ $tmpl?->accent_color ?? '#007bff' }};font-weight:600;margin-bottom:6px;background:rgba(0,0,0,.05);display:inline-block;padding:1px 6px;border-radius:3px;">
                                        {{ $card['student_id'] }}
                                    </div>
                                    <table style="font-size:.68rem;width:100%;border-collapse:collapse;color:{{ $tmpl?->text_color ?? '#000' }};">
                                        @if(!empty($card['class']))
                                        <tr>
                                            <td style="color:rgba(0,0,0,.5);padding:1px 0;width:45%;">Class:</td>
                                            <td style="font-weight:500;">{{ $card['class'] }} {{ $card['section'] ? '- '.$card['section'] : '' }}</td>
                                        </tr>
                                        @endif
                                        @if(!empty($card['roll_no']))
                                        <tr>
                                            <td style="color:rgba(0,0,0,.5);padding:1px 0;">Roll:</td>
                                            <td style="font-weight:500;">{{ $card['roll_no'] }}</td>
                                        </tr>
                                        @endif
                                        @if(!empty($card['mobile']))
                                        <tr>
                                            <td style="color:rgba(0,0,0,.5);padding:1px 0;">Mobile:</td>
                                            <td>{{ $card['mobile'] }}</td>
                                        </tr>
                                        @endif
                                        @if(!empty($card['blood_group']))
                                        <tr>
                                            <td style="color:rgba(0,0,0,.5);padding:1px 0;">Blood:</td>
                                            <td style="color:{{ $tmpl?->accent_color ?? '#dc3545' }};font-weight:700;">{{ $card['blood_group'] }}</td>
                                        </tr>
                                        @endif
                                    </table>
                                </div>
                            </div>

                            {{-- Footer --}}
                            <div style="padding:8px 14px;border-top:1px solid rgba(0,0,0,.08);display:flex;justify-content:space-between;align-items:center;font-size:.65rem;color:rgba(0,0,0,.5);">
                                <span>
                                    @if(!empty($card['expiry_date']))
                                        Valid: {{ \Carbon\Carbon::parse($card['expiry_date'])->format('d M Y') }}
                                    @endif
                                </span>
                                <span>{{ $tmpl?->footer_text ?: 'Authorized Signature' }}</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-outline-secondary border-secondary" wire:click="$set('showPrintPreview',false)">
                        <i class="bi bi-x-lg me-1"></i>Close
                    </button>
                    <button class="btn bg-dark text-white" onclick="window.print()">
                        <i class="bi bi-printer me-1"></i>Print All Cards
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Print-only layout --}}
    <div class="print-section">
        <style>
            @media print {
                body { background: #fff !important; }
                .no-print {display: none !important; }
                .print-section { display: block !important; }
                .print-cards-grid { display: flex; flex-wrap: wrap; gap: 10px; padding: 10px; }
            }
        </style>
        <div class="print-cards-grid">
            @foreach($printCards as $card)
            @php $tmpl = $selectedTemplate; @endphp
            <div class="id-card-print" style="background:{{ $tmpl?->background_color ?? '#fff' }};color:{{ $tmpl?->text_color ?? '#000' }};">
                <div style="background:{{ $tmpl?->accent_color ?? '#007bff' }};padding:12px 14px;text-align:center;">
                    <div style="color:#fff;font-weight:700;font-size:.78rem;">{{ $tmpl?->header_text ?: ($card['institute_name'] ?? 'Institute') }}</div>
                    <div style="color:rgba(255,255,255,.7);font-size:.62rem;">STUDENT ID CARD</div>
                </div>
                <div style="padding:12px 14px;display:flex;gap:10px;">
                    @if(!empty($card['photo']))
                        <img src="{{ asset('storage/' . $card['photo']) }}"
                            style="width:72px;height:88px;object-fit:cover;border-radius:6px;border:2px solid {{ $tmpl?->accent_color ?? '#007bff' }};">
                    @else
                        <div style="width:72px;height:88px;border-radius:6px;background:rgba(0,0,0,.06);
                                    display:flex;align-items:center;justify-content:center;
                                    border:2px solid {{ $tmpl?->accent_color ?? '#007bff' }};">
                            <i class="bi bi-person" style="font-size:2rem;opacity:.3;color:{{ $tmpl?->text_color ?? '#000' }};"></i>
                        </div>
                    @endif
                    <div style="flex:1;">
                        <div style="font-weight:700;font-size:.85rem;">{{ $card['name'] }}</div>
                        <div style="font-size:.7rem;margin-bottom:4px;">{{ $card['student_id'] }}</div>
                        <div style="font-size:.68rem;">Class: {{ $card['class'] }}</div>
                        <div style="font-size:.68rem;">Roll: {{ $card['roll_no'] }}</div>
                        <div style="font-size:.68rem;">Mobile: {{ $card['mobile'] }}</div>
                        <div style="font-size:.68rem;color:red;">Blood: {{ $card['blood_group'] }}</div>
                    </div>
                </div>
                <div style="padding:6px 14px;border-top:1px solid #e5e7eb;font-size:.65rem;color:#6b7280;display:flex;justify-content:space-between;">
                    <span>Valid: {{ !empty($card['expiry_date']) ? \Carbon\Carbon::parse($card['expiry_date'])->format('d M Y') : '' }}</span>
                    <span>{{ $tmpl?->footer_text ?: '' }}</span>
                </div>
            </div>
            @endforeach
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

        .form-control:focus, .form-select:focus {
            border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-light);
        }

        .btn-sm { font-size: .78rem; padding: .3rem .65rem; border-radius: 6px; }

    </style>
@endpush

@push('styles')
<style>
    .gen-section {
        background: #fbfcff;
        border-radius: 10px;
        border: 1px solid #2e3a4e;
        margin-bottom: 20px;
        overflow: hidden;
    }
    .gen-section-header {
        padding: 14px 20px;
        border-bottom: 2px solid var(--primary);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .gen-section-header h6 {
        color: #020202;
        font-weight: 600;
        font-size: .9rem;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .gen-section-body { padding: 20px; }

    /* Dark Selects & Inputs */
    .gen-label {
        font-size: .75rem;
        font-weight: 500;
        color: #9ca3af;
        margin-bottom: 6px;
        display: block;
    }
    .gen-label .req { color: var(--primary); }
    .gen-select, .gen-input {
        width: 100%;
        background: #1e2432;
        border: 1px solid #3a4558;
        color: #e2e8f0;
        border-radius: 8px;
        padding: 10px 14px;
        font-size: .875rem;
        appearance: none;
        -webkit-appearance: none;
        transition: border-color .2s;
        font-family: 'Inter', sans-serif;
    }
    .gen-select { background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%239ca3af' d='M6 8L1 3h10z'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 12px center; padding-right: 36px; cursor: pointer; }
    .gen-select:focus, .gen-input:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(239,84,84,.15);
    }
    .gen-select option { background: #1e2432; color: #e2e8f0; }

    /* Filter Button */
    .btn-filter {
        background: #2e3a4e;
        border: 1px solid #3a4558;
        color: #e2e8f0;
        padding: 10px 20px;
        border-radius: 8px;
        font-size: .875rem;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all .2s;
    }
    .btn-filter:hover { background: #3a4558; border-color: var(--primary); color: var(--primary); }

    /* Generate button */
    .btn-generate {
        background: var(--primary);
        border: none;
        color: #fff;
        padding: 8px 20px;
        border-radius: 8px;
        font-size: .875rem;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: background .2s;
    }
    .btn-generate:hover { background: #d63e3e; }
    .btn-generate:disabled { opacity: .6; cursor: not-allowed; }

    /* Date row */
    .date-row { display: flex; gap: 20px; margin-bottom: 16px; flex-wrap: wrap; }
    .date-field { display: flex; flex-direction: column; gap: 4px; }
    .date-label { font-size: .75rem; color: #9ca3af; }

    /* Student Table */
    .gen-table { width: 100%; border-collapse: collapse; }
    .gen-table thead tr { border-bottom: 1px solid #2e3a4e; }
    .gen-table th {
        padding: 10px 14px;
        font-size: .72rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: #9ca3af;
        background: #1e2a3a;
    }
    .gen-table td {
        padding: 11px 14px;
        font-size: .85rem;
        color: #cbd5e1;
        border-bottom: 1px solid #2e3a4e;
    }
    .gen-table tbody tr:hover { background: rgba(255,255,255,.03); }
    .gen-table .check-col { width: 44px; text-align: center; }

    /* Red checkbox */
    .red-check {
        width: 18px; height: 18px;
        accent-color: var(--primary);
        cursor: pointer;
        border-radius: 4px;
    }

    /* Empty state */
    .gen-empty {
        text-align: center;
        padding: 48px 20px;
        color: #4b5563;
    }
    .gen-empty i { font-size: 3rem; display: block; margin-bottom: 12px; }

    /* ID Card Print Preview */
    .id-card-print {
        width: 325px;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0,0,0,.2);
        font-family: 'Inter', sans-serif;
        break-inside: avoid;
        display: inline-block;
        margin: 8px;
    }

    @media print {
        .print-section { display: block !important; }
        .id-card-print { box-shadow: none; border: 1px solid #e5e7eb; }
    }

    .print-section { display: none; }

    /* Responsive selects */
    @media (max-width: 576px) {
        .date-row { flex-direction: column; }
        .gen-section-body .row > div { margin-bottom: 12px; }
    }
</style>
@endpush
