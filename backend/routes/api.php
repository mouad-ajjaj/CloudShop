<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\AdminController;

/*
|--------------------------------------------------------------------------
| Public Routes (Open to everyone)
|--------------------------------------------------------------------------
*/

// 1. Authentication
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login'])->name('login');

// 2. Browsing
Route::get('/products', [ProductController::class, 'index']); 
Route::get('/products/{id}', [ProductController::class, 'show']); 
Route::get('/stores/{id}', [StoreController::class, 'show']); 


/*
|--------------------------------------------------------------------------
| Protected Routes (Requires Login Token)
|--------------------------------------------------------------------------
*/

Route::group(['middleware' => ['auth:sanctum']], function () {

    // 1. Session Management
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // 2. Orders (Customer Side)
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders', [OrderController::class, 'index']);

    // 3. Vendor Routes
    Route::prefix('vendor')->group(function () {
        Route::get('/stats', [VendorController::class, 'stats']);
        
        // Product Management
        Route::get('/products', [VendorController::class, 'products']);
        Route::post('/products', [ProductController::class, 'store']);
        Route::put('/products/{id}', [ProductController::class, 'update']);
        Route::delete('/products/{id}', [ProductController::class, 'destroy']);
        
        // Store Management
        Route::put('/store', [StoreController::class, 'update']);
        
        // Order Management
        Route::get('/orders/recent', [VendorController::class, 'recentOrders']);
        
        // *** THIS IS THE NEW ROUTE FOR UPDATING STATUS ***
        Route::put('/orders/{id}', [VendorController::class, 'updateOrder']); 
    });

    // 4. Admin Routes
    Route::prefix('admin')->group(function () {
        Route::get('/stats', [AdminController::class, 'stats']);
        Route::get('/users', [AdminController::class, 'users']);
        Route::delete('/users/{id}', [AdminController::class, 'deleteUser']);
        Route::get('/stores', [AdminController::class, 'stores']);
        Route::delete('/stores/{id}', [AdminController::class, 'deleteStore']);
        Route::get('/products', [AdminController::class, 'products']);
        Route::delete('/products/{id}', [AdminController::class, 'deleteProduct']);
        Route::get('/stores/top', [AdminController::class, 'topStores']);
    });
});