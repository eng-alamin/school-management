<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

/**
 * Seeds the FIXED, predefined permission list used by Institution-level
 * roles (guard_name = 'web').
 *
 * Module keys below are derived directly from the `// comment` groups in
 * routes/web.php (Admin group). Actions are limited to what each module's
 * routes actually support:
 *   - 'view'   => index/list/show routes exist
 *   - 'create' => add/create routes exist
 *   - 'edit'   => edit/update routes exist
 *   - 'delete' => a delete action exists for that module (toggle/remove)
 *
 * IMPORTANT:
 * - Permissions are GLOBAL (not team/institution scoped) — only `roles`
 *   and the pivot tables are team-scoped by Spatie's teams feature.
 * - This seeder is idempotent (firstOrCreate) — safe to re-run whenever
 *   new modules/routes are added.
 * - Naming convention: '{module}.{action}'.
 */
class InstitutionRolePermissionSeeder extends Seeder
{
    /**
     * module_key => [actions...]
     * Order mirrors the route file's group order for easy cross-checking.
     */
    private const PERMISSIONS = [
        'dashboard'              => ['view'],

        'branch'                 => ['view', 'create', 'edit', 'delete'],

        // Inventory
        'inventory_unit'         => ['view', 'create', 'edit', 'delete'],
        'inventory_category'     => ['view', 'create', 'edit', 'delete'],
        'inventory_store'        => ['view', 'create', 'edit', 'delete'],
        'inventory_supplier'     => ['view', 'create', 'edit', 'delete'],
        'inventory_product'      => ['view', 'create', 'edit', 'delete'],
        'inventory_purchase'     => ['view', 'create', 'edit', 'delete'],
        'inventory_sale'         => ['view', 'create', 'edit', 'delete'],

        // Admission
        'admission'               => ['view', 'create'],

        // Student
        'student'                 => ['view', 'create', 'edit'],
        'student_invoice'         => ['view', 'create'],
        'student_account'         => ['view'],
        'student_attendance'      => ['view', 'create'],
        'student_enrollment'      => ['view'],

        // Academic
        'academic_session'        => ['view', 'create', 'edit'],
        'academic_group'          => ['view', 'create', 'edit'],
        'academic_class'          => ['view', 'create', 'edit'],
        'academic_section'        => ['view', 'create', 'edit'],
        'academic_subject'        => ['view', 'create', 'edit'],
        'academic_class_assign'   => ['view', 'create', 'edit'],
        'academic_class_schedule' => ['view', 'create'],
        'academic_teacher_schedule'    => ['view'],
        'academic_substitute_teacher'  => ['view', 'create'],
        'academic_student_promotion'   => ['view', 'create'],
        'academic_student_enrollment'  => ['view'],

        // Employee
        'employee_department'    => ['view', 'create', 'edit', 'delete'],
        'employee_designation'   => ['view', 'create', 'edit', 'delete'],
        'employee'                => ['view', 'create', 'edit'],
        'employee_account'        => ['view'],
        'employee_invoice'        => ['view'],

        // Parent
        'parent'                   => ['view', 'create', 'edit'],

        // Homework
        'homework'                 => ['view', 'create', 'edit'],

        // Card
        'card_id_template'         => ['view', 'create', 'edit'],
        'card_student_id'          => ['view', 'create'],
        'card_employee_id'         => ['view', 'create'],
        'card_admit_template'      => ['view', 'create', 'edit'],
        'card_generate_admit'      => ['view', 'create'],

        // Certificate
        'certificate_template'     => ['view', 'create', 'edit'],
        'certificate_generate'     => ['view', 'create'],

        // Salary
        'salary_template'          => ['view', 'create', 'edit'],
        'salary_assign'            => ['view', 'create'],
        'salary_payment'           => ['view', 'create'],
        'salary_advance'           => ['view', 'create'],

        // Leave
        'leave_category'           => ['view', 'create', 'edit', 'delete'],
        'leave_application'        => ['view', 'create', 'edit'],

        // Exam
        'exam_term'                => ['view', 'create', 'edit', 'delete'],
        'exam_hall'                => ['view', 'create', 'edit', 'delete'],
        'exam_mark'                => ['view', 'create', 'edit'],
        'exam_type'                => ['view', 'create', 'edit', 'delete'],
        'exam_setup'               => ['view', 'create', 'edit', 'delete'],
        'exam_schedule'            => ['view', 'create'],
        'exam_entry'               => ['view', 'create', 'edit'],
        'exam_position'            => ['view'],
        'exam_grade'               => ['view', 'create', 'edit', 'delete'],

        // Attendance
        'attendance_student'       => ['view', 'create'],
        'attendance_employee'      => ['view', 'create'],
        'attendance_exam'          => ['view', 'create'],
        'attendance_duty_assign'   => ['view', 'create'],

        // Biometric
        'biometric_device'         => ['view', 'create', 'edit', 'delete'],
        'biometric_mapping'        => ['view', 'create', 'edit', 'delete'],

        // Event
        'event_type'               => ['view', 'create', 'edit', 'delete'],
        'event'                    => ['view', 'create', 'edit'],

        // Office Accounting
        'office_accounting_account' => ['view', 'create', 'edit'],
        'office_accounting_head'    => ['view', 'create', 'edit'],
        'office_accounting_deposit' => ['view', 'create', 'edit'],
        'office_accounting_expense' => ['view', 'create', 'edit'],
        'office_accounting_transaction' => ['view'],

        // Student Accounting
        'student_accounting_fee_type'   => ['view', 'create', 'edit', 'delete'],
        'student_accounting_fee_setup'  => ['view', 'create', 'edit', 'delete'],
        'student_accounting_fee_fine'   => ['view', 'create', 'edit', 'delete'],
        'student_accounting_student_fine' => ['view', 'create', 'edit', 'delete'],
        'student_accounting_fee_invoice'  => ['view', 'create'],

        // Mailbox
        'mailbox'                   => ['view', 'create', 'delete'],

        // Log
        'log_activity'              => ['view'],
        'log_session'               => ['view'],
        'log_login'                 => ['view'],

        // Notice / Notification
        'notice'                    => ['view', 'create', 'edit', 'delete'],
        'notification'              => ['view'],

        // Role & Permission
        'role_permission'           => ['view', 'create', 'edit', 'delete'],

        // Report
        'report_attendance'         => ['view'],

        // Information
        'information_committee'     => ['view', 'create', 'edit', 'delete'],
        'information_facility'      => ['view', 'create', 'edit', 'delete'],

        // Setting
        'setting_institution'       => ['view', 'edit'],
        'setting_feature'           => ['view', 'edit'],
    ];

    /**
     * Human-readable labels for the UI matrix (Module column).
     * Falls back to Str::headline($key) in the UI if a key is missing here.
     */
    public const MODULE_LABELS = [
        'dashboard'               => 'Dashboard',
        'branch'                  => 'Branch',
        'inventory_unit'          => 'Inventory Unit',
        'inventory_category'      => 'Inventory Category',
        'inventory_store'         => 'Inventory Store',
        'inventory_supplier'      => 'Inventory Supplier',
        'inventory_product'       => 'Inventory Product',
        'inventory_purchase'      => 'Inventory Purchase',
        'inventory_sale'          => 'Inventory Sale',
        'admission'                => 'Online Admission',
        'student'                  => 'Student',
        'student_invoice'          => 'Student Invoice / Payment',
        'student_account'          => 'Student Account',
        'student_attendance'       => 'Student Attendance',
        'student_enrollment'       => 'Student Enrollment',
        'academic_session'         => 'Academic Session',
        'academic_group'           => 'Academic Group',
        'academic_class'           => 'Academic Class',
        'academic_section'         => 'Academic Section',
        'academic_subject'         => 'Academic Subject',
        'academic_class_assign'    => 'Class Assign',
        'academic_class_schedule'  => 'Class Schedule',
        'academic_teacher_schedule'    => 'Teacher Schedule',
        'academic_substitute_teacher'  => 'Substitute Teacher',
        'academic_student_promotion'   => 'Student Promotion',
        'academic_student_enrollment'  => 'Student Enrollment (Academic)',
        'employee_department'      => 'Employee Department',
        'employee_designation'     => 'Employee Designation',
        'employee'                  => 'Employee',
        'employee_account'          => 'Employee Account',
        'employee_invoice'          => 'Employee Invoice',
        'parent'                     => 'Parent',
        'homework'                   => 'Homework',
        'card_id_template'           => 'ID Card Template',
        'card_student_id'            => 'Student ID Card',
        'card_employee_id'           => 'Employee ID Card',
        'card_admit_template'        => 'Admit Card Template',
        'card_generate_admit'        => 'Generate Admit Card',
        'certificate_template'       => 'Certificate Template',
        'certificate_generate'       => 'Generate Certificate',
        'salary_template'            => 'Salary Template',
        'salary_assign'              => 'Salary Assign',
        'salary_payment'             => 'Salary Payment',
        'salary_advance'             => 'Salary Advance',
        'leave_category'             => 'Leave Category',
        'leave_application'          => 'Leave Application',
        'exam_term'                  => 'Exam Term',
        'exam_hall'                  => 'Exam Hall',
        'exam_mark'                  => 'Exam Mark',
        'exam_type'                  => 'Exam Type',
        'exam_setup'                 => 'Exam Setup',
        'exam_schedule'              => 'Exam Schedule',
        'exam_entry'                 => 'Exam Entry',
        'exam_position'              => 'Exam Position',
        'exam_grade'                 => 'Exam Grade',
        'attendance_student'         => 'Student Attendance (Daily)',
        'attendance_employee'        => 'Employee Attendance',
        'attendance_exam'            => 'Exam Attendance',
        'attendance_duty_assign'     => 'Duty Assign',
        'biometric_device'           => 'Biometric Device',
        'biometric_mapping'          => 'Biometric Device Mapping',
        'event_type'                 => 'Event Type',
        'event'                      => 'Event',
        'office_accounting_account'  => 'Office Account',
        'office_accounting_head'     => 'Voucher Head',
        'office_accounting_deposit'  => 'Office Deposit',
        'office_accounting_expense'  => 'Office Expense',
        'office_accounting_transaction' => 'Office Transaction',
        'student_accounting_fee_type'  => 'Fee Type',
        'student_accounting_fee_setup' => 'Fee Setup',
        'student_accounting_fee_fine'  => 'Fee Fine',
        'student_accounting_student_fine' => 'Student Fine',
        'student_accounting_fee_invoice'  => 'Fee Invoice',
        'mailbox'                     => 'Mailbox',
        'log_activity'                => 'Activity Log',
        'log_session'                 => 'Session Log',
        'log_login'                   => 'Login Log',
        'notice'                       => 'Notice',
        'notification'                 => 'Notification',
        'role_permission'              => 'Role & Permission',
        'report_attendance'            => 'Attendance Report',
        'information_committee'        => 'Institution Committee',
        'information_facility'         => 'Institution Facility',
        'setting_institution'          => 'Institution Setting',
        'setting_feature'              => 'Feature Setting',
    ];

    /**
     * Groups module_keys into parent "Module" rows for the accordion-style
     * permission matrix UI (RolePermission Create/Edit pages). Clicking a
     * parent with >1 child expands to reveal its sub-modules. Parents with
     * exactly 1 child render as a flat row (no expand arrow) since there is
     * nothing to expand into.
     *
     * This is a pure UI/query-layer grouping — it does NOT change any
     * permission name in the database, so existing role→permission
     * assignments are completely unaffected.
     *
     * group_key => ['label' => ..., 'children' => [module_key, ...]]
     */
    public const PARENT_GROUPS = [
        'dashboard' => [
            'label' => 'Dashboard',
            'children' => ['dashboard'],
        ],
        'branch' => [
            'label' => 'Branch',
            'children' => ['branch'],
        ],
        'inventory' => [
            'label' => 'Inventory',
            'children' => [
                'inventory_unit', 'inventory_category', 'inventory_store',
                'inventory_supplier', 'inventory_product', 'inventory_purchase', 'inventory_sale',
            ],
        ],
        'admission' => [
            'label' => 'Online Admission',
            'children' => ['admission'],
        ],
        'student' => [
            'label' => 'Student',
            'children' => [
                'student', 'student_invoice', 'student_account',
                'student_attendance', 'student_enrollment',
            ],
        ],
        'academic' => [
            'label' => 'Academic',
            'children' => [
                'academic_session', 'academic_group', 'academic_class', 'academic_section',
                'academic_subject', 'academic_class_assign', 'academic_class_schedule',
                'academic_teacher_schedule', 'academic_substitute_teacher',
                'academic_student_promotion', 'academic_student_enrollment',
            ],
        ],
        'employee' => [
            'label' => 'Employee',
            'children' => [
                'employee_department', 'employee_designation', 'employee',
                'employee_account', 'employee_invoice',
            ],
        ],
        'parent' => [
            'label' => 'Parent / Guardian',
            'children' => ['parent'],
        ],
        'homework' => [
            'label' => 'Homework',
            'children' => ['homework'],
        ],
        'card' => [
            'label' => 'Card',
            'children' => [
                'card_id_template', 'card_student_id', 'card_employee_id',
                'card_admit_template', 'card_generate_admit',
            ],
        ],
        'certificate' => [
            'label' => 'Certificate',
            'children' => ['certificate_template', 'certificate_generate'],
        ],
        'salary' => [
            'label' => 'Salary',
            'children' => ['salary_template', 'salary_assign', 'salary_payment', 'salary_advance'],
        ],
        'leave' => [
            'label' => 'Leave',
            'children' => ['leave_category', 'leave_application'],
        ],
        'exam' => [
            'label' => 'Exam',
            'children' => [
                'exam_term', 'exam_hall', 'exam_mark', 'exam_type', 'exam_setup',
                'exam_schedule', 'exam_entry', 'exam_position', 'exam_grade',
            ],
        ],
        'attendance' => [
            'label' => 'Attendance',
            'children' => [
                'attendance_student', 'attendance_employee',
                'attendance_exam', 'attendance_duty_assign',
            ],
        ],
        'biometric' => [
            'label' => 'Biometric',
            'children' => ['biometric_device', 'biometric_mapping'],
        ],
        'event' => [
            'label' => 'Event',
            'children' => ['event_type', 'event'],
        ],
        'office_accounting' => [
            'label' => 'Office Accounting',
            'children' => [
                'office_accounting_account', 'office_accounting_head', 'office_accounting_deposit',
                'office_accounting_expense', 'office_accounting_transaction',
            ],
        ],
        'student_accounting' => [
            'label' => 'Student Accounting',
            'children' => [
                'student_accounting_fee_type', 'student_accounting_fee_setup', 'student_accounting_fee_fine',
                'student_accounting_student_fine', 'student_accounting_fee_invoice',
            ],
        ],
        'mailbox' => [
            'label' => 'Mailbox',
            'children' => ['mailbox'],
        ],
        'log' => [
            'label' => 'Logs',
            'children' => ['log_activity', 'log_session', 'log_login'],
        ],
        'notice' => [
            'label' => 'Notice & Notification',
            'children' => ['notice', 'notification'],
        ],
        'role_permission' => [
            'label' => 'Role & Permission',
            'children' => ['role_permission'],
        ],
        'report' => [
            'label' => 'Report',
            'children' => ['report_attendance'],
        ],
        'information' => [
            'label' => 'Information',
            'children' => ['information_committee', 'information_facility'],
        ],
        'setting' => [
            'label' => 'Setting',
            'children' => ['setting_institution', 'setting_feature'],
        ],
    ];

    public function run(): void
    {
        // Permissions are global (not team-scoped) — clear any stale team
        // context before seeding to avoid Spatie applying an unwanted scope.
        app(PermissionRegistrar::class)->setPermissionsTeamId(null);

        foreach (self::PERMISSIONS as $module => $actions) {
            foreach ($actions as $action) {
                Permission::firstOrCreate([
                    'name'       => "{$module}.{$action}",
                    'guard_name' => 'web',
                ]);
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}