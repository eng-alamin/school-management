<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Inventory\UnitController;
use App\Http\Controllers\Api\Inventory\CategoryController;
use App\Http\Controllers\Api\Inventory\ProductController;
// use App\Http\Controllers\Api\Inventory\StoreController;
// use App\Http\Controllers\Api\Inventory\SupplierController;

// Public route
Route::post('login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('user', fn(Request $request) => $request->user());

    Route::prefix('inventory')->group(function () {
        Route::apiResource('units', UnitController::class);
        Route::apiResource('categories', CategoryController::class);

        Route::get('products/form-data', [ProductController::class, 'formData']);
        Route::apiResource('products', ProductController::class);
        
        // Route::apiResource('stores', StoreController::class);
        // Route::apiResource('suppliers', SupplierController::class);
    });
});