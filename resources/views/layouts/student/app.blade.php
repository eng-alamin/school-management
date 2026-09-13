{{-- resources/views/layouts/student/app.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ $title ?? config('app.name') }}</title>

  <link rel="shortcut icon" href="{{ ($logo = setting('system_logo')) ? asset('storage/'.$logo) : asset('assets/img/default-logo.png') }}">
  
  <!-- Bootstrap CSS -->
  <link href="{{asset('assets/vendor/bootstrap/css/bootstrap.min.css')}}" rel="stylesheet"/>
  <!-- Dropzone CSS -->
  <link href="{{asset('assets/vendor/dropzone/dropzone.min.css')}}" type="text/css" rel="stylesheet"/>
  <!-- Theme CSS -->
  <link rel="stylesheet" href="{{asset('assets/css/theme.css')}}"/>
  @stack('styles')
  @livewireStyles
</head>
<body>

{{-- SIDEBAR OVERLAY (mobile) --}}
<div class="sidebar-overlay" id="sidebarOverlay"></div>

{{-- START SIDEBAR --}}
@include('layouts.student.sidebar')
{{-- START SIDEBAR --}}

<div class="main-wrap">

  <!-- START TOP NAV -->
  @include('layouts.student.header')
  <!-- END TOP NAV -->

  <div class="page-body">
    {{ $slot }}
  </div>

  <!-- START FOOTER -->
  @include('layouts.student.footer')
  <!-- END FOOTER -->

</div>

<!-- ═══════ Toast Container ═══════ -->
<div class="toast-container" id="toastContainer"></div>


<!-- Bootstrap JS -->
<script src="{{asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js')}}"></script>
<!-- Dropzone JS -->
<script src="{{asset('assets/vendor/dropzone/dropzone.min.js')}}"></script>
<!-- Theme JS -->
<script src="{{asset('assets/js/theme.js')}}"></script>
<script type="module" src="{{asset('assets/js/lang/lang.js')}}"></script>

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
@stack('scripts')
@livewireScripts
</body>
</html>
