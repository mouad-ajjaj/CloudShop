<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VendorController extends Controller
{
    // 1. Dashboard Stats (Revenue, Counts)
    public function stats()
    {
        // Calculate total revenue (sum of all orders not cancelled)
        $revenue = Order::where('status', '!=', 'cancelled')->sum('total_amount');
        $sales = OrderItem::count();
        $products = Product::count();

        return response()->json([
            'revenue' => $revenue,
            'sales' => $sales,
            'products' => $products
        ]);
    }

    // 2. List All Products (For the "Mes Produits" Table)
    public function products()
    {
        return Product::latest()->get();
    }

    // 3. List Orders (For "Tableau de bord" and "Commandes")
    public function recentOrders()
    {
        // We get OrderItems to show exactly WHICH product was sold
        $items = OrderItem::with(['order.user', 'product'])
            ->latest()
            ->limit(100) // Get last 100 items
            ->get();

        // Transform the data so the Frontend works easily
        return $items->map(function ($item) {
            return [
                'id' => $item->order_id, // Use Order ID so update works
                'item_id' => $item->id,
                'quantity' => $item->quantity,
                'price' => $item->price,
                'created_at' => $item->created_at,
                // Status comes from the parent Order
                'status' => $item->order ? $item->order->status : 'pending',
                // Product details
                'product' => $item->product ? [
                    'name' => $item->product->name,
                    'image' => $item->product->image
                ] : null,
                // Order/User details
                'order' => $item->order ? [
                    'id' => $item->order->id,
                    'user' => $item->order->user ? ['name' => $item->order->user->name] : null
                ] : null
            ];
        });
    }

    // 4. Update Order Status (The new function for your button)
    public function updateOrder(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled'
        ]);

        // Find the Order by ID and update it
        $order = Order::findOrFail($id);
        $order->status = $request->status;
        $order->save();

        return response()->json([
            'message' => 'Statut mis à jour avec succès',
            'status' => $order->status
        ]);
    }
}