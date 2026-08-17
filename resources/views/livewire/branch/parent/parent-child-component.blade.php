<div>
    <div class="card border-0 bg-transparent">
        <div class="container-xl mt-4">

            <div id="guardianChildrenPrintable">

                @include('livewire.admin.parent.parent-navbar', ['guardian' => $guardian])

                <!-- Children / Students -->
                <div class="card shadow-sm mb-4 mt-4 avoid-break">
                    <div class="card-header bg-white d-flex align-items-center gap-2">
                        <span class="material-icons-round text-primary">family_restroom</span>
                        <span class="fw-semibold">Children ({{ $guardian->students->count() }})</span>
                    </div>

                    <div class="card-body p-0">
                        @if($guardian->students->isNotEmpty())
                            <div class="table-responsive">
                                <table class="table table-hover mb-0 align-middle">
                                    <thead>
                                        <tr>
                                            <th class="small text-muted text-uppercase">#</th>
                                            <th class="small text-muted text-uppercase">Name</th>
                                            <th class="small text-muted text-uppercase">Class</th>
                                            <th class="small text-muted text-uppercase">Section</th>
                                            <th class="small text-muted text-uppercase">Roll No</th>
                                            <th class="small text-muted text-uppercase">Register No</th>
                                            <th class="small text-muted text-uppercase">Gender</th>
                                            <th class="small text-muted text-uppercase no-print">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($guardian->students as $index => $student)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <img src="{{ $student->photo ? asset('storage/' . $student->photo) : 'https://ui-avatars.com/api/?name=' . urlencode($student->name) . '&size=64&background=random' }}"
                                                        style="width:34px;height:34px;border-radius:8px;object-fit:cover;" alt="{{ $student->name }}">
                                                    <span>
                                                        <span class="fw-500">{{ $student->name }}</span> <br> 
                                                        <span class="fs-8 fw-bold">{{ $student->student_id }}</span>
                                                    </span>
                                                </div>
                                            </td>
                                            <td>{{ $student->class?->name ?? '—' }}</td>
                                            <td>{{ $student->section?->name ?? '—' }}</td>
                                            <td>{{ $student->roll_no ?? '—' }}</td>
                                            <td>{{ $student->registration_no ?? '—' }}</td>
                                            <td>{{ ucfirst($student->gender ?? '—') }}</td>
                                            <td class="no-print">
                                                <a href="{{ route('admin.student.overview', ['id' => $student->id]) }}"
                                                    target="_blank" class="btn btn-outline-secondary btn-sm" title="View Student">
                                                    <span class="material-icons-round align-middle" style="font-size:1rem;">visibility</span>
                                                </a>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-5 text-muted">
                                <span class="material-icons-round d-block mb-2 opacity-25" style="font-size:3rem">people_outline</span>
                                <p class="mb-0 small">No children linked to this parent.</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Action Toolbar — hidden on print via no-print -->
                <div class="d-flex justify-content-end gap-2 my-3 no-print">
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="history.back()">
                        <span class="material-icons-round align-middle" style="font-size:1rem;">arrow_back</span>
                        Back
                    </button>
                    <button type="button" class="btn btn-dark btn-sm" onclick="printGuardianChildren()">
                        <span class="material-icons-round align-middle" style="font-size:1rem;">print</span>
                        Print
                    </button>
                </div>

            </div>

        </div>
    </div>
</div>

@push('scripts')
    <script>
        function printGuardianChildren() {
            const printableEl = document.getElementById('guardianChildrenPrintable');

            if (!printableEl) {
                return;
            }

            const printContent = printableEl.innerHTML;
            const printWindow = window.open('', '_blank', 'width=900,height=650');

            if (!printWindow) {
                alert('Print window block hoye গেছে। Browser-er popup blocker check korun.');
                return;
            }

            printWindow.document.write(`
                <html>
                    <head>
                        <title>Children - {{ $guardian->name }}</title>
                        <style>
                            * { box-sizing: border-box; }
                            body { font-family: Arial, Helvetica, sans-serif; padding: 28px; color: #222; }
                            .no-print { display: none !important; }

                            .row { display: flex; flex-wrap: wrap; margin: 0 -8px; }
                            .col-12 { width: 100%; padding: 6px 8px; }
                            .col-6 { width: 50%; padding: 6px 8px; }
                            .col-md-3 { width: 25%; padding: 6px 8px; }
                            .col-md-4 { width: 33.333%; padding: 6px 8px; }
                            .col-md-6 { width: 50%; padding: 6px 8px; }
                            .col-md-8 { width: 66.666%; padding: 6px 8px; }

                            .d-flex { display: flex; }
                            .flex-wrap { flex-wrap: wrap; }
                            .align-items-center { align-items: center; }
                            .align-items-start { align-items: flex-start; }
                            .justify-content-between { justify-content: space-between; }
                            .gap-1 { gap: 4px; }
                            .gap-2 { gap: 8px; }
                            .gap-3 { gap: 12px; }
                            .gap-4 { gap: 16px; }
                            .flex-grow-1 { flex-grow: 1; }
                            .mb-1 { margin-bottom: 4px; }
                            .mb-2 { margin-bottom: 8px; }
                            .mb-4 { margin-bottom: 16px; }
                            .mt-3 { margin-top: 12px; }
                            .p-4 { padding: 16px; }


                            .text-muted { color: #6c757d !important; }
                            .text-dark { color: #212529 !important; }
                            .fw-bold, .fw-semibold, .fw-medium { font-weight: 600; }
                            .fs-5, .fs-6 { font-size: .9rem; }
                            .small { font-size: .8rem; }
                            .text-uppercase { text-transform: uppercase; letter-spacing: .05em; }

                            .card { border: 1px solid #ddd; border-radius: 8px;
                                    margin-bottom: 16px; padding: 14px; }
                            .card-header { font-weight: 600; margin-bottom: 8px; padding-bottom: 8px;
                                           border-bottom: 1px solid #eee; }

                            /* ── Table styling — এটাই মিসিং ছিল ── */
                            table { width: 100%; border-collapse: collapse; font-size: .85rem; }
                            thead { display: table-header-group; }
                            th, td {
                                padding: 8px 12px;
                                text-align: left;
                                border-bottom: 1px solid #ddd;
                                vertical-align: middle;
                                white-space: nowrap;
                            }
                            thead th {
                                border-bottom: 2px solid #999;
                                background: #f8f9fa;
                                font-weight: 600;
                            }
                            tbody tr:nth-child(even) { background: #fafafa; }

                            .material-icons-round, .bi { display: none; } /* icon fonts aren't loaded here */

                            .avoid-break { break-inside: avoid; page-break-inside: avoid; }
                        </style>
                    </head>
                    <body>
                        ${printContent}
                    </body>
                </html>
            `);

            printWindow.document.close();
            printWindow.focus();

            setTimeout(() => {
                printWindow.print();
                printWindow.close();
            }, 250);
        }
    </script>
@endpush