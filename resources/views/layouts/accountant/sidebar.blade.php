<aside class="sidebar no-print" id="mainSidebar">

  <!-- Brand -->
  <div class="sidebar-brand">
    <div class="brand-icon">
      <span class="material-icons-round">dashboard</span>
    </div>
    <div class="brand-text">
      <div class="brand-name">Accountant Dashboard</div>
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
    <a href="{{ route('accountant.profile.overview') }}"><span class="ud-icon">MP</span> <span id="ud-profile">My Profile</span></a>
    <a href="{{ route('accountant.profile.setting') }}"><span class="ud-icon">S</span> <span id="ud-settings">Settings</span></a>
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
        <a href="{{route('accountant.dashboard') }}" class="nav1-link {{ str_contains(request()->url(), 'dashboard') == true ? 'active' : '' }}">
          <span class="material-icons-round nav-icon">space_dashboard</span>
          <span class="nav-label" id="nav-dashboards">Dashboard</span>
        </a>
      </li>

      <li class="nav1-item">
        <div class="nav1-link {{ str_contains(request()->url(), 'accountant/inventory/') == true ? 'active open' : '' }}" onclick="toggleNav1(this)">
          <span class="material-icons-round nav-icon">inventory_2</span>
          <span class="nav-label" id="nav-inventory">Inventory</span>
          <span class="material-icons-round nav-arrow">expand_more</span>
        </div>
        <div class="nav2-collapse {{ str_contains(request()->url(), 'accountant/inventory/') == true ? 'show' : '' }}">
          <ul>
            <li class="nav2-item"><a href="{{ route('accountant.inventory.products') }}" class="nav2-link {{ Route::is('accountant.inventory.products') == true ? 'active' : '' }}"><span class="nav2-icon">C</span><span class="nav2-label" id="nav-products">Products</span></a></li>
            <li class="nav2-item"><a href="{{route('accountant.inventory.stores') }}" class="nav2-link {{ Route::is('accountant.inventory.stores') == true ? 'active' : '' }}"><span class="nav2-icon">C</span><span class="nav2-label" id="nav-products">Stores</span></a></li>
            <li class="nav2-item"><a href="{{route('accountant.inventory.suppliers') }}" class="nav2-link {{ Route::is('accountant.inventory.suppliers') == true ? 'active' : '' }}"><span class="nav2-icon">C</span><span class="nav2-label" id="nav-products">Suppliers</span></a></li>
            <li class="nav2-item"><a href="{{route('accountant.inventory.purchase.list') }}" class="nav2-link {{ Route::is('accountant.inventory.purchase.list') == true ? 'active' : '' }}"><span class="nav2-icon">C</span><span class="nav2-label" id="nav-products">Purchases</span></a></li>
            <li class="nav2-item"><a href="{{route('accountant.inventory.sale.list') }}" class="nav2-link {{ Route::is('accountant.inventory.sale.list') == true ? 'active' : '' }}"><span class="nav2-icon">C</span><span class="nav2-label" id="nav-products">Sales</span></a></li>
          </ul>
        </div>
      </li>

      <li class="nav1-item">
        <a class="nav1-link {{ Route::is('accountant.student.list', 'accountant.student.edit') == true ? 'active' : '' }}" href="{{route('accountant.student.list') }}">
          <span class="material-icons-round nav-icon">school</span>
          <span class="nav-label" id="nav-students">Students</span>
        </a>
      </li>

      <li class="nav1-item">
        <a class="nav1-link {{ str_contains(request()->url(), 'accountant/parent/') == true ? 'active' : '' }}" href="{{route('accountant.parent.list') }}">
          <span class="material-icons-round nav-icon">groups</span>
          <span class="nav-label" id="nav-parents">Parents</span>
        </a>
      </li>

      {{-- <li class="nav1-item">
        <div class="nav1-link {{ str_contains(request()->url(), 'accountant/employee/') == true ? 'active open' : '' }}" onclick="toggleNav1(this)">
          <span class="material-icons-round nav-icon">badge</span>
          <span class="nav-label" id="nav-employees">Employees</span>
          <span class="material-icons-round nav-arrow">expand_more</span>
        </div>
        <div class="nav2-collapse {{ str_contains(request()->url(), 'accountant/employee/') == true ? 'show' : '' }}">
          <ul>
            <li class="nav2-item"><a href="{{route('accountant.employee.list') }}" class="nav2-link {{ request()->is('accountant/employee/list', 'accountant/employee/add', 'accountant/employee/edit/*') ? 'active' : '' }}"><span class="nav2-icon">L</span><span class="nav2-label" id="nav-employee-list">Employee List</span></a></li>
            <li class="nav2-item"><a href="{{route('accountant.employee.departments') }}" class="nav2-link {{ str_contains(request()->url(), 'accountant/employee/departments') == true ? 'active' : '' }}"><span class="nav2-icon">D</span><span class="nav2-label" id="nav-department">Department</span></a></li>
            <li class="nav2-item"><a href="{{route('accountant.employee.designations') }}" class="nav2-link {{ str_contains(request()->url(), 'accountant/employee/designations') == true ? 'active' : '' }}"><span class="nav2-icon">D</span><span class="nav2-label" id="nav-designation">Designation</span></a></li>
          </ul>
        </div>
      </li> --}}

      {{-- <li class="nav1-item">
        <div class="nav1-link {{ str_contains(request()->url(), 'accountant/card/') == true ? 'active open' : '' }}" onclick="toggleNav1(this)">
          <span class="material-icons-round nav-icon">credit_card</span>
          <span class="nav-label" id="nav-card-management">Card Management</span>
          <span class="material-icons-round nav-arrow">expand_more</span>
        </div>
        <div class="nav2-collapse {{ str_contains(request()->url(), 'accountant/card/') == true ? 'show' : '' }}">
          <ul>
            <li class="nav2-item"><a href="{{route('accountant.card.id-card-templates') }}" class="nav2-link {{ str_contains(request()->url(), 'accountant/card/id-card-templates') == true ? 'active' : '' }}"><span class="nav2-icon">G</span><span class="nav2-label" id="nav-idcard-template">Id Card Templete</span></a></li>
            <li class="nav2-item"><a href="{{route('accountant.card.student-id-cards') }}" class="nav2-link {{ str_contains(request()->url(), 'accountant/card/student-id-cards') == true ? 'active' : '' }}"><span class="nav2-icon">T</span><span class="nav2-label" id="nav-student-id-card">Student Id Card</span></a></li>
            <li class="nav2-item"><a href="{{route('accountant.card.employee-id-cards') }}" class="nav2-link {{ str_contains(request()->url(), 'accountant/card/employee-id-cards') == true ? 'active' : '' }}"><span class="nav2-icon">N</span><span class="nav2-label" id="nav-employee-id-card">Employee Id Card</span></a></li>
            <li class="nav2-item"><a href="{{route('accountant.card.admit-card-templates') }}" class="nav2-link {{ str_contains(request()->url(), 'accountant/card/admit-card-templates') == true ? 'active' : '' }}"><span class="nav2-icon">N</span><span class="nav2-label" id="nav-admit-card-template">Admit Card Templete</span></a></li>
            <li class="nav2-item"><a href="{{route('accountant.card.generate-admit-cards') }}" class="nav2-link {{ str_contains(request()->url(), 'accountant/card/generate-admit-cards') == true ? 'active' : '' }}"><span class="nav2-icon">N</span><span class="nav2-label" id="nav-admit-card-generate">Admit Card Generate</span></a></li>
          </ul>
        </div>
      </li> --}}

      {{-- <li class="nav1-item">
        <div class="nav1-link {{ str_contains(request()->url(), 'accountant/certificate/') == true ? 'active open' : '' }}" onclick="toggleNav1(this)">
          <span class="material-icons-round nav-icon">workspace_premium</span>
          <span class="nav-label" id="nav-certificate">Certificate</span>
          <span class="material-icons-round nav-arrow">expand_more</span>
        </div>
        <div class="nav2-collapse {{ str_contains(request()->url(), 'accountant/certificate/') == true ? 'show' : '' }}">
          <ul>
            <li class="nav2-item"><a href="{{route('accountant.certificate.list-template') }}" class="nav2-link {{ str_contains(request()->url(), 'accountant/certificate/list-template') == true ? 'active' : '' }}"><span class="nav2-icon">G</span><span class="nav2-label" id="nav-idcard-template">Certificate Templete</span></a></li>
            <li class="nav2-item"><a href="{{route('accountant.certificate.generate-student') }}" class="nav2-link {{ str_contains(request()->url(), 'accountant/certificate/generate-student') == true ? 'active' : '' }}"><span class="nav2-icon">T</span><span class="nav2-label" id="nav-student-id-card">Generate Student</span></a></li>
            <li class="nav2-item"><a href="{{route('accountant.certificate.generate-employee') }}" class="nav2-link {{ str_contains(request()->url(), 'accountant/certificate/generate-employee') == true ? 'active' : '' }}"><span class="nav2-icon">N</span><span class="nav2-label" id="nav-employee-id-card">Generate Employee</span></a></li>
          </ul>
        </div>
      </li> --}}

      <li class="nav1-item">
        <div class="nav1-link {{ str_contains(request()->url(), 'accountant/salary') || str_contains(request()->url(), 'accountant/leave') ? 'active open' : '' }}" onclick="toggleNav1(this)">
          <span class="material-icons-round nav-icon">manage_accounts</span>
          <span class="nav-label" id="nav-human-resource">Human Resource</span>
          <span class="material-icons-round nav-arrow">expand_more</span>
        </div>
        <div class="nav2-collapse {{ str_contains(request()->url(), 'accountant/salary') || str_contains(request()->url(), 'accountant/leave') == true ? 'show' : '' }}">
          <ul>
            <li class="nav2-item"><a href="{{route('accountant.salary.list-template') }}" class="nav2-link {{ str_contains(request()->url(), 'accountant/salary/list-template') == true ? 'active' : '' }}"><span class="nav2-icon">T</span><span class="nav2-label" id="nav-salary-template">Salary Template</span></a></li>
            <li class="nav2-item"><a href="{{route('accountant.salary.assign') }}" class="nav2-link {{ str_contains(request()->url(), 'accountant/salary/assign') == true ? 'active' : '' }}"><span class="nav2-icon">A</span><span class="nav2-label" id="nav-salary-assign">Salary Assign</span></a></li>
            <li class="nav2-item"><a href="{{route('accountant.salary.payment') }}" class="nav2-link {{ str_contains(request()->url(), 'accountant/salary/payment') == true ? 'active' : '' }}"><span class="nav2-icon">P</span><span class="nav2-label" id="nav-salary-payment">Salary Payment</span></a></li>
            <li class="nav2-item"><a href="{{route('accountant.leave.applications') }}" class="nav2-link {{ str_contains(request()->url(), 'accountant/leave') == true ? 'active' : '' }}"><span class="nav2-icon">P</span><span class="nav2-label" id="nav-leaves">Leaves</span></a></li>
          </ul>
        </div>
      </li>

      <li class="nav1-item">
        <div class="nav1-link {{ str_contains(request()->url(), 'accountant/student-accounting') == true ? 'active open' : '' }}" onclick="toggleNav1(this)">
          <span class="material-icons-round nav-icon">account_balance_wallet</span>
          <span class="nav-label" id="nav-student-accounting">Student Accounting</span>
          <span class="material-icons-round nav-arrow">expand_more</span>
        </div>
        <div class="nav2-collapse {{ str_contains(request()->url(), 'accountant/student-accounting') == true ? 'show' : '' }}">
          <ul>
            <li class="nav2-item"><a href="{{route('accountant.student-accounting.fee.types') }}" class="nav2-link {{ str_contains(request()->url(), 'accountant/student-accounting/fee-types') == true ? 'active' : '' }}"><span class="nav2-icon">G</span><span class="nav2-label" id="nav-fees-type">Fees Type</span></a></li>
            <li class="nav2-item"><a href="{{route('accountant.student-accounting.fee.groups') }}" class="nav2-link {{ str_contains(request()->url(), 'accountant/student-accounting/fee-groups') == true ? 'active' : '' }}"><span class="nav2-icon">T</span><span class="nav2-label" id="nav-fees-group">Fees Group</span></a></li>
            <li class="nav2-item"><a href="{{route('accountant.student-accounting.fee.fines') }}" class="nav2-link {{ str_contains(request()->url(), 'accountant/student-accounting/fee-fines') == true ? 'active' : '' }}"><span class="nav2-icon">N</span><span class="nav2-label" id="nav-fine-setup">Fine Setup</span></a></li>
            <li class="nav2-item"><a href="{{route('accountant.student-accounting.fee.allocations') }}" class="nav2-link {{ str_contains(request()->url(), 'accountant/student-accounting/fee-allocations') == true ? 'active' : '' }}"><span class="nav2-icon">N</span><span class="nav2-label" id="nav-fees-allocation">Fees Allocation</span></a></li>
            <li class="nav2-item"><a href="{{route('accountant.student-accounting.fee.invoices') }}" class="nav2-link {{ str_contains(request()->url(), 'accountant/student-accounting/fee-invoices') == true ? 'active' : '' }}"><span class="nav2-icon">N</span><span class="nav2-label" id="nav-fees-pay-invoice">Fees Pay / Invoice</span></a></li>
          </ul>
        </div>
      </li>

      <li class="nav1-item">
        <div class="nav1-link {{ str_contains(request()->url(), 'accountant/office-accounting') == true ? 'active open' : '' }}" onclick="toggleNav1(this)">
          <span class="material-icons-round nav-icon">business_center</span>
          <span class="nav-label" id="nav-office-accounting">Office Accounting</span>
          <span class="material-icons-round nav-arrow">expand_more</span>
        </div>
        <div class="nav2-collapse {{ str_contains(request()->url(), 'accountant/office-accounting') == true ? 'show' : '' }}">
          <ul>
            <li class="nav2-item"><a href="{{route('accountant.office-accounting.accounts') }}" class="nav2-link {{ str_contains(request()->url(), 'accountant/office-accounting/accounts') == true ? 'active' : '' }}"><span class="nav2-icon">A</span><span class="nav2-label" id="nav-account">Account</span></a></li>
            <li class="nav2-item"><a href="{{route('accountant.office-accounting.deposit.list') }}" class="nav2-link {{ str_contains(request()->url(), 'accountant/office-accounting/voucher-deposit-list') == true ? 'active' : '' }}"><span class="nav2-icon">D</span><span class="nav2-label" id="nav-new-deposit">Deposit</span></a></li>
            <li class="nav2-item"><a href="{{route('accountant.office-accounting.expense.list') }}" class="nav2-link {{ str_contains(request()->url(), 'accountant/office-accounting/voucher-expense-list') == true ? 'active' : '' }}"><span class="nav2-icon">E</span><span class="nav2-label" id="nav-new-expense">Expense</span></a></li>
            <li class="nav2-item"><a href="{{route('accountant.office-accounting.transactions') }}" class="nav2-link {{ str_contains(request()->url(), 'accountant/office-accounting/transactions') == true ? 'active' : '' }}"><span class="nav2-icon">T</span><span class="nav2-label" id="nav-all-transactions">Transactions</span></a></li>
            <li class="nav2-item"><a href="{{route('accountant.office-accounting.heads') }}" class="nav2-link {{ str_contains(request()->url(), 'accountant/office-accounting/voucher-head') == true ? 'active' : '' }}"><span class="nav2-icon">H</span><span class="nav2-label" id="nav-voucher-head">Voucher Head</span></a></li>
          </ul>
        </div>
      </li>

      <li class="nav1-item">
        <a href="{{route('accountant.event.list') }}" class="nav1-link {{ str_contains(request()->url(), 'accountant/event') == true ? 'active' : '' }}">
          <span class="material-icons-round nav-icon">event</span>
          <span class="nav-label" id="nav-events">Events</span>
        </a>
      </li>

      <li class="nav1-item">
        <a href="{{route('accountant.mailbox.inbox') }}" class="nav1-link {{ str_contains(request()->url(), 'accountant/mailbox/') == true ? 'active' : '' }}">
          <span class="material-icons-round nav-icon">chat</span>
          <span class="nav-label" id="nav-mailbox">Mailbox</span>
        </a>
      </li>

      <li class="nav1-item">
        <a href="{{route('accountant.notice') }}" class="nav1-link {{ str_contains(request()->url(), 'accountant/notice-board') == true ? 'active' : '' }}">
          <span class="material-icons-round nav-icon">chat</span>
          <span class="nav-label" id="nav-notices">Notice</span>
        </a>
      </li>
      
      <li class="nav1-item">
        <a href="{{route('accountant.notifications.index') }}" class="nav1-link {{ str_contains(request()->url(), 'accountant/notifications') == true ? 'active' : '' }}">
          <span class="material-icons-round nav-icon">chat</span>
          <span class="nav-label" id="nav-notifications">Notifications</span>
        </a>
      </li>
            
      {{-- <li class="nav1-item">
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
      </li> --}}
      
    </ul>

  </div>
</aside>