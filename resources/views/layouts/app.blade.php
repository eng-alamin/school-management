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
  <!-- Theme CSS -->
  <link href="{{asset('assets/css/theme.css')}}" rel="stylesheet"/>
  @stack('styles')
  @livewireStyles
</head>
<body>

    <div class="app">
        {{ $slot }}
    </div>


<!-- Bootstrap JS -->
<script src="{{asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js')}}"></script>

@stack('scripts')
@livewireScripts
</body>
</html>
