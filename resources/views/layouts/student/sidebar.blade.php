<aside class="sidebar no-print" id="mainSidebar">

  <!-- Brand -->
  <div class="sidebar-brand">
    <div class="brand-icon">
      <span class="material-icons-round">dashboard</span>
    </div>
    <div class="brand-text">
      <div class="brand-name">Student Dashboard</div>
      <div class="brand-sub">{{ institution()->name }}</div>
    </div>
  </div>

  <!-- User -->
  <div class="sidebar-user" id="userToggle">
    <img src="{{ auth()->user()->avatar ? asset(auth()->user()->avatar) : asset('assets/img/default-avatar.jpg') }}" class="user-avatar" alt="{{ auth()->user()->name}}">
    <span class="user-name">{{ auth()->user()->name}}</span>
    <span class="material-icons-round user-arrow" id="userArrow">expand_more</span>
  </div>
  <div class="user-dropdown" id="userDropdown">
    <a href="{{ route('student.profile.overview') }}"><span class="ud-icon">MP</span> <span id="ud-profile">My Profile</span></a>
    <a href="{{ route('student.profile.setting') }}"><span class="ud-icon">S</span> <span id="ud-settings">Settings</span></a>
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
        <a href="{{route('student.dashboard') }}" class="nav1-link {{ str_contains(request()->url(), 'student/dashboard') == true ? 'active' : '' }}">
          <span class="material-icons-round nav-icon">space_dashboard</span>
          <span class="nav-label" id="nav-dashboards">Dashboard</span>
        </a>
      </li>

      <li class="nav1-item">
        <a href="{{route('student.teachers') }}" class="nav1-link {{ Route::is('student.teachers') == true ? 'active' : '' }}">
          <span class="material-icons-round nav-icon">school</span>
          <span class="nav-label" id="nav-teachers">Teachers</span>
        </a>
      </li>

      <li class="nav1-item">
        <a href="{{route('student.subjects') }}" class="nav1-link {{ Route::is('student.subjects') == true ? 'active' : '' }}">
          <span class="material-icons-round nav-icon">school</span>
          <span class="nav-label" id="nav-subjects">Subjects</span>
        </a>
      </li>

      <li class="nav1-item">
        <a href="{{route('student.classes') }}" class="nav1-link {{ Route::is('student.classes') == true ? 'active' : '' }}">
          <span class="material-icons-round nav-icon">school</span>
          <span class="nav-label" id="nav-classes">Classes</span>
        </a>
      </li>

      <li class="nav1-item">
        <a href="{{route('student.leaves')}}" class="nav1-link {{ Route::is('student.leaves') == true ? 'active' : '' }}">
          <span class="material-icons-round nav-icon">school</span>
          <span class="nav-label" id="nav-leaves">Leaves</span>
        </a>
      </li>

      <li class="nav1-item">
        <a href="{{route('student.homeworks')}}" class="nav1-link {{ Route::is('student.homeworks') == true ? 'active' : '' }}">
          <span class="material-icons-round nav-icon">school</span>
          <span class="nav-label" id="nav-homeworks">Homeworks</span>
        </a>
      </li>

      <li class="nav1-item">
        <a href="{{route('student.exams')}}" class="nav1-link {{ Route::is('student.exams') == true ? 'active' : '' }}">
          <span class="material-icons-round nav-icon">school</span>
          <span class="nav-label" id="nav-exams">Exams</span>
        </a>
      </li>

      <li class="nav1-item">
        <a href="{{route('student.events')}}" class="nav1-link {{ Route::is('student.events') == true ? 'active' : '' }}">
          <span class="material-icons-round nav-icon">school</span>
          <span class="nav-label" id="nav-events">Events</span>
        </a>
      </li>

    </ul>

  </div>
</aside>