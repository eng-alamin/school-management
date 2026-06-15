<aside class="sidebar no-print" id="mainSidebar">

  <!-- Brand -->
  <div class="sidebar-brand">
    <div class="brand-icon">
      <span class="material-icons-round">dashboard</span>
    </div>
    <div class="brand-text">
      <div class="brand-name">Super Admin Dashboard</div>
      <div class="brand-sub">{{ auth()->user()->name }}</div>
    </div>
  </div>

  <!-- User -->
  <div class="sidebar-user" id="userToggle">
    <img src="{{ auth()->user()->avatar ? asset(auth()->user()->avatar) : asset('assets/img/default-avatar.jpg') }}" class="user-avatar" alt="{{ auth()->user()->name}}">
    <span class="user-name">{{ auth()->user()->name}}</span>
    <span class="material-icons-round user-arrow" id="userArrow">expand_more</span>
  </div>
  <div class="user-dropdown" id="userDropdown">
    <a href="{{ route('admin.profile.overview') }}"><span class="ud-icon">MP</span> <span id="ud-profile">My Profile</span></a>
    <a href="{{ route('admin.profile.setting') }}"><span class="ud-icon">S</span> <span id="ud-settings">Settings</span></a>
    <a href="{{route('logout') }} " class="pd-menu-item danger" onclick="event.preventDefault(); document.getElementById('logout-form').submit()">
        <span class="ud-icon">L</span> <span id="ud-logout">Logout</span>
    </a>
    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">
        @csrf
    </form>
  </div>

  <!-- Scrollable nav -->
  <div class="sidebar-scroll">

    <ul>
      <li class="nav1-item">
        <a href="{{route('superadmin.dashboard') }}" class="nav1-link {{ str_contains(request()->url(), 'dashboard') == true ? 'active' : '' }}">
          <span class="material-icons-round nav-icon">space_dashboard</span>
          <span class="nav-label" id="nav-dashboards">Dashboard</span>
        </a>
      </li>

      <li class="nav1-item">
        <div class="nav1-link {{ str_contains(request()->url(), 'superadmin/schools/') == true ? 'active open' : '' }}" onclick="toggleNav1(this)">
          <span class="material-icons-round nav-icon">schools</span>
          <span class="nav-label" id="nav-schools">Schools</span>
          <span class="material-icons-round nav-arrow">expand_more</span>
        </div>
        <div class="nav2-collapse {{ str_contains(request()->url(), 'superadmin/schools/') == true ? 'show' : '' }}">
          <ul>
            <li class="nav2-item"><a href="{{ route('superadmin.schools.index') }}" class="nav2-link {{ Route::is('superadmin.schools.index') == true ? 'active' : '' }}"><span class="nav2-icon">S</span><span class="nav2-label" id="nav-schools">Schools</span></a></li>
            <li class="nav2-item"><a href="{{route('superadmin.schools.admin') }}" class="nav2-link {{ Route::is('superadmin.schools.admin') == true ? 'active' : '' }}"><span class="nav2-icon">SA</span><span class="nav2-label" id="nav-admins">Admins</span></a></li>
          </ul>
        </div>
      </li>

      <li class="nav1-item">
        <a href="{{route('superadmin.invoices.index') }}" class="nav1-link {{ str_contains(request()->url(), 'superadmin/billings/invoices') == true ? 'active' : '' }}">
          <span class="material-icons-round nav-icon">schools</span>
          <span class="nav-label" id="nav-schools">Billings Invoice</span>
        </a>
      </li>

      <li class="nav1-item">
        <div class="nav1-link {{ str_contains(request()->url(), 'superadmin/monitoring/') == true ? 'active open' : '' }}" onclick="toggleNav1(this)">
          <span class="material-icons-round nav-icon">schools</span>
          <span class="nav-label" id="nav-schools">Health & Monitoring</span>
          <span class="material-icons-round nav-arrow">expand_more</span>
        </div>
        <div class="nav2-collapse {{ str_contains(request()->url(), 'superadmin/monitoring/') == true ? 'show' : '' }}"> 
          <ul>
            <li class="nav2-item"><a href="{{ route('superadmin.monitoring.server') }}" class="nav2-link {{ Route::is('superadmin.monitoring.server') == true ? 'active' : '' }}"><span class="nav2-icon">S</span><span class="nav2-label" id="nav-schools">Server</span></a></li>
            <li class="nav2-item"><a href="{{route('superadmin.monitoring.queue') }}" class="nav2-link {{ Route::is('superadmin.monitoring.queue') == true ? 'active' : '' }}"><span class="nav2-icon">Q</span><span class="nav2-label" id="nav-schools-admins">Queue</span></a></li>
            <li class="nav2-item"><a href="{{route('superadmin.monitoring.logs') }}" class="nav2-link {{ Route::is('superadmin.monitoring.logs') == true ? 'active' : '' }}"><span class="nav2-icon">L</span><span class="nav2-label" id="nav-schools-admins">Logs</span></a></li>
            <li class="nav2-item"><a href="{{route('superadmin.monitoring.performance') }}" class="nav2-link {{ Route::is('superadmin.monitoring.performance') == true ? 'active' : '' }}"><span class="nav2-icon">P</span><span class="nav2-label" id="nav-schools-admins">Performance</span></a></li>
          </ul>
        </div>
      </li>

      <li class="nav1-item">
        <div class="nav1-link {{ str_contains(request()->url(), 'superadmin/activity-logs') || str_contains(request()->url(), 'superadmin/activity-logs') == true ? 'active open' : '' }}" onclick="toggleNav1(this)">
          <span class="material-icons-round nav-icon">schools</span>
          <span class="nav-label" id="nav-schools">Audit & Security Logs</span>
          <span class="material-icons-round nav-arrow">expand_more</span>
        </div>
        <div class="nav2-collapse {{ str_contains(request()->url(), 'superadmin/activity-logs') || str_contains(request()->url(), 'superadmin/login-logs') == true ? 'show' : '' }}">
          <ul>
            <li class="nav2-item"><a href="{{ route('superadmin.activitylog') }}" class="nav2-link {{ Route::is('superadmin.activitylog') == true ? 'active' : '' }}"><span class="nav2-icon">AL</span><span class="nav2-label" id="nav-schools">Activity Logs</span></a></li>
            <li class="nav2-item"><a href="{{ route('superadmin.loginlog') }}" class="nav2-link {{ Route::is('superadmin.loginlog') == true ? 'active' : '' }}"><span class="nav2-icon">LL</span><span class="nav2-label" id="nav-schools">Login Logs</span></a></li>
          </ul>
        </div>
      </li>

      <li class="nav1-item">
        <div class="nav1-link {{ str_contains(request()->url(), 'superadmin/settings') == true ? 'active open' : '' }}" onclick="toggleNav1(this)">
          <span class="material-icons-round nav-icon">schools</span>
          <span class="nav-label" id="nav-schools">System Settings</span>
          <span class="material-icons-round nav-arrow">expand_more</span>
        </div>
        <div class="nav2-collapse {{ str_contains(request()->url(), 'superadmin/settings') == true ? 'show' : '' }}">
          <ul>
            <li class="nav2-item"><a href="{{ route('superadmin.settings') }}" class="nav2-link {{ Route::is('superadmin.settings') == true ? 'active' : '' }}"><span class="nav2-icon">S</span><span class="nav2-label" id="nav-schools">Settings</span></a></li>
          </ul>
        </div>
      </li>

    </ul>

  </div>
</aside>