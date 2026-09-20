/* ═══════════════════════════════════════════════════════════════════
   HEADER.JS — Shared Card Header Language Extension
   Extends lang.js translations with generic card-header keys
   (<h5 id="cHT..."> titles and <p id="cHS..."> subtitles) used across
   Academic, Admission, Attendance, Billing, Biometric, Branch, Cards,
   Certificate, Employee, Event and Exam pages and applies them to the
   DOM on language change.

   NOTE: These subtitles are intentionally NOT included because they
   contain dynamic Blade values (JS would overwrite them):
     - cHSBiometricEUM            ({{ $device->device_name }} ...)
     - cHSCertificateAddTemplate  ({{ $totalSteps }})
     - cHSCertificateEditTemplate ({{ $totalSteps }})
═══════════════════════════════════════════════════════════════════ */

import { translations } from "./lang.js";

/* ── Header translation keys (keyed directly by element id) ── */
const HeaderTranslations = {
    en: {
        /* ── Academic ── */

        /* Class Assign */
        cHTClassAssign: "All Class Assign",
        cHSClassAssign:
            "Manage class assignments, create, update, and organize easily.",

        /* Classes */
        cHTClass: "All Classes",
        cHSClass: "Manage classes, create, update, and organize easily.",

        /* Class Schedule */
        cHTClassScheduleCreate: "Add Schedule",
        cHSClassScheduleCreate: "Create new class schedule",
        cHTClassScheduleList: "Class Schedules",
        cHSClassScheduleList: "View class schedule by class and section",

        /* Groups */
        cHTGroup: "All Groups",
        cHSGroup: "Manage groups, create, update, and organize easily.",

        /* Sections */
        cHTSection: "All Sections",
        cHSSection: "Manage sections, create, update, and organize easily.",

        /* Academic Session */
        cHTSession: "Academic Session",
        cHSSession:
            "Manage academic sessions, create, update, and organize sessions easily.",

        /* Student Enrollment */
        cHTStudentEnrollment: "Student Enrollments",
        cHSStudentEnrollment:
            "Manage enrollments, filter by class and section.",

        /* Student Promotion */
        cHTStudentPromotion: "Student Promotion",
        cHSStudentPromotion: "Promote students to the next session/class",

        /* Subjects */
        cHTSubject: "All Subjects",
        cHSSubject: "Manage subjects, create, update, and organize easily.",

        /* Substitute Teacher */
        cHTSubstituteTeacher: "Substitute Teacher Assignment",
        cHSSubstituteTeacher:
            "Find absent teacher periods and assign substitutes",

        /* Teacher Schedule */
        cHTTeacherSchedule: "Teacher Schedule",
        cHSTeacherSchedule:
            "View weekly class schedule assigned to a specific teacher.",

        /* ── Online Admission ── */
        cHTOnlineAdmission: "Online Admissions",
        cHSOnlineAdmission:
            "Review, approve or reject student admission applications.",

        /* ── Attendance ── */
        cHTAttendanceStudent: "Student Attendance",
        cHSAttendanceStudent: "Mark or update student attendance",
        cHTAttendanceEmployee: "Employee Attendance",
        cHSAttendanceEmployee: "Mark or update employee attendance",
        cHTAttendanceExam: "Exam Attendance",
        cHSAttendanceExam: "Create or update exam attendance",
        cHTAttendanceExamDuty: "Exam Duty Assign",
        cHSAttendanceExamDuty:
            "Assign invigilator teachers to each exam schedule.",
        cHTAttendanceTeacherDuty: "Attendance Duty Assign",
        cHSAttendanceTeacherDuty:
            "Assign a teacher to take daily student attendance for each class/section.",

        /* ── Billing ── */
        cHTBillingShow: "Billing & Invoices",
        cHSBillingShow: "View your monthly usage bills and payment status.",

        /* ── Biometric ── */
        cHTBiometricDevice: "Biometric Devices",
        cHSBiometricDevice:
            "Register and manage attendance / access control devices.",
        cHTBiometricCUM: "Add Mapping",
        cHSBiometricCUM:
            "Manage students, filter by device, type, class and section.",
        cHTBiometricEUM: "Edit Mapping",
        cHTBiometricIUM: "Device User Mapping",
        cHSBiometricIUM:
            "Link biometric device user IDs with students and employees.",

        /* ── Branch ── */
        cHTBranchIndex: "Branches",
        cHSBranchIndex: "Manage your institution's branches / campuses.",
        cHTBranchEdit: "Branch Registration",
        cHSBranchEdit: "Update existing branch record",

        /* ── Admit Card / ID Card ── */
        cHTAdmitCardTemplate: "Admit Card Templates",
        cHSAdmitCardTemplate:
            "A lightweight, extendable, dependency-free javascript HTML table plugin.",
        cHTEmployeeIdCard: "Employee Id Card Generate",
        cHSEmployeeIdCard:
            "A lightweight, extendable, dependency-free javascript HTML table plugin.",
        cHTGenerateAdmitCard: "Admit Card Generate",
        cHSGenerateAdmitCard:
            "Generate exam admit cards for selected class, section and exam.",
        cHTIdCardTemplate: "ID Card Templates",
        cHSIdCardTemplate:
            "A lightweight, extendable, dependency-free javascript HTML table plugin.",
        cHTStudentIdCard: "Student Id Card Generate",
        cHSStudentIdCard:
            "A lightweight, extendable, dependency-free javascript HTML table plugin.",

        /* ── Certificate ── */
        cHTCertificateAddTemplate: "New Certificate Template",
        cHTCertificateEditTemplate: "Edit Certificate Template",
        cHTCertificateListTemplate: "Certificate Templates",
        cHSCertificateListTemplate:
            "Manage certificate templates, create, update, and organize them easily.",
        cHTCertificateGenerateEmployee: "Employee Certificate Generate",
        cHSCertificateGenerateEmployee:
            "Select role and template, then generate certificates for employees.",
        cHTCertificateGenerateStudent: "Student Certificate Generate",
        cHSCertificateGenerateStudent:
            "Select class, section and template, then generate certificates for students.",

        /* ── Employee ── */
        cHTEmployeeDepartment: "All Departments",
        cHSEmployeeDepartment:
            "Manage departments, create, update, and organize easily.",
        cHTEmployeeDesignation: "All Designations",
        cHSEmployeeDesignation:
            "Manage designations, create, update, and organize easily.",
        cHTEmployeeAdd: "Employee Registration",
        cHSEmployeeAdd: "Create new employee record",
        cHTEmployeeEdit: "Employee Registration",
        cHSEmployeeEdit: "Update existing employee record",
        cHTEmployeeList: "All Employees",
        cHSEmployeeList: "Manage employees, view details, and organize easily.",

        /* ── Event ── */
        cHTEventAdd: "Add Event",
        cHSEventAdd: "Create new event record",
        cHTEventEdit: "Edit Event",
        cHSEventEdit: "Update existing event record",
        cHTEventList: "Events",
        cHSEventList:
            "Manage events, create, update, and organize academic events easily.",
        cHTEventType: "Event Type",
        cHSEventType:
            "Manage event types, create, update, and organize academic types easily.",

        /* ── Exam ── */
        cHTExamEntry: "Mark Entries",
        cHSExamEntry: "Enter or update student marks for an exam subject",
        cHTExamSetup: "Exam Setup",
        cHSExamSetup:
            "Manage exam setups, create, update, and organize academic setups easily.",
        cHTExamGrade: "Grades Range",
        cHSExamGrade:
            "Manage exam grades, create, update, and organize academic grades easily.",
        cHTExamHall: "Exam Hall",
        cHSExamHall:
            "Manage exam halls, create, update, and organize academic halls easily.",
        cHTExamMark: "Exam Mark Distribution",
        cHSExamMark:
            "Manage exam marks, create, update, and organize academic marks easily.",
        cHTExamPosition: "Class Position",
        cHSExamPosition: "Generate and review student exam rank / position",
        cHTExamScheduleAdd: "Exam Schedule",
        cHSExamScheduleAdd:
            "Create or update the date, time and room for each subject.",
        cHTExamScheduleList: "Exam Schedule",
        cHSExamScheduleList:
            "Manage exam schedules, create, update, and organize academic schedules easily.",
        cHTExamTerm: "Exam Term",
        cHSExamTerm:
            "Manage exam terms, create, update, and organize academic terms easily.",
        cHTExamType: "Exam Type",
        cHSExamType:
            "Manage exam types, create, update, and organize academic types easily.",
    },
    bn: {
        /* ── Academic ── */

        /* Class Assign */
        cHTClassAssign: "সকল শ্রেণি অ্যাসাইন",
        cHSClassAssign:
            "শ্রেণি অ্যাসাইনমেন্ট পরিচালনা করুন, সহজে তৈরি, আপডেট ও সাজান।",

        /* Classes */
        cHTClass: "সকল শ্রেণি",
        cHSClass: "শ্রেণি পরিচালনা করুন, সহজে তৈরি, আপডেট ও সাজান।",

        /* Class Schedule */
        cHTClassScheduleCreate: "রুটিন যোগ করুন",
        cHSClassScheduleCreate: "নতুন ক্লাস রুটিন তৈরি করুন",
        cHTClassScheduleList: "ক্লাস রুটিন",
        cHSClassScheduleList: "শ্রেণি ও শাখা অনুযায়ী ক্লাস রুটিন দেখুন",

        /* Groups */
        cHTGroup: "সকল গ্রুপ",
        cHSGroup: "গ্রুপ পরিচালনা করুন, সহজে তৈরি, আপডেট ও সাজান।",

        /* Sections */
        cHTSection: "সকল শাখা",
        cHSSection: "শাখা পরিচালনা করুন, সহজে তৈরি, আপডেট ও সাজান।",

        /* Academic Session */
        cHTSession: "শিক্ষাবর্ষ",
        cHSSession: "শিক্ষাবর্ষ পরিচালনা করুন, সহজে তৈরি, আপডেট ও সাজান।",

        /* Student Enrollment */
        cHTStudentEnrollment: "শিক্ষার্থী ভর্তি",
        cHSStudentEnrollment:
            "ভর্তি পরিচালনা করুন, শ্রেণি ও শাখা অনুযায়ী ফিল্টার করুন।",

        /* Student Promotion */
        cHTStudentPromotion: "শিক্ষার্থী পদোন্নতি",
        cHSStudentPromotion:
            "শিক্ষার্থীদের পরবর্তী সেশন/শ্রেণিতে উত্তীর্ণ করুন",

        /* Subjects */
        cHTSubject: "সকল বিষয়",
        cHSSubject: "বিষয় পরিচালনা করুন, সহজে তৈরি, আপডেট ও সাজান।",

        /* Substitute Teacher */
        cHTSubstituteTeacher: "বদলি শিক্ষক নিয়োগ",
        cHSSubstituteTeacher:
            "অনুপস্থিত শিক্ষকের পিরিয়ড খুঁজে বদলি শিক্ষক নিয়োগ দিন",

        /* Teacher Schedule */
        cHTTeacherSchedule: "শিক্ষকের রুটিন",
        cHSTeacherSchedule: "নির্দিষ্ট শিক্ষকের সাপ্তাহিক ক্লাস রুটিন দেখুন।",

        /* ── Online Admission ── */
        cHTOnlineAdmission: "অনলাইন ভর্তি",
        cHSOnlineAdmission:
            "ভর্তি আবেদন পর্যালোচনা করুন, অনুমোদন বা প্রত্যাখ্যান করুন।",

        /* ── Attendance ── */
        cHTAttendanceStudent: "শিক্ষার্থী উপস্থিতি",
        cHSAttendanceStudent: "শিক্ষার্থীর উপস্থিতি নিন বা আপডেট করুন",
        cHTAttendanceEmployee: "কর্মচারী উপস্থিতি",
        cHSAttendanceEmployee: "কর্মচারীর উপস্থিতি নিন বা আপডেট করুন",
        cHTAttendanceExam: "পরীক্ষার উপস্থিতি",
        cHSAttendanceExam: "পরীক্ষার উপস্থিতি তৈরি বা আপডেট করুন",
        cHTAttendanceExamDuty: "পরীক্ষার দায়িত্ব বণ্টন",
        cHSAttendanceExamDuty:
            "প্রতিটি পরীক্ষার রুটিনে পরিদর্শক শিক্ষক নিয়োগ দিন।",
        cHTAttendanceTeacherDuty: "উপস্থিতি দায়িত্ব বণ্টন",
        cHSAttendanceTeacherDuty:
            "প্রতিটি শ্রেণি/শাখার দৈনিক শিক্ষার্থী উপস্থিতি নেওয়ার জন্য শিক্ষক নিয়োগ দিন।",

        /* ── Billing ── */
        cHTBillingShow: "বিলিং ও ইনভয়েস",
        cHSBillingShow: "আপনার মাসিক ব্যবহারের বিল ও পেমেন্টের অবস্থা দেখুন।",

        /* ── Biometric ── */
        cHTBiometricDevice: "বায়োমেট্রিক ডিভাইস",
        cHSBiometricDevice:
            "উপস্থিতি / অ্যাক্সেস কন্ট্রোল ডিভাইস নিবন্ধন ও পরিচালনা করুন।",
        cHTBiometricCUM: "ম্যাপিং যোগ করুন",
        cHSBiometricCUM:
            "শিক্ষার্থী পরিচালনা করুন, ডিভাইস, ধরন, শ্রেণি ও শাখা অনুযায়ী ফিল্টার করুন।",
        cHTBiometricEUM: "ম্যাপিং সম্পাদনা",
        cHTBiometricIUM: "ডিভাইস ইউজার ম্যাপিং",
        cHSBiometricIUM:
            "বায়োমেট্রিক ডিভাইসের ইউজার আইডি শিক্ষার্থী ও কর্মচারীর সাথে সংযুক্ত করুন।",

        /* ── Branch ── */
        cHTBranchIndex: "ব্রাঞ্চসমূহ",
        cHSBranchIndex: "আপনার প্রতিষ্ঠানের ব্রাঞ্চ / ক্যাম্পাস পরিচালনা করুন।",
        cHTBranchEdit: "ব্রাঞ্চ নিবন্ধন",
        cHSBranchEdit: "বিদ্যমান ব্রাঞ্চের তথ্য আপডেট করুন",

        /* ── Admit Card / ID Card ── */
        cHTAdmitCardTemplate: "অ্যাডমিট কার্ড টেমপ্লেট",
        cHSAdmitCardTemplate:
            "একটি হালকা, সম্প্রসারণযোগ্য, ডিপেন্ডেন্সি-মুক্ত জাভাস্ক্রিপ্ট HTML টেবিল প্লাগইন।",
        cHTEmployeeIdCard: "কর্মচারী আইডি কার্ড তৈরি",
        cHSEmployeeIdCard:
            "একটি হালকা, সম্প্রসারণযোগ্য, ডিপেন্ডেন্সি-মুক্ত জাভাস্ক্রিপ্ট HTML টেবিল প্লাগইন।",
        cHTGenerateAdmitCard: "অ্যাডমিট কার্ড তৈরি",
        cHSGenerateAdmitCard:
            "নির্বাচিত শ্রেণি, শাখা ও পরীক্ষার জন্য পরীক্ষার অ্যাডমিট কার্ড তৈরি করুন।",
        cHTIdCardTemplate: "আইডি কার্ড টেমপ্লেট",
        cHSIdCardTemplate:
            "একটি হালকা, সম্প্রসারণযোগ্য, ডিপেন্ডেন্সি-মুক্ত জাভাস্ক্রিপ্ট HTML টেবিল প্লাগইন।",
        cHTStudentIdCard: "শিক্ষার্থী আইডি কার্ড তৈরি",
        cHSStudentIdCard:
            "একটি হালকা, সম্প্রসারণযোগ্য, ডিপেন্ডেন্সি-মুক্ত জাভাস্ক্রিপ্ট HTML টেবিল প্লাগইন।",

        /* ── Certificate ── */
        cHTCertificateAddTemplate: "নতুন সার্টিফিকেট টেমপ্লেট",
        cHTCertificateEditTemplate: "সার্টিফিকেট টেমপ্লেট সম্পাদনা",
        cHTCertificateListTemplate: "সার্টিফিকেট টেমপ্লেট",
        cHSCertificateListTemplate:
            "সার্টিফিকেট টেমপ্লেট পরিচালনা করুন, সহজে তৈরি, আপডেট ও সাজান।",
        cHTCertificateGenerateEmployee: "কর্মচারী সার্টিফিকেট তৈরি",
        cHSCertificateGenerateEmployee:
            "ভূমিকা ও টেমপ্লেট নির্বাচন করুন, তারপর কর্মচারীদের জন্য সার্টিফিকেট তৈরি করুন।",
        cHTCertificateGenerateStudent: "শিক্ষার্থী সার্টিফিকেট তৈরি",
        cHSCertificateGenerateStudent:
            "শ্রেণি, শাখা ও টেমপ্লেট নির্বাচন করুন, তারপর শিক্ষার্থীদের জন্য সার্টিফিকেট তৈরি করুন।",

        /* ── Employee ── */
        cHTEmployeeDepartment: "সকল বিভাগ",
        cHSEmployeeDepartment: "বিভাগ পরিচালনা করুন, সহজে তৈরি, আপডেট ও সাজান।",
        cHTEmployeeDesignation: "সকল পদবি",
        cHSEmployeeDesignation: "পদবি পরিচালনা করুন, সহজে তৈরি, আপডেট ও সাজান।",
        cHTEmployeeAdd: "কর্মচারী নিবন্ধন",
        cHSEmployeeAdd: "নতুন কর্মচারীর তথ্য তৈরি করুন",
        cHTEmployeeEdit: "কর্মচারী নিবন্ধন",
        cHSEmployeeEdit: "বিদ্যমান কর্মচারীর তথ্য আপডেট করুন",
        cHTEmployeeList: "সকল কর্মচারী",
        cHSEmployeeList:
            "কর্মচারী পরিচালনা করুন, বিস্তারিত দেখুন ও সহজে সাজান।",

        /* ── Event ── */
        cHTEventAdd: "ইভেন্ট যোগ করুন",
        cHSEventAdd: "নতুন ইভেন্টের তথ্য তৈরি করুন",
        cHTEventEdit: "ইভেন্ট সম্পাদনা",
        cHSEventEdit: "বিদ্যমান ইভেন্টের তথ্য আপডেট করুন",
        cHTEventList: "ইভেন্টসমূহ",
        cHSEventList:
            "ইভেন্ট পরিচালনা করুন, একাডেমিক ইভেন্ট সহজে তৈরি, আপডেট ও সাজান।",
        cHTEventType: "ইভেন্টের ধরন",
        cHSEventType:
            "ইভেন্টের ধরন পরিচালনা করুন, একাডেমিক ধরন সহজে তৈরি, আপডেট ও সাজান।",

        /* ── Exam ── */
        cHTExamEntry: "নম্বর এন্ট্রি",
        cHSExamEntry:
            "পরীক্ষার বিষয়ের জন্য শিক্ষার্থীর নম্বর ইনপুট বা আপডেট করুন",
        cHTExamSetup: "পরীক্ষা সেটআপ",
        cHSExamSetup:
            "পরীক্ষা সেটআপ পরিচালনা করুন, একাডেমিক সেটআপ সহজে তৈরি, আপডেট ও সাজান।",
        cHTExamGrade: "গ্রেড রেঞ্জ",
        cHSExamGrade:
            "পরীক্ষার গ্রেড পরিচালনা করুন, একাডেমিক গ্রেড সহজে তৈরি, আপডেট ও সাজান।",
        cHTExamHall: "পরীক্ষা হল",
        cHSExamHall:
            "পরীক্ষার হল পরিচালনা করুন, একাডেমিক হল সহজে তৈরি, আপডেট ও সাজান।",
        cHTExamMark: "পরীক্ষার নম্বর বণ্টন",
        cHSExamMark:
            "পরীক্ষার নম্বর পরিচালনা করুন, একাডেমিক নম্বর সহজে তৈরি, আপডেট ও সাজান।",
        cHTExamPosition: "শ্রেণিতে অবস্থান",
        cHSExamPosition:
            "শিক্ষার্থীর পরীক্ষার র‍্যাংক / অবস্থান তৈরি ও পর্যালোচনা করুন",
        cHTExamScheduleAdd: "পরীক্ষার রুটিন",
        cHSExamScheduleAdd:
            "প্রতিটি বিষয়ের তারিখ, সময় ও কক্ষ নির্ধারণ বা আপডেট করুন।",
        cHTExamScheduleList: "পরীক্ষার রুটিন",
        cHSExamScheduleList:
            "পরীক্ষার রুটিন পরিচালনা করুন, একাডেমিক রুটিন সহজে তৈরি, আপডেট ও সাজান।",
        cHTExamTerm: "পরীক্ষার টার্ম",
        cHSExamTerm:
            "পরীক্ষার টার্ম পরিচালনা করুন, একাডেমিক টার্ম সহজে তৈরি, আপডেট ও সাজান।",
        cHTExamType: "পরীক্ষার ধরন",
        cHSExamType:
            "পরীক্ষার ধরন পরিচালনা করুন, একাডেমিক ধরন সহজে তৈরি, আপডেট ও সাজান।",
    },
};

/* ── Merge into shared translations object ── */
Object.assign(translations.en, HeaderTranslations.en);
Object.assign(translations.bn, HeaderTranslations.bn);

/* ── Apply header DOM updates ── */
function applyHeaderLang(lang) {
    const t = HeaderTranslations[lang] || HeaderTranslations.en;
    const setText = (id, val) => {
        const el = document.getElementById(id);
        if (el) el.textContent = val;
    };

    Object.keys(t).forEach((id) => {
        setText(id, t[id]);
    });
}

// Apply on load
applyHeaderLang(localStorage.getItem("lang") || "en");

// Re-apply whenever lang.js switches language
document.addEventListener("langChanged", (e) => {
    applyHeaderLang(e.detail.lang);
});

export { HeaderTranslations, applyHeaderLang };
