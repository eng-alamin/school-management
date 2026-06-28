<div>

    <div class="card">

        <div class="mat-card-header header-pink-gradient no-print">
            <h5 id="emp-view-header-title">Employee Overview</h5>
        </div>

        <div class="container-xl mt-4">

            @include('livewire.admin.employee.employee-navbar', ['employee' => $employee])

            <!-- START CONTENT -->

            <!-- Job Details -->
            <div class="section-card">
                <div class="section-head">
                    <div class="section-icon"><span class="material-icons-round">work</span></div>
                    <span class="section-title">Job Details</span>
                </div>
                <div class="fgrid fgrid-3">
                    <div class="f"><div class="f-lbl">Employee ID</div><div class="f-val">{{ $employee->employee_id }}</div></div>
                    <div class="f"><div class="f-lbl">Role</div><div class="f-val">{{ ucfirst($employee->user->role ?? '—') }}</div></div>
                    <div class="f no-br"><div class="f-lbl">Joining Date</div><div class="f-val">{{ $employee->joining_date ? \Carbon\Carbon::parse($employee->joining_date)->format('d M Y') : '—' }}</div></div>
                    <div class="f no-bb"><div class="f-lbl">Designation</div><div class="f-val">{{ $employee->designation->name ?? '—' }}</div></div>
                    <div class="f no-bb"><div class="f-lbl">Department</div><div class="f-val">{{ $employee->department->name ?? '—' }}</div></div>
                    <div class="f no-bb no-br"><div class="f-lbl">Total Experience</div><div class="f-val">{{ $employee->total_experience ?? '—' }}</div></div>
                </div>
            </div>

            <!-- Employee Details -->
            <div class="section-card">
                <div class="section-head">
                    <div class="section-icon"><span class="material-icons-round">person</span></div>
                    <span class="section-title">Employee Details</span>
                </div>
                <div class="fgrid fgrid-3">
                    <div class="f span2"><div class="f-lbl">Full Name</div><div class="f-val">{{ $employee->name }}</div></div>
                    <div class="f no-br"><div class="f-lbl">Date of Birth</div><div class="f-val">{{ $employee->dob ? \Carbon\Carbon::parse($employee->dob)->format('d M Y') : '—' }}</div></div>
                    <div class="f"><div class="f-lbl">Religion</div><div class="f-val">{{ $employee->religion ? ucfirst($employee->religion) : '—' }}</div></div>
                    <div class="f"><div class="f-lbl">Mobile No</div><div class="f-val">{{ $employee->mobile ?? '—' }}</div></div>
                    <div class="f span2 no-br"><div class="f-lbl">Email</div><div class="f-val">{{ $employee->email ?? '—' }}</div></div>
                    <div class="f span3"><div class="f-lbl">Qualification</div><div class="f-val">{{ $employee->qualification ?? '—' }}</div></div>
                    <div class="f span3"><div class="f-lbl">Experience Detail</div><div class="f-val">{{ $employee->experience_detail ?? '—' }}</div></div>
                    <div class="f span3"><div class="f-lbl">Present Address</div><div class="f-val">{{ $employee->present_address ?? '—' }}</div></div>
                    <div class="f span3 no-bb"><div class="f-lbl">Permanent Address</div><div class="f-val">{{ $employee->permanent_address ?? '—' }}</div></div>
                    <div class="photo-row no-bb no-print">
                        <div class="photo-thumb">
                            @if($employee->photo)
                                <img src="{{ asset('storage/' . $employee->photo) }}" alt="{{ $employee->name }}" style="width:100%;height:100%;object-fit:cover;border-radius:8px">
                            @else
                                <span class="material-icons-round">person</span>
                                <span>No photo</span>
                            @endif
                        </div>
                        <div>
                            <div class="f-lbl" style="margin-bottom:4px">Profile Picture</div>
                            <div style="color:var(--muted);font-size:.8rem;font-style:italic">
                                {{ $employee->photo ? 'Photo uploaded' : 'No photo uploaded' }}
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
                    <div class="f no-bb"><div class="f-lbl">Username</div><div class="f-val">{{ $employee->user->username ?? '—' }}</div></div>
                    <div class="f no-bb"><div class="f-lbl">Password</div><div class="f-val dots">••••••••</div></div>
                    <div class="f no-bb no-br"><div class="f-lbl">Email</div><div class="f-val">{{ $employee->user->email ?? '—' }}</div></div>
                </div>
            </div>

            <!-- Bank Info -->
            <div class="section-card">
                <div class="section-head">
                    <div class="section-icon"><span class="material-icons-round">account_balance</span></div>
                    <span class="section-title">Bank Info</span>
                </div>
                <div class="fgrid fgrid-3">
                    <div class="f"><div class="f-lbl">Bank Name</div><div class="f-val">{{ $employee->bank_name ?? '—' }}</div></div>
                    <div class="f"><div class="f-lbl">Holder Name</div><div class="f-val">{{ $employee->holder_name ?? '—' }}</div></div>
                    <div class="f no-br"><div class="f-lbl">Bank Branch</div><div class="f-val">{{ $employee->bank_branch ?? '—' }}</div></div>
                    <div class="f no-bb"><div class="f-lbl">Bank Address</div><div class="f-val">{{ $employee->bank_address ?? '—' }}</div></div>
                    <div class="f no-bb"><div class="f-lbl">IFSC Code</div><div class="f-val">{{ $employee->ifsc_code ?? '—' }}</div></div>
                    <div class="f no-bb no-br"><div class="f-lbl">Account No</div><div class="f-val">{{ $employee->account_no ?? '—' }}</div></div>
                </div>
            </div>

            <!-- Footer -->
            <div class="footer-actions no-print">
                <a href="{{ route('admin.employee.edit', ['id' => $employee->id]) }}" class="btn btn-ghost">
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
        .employee-navbar,
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