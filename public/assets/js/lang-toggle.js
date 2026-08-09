/* ═══════════════════════════════════════
   LANGUAGE SWITCH (EN / বাংলা)
   Lightweight data-en / data-bn attribute pattern
═══════════════════════════════════════ */

function getCurrentLang() {
    return localStorage.getItem("lang") || "en";
}

function applyMinistryLang(lang) {
    document.querySelectorAll("[data-en][data-bn]").forEach((el) => {
        const text = lang === "bn" ? el.dataset.bn : el.dataset.en;
        if (text !== undefined) {
            el.textContent = text;
        }
    });

    // Placeholder translation (e.g. search inputs)
    document.querySelectorAll("[data-en-ph][data-bn-ph]").forEach((el) => {
        const ph = lang === "bn" ? el.dataset.bnPh : el.dataset.enPh;
        if (ph !== undefined) {
            el.setAttribute("placeholder", ph);
        }
    });

    // Sync main toggle-pill active button state (id="langToggle")
    const toggle = document.getElementById("langToggle");
    if (toggle) {
        const buttons = toggle.querySelectorAll("button");
        buttons.forEach((btn) => btn.classList.remove("active"));
        const target =
            lang === "bn"
                ? toggle.querySelector("button[onclick*=\"'bn'\"]")
                : toggle.querySelector("button[onclick*=\"'en'\"]");
        if (target) target.classList.add("active");
    }

    // Sync the profile-settings-page toggle (id="settingsLangEN" / "settingsLangBN")
    const settingsEN = document.getElementById("settingsLangEN");
    const settingsBN = document.getElementById("settingsLangBN");
    if (settingsEN && settingsBN) {
        settingsEN.classList.toggle("active", lang === "en");
        settingsBN.classList.toggle("active", lang === "bn");
    }
}

function setLang(lang, btn) {
    localStorage.setItem("lang", lang);

    if (btn) {
        const toggle = btn.closest("#langToggle");
        if (toggle) {
            toggle
                .querySelectorAll("button")
                .forEach((b) => b.classList.remove("active"));
        }
        btn.classList.add("active");
    }

    applyMinistryLang(lang);

    document.dispatchEvent(
        new CustomEvent("langChanged", { detail: { lang } }),
    );
}

// Variant used by the profile-settings-page toggle (different ids/markup,
// same underlying language state). Keeps both toggles on the page in sync.
function setLangFromSettings(lang) {
    setLang(lang, null);
}

// React to lang changes triggered from elsewhere (e.g. front.js on shared pages)
document.addEventListener("langChanged", (event) => {
    applyMinistryLang(event.detail.lang);
});

// Initial apply on page load
applyMinistryLang(getCurrentLang());

// Re-apply after every Livewire DOM update (component re-render resets textContent)
document.addEventListener("livewire:init", () => {
    Livewire.hook("commit", ({ succeed }) => {
        succeed(() => {
            applyMinistryLang(getCurrentLang());
        });
    });
});

// Expose to global scope for inline onclick handlers
window.setLang = setLang;
window.setLangFromSettings = setLangFromSettings;

export { applyMinistryLang, setLang, setLangFromSettings, getCurrentLang };
