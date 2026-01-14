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
            'revenue' => Order::where('status', '!=', 'cancelled')->sum('total_amount'),
            'reports' => 0 // Placeholder for future feature
        ]);
    }

    // User Management (Added withCount for orders)
    public function users() { 
        return User::withCount('orders')->latest()->get(); 
    }
    
    public function deleteUser($id) { 
        User::destroy($id); 
        return response()->noContent(); 
    }

    // Store Management (Added withCount for products)
    public function stores() { 
        return Store::with('user')->withCount('products')->latest()->get(); 
    }
    
    public function deleteStore($id) { 
        Store::destroy($id); 
        return response()->noContent(); 
    }

    // Product Management (With Store Info)
    public function products() { 
        return Product::with('store')->latest()->get(); 
    }
    
    public function deleteProduct($id) { 
        Product::destroy($id); 
        return response()->noContent(); 
    }

    public function topStores()
    {
        return Store::withCount('products')
                    ->orderBy('products_count', 'desc')
                    ->take(5)
                    ->get();
    }
}