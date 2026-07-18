<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

// Public route
Route::post('login', [App\Http\Controllers\Api\AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [App\Http\Controllers\Api\AuthController::class, 'logout']);
    Route::get('user', fn (Request $request) => $request->user());

    Route::prefix('admin')->group(function () {
        Route::get('dashboard', [App\Http\Controllers\Api\Admin\DashboardController::class, 'stats']);

        Route::apiResource('inventory/units', App\Http\Controllers\Api\Admin\Inventory\UnitController::class);
        Route::apiResource('inventory/categories', App\Http\Controllers\Api\Admin\Inventory\CategoryController::class);
        Route::get('inventory/products/form-data', [App\Http\Controllers\Api\Admin\Inventory\ProductController::class, 'formData']);
        Route::apiResource('inventory/products', App\Http\Controllers\Api\Admin\Inventory\ProductController::class);
        Route::apiResource('inventory/stores', App\Http\Controllers\Api\Admin\Inventory\StoreController::class);
        Route::apiResource('inventory/suppliers', App\Http\Controllers\Api\Admin\Inventory\SupplierController::class);
        Route::get('inventory/purchases/form-data', [App\Http\Controllers\Api\Admin\Inventory\PurchaseController::class, 'formData']);
        Route::apiResource('inventory/purchases', App\Http\Controllers\Api\Admin\Inventory\PurchaseController::class);
        Route::get('inventory/sales/form-data', [App\Http\Controllers\Api\Admin\Inventory\SaleController::class, 'formData']);
        Route::apiResource('inventory/sales', App\Http\Controllers\Api\Admin\Inventory\SaleController::class);

        // Employee
        Route::apiResource('employee/departments', App\Http\Controllers\Api\Admin\Employee\DepartmentController::class);
        Route::apiResource('employee/designations', App\Http\Controllers\Api\Admin\Employee\DesignationController::class);
        Route::get('employee/form-data', [App\Http\Controllers\Api\Admin\Employee\EmployeeController::class, 'formData']);
        Route::apiResource('employee', App\Http\Controllers\Api\Admin\Employee\EmployeeController::class);

        // Academic
        Route::apiResource('academic/sessions', App\Http\Controllers\Api\Admin\Academic\SessionController::class);
        Route::apiResource('academic/groups', App\Http\Controllers\Api\Admin\Academic\GroupController::class);
        Route::apiResource('academic/subjects', App\Http\Controllers\Api\Admin\Academic\SubjectController::class);
        Route::apiResource('academic/sections', App\Http\Controllers\Api\Admin\Academic\SectionController::class);
        Route::apiResource('academic/classes', App\Http\Controllers\Api\Admin\Academic\ClassController::class);
        Route::get('academic/class-assigns/form-data', [App\Http\Controllers\Api\Admin\Academic\ClassAssignController::class, 'formData']);
        Route::apiResource('academic/class-assigns', App\Http\Controllers\Api\Admin\Academic\ClassAssignController::class);

        Route::get('academic/class-schedules/classes', [App\Http\Controllers\Api\Admin\Academic\ClassScheduleController::class, 'classes']);
        Route::get('academic/class-schedules/sections', [App\Http\Controllers\Api\Admin\Academic\ClassScheduleController::class, 'sections']);
        Route::get('academic/class-schedules/subjects', [App\Http\Controllers\Api\Admin\Academic\ClassScheduleController::class, 'subjectsAndTeachers']);
        Route::get('academic/class-schedules/week', [App\Http\Controllers\Api\Admin\Academic\ClassScheduleController::class, 'week']);
        Route::get('academic/class-schedules', [App\Http\Controllers\Api\Admin\Academic\ClassScheduleController::class, 'show']);
        Route::post('academic/class-schedules', [App\Http\Controllers\Api\Admin\Academic\ClassScheduleController::class, 'store']);

        Route::get('academic/teacher-schedules/teachers', [App\Http\Controllers\Api\Admin\Academic\TeacherScheduleController::class, 'teachers']);
        Route::get('academic/teacher-schedules', [App\Http\Controllers\Api\Admin\Academic\TeacherScheduleController::class, 'schedule']);

        // Parent
        Route::get('parents', [App\Http\Controllers\Api\Admin\Parent\ParentController::class, 'index']);
        Route::get('parents/{id}', [App\Http\Controllers\Api\Admin\Parent\ParentController::class, 'show']);
        Route::post('parents', [App\Http\Controllers\Api\Admin\Parent\ParentController::class, 'store']);
        Route::put('parents/{id}', [App\Http\Controllers\Api\Admin\Parent\ParentController::class, 'update']); // multipart + _method=PUT spoofing
        Route::delete('parents/{id}', [App\Http\Controllers\Api\Admin\Parent\ParentController::class, 'destroy']);

        // Student
        Route::get('students', [App\Http\Controllers\Api\Admin\Student\StudentController::class, 'index']);
        Route::get('students/{id}', [App\Http\Controllers\Api\Admin\Student\StudentController::class, 'show']);
        Route::post('students', [App\Http\Controllers\Api\Admin\Student\StudentController::class, 'store']);
        Route::put('students/{id}', [App\Http\Controllers\Api\Admin\Student\StudentController::class, 'update']);
        Route::delete('students/{id}', [App\Http\Controllers\Api\Admin\Student\StudentController::class, 'destroy']);

        // Homework
        Route::get('homeworks/form-data', [App\Http\Controllers\Api\Admin\Homework\HomeworkController::class, 'formData']);
        Route::apiResource('homeworks', App\Http\Controllers\Api\Admin\Homework\HomeworkController::class);

        // Event
        Route::get('events/form-data', [App\Http\Controllers\Api\Admin\Event\EventController::class, 'formData']);
        Route::apiResource('events', App\Http\Controllers\Api\Admin\Event\EventController::class);
    });
});