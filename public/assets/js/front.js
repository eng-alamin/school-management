/* ============================================================
   EMS FRONTEND — front.js
   All site-wide frontend JS lives here (kept out of Blade files).
   ============================================================ */

document.addEventListener("DOMContentLoaded", function () {
    const html = document.documentElement;

    /* ---------------------------------------------------------
     PAGE LOADER — hides once the page is ready
  --------------------------------------------------------- */
    const pageLoader = document.getElementById("pageLoader");
    function hideLoader() {
        if (!pageLoader) return;
        pageLoader.classList.add("loader-hidden");
        setTimeout(() => pageLoader.remove(), 600);
    }
    window.addEventListener("load", hideLoader);
    // Safety fallback in case 'load' is delayed by slow external assets
    setTimeout(hideLoader, 2500);

    /* ---------------------------------------------------------
     LANGUAGE TOGGLE (বাংলা / English)
  --------------------------------------------------------- */
    const langBtn = document.getElementById("langToggle");
    if (langBtn) {
        let currentLang = localStorage.getItem("language") || "bn";
        setLanguage(currentLang);

        langBtn.addEventListener("click", () => {
            currentLang = currentLang === "bn" ? "en" : "bn";
            localStorage.setItem("language", currentLang);
            setLanguage(currentLang);
        });
    }
    function setLanguage(lang) {
        html.setAttribute("data-lang", lang);
        html.setAttribute("lang", lang);
        if (langBtn) langBtn.textContent = lang === "bn" ? "EN" : "বাং";
    }

    /* ---------------------------------------------------------
     DARK MODE TOGGLE
  --------------------------------------------------------- */
    const themeToggle = document.getElementById("themeToggle");
    const savedTheme = localStorage.getItem("govEduTheme") || "light";
    html.setAttribute("data-theme", savedTheme);

    if (themeToggle) {
        themeToggle.addEventListener("click", () => {
            const current = html.getAttribute("data-theme");
            const next = current === "light" ? "dark" : "light";
            html.setAttribute("data-theme", next);
            localStorage.setItem("govEduTheme", next);
        });
    }

    /* ---------------------------------------------------------
     SCROLL TO TOP
  --------------------------------------------------------- */
    const scrollTopBtn = document.getElementById("scrollTop");
    if (scrollTopBtn) {
        window.addEventListener("scroll", () => {
            scrollTopBtn.classList.toggle("visible", window.scrollY > 300);
        });
        scrollTopBtn.addEventListener("click", () =>
            window.scrollTo({ top: 0, behavior: "smooth" }),
        );
    }

    /* ---------------------------------------------------------
     COUNTER ANIMATION (stat numbers)
  --------------------------------------------------------- */
    function animateCounters() {
        document.querySelectorAll(".stat-number[data-count]").forEach((el) => {
            const target = parseInt(el.getAttribute("data-count"), 10) || 0;
            const duration = 2000;
            const step = target / (duration / 16);
            let current = 0;
            const timer = setInterval(() => {
                current = Math.min(current + step, target);
                el.textContent = Math.floor(current).toLocaleString();
                if (current >= target) clearInterval(timer);
            }, 16);
        });
    }
    const statsSection = document.getElementById("stats-section");
    if (statsSection) {
        const counterObserver = new IntersectionObserver(
            (entries) => {
                if (entries[0].isIntersecting) {
                    animateCounters();
                    counterObserver.disconnect();
                }
            },
            { threshold: 0.3 },
        );
        counterObserver.observe(statsSection);
    }

    /* ---------------------------------------------------------
     ACTIVE NAV LINK ON SCROLL
  --------------------------------------------------------- */
    const sections = document.querySelectorAll("section[id]");
    if (sections.length) {
        window.addEventListener("scroll", () => {
            let current = "";
            sections.forEach((sec) => {
                if (window.scrollY >= sec.offsetTop - 120) current = sec.id;
            });
            document.querySelectorAll(".nav-link").forEach((link) => {
                link.classList.toggle(
                    "active",
                    link.getAttribute("href") === "#" + current,
                );
            });
        });
    }

    /* ---------------------------------------------------------
     SCROLL-REVEAL — animates .fade-up elements as they enter
     the viewport (elements already visible on load reveal
     immediately, e.g. the hero section).
  --------------------------------------------------------- */
    const revealTargets = document.querySelectorAll(".fade-up");
    if (revealTargets.length) {
        const revealObserver = new IntersectionObserver(
            (entries, obs) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add("in-view");
                        obs.unobserve(entry.target);
                    }
                });
            },
            { threshold: 0.12, rootMargin: "0px 0px -40px 0px" },
        );
        revealTargets.forEach((el) => revealObserver.observe(el));
    }

    /* ---------------------------------------------------------
     BUTTON RIPPLE MICRO-INTERACTION
  --------------------------------------------------------- */
    document.addEventListener("click", (e) => {
        const btn = e.target.closest(".btn");
        if (!btn) return;
        const rect = btn.getBoundingClientRect();
        const size = Math.max(rect.width, rect.height);
        const ripple = document.createElement("span");
        ripple.className = "ripple-dot";
        ripple.style.width = ripple.style.height = size + "px";
        ripple.style.left = e.clientX - rect.left - size / 2 + "px";
        ripple.style.top = e.clientY - rect.top - size / 2 + "px";
        btn.appendChild(ripple);
        setTimeout(() => ripple.remove(), 650);
    });
});

/* ---------------------------------------------------------
   LIVEWIRE GLOBAL LISTENERS
   (consolidated from admission / registration wizard pages)
--------------------------------------------------------- */
document.addEventListener("livewire:initialized", () => {
    // Date inputs updated programmatically from the backend
    Livewire.on("date-updated", function (event) {
        const dobInput = document.querySelector(
            'input[type="date"][wire\\:model="dob"]',
        );
        if (dobInput && (event.date || event.dob)) {
            dobInput.value = event.date || event.dob;
        }
        const joiningInput = document.querySelector(
            'input[type="date"][wire\\:model="joining_date"]',
        );
        if (joiningInput && event.joining_date) {
            joiningInput.value = event.joining_date;
        }
    });

    // Wizard steps / submit / reset scroll the page back to the top
    Livewire.on("scroll-top", function () {
        window.scrollTo({ top: 0, behavior: "smooth" });
    });
});
