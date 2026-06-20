<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\RegistrationPaymentController;

Route::get('register', \App\Livewire\RegisterComponent::class)->name('register');
Route::get('login', \App\Livewire\LoginComponent::class)->name('login');
Route::get('forgot-password', \App\Livewire\Auth\ForgotPasswordComponent::class)->name('forgot.password');
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
        'super_admin' => redirect()->route('superadmin.dashboard'),
        default => abort(403),
    };
})->middleware('auth')->name('dashboard');


// Admin
Route::middleware(['auth', 'role:admin', 'billing.check'])->group(function () {
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
    Route::get('/student/{id}/overview', \App\Livewire\Admin\Student\StudentOverviewComponent::class)->name('admin.student.overview');
    Route::get('student/{id}/invoice', \App\Livewire\Admin\Student\StudentInvoiceComponent::class)->name('admin.student.invoice');
    Route::get('student/{id}/payment/add', \App\Livewire\Admin\Student\PaymentAddComponent::class)->name('admin.student.payment.add');

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

    Route::get('mailbox/compose', \App\Livewire\Admin\Mailbox\ComposeComponent::class)->name('admin.mailbox.compose');
    Route::get('mailbox/inbox', \App\Livewire\Admin\Mailbox\InboxComponent::class)->name('admin.mailbox.inbox');
    Route::get('mailbox/sent', \App\Livewire\Admin\Mailbox\SentComponent::class)->name('admin.mailbox.sent');
    Route::get('mailbox/important', \App\Livewire\Admin\Mailbox\ImportantComponent::class)->name('admin.mailbox.important');
    Route::get('mailbox/trash', \App\Livewire\Admin\Mailbox\TrashComponent::class)->name('admin.mailbox.trash');

    Route::get('notice-board', \App\Livewire\Admin\Notice\NoticeComponent::class)->name('admin.notice');
    Route::get('activity-logs', \App\Livewire\Admin\Log\ActivityLogComponent::class)->name('admin.activitylog');
    Route::get('login-logs', \App\Livewire\Admin\Log\LoginLogComponent::class)->name('admin.loginlog');
    Route::get('notifications', \App\Livewire\Admin\Notifications\Index::class)->name('admin.notifications.index');

    Route::get('setting/school', \App\Livewire\Admin\Setting\SchoolComponent::class)->name('admin.setting.school');
    Route::get('setting/sessions', \App\Livewire\Admin\Setting\SessionComponent::class)->name('admin.setting.sessions');

    Route::get('profile/overview', \App\Livewire\Admin\Profile\OverviewComponent::class)->name('admin.profile.overview');
    Route::get('profile/setting', \App\Livewire\Admin\Profile\SettingComponent::class)->name('admin.profile.setting');
    Route::get('profile/activitylog', \App\Livewire\Admin\Profile\ActivityLogComponent::class)->name('admin.profile.activitylog');
    Route::get('profile/loginlog', \App\Livewire\Admin\Profile\LoginlogComponent::class)->name('admin.profile.loginlog');
});

    // ════════════════════════════════════════
    // BILLING (Monthly Invoice Payment)
    // ════════════════════════════════════════
    Route::middleware(['auth'])->group(function () {
        Route::get('/billing', \App\Livewire\Admin\Billing\BillingShow::class)->name('billing.show');
        Route::get('/billing/{invoice}/pay', [PaymentController::class, 'pay'])->name('billing.pay');
    });

    Route::controller(PaymentController::class)
    ->prefix('billing/payment')
    ->name('billing.payment.')
    ->group(function () {
        Route::post('success', 'success')->name('success');
        Route::post('fail',    'fail')->name('fail');
        Route::post('cancel',  'cancel')->name('cancel');
        Route::post('ipn',     'ipn')->name('ipn');

        Route::get('result', function () { return view('admin.billing.payment-result'); })->name('result');
    });

    // ════════════════════════════════════════
    // REGISTRATION (New School Setup Payment)
    // ════════════════════════════════════════
    Route::controller(RegistrationPaymentController::class)
    ->prefix('registration/payment')
    ->name('registration.payment.')
    ->group(function () {
        Route::get('pay',      'pay')->name('pay');
        Route::post('success', 'success')->name('success');
        Route::post('fail',    'fail')->name('fail');
        Route::post('cancel',  'cancel')->name('cancel');
        Route::post('ipn',     'ipn')->name('ipn');
    });

// Accountant
Route::middleware(['auth', 'role:accountant', 'billing.check'])->group(function () {
    Route::get('accountant/dashboard', \App\Livewire\Accountant\DashboardComponent::class)->name('accountant.dashboard');

    // Inventory
    Route::get('accountant/inventory/units', \App\Livewire\Accountant\Inventory\UnitComponent::class)->name('accountant.inventory.units');
    Route::get('accountant/inventory/categories', \App\Livewire\Accountant\Inventory\CategoryComponent::class)->name('accountant.inventory.categories');
    Route::get('accountant/inventory/stores', \App\Livewire\Accountant\Inventory\StoreComponent::class)->name('accountant.inventory.stores');
    Route::get('accountant/inventory/suppliers', \App\Livewire\Accountant\Inventory\SupplierComponent::class)->name('accountant.inventory.suppliers');
    Route::get('accountant/inventory/products', \App\Livewire\Accountant\Inventory\ProductComponent::class)->name('accountant.inventory.products');
    Route::get('accountant/inventory/purchase/list', \App\Livewire\Accountant\Inventory\PurchaseListComponent::class)->name('accountant.inventory.purchase.list');
    Route::get('accountant/inventory/purchase/add', \App\Livewire\Accountant\Inventory\PurchaseAddComponent::class)->name('accountant.inventory.purchase.add');
    Route::get('accountant/inventory/purchase/{id}/edit', \App\Livewire\Accountant\Inventory\PurchaseEditComponent::class)->name('accountant.inventory.purchase.edit');
    Route::get('accountant/inventory/sale/list', \App\Livewire\Accountant\Inventory\SaleListComponent::class)->name('accountant.inventory.sale.list');
    Route::get('accountant/inventory/sale/add', \App\Livewire\Accountant\Inventory\SaleAddComponent::class)->name('accountant.inventory.sale.add');
    Route::get('accountant/inventory/sale/{id}/edit', \App\Livewire\Accountant\Inventory\SaleEditComponent::class)->name('accountant.inventory.sale.edit');
    
    // Student
    Route::get('accountant/student/create', \App\Livewire\Accountant\Student\StudentAddComponent::class)->name('accountant.student.add');
    Route::get('accountant/student/list', \App\Livewire\Accountant\Student\StudentListComponent::class)->name('accountant.student.list');
    Route::get('accountant/student/{id}/edit', \App\Livewire\Accountant\Student\StudentEditComponent::class)->name('accountant.student.edit');
    Route::get('accountant/student/{id}/overview', \App\Livewire\Accountant\Student\StudentOverviewComponent::class)->name('accountant.student.overview');
    Route::get('accountant/student/{id}/invoice', \App\Livewire\Accountant\Student\StudentInvoiceComponent::class)->name('accountant.student.invoice');
    Route::get('accountant/student/{id}/payment/add', \App\Livewire\Accountant\Student\PaymentAddComponent::class)->name('accountant.student.payment.add');

    // Parent
    Route::get('accountant/parent/list', \App\Livewire\Accountant\Parent\ParentListComponent::class)->name('accountant.parent.list');
    Route::get('accountant/parent/add', \App\Livewire\Accountant\Parent\ParentAddComponent::class)->name('accountant.parent.add');
    Route::get('accountant/parent/edit/{id}', \App\Livewire\Accountant\Parent\ParentEditComponent::class)->name('accountant.parent.edit');
    Route::get('accountant/parent/{id}/overview', \App\Livewire\Accountant\Parent\ParentOverviewComponent::class)->name('accountant.parent.overview');
    Route::get('accountant/parent/{id}/child', \App\Livewire\Accountant\Parent\ParentChildComponent::class)->name('accountant.parent.child');

    // Employee
    Route::get('accountant/employee/departments', \App\Livewire\Accountant\Employee\DepartmentComponent::class)->name('accountant.employee.departments');
    Route::get('accountant/employee/designations', \App\Livewire\Accountant\Employee\DesignationComponent::class)->name('accountant.employee.designations');
    Route::get('accountant/employee/list', \App\Livewire\Accountant\Employee\EmployeeListComponent::class)->name('accountant.employee.list');
    Route::get('accountant/employee/add', \App\Livewire\Accountant\Employee\EmployeeAddComponent::class)->name('accountant.employee.add');
    Route::get('accountant/employee/edit/{id}', \App\Livewire\Accountant\Employee\EmployeeEditComponent::class)->name('accountant.employee.edit');

    // Card
    Route::get('accountant/card/id-card-templates', \App\Livewire\Accountant\Card\IdCardTemplateComponent::class)->name('accountant.card.id-card-templates');
    Route::get('accountant/card/student-id-cards', \App\Livewire\Accountant\Card\StudentIdCardComponent::class)->name('accountant.card.student-id-cards');
    Route::get('accountant/card/employee-id-cards', \App\Livewire\Accountant\Card\EmployeeIdCardComponent::class)->name('accountant.card.employee-id-cards');    
    Route::get('accountant/card/admit-card-templates', \App\Livewire\Accountant\Card\AdmitCardTemplateComponent::class)->name('accountant.card.admit-card-templates');
    Route::get('accountant/card/generate-admit-cards', \App\Livewire\Accountant\Card\GenerateAdmitCardComponent::class)->name('accountant.card.generate-admit-cards');

    // Certificate
    Route::get('accountant/certificate/add-template', \App\Livewire\Accountant\Certificate\AddTemplateComponent::class)->name('accountant.certificate.add-template');
    Route::get('accountant/certificate/{id}/edit-template', \App\Livewire\Accountant\Certificate\EditTemplateComponent::class)->name('accountant.certificate.edit-template');
    Route::get('accountant/certificate/list-template', \App\Livewire\Accountant\Certificate\ListTemplateComponent::class)->name('accountant.certificate.list-template');
    Route::get('accountant/certificate/generate-student', \App\Livewire\Accountant\Certificate\GenerateStudentComponent::class)->name('accountant.certificate.generate-student');
    Route::get('accountant/certificate/generate-employee', \App\Livewire\Accountant\Certificate\GenerateEmployeeComponent::class)->name('accountant.certificate.generate-employee');
    
    // Salary 
    Route::get('accountant/salary/add-template', \App\Livewire\Accountant\Salary\AddTemplateComponent::class)->name('accountant.salary.add-template');
    Route::get('accountant/salary/{id}/edit-template', \App\Livewire\Accountant\Salary\EditTemplateComponent::class)->name('accountant.salary.edit-template');
    Route::get('accountant/salary/list-template', \App\Livewire\Accountant\Salary\ListTemplateComponent::class)->name('accountant.salary.list-template');
    Route::get('accountant/salary/assign', \App\Livewire\Accountant\Salary\AssignComponent::class)->name('accountant.salary.assign');
    Route::get('accountant/salary/{id}/{month}/add-payment', \App\Livewire\Accountant\Salary\AddPaymentComponent::class)->name('accountant.salary.add-payment');
    Route::get('accountant/salary/{id}/{month}/invoice-payment', \App\Livewire\Accountant\Salary\InvoicePaymentComponent::class)->name('accountant.salary.invoice-payment');
    Route::get('accountant/salary/payment', \App\Livewire\Accountant\Salary\PaymentComponent::class)->name('accountant.salary.payment');

    // Leave
    Route::get('accountant/leave/categories', \App\Livewire\Accountant\Leave\CategoryComponent::class)->name('accountant.leave.categories');
    Route::get('accountant/leave/applications', \App\Livewire\Accountant\Leave\ApplicationComponent::class)->name('accountant.leave.applications');

    // Office Accounting
    Route::get('accountant/office-accounting/accounts', \App\Livewire\Accountant\OfficeAccounting\AccountComponent::class)->name('accountant.office-accounting.accounts');
    Route::get('accountant/office-accounting/voucher-head', \App\Livewire\Accountant\OfficeAccounting\HeadComponent::class)->name('accountant.office-accounting.heads');
    Route::get('accountant/office-accounting/voucher-deposit-add', \App\Livewire\Accountant\OfficeAccounting\DepositAddComponent::class)->name('accountant.office-accounting.deposit.add');
    Route::get('accountant/office-accounting/{id}/voucher-deposit-edit', \App\Livewire\Accountant\OfficeAccounting\DepositEditComponent::class)->name('accountant.office-accounting.deposit.edit');
    Route::get('accountant/office-accounting/voucher-deposit-list', \App\Livewire\Accountant\OfficeAccounting\DepositListComponent::class)->name('accountant.office-accounting.deposit.list');
    Route::get('accountant/office-accounting/voucher-expense-add', \App\Livewire\Accountant\OfficeAccounting\ExpenseAddComponent::class)->name('accountant.office-accounting.expense.add');
    Route::get('accountant/office-accounting/{id}/voucher-expense-edit', \App\Livewire\Accountant\OfficeAccounting\ExpenseEditComponent::class)->name('accountant.office-accounting.expense.edit');
    Route::get('accountant/office-accounting/voucher-expense-list', \App\Livewire\Accountant\OfficeAccounting\ExpenseListComponent::class)->name('accountant.office-accounting.expense.list');
    Route::get('accountant/office-accounting/transactions', \App\Livewire\Accountant\OfficeAccounting\TransactionComponent::class)->name('accountant.office-accounting.transactions');

    // Student Accountant
    Route::get('accountant/student-accounting/fee-types', \App\Livewire\Accountant\StudentAccounting\FeeTypeComponent::class)->name('accountant.student-accounting.fee.types');
    Route::get('accountant/student-accounting/fee-groups', \App\Livewire\Accountant\StudentAccounting\FeeGroupComponent::class)->name('accountant.student-accounting.fee.groups');
    Route::get('accountant/student-accounting/fee-fines', \App\Livewire\Accountant\StudentAccounting\FeeFineComponent::class)->name('accountant.student-accounting.fee.fines');
    Route::get('accountant/student-accounting/fee-allocations', \App\Livewire\Accountant\StudentAccounting\FeeAllocationComponent::class)->name('accountant.student-accounting.fee.allocations');
    Route::get('accountant/student-accounting/fee-invoices', \App\Livewire\Accountant\StudentAccounting\FeeInvoiceComponent::class)->name('accountant.student-accounting.fee.invoices');

    // Event 
    Route::get('accountant/event/types', \App\Livewire\Accountant\Event\TypeComponent::class)->name('accountant.event.types');
    Route::get('accountant/event/add', \App\Livewire\Accountant\Event\AddComponent::class)->name('accountant.event.add');
    Route::get('accountant/events/{id}/edit', \App\Livewire\Accountant\Event\EditComponent::class)->name('accountant.event.edit');
    Route::get('accountant/event/list', \App\Livewire\Accountant\Event\ListComponent::class)->name('accountant.event.list');
    
    // Mailbox
    Route::get('accountant/mailbox/compose', \App\Livewire\Accountant\Mailbox\ComposeComponent::class)->name('accountant.mailbox.compose');
    Route::get('accountant/mailbox/inbox', \App\Livewire\Accountant\Mailbox\InboxComponent::class)->name('accountant.mailbox.inbox');
    Route::get('accountant/mailbox/sent', \App\Livewire\Accountant\Mailbox\SentComponent::class)->name('accountant.mailbox.sent');
    Route::get('accountant/mailbox/important', \App\Livewire\Accountant\Mailbox\ImportantComponent::class)->name('accountant.mailbox.important');
    Route::get('accountant/mailbox/trash', \App\Livewire\Accountant\Mailbox\TrashComponent::class)->name('accountant.mailbox.trash');

    Route::get('accountant/notice-board', \App\Livewire\Accountant\Notice\NoticeComponent::class)->name('accountant.notice');
    Route::get('accountant/notifications', \App\Livewire\Accountant\Notifications\Index::class)->name('accountant.notifications.index');

    // Profile 
    Route::get('accountant/profile/overview', \App\Livewire\Accountant\Profile\OverviewComponent::class)->name('accountant.profile.overview');
    Route::get('accountant/profile/setting', \App\Livewire\Accountant\Profile\SettingComponent::class)->name('accountant.profile.setting');
    Route::get('accountant/profile/activitylog', \App\Livewire\Accountant\Profile\ActivityLogComponent::class)->name('accountant.profile.activitylog');
    Route::get('accountant/profile/loginlog', \App\Livewire\Accountant\Profile\LoginlogComponent::class)->name('accountant.profile.loginlog');

});

// Teacher
Route::middleware(['auth', 'role:teacher', 'billing.check'])->group(function () {
    Route::get('teacher/dashboard', \App\Livewire\Teacher\DashboardComponent::class)->name('teacher.dashboard');

    // Student
    Route::get('teacher/student/create', \App\Livewire\Teacher\Student\StudentAddComponent::class)->name('teacher.student.add');
    Route::get('teacher/student/list', \App\Livewire\Teacher\Student\StudentListComponent::class)->name('teacher.student.list');
    Route::get('teacher/student/{id}/edit', \App\Livewire\Teacher\Student\StudentEditComponent::class)->name('teacher.student.edit');
    Route::get('teacher/student/{id}/overview', \App\Livewire\Teacher\Student\StudentOverviewComponent::class)->name('teacher.student.overview');
    Route::get('teacher/student/{id}/invoice', \App\Livewire\Teacher\Student\StudentInvoiceComponent::class)->name('teacher.student.invoice');
    Route::get('teacher/student/{id}/payment/add', \App\Livewire\Teacher\Student\PaymentAddComponent::class)->name('teacher.student.payment.add');

    // Parent
    Route::get('teacher/parent/list', \App\Livewire\Teacher\Parent\ParentListComponent::class)->name('teacher.parent.list');
    Route::get('teacher/parent/add', \App\Livewire\Teacher\Parent\ParentAddComponent::class)->name('teacher.parent.add');
    Route::get('teacher/parent/edit/{id}', \App\Livewire\Teacher\Parent\ParentEditComponent::class)->name('teacher.parent.edit');
    Route::get('teacher/parent/{id}/overview', \App\Livewire\Teacher\Parent\ParentOverviewComponent::class)->name('teacher.parent.overview');
    Route::get('teacher/parent/{id}/child', \App\Livewire\Teacher\Parent\ParentChildComponent::class)->name('teacher.parent.child');

    // // Academic
    Route::get('teacher/academic/categories', \App\Livewire\Teacher\Academic\CategoryComponent::class)->name('teacher.academic.categories');
    Route::get('teacher/academic/classes', \App\Livewire\Teacher\Academic\ClassComponent::class)->name('teacher.academic.classes');
    Route::get('teacher/academic/sections', \App\Livewire\Teacher\Academic\SectionComponent::class)->name('teacher.academic.sections');
    Route::get('teacher/academic/subjects', \App\Livewire\Teacher\Academic\SubjectComponent::class)->name('teacher.academic.subjects');
    Route::get('teacher/academic/class-assign', \App\Livewire\Teacher\Academic\ClassAssignComponent::class)->name('teacher.academic.class-assign');
    Route::get('teacher/academic/teacher-assign', \App\Livewire\Teacher\Academic\TeacherAssignComponent::class)->name('teacher.academic.teacher-assign');
    Route::get('teacher/academic/class-schedule/create', \App\Livewire\Teacher\Academic\ClassScheduleCreateComponent::class)->name('teacher.academic.class-schedule.create');
    Route::get('teacher/academic/class-schedule/list', \App\Livewire\Teacher\Academic\ClassScheduleListComponent::class)->name('teacher.academic.class-schedule.list');
    Route::get('teacher/academic/teacher-schedule', \App\Livewire\Teacher\Academic\TeacherScheduleComponent::class)->name('teacher.academic.teacher-schedule');
    Route::get('teacher/academic/student-promotion', \App\Livewire\Teacher\Academic\StudentPromotionComponent::class)->name('teacher.academic.student-promotion');

    // Homework
    Route::get('teacher/homework/add', \App\Livewire\Teacher\Homework\HomeworkAddComponent::class)->name('teacher.homework.add');
    Route::get('teacher/homework/list', \App\Livewire\Teacher\Homework\HomeworkListComponent::class)->name('teacher.homework.list');
    Route::get('teacher/homework/edit/{id}', \App\Livewire\Teacher\Homework\HomeworkEditComponent::class)->name('teacher.homework.edit');

    // Route::get('teacher/card/id-card-templates', \App\Livewire\Teacher\Card\IdCardTemplateComponent::class)->name('teacher.card.id-card-templates');
    // Route::get('teacher/card/student-id-cards', \App\Livewire\Teacher\Card\StudentIdCardComponent::class)->name('teacher.card.student-id-cards');
    // Route::get('teacher/card/employee-id-cards', \App\Livewire\Teacher\Card\EmployeeIdCardComponent::class)->name('teacher.card.employee-id-cards');    
    // Route::get('teacher/card/admit-card-templates', \App\Livewire\Teacher\Card\AdmitCardTemplateComponent::class)->name('teacher.card.admit-card-templates');
    // Route::get('teacher/card/generate-admit-cards', \App\Livewire\Teacher\Card\GenerateAdmitCardComponent::class)->name('teacher.card.generate-admit-cards');

    // Route::get('certificate/add-template', \App\Livewire\Teacher\Certificate\AddTemplateComponent::class)->name('teacher.certificate.add-template');
    // Route::get('certificate/{id}/edit-template', \App\Livewire\Teacher\Certificate\EditTemplateComponent::class)->name('teacher.certificate.edit-template');
    // Route::get('certificate/list-template', \App\Livewire\Teacher\Certificate\ListTemplateComponent::class)->name('teacher.certificate.list-template');
    // Route::get('certificate/generate-student', \App\Livewire\Teacher\Certificate\GenerateStudentComponent::class)->name('teacher.certificate.generate-student');
    // Route::get('certificate/generate-employee', \App\Livewire\Teacher\Certificate\GenerateEmployeeComponent::class)->name('teacher.certificate.generate-employee');
    

    // Leave
    Route::get('teacher/leave/applications', \App\Livewire\Teacher\Leave\ApplicationComponent::class)->name('teacher.leave.applications');
     
    // Exam 
    Route::get('teacher/exam/schedule/list', \App\Livewire\Teacher\Exam\ScheduleListComponent::class)->name('teacher.exam.schedule.list');    

    // Attendance
    Route::get('teacher/attendance/students', \App\Livewire\Teacher\Attendance\StudentComponent::class)->name('teacher.attendance.students');
    Route::get('teacher/attendance/exams', \App\Livewire\Teacher\Attendance\ExamComponent::class)->name('teacher.attendance.exams');

    // Event
    Route::get('teacher/event/types', \App\Livewire\Teacher\Event\TypeComponent::class)->name('teacher.event.types');
    Route::get('teacher/event/add', \App\Livewire\Teacher\Event\AddComponent::class)->name('teacher.event.add');
    Route::get('teacher/events/{id}/edit', \App\Livewire\Teacher\Event\EditComponent::class)->name('teacher.event.edit');
    Route::get('teacher/event/list', \App\Livewire\Teacher\Event\ListComponent::class)->name('teacher.event.list');
    
    // Mailbox
    Route::get('teacher/mailbox/compose', \App\Livewire\Teacher\Mailbox\ComposeComponent::class)->name('teacher.mailbox.compose');
    Route::get('teacher/mailbox/inbox', \App\Livewire\Teacher\Mailbox\InboxComponent::class)->name('teacher.mailbox.inbox');
    Route::get('teacher/mailbox/sent', \App\Livewire\Teacher\Mailbox\SentComponent::class)->name('teacher.mailbox.sent');
    Route::get('teacher/mailbox/important', \App\Livewire\Teacher\Mailbox\ImportantComponent::class)->name('teacher.mailbox.important');
    Route::get('teacher/mailbox/trash', \App\Livewire\Teacher\Mailbox\TrashComponent::class)->name('teacher.mailbox.trash');

    Route::get('teacher/notice-board', \App\Livewire\Teacher\Notice\NoticeComponent::class)->name('teacher.notice');
    Route::get('teacher/notifications', \App\Livewire\Teacher\Notifications\Index::class)->name('teacher.notifications.index');
    
    // Profile
    Route::get('teacher/profile/overview', \App\Livewire\Teacher\Profile\OverviewComponent::class)->name('teacher.profile.overview');
    Route::get('teacher/profile/setting', \App\Livewire\Teacher\Profile\SettingComponent::class)->name('teacher.profile.setting');
    Route::get('teacher/profile/activitylog', \App\Livewire\Teacher\Profile\ActivityLogComponent::class)->name('teacher.profile.activitylog');
    Route::get('teacher/profile/loginlog', \App\Livewire\Teacher\Profile\LoginlogComponent::class)->name('teacher.profile.loginlog');
    
});
    
// Parent
Route::middleware(['auth', 'role:parent', 'billing.check'])->group(function () {
    Route::get('parent/dashboard', \App\Livewire\Parent\DashboardComponent::class)->name('parent.dashboard');

    // Profile
    Route::get('parent/profile/overview', \App\Livewire\Parent\Profile\OverviewComponent::class)->name('parent.profile.overview');
    Route::get('parent/profile/setting', \App\Livewire\Parent\Profile\SettingComponent::class)->name('parent.profile.setting');
    Route::get('parent/profile/activitylog', \App\Livewire\Parent\Profile\ActivityLogComponent::class)->name('parent.profile.activitylog');
    Route::get('parent/profile/loginlog', \App\Livewire\Parent\Profile\LoginLogComponent::class)->name('parent.profile.loginlog');
});

// Student
Route::middleware(['auth', 'role:student', 'billing.check'])->group(function () {
    Route::get('student/dashboard', \App\Livewire\Student\DashboardComponent::class)->name('student.dashboard');
    Route::get('student/teachers', \App\Livewire\Student\TeacherComponent::class)->name('student.teachers');
    Route::get('student/subjects', \App\Livewire\Student\SubjectComponent::class)->name('student.subjects');
    Route::get('student/classes', \App\Livewire\Student\ClassComponent::class)->name('student.classes');
    Route::get('student/leaves', \App\Livewire\Student\LeaveComponent::class)->name('student.leaves');
    Route::get('student/homeworks', \App\Livewire\Student\HomeworkComponent::class)->name('student.homeworks');
    Route::get('student/exams', \App\Livewire\Student\ExamComponent::class)->name('student.exams');
    Route::get('student/events', \App\Livewire\Student\EventComponent::class)->name('student.events');

    // Profile
    Route::get('student/profile/overview', \App\Livewire\Student\Profile\OverviewComponent::class)->name('student.profile.overview');
    Route::get('student/profile/setting', \App\Livewire\Student\Profile\SettingComponent::class)->name('student.profile.setting');
    Route::get('student/profile/activitylog', \App\Livewire\Student\Profile\ActivityLogComponent::class)->name('student.profile.activitylog');
    Route::get('student/profile/loginlog', \App\Livewire\Student\Profile\LoginLogComponent::class)->name('student.profile.loginlog');
});


// Super Admin
Route::middleware(['auth', 'role:super_admin'])->prefix('superadmin')->name('superadmin.')->group(function () {
    Route::get('/dashboard',\App\Livewire\SuperAdmin\DashboardComponent::class)->name('dashboard');
    Route::get('/schools/index', \App\Livewire\SuperAdmin\School\SchoolListComponent::class)->name('schools.index');

    Route::get('/billings/invoices', \App\Livewire\SuperAdmin\Billing\InvoiceIndex::class)->name('invoices.index');

    Route::get('/activity-logs', \App\Livewire\SuperAdmin\Log\ActivityLogComponent::class)->name('activitylog');
    Route::get('/login-logs', \App\Livewire\SuperAdmin\Log\LoginLogComponent::class)->name('loginlog');

    Route::get('/monitoring/server', \App\Livewire\SuperAdmin\Monitoring\ServerStatusComponent::class)->name('monitoring.server');
    Route::get('/monitoring/queue', \App\Livewire\SuperAdmin\Monitoring\QueueMonitorComponent::class)->name('monitoring.queue');
    Route::get('/monitoring/logs', \App\Livewire\SuperAdmin\Monitoring\ErrorLogsComponent::class)->name('monitoring.logs');
    Route::get('/monitoring/performance', \App\Livewire\SuperAdmin\Monitoring\PerformanceMetricsComponent::class)->name('monitoring.performance');

    Route::get('/settings', \App\Livewire\SuperAdmin\Settings\SystemSettingsComponent::class)->name('settings');
});


    Route::get('/run-billing/{command}', function ($command) {
        $allowed = [
            'monthly-generate' => 'billing:monthly-generate',
            'check-overdue'    => 'billing:check-overdue',
        ];
        if (!array_key_exists($command, $allowed)) {
            abort(403, 'Not allowed');
        }
        Artisan::call($allowed[$command]);
        return response()->json(['status' => 'done', 'command' => $command]);
    })->middleware('auth'); // শুধু logged-in user চালাতে পারবে



    Route::get('key', function () {
        Artisan::call('key:generate');
        return redirect()->back()->with('success','Thanks for the generate key!');
    })->name('key');

    Route::get('clear', function () {
        Artisan::call('optimize:clear');
        return redirect()->back()->with('success','Thanks for the fast site!');
    })->name('clear');

    Route::get('link', function () {
        Artisan::call('storage:link');
        return redirect()->back()->with('success','Thanks for the link storage!');
    })->name('link');



// Route::get('setting/backups', \App\Livewire\Admin\Setting\BackupComponent::class)->name('admin.setting.backups');

    // Route::get('try', function () {
    //     auth()->user()->sendEmailVerificationNotification();
    //     return redirect()->back()->with('success','Thanks for the fast site!');
    // })->name('try');

    
    // Route::get('backup', function () {
    //     // Artisan::call('backup:clean');
    //     Artisan::call('backup:run');
    //     return redirect()->back()->with('success','Thanks for the backup!');
    // })->name('backup');

    // Route::get('permissionrefresh', function () {
    //     Artisan::call('migrate:refresh --path=/database/migrations/2024_01_15_210628_create_permission_tables.php');
    // });
    // Route::get('permissionreseed', function () {
    //     Artisan::call('db:seed --class=PermissionSeeder');
    // });
    Route::get('fresh', function () {
        Artisan::call('migrate:fresh --seed');
    });
    Route::get('migrate', function () {
        Artisan::call('migrate');
    });
    

    