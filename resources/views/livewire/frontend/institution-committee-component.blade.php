<div>
    {{-- ===== HERO ===== --}}
    <section class="hero-section">
        <div class="container position-relative">
            <div class="row align-items-center g-4">
                <div class="col-lg-8">
                    <div class="d-flex align-items-center gap-3 mb-3 fade-up">
                        <div class="rounded-4 d-flex align-items-center justify-content-center overflow-hidden flex-shrink-0"
                             style="width:76px;height:76px;background: var(--bg-card); box-shadow: var(--shadow-lg); border: 1px solid var(--border);">
                            <i class="bi bi-person-vcard fs-1 text-primary"></i>
                        </div>
                        <div>
                            <span class="hero-badge">
                                <i class="bi bi-patch-check-fill"></i>
                                <span class="lang-bn">পরিচালনা কমিটি</span>
                                <span class="lang-en">Governing Body</span>
                            </span>
                            <h1 class="hero-title mb-0" style="font-size: 2.1rem;">
                                <span class="lang-bn">কমিটির সদস্যবৃন্দ</span>
                                <span class="lang-en">Committee Members</span>
                            </h1>
                        </div>
                    </div>
                    <p class="hero-subtitle fade-up delay-1 mb-0">
                        <span class="lang-bn">প্রতিষ্ঠানের পরিচালনা কমিটির বর্তমান ও সাবেক সদস্যদের তালিকা নিচে দেখুন।</span>
                        <span class="lang-en">View the current and former members of the institution's governing committee below.</span>
                    </p>
                </div>

                <div class="col-lg-4 text-center text-lg-end fade-up delay-2">
                    <a href="{{ url()->previous() }}" class="btn btn-outline-primary rounded-pill px-4 py-2">
                        <i class="bi bi-arrow-left me-1"></i>
                        <span class="lang-bn">ফিরে যান</span>
                        <span class="lang-en">Go Back</span>
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== STATS STRIP ===== --}}
    <section class="stats-section" id="stats-section">
        <div class="container position-relative">
            <div class="row g-4 justify-content-center">
                <div class="col-lg-3 col-md-6 col-6">
                    <div class="stat-card text-center fade-up">
                        <div class="stat-icon"><i class="bi bi-person-check"></i></div>
                        <div class="stat-number" data-count="{{ $counts['active'] }}">0</div>
                        <div class="stat-label">
                            <span class="lang-bn">বর্তমান সদস্য</span>
                            <span class="lang-en">Active Members</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-6">
                    <div class="stat-card text-center fade-up delay-1">
                        <div class="stat-icon"><i class="bi bi-person-dash"></i></div>
                        <div class="stat-number" data-count="{{ $counts['former'] }}">0</div>
                        <div class="stat-label">
                            <span class="lang-bn">সাবেক সদস্য</span>
                            <span class="lang-en">Former Members</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== MEMBERS SECTION ===== --}}
    <section class="py-5" style="background: var(--bg-alt);">
        <div class="container">

            {{-- Filter + Search Bar --}}
            <div class="feature-card p-3 p-md-4 mb-4">
                <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap">
                    <div class="btn-group" role="group">
                        <button type="button"
                                wire:click="setStatusFilter('active')"
                                class="btn btn-sm {{ $statusFilter === 'active' ? 'btn-primary' : 'btn-outline-primary' }} rounded-pill me-2">
                            <span class="lang-bn">বর্তমান</span>
                            <span class="lang-en">Active</span>
                            <span class="badge bg-light text-dark ms-1">{{ $counts['active'] }}</span>
                        </button>
                        <button type="button"
                                wire:click="setStatusFilter('former')"
                                class="btn btn-sm {{ $statusFilter === 'former' ? 'btn-primary' : 'btn-outline-primary' }} rounded-pill me-2">
                            <span class="lang-bn">সাবেক</span>
                            <span class="lang-en">Former</span>
                            <span class="badge bg-light text-dark ms-1">{{ $counts['former'] }}</span>
                        </button>
                        <button type="button"
                                wire:click="setStatusFilter('all')"
                                class="btn btn-sm {{ $statusFilter === 'all' ? 'btn-primary' : 'btn-outline-primary' }} rounded-pill">
                            <span class="lang-bn">সকল</span>
                            <span class="lang-en">All</span>
                        </button>
                    </div>

                    <div style="min-width: 240px;">
                        <input type="text"
                               wire:model.live.debounce.400ms="search"
                               class="form-control form-control-sm"
                               placeholder="নাম বা পদবি দিয়ে খুঁজুন...">
                    </div>
                </div>
            </div>

            {{-- Members Grid --}}
            <div class="row g-4">
                @forelse($members as $member)
                    <div class="col-lg-4 col-md-6" wire:key="member-{{ $member->id }}">
                        <div class="feature-card p-4 h-100 text-center">
                            <div class="rounded-circle overflow-hidden mx-auto mb-3 d-flex align-items-center justify-content-center"
                                 style="width:84px;height:84px;background: var(--bg-alt); border: 2px solid var(--border);">
                                @if($member->photo)
                                    <img src="{{ $member->photo_url }}" alt="{{ $member->name }}" class="w-100 h-100" style="object-fit: cover;">
                                @else
                                    <i class="bi bi-person-fill fs-1 text-primary"></i>
                                @endif
                            </div>

                            <h5 class="fw-bold mb-1">{{ $member->name }}</h5>
                            <span class="badge bg-primary rounded-pill mb-2">{{ $member->designation }}</span>

                            @if($member->status === 'former')
                                <div>
                                    <span class="badge bg-secondary rounded-pill mb-2">
                                        <span class="lang-bn">সাবেক সদস্য</span>
                                        <span class="lang-en">Former Member</span>
                                    </span>
                                </div>
                            @endif

                            @if($member->term_start_date)
                                <p class="text-muted small mb-3">
                                    <i class="bi bi-calendar-range me-1"></i>
                                    {{ $member->term_start_date->format('M Y') }}
                                    &ndash;
                                    {{ $member->term_end_date ? $member->term_end_date->format('M Y') : (($member->status === 'active') ? 'বর্তমান' : '—') }}
                                </p>
                            @endif

                            <button type="button"
                                    class="btn btn-sm btn-outline-primary rounded-pill w-100"
                                    wire:click="openMemberDetails({{ $member->id }})"
                                    data-bs-toggle="modal"
                                    data-bs-target="#memberDetailModal">
                                <i class="bi bi-eye me-1"></i>
                                <span class="lang-bn">বিস্তারিত দেখুন</span>
                                <span class="lang-en">View Details</span>
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="feature-card p-5 text-center text-muted">
                            <i class="bi bi-person-x fs-1 d-block mb-2"></i>
                            <span class="lang-bn">কোনো সদস্য পাওয়া যায়নি</span>
                            <span class="lang-en">No committee members found</span>
                        </div>
                    </div>
                @endforelse
            </div>

            @if($members->hasPages())
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mt-4">
                    <small class="text-muted">Showing {{ $members->firstItem() ?? 0 }}–{{ $members->lastItem() ?? 0 }} of {{ $members->total() }}</small>
                    {{ $members->links('vendor.pagination.custom') }}
                </div>
            @endif
        </div>
    </section>

    {{-- ===== MEMBER DETAIL MODAL ===== --}}
    <div class="modal fade" id="memberDetailModal" tabindex="-1" aria-labelledby="memberDetailModalLabel" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 1rem; border: none;">
                <div class="modal-header" style="border-bottom: 1px solid var(--border);">
                    <h5 class="modal-title fw-bold" id="memberDetailModalLabel">
                        <span class="lang-bn">সদস্যের বিস্তারিত তথ্য</span>
                        <span class="lang-en">Member Details</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" wire:click="closeMemberDetails"></button>
                </div>
                <div class="modal-body p-4">
                    @if($selectedMember)
                        <div class="text-center mb-4">
                            <div class="rounded-circle overflow-hidden mx-auto mb-3 d-flex align-items-center justify-content-center"
                                 style="width:96px;height:96px;background: var(--bg-alt); border: 2px solid var(--border);">
                                @if($selectedMember->photo)
                                    <img src="{{ $selectedMember->photo_url }}" alt="{{ $selectedMember->name }}" class="w-100 h-100" style="object-fit: cover;">
                                @else
                                    <i class="bi bi-person-fill fs-1 text-primary"></i>
                                @endif
                            </div>
                            <h5 class="fw-bold mb-1">{{ $selectedMember->name }}</h5>
                            <span class="badge bg-primary rounded-pill">{{ $selectedMember->designation }}</span>
                            @if($selectedMember->status === 'former')
                                <span class="badge bg-secondary rounded-pill">
                                    <span class="lang-bn">সাবেক সদস্য</span>
                                    <span class="lang-en">Former</span>
                                </span>
                            @endif
                        </div>

                        <div class="row g-3">
                            @if($selectedMember->phone)
                                <div class="col-md-6">
                                    <div class="contact-info-item">
                                        <div class="contact-info-icon"><i class="bi bi-telephone-fill"></i></div>
                                        <div>
                                            <h6><span class="lang-bn">ফোন</span><span class="lang-en">Phone</span></h6>
                                            <p>{{ $selectedMember->phone }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            @if($selectedMember->email)
                                <div class="col-md-6">
                                    <div class="contact-info-item">
                                        <div class="contact-info-icon"><i class="bi bi-envelope-fill"></i></div>
                                        <div>
                                            <h6><span class="lang-bn">ইমেইল</span><span class="lang-en">Email</span></h6>
                                            <p>{{ $selectedMember->email }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            @if($selectedMember->address)
                                <div class="col-md-12">
                                    <div class="contact-info-item">
                                        <div class="contact-info-icon"><i class="bi bi-geo-alt-fill"></i></div>
                                        <div>
                                            <h6><span class="lang-bn">ঠিকানা</span><span class="lang-en">Address</span></h6>
                                            <p>{{ $selectedMember->address }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            @if($selectedMember->term_start_date)
                                <div class="col-md-12">
                                    <div class="contact-info-item">
                                        <div class="contact-info-icon"><i class="bi bi-calendar-range"></i></div>
                                        <div>
                                            <h6><span class="lang-bn">মেয়াদকাল</span><span class="lang-en">Term</span></h6>
                                            <p>
                                                {{ $selectedMember->term_start_date->format('d M, Y') }}
                                                &ndash;
                                                {{ $selectedMember->term_end_date ? $selectedMember->term_end_date->format('d M, Y') : (($selectedMember->status === 'active') ? 'বর্তমান পর্যন্ত' : '—') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="text-center text-muted py-4">
                            <div class="spinner-border spinner-border-sm mb-2" role="status"></div>
                            <p class="small mb-0">
                                <span class="lang-bn">লোড হচ্ছে...</span>
                                <span class="lang-en">Loading...</span>
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>