<!doctype html>
<html lang="bn" data-theme="light" data-lang="bn">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>EMS – জাতীয় বিদ্যালয় ব্যবস্থাপনা সিস্টেম</title>

    <link rel="shortcut icon" href="{{ ($logo = setting('system_logo')) ? asset('storage/'.$logo) : asset('assets/img/default-logo.png') }}">

    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
      rel="stylesheet"
    />
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css"
      rel="stylesheet"
    />
    <link
      href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&family=Sora:wght@400;600;700;800&family=DM+Sans:wght@400;500;600&display=swap"
      rel="stylesheet"
    />

    <!-- Bootstrap-select CSS -->
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/css/bootstrap-select.min.css"
      rel="stylesheet"
    />

    <link rel="stylesheet" href="{{asset('assets/css/front.css')}}"/>

    @stack('styles')
    @livewireStyles
  </head>
  <body>
    <!-- ===== PAGE LOADER ===== -->
    <div id="pageLoader">
      <svg
          xmlns="http://www.w3.org/2000/svg"
          height="90px"
          width="90px"
          viewBox="0 0 200 200"
          class="ems-pencil"
      >
        <defs>
            <clipPath id="ems-pencil-eraser">
                <rect height="30" width="30" ry="5" rx="5"></rect>
            </clipPath>
        </defs>
        <circle
            transform="rotate(-113,100,100)"
            stroke-linecap="round"
            stroke-dashoffset="439.82"
            stroke-dasharray="439.82 439.82"
            stroke-width="2"
            stroke="currentColor"
            fill="none"
            r="70"
            class="ems-pencil__stroke"
        ></circle>
        <g transform="translate(100,100)" class="ems-pencil__rotate">
            <g fill="none">
                <circle
                    transform="rotate(-90)"
                    stroke-dashoffset="402"
                    stroke-dasharray="402.12 402.12"
                    stroke-width="30"
                    stroke="hsl(223,90%,50%)"
                    r="64"
                    class="ems-pencil__body1"
                ></circle>
                <circle
                    transform="rotate(-90)"
                    stroke-dashoffset="465"
                    stroke-dasharray="464.96 464.96"
                    stroke-width="10"
                    stroke="hsl(223,90%,60%)"
                    r="74"
                    class="ems-pencil__body2"
                ></circle>
                <circle
                    transform="rotate(-90)"
                    stroke-dashoffset="339"
                    stroke-dasharray="339.29 339.29"
                    stroke-width="10"
                    stroke="hsl(223,90%,40%)"
                    r="54"
                    class="ems-pencil__body3"
                ></circle>
            </g>
            <g transform="rotate(-90) translate(49,0)" class="ems-pencil__eraser">
                <g class="ems-pencil__eraser-skew">
                    <rect height="30" width="30" ry="5" rx="5" fill="hsl(223,90%,70%)"></rect>
                    <rect clip-path="url(#ems-pencil-eraser)" height="30" width="5" fill="hsl(223,90%,60%)"></rect>
                    <rect height="20" width="30" fill="hsl(223,10%,90%)"></rect>
                    <rect height="20" width="15" fill="hsl(223,10%,70%)"></rect>
                    <rect height="20" width="5" fill="hsl(223,10%,80%)"></rect>
                    <rect height="2" width="30" y="6" fill="hsla(223,10%,10%,0.2)"></rect>
                    <rect height="2" width="30" y="13" fill="hsla(223,10%,10%,0.2)"></rect>
                </g>
            </g>
            <g transform="rotate(-90) translate(49,-30)" class="ems-pencil__point">
                <polygon points="15 0,30 30,0 30" fill="hsl(33,90%,70%)"></polygon>
                <polygon points="15 0,6 30,0 30" fill="hsl(33,90%,50%)"></polygon>
                <polygon points="15 0,20 10,10 10" fill="hsl(223,10%,10%)"></polygon>
            </g>
        </g>
      </svg>
    </div>

    <!-- ===== NAVBAR ===== -->
    @include('layouts.frontend.header')

      {{ $slot }}

    <!-- ===== FOOTER ===== -->
    @include('layouts.frontend.footer')

    <!-- Scroll to Top -->
    <button class="scroll-top" id="scrollTop" aria-label="Scroll to top">
      <i class="bi bi-arrow-up"></i>
    </button>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Bootstrap-select JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/js/bootstrap-select.min.js"></script>

    <script src="{{ asset('assets/js/front.js') }}"></script>

<script>
        // ── Single script: material-style input focus/fill states +
        //    bootstrap-select (selectpicker) init/sync.
        //    All selects use class="selectpicker" only. ──

        document.addEventListener('livewire:initialized', () => {

            setTimeout(() => initAllFields(), 100);

            Livewire.hook('morph.updated', ({ el }) => {
                setTimeout(() => initAllFields(), 0);
            });

            function initAllFields() {

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
            }

            // Registered once — not inside initAllFields — so it never
            // piles up duplicate listeners on every morph.updated call.
            Livewire.on('date-updated', function (event) {
                var data = Array.isArray(event) ? event[0] : event;
                if (!data || !data.field) return;

                var input = document.querySelector(
                    '.input-group-outline input[type="date"][wire\\:model="' + data.field + '"]'
                );
                if (!input) return;

                var newDate = data.date || '';
                if (newDate) {
                    input.value = newDate;
                    input.dataset.dpValue = newDate;
                    if (input._dpTriggerSync) {
                        input._dpTriggerSync(newDate);
                    }
                }
            });

        });

        // ── bootstrap-select (selectpicker) init/sync ──
        // Uses a MutationObserver instead of relying on Livewire's
        // morph.updated hook, so it works no matter when/whether that
        // hook fires (wire:ignore blocks, nested components, step
        // switches via conditional blocks, etc). Any .selectpicker that appears in
        // the DOM gets initialized exactly once.

        function initOneSelectpicker(select) {
            var $select = $(select);
            if ($select.data('selectpicker')) {
                $select.selectpicker('destroy');
            }
            $select.selectpicker();
        }

        function scanAndInitSelectpickers(root) {
            (root || document).querySelectorAll('select.selectpicker').forEach(initOneSelectpicker);
        }

        document.addEventListener('DOMContentLoaded', function () {
            scanAndInitSelectpickers(document);
        });
        document.addEventListener('livewire:navigated', function () {
            scanAndInitSelectpickers(document);
        });

        var selectpickerObserver = new MutationObserver(function (mutations) {
            mutations.forEach(function (mutation) {
                mutation.addedNodes.forEach(function (node) {
                    if (node.nodeType !== 1) return; // element nodes only
                    if (node.matches && node.matches('select.selectpicker')) {
                        initOneSelectpicker(node);
                    }
                    if (node.querySelectorAll) {
                        node.querySelectorAll('select.selectpicker').forEach(initOneSelectpicker);
                    }
                });
            });
        });
        selectpickerObserver.observe(document.body, { childList: true, subtree: true });

        // Push selectpicker change to Livewire's wire:model
        $(document).on('changed.bs.select', '.selectpicker[wire\\:model], .selectpicker[wire\\:model\\.live]', function () {
            let el = this;
            let attr = el.getAttribute('wire:model.live') ? 'wire:model.live' : 'wire:model';
            let model = el.getAttribute(attr).replace('.live', '');
            let value = $(el).val();
            let root = el.closest('[wire\\:id]');
            if (!root) return;
            window.Livewire.find(root.getAttribute('wire:id')).set(model, value);
        });
    </script>

    @stack('scripts')
    @livewireScripts
  </body>
</html>