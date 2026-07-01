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
        dashboard: "Dashboard",
        analytics: "Analytics",
        discover: "Discover",
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
        stores: "Stores",
        suppliers: "Suppliers",
        purchases: "Purchases",
        sales: "Sales",
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

        // Inventory
        inventory: "Inventory",
        inventoryCategoryTitle: "Inventory Category",
        inventoryCategorySubtitle:
            "Manage inventory categories, create, update, and organize categories easily.",
        inventoryCategorySearch: "Search",
        inventoryCategoryAddBtn: "Add Category",
        inventoryStoreTitle: "Inventory Store",
        inventoryStoreSubtitle:
            "Manage inventory stores, create, update, and organize stores easily.",
        inventoryStoreSearch: "Search",
        inventoryStoreAddBtn: "Add Store",
        inventorySupplierTitle: "Inventory Supplier",
        inventorySupplierSubtitle:
            "Manage inventory suppliers, create, update, and organize suppliers easily.",
        inventorySupplierSearch: "Search",
        inventorySupplierAddBtn: "Add Supplier",
        inventoryUnitTitle: "Inventory Unit",
        inventoryUnitSubtitle:
            "Manage inventory units, create, update, and organize units easily.",
        inventoryUnitSearch: "Search",
        inventoryUnitAddBtn: "Add Unit",
        inventoryProductTitle: "Inventory Product",
        inventoryProductSubtitle:
            "Manage inventory products, create, update, and organize products easily.",
        inventoryProductSearch: "Search",
        inventoryProductCategoryBtn: "Category",
        inventoryProductUnitBtn: "Unit",
        inventoryProductAddBtn: "Add Product",

        // Admission
        admission: "Admission",
        createAdmission: "Create Admission",
        onlineAdmission: "Online Admission",

        // Students & Parents
        category: "Category",
        students: "Students",
        parents: "Parents",

        // Employees
        employees: "Employees",
        employeeList: "Employee List",
        department: "Department",
        designation: "Designation",

        // Card Management
        cardManagement: "Card Management",
        idCardTemplate: "Id Card Template",
        studentIdCard: "Student Id Card",
        employeeIdCard: "Employee Id Card",
        admitCardTemplate: "Admit Card Template",
        admitCardGenerate: "Admit Card Generate",

        // Certificate Management
        certificateManagement: "Certificate Management",
        certificateTemplate: "Certificate Template",
        generateStudent: "Generate Student",
        generateEmployee: "Generate Employee",

        humanResource: "Human Resources",

        // Academic
        academic: "Academic",
        sessions: "Sessions",
        classSection: "Class & Section",
        subject: "Subject",
        classAssign: "Class Assign",
        teacherAssign: "Teacher Assign",
        classSchedule: "Class Schedule",
        teacherSchedule: "Teacher Schedule",
        studentPromotion: "Student Promotion",

        homeWork: "Home Work",

        // Exam Master
        examMaster: "Exam Master",
        examSetup: "Exam Setup",
        examSchedule: "Exam Schedule",
        marks: "Marks",

        // Attendance
        attendance: "Attendance",
        attendanceStudent: "Student",
        attendanceEmployee: "Employee",
        attendanceExam: "Exam",

        events: "Events",
        bulkSmsEmail: "Bulk Sms And Email",

        // Student Accounting
        studentAccounting: "Student Accounting",
        feesType: "Fees Type",
        feesGroup: "Fees Group",
        fineSetup: "Fine Setup",
        feesAllocation: "Fees Allocation",
        feesPayInvoice: "Fees Pay / Invoice",

        // Office Accounting
        officeAccounting: "Office Accounting",
        deposit: "Deposit",
        expense: "Expense",
        transactions: "Transactions",
        voucherHead: "Voucher Head",

        // Salary Management
        salaryManagement: "Salary Management",
        salaryTemplate: "Salary Template",
        salaryAssign: "Salary Assign",
        salaryPayment: "Salary Payment",

        leaves: "Leaves",
        mailbox: "Mailbox",
        notices: "Notices",
        alumni: "Alumni",

        // Settings
        institutionSettings: "Institution Settings",
        cronJob: "Cron Job",
        activityLogs: "Activity Logs",
        loginLogs: "Login Logs",
        clearCache: "Clear Cache",

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
        dashboard: "ড্যাশবোর্ড",
        analytics: "বিশ্লেষণ",
        discover: "আবিষ্কার",
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
        stores: "স্টোর",
        suppliers: "সরবরাহকারী",
        purchases: "ক্রয়",
        sales: "বিক্রয়",
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

        // Inventory
        inventory: "ইনভেন্টরি",
        inventoryCategoryTitle: "ইনভেন্টরি বিভাগ",
        inventoryCategorySubtitle:
            "ইনভেন্টরি বিভাগ পরিচালনা করুন, সহজে তৈরি, আপডেট এবং সংগঠিত করুন।",
        inventoryCategorySearch: "খুঁজুন",
        inventoryCategoryAddBtn: "বিভাগ যোগ করুন",
        inventoryStoreTitle: "ইনভেন্টরি স্টোর",
        inventoryStoreSubtitle:
            "ইনভেন্টরি স্টোর পরিচালনা করুন, সহজে তৈরি, আপডেট এবং সংগঠিত করুন।",
        inventoryStoreSearch: "খুঁজুন",
        inventoryStoreAddBtn: "স্টোর যোগ করুন",
        inventorySupplierTitle: "ইনভেন্টরি সরবরাহকারী",
        inventorySupplierSubtitle:
            "ইনভেন্টরি সরবরাহকারী পরিচালনা করুন, সহজে তৈরি, আপডেট এবং সংগঠিত করুন।",
        inventorySupplierSearch: "খুঁজুন",
        inventorySupplierAddBtn: "সরবরাহকারী যোগ করুন",
        inventoryUnitTitle: "ইনভেন্টরি ইউনিট",
        inventoryUnitSubtitle:
            "ইনভেন্টরি ইউনিট পরিচালনা করুন, সহজে তৈরি, আপডেট এবং সংগঠিত করুন।",
        inventoryUnitSearch: "খুঁজুন",
        inventoryUnitAddBtn: "ইউনিট যোগ করুন",
        inventoryProductTitle: "ইনভেন্টরি পণ্য",
        inventoryProductSubtitle:
            "ইনভেন্টরি পণ্য পরিচালনা করুন, সহজে তৈরি, আপডেট এবং সংগঠিত করুন।",
        inventoryProductSearch: "খুঁজুন",
        inventoryProductCategoryBtn: "বিভাগ",
        inventoryProductUnitBtn: "ইউনিট",
        inventoryProductAddBtn: "পণ্য যোগ করুন",

        // Admission
        admission: "ভর্তি",
        createAdmission: "ভর্তি তৈরি করুন",
        onlineAdmission: "অনলাইন ভর্তি",

        // Students & Parents
        category: "বিভাগ",
        students: "ছাত্রছাত্রী",
        parents: "পিতা-মাতা",

        // Employees
        employees: "কর্মচারী",
        employeeList: "কর্মচারী তালিকা",
        department: "বিভাগ",
        designation: "পদবি",

        // Card Management
        cardManagement: "কার্ড ব্যবস্থাপনা",
        idCardTemplate: "আইডি কার্ড টেমপ্লেট",
        studentIdCard: "ছাত্র আইডি কার্ড",
        employeeIdCard: "কর্মচারী আইডি কার্ড",
        admitCardTemplate: "অ্যাডমিট কার্ড টেমপ্লেট",
        admitCardGenerate: "অ্যাডমিট কার্ড তৈরি",

        // Certificate Management
        certificateManagement: "সার্টিফিকেট ব্যবস্থাপনা",
        certificateTemplate: "সার্টিফিকেট টেমপ্লেট",
        generateStudent: "ছাত্র সার্টিফিকেট তৈরি",
        generateEmployee: "কর্মচারী সার্টিফিকেট তৈরি",

        humanResource: "মানব সম্পদ",

        // Academic
        academic: "একাডেমিক",
        sessions: "সেশন",
        classSection: "শ্রেণি ও বিভাগ",
        subject: "বিষয়",
        classAssign: "ক্লাস নির্ধারণ",
        teacherAssign: "শিক্ষক নির্ধারণ",
        classSchedule: "ক্লাস সময়সূচি",
        teacherSchedule: "শিক্ষক সময়সূচি",
        studentPromotion: "ছাত্র পদোন্নতি",

        homeWork: "হোম ওয়ার্ক",

        // Exam Master
        examMaster: "পরীক্ষার মাস্টার",
        examSetup: "পরীক্ষা সেটআপ",
        examSchedule: "পরীক্ষার সময়সূচি",
        marks: "নম্বর",

        // Attendance
        attendance: "উপস্থিতি",
        attendanceStudent: "ছাত্র",
        attendanceEmployee: "কর্মচারী",
        attendanceExam: "পরীক্ষা",

        events: "ইভেন্ট",
        bulkSmsEmail: "বাল্ক এসএমএস এবং ইমেইল",

        // Student Accounting
        studentAccounting: "ছাত্র অ্যাকাউন্টিং",
        feesType: "ফি প্রকার",
        feesGroup: "ফি গ্রুপ",
        fineSetup: "জরিমানা সেটআপ",
        feesAllocation: "ফি বরাদ্দ",
        feesPayInvoice: "ফি পরিশোধ / চালান",

        // Office Accounting
        officeAccounting: "অফিস অ্যাকাউন্টিং",
        deposit: "জমা",
        expense: "ব্যয়",
        transactions: "লেনদেন",
        voucherHead: "ভাউচার হেড",

        // Salary Management
        salaryManagement: "বেতন ব্যবস্থাপনা",
        salaryTemplate: "বেতন টেমপ্লেট",
        salaryAssign: "বেতন নির্ধারণ",
        salaryPayment: "বেতন পরিশোধ",

        leaves: "ছুটি",
        mailbox: "মেইলবক্স",
        notices: "নোটিশ",
        alumni: "অ্যালামনাই",

        // Settings
        institutionSettings: "প্রতিষ্ঠানের সেটিংস",
        cronJob: "ক্রন জব",
        activityLogs: "কার্যক্রম লগ",
        loginLogs: "লগইন লগ",
        clearCache: "ক্যাশ পরিষ্কার",

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
    // Notify other modules (e.g. inventory.js) about language change
    document.dispatchEvent(
        new CustomEvent("langChanged", { detail: { lang } }),
    );

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
    setText("nav-dashboard", t.dashboard);
    setText("nav-analytics", t.analytics);
    setText("nav-discover", t.discover);
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
    setText("nav-stores", t.stores);
    setText("nav-suppliers", t.suppliers);
    setText("nav-purchases", t.purchases);
    setText("nav-sales", t.sales);
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

    // Inventory sidebar
    setText("nav-inventory", t.inventory);

    // Inventory module pages
    setText("inventory-category-title", t.inventoryCategoryTitle);
    setText("inventory-category-subtitle", t.inventoryCategorySubtitle);
    setPlaceholder("inventory-category-search", t.inventoryCategorySearch);
    setText("inventory-category-add-btn", t.inventoryCategoryAddBtn);

    setText("inventory-store-title", t.inventoryStoreTitle);
    setText("inventory-store-subtitle", t.inventoryStoreSubtitle);
    setPlaceholder("inventory-store-search", t.inventoryStoreSearch);
    setText("inventory-store-add-btn", t.inventoryStoreAddBtn);

    setText("inventory-supplier-title", t.inventorySupplierTitle);
    setText("inventory-supplier-subtitle", t.inventorySupplierSubtitle);
    setPlaceholder("inventory-supplier-search", t.inventorySupplierSearch);
    setText("inventory-supplier-add-btn", t.inventorySupplierAddBtn);

    setText("inventory-unit-title", t.inventoryUnitTitle);
    setText("inventory-unit-subtitle", t.inventoryUnitSubtitle);
    setPlaceholder("inventory-unit-search", t.inventoryUnitSearch);
    setText("inventory-unit-add-btn", t.inventoryUnitAddBtn);

    setText("inventory-product-title", t.inventoryProductTitle);
    setText("inventory-product-subtitle", t.inventoryProductSubtitle);
    setPlaceholder("inventory-product-search", t.inventoryProductSearch);
    setText("inventory-product-category-btn", t.inventoryProductCategoryBtn);
    setText("inventory-product-unit-btn", t.inventoryProductUnitBtn);
    setText("inventory-product-add-btn", t.inventoryProductAddBtn);

    // Admission
    setText("nav-admission", t.admission);
    setText("nav-create-admission", t.createAdmission);
    setText("nav-online-admission", t.onlineAdmission);

    // Students & Parents
    setText("nav-category", t.category);
    setText("nav-students", t.students);
    setText("nav-parents", t.parents);

    // Employees
    setText("nav-employees", t.employees);
    setText("nav-employee-list", t.employeeList);
    setText("nav-department", t.department);
    setText("nav-designation", t.designation);

    // Academic
    setText("nav-academic", t.academic);
    setText("nav-sessions", t.sessions);
    setText("nav-class-section", t.classSection);
    setText("nav-subject", t.subject);
    setText("nav-class-assign", t.classAssign);
    setText("nav-teacher-assign", t.teacherAssign);
    setText("nav-class-schedule", t.classSchedule);
    setText("nav-teacher-schedule", t.teacherSchedule);
    setText("nav-student-promotion", t.studentPromotion);

    // Homework
    setText("nav-homework", t.homeWork);

    // Exam Master
    setText("nav-exam-master", t.examMaster);
    setText("nav-exam-setup", t.examSetup);
    setText("nav-exam-schedule", t.examSchedule);
    setText("nav-marks", t.marks);

    // Attendance
    setText("nav-attendance", t.attendance);
    setText("nav-attendance-student", t.attendanceStudent);
    setText("nav-attendance-employee", t.attendanceEmployee);
    setText("nav-attendance-exam", t.attendanceExam);

    // Student Accounting
    setText("nav-student-accounting", t.studentAccounting);
    setText("nav-fees-type", t.feesType);
    setText("nav-fees-group", t.feesGroup);
    setText("nav-fine-setup", t.fineSetup);
    setText("nav-fees-allocation", t.feesAllocation);
    setText("nav-fees-pay-invoice", t.feesPayInvoice);

    // Office Accounting
    setText("nav-office-accounting", t.officeAccounting);
    setText("nav-deposit", t.deposit);
    setText("nav-expense", t.expense);
    setText("nav-transactions", t.transactions);
    setText("nav-voucher-head", t.voucherHead);

    // Salary Management
    setText("nav-salary-management", t.salaryManagement);
    setText("nav-salary-template", t.salaryTemplate);
    setText("nav-salary-assign", t.salaryAssign);
    setText("nav-salary-payment", t.salaryPayment);

    // Card Management
    setText("nav-card-management", t.cardManagement);
    setText("nav-id-card-template", t.idCardTemplate);
    setText("nav-student-id-card", t.studentIdCard);
    setText("nav-employee-id-card", t.employeeIdCard);
    setText("nav-admit-card-template", t.admitCardTemplate);
    setText("nav-admit-card-generate", t.admitCardGenerate);

    // Certificate Management
    setText("nav-certificate-management", t.certificateManagement);
    setText("nav-certificate-template", t.certificateTemplate);
    setText("nav-generate-student", t.generateStudent);
    setText("nav-generate-employee", t.generateEmployee);

    // Others
    setText("nav-leaves", t.leaves);
    setText("nav-events", t.events);
    setText("nav-mailbox", t.mailbox);
    setText("nav-notices", t.notices);
    setText("nav-bulk-sms-email", t.bulkSmsEmail);
    setText("nav-alumni", t.alumni);

    // Settings
    setText("nav-institution-settings", t.institutionSettings);
    setText("nav-cron-job", t.cronJob);
    setText("nav-activity-logs", t.activityLogs);
    setText("nav-login-logs", t.loginLogs);
    setText("nav-clear-cache", t.clearCache);
    setText("nav-pages-section", t.pagesLabel);

    // Page content
    setText("pageTitleEl", t.pageTitle);
    setText("cardHeaderTitle", t.allProducts);
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

// Expose to global scope for inline onclick handlers
window.setLang = setLang;
window.setLangFromSettings = setLangFromSettings;

export { translations, currentLang, applyLang, setLang, setLangFromSettings };
import("./table.js");
