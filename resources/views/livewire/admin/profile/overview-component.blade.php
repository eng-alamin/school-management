<div>
    <div class="card">

        <!-- floating header -->
        <div class="mat-card-header header-primary-gradient">
            <h5 id="cardHeaderTitleProfileOverview">Profile Overview</h5>
        </div>

        <div class="container-xl mt-4">

            @include('livewire.admin.profile.navbar', ['user' => $user])

            <!-- START CONTENT -->

            <!-- Profile Details -->
            <div class="card profile-card p-4 mb-4">

                <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                    <span class="fw-bold fs-5">Profile Details</span>
                    <a href="{{ route('admin.profile.setting', ['id' => $user->id]) }}" class="btn btn-primary">
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