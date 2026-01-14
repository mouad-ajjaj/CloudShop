<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ReviewController; // Added ReviewController

/*
|--------------------------------------------------------------------------
| Public Routes (Open to everyone)
|--------------------------------------------------------------------------
*/

// 1. Authentication
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login'])->name('login');

// 2. Browsing Products
Route::get('/products', [ProductController::class, 'index']); 
Route::get('/products/{id}', [ProductController::class, 'show']); 

// 3. Browsing Stores
Route::get('/stores', [StoreController::class, 'index']); 
Route::get('/stores/{id}', [StoreController::class, 'show']); 

// 4. Reviews (Read-only)
Route::get('/products/{id}/reviews', [ReviewController::class, 'index']);


/*
|--------------------------------------------------------------------------
| Protected Routes (Requires Login Token)
|--------------------------------------------------------------------------
*/

Route::group(['middleware' => ['auth:sanctum']], function () {

    // 1. Common User Routes (Client, Vendor, Admin)
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // 2. Client Actions
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders', [OrderController::class, 'index']);
    
    // Reviews (Write)
    Route::post('/products/{id}/reviews', [ReviewController::class, 'store']);

    // 3. Vendor Routes (Protected: Only Store Owners)
    Route::prefix('vendor')->middleware('role:store_owner')->group(function () {
        Route::get('/stats', [VendorController::class, 'stats']);
        
        // Store Management
        Route::get('/store/me', [StoreController::class, 'myStore']); // Get current store info
        Route::put('/store', [StoreController::class, 'update']);     // Update store settings
        
        // Product Management
        Route::get('/products', [VendorController::class, 'products']);
        Route::post('/products', [ProductController::class, 'store']);
        Route::put('/products/{id}', [ProductController::class, 'update']);
        Route::delete('/products/{id}', [ProductController::class, 'destroy']);
        
        // Order Management
        Route::get('/orders/recent', [VendorController::class, 'recentOrders']);
        Route::put('/orders/{id}', [VendorController::class, 'updateOrder']); 
    });

    // 4. Admin Routes (LOCKED: Only Admins)
    Route::prefix('admin')->middleware('role:admin')->group(function () {
        // Dashboard Stats
        Route::get('/stats', [AdminController::class, 'stats']);
        
        // Users
        Route::get('/users', [AdminController::class, 'users']);
        Route::delete('/users/{id}', [AdminController::class, 'deleteUser']);
        
        // Stores
        Route::get('/stores', [AdminController::class, 'stores']);
        Route::delete('/stores/{id}', [AdminController::class, 'deleteStore']);
        Route::get('/stores/top', [AdminController::class, 'topStores']);
        
        // Products
        Route::get('/products', [AdminController::class, 'products']);
        Route::delete('/products/{id}', [AdminController::class, 'deleteProduct']);
    });
});