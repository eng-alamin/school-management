<aside class="sidebar no-print" id="mainSidebar">

  <!-- Brand -->
  <div class="sidebar-brand">
    <div class="brand-icon">
      <span class="material-icons-round">dashboard</span>
    </div>
    <div class="brand-text">
      <div class="brand-name">Dashboard</div>
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
    <a href="{{ route('superadmin.profile.overview') }}"><span class="ud-icon">MP</span> <span id="ud-profile">My Profile</span></a>
    <a href="{{ route('superadmin.profile.setting') }}"><span class="ud-icon">S</span> <span id="ud-settings">Settings</span></a>
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
        <a href="{{route('superadmin.institutions.index') }}" class="nav1-link {{ str_contains(request()->url(), 'superadmin/institutions/') == true ? 'active' : '' }}">
          <span class="material-icons-round nav-icon">school</span>
          <span class="nav-label" id="nav-schools">Institutions</span>
        </a>
      </li>

      <li class="nav1-item">
        <a href="{{route('superadmin.admins.index') }}" class="nav1-link {{ str_contains(request()->url(), 'superadmin/admins/') == true ? 'active' : '' }}">
          <span class="material-icons-round nav-icon">school</span>
          <span class="nav-label" id="nav-admins">Admins</span>
        </a>
      </li>

      <li class="nav1-item">
        <a href="{{route('superadmin.invoices.index') }}" class="nav1-link {{ str_contains(request()->url(), 'superadmin/billings/invoices') == true ? 'active' : '' }}">
          <span class="material-icons-round nav-icon">receipt</span>
          <span class="nav-label" id="nav-schools">Billings Invoice</span>
        </a>
      </li>

      <li class="nav1-item">
        <div class="nav1-link {{ str_contains(request()->url(), 'superadmin/monitoring/') == true ? 'active open' : '' }}" onclick="toggleNav1(this)">
          <span class="material-icons-round nav-icon">analytics</span>
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
        <div class="nav1-link {{ str_contains(request()->url(), 'superadmin/activity-logs') || str_contains(request()->url(), 'superadmin/session-logs') || str_contains(request()->url(), 'superadmin/login-logs') == true ? 'active open' : '' }}" onclick="toggleNav1(this)">
          <span class="material-icons-round nav-icon">security</span>
          <span class="nav-label" id="nav-schools">Audit & Security Logs</span>
          <span class="material-icons-round nav-arrow">expand_more</span>
        </div>
        <div class="nav2-collapse {{ str_contains(request()->url(), 'superadmin/activity-logs') || str_contains(request()->url(), 'superadmin/session-logs') || str_contains(request()->url(), 'superadmin/login-logs') == true ? 'show' : '' }}">
          <ul>
            <li class="nav2-item"><a href="{{ route('superadmin.activitylog') }}" class="nav2-link {{ Route::is('superadmin.activitylog') == true ? 'active' : '' }}"><span class="nav2-icon">AL</span><span class="nav2-label" id="nav-schools">Activity Logs</span></a></li>
            <li class="nav2-item"><a href="{{ route('superadmin.sessionlog') }}" class="nav2-link {{ Route::is('superadmin.sessionlog') == true ? 'active' : '' }}"><span class="nav2-icon">LL</span><span class="nav2-label" id="nav-schools">Session Logs</span></a></li>
            <li class="nav2-item"><a href="{{ route('superadmin.loginlog') }}" class="nav2-link {{ Route::is('superadmin.loginlog') == true ? 'active' : '' }}"><span class="nav2-icon">LL</span><span class="nav2-label" id="nav-schools">Login Logs</span></a></li>
          </ul>
        </div>
      </li>

      <li class="nav1-item">
        <div class="nav1-link {{ str_contains(request()->url(), 'superadmin/settings') == true ? 'active open' : '' }}" onclick="toggleNav1(this)">
          <span class="material-icons-round nav-icon">settings</span>
          <span class="nav-label" id="nav-schools">System Settings</span>
          <span class="material-icons-round nav-arrow">expand_more</span>
        </div>
        <div class="nav2-collapse {{ str_contains(request()->url(), 'superadmin/settings') || str_contains(request()->url(), 'superadmin/pricingrates') == true ? 'show' : '' }}">
          <ul>
            <li class="nav2-item"><a href="{{ route('superadmin.settings') }}" class="nav2-link {{ Route::is('superadmin.settings') == true ? 'active' : '' }}"><span class="nav2-icon">S</span><span class="nav2-label" id="nav-settings">Settings</span></a></li>
            <li class="nav2-item"><a href="{{ route('superadmin.pricingrates') }}" class="nav2-link {{ Route::is('superadmin.pricingrates') == true ? 'active' : '' }}"><span class="nav2-icon">PR</span><span class="nav2-label" id="nav-pricingrates">Pricing Rates</span></a></li>
          </ul>
        </div>
      </li>

      <li class="nav1-item">
        <a href="{{route('superadmin.backups') }}" class="nav1-link {{ str_contains(request()->url(), 'backups') == true ? 'active' : '' }}">
          <span class="material-icons-round nav-icon">backup</span>
          <span class="nav-label" id="nav-backups">Backups</span>
        </a>
      </li>

    </ul>

  </div>
</aside>