<aside class="sidebar no-print" id="mainSidebar">

  <!-- Brand -->
  <div class="sidebar-brand">
    <div class="brand-icon">
      <span class="material-icons-round">dashboard</span>
    </div>
    <div class="brand-text">
      <div class="brand-name">Teacher Dashboard</div>
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
    <a href="{{ route('teacher.profile.overview') }}"><span class="ud-icon">MP</span> <span id="ud-profile">My Profile</span></a>
    <a href="{{ route('teacher.profile.setting') }}"><span class="ud-icon">S</span> <span id="ud-settings">Settings</span></a>
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
        <a href="{{route('teacher.dashboard') }}" class="nav1-link {{ str_contains(request()->url(), 'teacher/dashboard') == true ? 'active' : '' }}">
          <span class="material-icons-round nav-icon">space_dashboard</span>
          <span class="nav-label" id="nav-dashboards">Dashboard</span>
        </a>
      </li>

      <li class="nav1-item">
        <a class="nav1-link {{ Route::is('teacher.student.list', 'teacher.student.edit') == true ? 'active' : '' }}" href="{{route('teacher.student.list') }}">
          <span class="material-icons-round nav-icon">school</span>
          <span class="nav-label" id="nav-students">Students</span>
        </a>
      </li>

      <li class="nav1-item">
        <a class="nav1-link {{ str_contains(request()->url(), 'teacher/parent/') == true ? 'active' : '' }}" href="{{route('teacher.parent.list') }}">
          <span class="material-icons-round nav-icon">groups</span>
          <span class="nav-label" id="nav-parents">Parents</span>
        </a>
      </li>

      <li class="nav1-item">
        <a class="nav1-link {{ str_contains(request()->url(), 'teacher/homework') == true ? 'active' : '' }}" href="{{ route('teacher.homework.list')  }}">
          <span class="material-icons-round nav-icon">assignment</span>
          <span class="nav-label" id="nav-home-work">Home Work</span>
        </a>
      </li>

      <li class="nav1-item">
        <a class="nav1-link {{ str_contains(request()->url(), 'teacher/academic/class-schedule') == true ? 'active' : '' }}" href="{{route('teacher.academic.class-schedule.list') }}">
          <span class="material-icons-round nav-icon">menu_book</span>
          <span class="nav-label" id="nav-parents">Class Schedule</span>
        </a>
      </li>

      <li class="nav1-item">
        <a class="nav1-link {{ str_contains(request()->url(), 'teacher/exam/schedule') == true ? 'active' : '' }}" href="{{ route('teacher.exam.schedule.list')  }}">
          <span class="material-icons-round nav-icon">assignment</span>
          <span class="nav-label" id="nav-home-work">Exam Schedule</span>
        </a>
      </li>

      <li class="nav1-item">
        <div class="nav1-link {{ str_contains(request()->url(), 'teacher/attendance') == true ? 'active open' : '' }}" onclick="toggleNav1(this)">
          <span class="material-icons-round nav-icon">event_available</span>
          <span class="nav-label" id="nav-attendance">Attendance</span>
          <span class="material-icons-round nav-arrow">expand_more</span>
        </div>
        <div class="nav2-collapse {{ str_contains(request()->url(), 'attendance') == true ? 'show' : '' }}">
          <ul>
            <li class="nav2-item"><a href="{{route('teacher.attendance.students') }}" class="nav2-link {{ str_contains(request()->url(), 'teacher/attendance/students') == true ? 'active' : '' }}"><span class="nav2-icon">G</span><span class="nav2-label" id="nav-student">Student</span></a></li>
            {{-- <li class="nav2-item"><a href="{{route('teacher.attendance.employees') }}" class="nav2-link {{ str_contains(request()->url(), 'teacher/attendance/employees') == true ? 'active' : '' }}"><span class="nav2-icon">G</span><span class="nav2-label" id="nav-student">Enployee</span></a></li> --}}
            <li class="nav2-item"><a href="{{route('teacher.attendance.exams') }}" class="nav2-link {{ str_contains(request()->url(), 'teacher/attendance/exams') == true ? 'active' : '' }}"><span class="nav2-icon">G</span><span class="nav2-label" id="nav-student">Exam</span></a></li>
          </ul>
        </div>
      </li>

      <li class="nav1-item">
        <div class="nav1-link {{ str_contains(request()->url(), 'teacher/leave') ? 'active open' : '' }}" onclick="toggleNav1(this)">
          <span class="material-icons-round nav-icon">event</span>
          <span class="nav-label" id="nav-leaves">Leaves</span>
          <span class="material-icons-round nav-arrow">expand_more</span>
        </div>
        <div class="nav2-collapse {{ str_contains(request()->url(), 'teacher/leave') == true ? 'show' : '' }}">
          <ul>
            <li class="nav2-item"><a href="{{route('teacher.leave.apply') }}" class="nav2-link {{ str_contains(request()->url(), 'teacher/leave/apply') == true ? 'active' : '' }}"><span class="nav2-icon">P</span><span class="nav2-label" id="nav-idcard-template">Apply</span></a></li>
            <li class="nav2-item"><a href="{{route('teacher.leave.students') }}" class="nav2-link {{ str_contains(request()->url(), 'teacher/leave/students') == true ? 'active' : '' }}"><span class="nav2-icon">P</span><span class="nav2-label" id="nav-idcard-template">Students</span></a></li>
          </ul>
        </div>
      </li>

      <li class="nav1-item">
        <a href="{{route('teacher.event.list') }}" class="nav1-link {{ str_contains(request()->url(), 'event') == true ? 'active' : '' }}">
          <span class="material-icons-round nav-icon">event</span>
          <span class="nav-label" id="nav-events">Events</span>
        </a>
      </li>

      <li class="nav1-item">
        <a href="{{route('teacher.mailbox.inbox') }}" class="nav1-link {{ str_contains(request()->url(), 'mailbox/') == true ? 'active' : '' }}">
          <span class="material-icons-round nav-icon">chat</span>
          <span class="nav-label" id="nav-mailbox">Mailbox</span>
        </a>
      </li>

      <li class="nav1-item">
        <a href="{{route('teacher.notices') }}" class="nav1-link {{ str_contains(request()->url(), 'notices') == true ? 'active' : '' }}">
          <span class="material-icons-round nav-icon">chat</span>
          <span class="nav-label" id="nav-notices">Notices</span>
        </a>
      </li>
            
    </ul>

  </div>
</aside>