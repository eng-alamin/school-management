/* ═══════════════════════════════════════════════════════════════════
   EMPLOYEE.JS — Employee Module Language Extension
   Extends lang.js translations with employee-specific keys
   and applies them to the DOM on language change.
═══════════════════════════════════════════════════════════════════ */

import { translations } from "./lang.js";

/* ── Employee translation keys ── */
const employeeTranslations = {
    en: {
        // ── Department component ──
        deptHeaderTitle: "All Departments",
        deptHeaderSubtitle:
            "Manage departments, create, update, and organize easily.",
        deptNewBtn: "New Department",
        deptDeleteTitle: "Delete Department?",
        deptDeleteMsg: "This action cannot be undone.",
        deptThSl: "SL",
        deptThName: "Name",
        deptThActions: "Actions",
        deptModalActionEdit: "Edit",
        deptModalActionCreate: "Create",
        deptModalSuffix: "Department",
        deptLblName: "Name",

        // ── Designation component ──
        desigHeaderTitle: "All Designations",
        desigHeaderSubtitle:
            "Manage designations, create, update, and organize easily.",
        desigNewBtn: "New Designation",
        desigDeleteTitle: "Delete Designation?",
        desigDeleteMsg: "This action cannot be undone.",
        desigThSl: "SL",
        desigThName: "Name",
        desigThActions: "Actions",
        desigModalActionEdit: "Edit",
        desigModalActionCreate: "Create",
        desigModalSuffix: "Designation",
        desigLblName: "Name",

        // ── Employee List component ──
        empListHeaderTitle: "All Employees",
        empListHeaderSubtitle:
            "Manage employees, view details, and organize easily.",
        empDeleteTitle: "Delete Employee?",
        empDeleteMsg: "This action cannot be undone.",
        empThSl: "SL",
        empThName: "Name",
        empThRole: "Role",
        empThDesignation: "Designation",
        empThDepartment: "Department",
        empThEmail: "Email",
        empThPhone: "Phone",
        empThActions: "Actions",

        // ── Employee Add / Edit shared form field labels ──
        empFormLabels: {
            role: "Role",
            joiningDate: "Joining Date",
            designation: "Designation",
            department: "Department",
            qualification: "Qualification",
            totalExperience: "Total Experience",
            experienceDetail: "Experience Details",
            name: "Name",
            dob: "Date Of Birth",
            religion: "Religion",
            mobile: "Mobile",
            email: "Email",
            presentAddress: "Present Address",
            permanentAddress: "Permanent Address",
            photo: "Photo",
            username: "Username",
            password: "Password",
            bankName: "Bank Name",
            holderName: "Holder Name",
            bankBranch: "Bank Branch",
            ifscCode: "IFSC Code",
            accountNo: "Account No",
            bankAddress: "Bank Address",
        },

        // ── Employee Add component ──
        empAddHeaderTitle: "Employee Registration",
        empAddHeaderSubtitle: "Create new employee record",

        // ── Employee Edit component ──
        empEditHeaderTitle: "Employee Registration",
        empEditHeaderSubtitle: "Update existing employee record",

        // ── Employee View component ──
        empViewHeaderTitle: "Employee Overview",
    },
    bn: {
        // ── Department component ──
        deptHeaderTitle: "সকল বিভাগ",
        deptHeaderSubtitle:
            "বিভাগ পরিচালনা করুন, সহজে তৈরি, আপডেট এবং সংগঠিত করুন।",
        deptNewBtn: "নতুন বিভাগ",
        deptDeleteTitle: "বিভাগ মুছবেন?",
        deptDeleteMsg: "এই ক্রিয়াটি পূর্বাবস্থায় ফেরানো যাবে না।",
        deptThSl: "ক্রমিক",
        deptThName: "নাম",
        deptThActions: "কার্যক্রম",
        deptModalActionEdit: "সম্পাদনা",
        deptModalActionCreate: "তৈরি",
        deptModalSuffix: "বিভাগ",
        deptLblName: "নাম",

        // ── Designation component ──
        desigHeaderTitle: "সকল পদবি",
        desigHeaderSubtitle:
            "পদবি পরিচালনা করুন, সহজে তৈরি, আপডেট এবং সংগঠিত করুন।",
        desigNewBtn: "নতুন পদবি",
        desigDeleteTitle: "পদবি মুছবেন?",
        desigDeleteMsg: "এই ক্রিয়াটি পূর্বাবস্থায় ফেরানো যাবে না।",
        desigThSl: "ক্রমিক",
        desigThName: "নাম",
        desigThActions: "কার্যক্রম",
        desigModalActionEdit: "সম্পাদনা",
        desigModalActionCreate: "তৈরি",
        desigModalSuffix: "পদবি",
        desigLblName: "নাম",

        // ── Employee List component ──
        empListHeaderTitle: "সকল কর্মচারী",
        empListHeaderSubtitle:
            "কর্মচারী পরিচালনা করুন, বিবরণ দেখুন এবং সহজে সংগঠিত করুন।",
        empDeleteTitle: "কর্মচারী মুছবেন?",
        empDeleteMsg: "এই ক্রিয়াটি পূর্বাবস্থায় ফেরানো যাবে না।",
        empThSl: "ক্রমিক",
        empThName: "নাম",
        empThRole: "ভূমিকা",
        empThDesignation: "পদবি",
        empThDepartment: "বিভাগ",
        empThEmail: "ইমেইল",
        empThPhone: "ফোন",
        empThActions: "কার্যক্রম",

        // ── Employee Add / Edit shared form field labels ──
        empFormLabels: {
            role: "ভূমিকা",
            joiningDate: "যোগদানের তারিখ",
            designation: "পদবি",
            department: "বিভাগ",
            qualification: "যোগ্যতা",
            totalExperience: "মোট অভিজ্ঞতা",
            experienceDetail: "অভিজ্ঞতার বিবরণ",
            name: "নাম",
            dob: "জন্ম তারিখ",
            religion: "ধর্ম",
            mobile: "মোবাইল",
            email: "ইমেইল",
            presentAddress: "বর্তমান ঠিকানা",
            permanentAddress: "স্থায়ী ঠিকানা",
            photo: "ছবি",
            username: "ইউজারনেম",
            password: "পাসওয়ার্ড",
            bankName: "ব্যাংকের নাম",
            holderName: "হোল্ডারের নাম",
            bankBranch: "ব্যাংক শাখা",
            ifscCode: "IFSC কোড",
            accountNo: "অ্যাকাউন্ট নম্বর",
            bankAddress: "ব্যাংক ঠিকানা",
        },

        // ── Employee Add component ──
        empAddHeaderTitle: "কর্মচারী নিবন্ধন",
        empAddHeaderSubtitle: "নতুন কর্মচারী রেকর্ড তৈরি করুন",

        // ── Employee Edit component ──
        empEditHeaderTitle: "কর্মচারী নিবন্ধন",
        empEditHeaderSubtitle: "বিদ্যমান কর্মচারী রেকর্ড আপডেট করুন",

        // ── Employee View component ──
        empViewHeaderTitle: "কর্মচারী ওভারভিউ",
    },
};

/* ── Merge into shared translations object ── */
Object.assign(translations.en, employeeTranslations.en);
Object.assign(translations.bn, employeeTranslations.bn);

/* ── Apply employee-specific DOM updates ── */
function applyEmployeeLang(lang) {
    const t = employeeTranslations[lang];
    const setText = (id, val) => {
        const el = document.getElementById(id);
        if (el) el.textContent = val;
    };

    // ── Apply shared form field labels to a component's <label> spans ──
    // prefix matches the blade id pattern e.g. "emp-add", "emp-edit"
    const applyFormLabels = (prefix, labels) => {
        setText(prefix + "-lbl-role", labels.role);
        setText(prefix + "-lbl-joining-date", labels.joiningDate);
        setText(prefix + "-lbl-designation", labels.designation);
        setText(prefix + "-lbl-department", labels.department);
        setText(prefix + "-lbl-qualification", labels.qualification);
        setText(prefix + "-lbl-total-experience", labels.totalExperience);
        setText(prefix + "-lbl-experience-detail", labels.experienceDetail);
        setText(prefix + "-lbl-name", labels.name);
        setText(prefix + "-lbl-dob", labels.dob);
        setText(prefix + "-lbl-religion", labels.religion);
        setText(prefix + "-lbl-mobile", labels.mobile);
        setText(prefix + "-lbl-email", labels.email);
        setText(prefix + "-lbl-present-address", labels.presentAddress);
        setText(prefix + "-lbl-permanent-address", labels.permanentAddress);
        setText(prefix + "-lbl-photo", labels.photo);
        setText(prefix + "-lbl-username", labels.username);
        setText(prefix + "-lbl-password", labels.password);
        setText(prefix + "-lbl-bank-name", labels.bankName);
        setText(prefix + "-lbl-holder-name", labels.holderName);
        setText(prefix + "-lbl-bank-branch", labels.bankBranch);
        setText(prefix + "-lbl-ifsc-code", labels.ifscCode);
        setText(prefix + "-lbl-account-no", labels.accountNo);
        setText(prefix + "-lbl-bank-address", labels.bankAddress);
    };

    // ── Department ──
    setText("dept-header-title", t.deptHeaderTitle);
    setText("dept-header-subtitle", t.deptHeaderSubtitle);
    setText("newDepartmentBtn", t.deptNewBtn);
    setText("dept-delete-title", t.deptDeleteTitle);
    setText("dept-delete-msg", t.deptDeleteMsg);
    setText("dept-th-sl", t.deptThSl);
    setText("dept-th-name", t.deptThName);
    setText("dept-th-actions", t.deptThActions);
    setText("dept-modal-suffix", t.deptModalSuffix);
    setText("dept-lbl-name", t.deptLblName);
    const deptModalAction = document.getElementById("dept-modal-action");
    if (deptModalAction) {
        deptModalAction.textContent =
            deptModalAction.dataset.mode === "edit"
                ? t.deptModalActionEdit
                : t.deptModalActionCreate;
    }

    // ── Designation ──
    setText("desig-header-title", t.desigHeaderTitle);
    setText("desig-header-subtitle", t.desigHeaderSubtitle);
    setText("newDesignationBtn", t.desigNewBtn);
    setText("desig-delete-title", t.desigDeleteTitle);
    setText("desig-delete-msg", t.desigDeleteMsg);
    setText("desig-th-sl", t.desigThSl);
    setText("desig-th-name", t.desigThName);
    setText("desig-th-actions", t.desigThActions);
    setText("desig-modal-suffix", t.desigModalSuffix);
    setText("desig-lbl-name", t.desigLblName);
    const desigModalAction = document.getElementById("desig-modal-action");
    if (desigModalAction) {
        desigModalAction.textContent =
            desigModalAction.dataset.mode === "edit"
                ? t.desigModalActionEdit
                : t.desigModalActionCreate;
    }

    // ── Employee List ──
    setText("emp-list-header-title", t.empListHeaderTitle);
    setText("emp-list-header-subtitle", t.empListHeaderSubtitle);
    setText("emp-delete-title", t.empDeleteTitle);
    setText("emp-delete-msg", t.empDeleteMsg);
    setText("emp-th-sl", t.empThSl);
    setText("emp-th-name", t.empThName);
    setText("emp-th-role", t.empThRole);
    setText("emp-th-designation", t.empThDesignation);
    setText("emp-th-department", t.empThDepartment);
    setText("emp-th-email", t.empThEmail);
    setText("emp-th-phone", t.empThPhone);
    setText("emp-th-actions", t.empThActions);

    // ── Employee Add ──
    setText("emp-add-header-title", t.empAddHeaderTitle);
    setText("emp-add-header-subtitle", t.empAddHeaderSubtitle);
    applyFormLabels("emp-add", t.empFormLabels);

    // ── Employee Edit ──
    setText("emp-edit-header-title", t.empEditHeaderTitle);
    setText("emp-edit-header-subtitle", t.empEditHeaderSubtitle);
    applyFormLabels("emp-edit", t.empFormLabels);

    // ── Employee View ──
    setText("emp-view-header-title", t.empViewHeaderTitle);
}

// Apply on load
applyEmployeeLang(localStorage.getItem("lang") || "en");

// Re-apply whenever lang.js switches language
document.addEventListener("langChanged", (e) => {
    applyEmployeeLang(e.detail.lang);
});

export { employeeTranslations, applyEmployeeLang };
