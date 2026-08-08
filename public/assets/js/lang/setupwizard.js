/* ═══════════════════════════════════════
   SETUP WIZARD — LANGUAGE SWITCH (EN / বাংলা)
═══════════════════════════════════════ */

function getCurrentLang() {
    return localStorage.getItem("lang") || "en";
}

function applySetupWizardLang(lang) {
    document.querySelectorAll("[data-en][data-bn]").forEach((el) => {
        const text = lang === "bn" ? el.dataset.bn : el.dataset.en;
        if (text !== undefined) {
            el.textContent = text;
        }
    });
}

document.addEventListener("langChanged", (event) => {
    applySetupWizardLang(event.detail.lang);
});

applySetupWizardLang(getCurrentLang());

document.addEventListener("livewire:init", () => {
    Livewire.hook("commit", ({ succeed }) => {
        succeed(() => {
            applySetupWizardLang(getCurrentLang());
        });
    });
});

export { applySetupWizardLang };
