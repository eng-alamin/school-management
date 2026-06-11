<div class="mat-card" style="padding-top:28px">

    <!-- floating header -->
    <div class="mat-card-header header-pink-gradient">
        <h5 id="cardHeaderTitleParentOverview">Parent Overview</h5>
    </div>

    <div class="container-xl mt-4">

        @include('livewire.admin.parent.parent-navbar', ['parent' => $parent])

        <!-- START CONTENT -->

        <!-- Guardian Details -->
        <div class="section-card">
            <div class="section-head">
                <div class="section-icon"><span class="material-icons-round">supervisor_account</span></div>
                <span class="section-title">Guardian Details</span>
            </div>
            <div class="fgrid fgrid-3">
                <div class="f span2"><div class="f-lbl">Name</div><div class="f-val">{{ $parent->name }}</div></div>
                <div class="f no-br"><div class="f-lbl">Relation</div><div class="f-val">{{ $parent->relation ?? '—' }}</div></div>
                <div class="f"><div class="f-lbl">Father Name</div><div class="f-val">{{ $parent->father_name ?? '—' }}</div></div>
                <div class="f span2 no-br"><div class="f-lbl">Mother Name</div><div class="f-val">{{ $parent->mother_name ?? '—' }}</div></div>
                <div class="f"><div class="f-lbl">Occupation</div><div class="f-val">{{ $parent->occupation ?? '—' }}</div></div>
                <div class="f"><div class="f-lbl">Income</div><div class="f-val">{{ $parent->income ?? '—' }}</div></div>
                <div class="f no-br"><div class="f-lbl">Education</div><div class="f-val">{{ $parent->education ?? '—' }}</div></div>
                <div class="f"><div class="f-lbl">Mobile No</div><div class="f-val">{{ $parent->mobile ?? '—' }}</div></div>
                <div class="f span2 no-br"><div class="f-lbl">Email</div><div class="f-val">{{ $parent->email ?? '—' }}</div></div>
                <div class="f span3"><div class="f-lbl">Address</div><div class="f-val">{{ $parent->address ?? '—' }}</div></div>
                <div class="photo-row">
                    <div class="photo-thumb">
                        @if($parent->photo)
                            <img src="{{ asset('storage/' . $parent->photo) }}" alt="{{ $parent->name }}" style="width:100%;height:100%;object-fit:cover;border-radius:8px">
                        @else
                            <span class="material-icons-round">person</span>
                            <span>No photo</span>
                        @endif
                    </div>
                    <div>
                        <div class="f-lbl" style="margin-bottom:4px">Guardian Picture</div>
                        <div style="color:var(--muted);font-size:.8rem;font-style:italic">
                            {{ $parent->photo ? 'Photo uploaded' : 'No photo uploaded' }}
                        </div>
                    </div>
                </div>
                <div class="f no-bb"><div class="f-lbl">Username</div><div class="f-val">{{ $parent->user->username ?? '—' }}</div></div>
                <div class="f no-bb"><div class="f-lbl">Password</div><div class="f-val dots">••••••••</div></div>
                <div class="f no-bb no-br"><div class="f-lbl">Email</div><div class="f-val">{{ $parent->user->email ?? '—' }}</div></div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer-actions">
            <a href="{{ route('admin.parent.edit', ['id' => $parent->id]) }}" class="btn btn-ghost">
                <span class="material-icons-round">edit</span> Edit
            </a>
            <a href="#" class="btn btn-dark">
                <span class="material-icons-round">print</span> Print
            </a>
        </div>
        <!-- END CONTENT -->

    </div>

</div>