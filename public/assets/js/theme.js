// scrollToFirstError on Livewire validation failure
document.addEventListener("livewire:init", () => {
    function scrollToFirstError() {
        setTimeout(() => {
            // Livewire error fields
            let el = document.querySelector(
                ".is-invalid, .border-danger, .text-danger",
            );

            if (!el) return;

            let target =
                el.closest(".col-md-3, .col-md-6, .input-group, .form-group") ||
                el;

            target.scrollIntoView({
                behavior: "smooth",
                block: "center",
            });

            let input = target.querySelector("input, select, textarea");
            if (input) input.focus();
        }, 150);
    }
    // Livewire event listener
    Livewire.on("validation-failed", () => {
        scrollToFirstError();
    });
});

// Toast notification function
function toast(message, type = "success") {
    const tc = document.getElementById("toastContainer");
    if (!tc) return;

    const t = document.createElement("div");
    t.className = `toast-item ${type}`;
    t.innerHTML = `
        <i class="bi bi-${type === "success" ? "check-circle-fill" : "exclamation-triangle-fill"}"></i>
        <span>${message}</span>
    `;
    tc.appendChild(t);

    // Force reflow to trigger transition
    requestAnimationFrame(() => {
        t.style.opacity = "1";
        t.style.transform = "translateX(0)";
    });

    setTimeout(() => {
        t.style.opacity = "0";
        t.style.transform = "translateX(30px)";
        setTimeout(() => t.remove(), 300);
    }, 3000);
}
// Livewire 3 event listener
document.addEventListener("livewire:init", function () {
    Livewire.on("toast", (event) => {
        console.log("Toast event received:", event);
        toast(event.message, event.type);
    });
});

/* Save button */
document.addEventListener("livewire:init", () => {
    Livewire.on("saved", () => {
        let btn = document.querySelector(".btn-pink");

        btn.innerHTML =
            '<span class="material-icons-round">check_circle</span> Saved!';
        btn.style.background = "linear-gradient(195deg,#4caf50,#2e7d32)";

        setTimeout(() => {
            btn.innerHTML =
                '<span class="material-icons-round">save</span> Save';
            btn.style.background = "";
        }, 2000);
    });
});

/* ═══════════════════════════════════════
   DROPZONE INIT
═══════════════════════════════════════ */
Dropzone.autoDiscover = false;

document.addEventListener("DOMContentLoaded", function () {
    if (document.getElementById("myDropzone")) {
        var myDropzone = new Dropzone("#myDropzone", {
            url: "#",
            autoProcessQueue: false,
            addRemoveLinks: true,
            dictRemoveFile: "x",
            error: function (file) {
                file.previewElement.classList.remove("dz-error");
                file.previewElement.querySelector(
                    ".dz-error-message",
                ).style.display = "none";
            },
            removedfile: function (file) {
                file.previewElement.remove();
            },
            success: function (file, response) {
                file.serverFilename = response.filename;
            },
        });
    }
});

/* ═══════════════════════════════════════
   DARK MODE
═══════════════════════════════════════ */
function toggleDark() {
    const isDark = document.body.classList.toggle("dark-mode");
    document.getElementById("darkIcon").textContent = isDark
        ? "light_mode"
        : "dark_mode";
    const sw = document.getElementById("darkModeSwitch");
    if (sw) sw.checked = isDark;
    localStorage.setItem("darkMode", isDark ? "1" : "0");
}
if (localStorage.getItem("darkMode") === "1") {
    document.body.classList.add("dark-mode");
    document.getElementById("darkIcon").textContent = "light_mode";
    // sync switch after DOM ready
    window.addEventListener("DOMContentLoaded", () => {
        const sw = document.getElementById("darkModeSwitch");
        if (sw) sw.checked = true;
    });
}

/* ═══════════════════════════════════════
   TOPNAV DROPDOWNS
═══════════════════════════════════════ */
function toggleDropdown(id, e) {
    e && e.stopPropagation();
    const target = document.getElementById(id);
    const backdrop = document.getElementById("dropdownBackdrop");
    const isOpen = target.classList.contains("show");
    // close all first
    document
        .querySelectorAll(".topnav-dropdown")
        .forEach((d) => d.classList.remove("show"));
    if (!isOpen) {
        target.classList.add("show");
        backdrop.classList.add("show");
    } else {
        backdrop.classList.remove("show");
    }
}
function closeAllDropdowns() {
    document
        .querySelectorAll(".topnav-dropdown")
        .forEach((d) => d.classList.remove("show"));
    document.getElementById("dropdownBackdrop").classList.remove("show");
}
// Close on Escape key
document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") closeAllDropdowns();
});

/* Settings panel lang sync */
function setLangFromSettings(lang) {
    const mainBtns = document.querySelectorAll("#langToggle button");
    if (lang === "en") {
        mainBtns[0].classList.add("active");
        mainBtns[1].classList.remove("active");
        document.getElementById("settingsLangEN").classList.add("active");
        document.getElementById("settingsLangBN").classList.remove("active");
    } else {
        mainBtns[1].classList.add("active");
        mainBtns[0].classList.remove("active");
        document.getElementById("settingsLangEN").classList.remove("active");
        document.getElementById("settingsLangBN").classList.add("active");
    }
    currentLang = lang;
    localStorage.setItem("lang", lang);
    applyLang(lang);
}

/* ═══════════════════════════════════════
   LANGUAGE SWITCH (EN / বাংলা)
═══════════════════════════════════════ */
const translations = {
    en: {
        brandName: "Material Dashboard",
        brandSub: "PRO Bootstrap 5",
        userName: "Brooklyn Alice",
        myProfile: "My Profile",
        settings: "Settings",
        logout: "Logout",
        dashboards: "Dashboards",
        analytics: "Analytics",
        discover: "Discover",
        sales: "Sales",
        automotive: "Automotive",
        smartHome: "Smart Home",
        pages: "Pages",
        vrLabel: "Virtual Reality",
        vrDefault: "VR Default",
        vrInfo: "VR Info",
        pricingPage: "Pricing Page",
        rtl: "RTL",
        widgets: "Widgets",
        charts: "Charts",
        notifications: "Notifications",
        account: "Account",
        billing: "Billing",
        invoice: "Invoice",
        security: "Security",
        applications: "Applications",
        crm: "CRM",
        kanban: "Kanban",
        wizard: "Wizard",
        dataTables: "DataTables",
        calendar: "Calendar",
        stats: "Stats",
        ecommerce: "Ecommerce",
        products: "Products",
        newProduct: "New Product",
        editProduct: "Edit Product",
        productPage: "Product Page",
        productsList: "Products List",
        orders: "Orders",
        orderList: "Order List",
        orderDetails: "Order Details",
        referral: "Referral",
        team: "Team",
        allProjects: "All Projects",
        messages: "Messages",
        newUser: "New User",
        profileOverview: "Profile Overview",
        reports: "Reports",
        projects: "Projects",
        general: "General",
        timeline: "Timeline",
        newProject: "New Project",
        authentication: "Authentication",
        signIn: "Sign In",
        signUp: "Sign Up",
        resetPassword: "Reset Password",
        error: "Error",
        basic: "Basic",
        cover: "Cover",
        illustration: "Illustration",
        docs: "DOCS",
        basicLabel: "Basic",
        components: "Components",
        alerts: "Alerts",
        buttons: "Buttons",
        cards: "Cards",
        forms: "Forms",
        modal: "Modal",
        tables: "Tables",
        inventory: "Inventory",
        admission: "Admission",
        createAdmission: "Create Admission",
        onlineAdmission: "Online Admission",
        category: "Category",
        students: "Students",
        parents: "Parents",
        cardManagement: "Card Management",
        certificate: "Certificate",
        employees: "Employees",
        humanResource: "Human Resources",
        academic: "Academic",
        homeWork: "Home Work",
        examMaster: "Exam Master",
        attendance: "Attendance",
        events: "Events",
        bulkSmsEmail: "Bulk Sms And Email",
        studentAccounting: "Student Accounting",
        officeAccounting: "Office Accounting",
        message: "Message",
        reports: "Reports",
        alumni: "Alumni",
        settings: "Settings",
        pagesLabel: "PAGES",
        allProducts: "All Products",
        pageTitle: "Products",
        addProduct: "Add Product",
        importBtn: "Import",
        exportBtn: "Export CSV",
        search: "Search",
        colProduct: "Product",
        colCategory: "Category",
        colPrice: "Price",
        colSku: "SKU",
        colQty: "Qty",
        colStatus: "Status",
        colActions: "Actions",
        footerText: "Crafted with",
        footerBy: "by Creative Tim",
    },
    bn: {
        brandName: "ম্যাটেরিয়াল ড্যাশবোর্ড",
        brandSub: "PRO Bootstrap 5",
        userName: "ব্রুকলিন অ্যালিস",
        myProfile: "আমার প্রোফাইল",
        settings: "সেটিংস",
        logout: "লগ আউট",
        dashboards: "ড্যাশবোর্ড",
        analytics: "বিশ্লেষণ",
        discover: "আবিষ্কার",
        sales: "বিক্রয়",
        automotive: "অটোমোটিভ",
        smartHome: "স্মার্ট হোম",
        pages: "পেজসমূহ",
        vrLabel: "ভার্চুয়াল রিয়েলিটি",
        vrDefault: "ভিআর ডিফল্ট",
        vrInfo: "ভিআর তথ্য",
        pricingPage: "মূল্য পেজ",
        rtl: "আরটিএল",
        widgets: "উইজেট",
        charts: "চার্ট",
        notifications: "বিজ্ঞপ্তি",
        account: "অ্যাকাউন্ট",
        billing: "বিলিং",
        invoice: "চালান",
        security: "নিরাপত্তা",
        applications: "অ্যাপ্লিকেশন",
        crm: "সিআরএম",
        kanban: "কানবান",
        wizard: "উইজার্ড",
        dataTables: "ডেটাটেবিল",
        calendar: "ক্যালেন্ডার",
        stats: "পরিসংখ্যান",
        ecommerce: "ই-কমার্স",
        products: "পণ্যসমূহ",
        newProduct: "নতুন পণ্য",
        editProduct: "পণ্য সম্পাদনা",
        productPage: "পণ্য পেজ",
        productsList: "পণ্য তালিকা",
        orders: "অর্ডার",
        orderList: "অর্ডার তালিকা",
        orderDetails: "অর্ডার বিবরণ",
        referral: "রেফারেল",
        team: "দল",
        allProjects: "সব প্রজেক্ট",
        messages: "বার্তা",
        newUser: "নতুন ব্যবহারকারী",
        profileOverview: "প্রোফাইল ওভারভিউ",
        reports: "রিপোর্ট",
        projects: "প্রজেক্ট",
        general: "সাধারণ",
        timeline: "টাইমলাইন",
        newProject: "নতুন প্রজেক্ট",
        authentication: "প্রমাণীকরণ",
        signIn: "সাইন ইন",
        signUp: "সাইন আপ",
        resetPassword: "পাসওয়ার্ড রিসেট",
        error: "ত্রুটি",
        basic: "বেসিক",
        cover: "কভার",
        illustration: "ইলাস্ট্রেশন",
        docs: "ডকস",
        basicLabel: "বেসিক",
        components: "কম্পোনেন্ট",
        alerts: "সতর্কতা",
        buttons: "বাটন",
        cards: "কার্ড",
        forms: "ফর্ম",
        modal: "মডাল",
        tables: "টেবিল",
        inventory: "ইনভেন্টরি",
        admission: "ভর্তি",
        createAdmission: "ভর্তি তৈরি করুন",
        onlineAdmission: "অনলাইন ভর্তি",
        category: "বিভাগ",
        students: "ছাত্রছাত্রী",
        parents: "পিতা-মাতা",
        employees: "কর্মচারী",
        cardManagement: "কার্ড ব্যবস্থাপনা",
        certificate: "সার্টিফিকেট",
        humanResource: "মানব সম্পদ",
        academic: "একাডেমিক",
        homeWork: "হোম ওয়ার্ক",
        examMaster: "পরীক্ষার মাস্টার",
        attendance: "উপস্থিতি",
        events: "ইভেন্ট",
        bulkSmsEmail: "বাল্ক এসএমএস এবং ইমেইল",
        studentAccounting: "ছাত্র অ্যাকাউন্টিং",
        officeAccounting: "অফিস অ্যাকাউন্টিং",
        message: "বার্তা",
        reports: "রিপোর্ট",
        alumni: "অ্যালামনাই",
        settings: "সেটিংস",
        pagesLabel: "পেজ",
        allProducts: "সকল পণ্য",
        pageTitle: "পণ্যসমূহ",
        addProduct: "পণ্য যোগ করুন",
        importBtn: "আমদানি",
        exportBtn: "CSV রপ্তানি",
        search: "খুঁজুন…",
        colProduct: "পণ্য",
        colCategory: "বিভাগ",
        colPrice: "মূল্য",
        colSku: "এসকেইউ",
        colQty: "পরিমাণ",
        colStatus: "অবস্থা",
        colActions: "কার্যক্রম",
        footerText: "ভালোবাসায় তৈরি",
        footerBy: "ক্রিয়েটিভ টিম দ্বারা",
    },
};

let currentLang = localStorage.getItem("lang") || "en";

function applyLang(lang) {
    const t = translations[lang];
    const setText = (id, val) => {
        const el = document.getElementById(id);
        if (el) el.textContent = val;
    };
    const setPlaceholder = (id, val) => {
        const el = document.getElementById(id);
        if (el) el.placeholder = val;
    };

    // Brand
    setText("brandName", t.brandName);
    setText("brandSub", t.brandSub);
    setText("userName", t.userName);
    setText("userNameSidebar", t.userName);

    // User dropdown
    setText("ud-profile", t.myProfile);
    setText("ud-settings", t.settings);
    setText("ud-logout", t.logout);

    // Sidebar nav labels
    setText("nav-dashboards", t.dashboards);
    setText("nav-analytics", t.analytics);
    setText("nav-discover", t.discover);
    setText("nav-sales", t.sales);
    setText("nav-automotive", t.automotive);
    setText("nav-smarthome", t.smartHome);
    setText("nav-pages", t.pages);
    setText("nav-vr", t.vrLabel);
    setText("nav-vrdefault", t.vrDefault);
    setText("nav-vrinfo", t.vrInfo);
    setText("nav-pricing", t.pricingPage);
    setText("nav-rtl", t.rtl);
    setText("nav-widgets", t.widgets);
    setText("nav-charts", t.charts);
    setText("nav-notifications", t.notifications);
    setText("nav-account", t.account);
    setText("nav-settings", t.settings);
    setText("nav-billing", t.billing);
    setText("nav-invoice", t.invoice);
    setText("nav-security", t.security);
    setText("nav-applications", t.applications);
    setText("nav-crm", t.crm);
    setText("nav-kanban", t.kanban);
    setText("nav-wizard", t.wizard);
    setText("nav-datatables", t.dataTables);
    setText("nav-calendar", t.calendar);
    setText("nav-stats", t.stats);
    setText("nav-ecommerce", t.ecommerce);
    setText("nav-products", t.products);
    setText("nav-newproduct", t.newProduct);
    setText("nav-editproduct", t.editProduct);
    setText("nav-productpage", t.productPage);
    setText("nav-productslist", t.productsList);
    setText("nav-orders", t.orders);
    setText("nav-orderlist", t.orderList);
    setText("nav-orderdetails", t.orderDetails);
    setText("nav-referral", t.referral);
    setText("nav-team", t.team);
    setText("nav-allprojects", t.allProjects);
    setText("nav-messages", t.messages);
    setText("nav-newuser", t.newUser);
    setText("nav-profileoverview", t.profileOverview);
    setText("nav-reports", t.reports);
    setText("nav-projects", t.projects);
    setText("nav-general", t.general);
    setText("nav-timeline", t.timeline);
    setText("nav-newproject", t.newProject);
    setText("nav-authentication", t.authentication);
    setText("nav-signin", t.signIn);
    setText("nav-signup", t.signUp);
    setText("nav-resetpassword", t.resetPassword);
    setText("nav-error", t.error);
    setText("nav-docs", t.docs);
    setText("nav-basic", t.basicLabel);
    setText("nav-components", t.components);
    setText("nav-inventory", t.inventory);
    setText("nav-admission", t.admission);
    setText("nav-createadmission", t.createAdmission);
    setText("nav-onlineadmission", t.onlineAdmission);
    setText("nav-category", t.category);
    setText("nav-students", t.students);
    setText("nav-parents", t.parents);
    setText("nav-employees", t.employees);
    setText("nav-card-management", t.cardManagement);
    setText("nav-certificate", t.certificate);
    setText("nav-human-resource", t.humanResource);
    setText("nav-academic", t.academic);
    setText("nav-home-work", t.homeWork);
    setText("nav-exam-master", t.examMaster);
    setText("nav-attendance", t.attendance);
    setText("nav-events", t.events);
    setText("nav-reports", t.reports);
    setText("nav-message", t.message);
    setText("nav-bulk-sms-email", t.bulkSmsEmail);
    setText("nav-student-accounting", t.studentAccounting);
    setText("nav-office-accounting", t.officeAccounting);
    setText("nav-alumni", t.alumni);
    setText("nav-settings", t.settings);
    setText("nav-pages-section", t.pagesLabel);

    // Page content
    setText("pageTitleEl", t.pageTitle);
    setText("cardHeaderTitle", t.allProducts);
    // setText('cardHeaderSubtitle', t.tableSubtitle);
    setText("addProductBtn", t.addProduct);
    setText("importBtnEl", t.importBtn);
    setText("exportBtnEl", t.exportBtn);
    setPlaceholder("tableSearch", t.search);

    // Table headers
    setText("th-product-lbl", t.colProduct);
    setText("th-category-lbl", t.colCategory);
    setText("th-price-lbl", t.colPrice);
    setText("th-sku-lbl", t.colSku);
    setText("th-qty-lbl", t.colQty);
    setText("th-status-lbl", t.colStatus);
    setText("th-actions-lbl", t.colActions);
}

function setLang(lang, btn) {
    currentLang = lang;
    localStorage.setItem("lang", lang);
    document
        .querySelectorAll("#langToggle button")
        .forEach((b) => b.classList.remove("active"));
    btn.classList.add("active");
    applyLang(lang);
}

// Apply saved language on load
(function () {
    const saved = localStorage.getItem("lang") || "en";
    if (saved === "bn") {
        const btns = document.querySelectorAll("#langToggle button");
        if (btns.length >= 2) {
            btns[0].classList.remove("active");
            btns[1].classList.add("active");
        }
    }
    applyLang(saved);
})();

/* ═══════════════════════════════════════
   SIDEBAR TOGGLE LOGIC
═══════════════════════════════════════ */
function toggleNav1(el) {
    const collapse = el.nextElementSibling;
    if (!collapse) return;
    const isOpen = collapse.classList.contains("show");
    // close all sibling collapses
    el.closest("ul")
        .querySelectorAll(".nav2-collapse.show")
        .forEach((c) => {
            if (c !== collapse) {
                c.classList.remove("show");
                c.previousElementSibling.classList.remove("open");
            }
        });
    collapse.classList.toggle("show", !isOpen);
    el.classList.toggle("open", !isOpen);
}

function toggleNav2(el) {
    const collapse = el.nextElementSibling;
    if (!collapse) return;
    const isOpen = collapse.classList.contains("show");
    collapse.classList.toggle("show", !isOpen);
    el.classList.toggle("open", !isOpen);
}

/* user dropdown */
document.getElementById("userToggle").addEventListener("click", function () {
    const dd = document.getElementById("userDropdown");
    const arrow = document.getElementById("userArrow");
    dd.classList.toggle("show");
    arrow.classList.toggle("open");
});

/* mobile sidebar */
const sidebar = document.getElementById("mainSidebar");
const overlay = document.getElementById("sidebarOverlay");
const toggleBtn = document.getElementById("sidebarToggle");

toggleBtn.addEventListener("click", () => {
    sidebar.classList.toggle("show");
    overlay.classList.toggle("show");
});
overlay.addEventListener("click", () => {
    sidebar.classList.remove("show");
    overlay.classList.remove("show");
});

// ===================== focused / defocused =====================
function focused(el) {
    var parent = el.closest(".input-group");
    if (parent) parent.classList.add("is-focused");
}

function defocused(el) {
    var parent = el.closest(".input-group");
    if (parent) {
        parent.classList.remove("is-focused");
        var val = el.value;
        if (val && val.trim() !== "") {
            parent.classList.add("is-filled");
        } else {
            parent.classList.remove("is-filled");
        }
    }
}

var _activeDropdown = null;

function _positionDropdown(panel, trigger) {
    var rect = trigger.getBoundingClientRect();
    var panelH = Math.min(220, 300);
    var spaceBelow = window.innerHeight - rect.bottom;
    var openUp = spaceBelow < panelH + 8 && rect.top > spaceBelow;

    panel.style.width = rect.width + "px";
    panel.style.left = rect.left + "px";

    if (openUp) {
        panel.style.top = "auto";
        panel.style.bottom = window.innerHeight - rect.top + 4 + "px";
    } else {
        panel.style.bottom = "auto";
        panel.style.top = rect.bottom + 4 + "px";
    }
}

function buildCustomSelect(nativeSelect) {
    var wrapper = document.createElement("div");
    wrapper.className = "custom-select-wrapper";

    var trigger = document.createElement("div");
    trigger.className = "custom-select-trigger";
    trigger.setAttribute("tabindex", "0");
    trigger.setAttribute("role", "combobox");
    trigger.setAttribute("aria-haspopup", "listbox");
    trigger.setAttribute("aria-expanded", "false");

    var trigText = document.createElement("span");
    trigText.className = "trigger-text";

    var caretIcon = document.createElement("span");
    caretIcon.className = "material-icons-round caret-icon";
    caretIcon.textContent = "expand_more";

    trigger.appendChild(trigText);
    trigger.appendChild(caretIcon);
    wrapper.appendChild(trigger);

    /* Portal dropdown — appended to <body> directly */
    var dropdown = document.createElement("div");
    dropdown.className = "custom-select-dropdown";
    dropdown.setAttribute("role", "listbox");
    document.body.appendChild(dropdown);

    var realOptions = Array.from(nativeSelect.options).filter(function (o) {
        return o.value !== "";
    });
    var useSearch = realOptions.length >= 5;
    var searchInput = null;

    if (useSearch) {
        var searchWrap = document.createElement("div");
        searchWrap.className = "custom-select-search";
        var searchIcon = document.createElement("span");
        searchIcon.className = "material-icons-round";
        searchIcon.textContent = "search";
        searchInput = document.createElement("input");
        searchInput.type = "text";
        searchInput.placeholder = "Search...";
        searchInput.setAttribute("autocomplete", "off");
        searchWrap.appendChild(searchIcon);
        searchWrap.appendChild(searchInput);
        dropdown.appendChild(searchWrap);
    }

    function buildOptions(filter) {
        var existing = dropdown.querySelectorAll(
            ".custom-select-option, .custom-select-empty",
        );
        existing.forEach(function (el) {
            el.remove();
        });

        var opts = Array.from(nativeSelect.options);
        var filtered = opts.filter(function (o) {
            if (!filter) return true;
            return o.textContent.toLowerCase().includes(filter.toLowerCase());
        });

        if (filtered.length === 0) {
            var empty = document.createElement("div");
            empty.className = "custom-select-empty";
            empty.textContent = "No results found";
            dropdown.appendChild(empty);
            return;
        }

        var bloodColors = {
            "A+": "#e53935",
            "A-": "#ef9a9a",
            "B+": "#1e88e5",
            "B-": "#90caf9",
            "AB+": "#8e24aa",
            "AB-": "#ce93d8",
            "O+": "#43a047",
            "O-": "#a5d6a7",
        };

        filtered.forEach(function (opt) {
            var item = document.createElement("div");
            item.className = "custom-select-option";
            item.setAttribute("role", "option");
            item.dataset.value = opt.value;

            if (opt.value === "" || opt.disabled)
                item.classList.add("placeholder-opt");
            if (
                opt.value !== "" &&
                opt === nativeSelect.options[nativeSelect.selectedIndex]
            )
                item.classList.add("selected");

            if (bloodColors[opt.value]) {
                var dot = document.createElement("span");
                dot.className = "opt-dot";
                dot.style.background = bloodColors[opt.value];
                item.appendChild(dot);
            }

            var label = document.createElement("span");
            label.textContent = opt.textContent;
            item.appendChild(label);

            item.addEventListener("mousedown", function (e) {
                e.preventDefault();
                selectOption(opt.value, opt.textContent);
                closeDropdown();
            });

            dropdown.appendChild(item);
        });
    }

    function selectOption(value, label) {
        nativeSelect.value = value;
        nativeSelect.dispatchEvent(new Event("change", { bubbles: true }));
        trigText.textContent = label;
        wrapper.classList[value === "" ? "remove" : "add"]("has-value");
        buildOptions(searchInput ? searchInput.value : "");
    }

    function openDropdown() {
        if (_activeDropdown && _activeDropdown !== dropdown) {
            _activeDropdown.classList.remove("open");
            var prevWrapper = document.querySelector(
                ".custom-select-wrapper.open",
            );
            if (prevWrapper) prevWrapper.classList.remove("open");
        }
        _activeDropdown = dropdown;
        wrapper.classList.add("open");
        trigger.setAttribute("aria-expanded", "true");
        buildOptions("");
        dropdown.classList.add("open");
        requestAnimationFrame(function () {
            _positionDropdown(dropdown, trigger);
        });
        if (searchInput) {
            searchInput.value = "";
            setTimeout(function () {
                searchInput.focus();
            }, 40);
        }
    }

    function closeDropdown() {
        dropdown.classList.remove("open");
        wrapper.classList.remove("open");
        trigger.setAttribute("aria-expanded", "false");
        if (_activeDropdown === dropdown) _activeDropdown = null;
    }

    trigger.addEventListener("click", function (e) {
        e.stopPropagation();
        wrapper.classList.contains("open") ? closeDropdown() : openDropdown();
    });

    trigger.addEventListener("keydown", function (e) {
        if (e.key === "Enter" || e.key === " ") {
            e.preventDefault();
            wrapper.classList.contains("open")
                ? closeDropdown()
                : openDropdown();
        } else if (e.key === "Escape") {
            closeDropdown();
        } else if (e.key === "ArrowDown" || e.key === "ArrowUp") {
            e.preventDefault();
            if (!wrapper.classList.contains("open")) openDropdown();
            navigateOptions(e.key === "ArrowDown" ? 1 : -1);
        }
    });

    if (searchInput) {
        searchInput.addEventListener("input", function () {
            buildOptions(searchInput.value);
        });
        searchInput.addEventListener("keydown", function (e) {
            if (e.key === "Escape") closeDropdown();
            if (e.key === "ArrowDown") {
                e.preventDefault();
                navigateOptions(1);
            }
            if (e.key === "ArrowUp") {
                e.preventDefault();
                navigateOptions(-1);
            }
        });
    }

    function navigateOptions(dir) {
        var items = Array.from(
            dropdown.querySelectorAll(
                ".custom-select-option:not(.placeholder-opt)",
            ),
        );
        if (!items.length) return;
        var kbdFocused = dropdown.querySelector(
            ".custom-select-option.kbd-focus",
        );
        var idx = kbdFocused ? items.indexOf(kbdFocused) : -1;
        if (kbdFocused) kbdFocused.classList.remove("kbd-focus");
        idx = (idx + dir + items.length) % items.length;
        items[idx].classList.add("kbd-focus");
        items[idx].scrollIntoView({ block: "nearest" });
    }

    var currentOpt = nativeSelect.options[nativeSelect.selectedIndex];
    if (currentOpt) {
        trigText.textContent = currentOpt.textContent;
        if (currentOpt.value !== "") wrapper.classList.add("has-value");
    }

    nativeSelect.parentNode.insertBefore(wrapper, nativeSelect.nextSibling);
    wrapper._dropdown = dropdown;
    wrapper._closeDropdown = closeDropdown;
}

/* Global close on outside click */
document.addEventListener("click", function (e) {
    if (
        !e.target.closest(".custom-select-wrapper") &&
        !e.target.closest(".custom-select-dropdown")
    ) {
        document
            .querySelectorAll(".custom-select-wrapper.open")
            .forEach(function (w) {
                if (w._closeDropdown) w._closeDropdown();
            });
    }
});

/* Reposition on scroll/resize */
window.addEventListener(
    "scroll",
    function () {
        if (!_activeDropdown) return;
        var openWrapper = document.querySelector(".custom-select-wrapper.open");
        if (openWrapper)
            _positionDropdown(
                _activeDropdown,
                openWrapper.querySelector(".custom-select-trigger"),
            );
    },
    true,
);

window.addEventListener("resize", function () {
    if (!_activeDropdown) return;
    var openWrapper = document.querySelector(".custom-select-wrapper.open");
    if (openWrapper)
        _positionDropdown(
            _activeDropdown,
            openWrapper.querySelector(".custom-select-trigger"),
        );
});

/* ════════════════════════════════════════
   START CUSTOM DATEPICKER
════════════════════════════════════════ */
(function () {
    var MONTHS = [
        "January",
        "February",
        "March",
        "April",
        "May",
        "June",
        "July",
        "August",
        "September",
        "October",
        "November",
        "December",
    ];
    var WDS = ["Su", "Mo", "Tu", "We", "Th", "Fr", "Sa"];
    var _activeDP = null;

    function _posDP(panel, trigger) {
        var r = trigger.getBoundingClientRect();
        var panelH = 320;
        var spaceBelow = window.innerHeight - r.bottom;
        panel.style.left = r.left + "px";
        panel.style.width = Math.max(r.width, 280) + "px";
        if (spaceBelow < panelH + 8 && r.top > spaceBelow) {
            panel.style.top = "auto";
            panel.style.bottom = window.innerHeight - r.top + 4 + "px";
        } else {
            panel.style.bottom = "auto";
            panel.style.top = r.bottom + 4 + "px";
        }
    }

    function buildDatepicker(input) {
        var wrapper = input.parentElement;
        var trigger = document.createElement("div");
        trigger.className = "dp-trigger";
        trigger.setAttribute("tabindex", "0");
        trigger.innerHTML =
            '<span class="dp-text">Select date</span><span class="material-icons-round nav-icon">calendar_month</span>';
        wrapper.appendChild(trigger);

        var today = new Date();
        var selDate = null;

        // ── server value অথবা data-dp-value থেকে initial date নাও ──
        var initVal =
            input.getAttribute("value") ||
            input.dataset.dpValue ||
            input.value ||
            "";
        if (initVal) {
            var parts = initVal.split("-");
            if (parts.length === 3) {
                selDate = new Date(+parts[0], +parts[1] - 1, +parts[2]);
            }
        }

        var viewYear = selDate ? selDate.getFullYear() : today.getFullYear();
        var viewMonth = selDate ? selDate.getMonth() : today.getMonth();
        var mode = "days";
        var yearRangeStart = Math.floor(viewYear / 12) * 12;

        var panel = document.createElement("div");
        panel.className = "dp-panel";
        document.body.appendChild(panel);

        function formatDisplay(d) {
            if (!d) return null;
            return (
                d.getDate() +
                " " +
                MONTHS[d.getMonth()].slice(0, 3) +
                " " +
                d.getFullYear()
            );
        }

        function syncTrigger() {
            var txt = trigger.querySelector(".dp-text");
            if (selDate) {
                txt.textContent = formatDisplay(selDate);
                trigger.classList.add("dp-has-value");
            } else {
                txt.textContent = "";
                trigger.classList.remove("dp-has-value");
            }
            if (selDate) {
                var y = selDate.getFullYear();
                var m = String(selDate.getMonth() + 1).padStart(2, "0");
                var d = String(selDate.getDate()).padStart(2, "0");
                input.value = y + "-" + m + "-" + d;
            } else {
                input.value = "";
            }
            var ig = wrapper.closest(".input-group");
            if (ig) {
                if (input.value) ig.classList.add("is-filled");
                else ig.classList.remove("is-filled");
            }

            // ── Livewire sync ──
            input.dispatchEvent(new Event("input", { bubbles: true }));
            input.dispatchEvent(new Event("change", { bubbles: true }));
        }

        // ── বাইরে থেকে date set করার জন্য (edit page) ──
        input._dpTriggerSync = function (dateStr) {
            if (!dateStr) return;
            var parts = dateStr.split("-");
            if (parts.length === 3) {
                selDate = new Date(+parts[0], +parts[1] - 1, +parts[2]);
                viewYear = selDate.getFullYear();
                viewMonth = selDate.getMonth();
                syncTrigger();
            }
        };

        function renderPanel() {
            panel.innerHTML = "";
            var hdr = document.createElement("div");
            hdr.className = "dp-header";

            var prevBtn = document.createElement("button");
            prevBtn.className = "dp-nav-btn";
            prevBtn.type = "button";
            prevBtn.textContent = "<<";
            var nextBtn = document.createElement("button");
            nextBtn.className = "dp-nav-btn";
            nextBtn.type = "button";
            nextBtn.textContent = ">>";

            var center = document.createElement("div");
            center.className = "dp-header-center";

            if (mode === "days") {
                var mBtn = document.createElement("button");
                mBtn.className = "dp-month-btn";
                mBtn.type = "button";
                mBtn.textContent = MONTHS[viewMonth];
                mBtn.onclick = function () {
                    mode = "months";
                    renderPanel();
                };

                var yBtn = document.createElement("button");
                yBtn.className = "dp-year-btn";
                yBtn.type = "button";
                yBtn.textContent = viewYear;
                yBtn.onclick = function () {
                    mode = "years";
                    yearRangeStart = Math.floor(viewYear / 12) * 12;
                    renderPanel();
                };

                center.appendChild(mBtn);
                center.appendChild(yBtn);
                prevBtn.onclick = function () {
                    viewMonth--;
                    if (viewMonth < 0) {
                        viewMonth = 11;
                        viewYear--;
                    }
                    renderPanel();
                };
                nextBtn.onclick = function () {
                    viewMonth++;
                    if (viewMonth > 11) {
                        viewMonth = 0;
                        viewYear++;
                    }
                    renderPanel();
                };
            } else if (mode === "months") {
                var yBtn2 = document.createElement("button");
                yBtn2.className = "dp-year-btn";
                yBtn2.type = "button";
                yBtn2.textContent = viewYear;
                yBtn2.onclick = function () {
                    mode = "years";
                    yearRangeStart = Math.floor(viewYear / 12) * 12;
                    renderPanel();
                };
                center.appendChild(yBtn2);
                prevBtn.onclick = function () {
                    viewYear--;
                    renderPanel();
                };
                nextBtn.onclick = function () {
                    viewYear++;
                    renderPanel();
                };
            } else {
                var rangeLabel = document.createElement("button");
                rangeLabel.className = "dp-year-btn";
                rangeLabel.type = "button";
                rangeLabel.style.cursor = "default";
                rangeLabel.textContent =
                    yearRangeStart + " - " + (yearRangeStart + 11);
                center.appendChild(rangeLabel);
                prevBtn.onclick = function () {
                    yearRangeStart -= 12;
                    renderPanel();
                };
                nextBtn.onclick = function () {
                    yearRangeStart += 12;
                    renderPanel();
                };
            }

            hdr.appendChild(prevBtn);
            hdr.appendChild(center);
            hdr.appendChild(nextBtn);
            panel.appendChild(hdr);

            if (mode === "days") {
                var wdRow = document.createElement("div");
                wdRow.className = "dp-weekdays";
                WDS.forEach(function (w) {
                    var wd = document.createElement("div");
                    wd.className = "dp-wd";
                    wd.textContent = w;
                    wdRow.appendChild(wd);
                });
                panel.appendChild(wdRow);

                var grid = document.createElement("div");
                grid.className = "dp-days";
                var first = new Date(viewYear, viewMonth, 1).getDay();
                var daysInMonth = new Date(
                    viewYear,
                    viewMonth + 1,
                    0,
                ).getDate();
                var daysInPrev = new Date(viewYear, viewMonth, 0).getDate();

                for (var i = 0; i < first; i++) {
                    var d = document.createElement("button");
                    d.type = "button";
                    d.className = "dp-day dp-day-other";
                    d.textContent = daysInPrev - first + 1 + i;
                    grid.appendChild(d);
                }
                for (var day = 1; day <= daysInMonth; day++) {
                    (function (day) {
                        var d = document.createElement("button");
                        d.type = "button";
                        d.className = "dp-day";
                        d.textContent = day;
                        var isToday =
                            day === today.getDate() &&
                            viewMonth === today.getMonth() &&
                            viewYear === today.getFullYear();
                        var isSel =
                            selDate &&
                            day === selDate.getDate() &&
                            viewMonth === selDate.getMonth() &&
                            viewYear === selDate.getFullYear();
                        if (isSel) d.classList.add("dp-day-selected");
                        else if (isToday) d.classList.add("dp-day-today");
                        d.onclick = function () {
                            selDate = new Date(viewYear, viewMonth, day);
                            syncTrigger();
                            closePanel();
                        };
                        grid.appendChild(d);
                    })(day);
                }
                var total = first + daysInMonth;
                var nextDays = total % 7 === 0 ? 0 : 7 - (total % 7);
                for (var nd = 1; nd <= nextDays; nd++) {
                    var dn = document.createElement("button");
                    dn.type = "button";
                    dn.className = "dp-day dp-day-other";
                    dn.textContent = nd;
                    grid.appendChild(dn);
                }
                panel.appendChild(grid);
            } else if (mode === "months") {
                var mgrid = document.createElement("div");
                mgrid.className = "dp-grid-view";
                MONTHS.forEach(function (m, mi) {
                    var btn = document.createElement("button");
                    btn.type = "button";
                    btn.className = "dp-grid-item";
                    btn.textContent = m.slice(0, 3);
                    if (mi === viewMonth) btn.classList.add("dp-grid-selected");
                    if (
                        mi === today.getMonth() &&
                        viewYear === today.getFullYear()
                    )
                        btn.classList.add("dp-grid-current");
                    btn.onclick = function () {
                        viewMonth = mi;
                        mode = "days";
                        renderPanel();
                    };
                    mgrid.appendChild(btn);
                });
                panel.appendChild(mgrid);
            } else {
                var ygrid = document.createElement("div");
                ygrid.className = "dp-grid-view";
                for (var y = yearRangeStart; y < yearRangeStart + 12; y++) {
                    (function (y) {
                        var btn = document.createElement("button");
                        btn.type = "button";
                        btn.className = "dp-grid-item";
                        btn.textContent = y;
                        if (y === viewYear)
                            btn.classList.add("dp-grid-selected");
                        if (y === today.getFullYear())
                            btn.classList.add("dp-grid-current");
                        btn.onclick = function () {
                            viewYear = y;
                            mode = "months";
                            renderPanel();
                        };
                        ygrid.appendChild(btn);
                    })(y);
                }
                panel.appendChild(ygrid);
            }

            var footer = document.createElement("div");
            footer.className = "dp-footer";
            var todayBtn = document.createElement("button");
            todayBtn.type = "button";
            todayBtn.className = "dp-today-btn";
            todayBtn.textContent = "Today";
            todayBtn.onclick = function () {
                selDate = new Date(
                    today.getFullYear(),
                    today.getMonth(),
                    today.getDate(),
                );
                viewYear = selDate.getFullYear();
                viewMonth = selDate.getMonth();
                mode = "days";
                syncTrigger();
                closePanel();
            };
            var clearBtn = document.createElement("button");
            clearBtn.type = "button";
            clearBtn.className = "dp-clear-btn";
            clearBtn.textContent = "Clear";
            clearBtn.onclick = function () {
                selDate = null;
                syncTrigger();
                closePanel();
            };
            footer.appendChild(todayBtn);
            footer.appendChild(clearBtn);
            panel.appendChild(footer);
        }

        function openPanel() {
            if (_activeDP && _activeDP !== panel) closeActiveDP();
            renderPanel();
            panel.classList.add("dp-panel-open");
            trigger.classList.add("dp-open");
            _activeDP = panel;
            _activeDP._trigger = trigger;
            _posDP(panel, trigger);

            // ── panel এর ভেতরের click bubble বন্ধ ──
            panel.onclick = function (e) {
                e.stopPropagation();
            };
        }

        function closePanel() {
            panel.classList.remove("dp-panel-open");
            trigger.classList.remove("dp-open");
            if (_activeDP === panel) _activeDP = null;
        }

        trigger.addEventListener("click", function (e) {
            e.stopPropagation();
            panel.classList.contains("dp-panel-open")
                ? closePanel()
                : openPanel();
        });
        trigger.addEventListener("keydown", function (e) {
            if (e.key === "Enter" || e.key === " ") {
                e.preventDefault();
                openPanel();
            }
            if (e.key === "Escape") closePanel();
        });

        syncTrigger();
    }

    function closeActiveDP() {
        if (_activeDP) {
            _activeDP.classList.remove("dp-panel-open");
            if (_activeDP._trigger)
                _activeDP._trigger.classList.remove("dp-open");
            _activeDP = null;
        }
    }

    document.addEventListener("click", function (e) {
        if (!_activeDP) return;
        var insidePanel = e.target.closest(".dp-panel");
        var insideTrigger = e.target.closest(".dp-trigger");
        if (insidePanel || insideTrigger) return;
        closeActiveDP();
    });

    window.addEventListener(
        "scroll",
        function () {
            if (_activeDP && _activeDP._trigger)
                _posDP(_activeDP, _activeDP._trigger);
        },
        true,
    );
    window.addEventListener("resize", function () {
        if (_activeDP && _activeDP._trigger)
            _posDP(_activeDP, _activeDP._trigger);
    });

    window._initDatepickers = function () {
        document
            .querySelectorAll('.input-group-outline input[type="date"]')
            .forEach(function (input) {
                if (input._dpInit) {
                    // ── already init — শুধু value sync করো ──
                    var val =
                        input.dataset.dpValue ||
                        input.getAttribute("value") ||
                        "";
                    if (val && input._dpTriggerSync) {
                        input._dpTriggerSync(val);
                    }
                    return;
                }
                input._dpInit = true;

                // ── server value আগে set করো ──
                var serverVal =
                    input.getAttribute("value") || input.dataset.dpValue || "";
                if (serverVal && !input.value) {
                    input.value = serverVal;
                }

                buildDatepicker(input);
            });
    };
})();

document.addEventListener("DOMContentLoaded", function () {
    document
        .querySelectorAll(".input-group-outline .form-select")
        .forEach(buildCustomSelect);
    _initDatepickers();

    document
        .querySelectorAll(".input-group-outline .form-control")
        .forEach(function (el) {
            var parent = el.closest(".input-group");
            if (el.value && el.value.trim() !== "" && parent)
                parent.classList.add("is-filled");
        });
});

// ── Livewire morph এর পরে datepicker re-sync ──
document.addEventListener("livewire:initialized", function () {
    Livewire.hook("morph.updated", function () {
        setTimeout(function () {
            _initDatepickers();
        }, 0);
    });
});
/* ════════════════════════════════════════
   END CUSTOM DATEPICKER
════════════════════════════════════════ */

/* Photo preview */
function previewPhoto(input, boxId) {
    var box = document.getElementById(boxId);
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function (e) {
            box.innerHTML =
                '<img src="' +
                e.target.result +
                '" class="photo-preview" alt="Preview"><br>' +
                '<small style="color:var(--muted);font-size:.7rem;margin-top:6px">' +
                input.files[0].name +
                "</small>" +
                '<input type="file" accept="image/*" onchange="previewPhoto(this,\'' +
                boxId +
                '\')" style="position:absolute;inset:0;opacity:0;cursor:pointer">';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

var _s = document.createElement("style");
_s.textContent =
    "@keyframes spin{from{transform:rotate(0)}to{transform:rotate(360deg)}}";
document.head.appendChild(_s);
