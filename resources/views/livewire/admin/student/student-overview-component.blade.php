<div>

    <div class="card">

        <div class="mat-card-header header-pink-gradient no-print">
            <h5 id="cardHeaderTitleStudentOverview">Student Overview</h5>
        </div>

        <div class="container-xl mt-4">

            @include('livewire.admin.student.student-navbar', ['student' => $student])

            <!-- START CONTENT -->

            <!-- Academic Details -->
            <div class="section-card">
                <div class="section-head">
                    <div class="section-icon"><span class="material-icons-round">school</span></div>
                    <span class="section-title">Academic Details</span>
                </div>
                <div class="fgrid fgrid-3">
                    <div class="f"><div class="f-lbl">Academic Year</div><div class="f-val">{{ $student->session->name ?? '—' }}</div></div>
                    <div class="f"><div class="f-lbl">Register No</div><div class="f-val">{{ $student->register_no }}</div></div>
                    <div class="f no-br"><div class="f-lbl">Roll No</div><div class="f-val">{{ $student->roll_no ?? '—' }}</div></div>
                    <div class="f no-bb"><div class="f-lbl">Admission Date</div><div class="f-val">{{ \Carbon\Carbon::parse($student->admission_date)->format('d M Y') }}</div></div>
                    <div class="f no-bb"><div class="f-lbl">Class</div><div class="f-val">{{ $student->class->name ?? '—' }}</div></div>
                    <div class="f no-bb no-br"><div class="f-lbl">Section · Category</div><div class="f-val">{{ $student->section->name ?? '—' }} &nbsp;·&nbsp; {{ $student->category->name ?? '—' }}</div></div>
                </div>
            </div>

            <!-- Student Details -->
            <div class="section-card">
                <div class="section-head">
                    <div class="section-icon"><span class="material-icons-round">person</span></div>
                    <span class="section-title">Student Details</span>
                </div>
                <div class="fgrid fgrid-3">
                    <div class="f span2"><div class="f-lbl">Full Name</div><div class="f-val">{{ $student->name }}</div></div>
                    <div class="f no-br"><div class="f-lbl">Gender</div><div class="f-val">{{ ucfirst($student->gender) }}</div></div>
                    <div class="f"><div class="f-lbl">Date of Birth</div><div class="f-val">{{ $student->dob ? \Carbon\Carbon::parse($student->dob)->format('d M Y') : '—' }}</div></div>
                    <div class="f"><div class="f-lbl">Blood Group</div><div class="f-val">{{ $student->blood_group ?? '—' }}</div></div>
                    <div class="f no-br"><div class="f-lbl">Religion</div><div class="f-val">{{ ucfirst($student->religion) ?? '—' }}</div></div>
                    <div class="f"><div class="f-lbl">Mobile No</div><div class="f-val">{{ $student->mobile ?? '—' }}</div></div>
                    <div class="f span2 no-br"><div class="f-lbl">Email</div><div class="f-val">{{ $student->email ?? '—' }}</div></div>
                    <div class="f span3"><div class="f-lbl">Present Address</div><div class="f-val">{{ $student->present_address ?? '—' }}</div></div>
                    <div class="f span3 no-bb"><div class="f-lbl">Permanent Address</div><div class="f-val">{{ $student->permanent_address ?? '—' }}</div></div>
                    <div class="photo-row no-bb no-print">
                        <div class="photo-thumb">
                            @if($student->photo)
                                <img src="{{ asset('storage/' . $student->photo) }}" alt="{{ $student->name }}" style="width:100%;height:100%;object-fit:cover;border-radius:8px">
                            @else
                                <span class="material-icons-round">person</span>
                                <span>No photo</span>
                            @endif
                        </div>
                        <div>
                            <div class="f-lbl" style="margin-bottom:4px">Profile Picture</div>
                            <div style="color:var(--muted);font-size:.8rem;font-style:italic">
                                {{ $student->photo ? 'Photo uploaded' : 'No photo uploaded' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Login Details -->
            <div class="section-card no-print">
                <div class="section-head">
                    <div class="section-icon"><span class="material-icons-round">lock</span></div>
                    <span class="section-title">Login Details</span>
                </div>
                <div class="fgrid fgrid-3">
                    <div class="f no-bb"><div class="f-lbl">Username</div><div class="f-val">{{ $student->user->username ?? '—' }}</div></div>
                    <div class="f no-bb"><div class="f-lbl">Password</div><div class="f-val dots">••••••••</div></div>
                    <div class="f no-bb no-br"><div class="f-lbl">Email</div><div class="f-val">{{ $student->user->email ?? '—' }}</div></div>
                </div>
            </div>

            <!-- Guardian Details -->
            @php $guardian = $student->guardians->first(); @endphp
            <div class="section-card">
                <div class="section-head">
                    <div class="section-icon"><span class="material-icons-round">supervisor_account</span></div>
                    <span class="section-title">Guardian Details</span>
                </div>
                @if($guardian)
                <div class="fgrid fgrid-3">
                    <div class="f span2"><div class="f-lbl">Name</div><div class="f-val">{{ $guardian->name }}</div></div>
                    <div class="f no-br"><div class="f-lbl">Relation</div><div class="f-val">{{ $guardian->relation ?? '—' }}</div></div>
                    <div class="f"><div class="f-lbl">Father Name</div><div class="f-val">{{ $guardian->father_name ?? '—' }}</div></div>
                    <div class="f span2 no-br"><div class="f-lbl">Mother Name</div><div class="f-val">{{ $guardian->mother_name ?? '—' }}</div></div>
                    <div class="f"><div class="f-lbl">Occupation</div><div class="f-val">{{ $guardian->occupation ?? '—' }}</div></div>
                    <div class="f"><div class="f-lbl">Income</div><div class="f-val">{{ $guardian->income ?? '—' }}</div></div>
                    <div class="f no-br"><div class="f-lbl">Education</div><div class="f-val">{{ $guardian->education ?? '—' }}</div></div>
                    <div class="f"><div class="f-lbl">Mobile No</div><div class="f-val">{{ $guardian->mobile ?? '—' }}</div></div>
                    <div class="f span2 no-br"><div class="f-lbl">Email</div><div class="f-val">{{ $guardian->email ?? '—' }}</div></div>
                    <div class="f span3"><div class="f-lbl">Address</div><div class="f-val">{{ $guardian->address ?? '—' }}</div></div>
                    <div class="photo-row no-print">
                        <div class="photo-thumb">
                            @if($guardian->photo)
                                <img src="{{ asset('storage/' . $guardian->photo) }}" alt="{{ $guardian->name }}" style="width:100%;height:100%;object-fit:cover;border-radius:8px">
                            @else
                                <span class="material-icons-round">person</span>
                                <span>No photo</span>
                            @endif
                        </div>
                        <div>
                            <div class="f-lbl" style="margin-bottom:4px">Guardian Picture</div>
                            <div style="color:var(--muted);font-size:.8rem;font-style:italic">
                                {{ $guardian->photo ? 'Photo uploaded' : 'No photo uploaded' }}
                            </div>
                        </div>
                    </div>
                    <div class="f no-bb no-print"><div class="f-lbl">Username</div><div class="f-val">{{ $guardian->user->username ?? '—' }}</div></div>
                    <div class="f no-bb no-print"><div class="f-lbl">Password</div><div class="f-val dots">••••••••</div></div>
                    <div class="f no-bb no-print no-br"><div class="f-lbl">Email</div><div class="f-val">{{ $guardian->user->email ?? '—' }}</div></div>
                </div>
                @else
                <div class="p-3 text-muted">No guardian found.</div>
                @endif
            </div>

            <!-- Previous School Details -->
            <div class="section-card">
                <div class="section-head">
                    <div class="section-icon"><span class="material-icons-round">history_edu</span></div>
                    <span class="section-title">Previous School Details</span>
                </div>
                <div class="fgrid fgrid-2">
                    <div class="f"><div class="f-lbl">School Name</div><div class="f-val">{{ $student->previous_school ?? '—' }}</div></div>
                    <div class="f no-br"><div class="f-lbl">Qualification</div><div class="f-val">{{ $student->qualification ?? '—' }}</div></div>
                    <div class="f span3 no-bb no-br"><div class="f-lbl">Remarks</div><div class="f-val">{{ $student->remarks ?? '—' }}</div></div>
                </div>
            </div>

            <!-- Footer -->
            <div class="footer-actions no-print">
                <a href="{{ route('admin.student.edit', ['id' => $student->id]) }}" class="btn btn-ghost">
                    <span class="material-icons-round">edit</span> Edit
                </a>
                <button type="button" class="btn btn-dark" onclick="window.print()">
                    <span class="material-icons-round">print</span> Print
                </button>
            </div>
            <!-- END CONTENT -->

        </div>

    </div>

</div>


@push('styles')
<style>
    @media print {
        /* sidebar, navbar, header সব hide */
        .no-print, .sidenav, .navbar,
        .student-navbar,
        nav, header, aside, footer { display: none !important; }

        /* card এর padding/shadow সরাও */
        .card {background: none !important; border: none !important; box-shadow: none !important; padding: 0 !important; }

        /* container full width */
        .container-xl { max-width: 100% !important; padding: 0 !important; }

        /* section card border রাখো কিন্তু shadow সরাও */
        .section-card { box-shadow: none !important; break-inside: avoid; }

        /* page break যাতে section মাঝখানে না ভাঙে */
        .section-card { page-break-inside: avoid; }

        body { background: white !important; }

        .profile-card > .d-flex {
            display: flex !important;
        }

        .profile-card .flex-grow-1 {
            width: 50% !important;
        }
    }
</style>
@endpush