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
    <a href="{{ route('teacher.profile.overview', ['tenant' => tenant('id')]) }}"><span class="ud-icon">MP</span> <span id="ud-profile">My Profile</span></a>
    <a href="{{ route('teacher.profile.setting', ['tenant' => tenant('id')]) }}"><span class="ud-icon">S</span> <span id="ud-settings">Settings</span></a>
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
        <a class="nav1-link {{ Route::is('teacher.dashboard') == true ? 'active' : '' }}" href="{{route('teacher.dashboard', ['tenant' => tenant('id')]) }}">
          <span class="material-icons-round nav-icon">space_dashboard</span>
          <span class="nav-label" id="nav-dashboard">Dashboard</span>
        </a>
      </li>

      <li class="nav1-item">
        <div class="nav1-link {{ Route::is('teacher.student.add') == true ? 'active open' : '' }}" onclick="toggleNav1(this)">
          <span class="material-icons-round nav-icon">how_to_reg</span>
          <span class="nav-label" id="nav-admission">Admission</span>
          <span class="material-icons-round nav-arrow">expand_more</span>
        </div>
        <div class="nav2-collapse {{ Route::is('teacher.student.add') == true ? 'show' : '' }}">
          <ul>
            <li class="nav2-item"><a href="{{route('teacher.student.add', ['tenant' => tenant('id')]) }}" class="nav2-link {{ Route::is('teacher.student.add') == true ? 'active' : '' }}"><span class="nav2-icon">C</span><span class="nav2-label" id="nav-createadmission">Create Admission</span></a></li>
            <li class="nav2-item"><a href="#" class="nav2-link"><span class="nav2-icon">O</span><span class="nav2-label" id="nav-onlineadmission">Online Admission</span></a></li>
          </ul>
        </div>
      </li>

      <li class="nav1-item">
        <a class="nav1-link {{ Route::is('teacher.student.list', 'teacher.student.edit') == true ? 'active' : '' }}" href="{{route('teacher.student.list', ['tenant' => tenant('id')]) }}">
          <span class="material-icons-round nav-icon">school</span>
          <span class="nav-label" id="nav-students">Students</span>
        </a>
      </li>

      <li class="nav1-item">
        <a class="nav1-link {{ str_contains(request()->url(), 'parent/') == true ? 'active' : '' }}" href="{{route('teacher.parent.list', ['tenant' => tenant('id')]) }}">
          <span class="material-icons-round nav-icon">groups</span>
          <span class="nav-label" id="nav-parents">Parents</span>
        </a>
      </li>

      <li class="nav1-item">
        <div class="nav1-link {{ str_contains(request()->url(), 'academic') == true ? 'active open' : '' }}" onclick="toggleNav1(this)">
          <span class="material-icons-round nav-icon">menu_book</span>
          <span class="nav-label" id="nav-academic">Academic</span>
          <span class="material-icons-round nav-arrow">expand_more</span>
        </div>
        <div class="nav2-collapse {{ str_contains(request()->url(), 'academic') == true ? 'show' : '' }}">
          <ul>
            <li class="nav2-item"><a href="{{route('teacher.academic.classes', ['tenant' => tenant('id')]) }}" class="nav2-link {{ str_contains(request()->url(), 'academic/classes') == true ? 'active' : '' }}"><span class="nav2-icon">C</span><span class="nav2-label" id="nav-class-section">Class & Section</span></a></li>
            <li class="nav2-item"><a href="{{route('teacher.academic.subjects', ['tenant' => tenant('id')]) }}" class="nav2-link {{ str_contains(request()->url(), 'academic/subjects') == true ? 'active' : '' }}"><span class="nav2-icon">S</span><span class="nav2-label" id="nav-subject">Subject</span></a></li>
            <li class="nav2-item"><a href="{{route('teacher.academic.class-assign', ['tenant' => tenant('id')]) }}" class="nav2-link {{ str_contains(request()->url(), 'academic/class-assign') == true ? 'active' : '' }}"><span class="nav2-icon">C</span><span class="nav2-label" id="nav-class-assign">Class Assign</span></a></li>
            <li class="nav2-item"><a href="{{route('teacher.academic.teacher-assign', ['tenant' => tenant('id')]) }}" class="nav2-link {{ str_contains(request()->url(), 'academic/teacher-assign') == true ? 'active' : '' }}"><span class="nav2-icon">T</span><span class="nav2-label" id="nav-class-assign">Teacher Assign</span></a></li>
            <li class="nav2-item"><a href="{{route('teacher.academic.class-schedule.list', ['tenant' => tenant('id')]) }}" class="nav2-link {{ str_contains(request()->url(), 'academic/class-schedule') == true ? 'active' : '' }}"><span class="nav2-icon">C</span><span class="nav2-label" id="nav-class-schedule">Class Schedule</span></a></li>
            <li class="nav2-item"><a href="{{route('teacher.academic.teacher-schedule', ['tenant' => tenant('id')]) }}" class="nav2-link {{ str_contains(request()->url(), 'academic/teacher-schedule') == true ? 'active' : '' }}"><span class="nav2-icon">T</span><span class="nav2-label" id="nav-teacher-schedule">Teacher Schedule</span></a></li>
            <li class="nav2-item"><a href="{{route('teacher.academic.student-promotion', ['tenant' => tenant('id')]) }}" class="nav2-link {{ str_contains(request()->url(), 'academic/student-promotion') == true ? 'active' : '' }}"><span class="nav2-icon">S</span><span class="nav2-label" id="nav-student-promotion">Student Promotion</span></a></li>
          </ul>
        </div>
      </li>

      <li class="nav1-item">
        <a class="nav1-link {{ str_contains(request()->url(), 'homework') == true ? 'active' : '' }}" href="{{ route('teacher.homework.list', ['tenant' => tenant('id')])  }}">
          <span class="material-icons-round nav-icon">assignment</span>
          <span class="nav-label" id="nav-home-work">Home Work</span>
        </a>
      </li>

      <li class="nav1-item">
        <div class="nav1-link {{ str_contains(request()->url(), 'exam/') == true ? 'active open' : '' }}" onclick="toggleNav1(this)">
          <span class="material-icons-round nav-icon">quiz</span>
          <span class="nav-label" id="nav-exam-master">Exam Master</span>
          <span class="material-icons-round nav-arrow">expand_more</span>
        </div>
        <div class="nav2-collapse {{ str_contains(request()->url(), 'exam') == true ? 'show' : '' }}">
          <ul>
            <li class="nav2-item"><a href="{{route('teacher.exam.setups', ['tenant' => tenant('id')]) }}" class="nav2-link {{ str_contains(request()->url(), 'exam/setups') == true ? 'active' : '' }}"><span class="nav2-icon">G</span><span class="nav2-label" id="nav-exams">Exams</span></a></li>
            <li class="nav2-item"><a href="{{route('teacher.exam.schedule.list', ['tenant' => tenant('id')]) }}" class="nav2-link {{ str_contains(request()->url(), 'exam/schedule') == true ? 'active' : '' }}"><span class="nav2-icon">T</span><span class="nav2-label" id="nav-exam-schedule">Exam Schedule</span></a></li>
            <li class="nav2-item"><div class="nav2-link"><span class="nav2-icon">N</span><span class="nav2-label" id="nav-marks">Marks</span></div></li>
          </ul>
        </div>
      </li>

      <li class="nav1-item">
        <div class="nav1-link {{ str_contains(request()->url(), 'attendance') == true ? 'active open' : '' }}" onclick="toggleNav1(this)">
          <span class="material-icons-round nav-icon">event_available</span>
          <span class="nav-label" id="nav-attendance">Attendance</span>
          <span class="material-icons-round nav-arrow">expand_more</span>
        </div>
        <div class="nav2-collapse {{ str_contains(request()->url(), 'attendance') == true ? 'show' : '' }}">
          <ul>
            <li class="nav2-item"><a href="{{route('teacher.attendance.students', ['tenant' => tenant('id')]) }}" class="nav2-link {{ str_contains(request()->url(), 'attendance/students') == true ? 'active' : '' }}"><span class="nav2-icon">G</span><span class="nav2-label" id="nav-student">Student</span></a></li>
            <li class="nav2-item"><a href="{{route('teacher.attendance.employees', ['tenant' => tenant('id')]) }}" class="nav2-link {{ str_contains(request()->url(), 'attendance/employees') == true ? 'active' : '' }}"><span class="nav2-icon">G</span><span class="nav2-label" id="nav-student">Enployee</span></a></li>
            <li class="nav2-item"><a href="{{route('teacher.attendance.exams', ['tenant' => tenant('id')]) }}" class="nav2-link {{ str_contains(request()->url(), 'attendance/exams') == true ? 'active' : '' }}"><span class="nav2-icon">G</span><span class="nav2-label" id="nav-student">Exam</span></a></li>
          </ul>
        </div>
      </li>

      <li class="nav1-item">
        <a href="{{route('teacher.event.list', ['tenant' => tenant('id')]) }}" class="nav1-link {{ str_contains(request()->url(), 'event') == true ? 'active' : '' }}">
          <span class="material-icons-round nav-icon">event</span>
          <span class="nav-label" id="nav-events">Events</span>
        </a>
      </li>

      <li class="nav1-item">
        <div class="nav1-link">
          <span class="material-icons-round nav-icon">chat</span>
          <span class="nav-label" id="nav-message">Message</span>
        </div>
      </li>
            
      <li class="nav1-item">
        <div class="nav1-link" onclick="toggleNav1(this)">
          <span class="material-icons-round nav-icon">bar_chart</span>
          <span class="nav-label" id="nav-reports">Reports</span>
          <span class="material-icons-round nav-arrow">expand_more</span>
        </div>
        <div class="nav2-collapse">
          <ul>
            <li class="nav2-item"><div class="nav2-link"><span class="nav2-icon">G</span><span class="nav2-label" id="nav-student">Student Reports</span></div></li>
            <li class="nav2-item"><div class="nav2-link"><span class="nav2-icon">T</span><span class="nav2-label" id="nav-fees">Fees Reports</span></div></li>
            <li class="nav2-item"><div class="nav2-link"><span class="nav2-icon">N</span><span class="nav2-label" id="nav-financial">Financial Reports</span></div></li>
            <li class="nav2-item"><div class="nav2-link"><span class="nav2-icon">N</span><span class="nav2-label" id="nav-attendance">Attendance Reports</span></div></li>
            <li class="nav2-item"><div class="nav2-link"><span class="nav2-icon">N</span><span class="nav2-label" id="nav-human">Human Resource</span></div></li>
            <li class="nav2-item"><div class="nav2-link"><span class="nav2-icon">N</span><span class="nav2-label" id="nav-examination">Examination</span></div></li>
            <li class="nav2-item"><div class="nav2-link"><span class="nav2-icon">N</span><span class="nav2-label" id="nav-inventory">Inventory</span></div></li>
          </ul>
        </div>
      </li>
    </ul>

  </div>
</aside>