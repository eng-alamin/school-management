/* ════════════════════════════════════════
   CUSTOM DATEPICKER
════════════════════════════════════════ */
(function() {

    var MONTHS = [
        'January','February','March','April','May','June',
        'July','August','September','October','November','December'
    ];

    var WDS = ['Su','Mo','Tu','We','Th','Fr','Sa'];

    var _activeDP = null;

    function _posDP(panel, trigger) {

        var r = trigger.getBoundingClientRect();
        var panelH = 320;
        var spaceBelow = window.innerHeight - r.bottom;

        panel.style.left = r.left + 'px';
        panel.style.width = Math.max(r.width, 280) + 'px';

        if (spaceBelow < panelH + 8 && r.top > spaceBelow) {

            panel.style.top = 'auto';

            panel.style.bottom =
                (window.innerHeight - r.top + 4) + 'px';

        } else {

            panel.style.bottom = 'auto';
            panel.style.top = (r.bottom + 4) + 'px';
        }
    }

    function buildDatepicker(input) {

        // hide native input
        input.style.opacity = '0';
        input.style.position = 'absolute';
        input.style.pointerEvents = 'none';

        var wrapper = input.parentElement;

        // trigger
        var trigger = document.createElement('div');
        trigger.className = 'dp-trigger';
        trigger.setAttribute('tabindex', '0');

        trigger.innerHTML = `
            <span class="dp-text">Select date</span>
            <span class="material-icons-round nav-icon">
                calendar_month
            </span>
        `;

        wrapper.appendChild(trigger);

        var today = new Date();

        var selDate = null;

        if (input.value) {

            var parts = input.value.split('-');

            selDate = new Date(
                +parts[0],
                +parts[1] - 1,
                +parts[2]
            );
        }

        var viewYear =
            selDate ? selDate.getFullYear() : today.getFullYear();

        var viewMonth =
            selDate ? selDate.getMonth() : today.getMonth();

        var mode = 'days';

        var yearRangeStart =
            Math.floor(viewYear / 12) * 12;

        // panel
        var panel = document.createElement('div');
        panel.className = 'dp-panel';

        document.body.appendChild(panel);

        function formatDisplay(d) {

            if (!d) return 'Select date';

            return d.getDate() + ' ' +
                   MONTHS[d.getMonth()].slice(0,3) +
                   ' ' +
                   d.getFullYear();
        }

        /* IMPORTANT */
        function syncLivewire() {

            input.dispatchEvent(
                new Event('input', { bubbles: true })
            );

            input.dispatchEvent(
                new Event('change', { bubbles: true })
            );
        }

        function syncTrigger() {

            var txt = trigger.querySelector('.dp-text');

            txt.textContent = formatDisplay(selDate);

            if (selDate) {

                trigger.classList.add('dp-has-value');

                var y = selDate.getFullYear();
                var m = String(selDate.getMonth() + 1)
                    .padStart(2, '0');

                var d = String(selDate.getDate())
                    .padStart(2, '0');

                input.value = `${y}-${m}-${d}`;

            } else {

                trigger.classList.remove('dp-has-value');

                input.value = '';
            }

            // IMPORTANT FOR LIVEWIRE
            syncLivewire();

            // input group fill state
            var ig = wrapper.closest('.input-group');

            if (ig) {

                if (input.value) {
                    ig.classList.add('is-filled');
                } else {
                    ig.classList.remove('is-filled');
                }
            }
        }

        function renderPanel() {

            panel.innerHTML = '';

            // header
            var hdr = document.createElement('div');
            hdr.className = 'dp-header';

            var prevBtn = document.createElement('button');
            prevBtn.type = 'button';
            prevBtn.className = 'dp-nav-btn';
            prevBtn.textContent = '‹';

            var nextBtn = document.createElement('button');
            nextBtn.type = 'button';
            nextBtn.className = 'dp-nav-btn';
            nextBtn.textContent = '›';

            var center = document.createElement('div');
            center.className = 'dp-header-center';

            var title = document.createElement('button');
            title.type = 'button';
            title.className = 'dp-year-btn';

            title.textContent =
                MONTHS[viewMonth] + ' ' + viewYear;

            center.appendChild(title);

            hdr.appendChild(prevBtn);
            hdr.appendChild(center);
            hdr.appendChild(nextBtn);

            panel.appendChild(hdr);

            prevBtn.onclick = function() {

                viewMonth--;

                if (viewMonth < 0) {
                    viewMonth = 11;
                    viewYear--;
                }

                renderPanel();
            };

            nextBtn.onclick = function() {

                viewMonth++;

                if (viewMonth > 11) {
                    viewMonth = 0;
                    viewYear++;
                }

                renderPanel();
            };

            // weekdays
            var wdRow = document.createElement('div');
            wdRow.className = 'dp-weekdays';

            WDS.forEach(function(w) {

                var wd = document.createElement('div');

                wd.className = 'dp-wd';
                wd.textContent = w;

                wdRow.appendChild(wd);
            });

            panel.appendChild(wdRow);

            // days
            var grid = document.createElement('div');
            grid.className = 'dp-days';

            var first =
                new Date(viewYear, viewMonth, 1).getDay();

            var daysInMonth =
                new Date(viewYear, viewMonth + 1, 0).getDate();

            for (var i = 0; i < first; i++) {

                var empty = document.createElement('div');

                grid.appendChild(empty);
            }

            for (let day = 1; day <= daysInMonth; day++) {

                let btn = document.createElement('button');

                btn.type = 'button';
                btn.className = 'dp-day';
                btn.textContent = day;

                btn.onclick = function() {

                    selDate =
                        new Date(viewYear, viewMonth, day);

                    syncTrigger();

                    closePanel();
                };

                grid.appendChild(btn);
            }

            panel.appendChild(grid);
        }

        function openPanel() {

            if (_activeDP && _activeDP !== panel) {

                closeActiveDP();
            }

            renderPanel();

            panel.classList.add('dp-panel-open');

            trigger.classList.add('dp-open');

            _activeDP = panel;

            _activeDP._trigger = trigger;

            _posDP(panel, trigger);
        }

        function closePanel() {

            panel.classList.remove('dp-panel-open');

            trigger.classList.remove('dp-open');

            if (_activeDP === panel) {
                _activeDP = null;
            }
        }

        trigger.addEventListener('click', function(e) {

            e.stopPropagation();

            if (panel.classList.contains('dp-panel-open')) {
                closePanel();
            } else {
                openPanel();
            }
        });

        syncTrigger();
    }

    function closeActiveDP() {

        if (_activeDP) {

            _activeDP.classList.remove('dp-panel-open');

            if (_activeDP._trigger) {
                _activeDP._trigger.classList.remove('dp-open');
            }

            _activeDP = null;
        }
    }

    document.addEventListener('click', function(e) {

        if (
            _activeDP &&
            !e.target.closest('.dp-panel') &&
            !e.target.closest('.dp-trigger')
        ) {
            closeActiveDP();
        }
    });

    window._initDatepickers = function() {

        document.querySelectorAll(
            '.input-group-outline input[type="date"]'
        ).forEach(function(input) {

            if (!input._dpInit) {

                input._dpInit = true;

                buildDatepicker(input);
            }
        });
    };

})();

document.addEventListener('DOMContentLoaded', function () {
    _initDatepickers();
});

window.buildDatepicker = buildDatepicker;