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
      @can('dashboard.view')
      <li class="nav1-item">
        <a href="{{route('accountant.dashboard') }}" class="nav1-link {{ str_contains(request()->url(), 'dashboard') == true ? 'active' : '' }}">
          <span class="material-icons-round nav-icon">space_dashboard</span>
          <span class="nav-label" id="nav-dashboard">Dashboard</span>
        </a>
      </li>
      @endcan

      @if(feature_enabled(\App\Support\Feature::INVENTORY_MODULE) && auth()->user()->hasAnyPermission([
          'inventory_product.view', 'inventory_store.view', 'inventory_supplier.view',
          'inventory_purchase.view', 'inventory_sale.view',
      ]))
        <li class="nav1-item">
          <div class="nav1-link {{ str_contains(request()->url(), 'accountant/inventory/') == true ? 'active open' : '' }}" onclick="toggleNav1(this)">
            <span class="material-icons-round nav-icon">inventory_2</span>
            <span class="nav-label" id="nav-inventory">Inventory</span>
            <span class="material-icons-round nav-arrow">expand_more</span>
          </div>
          <div class="nav2-collapse {{ str_contains(request()->url(), 'accountant/inventory/') == true ? 'show' : '' }}">
            <ul>
              @can('inventory_product.view')
              <li class="nav2-item"><a href="{{ route('accountant.inventory.products') }}" class="nav2-link {{ Route::is('accountant.inventory.products') == true ? 'active' : '' }}"><span class="nav2-icon">C</span><span class="nav2-label" id="nav-products">Products</span></a></li>
              @endcan
              @can('inventory_store.view')
              <li class="nav2-item"><a href="{{route('accountant.inventory.stores') }}" class="nav2-link {{ Route::is('accountant.inventory.stores') == true ? 'active' : '' }}"><span class="nav2-icon">C</span><span class="nav2-label" id="nav-products">Stores</span></a></li>
              @endcan
              @can('inventory_supplier.view')
              <li class="nav2-item"><a href="{{route('accountant.inventory.suppliers') }}" class="nav2-link {{ Route::is('accountant.inventory.suppliers') == true ? 'active' : '' }}"><span class="nav2-icon">C</span><span class="nav2-label" id="nav-products">Suppliers</span></a></li>
              @endcan
              @can('inventory_purchase.view')
              <li class="nav2-item"><a href="{{route('accountant.inventory.purchase.list') }}" class="nav2-link {{ Route::is('accountant.inventory.purchase.list') == true ? 'active' : '' }}"><span class="nav2-icon">C</span><span class="nav2-label" id="nav-products">Purchases</span></a></li>
              @endcan
              @can('inventory_sale.view')
              <li class="nav2-item"><a href="{{route('accountant.inventory.sale.list') }}" class="nav2-link {{ Route::is('accountant.inventory.sale.list') == true ? 'active' : '' }}"><span class="nav2-icon">C</span><span class="nav2-label" id="nav-products">Sales</span></a></li>
              @endcan
            </ul>
          </div>
        </li>
      @endif

      @can('student.view')
      <li class="nav1-item">
        <a class="nav1-link {{ Route::is('accountant.student.list', 'accountant.student.edit') == true ? 'active' : '' }}" href="{{route('accountant.student.list') }}">
          <span class="material-icons-round nav-icon">school</span>
          <span class="nav-label" id="nav-students">Students</span>
        </a>
      </li>
      @endcan

      @can('parent.view')
      <li class="nav1-item">
        <a class="nav1-link {{ str_contains(request()->url(), 'accountant/parent/') == true ? 'active' : '' }}" href="{{route('accountant.parent.list') }}">
          <span class="material-icons-round nav-icon">groups</span>
          <span class="nav-label" id="nav-parents">Parents</span>
        </a>
      </li>
      @endcan

      @can('employee.view')
      <li class="nav1-item">
        <a class="nav1-link {{ str_contains(request()->url(), 'accountant/employee/') == true ? 'active' : '' }}" href="{{route('accountant.parent.list') }}">
          <span class="material-icons-round nav-icon">groups</span>
          <span class="nav-label" id="nav-employees">Employees</span>
        </a>
      </li>
      @endcan

      @if(auth()->user()->hasAnyPermission([
          'student_accounting_fee_type.view', 'student_accounting_fee_setup.view',
          'student_accounting_fee_fine.view', 'student_accounting_student_fine.view',
          'student_accounting_fee_invoice.view',
      ]))
      <li class="nav1-item">
        <div class="nav1-link {{ str_contains(request()->url(), 'accountant/student-accounting') == true ? 'active open' : '' }}" onclick="toggleNav1(this)">
          <span class="material-icons-round nav-icon">account_balance_wallet</span>
          <span class="nav-label" id="nav-student-accounting">Student Accounting</span>
          <span class="material-icons-round nav-arrow">expand_more</span>
        </div>
        <div class="nav2-collapse {{ str_contains(request()->url(), 'student-accounting') == true ? 'show' : '' }}">
          <ul>
            @can('student_accounting_fee_type.view')
            <li class="nav2-item"><a href="{{route('accountant.student-accounting.fee.types') }}" class="nav2-link {{ Route::is('accountant.student-accounting.fee.types') == true ? 'active' : '' }}"><span class="nav2-icon">T</span><span class="nav2-label" id="nav-fees-type">Fees Type</span></a></li>
            @endcan
            @can('student_accounting_fee_setup.view')
            <li class="nav2-item"><a href="{{route('accountant.student-accounting.fee.setups') }}" class="nav2-link {{ Route::is('accountant.student-accounting.fee.setups') == true ? 'active' : '' }}"><span class="nav2-icon">S</span><span class="nav2-label" id="nav-fees-setup">Fees Setup</span></a></li>
            @endcan
            @can('student_accounting_fee_fine.view')
            <li class="nav2-item"><a href="{{route('accountant.student-accounting.fee.fines') }}" class="nav2-link {{ Route::is('accountant.student-accounting.fee.fines') == true ? 'active' : '' }}"><span class="nav2-icon">S</span><span class="nav2-label" id="nav-fine-setup">Fine Setup</span></a></li>
            @endcan
            @can('student_accounting_student_fine.view')
            <li class="nav2-item"><a href="{{route('accountant.student-accounting.student.fines') }}" class="nav2-link {{ Route::is('accountant.student-accounting.student.fines') == true ? 'active' : '' }}"><span class="nav2-icon">F</span><span class="nav2-label" id="nav-student-fine">Student Fine</span></a></li>
            @endcan
            @can('student_accounting_fee_invoice.view')
            <li class="nav2-item"><a href="{{route('accountant.student-accounting.fee.invoices') }}" class="nav2-link {{ Route::is('accountant.student-accounting.fee.invoices') == true ? 'active' : '' }}"><span class="nav2-icon">I</span><span class="nav2-label" id="nav-fees-pay-invoice">Fees Pay / Invoice</span></a></li>
            @endcan
          </ul>
        </div>
      </li>
      @endif

      @if(auth()->user()->hasAnyPermission([
          'office_accounting_account.view', 'office_accounting_deposit.view',
          'office_accounting_expense.view', 'office_accounting_transaction.view',
          'office_accounting_head.view',
      ]))
      <li class="nav1-item">
        <div class="nav1-link {{ str_contains(request()->url(), 'accountant/office-accounting') == true ? 'active open' : '' }}" onclick="toggleNav1(this)">
          <span class="material-icons-round nav-icon">business_center</span>
          <span class="nav-label" id="nav-office-accounting">Office Accounting</span>
          <span class="material-icons-round nav-arrow">expand_more</span>
        </div>
        <div class="nav2-collapse {{ str_contains(request()->url(), 'accountant/office-accounting') == true ? 'show' : '' }}">
          <ul>
            @can('office_accounting_account.view')
            <li class="nav2-item"><a href="{{route('accountant.office-accounting.accounts') }}" class="nav2-link {{ str_contains(request()->url(), '/accountant/office-accounting/accounts') == true ? 'active' : '' }}"><span class="nav2-icon">A</span><span class="nav2-label" id="nav-account">Account</span></a></li>
            @endcan
            @can('office_accounting_deposit.view')
            <li class="nav2-item"><a href="{{route('accountant.office-accounting.deposit.list') }}" class="nav2-link {{ str_contains(request()->url(), '/accountant/office-accounting/deposit-list') == true ? 'active' : '' }}"><span class="nav2-icon">D</span><span class="nav2-label" id="nav-deposit">Deposit</span></a></li>
            @endcan
            @can('office_accounting_expense.view')
            <li class="nav2-item"><a href="{{route('accountant.office-accounting.expense.list') }}" class="nav2-link {{ str_contains(request()->url(), '/accountant/office-accounting/expense-list') == true ? 'active' : '' }}"><span class="nav2-icon">E</span><span class="nav2-label" id="nav-expense">Expense</span></a></li>
            @endcan
            @can('office_accounting_transaction.view')
            <li class="nav2-item"><a href="{{route('accountant.office-accounting.transactions') }}" class="nav2-link {{ str_contains(request()->url(), '/accountant/office-accounting/transactions') == true ? 'active' : '' }}"><span class="nav2-icon">T</span><span class="nav2-label" id="nav-transactions">Transactions</span></a></li>
            @endcan
            @can('office_accounting_head.view')
            <li class="nav2-item"><a href="{{route('accountant.office-accounting.heads') }}" class="nav2-link {{ str_contains(request()->url(), '/accountant/office-accounting/voucher-head') == true ? 'active' : '' }}"><span class="nav2-icon">H</span><span class="nav2-label" id="nav-voucher-head">Voucher Head</span></a></li>
            @endcan
          </ul>
        </div>
      </li>
      @endif

      @if(auth()->user()->hasAnyPermission(['salary_template.view', 'salary_assign.view', 'salary_payment.view', 'salary_advance.view',]))
        <li class="nav1-item">
          <div class="nav1-link {{ str_contains(request()->url(), 'salary') ? 'active open' : '' }}" onclick="toggleNav1(this)">
            <span class="material-icons-round nav-icon">manage_accounts</span>
            <span class="nav-label" id="nav-salary-management">Salary Management</span>
            <span class="material-icons-round nav-arrow">expand_more</span>
          </div>
          <div class="nav2-collapse {{ str_contains(request()->url(), 'salary') == true ? 'show' : '' }}">
            <ul>
              @can('salary_template.view')
              <li class="nav2-item"><a href="{{route('accountant.salary.list-template') }}" class="nav2-link {{ str_contains(request()->url(), '/accountant/salary/list-template') == true ? 'active' : '' }}"><span class="nav2-icon">T</span><span class="nav2-label" id="nav-salary-template">Salary Template</span></a></li>
              @endcan
              @can('salary_assign.view')
              <li class="nav2-item"><a href="{{route('accountant.salary.assign') }}" class="nav2-link {{ str_contains(request()->url(), '/accountant/salary/assign') == true ? 'active' : '' }}"><span class="nav2-icon">A</span><span class="nav2-label" id="nav-salary-assign">Salary Assign</span></a></li>
              @endcan
              @can('salary_payment.view')
              <li class="nav2-item"><a href="{{route('accountant.salary.payment') }}" class="nav2-link {{ str_contains(request()->url(), '/accountant/salary/payment') == true ? 'active' : '' }}"><span class="nav2-icon">P</span><span class="nav2-label" id="nav-salary-payment">Salary Payment</span></a></li>
              @endcan
              @can('salary_advance.view')
              <li class="nav2-item"><a href="{{route('accountant.salary.advance') }}" class="nav2-link {{ str_contains(request()->url(), '/accountant/salary/advance') == true ? 'active' : '' }}"><span class="nav2-icon">P</span><span class="nav2-label" id="nav-salary-advance">Salary Advance</span></a></li>
              @endcan
            </ul>
          </div>
        </li>
      @endif

      @can('leave_application.view')
      <li class="nav1-item">
        <a href="{{route('accountant.leave.applications') }}" class="nav1-link {{ str_contains(request()->url(), 'accountant/leave/applications') || str_contains(request()->url(), 'accountant/leave/categories') == true ? 'active' : '' }}">
          <span class="material-icons-round nav-icon">exit_to_app</span>
          <span class="nav-label" id="nav-leaves">Leaves</span>
        </a>
      </li>
      @endcan

      @can('event.view')
      <li class="nav1-item">
        <a href="{{route('accountant.event.list') }}" class="nav1-link {{ str_contains(request()->url(), 'accountant/event') == true ? 'active' : '' }}">
          <span class="material-icons-round nav-icon">event</span>
          <span class="nav-label" id="nav-events">Events</span>
        </a>
      </li>
      @endcan

      @can('mailbox.view')
      <li class="nav1-item">
        <a href="{{route('accountant.mailbox.inbox') }}" class="nav1-link {{ str_contains(request()->url(), 'accountant/mailbox/') == true ? 'active' : '' }}">
          <span class="material-icons-round nav-icon">chat</span>
          <span class="nav-label" id="nav-mailbox">Mailbox</span>
        </a>
      </li>
      @endcan

      @can('notice.view')
      <li class="nav1-item">
        <a href="{{route('accountant.notices') }}" class="nav1-link {{ str_contains(request()->url(), 'accountant/notices') == true ? 'active' : '' }}">
          <span class="material-icons-round nav-icon">chat</span>
          <span class="nav-label" id="nav-notices">Notices</span>
        </a>
      </li>
      @endcan

      @can('notification.view')
      <li class="nav1-item">
        <a href="{{route('accountant.notifications.index') }}" class="nav1-link {{ str_contains(request()->url(), 'accountant/notifications') == true ? 'active' : '' }}">
          <span class="material-icons-round nav-icon">chat</span>
          <span class="nav-label" id="nav-notifications">Notifications</span>
        </a>
      </li>
      @endcan

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