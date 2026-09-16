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
    <img src="{{ auth()->user()->avatar ? asset('storage/' . auth()->user()->avatar) : asset('assets/img/default-avatar.jpg') }}" class="user-avatar" alt="{{ auth()->user()->name}}">
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
      @can('institution.dashboard.view')
      <li class="nav1-item">
        <a href="{{route('teacher.dashboard') }}" class="nav1-link {{ str_contains(request()->url(), 'teacher/dashboard') == true ? 'active' : '' }}">
          <span class="material-icons-round">space_dashboard</span>
          <span class="nav-label" id="nav-dashboard">Dashboard</span>
        </a>
      </li>
      @endcan

      @can('institution.student.view')
      <li class="nav1-item">
        <a class="nav1-link {{ Route::is('teacher.student.list', 'teacher.student.edit', 'teacher.student.overview',) == true ? 'active' : '' }}" href="{{route('teacher.student.list') }}">
          <span class="material-icons-round">school</span>
          <span class="nav-label" id="nav-students">Students</span>
        </a>
      </li>
      @endcan

      @can('institution.parent.view')
      <li class="nav1-item">
        <a class="nav1-link {{ str_contains(request()->url(), 'teacher/parent/') == true ? 'active' : '' }}" href="{{route('teacher.parent.list') }}">
          <span class="material-icons-round">groups</span>
          <span class="nav-label" id="nav-parents">Parents</span>
        </a>
      </li>
      @endcan

      @can('institution.homework.view')
      <li class="nav1-item">
        <a class="nav1-link {{ str_contains(request()->url(), 'teacher/homework') == true ? 'active' : '' }}" href="{{ route('teacher.homework.list')  }}">
          <span class="material-icons-round">assignment</span>
          <span class="nav-label" id="nav-home-work">Home Work</span>
        </a>
      </li>
      @endcan

      @can('institution.academic_class_schedule.view')
      <li class="nav1-item">
        <a class="nav1-link {{ str_contains(request()->url(), 'teacher/academic/class-schedule') == true ? 'active' : '' }}" href="{{route('teacher.academic.class-schedule.list') }}">
          <span class="material-icons-round">menu_book</span>
          <span class="nav-label" id="nav-class-schedule">Class Schedule</span>
        </a>
      </li>
      @endcan

      @can('institution.exam_schedule.view')
      <li class="nav1-item">
        <a class="nav1-link {{ str_contains(request()->url(), 'teacher/exam/schedule') == true ? 'active' : '' }}" href="{{ route('teacher.exam.schedule.list')  }}">
          <span class="material-icons-round">assignment</span>
          <span class="nav-label" id="nav-exam-schedule">Exam Schedule</span>
        </a>
      </li>
      @endcan

      @can('institution.question_paper.view')
      <li class="nav1-item">
        <a class="nav1-link {{ str_contains(request()->url(), 'teacher/question-papers') == true ? 'active' : '' }}" href="{{ route('teacher.question-papers.index')  }}">
          <span class="material-icons-round">quiz</span>
          <span class="nav-label" id="nav-question-papers">Question Papers</span>
        </a>
      </li>
      @endcan

      @canany(['institution.attendance_student.view', 'institution.attendance_exam.view'])
      <li class="nav1-item">
        <div class="nav1-link {{ str_contains(request()->url(), 'teacher/attendance') == true ? 'active open' : '' }}" onclick="toggleNav1(this)">
          <span class="material-icons-round">event_available</span>
          <span class="nav-label" id="nav-attendance">Attendance</span>
          <span class="material-icons-round nav-arrow">expand_more</span>
        </div>
        <div class="nav2-collapse {{ str_contains(request()->url(), 'attendance') == true ? 'show' : '' }}">
          <ul>
            @can('institution.attendance_student.view')
            <li class="nav2-item"><a href="{{route('teacher.attendance.students') }}" class="nav2-link {{ str_contains(request()->url(), 'teacher/attendance/students') == true ? 'active' : '' }}"><span class="nav2-icon">G</span><span class="nav2-label" id="nav-attendance-student">Student</span></a></li>
            @endcan
            {{-- <li class="nav2-item"><a href="{{route('teacher.attendance.employees') }}" class="nav2-link {{ str_contains(request()->url(), 'teacher/attendance/employees') == true ? 'active' : '' }}"><span class="nav2-icon">G</span><span class="nav2-label" id="nav-attendance-employee">Employee</span></a></li> --}}
            @can('institution.attendance_exam.view')
            <li class="nav2-item"><a href="{{route('teacher.attendance.exams') }}" class="nav2-link {{ str_contains(request()->url(), 'teacher/attendance/exams') == true ? 'active' : '' }}"><span class="nav2-icon">G</span><span class="nav2-label" id="nav-attendance-exam">Exam</span></a></li>
            @endcan
          </ul>
        </div>
      </li>
      @endcanany

      @canany(['institution.salary_payment.view', 'institution.salary_advance.view'])
      <li class="nav1-item">
        <div class="nav1-link {{ str_contains(request()->url(), 'teacher/salary') == true ? 'active open' : '' }}" onclick="toggleNav1(this)">
          <span class="material-icons-round">event_available</span>
          <span class="nav-label" id="nav-salary">Salary</span>
          <span class="material-icons-round nav-arrow">expand_more</span>
        </div>
        <div class="nav2-collapse {{ str_contains(request()->url(), 'teacher/salary') == true ? 'show' : '' }}">
          <ul>
            @can('institution.salary_payment.view')
            <li class="nav2-item"><a href="{{route('teacher.salary.history') }}" class="nav2-link {{ str_contains(request()->url(), 'teacher/salary/history') == true ? 'active' : '' }}"><span class="nav2-icon">G</span><span class="nav2-label" id="nav-history">History</span></a></li>
            @endcan
            @can('institution.salary_advance.view')
            <li class="nav2-item"><a href="{{route('teacher.salary.advance') }}" class="nav2-link {{ str_contains(request()->url(), 'teacher/salary/advance') == true ? 'active' : '' }}"><span class="nav2-icon">G</span><span class="nav2-label" id="nav-advance">Advance</span></a></li>
            @endcan
          </ul>
        </div>
      </li>
      @endcanany

      @can('institution.leave_application.view')
      <li class="nav1-item">
        <a href="{{route('teacher.leave.apply') }}" class="nav1-link {{ str_contains(request()->url(), 'teacher/leave') == true ? 'active' : '' }}">
          <span class="material-icons-round">event</span>
          <span class="nav-label" id="nav-leaves">Leaves</span>
        </a>
      </li>
      @endcan

      @can('institution.event.view')
      <li class="nav1-item">
        <a href="{{route('teacher.event.list') }}" class="nav1-link {{ str_contains(request()->url(), 'teacher/event') == true ? 'active' : '' }}">
          <span class="material-icons-round">event</span>
          <span class="nav-label" id="nav-events">Events</span>
        </a>
      </li>
      @endcan

      @can('institution.mailbox.view')
      <li class="nav1-item">
        <a href="{{route('teacher.mailbox.inbox') }}" class="nav1-link {{ str_contains(request()->url(), 'mailbox/') == true ? 'active' : '' }}">
          <span class="material-icons-round">chat</span>
          <span class="nav-label" id="nav-mailbox">Mailbox</span>
        </a>
      </li>
      @endcan

      @can('institution.notice.view')
      <li class="nav1-item">
        <a href="{{route('teacher.notices') }}" class="nav1-link {{ str_contains(request()->url(), 'notices') == true ? 'active' : '' }}">
          <span class="material-icons-round">chat</span>
          <span class="nav-label" id="nav-notices">Notices</span>
        </a>
      </li>
      @endcan

    </ul>

  </div>
</aside>