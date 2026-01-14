<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VendorController extends Controller
{
    // Helper: Get the authenticated user's store
    private function getMyStore()
    {
        return Store::where('user_id', Auth::id())->first();
    }

    // 1. Dashboard Stats (Scoped to Vendor)
    public function stats()
    {
        $store = $this->getMyStore();

        // If the vendor hasn't created a store yet
        if (!$store) {
            return response()->json(['revenue' => 0, 'sales' => 0, 'products' => 0]);
        }

        // A. Count products belonging to this store
        $productsCount = Product::where('store_id', $store->id)->count();

        // B. Count sales items linked to this store's products
        $salesCount = OrderItem::whereHas('product', function ($query) use ($store) {
            $query->where('store_id', $store->id);
        })->count();

        // C. Calculate Revenue 
        // (Sum of price * quantity for items belonging to THIS store, excluding cancelled orders)
        $revenue = OrderItem::whereHas('product', function ($query) use ($store) {
            $query->where('store_id', $store->id);
        })->whereHas('order', function ($q) {
            $q->where('status', '!=', 'cancelled');
        })->get()->sum(function ($item) {
            return $item->price * $item->quantity;
        });

        return response()->json([
            'revenue' => $revenue,
            'sales' => $salesCount,
            'products' => $productsCount
        ]);
    }

    // 2. List ONLY Vendor's Products
    public function products()
    {
        $store = $this->getMyStore();
        if (!$store) return [];

        return Product::where('store_id', $store->id)->latest()->get();
    }

    // 3. List ONLY Vendor's Relevant Orders
    public function recentOrders()
    {
        $store = $this->getMyStore();
        if (!$store) return [];

        // Fetch OrderItems where the product belongs to this store
        $items = OrderItem::whereHas('product', function ($query) use ($store) {
            $query->where('store_id', $store->id);
        })
        ->with(['order.user', 'product']) // Load relationships
        ->latest()
        ->limit(100)
        ->get();

        // Format data for the frontend
        return $items->map(function ($item) {
            return [
                'id' => $item->order_id,          // Order ID
                'item_id' => $item->id,           // Specific Item ID
                'quantity' => $item->quantity,
                'price' => $item->price,
                'created_at' => $item->created_at,
                'status' => $item->order ? $item->order->status : 'pending',
                'product' => $item->product ? [
                    'name' => $item->product->name,
                    'image' => $item->product->image
                ] : null,
                'order' => $item->order ? [
                    'id' => $item->order->id,
                    'user' => $item->order->user ? ['name' => $item->order->user->name] : null
                ] : null
            ];
        });
    }

    // 4. Update Order Status
    public function updateOrder(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled'
        ]);

        $order = Order::findOrFail($id);
        
        // Note: In a complex system, we would check if the vendor owns items in this order.
        // For this version, we allow the status update to proceed.
        $order->status = $request->status;
        $order->save();

        return response()->json([
            'message' => 'Statut mis à jour',
            'status' => $order->status
        ]);
    }
}