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

// --- CRITICAL FIX: Allow listing all stores so the Homepage works ---
Route::get('/stores', [StoreController::class, 'index']); 
// ------------------------------------------------------------------

Route::get('/stores/{id}', [StoreController::class, 'show']); 


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

    // 2. Client Orders
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders', [OrderController::class, 'index']);

    // 3. Vendor Routes (Protected: Only Store Owners)
    Route::prefix('vendor')->middleware('role:store_owner')->group(function () {
        Route::get('/stats', [VendorController::class, 'stats']);
        
        // --- Get "My Store" details (For Customization Page) ---
        Route::get('/store/me', [StoreController::class, 'myStore']);
        
        // Products
        Route::get('/products', [VendorController::class, 'products']);
        Route::post('/products', [ProductController::class, 'store']);
        Route::put('/products/{id}', [ProductController::class, 'update']);
        Route::delete('/products/{id}', [ProductController::class, 'destroy']);
        
        // Store Update
        Route::put('/store', [StoreController::class, 'update']);
        
        // Orders
        Route::get('/orders/recent', [VendorController::class, 'recentOrders']);
        Route::put('/orders/{id}', [VendorController::class, 'updateOrder']); 
    });

    // 4. Admin Routes (LOCKED: Only Admins)
    Route::prefix('admin')->middleware('role:admin')->group(function () {
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