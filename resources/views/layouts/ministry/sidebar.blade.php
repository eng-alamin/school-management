<aside class="sidebar no-print" id="mainSidebar">

  <!-- Brand -->
  <div class="sidebar-brand">
    <div class="brand-icon">
      <span class="material-icons-round">dashboard</span>
    </div>
    <div class="brand-text">
      <div class="brand-name" data-en="Dashboard" data-bn="ড্যাশবোর্ড">Dashboard</div>
      <div class="brand-sub">{{ auth()->user()->name }}</div>
    </div>
  </div>

  <!-- User -->
  <div class="sidebar-user" id="userToggle">
    <img src="{{ auth()->user()->avatar ? asset('storage/' . auth()->user()->avatar) : asset('assets/img/default-avatar.jpg') }}" class="user-avatar" alt="{{ auth()->user()->name}}">
    <span class="user-name">{{ auth()->user()->name}}</span>
    <span class="material-icons-round user-arrow" id="userArrow">expand_more</span>
  </div>
  <div class="user-dropdown" id="userDropdown">
    <a href="{{ route('ministry.profile.overview') }}"><span class="ud-icon">MP</span> <span id="ud-profile" data-en="My Profile" data-bn="আমার প্রোফাইল">My Profile</span></a>
    <a href="{{ route('ministry.profile.setting') }}"><span class="ud-icon">S</span> <span id="ud-settings" data-en="Settings" data-bn="সেটিংস">Settings</span></a>
    <a href="{{route('logout') }} " class="pd-menu-item danger" onclick="event.preventDefault(); document.getElementById('logout-form').submit()">
        <span class="ud-icon">L</span> <span id="ud-logout" data-en="Logout" data-bn="লগআউট">Logout</span>
    </a>
    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">
        @csrf
    </form>
  </div>

  <!-- Scrollable nav -->
  <div class="sidebar-scroll">

    <ul>
      <li class="nav1-item">
        <a href="{{route('ministry.dashboard') }}" class="nav1-link {{ str_contains(request()->url(), 'dashboard') == true ? 'active' : '' }}">
          <span class="material-icons-round nav-icon">space_dashboard</span>
          <span class="nav-label" id="nav-dashboards" data-en="Dashboard" data-bn="ড্যাশবোর্ড">Dashboard</span>
        </a>
      </li>

      @can('institution.view')
      <li class="nav1-item">
        <a href="{{route('ministry.institutions.index') }}" class="nav1-link {{ str_contains(request()->url(), 'ministry/institutions/index') == true ? 'active' : '' }}">
          <span class="material-icons-round nav-icon">school</span>
          <span class="nav-label" id="nav-schools" data-en="Institutions" data-bn="প্রতিষ্ঠানসমূহ">Institutions</span>
        </a>
      </li>
      @endcan

      @can('circular.manage')
      <li class="nav1-item">
        <a href="{{route('ministry.circulars.index') }}" class="nav1-link {{ str_contains(request()->url(), 'ministry/circulars/') == true ? 'active' : '' }}">
          <span class="material-icons-round nav-icon">announcement</span>
          <span class="nav-label" id="nav-circulars" data-en="Circulars" data-bn="পরিপত্র">Circulars</span>
        </a>
      </li>
      @endcan

      @can('compliance.manage')
      <li class="nav1-item">
        <div class="nav1-link {{ str_contains(request()->url(), 'ministry/compliance/') == true ? 'active open' : '' }}" onclick="toggleNav1(this)">
          <span class="material-icons-round nav-icon">fact_check</span>
          <span class="nav-label" id="nav-compliance" data-en="Compliance" data-bn="কমপ্লায়েন্স">Compliance</span>
          <span class="material-icons-round nav-arrow">expand_more</span>
        </div>
        <div class="nav2-collapse {{ str_contains(request()->url(), 'ministry/compliance/') == true ? 'show' : '' }}">
          <ul>
            <li class="nav2-item"><a href="{{ route('ministry.compliance.checklist') }}" class="nav2-link {{ Route::is('ministry.compliance.checklist') == true ? 'active' : '' }}"><span class="nav2-icon">C</span><span class="nav2-label" id="nav-checklist" data-en="Checklist" data-bn="চেকলিস্ট">Checklist</span></a></li>
            <li class="nav2-item"><a href="{{ route('ministry.compliance.inspections.index') }}" class="nav2-link {{ Route::is('ministry.compliance.inspections.*') == true ? 'active' : '' }}"><span class="nav2-icon">I</span><span class="nav2-label" id="nav-inspections" data-en="Inspections" data-bn="পরিদর্শন">Inspections</span></a></li>
            <li class="nav2-item"><a href="{{ route('ministry.compliance.violations.index') }}" class="nav2-link {{ Route::is('ministry.compliance.violations.*') == true ? 'active' : '' }}"><span class="nav2-icon">V</span><span class="nav2-label" id="nav-violations" data-en="Violations" data-bn="লঙ্ঘন">Violations</span></a></li>
          </ul>
        </div>
      </li>
      @endcan

      @can('grievance.manage')
      <li class="nav1-item">
        <a href="{{route('ministry.grievances.index') }}" class="nav1-link {{ str_contains(request()->url(), 'ministry/grievances/') == true ? 'active' : '' }}">
          <span class="material-icons-round nav-icon">announcement</span>
          <span class="nav-label" id="nav-grievances" data-en="Grievances" data-bn="অভিযোগসমূহ">Grievances</span>
        </a>
      </li>
      @endcan

      @can('exam-monitoring.view')
      <li class="nav1-item">
        <a href="{{route('ministry.academic.performance') }}" class="nav1-link {{ str_contains(request()->url(), 'academic-performance') == true ? 'active' : '' }}">
          <span class="material-icons-round nav-icon">school</span>
          <span class="nav-label" id="nav-academic-performance" data-en="Academic Performance" data-bn="একাডেমিক পারফরম্যান্স">Academic Performance</span>
        </a>
      </li>
      @endcan

      @can('ranking.view')
      <li class="nav1-item">
        <a href="{{route('ministry.ranking.index') }}" class="nav1-link {{ str_contains(request()->url(), 'ministry/ranking') == true ? 'active' : '' }}">
          <span class="material-icons-round nav-icon">leaderboard</span>
          <span class="nav-label" id="nav-ranking" data-en="Institution Ranking" data-bn="প্রতিষ্ঠান র‍্যাংকিং">Institution Ranking</span>
        </a>
      </li>
      @endcan

      @can('heatmap.view')
      <li class="nav1-item">
        <a href="{{route('ministry.geography.heatmap') }}" class="nav1-link {{ str_contains(request()->url(), 'ministry/geography/heatmap') == true ? 'active' : '' }}">
          <span class="material-icons-round nav-icon">map</span>
          <span class="nav-label" id="nav-geography-heatmap" data-en="Geography Heatmap" data-bn="ভৌগোলিক হিটম্যাপ">Geography Heatmap</span>
        </a>
      </li>
      @endcan

      @can('report.view')
      <li class="nav1-item">
        <a href="{{route('ministry.reports.index') }}" class="nav1-link {{ str_contains(request()->url(), 'ministry/reports') == true ? 'active' : '' }}">
          <span class="material-icons-round nav-icon">description</span>
          <span class="nav-label" id="nav-reports" data-en="Reports" data-bn="প্রতিবেদন">Reports</span>
        </a>
      </li>
      @endcan

      {{-- ===================== --}}
      {{-- Administration (নতুন — User Management + Role & Permission) --}}
      {{-- ===================== --}}
      @if (auth()->user()->hasRole('Ministry Super Admin'))
        <li class="nav1-item">
          <div class="nav1-link {{ (str_contains(request()->url(), 'ministry/users') || str_contains(request()->url(), 'ministry/roles')) == true ? 'active open' : '' }}" onclick="toggleNav1(this)">
            <span class="material-icons-round nav-icon">admin_panel_settings</span>
            <span class="nav-label" id="nav-administration" data-en="Administration" data-bn="প্রশাসন">Administration</span>
            <span class="material-icons-round nav-arrow">expand_more</span>
          </div>
          <div class="nav2-collapse {{ (str_contains(request()->url(), 'ministry/users') || str_contains(request()->url(), 'ministry/roles')) == true ? 'show' : '' }}">
            <ul>
              <li class="nav2-item"><a href="{{ route('ministry.users.index') }}" class="nav2-link {{ Route::is('ministry.users.*') == true ? 'active' : '' }}"><span class="nav2-icon">U</span><span class="nav2-label" id="nav-ministry-users" data-en="Ministry Users" data-bn="মন্ত্রণালয়ের ব্যবহারকারী">Ministry Users</span></a></li>
              <li class="nav2-item"><a href="{{ route('ministry.roles.index') }}" class="nav2-link {{ Route::is('ministry.roles.*') == true ? 'active' : '' }}"><span class="nav2-icon">R</span><span class="nav2-label" id="nav-ministry-roles" data-en="Roles & Permissions" data-bn="রোল ও পারমিশন">Roles & Permissions</span></a></li>
            </ul>
          </div>
        </li>
      @endif

    </ul>

  </div>
</aside>