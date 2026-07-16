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

        Route::apiResource('employee/departments', App\Http\Controllers\Api\Admin\Employee\DepartmentController::class);
        Route::apiResource('employee/designations', App\Http\Controllers\Api\Admin\Employee\DesignationController::class);
        Route::get('employee/form-data', [App\Http\Controllers\Api\Admin\Employee\EmployeeController::class, 'formData']);
        Route::apiResource('employee', App\Http\Controllers\Api\Admin\Employee\EmployeeController::class);

    });
});