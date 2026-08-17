<div>
    {{-- ===== HERO / SEARCH SECTION ===== --}}
    <section class="hero-section" style="padding: 110px 0 60px;" wire:ignore>
        <div class="container">
            <div class="text-center mb-4">
                <span class="hero-badge fade-up">
                    <i class="bi bi-search"></i>
                    <span class="lang-bn">প্রতিষ্ঠান খুঁজুন</span>
                    <span class="lang-en">Find Institution</span>
                </span>
                <h1 class="hero-title fade-up" style="font-size: 2.4rem;">
                    <span class="lang-bn">আপনার প্রতিষ্ঠান খুঁজে বের করুন</span>
                    <span class="lang-en">Search Your Institution</span>
                </h1>
                <p class="hero-subtitle fade-up delay-1 mx-auto" style="max-width: 620px;">
                    <span class="lang-bn">নাম, EIIN নম্বর অথবা এলাকা দিয়ে সারাদেশের নিবন্ধিত প্রতিষ্ঠানের তথ্য খুঁজুন।</span>
                    <span class="lang-en">Search registered institutions nationwide by name, EIIN number, or location.</span>
                </p>
            </div>

            {{-- Search Panel --}}
            <div class="row justify-content-center fade-up delay-2">
                <div class="col-lg-9">
                    <div class="glass-card p-4 p-md-4">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-6">
                                <label class="form-label">
                                    <span class="lang-bn">খুঁজুন</span>
                                    <span class="lang-en">Search</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent border-end-0">
                                        <i class="bi bi-search text-primary"></i>
                                    </span>
                                    <input
                                        type="text"
                                        class="form-control border-start-0 ps-0"
                                        placeholder="{{ 'Name, EIIN or District...' }}"
                                        wire:model.live.debounce.400ms="search"
                                    >
                                    <div wire:loading wire:target="search" class="input-group-text bg-transparent border-start-0">
                                        <span class="spinner-border spinner-border-sm text-primary"></span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">
                                    <span class="lang-bn">প্রকার</span>
                                    <span class="lang-en">Type</span>
                                </label>
                                <select class="selectpicker" wire:model.live="filterType">
                                    <option value="">
                                        {{ 'All Types' }}
                                    </option>
                                    @foreach($institutionTypes as $type)
                                        <option value="{{ $type }}">{{ ucwords(str_replace('_', ' ', $type)) }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">
                                    <span class="lang-bn">এলাকা</span>
                                    <span class="lang-en">District</span>
                                </label>
                                <select class="selectpicker" wire:model.live="filterDistrict">
                                    <option value="">
                                        {{ 'All Districts' }}
                                    </option>
                                    @foreach($institutionDistricts as $district)
                                        <option value="{{ $district }}">{{ $district }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        @if($search || $filterType || $filterDistrict)
                            <div class="mt-3 d-flex align-items-center gap-2">
                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" wire:click="resetFilters">
                                    <i class="bi bi-x-circle me-1"></i>
                                    <span class="lang-bn">ফিল্টার মুছুন</span>
                                    <span class="lang-en">Clear Filters</span>
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== STATS STRIP ===== --}}
    <section class="stats-section" style="padding: 45px 0;">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-2 col-md-4 col-6">
                    <div class="stat-card text-center">
                        <div class="stat-icon"><i class="bi bi-mortarboard"></i></div>
                        <div class="stat-number">{{ number_format($stats['high_schools']) }}</div>
                        <div class="stat-label">
                            <span class="lang-bn">উচ্চ বিদ্যালয়</span>
                            <span class="lang-en">High Schools</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-6">
                    <div class="stat-card text-center">
                        <div class="stat-icon"><i class="bi bi-bank"></i></div>
                        <div class="stat-number">{{ number_format($stats['colleges']) }}</div>
                        <div class="stat-label">
                            <span class="lang-bn">কলেজ</span>
                            <span class="lang-en">Colleges</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-6">
                    <div class="stat-card text-center">
                        <div class="stat-icon"><i class="bi bi-house-door"></i></div>
                        <div class="stat-number">{{ number_format($stats['school_college']) }}</div>
                        <div class="stat-label">
                            <span class="lang-bn">স্কুল ও কলেজ</span>
                            <span class="lang-en">School &amp; College</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-6">
                    <div class="stat-card text-center">
                        <div class="stat-icon"><i class="bi bi-people"></i></div>
                        <div class="stat-number">{{ number_format($stats['total_students']) }}</div>
                        <div class="stat-label">
                            <span class="lang-bn">শিক্ষার্থী</span>
                            <span class="lang-en">Students</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-8 col-12">
                    <div class="stat-card stat-highlight text-center text-md-start d-flex flex-column flex-md-row align-items-center justify-content-between h-100">
                        <div>
                            <div class="stat-icon"><i class="bi bi-people-fill"></i></div>
                            <div class="stat-number">{{ number_format($stats['total_teachers']) }}</div>
                            <div class="stat-label">
                                <span class="lang-bn">সারাদেশে শিক্ষক</span>
                                <span class="lang-en">Teachers Nationwide</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== RESULTS SECTION ===== --}}
    <section class="py-5" style="background: var(--bg-alt);">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
                <div>
                    <span class="sec-badge">
                        <span class="lang-bn">অনুসন্ধান ফলাফল</span>
                        <span class="lang-en">Search Results</span>
                    </span>
                    <h4 class="fw-bold mb-0 mt-1">
                        {{ $institutions->total() }}
                        <span class="lang-bn">টি প্রতিষ্ঠান পাওয়া গেছে</span>
                        <span class="lang-en">Institution(s) Found</span>
                    </h4>
                </div>
            </div>

            <div class="row g-4" wire:loading.class="opadistrict-50" wire:target="search,filterType,filterDistrict,gotoPage,previousPage,nextPage">
                @forelse($institutions as $inst)
                    <div class="col-md-6 col-lg-4">
                        <div class="feature-card p-4 h-100 d-flex flex-column">
                            <div class="d-flex align-items-start gap-3 mb-3">
                                <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0 overflow-hidden"
                                     style="width:60px;height:60px;background: rgba(25,135,84,0.09);">
                                    @if($inst->system_logo)
                                        <img src="{{ asset('storage/' . $inst->system_logo) }}" alt="{{ $inst->name }}" class="w-100 h-100" style="object-fit: cover;">
                                    @else
                                        <i class="bi bi-building fs-4 text-primary"></i>
                                    @endif
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1">{{ $inst->name }}</h6>
                                    @if($inst->type)
                                        <span class="module-badge" style="margin-bottom: 0; padding: 3px 12px; font-size: 0.75rem;">
                                            {{ ucwords(str_replace('_', ' ', $inst->type)) }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <ul class="list-unstyled small mb-4 flex-grow-1" style="color: var(--text-muted);">
                                @if($inst->eiin)
                                    <li class="mb-2 d-flex align-items-center gap-2">
                                        <i class="bi bi-hash text-primary"></i> EIIN: {{ $inst->eiin }}
                                    </li>
                                @endif
                                @if($inst->district)
                                    <li class="mb-2 d-flex align-items-center gap-2">
                                        <i class="bi bi-geo-alt text-primary"></i> {{ $inst->district }}
                                    </li>
                                @endif
                                @if($inst->phone)
                                    <li class="mb-2 d-flex align-items-center gap-2">
                                        <i class="bi bi-telephone text-primary"></i> {{ $inst->phone }}
                                    </li>
                                @endif
                                @if($inst->email)
                                    <li class="mb-2 d-flex align-items-center gap-2">
                                        <i class="bi bi-envelope text-primary"></i> {{ $inst->email }}
                                    </li>
                                @endif
                                @if($inst->academic_year)
                                    <li class="mb-2 d-flex align-items-center gap-2">
                                        <i class="bi bi-calendar3 text-primary"></i> {{ $inst->academic_year }}
                                    </li>
                                @endif
                            </ul>

                            <a href="{{ route('view.institution', $inst->id) }}" class="btn btn-primary rounded-pill w-100">
                                <span class="lang-bn">বিস্তারিত দেখুন</span>
                                <span class="lang-en">View Details</span>
                                <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="text-center py-5">
                            <div class="mx-auto mb-3 d-flex align-items-center justify-content-center rounded-circle"
                                 style="width:90px;height:90px;background: rgba(25,135,84,0.08);">
                                <i class="bi bi-search fs-1 text-primary"></i>
                            </div>
                            <h5 class="fw-bold mb-2">
                                <span class="lang-bn">কোনো প্রতিষ্ঠান পাওয়া যায়নি</span>
                                <span class="lang-en">No Institutions Found</span>
                            </h5>
                            <p class="text-muted mb-0">
                                <span class="lang-bn">অন্য কীওয়ার্ড অথবা ফিল্টার দিয়ে চেষ্টা করুন।</span>
                                <span class="lang-en">Try different keywords or filters.</span>
                            </p>
                        </div>
                    </div>
                @endforelse
            </div>

            @if($institutions->hasPages())
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 py-2 px-3">
                <small class="text-muted">Showing {{ $institutions->firstItem() ?? 0 }}–{{ $institutions->lastItem() ?? 0 }} of {{ $institutions->total() }}</small>
                {{ $institutions->links('vendor.pagination.custom') }}
            </div>
            @endif
        </div>
    </section>
</div>