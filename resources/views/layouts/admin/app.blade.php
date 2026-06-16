{{-- resources/views/layouts/admin/app.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ $title ?? config('app.name') }}</title>

  <!-- Bootstrap 5.3 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"/>
  <!-- Material Icons -->
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet"/>
  <!-- Dropzone CSS -->
  <link rel="stylesheet" href="https://unpkg.com/dropzone@5/dist/min/dropzone.min.css" type="text/css"/>

  <!-- Theme CSS -->
  <link rel="stylesheet" href="{{asset('assets/css/theme.css')}}"/>
  @stack('styles')
  @livewireStyles
</head>
<body>

{{-- SIDEBAR OVERLAY (mobile) --}}
<div class="sidebar-overlay" id="sidebarOverlay"></div>

{{-- START SIDEBAR --}}
@include('layouts.admin.sidebar')
{{-- START SIDEBAR --}}

<div class="main-wrap">

  <!-- START TOP NAV -->
  @include('layouts.admin.header')
  <!-- END TOP NAV -->

  <div class="page-body">
    {{ $slot }}
  </div>

  <!-- START FOOTER -->
  @include('layouts.admin.footer')
  <!-- END FOOTER -->

</div>

<!-- ═══════ Toast Container ═══════ -->
<div class="toast-container" id="toastContainer"></div>


<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- Dropzone JS -->
<script src="https://unpkg.com/dropzone@5/dist/min/dropzone.min.js"></script>
<!-- Theme JS -->
<script src="{{asset('assets/js/theme.js')}}"></script>

<script>
    // Close Modal
    window.addEventListener('closeModal', event => {
      const modal = bootstrap.Modal.getInstance(document.getElementById(event.detail.modalId));
      modal?.hide();
    });
    
    window.addEventListener('openModal', event => {
      const modalEl = document.getElementById(event.detail.modalId);

      let modal = bootstrap.Modal.getInstance(modalEl);
      if (!modal) {
          modal = new bootstrap.Modal(modalEl);
      }

      modal.show();
  });
</script>
<script>
  document.addEventListener("DOMContentLoaded", function () {

      // active menu item
      const activeItem = document.querySelector('.sidebar .active');
      if (activeItem) {
          activeItem.scrollIntoView({
              behavior: 'smooth',
              block: 'center'
          });
      }

  });
</script>


{{-- Start Session for Redirect --}}
@if(session('success'))
<script>
    toast(@json(session('success')), "success");
</script>
@endif

@if(session('error'))
<script>
    toast(@json(session('error')), "error");
</script>
@endif

@if(session('warning'))
<script>
    toast(@json(session('warning')), "warning");
</script>
@endif

@if(session('info'))
<script>
    toast(@json(session('info')), "info");
</script>
@endif
{{-- End Session for Redirect --}}

@stack('scripts')
@livewireScripts
</body>
</html>
