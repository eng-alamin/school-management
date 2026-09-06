<aside class="sidebar no-print" id="mainSidebar">

  <!-- Brand -->
  <div class="sidebar-brand">
    <div class="brand-icon">
      <span class="material-icons-round">dashboard</span>
    </div>
    <div class="brand-text">
      <div class="brand-name">Branch Dashboard</div>
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
    <a href="{{ route('branch.profile.overview') }}"><span class="ud-icon">MP</span> <span id="ud-profile">My Profile</span></a>
    <a href="{{ route('branch.profile.setting') }}"><span class="ud-icon">S</span> <span id="ud-settings">Settings</span></a>
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
        <a href="{{route('branch.dashboard') }}" class="nav1-link {{ str_contains(request()->url(), 'dashboard') == true ? 'active' : '' }}">
          <span class="material-icons-round nav-icon">space_dashboard</span>
          <span class="nav-label" id="nav-dashboard">Dashboard</span>
        </a>
      </li>

      @if(feature_enabled(\App\Support\Feature::INVENTORY_MODULE))
        <li class="nav1-item">
          <div class="nav1-link {{ str_contains(request()->url(), 'inventory/') == true ? 'active open' : '' }}" onclick="toggleNav1(this)">
            <span class="material-icons-round nav-icon">inventory_2</span>
            <span class="nav-label" id="nav-inventory">Inventory</span>
            <span class="material-icons-round nav-arrow">expand_more</span>
          </div>
          <div class="nav2-collapse {{ str_contains(request()->url(), 'inventory/') == true ? 'show' : '' }}">
            <ul>
              <li class="nav2-item"><a href="{{ route('branch.inventory.products') }}" class="nav2-link {{ Route::is('branch.inventory.products') == true ? 'active' : '' }}"><span class="nav2-icon">C</span><span class="nav2-label" id="nav-products">Products</span></a></li>
              <li class="nav2-item"><a href="{{route('branch.inventory.stores') }}" class="nav2-link {{ Route::is('branch.inventory.stores') == true ? 'active' : '' }}"><span class="nav2-icon">C</span><span class="nav2-label" id="nav-stores">Stores</span></a></li>
              <li class="nav2-item"><a href="{{route('branch.inventory.suppliers') }}" class="nav2-link {{ Route::is('branch.inventory.suppliers') == true ? 'active' : '' }}"><span class="nav2-icon">C</span><span class="nav2-label" id="nav-suppliers">Suppliers</span></a></li>
              <li class="nav2-item"><a href="{{route('branch.inventory.purchase.list') }}" class="nav2-link {{ str_contains(request()->url(), 'purchase') == true ? 'active' : '' }}"><span class="nav2-icon">C</span><span class="nav2-label" id="nav-purchases">Purchases</span></a></li>
              <li class="nav2-item"><a href="{{route('branch.inventory.sale.list') }}" class="nav2-link {{ str_contains(request()->url(), 'sale') == true ? 'active' : '' }}"><span class="nav2-icon">C</span><span class="nav2-label" id="nav-sales">Sales</span></a></li>
            </ul>
          </div>
        </li>
      @endif

      <li class="nav1-item">
        <div class="nav1-link {{ Route::is('branch.student.add') == true ? 'active open' : '' }}" onclick="toggleNav1(this)">
          <span class="material-icons-round nav-icon">how_to_reg</span>
          <span class="nav-label" id="nav-admission">Admission</span>
          <span class="material-icons-round nav-arrow">expand_more</span>
        </div>
        <div class="nav2-collapse {{ Route::is('branch.student.add') || Route::is('branch.admission.online') == true ? 'show' : '' }}">
          <ul>
            <li class="nav2-item"><a href="{{route('branch.student.add') }}" class="nav2-link {{ Route::is('branch.student.add') == true ? 'active' : '' }}"><span class="nav2-icon">C</span><span class="nav2-label" id="nav-create-admission">Create Admission</span></a></li>
            <li class="nav2-item"><a href="{{ route('branch.admission.online') }}" class="nav2-link {{ Route::is('branch.admission.online') == true ? 'active' : '' }}"><span class="nav2-icon">O</span><span class="nav2-label" id="nav-online-admission">Online Admission</span></a></li>
          </ul>
        </div>
      </li>

      <li class="nav1-item">
        <div class="nav1-link {{ Route::is('branch.student.list', 'branch.student.edit') == true ? 'active open' : '' }}" onclick="toggleNav1(this)">
          <span class="material-icons-round nav-icon">school</span>
          <span class="nav-label" id="nav-students">Students</span>
          <span class="material-icons-round nav-arrow">expand_more</span>
        </div>
        <div class="nav2-collapse {{ Route::is('branch.student.list', 'branch.student.edit') == true ? 'show' : '' }}">
          <ul>
            <li class="nav2-item"><a href="{{route('branch.student.list') }}" class="nav2-link {{ Route::is('branch.student.list') == true ? 'active' : '' }}"><span class="nav2-icon">C</span><span class="nav2-label" id="nav-student-list">Student List</span></a></li>
          </ul>
        </div>
      </li>

      <li class="nav1-item">
        <a class="nav1-link {{ str_contains(request()->url(), 'branch/parent/') == true ? 'active' : '' }}" href="{{route('branch.parent.list') }}">
          <span class="material-icons-round nav-icon">groups</span>
          <span class="nav-label" id="nav-parents">Parents</span>
        </a>
      </li>

      <li class="nav1-item">
        <div class="nav1-link {{ str_contains(request()->url(), '/branch/employee/') == true ? 'active open' : '' }}" onclick="toggleNav1(this)">
          <span class="material-icons-round nav-icon">badge</span>
          <span class="nav-label" id="nav-employees">Employees</span>
          <span class="material-icons-round nav-arrow">expand_more</span>
        </div>
        <div class="nav2-collapse {{ str_contains(request()->url(), '/branch/employee/') == true ? 'show' : '' }}">
          <ul>
            <li class="nav2-item"><a href="{{route('branch.employee.list') }}" class="nav2-link {{ request()->is('branch/employee/list', 'employee/add', 'employee/edit/*') ? 'active' : '' }}"><span class="nav2-icon">L</span><span class="nav2-label" id="nav-employee-list">Employee List</span></a></li>
            <li class="nav2-item"><a href="{{route('branch.employee.departments') }}" class="nav2-link {{ str_contains(request()->url(), 'branch/employee/departments') == true ? 'active' : '' }}"><span class="nav2-icon">D</span><span class="nav2-label" id="nav-department">Department</span></a></li>
            <li class="nav2-item"><a href="{{route('branch.employee.designations') }}" class="nav2-link {{ str_contains(request()->url(), 'branch/employee/designations') == true ? 'active' : '' }}"><span class="nav2-icon">D</span><span class="nav2-label" id="nav-designation">Designation</span></a></li>
          </ul>
        </div>
      </li>

      <li class="nav1-item">
        <div class="nav1-link {{ str_contains(request()->url(), 'academic') == true ? 'active open' : '' }}" onclick="toggleNav1(this)">
          <span class="material-icons-round nav-icon">menu_book</span>
          <span class="nav-label" id="nav-academic">Academic</span>
          <span class="material-icons-round nav-arrow">expand_more</span>
        </div>
        <div class="nav2-collapse {{ str_contains(request()->url(), 'academic') == true ? 'show' : '' }}">
          <ul>
            <li class="nav2-item"><a href="{{route('branch.academic.sessions') }}" class="nav2-link {{ str_contains(request()->url(), 'branch/academic/sessions') == true ? 'active' : '' }}"><span class="nav2-icon">S</span><span class="nav2-label" id="nav-sessions">Sessions</span></a></li>
            <li class="nav2-item"><a href="{{route('branch.academic.classes') }}" class="nav2-link {{ str_contains(request()->url(), 'branch/academic/classes') || str_contains(request()->url(), 'branch/academic/sections') || str_contains(request()->url(), 'branch/academic/groups') == true ? 'active' : '' }}"><span class="nav2-icon">C</span><span class="nav2-label" id="nav-class-section">Class Setup</span></a></li>
            <li class="nav2-item"><a href="{{route('branch.academic.class-assign') }}" class="nav2-link {{ str_contains(request()->url(), 'branch/academic/class-assign') == true ? 'active' : '' }}"><span class="nav2-icon">C</span><span class="nav2-label" id="nav-class-assign">Class Assign</span></a></li>
            <li class="nav2-item"><a href="{{route('branch.academic.class-schedule.list') }}" class="nav2-link {{ str_contains(request()->url(), 'branch/academic/class-schedule') == true ? 'active' : '' }}"><span class="nav2-icon">C</span><span class="nav2-label" id="nav-class-schedule">Class Schedule</span></a></li>
            <li class="nav2-item"><a href="{{route('branch.academic.teacher-schedule') }}" class="nav2-link {{ str_contains(request()->url(), 'branch/academic/teacher-schedule') == true ? 'active' : '' }}"><span class="nav2-icon">T</span><span class="nav2-label" id="nav-teacher-schedule">Teacher Schedule</span></a></li>
            <li class="nav2-item"><a href="{{route('branch.academic.substitute-teacher') }}" class="nav2-link {{ str_contains(request()->url(), 'branch/academic/substitute-teacher') == true ? 'active' : '' }}"><span class="nav2-icon">S</span><span class="nav2-label" id="nav-substitute-teacher">Substitute Teacher</span></a></li>
            <li class="nav2-item"><a href="{{route('branch.academic.student-promotion') }}" class="nav2-link {{ str_contains(request()->url(), 'branch/academic/student-promotion') == true ? 'active' : '' }}"><span class="nav2-icon">S</span><span class="nav2-label" id="nav-student-promotion">Student Promotion</span></a></li>
            <li class="nav2-item"><a href="{{route('branch.academic.student-enrollment') }}" class="nav2-link {{ str_contains(request()->url(), 'branch/academic/student-enrollment') == true ? 'active' : '' }}"><span class="nav2-icon">S</span><span class="nav2-label" id="nav-student-enrollment">Student Enrollment</span></a></li>
          </ul>
        </div>
      </li>

      <li class="nav1-item">
        <a class="nav1-link {{ str_contains(request()->url(), 'branch/homework') == true ? 'active' : '' }}" href="{{ route('branch.homework.list')  }}">
          <span class="material-icons-round nav-icon">assignment</span>
          <span class="nav-label" id="nav-homework">Home Work</span>
        </a>
      </li>

      <li class="nav1-item">
        <div class="nav1-link {{ str_contains(request()->url(), 'branch/question-papers') == true ? 'active open' : '' }}" onclick="toggleNav1(this)">
          <span class="material-icons-round nav-icon">quiz</span>
          <span class="nav-label" id="nav-question-paper">Question Paper</span>
          <span class="material-icons-round nav-arrow">expand_more</span>
        </div>
        <div class="nav2-collapse {{ str_contains(request()->url(), 'branch/question-papers') == true ? 'show' : '' }}">
          <ul>
            <li class="nav2-item"><a href="{{route('branch.question-papers.index') }}" class="nav2-link {{ str_contains(request()->url(), 'branch/question-papers') == true ? 'active' : '' }}"><span class="nav2-icon">G</span><span class="nav2-label" id="nav-question-papers">Question Papers</span></a></li>
            <li class="nav2-item"><a href="{{route('branch.question-papers.print.authorization') }}" class="nav2-link {{ str_contains(request()->url(), 'branch/question-papers/print') == true ? 'active' : '' }}"><span class="nav2-icon">T</span><span class="nav2-label" id="nav-print-authorization">Print Authorization</span></a></li>
          </ul>
        </div>
      </li>

      <li class="nav1-item">
        <div class="nav1-link {{ str_contains(request()->url(), 'exam/') == true ? 'active open' : '' }}" onclick="toggleNav1(this)">
          <span class="material-icons-round nav-icon">quiz</span>
          <span class="nav-label" id="nav-exam-master">Exam Master</span>
          <span class="material-icons-round nav-arrow">expand_more</span>
        </div>
        <div class="nav2-collapse {{ str_contains(request()->url(), 'exam') == true ? 'show' : '' }}">
          <ul>
            <li class="nav2-item"><a href="{{route('branch.exam.setups') }}" class="nav2-link {{ str_contains(request()->url(), 'branch/exam/setups') == true ? 'active' : '' }}"><span class="nav2-icon">G</span><span class="nav2-label" id="nav-exam-setup">Exam Setup</span></a></li>
            <li class="nav2-item"><a href="{{route('branch.exam.schedule.list') }}" class="nav2-link {{ str_contains(request()->url(), 'branch/exam/schedule') == true ? 'active' : '' }}"><span class="nav2-icon">T</span><span class="nav2-label" id="nav-exam-schedule">Exam Schedule</span></a></li>
            <li class="nav2-item"><a href="{{route('branch.exam.entries') }}" class="nav2-link {{ str_contains(request()->url(), 'branch/exam/entries') == true ? 'active' : '' }}"><span class="nav2-icon">T</span><span class="nav2-label" id="nav-mark-entries">Mark Entries</span></a></li>
            <li class="nav2-item"><a href="{{route('branch.exam.positions') }}" class="nav2-link {{ str_contains(request()->url(), 'branch/exam/positions') == true ? 'active' : '' }}"><span class="nav2-icon">T</span><span class="nav2-label" id="nav-exam-positions">Generate Positions</span></a></li>
            <li class="nav2-item"><a href="{{route('branch.exam.grades') }}" class="nav2-link {{ str_contains(request()->url(), 'branch/exam/grades') == true ? 'active' : '' }}"><span class="nav2-icon">T</span><span class="nav2-label" id="nav-grades-range">Grades Range</span></a></li>
          </ul>
        </div>
      </li>

      <li class="nav1-item">
        <div class="nav1-link {{ str_contains(request()->url(), '/branch/attendance/') == true ? 'active open' : '' }}" onclick="toggleNav1(this)">
          <span class="material-icons-round nav-icon">event_available</span>
          <span class="nav-label" id="nav-attendance">Attendance</span>
          <span class="material-icons-round nav-arrow">expand_more</span>
        </div>
        <div class="nav2-collapse {{ str_contains(request()->url(), '/branch/attendance/') == true ? 'show' : '' }}">
          <ul>
            <li class="nav2-item"><a href="{{route('branch.attendance.students') }}" class="nav2-link {{ str_contains(request()->url(), '/branch/attendance/students') == true ? 'active' : '' }}"><span class="nav2-icon">G</span><span class="nav2-label" id="nav-attendance-student">Student</span></a></li>
            <li class="nav2-item"><a href="{{route('branch.attendance.employees') }}" class="nav2-link {{ str_contains(request()->url(), '/branch/attendance/employees') == true ? 'active' : '' }}"><span class="nav2-icon">G</span><span class="nav2-label" id="nav-attendance-employee">Employee</span></a></li>
            <li class="nav2-item"><a href="{{route('branch.attendance.exams') }}" class="nav2-link {{ str_contains(request()->url(), '/branch/attendance/exams') == true ? 'active' : '' }}"><span class="nav2-icon">G</span><span class="nav2-label" id="nav-attendance-exam">Exam</span></a></li>
            <li class="nav2-item"><a href="{{route('branch.attendance.duty-assign') }}" class="nav2-link {{ str_contains(request()->url(), '/branch/attendance/duty-assign') == true ? 'active' : '' }}"><span class="nav2-icon">G</span><span class="nav2-label" id="nav-attendance-duty-assign">Attendance Duty Assign</span></a></li>
            <li class="nav2-item"><a href="{{route('branch.attendance.exam-duty-assign') }}" class="nav2-link {{ str_contains(request()->url(), '/branch/attendance/exam-duty-assign') == true ? 'active' : '' }}"><span class="nav2-icon">G</span><span class="nav2-label" id="nav-exam-duty-assign">Exam Duty Assign</span></a></li>
          </ul>
        </div>
      </li>

      <li class="nav1-item">
        <div class="nav1-link {{ str_contains(request()->url(), '/branch/biometric/') == true ? 'active open' : '' }}" onclick="toggleNav1(this)">
          <span class="material-icons-round nav-icon">fingerprint</span>
          <span class="nav-label" id="nav-biometric">Biometric</span>
          <span class="material-icons-round nav-arrow">expand_more</span>
        </div>
        <div class="nav2-collapse {{ str_contains(request()->url(), '/biometric/') == true ? 'show' : '' }}">
          <ul>
            <li class="nav2-item"><a href="{{route('branch.biometric.devices') }}" class="nav2-link {{ str_contains(request()->url(), '/branch/biometric/devices') == true ? 'active' : '' }}"><span class="nav2-icon">BD</span><span class="nav2-label" id="nav-biometric-devices">Biometric Devices</span></a></li>
            <li class="nav2-item"><a href="{{route('branch.biometric.mapping.index') }}" class="nav2-link {{ str_contains(request()->url(), '/branch/biometric/mapping/') == true ? 'active' : '' }}"><span class="nav2-icon">DUM</span><span class="nav2-label" id="nav-biometric-device-mappings">Device User Mappings</span></a></li>
          </ul>
        </div>
      </li>

      <li class="nav1-item">
        <div class="nav1-link {{ str_contains(request()->url(), 'branch/student-accounting') == true ? 'active open' : '' }}" onclick="toggleNav1(this)">
          <span class="material-icons-round nav-icon">account_balance_wallet</span>
          <span class="nav-label" id="nav-student-accounting">Student Accounting</span>
          <span class="material-icons-round nav-arrow">expand_more</span>
        </div>
        <div class="nav2-collapse {{ str_contains(request()->url(), 'student-accounting') == true ? 'show' : '' }}">
          <ul>
            <li class="nav2-item"><a href="{{route('branch.student-accounting.fee.types') }}" class="nav2-link {{ Route::is('branch.student-accounting.fee.types') == true ? 'active' : '' }}"><span class="nav2-icon">T</span><span class="nav2-label" id="nav-fees-type">Fees Type</span></a></li>
            <li class="nav2-item"><a href="{{route('branch.student-accounting.fee.setups') }}" class="nav2-link {{ Route::is('branch.student-accounting.fee.setups') == true ? 'active' : '' }}"><span class="nav2-icon">S</span><span class="nav2-label" id="nav-fees-setup">Fees Setup</span></a></li>
            <li class="nav2-item"><a href="{{route('branch.student-accounting.fee.fines') }}" class="nav2-link {{ Route::is('branch.student-accounting.fee.fines') == true ? 'active' : '' }}"><span class="nav2-icon">S</span><span class="nav2-label" id="nav-fine-setup">Fine Setup</span></a></li>
            <li class="nav2-item"><a href="{{route('branch.student-accounting.student.fines') }}" class="nav2-link {{ Route::is('branch.student-accounting.student.fines') == true ? 'active' : '' }}"><span class="nav2-icon">F</span><span class="nav2-label" id="nav-student-fine">Student Fine</span></a></li>
            <li class="nav2-item"><a href="{{route('branch.student-accounting.fee.invoices') }}" class="nav2-link {{ Route::is('branch.student-accounting.fee.invoices') == true ? 'active' : '' }}"><span class="nav2-icon">I</span><span class="nav2-label" id="nav-fees-pay-invoice">Fees Pay / Invoice</span></a></li>
          </ul>
        </div>
      </li>

      <li class="nav1-item">
        <div class="nav1-link {{ str_contains(request()->url(), 'branch/office-accounting') == true ? 'active open' : '' }}" onclick="toggleNav1(this)">
          <span class="material-icons-round nav-icon">business_center</span>
          <span class="nav-label" id="nav-office-accounting">Office Accounting</span>
          <span class="material-icons-round nav-arrow">expand_more</span>
        </div>
        <div class="nav2-collapse {{ str_contains(request()->url(), 'branch/office-accounting') == true ? 'show' : '' }}">
          <ul>
            <li class="nav2-item"><a href="{{route('branch.office-accounting.accounts') }}" class="nav2-link {{ str_contains(request()->url(), '/branch/office-accounting/accounts') == true ? 'active' : '' }}"><span class="nav2-icon">A</span><span class="nav2-label" id="nav-account">Account</span></a></li>
            <li class="nav2-item"><a href="{{route('branch.office-accounting.deposit.list') }}" class="nav2-link {{ str_contains(request()->url(), '/branch/office-accounting/deposit-list') == true ? 'active' : '' }}"><span class="nav2-icon">D</span><span class="nav2-label" id="nav-deposit">Deposit</span></a></li>
            <li class="nav2-item"><a href="{{route('branch.office-accounting.expense.list') }}" class="nav2-link {{ str_contains(request()->url(), '/branch/office-accounting/expense-list') == true ? 'active' : '' }}"><span class="nav2-icon">E</span><span class="nav2-label" id="nav-expense">Expense</span></a></li>
            <li class="nav2-item"><a href="{{route('branch.office-accounting.transactions') }}" class="nav2-link {{ str_contains(request()->url(), '/branch/office-accounting/transactions') == true ? 'active' : '' }}"><span class="nav2-icon">T</span><span class="nav2-label" id="nav-transactions">Transactions</span></a></li>
            <li class="nav2-item"><a href="{{route('branch.office-accounting.heads') }}" class="nav2-link {{ str_contains(request()->url(), '/branch/office-accounting/voucher-head') == true ? 'active' : '' }}"><span class="nav2-icon">H</span><span class="nav2-label" id="nav-voucher-head">Voucher Head</span></a></li>
          </ul>
        </div>
      </li>

      <li class="nav1-item">
        <div class="nav1-link {{ str_contains(request()->url(), 'salary') ? 'active open' : '' }}" onclick="toggleNav1(this)">
          <span class="material-icons-round nav-icon">manage_accounts</span>
          <span class="nav-label" id="nav-salary-management">Salary Management</span>
          <span class="material-icons-round nav-arrow">expand_more</span>
        </div>
        <div class="nav2-collapse {{ str_contains(request()->url(), 'salary') == true ? 'show' : '' }}">
          <ul>
            <li class="nav2-item"><a href="{{route('branch.salary.list-template') }}" class="nav2-link {{ str_contains(request()->url(), '/branch/salary/list-template') == true ? 'active' : '' }}"><span class="nav2-icon">T</span><span class="nav2-label" id="nav-salary-template">Salary Template</span></a></li>
            <li class="nav2-item"><a href="{{route('branch.salary.assign') }}" class="nav2-link {{ str_contains(request()->url(), '/branch/salary/assign') == true ? 'active' : '' }}"><span class="nav2-icon">A</span><span class="nav2-label" id="nav-salary-assign">Salary Assign</span></a></li>
            <li class="nav2-item"><a href="{{route('branch.salary.payment') }}" class="nav2-link {{ str_contains(request()->url(), '/branch/salary/payment') == true ? 'active' : '' }}"><span class="nav2-icon">P</span><span class="nav2-label" id="nav-salary-payment">Salary Payment</span></a></li>
            <li class="nav2-item"><a href="{{route('branch.salary.advance') }}" class="nav2-link {{ str_contains(request()->url(), '/branch/salary/advance') == true ? 'active' : '' }}"><span class="nav2-icon">P</span><span class="nav2-label" id="nav-salary-advance">Salary Advance</span></a></li>
          </ul>
        </div>
      </li>

      @if(feature_enabled(\App\Support\Feature::CARD_MODULE))
        <li class="nav1-item">
          <div class="nav1-link {{ str_contains(request()->url(), 'card/') == true ? 'active open' : '' }}" onclick="toggleNav1(this)">
            <span class="material-icons-round nav-icon">credit_card</span>
            <span class="nav-label" id="nav-card-management">Card Management</span>
            <span class="material-icons-round nav-arrow">expand_more</span>
          </div>
          <div class="nav2-collapse {{ str_contains(request()->url(), 'card/') == true ? 'show' : '' }}">
            <ul>
              <li class="nav2-item"><a href="{{route('branch.card.id-card-templates') }}" class="nav2-link {{ str_contains(request()->url(), '/branch/card/id-card-templates') == true ? 'active' : '' }}"><span class="nav2-icon">G</span><span class="nav2-label" id="nav-id-card-template">Id Card Template</span></a></li>
              <li class="nav2-item"><a href="{{route('branch.card.student-id-cards') }}" class="nav2-link {{ str_contains(request()->url(), '/branch/card/student-id-cards') == true ? 'active' : '' }}"><span class="nav2-icon">T</span><span class="nav2-label" id="nav-student-id-card">Student Id Card</span></a></li>
              <li class="nav2-item"><a href="{{route('branch.card.employee-id-cards') }}" class="nav2-link {{ str_contains(request()->url(), '/branch/card/employee-id-cards') == true ? 'active' : '' }}"><span class="nav2-icon">N</span><span class="nav2-label" id="nav-employee-id-card">Employee Id Card</span></a></li>
              <li class="nav2-item"><a href="{{route('branch.card.admit-card-templates') }}" class="nav2-link {{ str_contains(request()->url(), '/branch/card/admit-card-templates') == true ? 'active' : '' }}"><span class="nav2-icon">N</span><span class="nav2-label" id="nav-admit-card-template">Admit Card Template</span></a></li>
              <li class="nav2-item"><a href="{{route('branch.card.generate-admit-cards') }}" class="nav2-link {{ str_contains(request()->url(), '/branch/card/generate-admit-cards') == true ? 'active' : '' }}"><span class="nav2-icon">N</span><span class="nav2-label" id="nav-admit-card-generate">Admit Card Generate</span></a></li>
            </ul>
          </div>
        </li>
      @endif

      @if(feature_enabled(\App\Support\Feature::CERTIFICATE_MODULE))
        <li class="nav1-item">
          <div class="nav1-link {{ str_contains(request()->url(), 'certificate/') == true ? 'active open' : '' }}" onclick="toggleNav1(this)">
            <span class="material-icons-round nav-icon">workspace_premium</span>
            <span class="nav-label" id="nav-certificate-management">Certificate Management</span>
            <span class="material-icons-round nav-arrow">expand_more</span>
          </div>
          <div class="nav2-collapse {{ str_contains(request()->url(), 'certificate/') == true ? 'show' : '' }}">
            <ul>
              <li class="nav2-item"><a href="{{route('branch.certificate.list-template') }}" class="nav2-link {{ str_contains(request()->url(), '/branch/certificate/list-template') == true ? 'active' : '' }}"><span class="nav2-icon">G</span><span class="nav2-label" id="nav-certificate-template">Certificate Template</span></a></li>
              <li class="nav2-item"><a href="{{route('branch.certificate.generate-student') }}" class="nav2-link {{ str_contains(request()->url(), '/branch/certificate/generate-student') == true ? 'active' : '' }}"><span class="nav2-icon">T</span><span class="nav2-label" id="nav-generate-student">Generate Student</span></a></li>
              <li class="nav2-item"><a href="{{route('branch.certificate.generate-employee') }}" class="nav2-link {{ str_contains(request()->url(), '/branch/certificate/generate-employee') == true ? 'active' : '' }}"><span class="nav2-icon">N</span><span class="nav2-label" id="nav-generate-employee">Generate Employee</span></a></li>
            </ul>
          </div>
        </li>
      @endif

      <li class="nav1-item">
        <a href="{{route('branch.leave.applications') }}" class="nav1-link {{ str_contains(request()->url(), 'leave/applications') || str_contains(request()->url(), 'leave/categories') == true ? 'active' : '' }}">
          <span class="material-icons-round nav-icon">exit_to_app</span>
          <span class="nav-label" id="nav-leaves">Leaves</span>
        </a>
      </li>

      <li class="nav1-item">
        <a href="{{route('branch.event.list') }}" class="nav1-link {{ str_contains(request()->url(), '/branch/event') == true ? 'active' : '' }}">
          <span class="material-icons-round nav-icon">event</span>
          <span class="nav-label" id="nav-events">Events</span>
        </a>
      </li>

      <li class="nav1-item">
        <a href="{{route('branch.mailbox.inbox') }}" class="nav1-link {{ str_contains(request()->url(), '/branch/mailbox/') == true ? 'active' : '' }}">
          <span class="material-icons-round nav-icon">chat</span>
          <span class="nav-label" id="nav-mailbox">Mailbox</span>
        </a>
      </li>

      <li class="nav1-item">
        <a href="{{route('branch.notices') }}" class="nav1-link {{ str_contains(request()->url(), '/branch/notices') == true ? 'active' : '' }}">
          <span class="material-icons-round nav-icon">chat</span>
          <span class="nav-label" id="nav-notices">Notices</span>
        </a>
      </li>

      <li class="nav1-item">
        <a href="{{route('branch.notifications.index') }}" class="nav1-link {{ str_contains(request()->url(), '/branch/notifications') == true ? 'active' : '' }}">
          <span class="material-icons-round nav-icon">notifications</span>
          <span class="nav-label" id="nav-notifications">Notifications</span>
        </a>
      </li>
       
      <li class="nav1-item">
        <div class="nav1-link {{ str_contains(request()->url(), '/branch/reports') == true ? 'active open' : '' }}" onclick="toggleNav1(this)">
          <span class="material-icons-round nav-icon">bar_chart</span>
          <span class="nav-label" id="nav-reports">Reports</span>
          <span class="material-icons-round nav-arrow">expand_more</span>
        </div>
        <div class="nav2-collapse {{ str_contains(request()->url(), '/branch/reports') == true ? 'show' : '' }}">
          <ul>
            <li class="nav2-item"><a href="{{ route('branch.reports.attendances') }}" class="nav2-link {{ str_contains(request()->url(), '/branch/reports/attendances') == true ? 'active' : '' }}"><span class="nav2-icon">A</span><span class="nav2-label" id="nav-attendances">Attendances</span></a></li>
          </ul>
        </div>
      </li>

      <li class="nav1-item">
        <div class="nav1-link {{ str_contains(request()->url(), 'activity-logs') || str_contains(request()->url(), '/branch/session-logs') || str_contains(request()->url(), '/branch/login-logs') == true ? 'active open' : '' }}" onclick="toggleNav1(this)">
          <span class="material-icons-round nav-icon">security</span>
          <span class="nav-label" id="nav-audit-security">Audit & Security Logs</span>
          <span class="material-icons-round nav-arrow">expand_more</span>
        </div>
        <div class="nav2-collapse {{ str_contains(request()->url(), '/branch/activity-logs') || str_contains(request()->url(), '/branch/session-logs') || str_contains(request()->url(), '/branch/login-logs') == true ? 'show' : '' }}">
          <ul>
            <li class="nav2-item"><a href="{{ route('branch.activitylog') }}" class="nav2-link {{ Route::is('branch.activitylog') == true ? 'active' : '' }}"><span class="nav2-icon">AL</span><span class="nav2-label" id="nav-activity-logs">Activity Logs</span></a></li>
            <li class="nav2-item"><a href="{{ route('branch.sessionlog') }}" class="nav2-link {{ Route::is('branch.sessionlog') == true ? 'active' : '' }}"><span class="nav2-icon">SL</span><span class="nav2-label" id="nav-session-logs">Session Logs</span></a></li>
            <li class="nav2-item"><a href="{{ route('branch.loginlog') }}" class="nav2-link {{ Route::is('branch.loginlog') == true ? 'active' : '' }}"><span class="nav2-icon">LL</span><span class="nav2-label" id="nav-login-logs">Login Logs</span></a></li>
          </ul>
        </div>
      </li>

      <li class="nav1-item">
        <div class="nav1-link {{ request()->is('/branch/roles-permissions/*') ? 'active open' : '' }}" onclick="toggleNav1(this)">
          <span class="material-icons-round nav-icon">door_front</span>
          <span class="nav-label" id="nav-roles-permission">Role & Permission</span>
          <span class="material-icons-round nav-arrow">expand_more</span>
        </div>
        <div class="nav2-collapse {{ request()->is('/branch/roles-permissions/*') ? 'show' : '' }}">
          <ul>
            <li class="nav2-item"><a href="{{route('branch.role-permission.index') }}" class="nav2-link {{ str_contains(request()->url(), '/branch/roles-permissions/index') == true ? 'active' : '' }}"><span class="nav2-icon">I</span><span class="nav2-label" id="nav-committees">Index</span></a></li>
            <li class="nav2-item"><a href="{{route('branch.role-permission.create') }}" class="nav2-link {{ str_contains(request()->url(), '/branch/roles-permissions/create') == true ? 'active' : '' }}"><span class="nav2-icon">C</span><span class="nav2-label" id="nav-facilities">Create</span></a></li>
            <li class="nav2-item"><a href="{{route('branch.role-permission.users') }}" class="nav2-link {{ str_contains(request()->url(), '/branch/roles-permissions/users') == true ? 'active' : '' }}"><span class="nav2-icon">U</span><span class="nav2-label" id="nav-facilities">Users</span></a></li>
          </ul>
        </div>
      </li>

    </ul>

  </div>
</aside>