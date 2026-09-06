<ul class="nav nav-pills mb-3 att-type-pills">
    <li class="nav-item">
        <a href="{{ route('admin.reports.attendances.student') }}" class="nav-link {{ Route::is('admin.reports.attendances.student') == true ? 'active' : '' }}">
            <span class="material-icons-round" style="font-size:1rem;vertical-align:middle;">badge</span>
            Students
        </a>
    </li>
    <li class="nav-item">
        <a href="{{ route('admin.reports.attendances.employee') }}" class="nav-link {{ Route::is('admin.reports.attendances.employee') == true ? 'active' : '' }}">
            <span class="material-icons-round" style="font-size:1rem;vertical-align:middle;">badge</span>
            Employees
        </a>
    </li>
    <li class="nav-item">
        <a href="{{ route('admin.reports.attendances.exam') }}" class="nav-link {{ Route::is('admin.reports.attendances.exam') == true ? 'active' : '' }}">
            <span class="material-icons-round" style="font-size:1rem;vertical-align:middle;">badge</span>
            Exams
        </a>
    </li>
</ul>