<ul class="nav nav-pills mb-3 att-type-pills">
    <li class="nav-item">
        <a href="{{ route('admin.reports.inventory.stock') }}" class="nav-link {{ Route::is('admin.reports.inventory.stock') == true ? 'active' : '' }}">
            <span class="material-icons-round" style="font-size:1rem;vertical-align:middle;">badge</span>
            Stock
        </a>
    </li>
    <li class="nav-item">
        <a href="{{ route('admin.reports.inventory.sales') }}" class="nav-link {{ Route::is('admin.reports.inventory.sales') == true ? 'active' : '' }}">
            <span class="material-icons-round" style="font-size:1rem;vertical-align:middle;">badge</span>
            Sales
        </a>
    </li>
    <li class="nav-item">
        <a href="{{ route('admin.reports.inventory.purchases') }}" class="nav-link {{ Route::is('admin.reports.inventory.purchases') == true ? 'active' : '' }}">
            <span class="material-icons-round" style="font-size:1rem;vertical-align:middle;">badge</span>
            Purchases
        </a>
    </li>
</ul>