<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Store;
use App\Models\Product;
use App\Models\Order;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function stats()
    {
        return response()->json([
            'users' => User::count(),
            'stores' => Store::count(),
            'products' => Product::count(),
            'revenue' => Order::sum('total_amount') // Simplified global revenue
        ]);
    }

    // User Management
    public function users() { return User::all(); }
    public function deleteUser($id) { User::destroy($id); return response()->noContent(); }

    // Store Management
    public function stores() { return Store::with('user')->get(); }
    public function deleteStore($id) { Store::destroy($id); return response()->noContent(); }

    // Product Management
    public function products() { return Product::with('store')->get(); }
    public function deleteProduct($id) { Product::destroy($id); return response()->noContent(); }

    public function topStores()
    {
        // Simple top 5 stores by product count for now
        return Store::withCount('products')->orderBy('products_count', 'desc')->take(5)->get();
    }
}