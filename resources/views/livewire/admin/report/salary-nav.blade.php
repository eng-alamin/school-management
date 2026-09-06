<ul class="nav nav-pills mb-3 att-type-pills">
    <li class="nav-item">
        <a href="{{ route('admin.reports.salary.payments') }}" class="nav-link {{ Route::is('admin.reports.salary.payments') == true ? 'active' : '' }}">
            <span class="material-icons-round" style="font-size:1rem;vertical-align:middle;">badge</span>
            Payment
        </a>
    </li>
    <li class="nav-item">
        <a href="{{ route('admin.reports.salary.advances') }}" class="nav-link {{ Route::is('admin.reports.salary.advances') == true ? 'active' : '' }}">
            <span class="material-icons-round" style="font-size:1rem;vertical-align:middle;">badge</span>
            Advances
        </a>
    </li>
    <li class="nav-item">
        <a href="{{ route('admin.reports.salary.advance-repayments') }}" class="nav-link {{ Route::is('admin.reports.salary.advance-repayments') == true ? 'active' : '' }}">
            <span class="material-icons-round" style="font-size:1rem;vertical-align:middle;">badge</span>
            Repayments
        </a>
    </li>
</ul>