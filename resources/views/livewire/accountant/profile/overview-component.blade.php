<div>
    <div class="card">

        <!-- floating header -->
        <div class="mat-card-header header-pink-gradient">
            <h5 id="cardHeaderTitleProfileOverview">Profile Overview</h5>
        </div>

        <div class="container-xl mt-4">

            @include('livewire.admin.profile.navbar', ['user' => $user])

            <!-- START CONTENT -->

            <!-- Profile Details -->
            <div class="card profile-card p-4 mb-4">

                <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                    <span class="fw-bold fs-5">Profile Details</span>
                    <a href="{{ route('admin.profile.setting', ['id' => $user->id]) }}" class="btn-outline bg-dark text-white">
                        Edit Profile
                    </a>
                </div>

                <!-- Name -->
                <div class="d-flex align-items-center py-3 border-bottom">
                    <div class="profile-detail-label text-muted">Name</div>
                    <div class="profile-detail-value fw-semibold">{{ $user->name }}</div>
                </div>

                <!-- Email -->
                <div class="d-flex align-items-center py-3 border-bottom">
                    <div class="profile-detail-label text-muted">Email</div>
                    <div class="profile-detail-value d-flex align-items-center gap-2">
                        <span>{{ $user->email ?? '—' }}</span>
                        @if($user->email)
                            @if($user->email_verified_at)
                                <span class="badge rounded-pill text-bg-success" style="font-size:.72rem;font-weight:500">Verified</span>
                            @else
                                <span class="badge rounded-pill" style="font-size:.72rem;font-weight:500;background:#f44d7b;color:#fff">Unverified</span>
                            @endif
                        @endif
                    </div>
                </div>

                <!-- Phone -->
                <div class="d-flex align-items-center py-3">
                    <div class="profile-detail-label text-muted">Phone</div>
                    <div class="profile-detail-value fw-semibold">{{ $user->phone ?? '—' }}</div>
                </div>

            </div>
            <!-- END CONTENT -->

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
        .card-header .card-title { font-size: .95rem; font-weight: 600; margin: 0; }

        .modal-header { border-bottom: 1px solid var(--border); }
        .modal-footer { border-top: 1px solid var(--border); }
        .modal-title { font-weight: 600; font-size: 1rem; }

        .form-label { font-size: .8rem; font-weight: 600; color: var(--text-muted); margin-bottom: 4px; }
        .form-control, .form-select {
            border-radius: 8px; border: 1px solid var(--border);
            font-size: .875rem; padding: .45rem .75rem;
            transition: border-color .2s, box-shadow .2s;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-light);
        }

        .btn-sm { font-size: .78rem; padding: .3rem .65rem; border-radius: 6px; }

        .profile-detail-label {
            width: 200px;
            min-width: 200px;
            font-size: .92rem;
        }
        .profile-detail-value {
            flex: 1;
            font-size: .92rem;
}

    </style>
@endpush