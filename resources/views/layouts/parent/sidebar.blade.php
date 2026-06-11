<aside class="sidebar no-print" id="mainSidebar">

  <!-- Brand -->
  <div class="sidebar-brand">
    <div class="brand-icon">
      <span class="material-icons-round">dashboard</span>
    </div>
    <div class="brand-text">
      <div class="brand-name" id="brandName">Material Dashboard</div>
      <div class="brand-sub" id="brandSub">PRO Bootstrap 5</div>
    </div>
  </div>

  <!-- User -->
  <div class="sidebar-user" id="userToggle">
    <img src="{{ auth()->user()->avatar ? asset(auth()->user()->avatar) : global_asset('assets/img/default-avatar.jpg') }}" class="user-avatar" alt="{{ auth()->user()->name}}">
    <span class="user-name">{{ auth()->user()->name}}</span>
    <span class="material-icons-round user-arrow" id="userArrow">expand_more</span>
  </div>
  <div class="user-dropdown" id="userDropdown">
    <a href="{{ route('admin.profile.overview', ['tenant' => tenant('id')]) }}"><span class="ud-icon">MP</span> <span id="ud-profile">My Profile</span></a>
    <a href="{{ route('admin.profile.setting', ['tenant' => tenant('id')]) }}"><span class="ud-icon">S</span> <span id="ud-settings">Settings</span></a>
    <a href="{{route('logout', ['tenant' => tenant('id')]) }} " class="pd-menu-item danger" onclick="event.preventDefault(); document.getElementById('logout-form').submit()">
        <span class="ud-icon">L</span> <span id="ud-logout">Logout</span>
    </a>
    <form id="logout-form" action="{{ route('logout', ['tenant' => tenant('id')]) }}" method="POST" style="display:none;">
        @csrf
    </form>
  </div>

  <!-- Scrollable nav -->
  <div class="sidebar-scroll">

    <ul>
      <li class="nav1-item">
        <a class="nav1-link {{ Route::is('parent.dashboard') == true ? 'active' : '' }}" href="{{route('parent.dashboard', ['tenant' => tenant('id')]) }}">
          <span class="material-icons-round nav-icon">space_dashboard</span>
          <span class="nav-label" id="nav-dashboard">Dashboard</span>
        </a>
      </li>
    </ul>

  </div>
</aside>