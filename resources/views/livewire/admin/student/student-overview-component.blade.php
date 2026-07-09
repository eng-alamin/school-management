<div>

    <div class="card border-0 bg-transparent">

        <div class="mat-card-header header-pink-gradient no-print">
            <h5 id="cardHeaderTitleStudentOverview">Student Overview</h5>
        </div>

        <div class="container-xl mt-4">

            <div id="studentDetailsPrintable">

                @include('livewire.admin.student.student-navbar', ['student' => $student])

                <!-- START CONTENT -->

                <!-- Academic Details -->
                <div class="card shadow-sm mb-4 avoid-break">
                    <div class="card-header bg-white d-flex align-items-center gap-2">
                        <span class="material-icons-round text-primary">school</span>
                        <span class="fw-semibold">Academic Details</span>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-6 col-md-4">
                                <div class="text-muted small">Academic Year</div>
                                <div class="fw-medium">{{ $student->session->name ?? '—' }}</div>
                            </div>
                            <div class="col-6 col-md-4">
                                <div class="text-muted small">Register No</div>
                                <div class="fw-medium">{{ $student->registration_no ?? '—' }}</div>
                            </div>
                            <div class="col-6 col-md-4">
                                <div class="text-muted small">Roll No</div>
                                <div class="fw-medium">{{ $student->roll_no ?? '—' }}</div>
                            </div>
                            <div class="col-6 col-md-4">
                                <div class="text-muted small">Admission Date</div>
                                <div class="fw-medium">{{ $student->admission_date ? \Carbon\Carbon::parse($student->admission_date)->format('d M Y') : '—' }}</div>
                            </div>
                            <div class="col-6 col-md-4">
                                <div class="text-muted small">Class</div>
                                <div class="fw-medium">{{ $student->class->name ?? '—' }}</div>
                            </div>
                            <div class="col-6 col-md-4">
                                <div class="text-muted small">Section · Group</div>
                                <div class="fw-medium">{{ $student->section->name ?? '—' }} &nbsp;·&nbsp; {{ $student->group->name ?? '—' }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Student Details -->
                <div class="card shadow-sm mb-4 avoid-break">
                    <div class="card-header bg-white d-flex align-items-center gap-2">
                        <span class="material-icons-round text-primary">person</span>
                        <span class="fw-semibold">Student Details</span>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="text-muted small">Full Name</div>
                                <div class="fw-medium">{{ $student->name }}</div>
                            </div>
                            <div class="col-6 col-md-4">
                                <div class="text-muted small">Gender</div>
                                <div class="fw-medium">{{ $student->gender ? ucfirst($student->gender) : '—' }}</div>
                            </div>
                            <div class="col-6 col-md-4">
                                <div class="text-muted small">Date of Birth</div>
                                <div class="fw-medium">{{ $student->dob ? \Carbon\Carbon::parse($student->dob)->format('d M Y') : '—' }}</div>
                            </div>
                            <div class="col-6 col-md-4">
                                <div class="text-muted small">Blood Group</div>
                                <div class="fw-medium">{{ $student->blood_group ?? '—' }}</div>
                            </div>
                            <div class="col-6 col-md-4">
                                <div class="text-muted small">Religion</div>
                                <div class="fw-medium">{{ $student->religion ? ucfirst($student->religion) : '—' }}</div>
                            </div>
                            <div class="col-6 col-md-4">
                                <div class="text-muted small">Mobile No</div>
                                <div class="fw-medium">{{ $student->mobile ?? '—' }}</div>
                            </div>
                            <div class="col-md-8">
                                <div class="text-muted small">Email</div>
                                <div class="fw-medium">{{ $student->email ?? '—' }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-muted small">Present Address</div>
                                <div class="fw-medium">{{ $student->present_address ?? '—' }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-muted small">Permanent Address</div>
                                <div class="fw-medium">{{ $student->permanent_address ?? '—' }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Guardian Details -->
                @php $guardian = $student->guardians->first(); @endphp
                <div class="card shadow-sm mb-4 avoid-break">
                    <div class="card-header bg-white d-flex align-items-center gap-2">
                        <span class="material-icons-round text-primary">supervisor_account</span>
                        <span class="fw-semibold">Guardian Details</span>
                    </div>
                    <div class="card-body">
                        @if($guardian)
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="text-muted small">Name</div>
                                <div class="fw-medium">{{ $guardian->name }}</div>
                            </div>
                            <div class="col-6 col-md-4">
                                <div class="text-muted small">Relation</div>
                                <div class="fw-medium">{{ $guardian->relation ?? '—' }}</div>
                            </div>
                            <div class="col-6 col-md-4">
                                <div class="text-muted small">Father Name</div>
                                <div class="fw-medium">{{ $guardian->father_name ?? '—' }}</div>
                            </div>
                            <div class="col-6 col-md-4">
                                <div class="text-muted small">Mother Name</div>
                                <div class="fw-medium">{{ $guardian->mother_name ?? '—' }}</div>
                            </div>
                            <div class="col-6 col-md-4">
                                <div class="text-muted small">Occupation</div>
                                <div class="fw-medium">{{ $guardian->occupation ?? '—' }}</div>
                            </div>
                            <div class="col-6 col-md-4">
                                <div class="text-muted small">Income</div>
                                <div class="fw-medium">{{ $guardian->income ?? '—' }}</div>
                            </div>
                            <div class="col-6 col-md-4">
                                <div class="text-muted small">Education</div>
                                <div class="fw-medium">{{ $guardian->education ?? '—' }}</div>
                            </div>
                            <div class="col-6 col-md-4">
                                <div class="text-muted small">Mobile No</div>
                                <div class="fw-medium">{{ $guardian->mobile ?? '—' }}</div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-muted small">Email</div>
                                <div class="fw-medium">{{ $guardian->email ?? '—' }}</div>
                            </div>
                            <div class="col-12">
                                <div class="text-muted small">Address</div>
                                <div class="fw-medium">{{ $guardian->address ?? '—' }}</div>
                            </div>
                        </div>
                        @else
                        <div class="text-muted">No guardian found.</div>
                        @endif
                    </div>
                </div>

                <!-- Previous School Details -->
                <div class="card shadow-sm mb-4 avoid-break">
                    <div class="card-header bg-white d-flex align-items-center gap-2">
                        <span class="material-icons-round text-primary">history_edu</span>
                        <span class="fw-semibold">Previous School Details</span>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="text-muted small">School Name</div>
                                <div class="fw-medium">{{ $student->previous_institution ?? '—' }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-muted small">Qualification</div>
                                <div class="fw-medium">{{ $student->qualification ?? '—' }}</div>
                            </div>
                            <div class="col-12">
                                <div class="text-muted small">Remarks</div>
                                <div class="fw-medium">{{ $student->remarks ?? '—' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- END CONTENT -->

            </div>

            <!-- Action Toolbar -->
            <div class="d-flex justify-content-end gap-2 my-3 no-print">
                <a href="{{ route('admin.student.edit', ['id' => $student->id]) }}"
                   class="btn btn-outline-secondary btn-sm">
                    <span class="material-icons-round align-middle" style="font-size:1rem;">edit</span>
                    Edit
                </a>
                <button type="button" class="btn btn-dark btn-sm" onclick="printStudentDetails()">
                    <span class="material-icons-round align-middle" style="font-size:1rem;">print</span>
                    Print
                </button>
            </div>

        </div>

    </div>

</div>

@push('scripts')
    <script>
        function printStudentDetails() {
            const printableEl = document.getElementById('studentDetailsPrintable');

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
                        <title>Student Details - {{ $student->name }}</title>
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
                            .text-decoration-none { text-decoration: none; }
                            .fw-bold, .fw-semibold, .fw-medium { font-weight: 600; }
                            .fs-4 { font-size: 1.25rem; }
                            .fs-5, .fs-6 { font-size: .9rem; }
                            .small { font-size: .8rem; }

                            .badge { display: inline-block; padding: 3px 10px; border-radius: 12px;
                                     background: #212529; color: #fff; font-size: 11px; margin-right: 4px; }
                            .bg-dark { background: #212529 !important; color: #fff; }

                            .card, .card-custom { border: 1px solid #ddd; border-radius: 8px;
                                                   margin-bottom: 16px; padding: 14px; }
                            .card-header { font-weight: 600; margin-bottom: 8px; padding-bottom: 8px;
                                           border-bottom: 1px solid #eee; }

                            .avatar-wrap { position: relative; display: inline-block; }
                            .avatar-wrap img { width: 80px; height: 80px; border-radius: 50%; object-fit: cover; }
                            .online-dot { display: none; }

                            .stat-box { margin-right: 16px; }
                            .stat-label { color: #777; text-transform: uppercase; font-size: 11px; }

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