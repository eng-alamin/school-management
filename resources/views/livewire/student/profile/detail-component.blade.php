<div>
    <div class="card">

        <div class="mat-card-header header-pink-gradient">
            <h5>My Profile</h5>
        </div>

    <div class="container-xl mt-4">

        @include('livewire.tenant.student.profile.profile-navbar', ['student' => $student])

        {{-- Academic Details --}}
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

        {{-- Student Details --}}
        <div class="section-card">
            <div class="section-head">
                <div class="section-icon"><span class="material-icons-round">person</span></div>
                <span class="section-title">Personal Details</span>
            </div>
            <div class="fgrid fgrid-3">
                <div class="f span2"><div class="f-lbl">Full Name</div><div class="f-val">{{ $student->name }}</div></div>
                <div class="f no-br"><div class="f-lbl">Gender</div><div class="f-val">{{ ucfirst($student->gender) }}</div></div>
                <div class="f"><div class="f-lbl">Date of Birth</div><div class="f-val">{{ $student->dob ? \Carbon\Carbon::parse($student->dob)->format('d M Y') : '—' }}</div></div>
                <div class="f"><div class="f-lbl">Blood Group</div><div class="f-val">{{ $student->blood_group ?? '—' }}</div></div>
                <div class="f no-br"><div class="f-lbl">Religion</div><div class="f-val">{{ ucfirst($student->religion ?? '—') }}</div></div>
                <div class="f"><div class="f-lbl">Mobile No</div><div class="f-val">{{ $student->mobile ?? '—' }}</div></div>
                <div class="f span2 no-br"><div class="f-lbl">Email</div><div class="f-val">{{ $student->email ?? '—' }}</div></div>
                <div class="f span3"><div class="f-lbl">Present Address</div><div class="f-val">{{ $student->present_address ?? '—' }}</div></div>
                <div class="f span3 no-bb"><div class="f-lbl">Permanent Address</div><div class="f-val">{{ $student->permanent_address ?? '—' }}</div></div>
            </div>
        </div>

        {{-- Previous School Details --}}
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

    </div>

</div>

@push('styles')
<style>
    :root {
        --primary: rgba(33, 37, 41);
        --primary-light: rgba(239,84,84,.12);
    }
    .card { border: 1px solid var(--border); border-radius: 12px; box-shadow: 0 1px 4px rgba(0,0,0,.04); }
    .card-header { background: #fff; border-bottom: 1px solid var(--border); border-radius: 12px 12px 0 0 !important; padding: 16px 20px; }
</style>
@endpush