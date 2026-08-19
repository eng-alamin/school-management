<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BillingPaymentController;
use App\Http\Controllers\RegistrationPaymentController;
use App\Http\Controllers\DeviceAttendanceController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;

Route::middleware('guest')->group(function () {
    Route::get('login', \App\Livewire\Auth\LoginComponent::class)->name('login');
    Route::get('forgot-password', \App\Livewire\Auth\ForgotPasswordComponent::class)->name('forgot.password');
    Route::get('/reset-password/{token}',  \App\Livewire\Auth\ResetPasswordComponent::class)->name('password.reset');
});

    // Frontend
    Route::get('/', \App\Livewire\Frontend\HomeComponent::class)->name('home');
    Route::get('/online-admission', \App\Livewire\Frontend\OnlineAdmissionComponent::class)->name('admission.online');
    Route::get('/admission/{admission}/edit', \App\Livewire\Frontend\ExistingAdmissionComponent::class)->middleware('signed')->name('admission.edit');
    
    Route::get('/institution-registration', \App\Livewire\Frontend\InstitutionRegistrationComponent::class)->name('institution.registration');
    Route::get('/teacher-registration', \App\Livewire\Frontend\TeacherRegistrationComponent::class)->name('teacher.registration');
    
    Route::get('/find-institution', \App\Livewire\Frontend\FindInstitutionComponent::class)->name('find.institution');
    Route::get('/view-institution/{institution}', \App\Livewire\Frontend\ViewInstitutionComponent::class)->name('view.institution');
    Route::get('/institution-committee/{institution}', \App\Livewire\Frontend\InstitutionCommitteeComponent::class)->name('view.committee');
    

    Route::get('dashboard', function () {
        try {
            return redirect()->route(auth()->user()->dashboardRoute());
        } catch (\RuntimeException $e) {
            abort(403);
        }
    })->middleware('auth')->name('dashboard');

    // ════════════════════════════════════════
    // REGISTRATION (New Institution Setup Payment)
    // ════════════════════════════════════════
    Route::controller(RegistrationPaymentController::class)->prefix('registration/payment')->name('registration.payment.')->group(function () {
        Route::get('pay',      'pay')->name('pay');
        Route::post('success', 'success')->name('success');
        Route::post('fail',    'fail')->name('fail');
        Route::post('cancel',  'cancel')->name('cancel');
        Route::post('ipn',     'ipn')->name('ipn');
    });

    // ════════════════════════════════════════
    // BILLING (Monthly Invoice Payment)
    // ════════════════════════════════════════
    Route::middleware(['auth'])->group(function () {
        Route::get('/billing', \App\Livewire\Admin\Billing\BillingShow::class)->name('billing.show');
        Route::get('/billing/{invoice}/pay', [BillingPaymentController::class, 'pay'])->name('billing.pay');
    });

    Route::controller(BillingPaymentController::class)->prefix('billing/payment')->name('billing.payment.')->group(function () {
        Route::post('success', 'success')->name('success');
        Route::post('fail',    'fail')->name('fail');
        Route::post('cancel',  'cancel')->name('cancel');
        Route::post('ipn',     'ipn')->name('ipn');

        Route::get('result', function () { return view('admin.billing.payment-result'); })->name('result');
    });


    
// Parent
Route::middleware(['auth', 'role:parent'])->group(function () {
    // Dashboard
    Route::get('parent/dashboard', \App\Livewire\Parent\DashboardComponent::class)->name('parent.dashboard');

    // Profile
    Route::get('parent/profile/overview', \App\Livewire\Parent\Profile\OverviewComponent::class)->name('parent.profile.overview');
    Route::get('parent/profile/setting', \App\Livewire\Parent\Profile\SettingComponent::class)->name('parent.profile.setting');
    Route::get('parent/profile/activitylog', \App\Livewire\Parent\Profile\ActivityLogComponent::class)->name('parent.profile.activitylog');
    Route::get('parent/profile/loginlog', \App\Livewire\Parent\Profile\LoginLogComponent::class)->name('parent.profile.loginlog');

    // Mailbox
    Route::get('parent/mailbox/compose', \App\Livewire\Parent\Mailbox\ComposeComponent::class)->name('parent.mailbox.compose');
    Route::get('parent/mailbox/inbox', \App\Livewire\Parent\Mailbox\InboxComponent::class)->name('parent.mailbox.inbox');
    Route::get('parent/mailbox/sent', \App\Livewire\Parent\Mailbox\SentComponent::class)->name('parent.mailbox.sent');
    Route::get('parent/mailbox/important', \App\Livewire\Parent\Mailbox\ImportantComponent::class)->name('parent.mailbox.important');
    Route::get('parent/mailbox/trash', \App\Livewire\Parent\Mailbox\TrashComponent::class)->name('parent.mailbox.trash');

    // Notification
    Route::get('parent/notifications', \App\Livewire\Parent\Notifications\Index::class)->name('parent.notifications.index');

    // Notice 
    Route::get('parent/notices', \App\Livewire\Parent\Notice\NoticeComponent::class)->name('parent.notices');

    // Grievances
    Route::get('/grievances/index', \App\Livewire\Guardian\Grievance\IndexComponent::class)->name('guardian.grievances.index');
    Route::get('/grievances/create', \App\Livewire\Guardian\Grievance\CreateComponent::class)->name('guardian.grievances.create');
});

// Student
Route::middleware(['auth', 'role:student'])->group(function () {
    Route::get('student/dashboard', \App\Livewire\Student\DashboardComponent::class)->name('student.dashboard');
    Route::get('student/attendances', \App\Livewire\Student\AttendanceComponent::class)->name('student.attendances');
    Route::get('student/teachers', \App\Livewire\Student\TeacherComponent::class)->name('student.teachers');
    Route::get('student/subjects', \App\Livewire\Student\SubjectComponent::class)->name('student.subjects');
    Route::get('student/classes', \App\Livewire\Student\ClassComponent::class)->name('student.classes');
    Route::get('student/leaves', \App\Livewire\Student\LeaveComponent::class)->name('student.leaves');
    Route::get('student/homeworks', \App\Livewire\Student\HomeworkComponent::class)->name('student.homeworks');
    Route::get('student/exams', \App\Livewire\Student\ExamComponent::class)->name('student.exams');
    Route::get('student/results', \App\Livewire\Student\ResultComponent::class)->name('student.results');
    Route::get('student/events', \App\Livewire\Student\EventComponent::class)->name('student.events');

    // Profile
    Route::get('student/profile/overview', \App\Livewire\Student\Profile\OverviewComponent::class)->name('student.profile.overview');
    Route::get('student/profile/setting', \App\Livewire\Student\Profile\SettingComponent::class)->name('student.profile.setting');
    Route::get('student/profile/activitylog', \App\Livewire\Student\Profile\ActivityLogComponent::class)->name('student.profile.activitylog');
    Route::get('student/profile/loginlog', \App\Livewire\Student\Profile\LoginLogComponent::class)->name('student.profile.loginlog');
    // Mailbox
    Route::get('student/mailbox/compose', \App\Livewire\Student\Mailbox\ComposeComponent::class)->name('student.mailbox.compose');
    Route::get('student/mailbox/inbox', \App\Livewire\Student\Mailbox\InboxComponent::class)->name('student.mailbox.inbox');
    Route::get('student/mailbox/sent', \App\Livewire\Student\Mailbox\SentComponent::class)->name('student.mailbox.sent');
    Route::get('student/mailbox/important', \App\Livewire\Student\Mailbox\ImportantComponent::class)->name('student.mailbox.important');
    Route::get('student/mailbox/trash', \App\Livewire\Student\Mailbox\TrashComponent::class)->name('student.mailbox.trash');
    
    // Grievances
    Route::get('/grievances/index', \App\Livewire\Student\Grievance\IndexComponent::class)->name('student.grievances.index');
    Route::get('/grievances/create', \App\Livewire\Student\Grievance\CreateComponent::class)->name('student.grievances.create');

    // Notification
    Route::get('student/notifications', \App\Livewire\Student\Notifications\Index::class)->name('student.notifications.index');
    // Notice
    Route::get('student/notices', \App\Livewire\Student\Notice\NoticeComponent::class)->name('student.notices');
});

// Teacher
Route::middleware(['auth', 'role:teacher', 'billing.check'])->group(function () {
    Route::get('teacher/dashboard', \App\Livewire\Teacher\DashboardComponent::class)->name('teacher.dashboard');
    // Student
    Route::get('teacher/student/list', \App\Livewire\Teacher\Student\StudentListComponent::class)->name('teacher.student.list');
    Route::get('teacher/student/{id}/overview', \App\Livewire\Teacher\Student\StudentOverviewComponent::class)->name('teacher.student.overview');
    // Parent
    Route::get('teacher/parent/list', \App\Livewire\Teacher\Parent\ParentListComponent::class)->name('teacher.parent.list');
    Route::get('teacher/parent/{id}/overview', \App\Livewire\Teacher\Parent\ParentOverviewComponent::class)->name('teacher.parent.overview');
    Route::get('teacher/parent/{id}/child', \App\Livewire\Teacher\Parent\ParentChildComponent::class)->name('teacher.parent.child');
    // Class Schedule
    Route::get('teacher/academic/class-schedule/list', \App\Livewire\Teacher\Academic\ClassScheduleListComponent::class)->name('teacher.academic.class-schedule.list');
    // Homework
    Route::get('teacher/homework/add', \App\Livewire\Teacher\Homework\HomeworkAddComponent::class)->name('teacher.homework.add');
    Route::get('teacher/homework/list', \App\Livewire\Teacher\Homework\HomeworkListComponent::class)->name('teacher.homework.list');
    Route::get('teacher/homework/edit/{id}', \App\Livewire\Teacher\Homework\HomeworkEditComponent::class)->name('teacher.homework.edit');
    // Leave
    Route::get('teacher/leave/apply', \App\Livewire\Teacher\Leave\ApplyLeaveComponent::class)->name('teacher.leave.apply');
    // Exam Schedule
    Route::get('teacher/exam/schedule/list', \App\Livewire\Teacher\Exam\ScheduleListComponent::class)->name('teacher.exam.schedule.list');    
    // Attendance
    Route::get('teacher/attendance/students', \App\Livewire\Teacher\Attendance\StudentComponent::class)->name('teacher.attendance.students');
    Route::get('teacher/attendance/exams', \App\Livewire\Teacher\Attendance\ExamComponent::class)->name('teacher.attendance.exams');
    //Salary
    Route::get('teacher/salary/history', \App\Livewire\Teacher\Salary\HistoryComponent::class)->name('teacher.salary.history');
    Route::get('teacher/salary/advance', \App\Livewire\Teacher\Salary\AdvanceComponent::class)->name('teacher.salary.advance');
    Route::get('teacher/salary/{id}/{month}/payslip', \App\Livewire\Teacher\Salary\PayslipComponent::class)->name('teacher.salary.payslip');
    // Event
    Route::get('teacher/event/add', \App\Livewire\Teacher\Event\AddComponent::class)->name('teacher.event.add');
    Route::get('teacher/events/{id}/edit', \App\Livewire\Teacher\Event\EditComponent::class)->name('teacher.event.edit');
    Route::get('teacher/event/list', \App\Livewire\Teacher\Event\ListComponent::class)->name('teacher.event.list');
    // Mailbox
    Route::get('teacher/mailbox/compose', \App\Livewire\Teacher\Mailbox\ComposeComponent::class)->name('teacher.mailbox.compose');
    Route::get('teacher/mailbox/inbox', \App\Livewire\Teacher\Mailbox\InboxComponent::class)->name('teacher.mailbox.inbox');
    Route::get('teacher/mailbox/sent', \App\Livewire\Teacher\Mailbox\SentComponent::class)->name('teacher.mailbox.sent');
    Route::get('teacher/mailbox/important', \App\Livewire\Teacher\Mailbox\ImportantComponent::class)->name('teacher.mailbox.important');
    Route::get('teacher/mailbox/trash', \App\Livewire\Teacher\Mailbox\TrashComponent::class)->name('teacher.mailbox.trash');
    // Notice
    Route::get('teacher/notices', \App\Livewire\Teacher\Notice\NoticeComponent::class)->name('teacher.notices');
    Route::get('teacher/notifications', \App\Livewire\Teacher\Notifications\Index::class)->name('teacher.notifications.index');
    
    // Grievances
    Route::get('/grievances/index', \App\Livewire\Teacher\Grievance\IndexComponent::class)->name('teacher.grievances.index');
    Route::get('/grievances/create', \App\Livewire\Teacher\Grievance\CreateComponent::class)->name('teacher.grievances.create');

    // Profile
    Route::get('teacher/profile/overview', \App\Livewire\Teacher\Profile\OverviewComponent::class)->name('teacher.profile.overview');
    Route::get('teacher/profile/setting', \App\Livewire\Teacher\Profile\SettingComponent::class)->name('teacher.profile.setting');
    Route::get('teacher/profile/activitylog', \App\Livewire\Teacher\Profile\ActivityLogComponent::class)->name('teacher.profile.activitylog');
    Route::get('teacher/profile/loginlog', \App\Livewire\Teacher\Profile\LoginlogComponent::class)->name('teacher.profile.loginlog');
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
    Route::get('accountant/student/{id}/payment', \App\Livewire\Accountant\Student\StudentPaymentComponent::class)->name('accountant.student.payment');

    // Parent
    Route::get('accountant/parent/list', \App\Livewire\Accountant\Parent\ParentListComponent::class)->name('accountant.parent.list');
    Route::get('accountant/parent/add', \App\Livewire\Accountant\Parent\ParentAddComponent::class)->name('accountant.parent.add');
    Route::get('accountant/parent/edit/{id}', \App\Livewire\Accountant\Parent\ParentEditComponent::class)->name('accountant.parent.edit');
    Route::get('accountant/parent/{id}/overview', \App\Livewire\Accountant\Parent\ParentOverviewComponent::class)->name('accountant.parent.overview');
    Route::get('accountant/parent/{id}/child', \App\Livewire\Accountant\Parent\ParentChildComponent::class)->name('accountant.parent.child');

    // Employee 
    Route::get('accountant/employee/list', \App\Livewire\Accountant\Employee\EmployeeListComponent::class)->name('accountant.employee.list');
    Route::get('accountant/employee/add', \App\Livewire\Accountant\Employee\EmployeeAddComponent::class)->name('accountant.employee.add');
    Route::get('accountant/employee/{id}/edit', \App\Livewire\Accountant\Employee\EmployeeEditComponent::class)->name('accountant.employee.edit');
    Route::get('accountant/employee/{id}/view', \App\Livewire\Accountant\Employee\EmployeeViewComponent::class)->name('accountant.employee.view');

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
    // Notice
    Route::get('accountant/notices', \App\Livewire\Accountant\Notice\NoticeComponent::class)->name('accountant.notices');

    // Notification
    Route::get('accountant/notifications', \App\Livewire\Accountant\Notifications\Index::class)->name('accountant.notifications.index');

    // Profile 
    Route::get('accountant/profile/overview', \App\Livewire\Accountant\Profile\OverviewComponent::class)->name('accountant.profile.overview');
    Route::get('accountant/profile/setting', \App\Livewire\Accountant\Profile\SettingComponent::class)->name('accountant.profile.setting');
    Route::get('accountant/profile/activitylog', \App\Livewire\Accountant\Profile\ActivityLogComponent::class)->name('accountant.profile.activitylog');
    Route::get('accountant/profile/loginlog', \App\Livewire\Accountant\Profile\LoginlogComponent::class)->name('accountant.profile.loginlog');

});

// Branch
Route::middleware(['auth', 'role:branch', 'billing.check', 'permission.team'])->group(function () {
    Route::get('branch/dashboard', \App\Livewire\Branch\DashboardComponent::class)->name('branch.dashboard');

    // Inventory
    Route::get('/branch/inventory/units', \App\Livewire\Branch\Inventory\UnitComponent::class)->name('branch.inventory.units');
    Route::get('/branch/inventory/categories', \App\Livewire\Branch\Inventory\CategoryComponent::class)->name('branch.inventory.categories');
    Route::get('/branch/inventory/stores', \App\Livewire\Branch\Inventory\StoreComponent::class)->name('branch.inventory.stores');
    Route::get('/branch/inventory/suppliers', \App\Livewire\Branch\Inventory\SupplierComponent::class)->name('branch.inventory.suppliers');
    Route::get('/branch/inventory/products', \App\Livewire\Branch\Inventory\ProductComponent::class)->name('branch.inventory.products');
    Route::get('/branch/inventory/purchase/list', \App\Livewire\Branch\Inventory\PurchaseListComponent::class)->name('branch.inventory.purchase.list');
    Route::get('/branch/inventory/purchase/add', \App\Livewire\Branch\Inventory\PurchaseAddComponent::class)->name('branch.inventory.purchase.add');
    Route::get('/branch/inventory/purchase/{id}/edit', \App\Livewire\Branch\Inventory\PurchaseEditComponent::class)->name('branch.inventory.purchase.edit');
    Route::get('/branch/inventory/sale/list', \App\Livewire\Branch\Inventory\SaleListComponent::class)->name('branch.inventory.sale.list');
    Route::get('/branch/inventory/sale/add', \App\Livewire\Branch\Inventory\SaleAddComponent::class)->name('branch.inventory.sale.add');
    Route::get('/branch/inventory/sale/{id}/edit', \App\Livewire\Branch\Inventory\SaleEditComponent::class)->name('branch.inventory.sale.edit');
    
    // Admission
    Route::get('branch/online-admission', \App\Livewire\Branch\Admission\OnlineComponent::class)->name('branch.admission.online');

    // Student
    Route::get('/branch/student/add', \App\Livewire\Branch\Student\StudentAddComponent::class)->name('branch.student.add');
    Route::get('/branch/student/list', \App\Livewire\Branch\Student\StudentListComponent::class)->name('branch.student.list');
    Route::get('/branch/student/{id}/edit', \App\Livewire\Branch\Student\StudentEditComponent::class)->name('branch.student.edit');
    Route::get('/branch/student/{id}/overview', \App\Livewire\Branch\Student\OverviewComponent::class)->name('branch.student.overview');
    Route::get('/branch/student/{id}/invoice', \App\Livewire\Branch\Student\InvoiceComponent::class)->name('branch.student.invoice');
    Route::get('/branch/student/payment-collect/{invoice}', \App\Livewire\Branch\Student\PaymentCollectComponent::class)->name('branch.students.payment-collect');
    Route::get('/branch/student/{id}/account', \App\Livewire\Branch\Student\AccountComponent::class)->name('branch.student.account');
    Route::get('/branch/student/{id}/attendance', \App\Livewire\Branch\Student\AttendanceComponent::class)->name('branch.student.attendance');
    Route::get('/branch/student/{id}/enrollment', \App\Livewire\Branch\Student\EnrollmentComponent::class)->name('branch.student.enrollment');

    // // Parent
    Route::get('/branch/parent/list', \App\Livewire\Branch\Parent\ParentListComponent::class)->name('branch.parent.list');
    Route::get('/branch/parent/add', \App\Livewire\Branch\Parent\ParentAddComponent::class)->name('branch.parent.add');
    Route::get('/branch/parent/edit/{id}', \App\Livewire\Branch\Parent\ParentEditComponent::class)->name('branch.parent.edit');
    Route::get('/branch/parent/{id}/overview', \App\Livewire\Branch\Parent\ParentOverviewComponent::class)->name('branch.parent.overview');
    Route::get('/branch/parent/{id}/child', \App\Livewire\Branch\Parent\ParentChildComponent::class)->name('branch.parent.child');
    Route::get('/branch/parent/{id}/account', \App\Livewire\Branch\Parent\ParentAccountComponent::class)->name('branch.parent.account');

    // // Employee
    Route::get('/branch/employee/departments', \App\Livewire\Branch\Employee\DepartmentComponent::class)->name('branch.employee.departments');
    Route::get('/branch/employee/designations', \App\Livewire\Branch\Employee\DesignationComponent::class)->name('branch.employee.designations');
    Route::get('/branch/employee/list', \App\Livewire\Branch\Employee\EmployeeListComponent::class)->name('branch.employee.list');
    Route::get('/branch/employee/add', \App\Livewire\Branch\Employee\EmployeeAddComponent::class)->name('branch.employee.add');
    Route::get('/branch/employee/{id}/edit', \App\Livewire\Branch\Employee\EmployeeEditComponent::class)->name('branch.employee.edit');
    Route::get('/branch/employee/{id}/view', \App\Livewire\Branch\Employee\EmployeeViewComponent::class)->name('branch.employee.view');
    Route::get('/branch/employee/{id}/account', \App\Livewire\Branch\Employee\EmployeeAccountComponent::class)->name('branch.employee.account');
    Route::get('/branch/employee/{id}/invoices', \App\Livewire\Branch\Employee\EmployeeInvoiceComponent::class)->name('branch.employee.invoices');

    // Academic
    Route::get('/branch/academic/sessions', \App\Livewire\Branch\Academic\SessionComponent::class)->name('branch.academic.sessions');
    Route::get('/branch/academic/groups', \App\Livewire\Branch\Academic\GroupComponent::class)->name('branch.academic.groups');
    Route::get('/branch/academic/classes', \App\Livewire\Branch\Academic\ClassComponent::class)->name('branch.academic.classes');
    Route::get('/branch/academic/sections', \App\Livewire\Branch\Academic\SectionComponent::class)->name('branch.academic.sections');
    Route::get('/branch/academic/subjects', \App\Livewire\Branch\Academic\SubjectComponent::class)->name('branch.academic.subjects');
    Route::get('/branch/academic/class-assign', \App\Livewire\Branch\Academic\ClassAssignComponent::class)->name('branch.academic.class-assign');
    Route::get('/branch/academic/class-schedule/create', \App\Livewire\Branch\Academic\ClassScheduleCreateComponent::class)->name('branch.academic.class-schedule.create');
    Route::get('/branch/academic/class-schedule/list', \App\Livewire\Branch\Academic\ClassScheduleListComponent::class)->name('branch.academic.class-schedule.list');
    Route::get('/branch/academic/teacher-schedule', \App\Livewire\Branch\Academic\TeacherScheduleComponent::class)->name('branch.academic.teacher-schedule');
    Route::get('/branch/academic/substitute-teacher', \App\Livewire\Branch\Academic\SubstituteTeacherComponent::class)->name('branch.academic.substitute-teacher');
    Route::get('/branch/academic/student-promotion', \App\Livewire\Branch\Academic\StudentPromotionComponent::class)->name('branch.academic.student-promotion');
    Route::get('/branch/academic/student-enrollment', \App\Livewire\Branch\Academic\StudentEnrollmentComponent::class)->name('branch.academic.student-enrollment');

    // // Homework
    Route::get('/branch/homework/add', \App\Livewire\Branch\Homework\HomeworkAddComponent::class)->name('branch.homework.add');
    Route::get('/branch/homework/list', \App\Livewire\Branch\Homework\HomeworkListComponent::class)->name('branch.homework.list');
    Route::get('/branch/homework/edit/{id}', \App\Livewire\Branch\Homework\HomeworkEditComponent::class)->name('branch.homework.edit');

    // Exam
    Route::get('/branch/exam/terms', \App\Livewire\Branch\Exam\TermComponent::class)->name('branch.exam.terms');
    Route::get('/branch/exam/halls', \App\Livewire\Branch\Exam\HallComponent::class)->name('branch.exam.halls');
    Route::get('/branch/exam/marks', \App\Livewire\Branch\Exam\MarkComponent::class)->name('branch.exam.marks');    
    Route::get('/branch/exam/types', \App\Livewire\Branch\Exam\TypeComponent::class)->name('branch.exam.types');    
    Route::get('/branch/exam/setups', \App\Livewire\Branch\Exam\ExamSetupComponent::class)->name('branch.exam.setups');
    Route::get('/branch/exam/schedule/add', \App\Livewire\Branch\Exam\ScheduleAddComponent::class)->name('branch.exam.schedule.add');       
    Route::get('/branch/exam/schedule/list', \App\Livewire\Branch\Exam\ScheduleListComponent::class)->name('branch.exam.schedule.list');    
    Route::get('/branch/exam/entries', \App\Livewire\Branch\Exam\EntryComponent::class)->name('branch.exam.entries');    
    Route::get('/branch/exam/positions', \App\Livewire\Branch\Exam\PositionComponent::class)->name('branch.exam.positions');    
    Route::get('/branch/exam/grades', \App\Livewire\Branch\Exam\GradeComponent::class)->name('branch.exam.grades');

    // Attendance
    Route::get('/branch/attendance/students', \App\Livewire\Branch\Attendance\StudentComponent::class)->name('branch.attendance.students');
    Route::get('/branch/attendance/employees', \App\Livewire\Branch\Attendance\EmployeeComponent::class)->name('branch.attendance.employees');
    Route::get('/branch/attendance/exams', \App\Livewire\Branch\Attendance\ExamComponent::class)->name('branch.attendance.exams');
    Route::get('/branch/attendance/duty-assign', \App\Livewire\Branch\Attendance\AttendanceDutyAssignComponent::class)->name('branch.attendance.duty-assign');
    Route::get('/branch/attendance/exam-duty-assign', \App\Livewire\Branch\Attendance\ExamDutyAssignComponent::class)->name('branch.attendance.exam-duty-assign');
    
    // // Biometric
    Route::get('/branch/biometric/devices', \App\Livewire\Branch\Biometric\BiometricDeviceComponent::class)->name('branch.biometric.devices');
    Route::get('/branch/biometric/mapping/index', \App\Livewire\Branch\Biometric\IndexUserMappingComponent::class)->name('branch.biometric.mapping.index');
    Route::get('/branch/biometric/mapping/create', \App\Livewire\Branch\Biometric\CreateUserMappingComponent::class)->name('branch.biometric.mapping.create');
    Route::get('/branch/biometric/mapping/{id}/edit', \App\Livewire\Branch\Biometric\EditUserMappingComponent::class)->name('branch.biometric.mapping.edit');

    // StudentAccounting
    Route::get('branch/student-accounting/fee-types', \App\Livewire\Branch\StudentAccounting\FeeTypeComponent::class)->name('branch.student-accounting.fee.types');
    Route::get('branch/student-accounting/fee-setups', \App\Livewire\Branch\StudentAccounting\FeeSetupComponent::class)->name('branch.student-accounting.fee.setups');
    Route::get('branch/student-accounting/fee-fines', \App\Livewire\Branch\StudentAccounting\FeeFineComponent::class)->name('branch.student-accounting.fee.fines');
    Route::get('branch/student-accounting/student-fines', \App\Livewire\Branch\StudentAccounting\StudentFineComponent::class)->name('branch.student-accounting.student.fines');
    Route::get('branch/student-accounting/fee-invoices', \App\Livewire\Branch\StudentAccounting\FeeInvoiceComponent::class)->name('branch.student-accounting.fee.invoices');

    // OfficeAccounting
    Route::get('/branch/office-accounting/accounts', \App\Livewire\Branch\OfficeAccounting\AccountComponent::class)->name('branch.office-accounting.accounts');
    Route::get('/branch/office-accounting/voucher-head', \App\Livewire\Branch\OfficeAccounting\HeadComponent::class)->name('branch.office-accounting.heads');
    Route::get('/branch/office-accounting/deposit-add', \App\Livewire\Branch\OfficeAccounting\DepositAddComponent::class)->name('branch.office-accounting.deposit.add');
    Route::get('/branch/office-accounting/{id}/deposit-edit', \App\Livewire\Branch\OfficeAccounting\DepositEditComponent::class)->name('branch.office-accounting.deposit.edit');
    Route::get('/branch/office-accounting/deposit-list', \App\Livewire\Branch\OfficeAccounting\DepositListComponent::class)->name('branch.office-accounting.deposit.list');
    Route::get('/branch/office-accounting/expense-add', \App\Livewire\Branch\OfficeAccounting\ExpenseAddComponent::class)->name('branch.office-accounting.expense.add');
    Route::get('/branch/office-accounting/{id}/expense-edit', \App\Livewire\Branch\OfficeAccounting\ExpenseEditComponent::class)->name('branch.office-accounting.expense.edit');
    Route::get('/branch/office-accounting/expense-list', \App\Livewire\Branch\OfficeAccounting\ExpenseListComponent::class)->name('branch.office-accounting.expense.list');
    Route::get('/branch/office-accounting/transactions', \App\Livewire\Branch\OfficeAccounting\TransactionComponent::class)->name('branch.office-accounting.transactions');

    // Salary
    Route::get('/branch/salary/add-template', \App\Livewire\Branch\Salary\AddTemplateComponent::class)->name('branch.salary.add-template');
    Route::get('/branch/salary/{id}/edit-template', \App\Livewire\Branch\Salary\EditTemplateComponent::class)->name('branch.salary.edit-template');
    Route::get('/branch/salary/list-template', \App\Livewire\Branch\Salary\ListTemplateComponent::class)->name('branch.salary.list-template');
    Route::get('/branch/salary/assign', \App\Livewire\Branch\Salary\AssignComponent::class)->name('branch.salary.assign');
    Route::get('/branch/salary/{id}/{month}/add-payment', \App\Livewire\Branch\Salary\AddPaymentComponent::class)->name('branch.salary.add-payment');
    Route::get('/branch/salary/{id}/{month}/invoice-payment', \App\Livewire\Branch\Salary\InvoicePaymentComponent::class)->name('branch.salary.invoice-payment');
    Route::get('/branch/salary/payment', \App\Livewire\Branch\Salary\PaymentComponent::class)->name('branch.salary.payment');
    Route::get('/branch/salary/advance', \App\Livewire\Branch\Salary\AdvanceComponent::class)->name('branch.salary.advance');

    // // Card
    Route::get('/branch/card/id-card-templates', \App\Livewire\Branch\Card\IdCardTemplateComponent::class)->name('branch.card.id-card-templates');
    Route::get('/branch/card/student-id-cards', \App\Livewire\Branch\Card\StudentIdCardComponent::class)->name('branch.card.student-id-cards');
    Route::get('/branch/card/employee-id-cards', \App\Livewire\Branch\Card\EmployeeIdCardComponent::class)->name('branch.card.employee-id-cards');    
    Route::get('/branch/card/admit-card-templates', \App\Livewire\Branch\Card\AdmitCardTemplateComponent::class)->name('branch.card.admit-card-templates');
    Route::get('/branch/card/generate-admit-cards', \App\Livewire\Branch\Card\GenerateAdmitCardComponent::class)->name('branch.card.generate-admit-cards');

    // // Certificate
    Route::get('/branch/certificate/add-template', \App\Livewire\Branch\Certificate\AddTemplateComponent::class)->name('branch.certificate.add-template');
    Route::get('/branch/certificate/{id}/edit-template', \App\Livewire\Branch\Certificate\EditTemplateComponent::class)->name('branch.certificate.edit-template');
    Route::get('/branch/certificate/list-template', \App\Livewire\Branch\Certificate\ListTemplateComponent::class)->name('branch.certificate.list-template');
    Route::get('/branch/certificate/generate-student', \App\Livewire\Branch\Certificate\GenerateStudentComponent::class)->name('branch.certificate.generate-student');
    Route::get('/branch/certificate/generate-employee', \App\Livewire\Branch\Certificate\GenerateEmployeeComponent::class)->name('branch.certificate.generate-employee');

    // // Leave
    Route::get('/branch/leave/categories', \App\Livewire\Branch\Leave\CategoryComponent::class)->name('branch.leave.categories');
    Route::get('/branch/leave/applications', \App\Livewire\Branch\Leave\ApplicationComponent::class)->name('branch.leave.applications');

    // // Event
    Route::get('/branch/event/types', \App\Livewire\Branch\Event\TypeComponent::class)->name('branch.event.types');
    Route::get('/branch/event/add', \App\Livewire\Branch\Event\AddComponent::class)->name('branch.event.add');
    Route::get('/branch/events/{id}/edit', \App\Livewire\Branch\Event\EditComponent::class)->name('branch.event.edit');
    Route::get('/branch/event/list', \App\Livewire\Branch\Event\ListComponent::class)->name('branch.event.list');
    
    // // Mailbox
    Route::get('/branch/mailbox/compose', \App\Livewire\Branch\Mailbox\ComposeComponent::class)->name('branch.mailbox.compose');
    Route::get('/branch/mailbox/inbox', \App\Livewire\Branch\Mailbox\InboxComponent::class)->name('branch.mailbox.inbox');
    Route::get('/branch/mailbox/sent', \App\Livewire\Branch\Mailbox\SentComponent::class)->name('branch.mailbox.sent');
    Route::get('/branch/mailbox/important', \App\Livewire\Branch\Mailbox\ImportantComponent::class)->name('branch.mailbox.important');
    Route::get('/branch/mailbox/trash', \App\Livewire\Branch\Mailbox\TrashComponent::class)->name('branch.mailbox.trash');

    // Other
    Route::get('/branch/notices', \App\Livewire\Branch\Notice\NoticeComponent::class)->name('branch.notices');
    Route::get('/branch/notifications', \App\Livewire\Branch\Notifications\Index::class)->name('branch.notifications.index');

    // // Report
    Route::get('/branch/reports/attendances', \App\Livewire\Branch\Report\AttendanceReportComponent::class)->name('branch.reports.attendances');

    // Log
    Route::get('/branch/activity-logs', \App\Livewire\Branch\Log\ActivityLogComponent::class)->name('branch.activitylog');
    Route::get('/branch/session-logs', \App\Livewire\Branch\Log\SessionLogComponent::class)->name('branch.sessionlog');
    Route::get('/branch/login-logs', \App\Livewire\Branch\Log\LoginLogComponent::class)->name('branch.loginlog');

    Route::get('/branch/roles-permissions/index', \App\Livewire\Branch\RolePermission\IndexComponent::class)->name('branch.role-permission.index');
    Route::get('/branch/roles-permissions/create', \App\Livewire\Branch\RolePermission\CreateComponent::class)->name('branch.role-permission.create');
    Route::get('/branch/roles-permissions/{id}/edit', \App\Livewire\Branch\RolePermission\EditComponent::class)->name('branch.role-permission.edit');
    Route::get('/branch/roles-permissions/users', \App\Livewire\Branch\RolePermission\UserComponent::class)->name('branch.role-permission.users');

    // // Profile
    Route::get('branch/profile/overview', \App\Livewire\Branch\Profile\OverviewComponent::class)->name('branch.profile.overview');
    Route::get('branch/profile/setting', \App\Livewire\Branch\Profile\SettingComponent::class)->name('branch.profile.setting');
    Route::get('branch/profile/activitylog', \App\Livewire\Branch\Profile\ActivityLogComponent::class)->name('branch.profile.activitylog');
    Route::get('branch/profile/loginlog', \App\Livewire\Branch\Profile\LoginlogComponent::class)->name('branch.profile.loginlog');
});

// Admin
Route::middleware(['auth', 'role:admin', 'billing.check', 'setup.wizard', 'permission.team'])->group(function () {
    Route::get('admin/dashboard', \App\Livewire\Admin\DashboardComponent::class)->name('admin.dashboard');
    Route::get('admin/branches', \App\Livewire\Admin\Branch\IndexComponent::class)->name('admin.branches');

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
    
    // Admission
    Route::get('admin/online-admission', \App\Livewire\Admin\Admission\OnlineComponent::class)->name('admin.admission.online');

    // Student
    Route::get('/student/add', \App\Livewire\Admin\Student\StudentAddComponent::class)->name('admin.student.add');
    Route::get('/student/list', \App\Livewire\Admin\Student\StudentListComponent::class)->name('admin.student.list');
    Route::get('/student/{id}/edit', \App\Livewire\Admin\Student\StudentEditComponent::class)->name('admin.student.edit');
    Route::get('/student/{id}/overview', \App\Livewire\Admin\Student\OverviewComponent::class)->name('admin.student.overview');
    Route::get('/student/{id}/invoice', \App\Livewire\Admin\Student\InvoiceComponent::class)->name('admin.student.invoice');
    Route::get('/student/payment-collect/{invoice}', \App\Livewire\Admin\Student\PaymentCollectComponent::class)->name('admin.students.payment-collect');
    Route::get('/student/{id}/account', \App\Livewire\Admin\Student\AccountComponent::class)->name('admin.student.account');
    Route::get('/student/{id}/attendance', \App\Livewire\Admin\Student\AttendanceComponent::class)->name('admin.student.attendance');
    Route::get('/student/{id}/enrollment', \App\Livewire\Admin\Student\EnrollmentComponent::class)->name('admin.student.enrollment');

    // Academic
    Route::get('/academic/sessions', \App\Livewire\Admin\Academic\SessionComponent::class)->name('admin.academic.sessions');
    Route::get('/academic/groups', \App\Livewire\Admin\Academic\GroupComponent::class)->name('admin.academic.groups');
    Route::get('/academic/classes', \App\Livewire\Admin\Academic\ClassComponent::class)->name('admin.academic.classes');
    Route::get('/academic/sections', \App\Livewire\Admin\Academic\SectionComponent::class)->name('admin.academic.sections');
    Route::get('/academic/subjects', \App\Livewire\Admin\Academic\SubjectComponent::class)->name('admin.academic.subjects');
    Route::get('/academic/class-assign', \App\Livewire\Admin\Academic\ClassAssignComponent::class)->name('admin.academic.class-assign');
    Route::get('/academic/class-schedule/create', \App\Livewire\Admin\Academic\ClassScheduleCreateComponent::class)->name('admin.academic.class-schedule.create');
    Route::get('/academic/class-schedule/list', \App\Livewire\Admin\Academic\ClassScheduleListComponent::class)->name('admin.academic.class-schedule.list');
    Route::get('/academic/teacher-schedule', \App\Livewire\Admin\Academic\TeacherScheduleComponent::class)->name('admin.academic.teacher-schedule');
    Route::get('/academic/substitute-teacher', \App\Livewire\Admin\Academic\SubstituteTeacherComponent::class)->name('admin.academic.substitute-teacher');
    Route::get('/academic/student-promotion', \App\Livewire\Admin\Academic\StudentPromotionComponent::class)->name('admin.academic.student-promotion');
    Route::get('/academic/student-enrollment', \App\Livewire\Admin\Academic\StudentEnrollmentComponent::class)->name('admin.academic.student-enrollment');

    // Employee
    Route::get('/employee/departments', \App\Livewire\Admin\Employee\DepartmentComponent::class)->name('admin.employee.departments');
    Route::get('/employee/designations', \App\Livewire\Admin\Employee\DesignationComponent::class)->name('admin.employee.designations');
    Route::get('/employee/list', \App\Livewire\Admin\Employee\EmployeeListComponent::class)->name('admin.employee.list');
    Route::get('/employee/add', \App\Livewire\Admin\Employee\EmployeeAddComponent::class)->name('admin.employee.add');
    Route::get('/employee/{id}/edit', \App\Livewire\Admin\Employee\EmployeeEditComponent::class)->name('admin.employee.edit');
    Route::get('/employee/{id}/view', \App\Livewire\Admin\Employee\EmployeeViewComponent::class)->name('admin.employee.view');
    Route::get('/employee/{id}/account', \App\Livewire\Admin\Employee\EmployeeAccountComponent::class)->name('admin.employee.account');
    Route::get('/employee/{id}/invoices', \App\Livewire\Admin\Employee\EmployeeInvoiceComponent::class)->name('admin.employee.invoices');

    // Parent
    Route::get('/parent/list', \App\Livewire\Admin\Parent\ParentListComponent::class)->name('admin.parent.list');
    Route::get('/parent/add', \App\Livewire\Admin\Parent\ParentAddComponent::class)->name('admin.parent.add');
    Route::get('/parent/edit/{id}', \App\Livewire\Admin\Parent\ParentEditComponent::class)->name('admin.parent.edit');
    Route::get('/parent/{id}/overview', \App\Livewire\Admin\Parent\ParentOverviewComponent::class)->name('admin.parent.overview');
    Route::get('/parent/{id}/child', \App\Livewire\Admin\Parent\ParentChildComponent::class)->name('admin.parent.child');
    Route::get('/parent/{id}/account', \App\Livewire\Admin\Parent\ParentAccountComponent::class)->name('admin.parent.account');

    // Homework
    Route::get('/homework/add', \App\Livewire\Admin\Homework\HomeworkAddComponent::class)->name('admin.homework.add');
    Route::get('/homework/list', \App\Livewire\Admin\Homework\HomeworkListComponent::class)->name('admin.homework.list');
    Route::get('/homework/edit/{id}', \App\Livewire\Admin\Homework\HomeworkEditComponent::class)->name('admin.homework.edit');

    // Card
    Route::get('/card/id-card-templates', \App\Livewire\Admin\Card\IdCardTemplateComponent::class)->name('admin.card.id-card-templates');
    Route::get('/card/student-id-cards', \App\Livewire\Admin\Card\StudentIdCardComponent::class)->name('admin.card.student-id-cards');
    Route::get('/card/employee-id-cards', \App\Livewire\Admin\Card\EmployeeIdCardComponent::class)->name('admin.card.employee-id-cards');    
    Route::get('/card/admit-card-templates', \App\Livewire\Admin\Card\AdmitCardTemplateComponent::class)->name('admin.card.admit-card-templates');
    Route::get('/card/generate-admit-cards', \App\Livewire\Admin\Card\GenerateAdmitCardComponent::class)->name('admin.card.generate-admit-cards');

    // Certificate
    Route::get('certificate/add-template', \App\Livewire\Admin\Certificate\AddTemplateComponent::class)->name('admin.certificate.add-template');
    Route::get('certificate/{id}/edit-template', \App\Livewire\Admin\Certificate\EditTemplateComponent::class)->name('admin.certificate.edit-template');
    Route::get('certificate/list-template', \App\Livewire\Admin\Certificate\ListTemplateComponent::class)->name('admin.certificate.list-template');
    Route::get('certificate/generate-student', \App\Livewire\Admin\Certificate\GenerateStudentComponent::class)->name('admin.certificate.generate-student');
    Route::get('certificate/generate-employee', \App\Livewire\Admin\Certificate\GenerateEmployeeComponent::class)->name('admin.certificate.generate-employee');
    
    // Salary
    Route::get('salary/add-template', \App\Livewire\Admin\Salary\AddTemplateComponent::class)->name('admin.salary.add-template');
    Route::get('salary/{id}/edit-template', \App\Livewire\Admin\Salary\EditTemplateComponent::class)->name('admin.salary.edit-template');
    Route::get('salary/list-template', \App\Livewire\Admin\Salary\ListTemplateComponent::class)->name('admin.salary.list-template');
    Route::get('salary/assign', \App\Livewire\Admin\Salary\AssignComponent::class)->name('admin.salary.assign');
    Route::get('salary/{id}/{month}/add-payment', \App\Livewire\Admin\Salary\AddPaymentComponent::class)->name('admin.salary.add-payment');
    Route::get('salary/{id}/{month}/invoice-payment', \App\Livewire\Admin\Salary\InvoicePaymentComponent::class)->name('admin.salary.invoice-payment');
    Route::get('salary/payment', \App\Livewire\Admin\Salary\PaymentComponent::class)->name('admin.salary.payment');
    Route::get('salary/advance', \App\Livewire\Admin\Salary\AdvanceComponent::class)->name('admin.salary.advance');

    // Leave
    Route::get('leave/categories', \App\Livewire\Admin\Leave\CategoryComponent::class)->name('admin.leave.categories');
    Route::get('leave/applications', \App\Livewire\Admin\Leave\ApplicationComponent::class)->name('admin.leave.applications');
    
    // Exam
    Route::get('exam/terms', \App\Livewire\Admin\Exam\TermComponent::class)->name('admin.exam.terms');
    Route::get('exam/halls', \App\Livewire\Admin\Exam\HallComponent::class)->name('admin.exam.halls');
    Route::get('exam/marks', \App\Livewire\Admin\Exam\MarkComponent::class)->name('admin.exam.marks');    
    Route::get('exam/types', \App\Livewire\Admin\Exam\TypeComponent::class)->name('admin.exam.types');    
    Route::get('exam/setups', \App\Livewire\Admin\Exam\ExamSetupComponent::class)->name('admin.exam.setups');
    Route::get('exam/schedule/add', \App\Livewire\Admin\Exam\ScheduleAddComponent::class)->name('admin.exam.schedule.add');       
    Route::get('exam/schedule/list', \App\Livewire\Admin\Exam\ScheduleListComponent::class)->name('admin.exam.schedule.list');    
    Route::get('exam/entries', \App\Livewire\Admin\Exam\EntryComponent::class)->name('admin.exam.entries');    
    Route::get('exam/positions', \App\Livewire\Admin\Exam\PositionComponent::class)->name('admin.exam.positions');    
    Route::get('exam/grades', \App\Livewire\Admin\Exam\GradeComponent::class)->name('admin.exam.grades');

    // Attendance
    Route::get('attendance/students', \App\Livewire\Admin\Attendance\StudentComponent::class)->name('admin.attendance.students');
    Route::get('attendance/employees', \App\Livewire\Admin\Attendance\EmployeeComponent::class)->name('admin.attendance.employees');
    Route::get('attendance/exams', \App\Livewire\Admin\Attendance\ExamComponent::class)->name('admin.attendance.exams');
    Route::get('attendance/duty-assign', \App\Livewire\Admin\Attendance\AttendanceDutyAssignComponent::class)->name('admin.attendance.duty-assign');
    Route::get('attendance/exam-duty-assign', \App\Livewire\Admin\Attendance\ExamDutyAssignComponent::class)->name('admin.attendance.exam-duty-assign');
    
    // Biometric
    Route::get('/biometric/devices', \App\Livewire\Admin\Biometric\BiometricDeviceComponent::class)->name('admin.biometric.devices');
    Route::get('/biometric/mapping/index', \App\Livewire\Admin\Biometric\IndexUserMappingComponent::class)->name('admin.biometric.mapping.index');
    Route::get('/biometric/mapping/create', \App\Livewire\Admin\Biometric\CreateUserMappingComponent::class)->name('admin.biometric.mapping.create');
    Route::get('/biometric/mapping/{id}/edit', \App\Livewire\Admin\Biometric\EditUserMappingComponent::class)->name('admin.biometric.mapping.edit');

    // Event
    Route::get('event/types', \App\Livewire\Admin\Event\TypeComponent::class)->name('admin.event.types');
    Route::get('event/add', \App\Livewire\Admin\Event\AddComponent::class)->name('admin.event.add');
    Route::get('events/{id}/edit', \App\Livewire\Admin\Event\EditComponent::class)->name('admin.event.edit');
    Route::get('event/list', \App\Livewire\Admin\Event\ListComponent::class)->name('admin.event.list');
    
    // OfficeAccounting
    Route::get('office-accounting/accounts', \App\Livewire\Admin\OfficeAccounting\AccountComponent::class)->name('admin.office-accounting.accounts');
    Route::get('office-accounting/voucher-head', \App\Livewire\Admin\OfficeAccounting\HeadComponent::class)->name('admin.office-accounting.heads');
    Route::get('office-accounting/deposit-add', \App\Livewire\Admin\OfficeAccounting\DepositAddComponent::class)->name('admin.office-accounting.deposit.add');
    Route::get('office-accounting/{id}/deposit-edit', \App\Livewire\Admin\OfficeAccounting\DepositEditComponent::class)->name('admin.office-accounting.deposit.edit');
    Route::get('office-accounting/deposit-list', \App\Livewire\Admin\OfficeAccounting\DepositListComponent::class)->name('admin.office-accounting.deposit.list');
    Route::get('office-accounting/expense-add', \App\Livewire\Admin\OfficeAccounting\ExpenseAddComponent::class)->name('admin.office-accounting.expense.add');
    Route::get('office-accounting/{id}/expense-edit', \App\Livewire\Admin\OfficeAccounting\ExpenseEditComponent::class)->name('admin.office-accounting.expense.edit');
    Route::get('office-accounting/expense-list', \App\Livewire\Admin\OfficeAccounting\ExpenseListComponent::class)->name('admin.office-accounting.expense.list');
    Route::get('office-accounting/transactions', \App\Livewire\Admin\OfficeAccounting\TransactionComponent::class)->name('admin.office-accounting.transactions');

    // StudentAccounting
    Route::get('student-accounting/fee-types', \App\Livewire\Admin\StudentAccounting\FeeTypeComponent::class)->name('admin.student-accounting.fee.types');
    Route::get('student-accounting/fee-setups', \App\Livewire\Admin\StudentAccounting\FeeSetupComponent::class)->name('admin.student-accounting.fee.setups');
    Route::get('student-accounting/fee-fines', \App\Livewire\Admin\StudentAccounting\FeeFineComponent::class)->name('admin.student-accounting.fee.fines');
    Route::get('student-accounting/student-fines', \App\Livewire\Admin\StudentAccounting\StudentFineComponent::class)->name('admin.student-accounting.student.fines');
    Route::get('student-accounting/fee-invoices', \App\Livewire\Admin\StudentAccounting\FeeInvoiceComponent::class)->name('admin.student-accounting.fee.invoices');

    // Mailbox
    Route::get('mailbox/compose', \App\Livewire\Admin\Mailbox\ComposeComponent::class)->name('admin.mailbox.compose');
    Route::get('mailbox/inbox', \App\Livewire\Admin\Mailbox\InboxComponent::class)->name('admin.mailbox.inbox');
    Route::get('mailbox/sent', \App\Livewire\Admin\Mailbox\SentComponent::class)->name('admin.mailbox.sent');
    Route::get('mailbox/important', \App\Livewire\Admin\Mailbox\ImportantComponent::class)->name('admin.mailbox.important');
    Route::get('mailbox/trash', \App\Livewire\Admin\Mailbox\TrashComponent::class)->name('admin.mailbox.trash');

    // Log
    Route::get('/activity-logs', \App\Livewire\Admin\Log\ActivityLogComponent::class)->name('admin.activitylog');
    Route::get('/session-logs', \App\Livewire\Admin\Log\SessionLogComponent::class)->name('admin.sessionlog');
    Route::get('/login-logs', \App\Livewire\Admin\Log\LoginLogComponent::class)->name('admin.loginlog');

    // Other
    Route::get('notices', \App\Livewire\Admin\Notice\NoticeComponent::class)->name('admin.notices');
    Route::get('notifications', \App\Livewire\Admin\Notifications\Index::class)->name('admin.notifications.index');
    Route::get('roles-permissions/index', \App\Livewire\Admin\RolePermission\IndexComponent::class)->name('admin.role-permission.index');
    Route::get('roles-permissions/create', \App\Livewire\Admin\RolePermission\CreateComponent::class)->name('admin.role-permission.create');
    Route::get('roles-permissions/{id}/edit', \App\Livewire\Admin\RolePermission\EditComponent::class)->name('admin.role-permission.edit');
    Route::get('roles-permissions/users', \App\Livewire\Admin\RolePermission\UserComponent::class)->name('admin.role-permission.users');
    // Report
    Route::get('reports/attendances', \App\Livewire\Admin\Report\AttendanceReportComponent::class)->name('admin.reports.attendances');

    // Information
    Route::get('information/committees', \App\Livewire\Admin\Information\InstitutionCommitteeComponent::class)->name('admin.information.committees');
    Route::get('information/facilities', \App\Livewire\Admin\Information\InstitutionFacilityComponent::class)->name('admin.information.facilities');

    // Setting
    Route::get('setting/institution', \App\Livewire\Admin\Setting\InstitutionComponent::class)->name('admin.setting.institution');
    Route::get('setting/features', \App\Livewire\Admin\Setting\FeatureComponent::class)->name('admin.setting.features');

    // Profile
    Route::get('profile/overview', \App\Livewire\Admin\Profile\OverviewComponent::class)->name('admin.profile.overview');
    Route::get('profile/setting', \App\Livewire\Admin\Profile\SettingComponent::class)->name('admin.profile.setting');
    Route::get('profile/activitylog', \App\Livewire\Admin\Profile\ActivityLogComponent::class)->name('admin.profile.activitylog');
    Route::get('profile/loginlog', \App\Livewire\Admin\Profile\LoginlogComponent::class)->name('admin.profile.loginlog');
});
Route::middleware(['auth', 'role:admin', 'billing.check'])
    ->prefix('admin/setup-wizard')
    ->name('admin.setup-wizard.')
    ->group(function () {
        Route::get('/', \App\Livewire\Admin\SetupWizardComponent::class)->name('index');
    });

// Ministry
Route::middleware(['auth', 'role:ministry'])->prefix('ministry')->name('ministry.')->group(function () {
    Route::get('/dashboard',\App\Livewire\Ministry\DashboardComponent::class)->name('dashboard');

    // Institution
    Route::get('/institutions/index', \App\Livewire\Ministry\Institution\IndexComponent::class)->name('institutions.index');
    Route::get('/institutions/{institution}', \App\Livewire\Ministry\Institution\ShowComponent::class)->name('institutions.show');
    
    // Circular
    Route::get('/circulars/index', \App\Livewire\Ministry\Circular\IndexComponent::class)->name('circulars.index');
    Route::get('/circulars/create', \App\Livewire\Ministry\Circular\CreateComponent::class)->name('circulars.create');
    Route::get('/circulars/{circular}', \App\Livewire\Ministry\Circular\ShowComponent::class)->name('circulars.show');

    // Statistics
    Route::get('/statistics/students', \App\Livewire\Ministry\Statistics\StudentComponent::class)->name('statistics.students');
    Route::get('/statistics/teachers', \App\Livewire\Ministry\Statistics\TeacherComponent::class)->name('statistics.teachers');

    // Compliance & Inspection
    Route::get('/compliance/checklist', \App\Livewire\Ministry\Compliance\ChecklistComponent::class)->name('compliance.checklist');
    Route::get('/compliance/inspections/index', \App\Livewire\Ministry\Compliance\InspectionIndexComponent::class)->name('compliance.inspections.index');
    Route::get('/compliance/inspections/create', \App\Livewire\Ministry\Compliance\InspectionFormComponent::class)->name('compliance.inspections.create');
    Route::get('/compliance/inspections/{inspection}', \App\Livewire\Ministry\Compliance\InspectionShowComponent::class)->name('compliance.inspections.show');
    Route::get('/compliance/violations/index', \App\Livewire\Ministry\Compliance\ViolationIndexComponent::class)->name('compliance.violations.index');

    // Grievances    
    Route::get('/grievances/index', \App\Livewire\Ministry\Grievance\IndexComponent::class)->name('grievances.index');
    Route::get('/grievances/{grievance}', \App\Livewire\Ministry\Grievance\ShowComponent::class)->name('grievances.show');

    Route::get('/academic-performance', \App\Livewire\Ministry\Academic\PerformanceComponent::class)->name('academic.performance');
    Route::get('/ranking', \App\Livewire\Ministry\Ranking\IndexComponent::class)->name('ranking.index');
    Route::get('/geography/heatmap', \App\Livewire\Ministry\Geography\HeatmapComponent::class)->name('geography.heatmap');
    Route::get('/reports', \App\Livewire\Ministry\Reports\IndexComponent::class)->name('reports.index');
    Route::get('/users', \App\Livewire\Ministry\User\IndexComponent::class)->name('users.index');
    Route::get('/roles', \App\Livewire\Ministry\Role\IndexComponent::class)->name('roles.index');

    // Profile
    Route::get('/profile/overview', \App\Livewire\Ministry\Profile\OverviewComponent::class)->name('profile.overview');
    Route::get('/profile/setting', \App\Livewire\Ministry\Profile\SettingComponent::class)->name('profile.setting');
    Route::get('/profile/activitylog', \App\Livewire\Ministry\Profile\ActivityLogComponent::class)->name('profile.activitylog');
    Route::get('/profile/loginlog', \App\Livewire\Ministry\Profile\LoginlogComponent::class)->name('profile.loginlog');
});

// Super Admin
Route::middleware(['auth', 'role:super_admin'])->prefix('superadmin')->name('superadmin.')->group(function () {
    Route::get('/dashboard',\App\Livewire\SuperAdmin\DashboardComponent::class)->name('dashboard');
    Route::get('/institutions/index', \App\Livewire\SuperAdmin\Institution\InstitutionListComponent::class)->name('institutions.index');
    Route::get('/admins/index', \App\Livewire\SuperAdmin\Admin\AdminListComponent ::class)->name('admins.index');

    Route::get('/billings/invoices', \App\Livewire\SuperAdmin\Billing\InvoiceIndex::class)->name('invoices.index');

    // Log
    Route::get('/activity-logs', \App\Livewire\SuperAdmin\Log\ActivityLogComponent::class)->name('activitylog');
    Route::get('/session-logs', \App\Livewire\SuperAdmin\Log\SessionLogComponent::class)->name('sessionlog');
    Route::get('/login-logs', \App\Livewire\SuperAdmin\Log\LoginLogComponent::class)->name('loginlog');

    // Monitoring
    Route::get('/monitoring/server', \App\Livewire\SuperAdmin\Monitoring\ServerStatusComponent::class)->name('monitoring.server');
    Route::get('/monitoring/queue', \App\Livewire\SuperAdmin\Monitoring\QueueMonitorComponent::class)->name('monitoring.queue');
    Route::get('/monitoring/logs', \App\Livewire\SuperAdmin\Monitoring\ErrorLogsComponent::class)->name('monitoring.logs');
    Route::get('/monitoring/performance', \App\Livewire\SuperAdmin\Monitoring\PerformanceMetricsComponent::class)->name('monitoring.performance');
    
    // Other
    Route::get('/notifications', \App\Livewire\SuperAdmin\Notifications\Index::class)->name('notifications.index');
    Route::get('/backups', \App\Livewire\SuperAdmin\BackupComponent::class)->name('backups');
    Route::get('/settings', \App\Livewire\SuperAdmin\Settings\SystemSettingsComponent::class)->name('settings');
    Route::get('/pricingrates', \App\Livewire\SuperAdmin\Settings\PricingRateComponent::class)->name('pricingrates');

    Route::get('/biometric-devices', \App\Livewire\SuperAdmin\BiometricDeviceComponent::class)->name('biometric-devices');

    // Profile
    Route::get('/profile/overview', \App\Livewire\SuperAdmin\Profile\OverviewComponent::class)->name('profile.overview');
    Route::get('/profile/setting', \App\Livewire\SuperAdmin\Profile\SettingComponent::class)->name('profile.setting');
    Route::get('/profile/activitylog', \App\Livewire\SuperAdmin\Profile\ActivityLogComponent::class)->name('profile.activitylog');
    Route::get('/profile/loginlog', \App\Livewire\SuperAdmin\Profile\LoginlogComponent::class)->name('profile.loginlog');
});


// Biometric Attendance Device Routes
Route::match(['get', 'post'], '/iclock/cdata', [DeviceAttendanceController::class, 'cdata']);
Route::get('/iclock/getrequest', [DeviceAttendanceController::class, 'getRequest']);
Route::post('/iclock/devicecmd', [DeviceAttendanceController::class, 'deviceCmd']);


    Route::post('logout', function () {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect()->route('login');
    })->name('logout')->middleware('auth');

    Route::get('schedule', function () {
        Artisan::call('schedule:run');
        return redirect()->back()->with('success','Thanks for the generate schedule!');
    })->name('schedule')->middleware(['auth', 'role:super_admin']);

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
    })->middleware(['auth', 'role:super_admin']);

    use Illuminate\Support\Facades\Schedule;
    Schedule::command('homework:publish-scheduled')->everyMinute();

    Route::get('clear', function () {
        Artisan::call('optimize:clear');
        return redirect()->back()->with('success','Thanks for the fast site!');
    })->name('clear')->middleware(['auth', 'role:super_admin']);
    Route::get('migrate', function () {
        Artisan::call('migrate');
        return redirect()->back()->with('success','Thanks for the migration!');
    })->name('migrate')->middleware(['auth', 'role:super_admin']);
    Route::get('link', function () {
        Artisan::call('storage:link');
        return redirect()->back()->with('success','Thanks for the link storage!');
    })->name('link')->middleware(['auth', 'role:super_admin']);

    // Route::get('key', function () {
    //     Artisan::call('key:generate');
    //     return redirect()->back()->with('success','Thanks for the generate key!');
    // })->name('key');
    // Route::get('fresh', function () {
    //     Artisan::call('migrate:fresh --seed');
    // });
    // Route::get('permissionrefresh', function () {
    //     Artisan::call('migrate:refresh --path=/database/migrations/2024_01_15_210628_create_permission_tables.php');
    // });
    // Route::get('permissionreseed', function () {
    //     Artisan::call('db:seed --class=PermissionSeeder');
    // });
   // Route::get('try', function () {
    //     auth()->user()->sendEmailVerificationNotification();
    //     return redirect()->back()->with('success','Thanks for the fast site!');
    // })->name('try');



    

    