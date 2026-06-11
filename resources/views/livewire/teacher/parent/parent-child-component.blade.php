<div class="mat-card" style="padding-top:28px">

    <!-- floating header -->
    <div class="mat-card-header header-pink-gradient">
        <h5 id="cardHeaderTitleParentOverview">Parent Overview</h5>
    </div>

    <div class="container-xl mt-4">

        @include('livewire.tenant.teacher.parent.parent-navbar', ['parent' => $parent])

        <!-- START CONTENT -->

        <!-- Children / Students -->
        <div class="section-card mt-4">
            <div class="section-head">
                <div class="section-icon"><span class="material-icons-round">family_restroom</span></div>
                <span class="section-title">Children ({{ $parent->students->count() }})</span>
            </div>

            @if($parent->students->isNotEmpty())
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th style="font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:var(--muted)">#</th>
                                <th style="font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:var(--muted)">Name</th>
                                <th style="font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:var(--muted)">Class</th>
                                <th style="font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:var(--muted)">Section</th>
                                <th style="font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:var(--muted)">Roll No</th>
                                <th style="font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:var(--muted)">Register No</th>
                                <th style="font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:var(--muted)">Gender</th>
                                <th style="font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:var(--muted)">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($parent->students as $index => $student)
                            <tr>
                                <td style="font-size:.875rem;vertical-align:middle">{{ $index + 1 }}</td>
                                <td style="vertical-align:middle">
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="{{ $student->photo ? asset($student->photo) : 'https://ui-avatars.com/api/?name=' . urlencode($student->name) . '&size=64&background=random' }}"
                                            style="width:34px;height:34px;border-radius:8px;object-fit:cover;" alt="{{ $student->name }}">
                                        <span style="font-weight:500;font-size:.875rem">{{ $student->name }}</span>
                                    </div>
                                </td>
                                <td style="font-size:.875rem;vertical-align:middle">{{ $student->class?->name ?? '—' }}</td>
                                <td style="font-size:.875rem;vertical-align:middle">{{ $student->section?->name ?? '—' }}</td>
                                <td style="font-size:.875rem;vertical-align:middle">{{ $student->roll_no ?? '—' }}</td>
                                <td style="font-size:.875rem;vertical-align:middle">{{ $student->register_no ?? '—' }}</td>
                                <td style="font-size:.875rem;vertical-align:middle">{{ ucfirst($student->gender ?? '—') }}</td>
                                <td style="vertical-align:middle">
                                    <a href="{{ route('teacher.student.overview', ['tenant' => tenant('id'), 'id' => $student->id]) }}"
                                        target="_blank" class="act-btn view" title="View Student">
                                        <span class="material-icons-round">visibility</span>
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
                    <p class="mb-0" style="font-size:.85rem">No children linked to this parent.</p>
                </div>
            @endif
        </div>

        <!-- Footer -->
        <div class="footer-actions">
            <button type="button" class="btn-outline" onclick="history.back()">
                <span class="material-icons-round" style="font-size:16px">arrow_back</span>
                Back
            </button>
            <a href="#" class="btn btn-dark">
                <span class="material-icons-round">print</span> Print
            </a>
        </div>
        <!-- END CONTENT -->

    </div>

</div>