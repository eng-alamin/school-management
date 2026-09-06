{{-- resources/views/layouts/admin/app.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ $title ?? config('app.name') }}</title>

  <link rel="shortcut icon" href="{{ institution()->system_logo ? asset('storage/' . institution()->system_logo) : asset('assets/img/default-logo.png') }}">

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

@livewire('chatbox-component')

<!-- ═══════ Toast Container ═══════ -->
<div class="toast-container" id="toastContainer"></div>


<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- Dropzone JS -->
<script src="https://unpkg.com/dropzone@5/dist/min/dropzone.min.js"></script>
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


<script>
    document.addEventListener('livewire:initialized', () => {

        setTimeout(() => {
            requestAnimationFrame(() => {
                initAllFields();
            });
        }, 100);

        Livewire.hook('morph.updated', ({ el }) => {
            requestAnimationFrame(() => {
                initAllFields();
            });
        });

        function initAllFields() {

            // ── 1. Text/Textarea/Number is-filled ──
            document.querySelectorAll('.input-group-outline input, .input-group-outline textarea').forEach(function(input) {
                var group = input.closest('.input-group');
                if (!group) return;
                if (input.value && input.value.trim() !== '') {
                    group.classList.add('is-filled');
                } else {
                    group.classList.remove('is-filled');
                }
                if (input._materialInit) return;
                input._materialInit = true;
                input.addEventListener('focus', function() { group.classList.add('is-focused'); });
                input.addEventListener('blur', function() {
                    group.classList.remove('is-focused');
                    group.classList.toggle('is-filled', !!input.value.trim());
                });
                input.addEventListener('input', function() {
                    group.classList.toggle('is-filled', !!input.value.trim());
                });
            });

            // ── 2. Select is-filled ──
            document.querySelectorAll('.input-group-outline select').forEach(function(select) {
                var group = select.closest('.input-group');
                if (!group) return;
                if (select.value && select.value !== '') {
                    group.classList.add('is-filled');
                } else {
                    group.classList.remove('is-filled');
                }
                if (select._materialInit) return;
                select._materialInit = true;
                select.addEventListener('change', function() {
                    group.classList.toggle('is-filled', !!select.value);
                });
                select.addEventListener('focus', function() { group.classList.add('is-focused'); });
                select.addEventListener('blur', function() { group.classList.remove('is-focused'); });
            });

            // ── 3. Custom Select rebuild ──
            document.querySelectorAll('.input-group-outline .form-select').forEach(function(select) {
                var old = select.parentNode.querySelector('.custom-select-wrapper');
                if (old) old.remove();
                select.style.display = '';
                if (typeof buildCustomSelect === 'function') {
                    buildCustomSelect(select);
                }
            });

            // ── 4. Datepicker ──
            // document.querySelectorAll('.input-group-outline input[type="date"]').forEach(function(input) {
            //     if (input.dataset.dpInit === '1') return;
            //     input.dataset.dpInit = '1';
            //     input.addEventListener('change', function() {
            //         input.dispatchEvent(new Event('input', { bubbles: true }));
            //     });
            //     if (typeof buildDatepicker === 'function') {
            //         buildDatepicker(input);
            //     }
            // });

            // Livewire.on('date-updated', function (event) {
            //     var data = Array.isArray(event) ? event[0] : event;
            //     if (!data || !data.field) return;

            //     var input = document.querySelector(
            //         '.input-group-outline input[type="date"][wire\\:model="' + data.field + '"]'
            //     );
            //     if (!input) return;

            //     var newDate = data.date || '';
            //     if (newDate) {
            //         input.value = newDate;
            //         input.dataset.dpValue = newDate;
            //         if (input._dpTriggerSync) {
            //             input._dpTriggerSync(newDate);
            //         }
            //     }
            // });
        }

    });
</script>

@stack('scripts')
@livewireScripts
</body>
</html>
