<div>
    {{-- ===== INSTITUTION HERO ===== --}}
    <section class="hero-section">
        <div class="container position-relative">
            <div class="row align-items-center g-4">
                <div class="col-lg-8">
                    <div class="d-flex align-items-center gap-3 mb-3 fade-up">
                        <div class="rounded-4 d-flex align-items-center justify-content-center overflow-hidden flex-shrink-0"
                             style="width:96px;height:96px;background: var(--bg-card); box-shadow: var(--shadow-lg); border: 1px solid var(--border);">
                            @if($institution->system_logo)
                                <img src="{{ asset('storage/' . $institution->system_logo) }}" alt="{{ $institution->name }}" class="w-100 h-100" style="object-fit: cover;">
                            @else
                                <i class="bi bi-building fs-1 text-primary"></i>
                            @endif
                        </div>
                        <div>
                            @if($institution->type)
                                <span class="hero-badge">
                                    <i class="bi bi-patch-check-fill"></i>
                                    {{ ucwords(str_replace('_', ' ', $institution->type)) }}
                                </span>
                            @endif
                            <h1 class="hero-title mb-0" style="font-size: 2.3rem;">{{ $institution->name }}</h1>
                        </div>
                    </div>

                    <p class="hero-subtitle fade-up delay-1 mb-0">
                        <span class="lang-bn">এই প্রতিষ্ঠানের সম্পূর্ণ প্রোফাইল, শিক্ষক তালিকা এবং র‍্যাংকিং তথ্য নিচে দেখুন।</span>
                        <span class="lang-en">View the complete profile, staff directory, and ranking of this institution below.</span>
                    </p>

                    <div class="d-flex flex-wrap gap-2 mt-4 fade-up delay-2">
                        @if($institution->city)
                            <span class="integration-pill"><i class="bi bi-geo-alt-fill"></i> {{ $institution->city }}</span>
                        @endif
                        @if($institution->eiin)
                            <span class="integration-pill"><i class="bi bi-hash"></i> EIIN: {{ $institution->eiin }}</span>
                        @endif
                        @if($institution->academic_year)
                            <span class="integration-pill"><i class="bi bi-calendar3"></i> {{ $institution->academic_year }}</span>
                        @endif
                        <span class="integration-pill">
                            <i class="bi bi-{{ $institution->status ? 'check-circle-fill' : 'x-circle-fill' }}"></i>
                            <span class="lang-bn">{{ $institution->status ? 'সক্রিয়' : 'নিষ্ক্রিয়' }}</span>
                            <span class="lang-en">{{ $institution->status ? 'Active' : 'Inactive' }}</span>
                        </span>
                        @if($ranking['position'])
                            <span class="integration-pill" style="border-color: var(--accent2); color:#a3720a;">
                                <i class="bi bi-trophy-fill" style="color:#ffc107;"></i>
                                <span class="lang-bn">র‍্যাংক #{{ $ranking['position'] }} ({{ $ranking['total'] }} টি প্রতিষ্ঠানের মধ্যে)</span>
                                <span class="lang-en">Rank #{{ $ranking['position'] }} of {{ $ranking['total'] }}</span>
                            </span>
                        @endif
                        <span class="integration-pill" style="{{ $admission['is_open'] ? 'border-color:#198754;color:#198754;' : 'border-color:#dc3545;color:#dc3545;' }}">
                            <i class="bi bi-{{ $admission['is_open'] ? 'door-open-fill' : 'door-closed-fill' }}"></i>
                            <span class="lang-bn">ভর্তি {{ $admission['is_open'] ? 'চলমান' : 'বন্ধ' }}</span>
                            <span class="lang-en">Admission {{ $admission['is_open'] ? 'Open' : 'Closed' }}</span>
                        </span>
                    </div>
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
            <div class="row g-4">
                <div class="col-lg-3 col-md-6 col-6">
                    <div class="stat-card text-center fade-up">
                        <div class="stat-icon"><i class="bi bi-people"></i></div>
                        <div class="stat-number" data-count="{{ $stats['students'] }}">0</div>
                        <div class="stat-label">
                            <span class="lang-bn">শিক্ষার্থী</span>
                            <span class="lang-en">Students</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-6">
                    <div class="stat-card text-center fade-up delay-1">
                        <div class="stat-icon"><i class="bi bi-person-badge"></i></div>
                        <div class="stat-number" data-count="{{ $stats['teachers'] }}">0</div>
                        <div class="stat-label">
                            <span class="lang-bn">শিক্ষক</span>
                            <span class="lang-en">Teachers</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-6">
                    <div class="stat-card text-center fade-up delay-2">
                        <div class="stat-icon"><i class="bi bi-mortarboard"></i></div>
                        <div class="stat-number" data-count="{{ $stats['classes'] }}">0</div>
                        <div class="stat-label">
                            <span class="lang-bn">শ্রেণি</span>
                            <span class="lang-en">Classes</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-6">
                    <div class="stat-card text-center fade-up delay-3">
                        <div class="stat-icon"><i class="bi bi-grid-3x3-gap"></i></div>
                        <div class="stat-number" data-count="{{ $stats['sections'] }}">0</div>
                        <div class="stat-label">
                            <span class="lang-bn">শাখা</span>
                            <span class="lang-en">Sections</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== DETAILS SECTION ===== --}}
    <section class="py-5" style="background: var(--bg-alt);">
        <div class="container">
            <div class="row g-4">

                {{-- LEFT: Basic + Contact + Facilities + Employees --}}
                <div class="col-lg-8">

                    {{-- ===== PRINCIPAL & VICE PRINCIPAL MESSAGE ===== --}}
                    @if($principals->isNotEmpty())
                        <div class="row g-4 mb-4">
                            @foreach($principals as $principal)
                                <div class="{{ $principals->count() === 1 ? 'col-lg-12' : 'col-lg-6' }}" wire:key="principal-{{ $principal->id }}">
                                    <div class="feature-card p-4 p-md-5 h-100">
                                        <div class="d-flex align-items-start gap-3 mb-3">
                                            <div class="rounded-circle overflow-hidden flex-shrink-0 d-flex align-items-center justify-content-center"
                                                style="width:72px;height:72px;background: var(--bg-alt); border: 2px solid var(--border);">
                                                @if($principal->photo)
                                                    <img src="{{ asset('storage/' . $principal->photo) }}" alt="{{ $principal->name }}" class="w-100 h-100" style="object-fit: cover;">
                                                @else
                                                    <i class="bi bi-person-fill fs-2 text-primary"></i>
                                                @endif
                                            </div>
                                            <div>
                                                <h5 class="fw-bold mb-0">{{ $principal->name }}</h5>
                                                <span class="badge bg-primary rounded-pill mb-1">
                                                    <span class="lang-bn">{{ $principal->role_label_bn }}</span>
                                                    <span class="lang-en">{{ $principal->role_label_en }}</span>
                                                </span>
                                            </div>
                                        </div>
                                        <p class="text-muted mb-0" style="line-height: 1.8;">
                                            <i class="bi bi-quote me-1"></i>{{ $principal->comments }}
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    {{-- Basic Info --}}
                    <div class="feature-card p-4 p-md-5 mb-4">
                        <div class="d-flex align-items-center gap-3 mb-4 pb-3" style="border-bottom: 1px solid var(--border);">
                            <div class="feature-icon" style="width:52px;height:52px;margin-bottom:0;font-size:1.4rem;">
                                <i class="bi bi-info-circle"></i>
                            </div>
                            <h5 class="fw-bold mb-0">
                                <span class="lang-bn">প্রাতিষ্ঠানিক তথ্য</span>
                                <span class="lang-en">Institution Information</span>
                            </h5>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="contact-info-item">
                                    <div class="contact-info-icon"><i class="bi bi-building"></i></div>
                                    <div>
                                        <h6><span class="lang-bn">নাম</span><span class="lang-en">Name</span></h6>
                                        <p>{{ $institution->name }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="contact-info-item">
                                    <div class="contact-info-icon"><i class="bi bi-hash"></i></div>
                                    <div>
                                        <h6>EIIN</h6>
                                        <p>{{ $institution->eiin ?? '—' }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="contact-info-item">
                                    <div class="contact-info-icon"><i class="bi bi-tags"></i></div>
                                    <div>
                                        <h6><span class="lang-bn">ধরন</span><span class="lang-en">Type</span></h6>
                                        <p>{{ $institution->type ? ucwords(str_replace('_', ' ', $institution->type)) : '—' }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="contact-info-item">
                                    <div class="contact-info-icon"><i class="bi bi-calendar3"></i></div>
                                    <div>
                                        <h6><span class="lang-bn">শিক্ষাবর্ষ</span><span class="lang-en">Academic Year</span></h6>
                                        <p>{{ $institution->currentSession->name ?? '—' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Contact Info --}}
                    <div class="feature-card p-4 p-md-5 mb-4">
                        <div class="d-flex align-items-center gap-3 mb-4 pb-3" style="border-bottom: 1px solid var(--border);">
                            <div class="feature-icon" style="width:52px;height:52px;margin-bottom:0;font-size:1.4rem;">
                                <i class="bi bi-telephone"></i>
                            </div>
                            <h5 class="fw-bold mb-0">
                                <span class="lang-bn">যোগাযোগের তথ্য</span>
                                <span class="lang-en">Contact Information</span>
                            </h5>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="contact-info-item">
                                    <div class="contact-info-icon"><i class="bi bi-envelope-fill"></i></div>
                                    <div>
                                        <h6><span class="lang-bn">ইমেইল</span><span class="lang-en">Email</span></h6>
                                        <p>{{ $institution->email ?? '—' }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="contact-info-item">
                                    <div class="contact-info-icon"><i class="bi bi-telephone-fill"></i></div>
                                    <div>
                                        <h6><span class="lang-bn">ফোন</span><span class="lang-en">Phone</span></h6>
                                        <p>{{ $institution->phone ?? '—' }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="contact-info-item">
                                    <div class="contact-info-icon"><i class="bi bi-pin-map-fill"></i></div>
                                    <div>
                                        <h6><span class="lang-bn">শহর</span><span class="lang-en">City</span></h6>
                                        <p>{{ $institution->city ?? '—' }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="contact-info-item">
                                    <div class="contact-info-icon"><i class="bi bi-geo-alt-fill"></i></div>
                                    <div>
                                        <h6><span class="lang-bn">ঠিকানা</span><span class="lang-en">Address</span></h6>
                                        <p>{{ $institution->address ?? '—' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ===== FACILITIES / INFRASTRUCTURE ===== --}}
                    <div class="feature-card p-4 p-md-5 mb-4">
                        <div class="d-flex align-items-center gap-3 mb-4 pb-3" style="border-bottom: 1px solid var(--border);">
                            <div class="feature-icon" style="width:52px;height:52px;margin-bottom:0;font-size:1.4rem;">
                                <i class="bi bi-house-gear"></i>
                            </div>
                            <h5 class="fw-bold mb-0">
                                <span class="lang-bn">সুযোগ-সুবিধা</span>
                                <span class="lang-en">Facilities</span>
                            </h5>
                        </div>

                        <div class="row g-3">
                            @foreach($facilities as $facility)
                                <div class="col-md-4 col-12" wire:key="facility-{{ $facility['key'] }}">
                                    <div class="d-flex align-items-center gap-2 p-3 rounded-3"
                                         style="background: {{ $facility['available'] ? 'rgba(25,135,84,0.08)' : 'var(--bg-alt)' }}; border: 1px solid var(--border);">
                                        <i class="bi {{ $facility['icon'] }} fs-5" style="color: {{ $facility['available'] ? '#198754' : '#adb5bd' }};"></i>
                                        <div class="flex-grow-1">
                                            <div class="small fw-semibold" style="{{ $facility['available'] ? '' : 'color:#adb5bd;' }}">
                                                <span class="lang-bn">{{ $facility['label_bn'] }}</span>
                                                <span class="lang-en">{{ $facility['label_en'] }}</span>
                                            </div>
                                        </div>
                                        <i class="bi bi-{{ $facility['available'] ? 'check-circle-fill' : 'dash-circle' }}"
                                           style="color: {{ $facility['available'] ? '#198754' : '#adb5bd' }};"></i>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- ===== TEACHER DIRECTORY (Public Safe) ===== --}}
                    <div class="feature-card p-4 p-md-5">
                        <div class="d-flex align-items-center justify-content-between gap-3 mb-4 pb-3 flex-wrap" style="border-bottom: 1px solid var(--border);">
                            <div class="d-flex align-items-center gap-3">
                                <div class="feature-icon" style="width:52px;height:52px;margin-bottom:0;font-size:1.4rem;">
                                    <i class="bi bi-person-badge"></i>
                                </div>
                                <h5 class="fw-bold mb-0">
                                    <span class="lang-bn">শিক্ষক তালিকা</span>
                                    <span class="lang-en">Teacher Directory</span>
                                </h5>
                            </div>

                            <div style="min-width: 220px;">
                                <input type="text"
                                       wire:model.live.debounce.400ms="employeeSearch"
                                       class="form-control form-control-sm"
                                       placeholder="নাম দিয়ে খুঁজুন...">
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr>
                                        <th style="width:60px;">
                                            <span class="lang-bn">ছবি</span>
                                            <span class="lang-en">Photo</span>
                                        </th>
                                        <th>
                                            <span class="lang-bn">নাম</span>
                                            <span class="lang-en">Name</span>
                                        </th>
                                        <th>
                                            <span class="lang-bn">পদবি</span>
                                            <span class="lang-en">Designation</span>
                                        </th>
                                        <th style="width:70px;" class="text-center">
                                            <span class="lang-bn">বিস্তারিত</span>
                                            <span class="lang-en">Details</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($employees as $employee)
                                        <tr wire:key="employee-{{ $employee->id }}">
                                            <td>
                                                <div class="rounded-circle overflow-hidden flex-shrink-0 d-flex align-items-center justify-content-center"
                                                     style="width:42px;height:42px;background: var(--bg-alt); border: 1px solid var(--border);">
                                                    @if($employee->avatar)
                                                        <img src="{{ asset('storage/' . $employee->avatar) }}" alt="{{ $employee->name }}" class="w-100 h-100" style="object-fit: cover;">
                                                    @else
                                                        <i class="bi bi-person-fill text-primary"></i>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="fw-semibold">{{ $employee->name }}</td>
                                            <td>
                                                <span class="badge bg-secondary rounded-pill">
                                                    <span class="lang-bn">শিক্ষক</span>
                                                    <span class="lang-en">Teacher</span>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <button type="button"
                                                        class="btn btn-sm btn-outline-primary rounded-circle"
                                                        style="width:34px;height:34px;padding:0;"
                                                        wire:click="openEmployeeDetails({{ $employee->id }})"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#teacherDetailModal"
                                                        title="বিস্তারিত দেখুন">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">
                                                <span class="lang-bn">কোনো তথ্য পাওয়া যায়নি</span>
                                                <span class="lang-en">No records found</span>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            {{ $employees->links() }}
                        </div>
                    </div>
                </div>

                {{-- RIGHT: Sidebar --}}
                <div class="col-lg-4">
                    {{-- Ranking Card --}}
                    @if($ranking['position'])
                        <div class="contact-card mb-4">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <div class="feature-icon" style="width:44px;height:44px;margin-bottom:0;font-size:1.2rem; color:#a3720a; background: rgba(255,193,7,0.15);">
                                    <i class="bi bi-trophy-fill"></i>
                                </div>
                                <h5 class="fw-bold mb-0">
                                    <span class="lang-bn">প্রাতিষ্ঠানিক র‍্যাংকিং</span>
                                    <span class="lang-en">Institution Ranking</span>
                                </h5>
                            </div>
                            <div class="text-center py-2">
                                <div style="font-size: 2.4rem; font-weight: 800; color: var(--primary); font-family: 'Sora', sans-serif;">
                                    #{{ $ranking['position'] }}
                                </div>
                                <p class="text-muted small mb-0">
                                    <span class="lang-bn">{{ $ranking['total'] }} টি সক্রিয় প্রতিষ্ঠানের মধ্যে (শিক্ষার্থী সংখ্যা অনুযায়ী)</span>
                                    <span class="lang-en">out of {{ $ranking['total'] }} active institutions (by student count)</span>
                                </p>
                            </div>
                        </div>
                    @endif

                    {{-- ===== ADMISSION INFO CARD ===== --}}
                    <div class="contact-card mb-4">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <div class="feature-icon" style="width:44px;height:44px;margin-bottom:0;font-size:1.2rem; color:{{ $admission['is_open'] ? '#198754' : '#dc3545' }}; background: {{ $admission['is_open'] ? 'rgba(25,135,84,0.12)' : 'rgba(220,53,69,0.12)' }};">
                                <i class="bi bi-mortarboard-fill"></i>
                            </div>
                            <h5 class="fw-bold mb-0">
                                <span class="lang-bn">ভর্তি তথ্য</span>
                                <span class="lang-en">Admission Info</span>
                            </h5>
                        </div>

                        <div class="d-flex justify-content-between align-items-center py-2" style="border-bottom: 1px solid var(--border);">
                            <span class="text-muted small">
                                <span class="lang-bn">অবস্থা</span>
                                <span class="lang-en">Status</span>
                            </span>
                            <span class="badge {{ $admission['is_open'] ? 'bg-success' : 'bg-danger' }} rounded-pill">
                                <span class="lang-bn">{{ $admission['is_open'] ? 'চলমান' : 'বন্ধ' }}</span>
                                <span class="lang-en">{{ $admission['is_open'] ? 'Open' : 'Closed' }}</span>
                            </span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center py-2" style="border-bottom: 1px solid var(--border);">
                            <span class="text-muted small">
                                <span class="lang-bn">ভর্তি ফি (প্রায়)</span>
                                <span class="lang-en">Admission Fee (approx.)</span>
                            </span>
                            <span class="fw-semibold small">৳{{ number_format($admission['fee_min']) }} – ৳{{ number_format($admission['fee_max']) }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center py-2 mb-2" style="border-bottom: 1px solid var(--border);">
                            <span class="text-muted small">
                                <span class="lang-bn">আবেদন পদ্ধতি</span>
                                <span class="lang-en">Apply Mode</span>
                            </span>
                            <span class="fw-semibold small text-capitalize">{{ $admission['apply_mode'] }}</span>
                        </div>

                        <h6 class="small fw-bold mb-2">
                            <span class="lang-bn">প্রয়োজনীয় কাগজপত্র</span>
                            <span class="lang-en">Required Documents</span>
                        </h6>
                        <ul class="small text-muted ps-3 mb-0">
                            @foreach($admission['documents'] as $document)
                                <li>
                                    <span class="lang-bn">{{ $document['bn'] }}</span>
                                    <span class="lang-en">{{ $document['en'] }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    {{-- ===== NOTICE BOARD CARD ===== --}}
                    <div class="contact-card mb-4">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <div class="feature-icon" style="width:44px;height:44px;margin-bottom:0;font-size:1.2rem;">
                                <i class="bi bi-megaphone-fill"></i>
                            </div>
                            <h5 class="fw-bold mb-0">
                                <span class="lang-bn">নোটিশ বোর্ড</span>
                                <span class="lang-en">Notice Board</span>
                            </h5>
                        </div>

                        @forelse($notices as $notice)
                            @php
                                $typeBadge = [
                                    'admission' => 'bg-primary',
                                    'event'     => 'bg-info text-dark',
                                    'holiday'   => 'bg-warning text-dark',
                                ][$notice['type']] ?? 'bg-secondary';
                            @endphp
                            <div class="d-flex align-items-start gap-2 py-2" style="border-bottom: 1px solid var(--border);">
                                <span class="badge {{ $typeBadge }} rounded-pill mt-1" style="font-size: 0.65rem;">
                                    {{ ucfirst($notice['type']) }}
                                </span>
                                <div class="flex-grow-1">
                                    <p class="small mb-1">{{ $notice['title'] }}</p>
                                    <span class="text-muted" style="font-size: 0.72rem;">{{ $notice['date'] }}</span>
                                </div>
                            </div>
                        @empty
                            <p class="text-muted small mb-0">
                                <span class="lang-bn">কোনো নোটিশ নেই</span>
                                <span class="lang-en">No notices available</span>
                            </p>
                        @endforelse
                    </div>

                    <div class="contact-card mb-4" style="position: sticky; top: 100px;">
                        <div class="d-flex align-items-center gap-2 mb-4">
                            <div class="feature-icon" style="width:44px;height:44px;margin-bottom:0;font-size:1.2rem;">
                                <i class="bi bi-clipboard-data"></i>
                            </div>
                            <h5 class="fw-bold mb-0">
                                <span class="lang-bn">দ্রুত সারাংশ</span>
                                <span class="lang-en">Quick Summary</span>
                            </h5>
                        </div>

                        <div class="d-flex justify-content-between align-items-center py-2" style="border-bottom: 1px solid var(--border);">
                            <span class="text-muted small">
                                <span class="lang-bn">নিবন্ধন প্রিফিক্স</span>
                                <span class="lang-en">Registration Prefix</span>
                            </span>
                            <span class="fw-semibold small">{{ $institution->institution_code_prefix ?? '—' }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center py-2" style="border-bottom: 1px solid var(--border);">
                            <span class="text-muted small">
                                <span class="lang-bn">শুরু নম্বর</span>
                                <span class="lang-en">Register Start</span>
                            </span>
                            <span class="fw-semibold small">{{ $institution->register_start_from }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center py-2" style="border-bottom: 1px solid var(--border);">
                            <span class="text-muted small">
                                <span class="lang-bn">স্বয়ংক্রিয় স্টুডেন্ট লগইন</span>
                                <span class="lang-en">Auto Student Login</span>
                            </span>
                            <span class="badge {{ $institution->auto_generate_student_login ? 'bg-primary' : 'bg-secondary' }} rounded-pill">
                                {{ $institution->auto_generate_student_login ? 'ON' : 'OFF' }}
                            </span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center py-2">
                            <span class="text-muted small">
                                <span class="lang-bn">স্বয়ংক্রিয় অভিভাবক লগইন</span>
                                <span class="lang-en">Auto Guardian Login</span>
                            </span>
                            <span class="badge {{ $institution->auto_generate_guardian_login ? 'bg-primary' : 'bg-secondary' }} rounded-pill">
                                {{ $institution->auto_generate_guardian_login ? 'ON' : 'OFF' }}
                            </span>
                        </div>

                        <hr style="border-color: var(--border);">

                        <a href="{{ route('institution.registration') }}" class="btn btn-primary w-100 rounded-pill mb-2">
                            <i class="bi bi-send-fill me-2"></i>
                            <span class="lang-bn">যোগাযোগ করুন</span>
                            <span class="lang-en">Get in Touch</span>
                        </a>
                        <a href="{{ url()->previous() }}" class="btn btn-outline-primary w-100 rounded-pill">
                            <i class="bi bi-arrow-left me-2"></i>
                            <span class="lang-bn">তালিকায় ফিরুন</span>
                            <span class="lang-en">Back to List</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== TEACHER DETAIL MODAL ===== --}}
    <div class="modal fade" id="teacherDetailModal" tabindex="-1" aria-labelledby="teacherDetailModalLabel" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 1rem; border: none;">
                <div class="modal-header" style="border-bottom: 1px solid var(--border);">
                    <h5 class="modal-title fw-bold" id="teacherDetailModalLabel">
                        <span class="lang-bn">শিক্ষকের বিস্তারিত তথ্য</span>
                        <span class="lang-en">Teacher Details</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" wire:click="closeEmployeeDetails"></button>
                </div>
                <div class="modal-body p-4">
                    @if($employeeDetail)
                        <div class="text-center mb-4">
                            <div class="rounded-circle overflow-hidden mx-auto mb-3 d-flex align-items-center justify-content-center"
                                 style="width:96px;height:96px;background: var(--bg-alt); border: 2px solid var(--border);">
                                @if($employeeDetail['avatar'])
                                    <img src="{{ asset('storage/' . $employeeDetail['avatar']) }}" alt="{{ $employeeDetail['name'] }}" class="w-100 h-100" style="object-fit: cover;">
                                @else
                                    <i class="bi bi-person-fill fs-1 text-primary"></i>
                                @endif
                            </div>
                            <h5 class="fw-bold mb-1">{{ $employeeDetail['name'] }}</h5>
                            <span class="badge bg-secondary rounded-pill">
                                <span class="lang-bn">শিক্ষক</span>
                                <span class="lang-en">Teacher</span>
                            </span>
                        </div>
                        <p class="text-muted text-center mb-0" style="line-height: 1.8;">
                            {{ $employeeDetail['bio'] }}
                        </p>
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