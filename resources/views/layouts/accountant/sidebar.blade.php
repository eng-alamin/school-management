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
    <a href="{{ route('accountant.profile.overview', ['tenant' => tenant('id')]) }}"><span class="ud-icon">MP</span> <span id="ud-profile">My Profile</span></a>
    <a href="{{ route('accountant.profile.setting', ['tenant' => tenant('id')]) }}"><span class="ud-icon">S</span> <span id="ud-settings">Settings</span></a>
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
        <a class="nav1-link {{ Route::is('accountant.dashboard') == true ? 'active' : '' }}" href="{{route('accountant.dashboard', ['tenant' => tenant('id')]) }}">
          <span class="material-icons-round nav-icon">space_dashboard</span>
          <span class="nav-label" id="nav-dashboard">Dashboard</span>
        </a>
      </li>
      <li class="nav1-item">
        <div class="nav1-link {{ str_contains(request()->url(), 'salary') || str_contains(request()->url(), 'leave') ? 'active open' : '' }}" onclick="toggleNav1(this)">
          <span class="material-icons-round nav-icon">manage_accounts</span>
          <span class="nav-label" id="nav-human-resource">Human Resource</span>
          <span class="material-icons-round nav-arrow">expand_more</span>
        </div>
        <div class="nav2-collapse {{ str_contains(request()->url(), 'salary') || str_contains(request()->url(), 'leave') == true ? 'show' : '' }}">
          <ul>
            <li class="nav2-item"><a href="{{route('accountant.salary.list-template', ['tenant' => tenant('id')]) }}" class="nav2-link {{ str_contains(request()->url(), 'accountant.salary.list-template') == true ? 'active' : '' }}"><span class="nav2-icon">T</span><span class="nav2-label" id="nav-idcard-template">Salary Template</span></a></li>
            <li class="nav2-item"><a href="{{route('accountant.salary.assign', ['tenant' => tenant('id')]) }}" class="nav2-link {{ str_contains(request()->url(), 'accountant.salary.assign') == true ? 'active' : '' }}"><span class="nav2-icon">A</span><span class="nav2-label" id="nav-idcard-template">Salary Assign</span></a></li>
            <li class="nav2-item"><a href="{{route('accountant.salary.payment', ['tenant' => tenant('id')]) }}" class="nav2-link {{ str_contains(request()->url(), 'accountant.salary.payment') == true ? 'active' : '' }}"><span class="nav2-icon">P</span><span class="nav2-label" id="nav-idcard-template">Salary Payment</span></a></li>
            <li class="nav2-item"><a href="{{route('accountant.leave.applications', ['tenant' => tenant('id')]) }}" class="nav2-link {{ str_contains(request()->url(), 'leave') == true ? 'active' : '' }}"><span class="nav2-icon">P</span><span class="nav2-label" id="nav-idcard-template">Leaves</span></a></li>
          </ul>
        </div>
      </li>

      <li class="nav1-item">
        <div class="nav1-link {{ str_contains(request()->url(), 'student-accounting') == true ? 'active open' : '' }}" onclick="toggleNav1(this)">
          <span class="material-icons-round nav-icon">account_balance_wallet</span>
          <span class="nav-label" id="nav-student-accounting">Student Accounting</span>
          <span class="material-icons-round nav-arrow">expand_more</span>
        </div>
        <div class="nav2-collapse {{ str_contains(request()->url(), 'student-accounting') == true ? 'show' : '' }}">
          <ul>
            <li class="nav2-item"><a href="{{route('accountant.student-accounting.fee.types', ['tenant' => tenant('id')]) }}" class="nav2-link {{ str_contains(request()->url(), 'student-accounting/fee-types') == true ? 'active' : '' }}"><span class="nav2-icon">G</span><span class="nav2-label" id="nav-fees-type">Fees Type</span></a></li>
            <li class="nav2-item"><a href="{{route('accountant.student-accounting.fee.groups', ['tenant' => tenant('id')]) }}" class="nav2-link {{ str_contains(request()->url(), 'student-accounting/fee-groups') == true ? 'active' : '' }}"><span class="nav2-icon">T</span><span class="nav2-label" id="nav-timeline">Fees Group</span></a></li>
            <li class="nav2-item"><a href="{{route('accountant.student-accounting.fee.fines', ['tenant' => tenant('id')]) }}" class="nav2-link {{ str_contains(request()->url(), 'student-accounting/fee-fines') == true ? 'active' : '' }}"><span class="nav2-icon">N</span><span class="nav2-label" id="nav-fine-setup">Fine Setup</span></a></li>
            <li class="nav2-item"><a href="{{route('accountant.student-accounting.fee.allocations', ['tenant' => tenant('id')]) }}" class="nav2-link {{ str_contains(request()->url(), 'student-accounting/fee-allocations') == true ? 'active' : '' }}"><span class="nav2-icon">N</span><span class="nav2-label" id="nav-fees-allocation">Fees Allocation</span></a></li>
            <li class="nav2-item"><a href="{{route('accountant.student-accounting.fee.invoices', ['tenant' => tenant('id')]) }}" class="nav2-link {{ str_contains(request()->url(), 'student-accounting/fee-invoices') == true ? 'active' : '' }}"><span class="nav2-icon">N</span><span class="nav2-label" id="nav-fees-pay-invoice">Fees Pay / Invoice</span></a></li>
            {{-- <li class="nav2-item"><a href="{{route('accountant.student-accounting.fee.types', ['tenant' => tenant('id')]) }}" class="nav2-link {{ str_contains(request()->url(), 'student-accounting/fee-types') == true ? 'active' : '' }}"><span class="nav2-icon">N</span><span class="nav2-label" id="nav-due-fees-invoice">Due Fees Invoice</span></a></li>
            <li class="nav2-item"><a href="{{route('accountant.student-accounting.fee.types', ['tenant' => tenant('id')]) }}" class="nav2-link {{ str_contains(request()->url(), 'student-accounting/fee-types') == true ? 'active' : '' }}"><span class="nav2-icon">N</span><span class="nav2-label" id="nav-fees-reminder">Fees Reminder</span></a></li> --}}
          </ul>
        </div>
      </li>

      <li class="nav1-item">
        <div class="nav1-link {{ str_contains(request()->url(), 'office-accounting') == true ? 'active open' : '' }}" onclick="toggleNav1(this)">
          <span class="material-icons-round nav-icon">business_center</span>
          <span class="nav-label" id="nav-office-accounting">Office Accounting</span>
          <span class="material-icons-round nav-arrow">expand_more</span>
        </div>
        <div class="nav2-collapse {{ str_contains(request()->url(), 'office-accounting') == true ? 'show' : '' }}">
          <ul>
            <li class="nav2-item"><a href="{{route('accountant.office-accounting.accounts', ['tenant' => tenant('id')]) }}" class="nav2-link {{ str_contains(request()->url(), 'office-accounting/accounts') == true ? 'active' : '' }}"><span class="nav2-icon">A</span><span class="nav2-label" id="nav-account">Account</span></a></li>
            <li class="nav2-item"><a href="{{route('accountant.office-accounting.deposit.list', ['tenant' => tenant('id')]) }}" class="nav2-link {{ str_contains(request()->url(), 'office-accounting/voucher-deposit-list') == true ? 'active' : '' }}"><span class="nav2-icon">D</span><span class="nav2-label" id="nav-new-deposit">Deposit</span></a></li>
            <li class="nav2-item"><a href="{{route('accountant.office-accounting.expense.list', ['tenant' => tenant('id')]) }}" class="nav2-link {{ str_contains(request()->url(), 'office-accounting/voucher-expense-list') == true ? 'active' : '' }}"><span class="nav2-icon">E</span><span class="nav2-label" id="nav-new-expense">Expense</span></a></li>
            <li class="nav2-item"><a href="{{route('accountant.office-accounting.transactions', ['tenant' => tenant('id')]) }}" class="nav2-link {{ str_contains(request()->url(), 'office-accounting/transactions') == true ? 'active' : '' }}"><span class="nav2-icon">T</span><span class="nav2-label" id="nav-all-transactions">Transactions</span></a></li>
            <li class="nav2-item"><a href="{{route('accountant.office-accounting.heads', ['tenant' => tenant('id')]) }}" class="nav2-link {{ str_contains(request()->url(), 'office-accounting/voucher-head') == true ? 'active' : '' }}"><span class="nav2-icon">H</span><span class="nav2-label" id="nav-voucher-head">Voucher Head</span></a></li>
          </ul>
        </div>
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