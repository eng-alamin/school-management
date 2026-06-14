  <nav class="topnav no-print">
    <button class="topnav-toggle" id="sidebarToggle">
      <span class="material-icons-round">menu</span>
    </button>

    <div class="breadcrumb-wrap">
      <!-- <div class="breadcrumb-title" id="pageTitleEl">Pages</div> -->
    </div>

    <div class="topnav-right topnav-controls">
      <!-- Language Switch -->
      <div class="toggle-pill" id="langToggle">
        <button class="active" onclick="setLang('en', this)">EN</button>
        <button onclick="setLang('bn', this)">বাং</button>
      </div>

      <!-- Dark Mode Toggle -->
      <button class="dark-toggle-btn" id="darkToggleBtn" onclick="toggleDark()" title="Toggle Dark Mode">
        <span class="material-icons-round" id="darkIcon">dark_mode</span>
      </button>

      <!-- Settings -->
      <div class="topnav-dropdown-wrap">
        <button class="icon-btn" id="settingsBtn" title="Settings" onclick="toggleDropdown('settingsDropdown', event)">
          <span class="material-icons-round">settings</span>
        </button>
        <div class="topnav-dropdown" id="settingsDropdown" style="min-width:280px">
          <div class="settings-header"><h6>Quick Settings</h6></div>

          <div class="settings-item">
            <div class="settings-item-left">
              <span class="material-icons-round">dark_mode</span>
              <div>
                <div class="settings-item-label">Dark Mode</div>
                <div class="settings-item-sub">Switch to dark theme</div>
              </div>
            </div>
            <label class="sw">
              <input type="checkbox" id="darkModeSwitch" onchange="toggleDark()">
              <span class="sw-track"></span>
            </label>
          </div>

          <div class="settings-item">
            <div class="settings-item-left">
              <span class="material-icons-round">notifications</span>
              <div>
                <div class="settings-item-label">Notifications</div>
                <div class="settings-item-sub">Push notifications</div>
              </div>
            </div>
            <label class="sw">
              <input type="checkbox" checked>
              <span class="sw-track"></span>
            </label>
          </div>

          <div class="settings-item">
            <div class="settings-item-left">
              <span class="material-icons-round">language</span>
              <div>
                <div class="settings-item-label">Language</div>
                <div class="settings-item-sub">English / বাংলা</div>
              </div>
            </div>
            <div class="toggle-pill" style="transform:scale(.85);transform-origin:right center">
              <button id="settingsLangEN" class="active" onclick="setLangFromSettings('en')">EN</button>
              <button id="settingsLangBN" onclick="setLangFromSettings('bn')">বাং</button>
            </div>
          </div>

          <div class="settings-item">
            <div class="settings-item-left">
              <span class="material-icons-round">lock</span>
              <div>
                <div class="settings-item-label">Privacy Mode</div>
                <div class="settings-item-sub">Hide sensitive data</div>
              </div>
            </div>
            <label class="sw">
              <input type="checkbox">
              <span class="sw-track"></span>
            </label>
          </div>
        </div>
      </div>

      <!-- Notifications -->
      @livewire('notification-bell')

      <!-- Profile Avatar -->
      <div class="topnav-dropdown-wrap">
        <img src="{{ auth()->user()->avatar ? asset(auth()->user()->avatar) : asset('assets/img/default-avatar.jpg') }}" class="topnav-avatar" alt="{{ auth()->user()->name}}" onclick="toggleDropdown('profileDropdown', event)" style="cursor:pointer"/>
        <div class="topnav-dropdown" id="profileDropdown" style="min-width:220px">
          <div class="profile-dropdown-header">
            <img src="{{ auth()->user()->avatar ? asset(auth()->user()->avatar) : asset('assets/img/default-avatar.jpg') }}" alt="{{ auth()->user()->name}}">
            <div>
              <div class="pd-name">{{ auth()->user()->name}}</div>
              <div class="pd-email">{{ auth()->user()->email}}</div>
            </div>
          </div>
          <a href="{{ route('student.profile.overview') }}" class="pd-menu-item"><span class="material-icons-round">person</span> My Profile</a>
          <a href="{{ route('student.profile.setting') }}" class="pd-menu-item"><span class="material-icons-round">edit</span> Edit Profile</a>
          {{-- <div class="pd-menu-item"><span class="material-icons-round">receipt_long</span> Billing</div>
          <div class="pd-menu-item"><span class="material-icons-round">settings</span> Account Settings</div> --}}
          <a href="{{route('logout') }} " class="pd-menu-item danger" onclick="event.preventDefault(); document.getElementById('logout-form').submit()">
              <span class="material-icons-round">logout</span> Logout
          </a>
          <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">
              @csrf
          </form>
        </div>
      </div>
    </div>

    <!-- Backdrop to close dropdowns -->
    <div class="dropdown-backdrop" id="dropdownBackdrop" onclick="closeAllDropdowns()"></div>
  </nav>