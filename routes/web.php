<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('register', \App\Livewire\RegisterComponent::class)->name('register');
Route::get('login', \App\Livewire\LoginComponent::class)->name('login');
Route::post('logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->route('login');
})->name('logout');


 Route::get('/', \App\Livewire\Frontend\HomeComponent::class)->name('home');


 Route::get('dashboard', function () {
    $role = auth()->user()->role;

    return match ($role) {
        'admin' => redirect()->route('admin.dashboard'),
        'teacher' => redirect()->route('teacher.dashboard'),
        'student' => redirect()->route('student.dashboard'),
        'parent' => redirect()->route('parent.dashboard'),
        default => abort(403),
    };
})->middleware('auth')->name('dashboard');


// Admin
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('admin/dashboard', \App\Livewire\Admin\DashboardComponent::class)->name('admin.dashboard');

    // Inventory
    Route::get('inventory/units', \App\Livewire\Admin\Inventory\UnitComponent::class)->name('admin.inventory.units');
    Route::get('inventory/categories', \App\Livewire\Admin\Inventory\CategoryComponent::class)->name('admin.inventory.categories');
    Route::get('inventory/stores', \App\Livewire\Admin\Inventory\StoreComponent::class)->name('admin.inventory.stores');
    Route::get('inventory/suppliers', \App\Livewire\Admin\Inventory\SupplierComponent::class)->name('admin.inventory.suppliers');
    Route::get('inventory/products', \App\Livewire\Admin\Inventory\ProductComponent::class)->name('admin.inventory.products');
    Route::get('inventory/purchase/list', \App\Livewire\Admin\Inventory\PurchaseListComponent::class)->name('admin.inventory.purchase.list');
    Route::get('inventory/purchase/add', \App\Livewire\Admin\Inventory\PurchaseAddComponent::class)->name('admin.inventory.purchase.add');
    Route::get('inventory/purchase/{id}/edit', \App\Livewire\Admin\Inventory\PurchaseEditComponent::class)->name('admin.inventory.purchase.edit');
    Route::get('inventory/sale/list', \App\Livewire\Admin\Inventory\SaleListComponent::class)->name('admin.inventory.sale.list');
    Route::get('inventory/sale/add', \App\Livewire\Admin\Inventory\SaleAddComponent::class)->name('admin.inventory.sale.add');
    Route::get('inventory/sale/{id}/edit', \App\Livewire\Admin\Inventory\SaleEditComponent::class)->name('admin.inventory.sale.edit');
    
    // Student
    Route::get('/student/create', \App\Livewire\Admin\Student\StudentAddComponent::class)->name('admin.student.add');
    Route::get('/student/list', \App\Livewire\Admin\Student\StudentListComponent::class)->name('admin.student.list');
    Route::get('/student/{id}/edit', \App\Livewire\Admin\Student\StudentEditComponent::class)->name('admin.student.edit');
    Route::get('/student/{id}/overview', \App\Livewire\Admin\StudentOverviewComponent::class)->name('admin.student.overview');
    // Academic
    Route::get('/academic/categories', \App\Livewire\Admin\Academic\CategoryComponent::class)->name('admin.academic.categories');
    Route::get('/academic/classes', \App\Livewire\Admin\Academic\ClassComponent::class)->name('admin.academic.classes');
    Route::get('/academic/sections', \App\Livewire\Admin\Academic\SectionComponent::class)->name('admin.academic.sections');
    Route::get('/academic/subjects', \App\Livewire\Admin\Academic\SubjectComponent::class)->name('admin.academic.subjects');
    Route::get('/academic/class-assign', \App\Livewire\Admin\Academic\ClassAssignComponent::class)->name('admin.academic.class-assign');
    Route::get('/academic/teacher-assign', \App\Livewire\Admin\Academic\TeacherAssignComponent::class)->name('admin.academic.teacher-assign');
    Route::get('/academic/class-schedule/create', \App\Livewire\Admin\Academic\ClassScheduleCreateComponent::class)->name('admin.academic.class-schedule.create');
    Route::get('/academic/class-schedule/list', \App\Livewire\Admin\Academic\ClassScheduleListComponent::class)->name('admin.academic.class-schedule.list');
    Route::get('/academic/teacher-schedule', \App\Livewire\Admin\Academic\TeacherScheduleComponent::class)->name('admin.academic.teacher-schedule');
    Route::get('/academic/student-promotion', \App\Livewire\Admin\Academic\StudentPromotionComponent::class)->name('admin.academic.student-promotion');

    Route::get('/employee/departments', \App\Livewire\Admin\Employee\DepartmentComponent::class)->name('admin.employee.departments');
    Route::get('/employee/designations', \App\Livewire\Admin\Employee\DesignationComponent::class)->name('admin.employee.designations');
    Route::get('/employee/list', \App\Livewire\Admin\Employee\EmployeeListComponent::class)->name('admin.employee.list');
    Route::get('/employee/add', \App\Livewire\Admin\Employee\EmployeeAddComponent::class)->name('admin.employee.add');
    Route::get('/employee/edit/{id}', \App\Livewire\Admin\Employee\EmployeeEditComponent::class)->name('admin.employee.edit');

    Route::get('parent/list', \App\Livewire\Admin\Parent\ParentListComponent::class)->name('admin.parent.list');
    Route::get('parent/add', \App\Livewire\Admin\Parent\ParentAddComponent::class)->name('admin.parent.add');
    Route::get('parent/edit/{id}', \App\Livewire\Admin\Parent\ParentEditComponent::class)->name('admin.parent.edit');
    Route::get('parent/{id}/overview', \App\Livewire\Admin\Parent\ParentOverviewComponent::class)->name('admin.parent.overview');
    Route::get('parent/{id}/child', \App\Livewire\Admin\Parent\ParentChildComponent::class)->name('admin.parent.child');

    Route::get('/homework/add', \App\Livewire\Admin\Homework\HomeworkAddComponent::class)->name('admin.homework.add');
    Route::get('/homework/list', \App\Livewire\Admin\Homework\HomeworkListComponent::class)->name('admin.homework.list');
    Route::get('/homework/edit/{id}', \App\Livewire\Admin\Homework\HomeworkEditComponent::class)->name('admin.homework.edit');

    Route::get('/card/id-card-templates', \App\Livewire\Admin\Card\IdCardTemplateComponent::class)->name('admin.card.id-card-templates');
    Route::get('/card/student-id-cards', \App\Livewire\Admin\Card\StudentIdCardComponent::class)->name('admin.card.student-id-cards');
    Route::get('/card/employee-id-cards', \App\Livewire\Admin\Card\EmployeeIdCardComponent::class)->name('admin.card.employee-id-cards');    
    Route::get('/card/admit-card-templates', \App\Livewire\Admin\Card\AdmitCardTemplateComponent::class)->name('admin.card.admit-card-templates');
    Route::get('/card/generate-admit-cards', \App\Livewire\Admin\Card\GenerateAdmitCardComponent::class)->name('admin.card.generate-admit-cards');

    Route::get('certificate/add-template', \App\Livewire\Admin\Certificate\AddTemplateComponent::class)->name('admin.certificate.add-template');
    Route::get('certificate/{id}/edit-template', \App\Livewire\Admin\Certificate\EditTemplateComponent::class)->name('admin.certificate.edit-template');
    Route::get('certificate/list-template', \App\Livewire\Admin\Certificate\ListTemplateComponent::class)->name('admin.certificate.list-template');
    Route::get('certificate/generate-student', \App\Livewire\Admin\Certificate\GenerateStudentComponent::class)->name('admin.certificate.generate-student');
    Route::get('certificate/generate-employee', \App\Livewire\Admin\Certificate\GenerateEmployeeComponent::class)->name('admin.certificate.generate-employee');
    
    Route::get('salary/add-template', \App\Livewire\Admin\Salary\AddTemplateComponent::class)->name('admin.salary.add-template');
    Route::get('salary/{id}/edit-template', \App\Livewire\Admin\Salary\EditTemplateComponent::class)->name('admin.salary.edit-template');
    Route::get('salary/list-template', \App\Livewire\Admin\Salary\ListTemplateComponent::class)->name('admin.salary.list-template');
    Route::get('salary/assign', \App\Livewire\Admin\Salary\AssignComponent::class)->name('admin.salary.assign');
    Route::get('salary/{id}/{month}/add-payment', \App\Livewire\Admin\Salary\AddPaymentComponent::class)->name('admin.salary.add-payment');
    Route::get('salary/{id}/{month}/invoice-payment', \App\Livewire\Admin\Salary\InvoicePaymentComponent::class)->name('admin.salary.invoice-payment');
    Route::get('salary/payment', \App\Livewire\Admin\Salary\PaymentComponent::class)->name('admin.salary.payment');

    Route::get('leave/categories', \App\Livewire\Admin\Leave\CategoryComponent::class)->name('admin.leave.categories');
    Route::get('leave/applications', \App\Livewire\Admin\Leave\ApplicationComponent::class)->name('admin.leave.applications');
    
    Route::get('exam/terms', \App\Livewire\Admin\Exam\TermComponent::class)->name('admin.exam.terms');
    Route::get('exam/halls', \App\Livewire\Admin\Exam\HallComponent::class)->name('admin.exam.halls');
    Route::get('exam/marks', \App\Livewire\Admin\Exam\MarkComponent::class)->name('admin.exam.marks');    
    Route::get('exam/types', \App\Livewire\Admin\Exam\TypeComponent::class)->name('admin.exam.types');    
    Route::get('exam/setups', \App\Livewire\Admin\Exam\ExamSetupComponent::class)->name('admin.exam.setups');
    Route::get('exam/schedule/add', \App\Livewire\Admin\Exam\ScheduleAddComponent::class)->name('admin.exam.schedule.add');       
    Route::get('exam/schedule/list', \App\Livewire\Admin\Exam\ScheduleListComponent::class)->name('admin.exam.schedule.list');    
    
    Route::get('exam/grades', \App\Livewire\Admin\Exam\GradeComponent::class)->name('admin.exam.grades');

    Route::get('attendance/students', \App\Livewire\Admin\Attendance\StudentComponent::class)->name('admin.attendance.students');
    Route::get('attendance/employees', \App\Livewire\Admin\Attendance\EmployeeComponent::class)->name('admin.attendance.employees');
    Route::get('attendance/exams', \App\Livewire\Admin\Attendance\ExamComponent::class)->name('admin.attendance.exams');

    Route::get('event/types', \App\Livewire\Admin\Event\TypeComponent::class)->name('admin.event.types');
    Route::get('event/add', \App\Livewire\Admin\Event\AddComponent::class)->name('admin.event.add');
    Route::get('events/{id}/edit', \App\Livewire\Admin\Event\EditComponent::class)->name('admin.event.edit');
    Route::get('event/list', \App\Livewire\Admin\Event\ListComponent::class)->name('admin.event.list');
    
    Route::get('office-accounting/accounts', \App\Livewire\Admin\OfficeAccounting\AccountComponent::class)->name('admin.office-accounting.accounts');
    Route::get('office-accounting/voucher-head', \App\Livewire\Admin\OfficeAccounting\HeadComponent::class)->name('admin.office-accounting.heads');
    Route::get('office-accounting/voucher-deposit-add', \App\Livewire\Admin\OfficeAccounting\DepositAddComponent::class)->name('admin.office-accounting.deposit.add');
    Route::get('office-accounting/{id}/voucher-deposit-edit', \App\Livewire\Admin\OfficeAccounting\DepositEditComponent::class)->name('admin.office-accounting.deposit.edit');
    Route::get('office-accounting/voucher-deposit-list', \App\Livewire\Admin\OfficeAccounting\DepositListComponent::class)->name('admin.office-accounting.deposit.list');
    Route::get('office-accounting/voucher-expense-add', \App\Livewire\Admin\OfficeAccounting\ExpenseAddComponent::class)->name('admin.office-accounting.expense.add');
    Route::get('office-accounting/{id}/voucher-expense-edit', \App\Livewire\Admin\OfficeAccounting\ExpenseEditComponent::class)->name('admin.office-accounting.expense.edit');
    Route::get('office-accounting/voucher-expense-list', \App\Livewire\Admin\OfficeAccounting\ExpenseListComponent::class)->name('admin.office-accounting.expense.list');
    Route::get('office-accounting/transactions', \App\Livewire\Admin\OfficeAccounting\TransactionComponent::class)->name('admin.office-accounting.transactions');

    Route::get('student-accounting/fee-types', \App\Livewire\Admin\StudentAccounting\FeeTypeComponent::class)->name('admin.student-accounting.fee.types');
    Route::get('student-accounting/fee-groups', \App\Livewire\Admin\StudentAccounting\FeeGroupComponent::class)->name('admin.student-accounting.fee.groups');
    Route::get('student-accounting/fee-fines', \App\Livewire\Admin\StudentAccounting\FeeFineComponent::class)->name('admin.student-accounting.fee.fines');
    Route::get('student-accounting/fee-allocations', \App\Livewire\Admin\StudentAccounting\FeeAllocationComponent::class)->name('admin.student-accounting.fee.allocations');
    Route::get('student-accounting/fee-invoices', \App\Livewire\Admin\StudentAccounting\FeeInvoiceComponent::class)->name('admin.student-accounting.fee.invoices');

    Route::get('student/{id}/invoice', \App\Livewire\Admin\StudentInvoiceComponent::class)->name('admin.student.invoice');
    Route::get('student/{id}/payment/add', \App\Livewire\Admin\StudentAccounting\PaymentAddComponent::class)->name('admin.student.payment.add');

    Route::get('mailbox/compose', \App\Livewire\Admin\Mailbox\ComposeComponent::class)->name('admin.mailbox.compose');
    Route::get('mailbox/inbox', \App\Livewire\Admin\Mailbox\InboxComponent::class)->name('admin.mailbox.inbox');
    Route::get('mailbox/sent', \App\Livewire\Admin\Mailbox\SentComponent::class)->name('admin.mailbox.sent');
    Route::get('mailbox/important', \App\Livewire\Admin\Mailbox\ImportantComponent::class)->name('admin.mailbox.important');
    Route::get('mailbox/trash', \App\Livewire\Admin\Mailbox\TrashComponent::class)->name('admin.mailbox.trash');

    Route::get('notice-board', \App\Livewire\Admin\Notice\NoticeComponent::class)->name('admin.notice');

    Route::get('setting/school', \App\Livewire\Admin\Setting\SchoolComponent::class)->name('admin.setting.school');
    Route::get('setting/sessions', \App\Livewire\Admin\Setting\SessionComponent::class)->name('admin.setting.sessions');
    Route::get('setting/loginlog', \App\Livewire\Admin\Setting\LoginlogComponent::class)->name('admin.setting.loginlog');

    Route::get('profile/overview', \App\Livewire\Admin\Profile\OverviewComponent::class)->name('admin.profile.overview');
    Route::get('profile/setting', \App\Livewire\Admin\Profile\SettingComponent::class)->name('admin.profile.setting');
    Route::get('profile/loginlog', \App\Livewire\Admin\Profile\LoginlogComponent::class)->name('admin.profile.loginlog');

    Route::get('notifications', \App\Livewire\Admin\Notifications\Index::class)->name('admin.notifications.index');
});


// Accountant
// Route::middleware(['role:accountant'])->group(function () {
//     Route::get('accountant/student/create', \App\Livewire\Tenant\Accountant\Student\StudentAddComponent::class)->name('accountant.student.add');
//     Route::get('accountant/student/list', \App\Livewire\Tenant\Accountant\Student\StudentListComponent::class)->name('accountant.student.list');
//     Route::get('accountant/student/{id}/edit', \App\Livewire\Tenant\Accountant\Student\StudentEditComponent::class)->name('accountant.student.edit');
//     Route::get('accountant/student/{id}/overview', \App\Livewire\Tenant\Accountant\StudentOverviewComponent::class)->name('accountant.student.overview');

//     Route::get('accountant/dashboard', \App\Livewire\Tenant\Accountant\DashboardComponent::class)->name('accountant.dashboard');
//     Route::get('accountant/salary/add-template', \App\Livewire\Tenant\Accountant\Salary\AddTemplateComponent::class)->name('accountant.salary.add-template');
//     Route::get('accountant/salary/{id}/edit-template', \App\Livewire\Tenant\Accountant\Salary\EditTemplateComponent::class)->name('accountant.salary.edit-template');
//     Route::get('accountant/salary/list-template', \App\Livewire\Tenant\Accountant\Salary\ListTemplateComponent::class)->name('accountant.salary.list-template');
//     Route::get('accountant/salary/assign', \App\Livewire\Tenant\Accountant\Salary\AssignComponent::class)->name('accountant.salary.assign');
//     Route::get('accountant/salary/{id}/{month}/add-payment', \App\Livewire\Tenant\Accountant\Salary\AddPaymentComponent::class)->name('accountant.salary.add-payment');
//     Route::get('accountant/salary/{id}/{month}/invoice-payment', \App\Livewire\Tenant\Accountant\Salary\InvoicePaymentComponent::class)->name('accountant.salary.invoice-payment');
//     Route::get('accountant/salary/payment', \App\Livewire\Tenant\Accountant\Salary\PaymentComponent::class)->name('accountant.salary.payment');

//     Route::get('accountant/leave/categories', \App\Livewire\Tenant\Accountant\Leave\CategoryComponent::class)->name('accountant.leave.categories');
//     Route::get('accountant/leave/applications', \App\Livewire\Tenant\Accountant\Leave\ApplicationComponent::class)->name('accountant.leave.applications');
    
//     Route::get('accountant/office-accounting/accounts', \App\Livewire\Tenant\Accountant\OfficeAccounting\AccountComponent::class)->name('accountant.office-accounting.accounts');
//     Route::get('accountant/office-accounting/voucher-head', \App\Livewire\Tenant\Accountant\OfficeAccounting\HeadComponent::class)->name('accountant.office-accounting.heads');
//     Route::get('accountant/office-accounting/voucher-deposit-add', \App\Livewire\Tenant\Accountant\OfficeAccounting\DepositAddComponent::class)->name('accountant.office-accounting.deposit.add');
//     Route::get('accountant/office-accounting/{id}/voucher-deposit-edit', \App\Livewire\Tenant\Accountant\OfficeAccounting\DepositEditComponent::class)->name('accountant.office-accounting.deposit.edit');
//     Route::get('accountant/office-accounting/voucher-deposit-list', \App\Livewire\Tenant\Accountant\OfficeAccounting\DepositListComponent::class)->name('accountant.office-accounting.deposit.list');
//     Route::get('accountant/office-accounting/voucher-expense-add', \App\Livewire\Tenant\Accountant\OfficeAccounting\ExpenseAddComponent::class)->name('accountant.office-accounting.expense.add');
//     Route::get('accountant/office-accounting/{id}/voucher-expense-edit', \App\Livewire\Tenant\Accountant\OfficeAccounting\ExpenseEditComponent::class)->name('accountant.office-accounting.expense.edit');
//     Route::get('accountant/office-accounting/voucher-expense-list', \App\Livewire\Tenant\Accountant\OfficeAccounting\ExpenseListComponent::class)->name('accountant.office-accounting.expense.list');
//     Route::get('accountant/office-accounting/transactions', \App\Livewire\Tenant\Accountant\OfficeAccounting\TransactionComponent::class)->name('accountant.office-accounting.transactions');

//     Route::get('accountant/student-accounting/fee-types', \App\Livewire\Tenant\Accountant\StudentAccounting\FeeTypeComponent::class)->name('accountant.student-accounting.fee.types');
//     Route::get('accountant/student-accounting/fee-groups', \App\Livewire\Tenant\Accountant\StudentAccounting\FeeGroupComponent::class)->name('accountant.student-accounting.fee.groups');
//     Route::get('accountant/student-accounting/fee-fines', \App\Livewire\Tenant\Accountant\StudentAccounting\FeeFineComponent::class)->name('accountant.student-accounting.fee.fines');
//     Route::get('accountant/student-accounting/fee-allocations', \App\Livewire\Tenant\Accountant\StudentAccounting\FeeAllocationComponent::class)->name('accountant.student-accounting.fee.allocations');
//     Route::get('accountant/student-accounting/fee-invoices', \App\Livewire\Tenant\Accountant\StudentAccounting\FeeInvoiceComponent::class)->name('accountant.student-accounting.fee.invoices');

//     Route::get('accountant/student/{id}/invoice', \App\Livewire\Tenant\Accountant\StudentInvoiceComponent::class)->name('accountant.student.invoice');
//     Route::get('accountant/student/{id}/payment/add', \App\Livewire\Tenant\Accountant\StudentAccounting\PaymentAddComponent::class)->name('accountant.student.payment.add');

//     Route::get('accountant/profile/overview', \App\Livewire\Tenant\Accountant\Profile\OverviewComponent::class)->name('accountant.profile.overview');
//     Route::get('accountant/profile/setting', \App\Livewire\Tenant\Accountant\Profile\SettingComponent::class)->name('accountant.profile.setting');
//     Route::get('accountant/profile/activity', \App\Livewire\Tenant\Accountant\Profile\ActivityComponent::class)->name('accountant.profile.activity');

// });

// Teacher
// Route::middleware(['role:teacher'])->group(function () {
//     Route::get('teacher/dashboard', \App\Livewire\Tenant\Teacher\DashboardComponent::class)->name('teacher.dashboard');
//     Route::get('teacher/student/create', \App\Livewire\Tenant\Teacher\Student\StudentAddComponent::class)->name('teacher.student.add');
//     Route::get('teacher/student/list', \App\Livewire\Tenant\Teacher\Student\StudentListComponent::class)->name('teacher.student.list');
//     Route::get('teacher/student/{id}/edit', \App\Livewire\Tenant\Teacher\Student\StudentEditComponent::class)->name('teacher.student.edit');
//     Route::get('teacher/student/{id}/overview', \App\Livewire\Tenant\Teacher\StudentOverviewComponent::class)->name('teacher.student.overview');
//     // Academic
//     Route::get('teacher/academic/categories', \App\Livewire\Tenant\Teacher\Academic\CategoryComponent::class)->name('teacher.academic.categories');
//     Route::get('teacher/academic/classes', \App\Livewire\Tenant\Teacher\Academic\ClassComponent::class)->name('teacher.academic.classes');
//     Route::get('teacher/academic/sections', \App\Livewire\Tenant\Teacher\Academic\SectionComponent::class)->name('teacher.academic.sections');
//     Route::get('teacher/academic/subjects', \App\Livewire\Tenant\Teacher\Academic\SubjectComponent::class)->name('teacher.academic.subjects');
//     Route::get('teacher/academic/class-assign', \App\Livewire\Tenant\Teacher\Academic\ClassAssignComponent::class)->name('teacher.academic.class-assign');
//     Route::get('teacher/academic/teacher-assign', \App\Livewire\Tenant\Teacher\Academic\TeacherAssignComponent::class)->name('teacher.academic.teacher-assign');
//     Route::get('teacher/academic/class-schedule/create', \App\Livewire\Tenant\Teacher\Academic\ClassScheduleCreateComponent::class)->name('teacher.academic.class-schedule.create');
//     Route::get('teacher/academic/class-schedule/list', \App\Livewire\Tenant\Teacher\Academic\ClassScheduleListComponent::class)->name('teacher.academic.class-schedule.list');
//     Route::get('teacher/academic/teacher-schedule', \App\Livewire\Tenant\Teacher\Academic\TeacherScheduleComponent::class)->name('teacher.academic.teacher-schedule');
//     Route::get('teacher/academic/student-promotion', \App\Livewire\Tenant\Teacher\Academic\StudentPromotionComponent::class)->name('teacher.academic.student-promotion');

//     Route::get('teacher/parent/list', \App\Livewire\Tenant\Teacher\Parent\ParentListComponent::class)->name('teacher.parent.list');
//     Route::get('teacher/parent/add', \App\Livewire\Tenant\Teacher\Parent\ParentAddComponent::class)->name('teacher.parent.add');
//     Route::get('teacher/parent/edit/{id}', \App\Livewire\Tenant\Teacher\Parent\ParentEditComponent::class)->name('teacher.parent.edit');
//     Route::get('teacher/parent/{id}/overview', \App\Livewire\Tenant\Teacher\Parent\ParentOverviewComponent::class)->name('teacher.parent.overview');
//     Route::get('teacher/parent/{id}/child', \App\Livewire\Tenant\Teacher\Parent\ParentChildComponent::class)->name('teacher.parent.child');

//     Route::get('teacher/homework/add', \App\Livewire\Tenant\Teacher\Homework\HomeworkAddComponent::class)->name('teacher.homework.add');
//     Route::get('teacher/homework/list', \App\Livewire\Tenant\Teacher\Homework\HomeworkListComponent::class)->name('teacher.homework.list');
//     Route::get('teacher/homework/edit/{id}', \App\Livewire\Tenant\Teacher\Homework\HomeworkEditComponent::class)->name('teacher.homework.edit');

//     Route::get('teacher/leave/categories', \App\Livewire\Tenant\Teacher\Leave\CategoryComponent::class)->name('teacher.leave.categories');
//     Route::get('teacher/leave/applications', \App\Livewire\Tenant\Teacher\Leave\ApplicationComponent::class)->name('teacher.leave.applications');
    
//     Route::get('teacher/exam/terms', \App\Livewire\Tenant\Teacher\Exam\TermComponent::class)->name('teacher.exam.terms');
//     Route::get('teacher/exam/halls', \App\Livewire\Tenant\Teacher\Exam\HallComponent::class)->name('teacher.exam.halls');
//     Route::get('teacher/exam/marks', \App\Livewire\Tenant\Teacher\Exam\MarkComponent::class)->name('teacher.exam.marks');    
//     Route::get('teacher/exam/types', \App\Livewire\Tenant\Teacher\Exam\TypeComponent::class)->name('teacher.exam.types');    
//     Route::get('teacher/exam/setups', \App\Livewire\Tenant\Teacher\Exam\ExamSetupComponent::class)->name('teacher.exam.setups');
//     Route::get('teacher/exam/schedule/add', \App\Livewire\Tenant\Teacher\Exam\ScheduleAddComponent::class)->name('teacher.exam.schedule.add');       
//     Route::get('teacher/exam/schedule/list', \App\Livewire\Tenant\Teacher\Exam\ScheduleListComponent::class)->name('teacher.exam.schedule.list');    
    
//     Route::get('teacher/exam/grades', \App\Livewire\Tenant\Teacher\Exam\GradeComponent::class)->name('teacher.exam.grades');

//     Route::get('teacher/attendance/students', \App\Livewire\Tenant\Teacher\Attendance\StudentComponent::class)->name('teacher.attendance.students');
//     Route::get('teacher/attendance/employees', \App\Livewire\Tenant\Teacher\Attendance\EmployeeComponent::class)->name('teacher.attendance.employees');
//     Route::get('teacher/attendance/exams', \App\Livewire\Tenant\Teacher\Attendance\ExamComponent::class)->name('teacher.attendance.exams');

//     Route::get('teacher/event/types', \App\Livewire\Tenant\Teacher\Event\TypeComponent::class)->name('teacher.event.types');
//     Route::get('teacher/event/add', \App\Livewire\Tenant\Teacher\Event\AddComponent::class)->name('teacher.event.add');
//     Route::get('teacher/events/{id}/edit', \App\Livewire\Tenant\Teacher\Event\EditComponent::class)->name('teacher.event.edit');
//     Route::get('teacher/event/list', \App\Livewire\Tenant\Teacher\Event\ListComponent::class)->name('teacher.event.list');
    
//     Route::get('teacher/profile/overview', \App\Livewire\Tenant\Teacher\Profile\OverviewComponent::class)->name('teacher.profile.overview');
//     Route::get('teacher/profile/setting', \App\Livewire\Tenant\Teacher\Profile\SettingComponent::class)->name('teacher.profile.setting');
//     Route::get('teacher/profile/activity', \App\Livewire\Tenant\Teacher\Profile\ActivityComponent::class)->name('teacher.profile.activity');
// });
    
// Parent
// Route::middleware(['role:parent'])->group(function () {
//     Route::get('parent/dashboard', \App\Livewire\Tenant\Parent\DashboardComponent::class)->name('parent.dashboard');
// });

// Student
// Route::middleware(['role:student'])->group(function () {
//     Route::get('student/dashboard', \App\Livewire\Tenant\Student\DashboardComponent::class)->name('student.dashboard');
//     Route::get('student/teachers', \App\Livewire\Tenant\Student\TeacherComponent::class)->name('student.teachers');
//     Route::get('subjects', \App\Livewire\Tenant\Student\SubjectComponent::class)->name('student.subjects');
//     Route::get('classes', \App\Livewire\Tenant\Student\ClassComponent::class)->name('student.classes');
//     Route::get('leaves', \App\Livewire\Tenant\Student\LeaveComponent::class)->name('student.leaves');
//     Route::get('homeworks', \App\Livewire\Tenant\Student\HomeworkComponent::class)->name('student.homeworks');
//     Route::get('exams', \App\Livewire\Tenant\Student\ExamComponent::class)->name('student.exams');
//     Route::get('student/events', \App\Livewire\Tenant\Student\EventComponent::class)->name('student.events');
//     Route::get('profile-detail', \App\Livewire\Tenant\Student\Profile\DetailComponent::class)->name('student.profile.detail');
//     Route::get('profile-edit', \App\Livewire\Tenant\Student\Profile\EditComponent::class)->name('student.profile.edit');
// });




// Super Admin
// Route::get('setting/backups', \App\Livewire\Admin\Setting\BackupComponent::class)->name('admin.setting.backups');

