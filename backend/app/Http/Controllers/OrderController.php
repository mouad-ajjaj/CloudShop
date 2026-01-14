<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    // Create Order (Checkout)
    public function store(Request $request)
    {
        $request->validate([
            'cart' => 'required|array',
            'cart.*.id' => 'required|exists:products,id',
            'cart.*.quantity' => 'required|integer|min:1',
            'address' => 'required',
            'total' => 'required|numeric'
        ]);

        // Use Transaction for Data Integrity
        return DB::transaction(function () use ($request) {
            
            // 1. Create the Main Order
            $order = Order::create([
                'user_id' => Auth::id(),
                'total_amount' => $request->total,
                'status' => 'pending',
                'address' => is_array($request->address) ? json_encode($request->address) : $request->address
            ]);

            // 2. Loop through cart items
            foreach ($request->cart as $item) {
                // Lock the product row to prevent race conditions
                $product = Product::lockForUpdate()->find($item['id']);
                
                // Check Stock
                if ($product->stock < $item['quantity']) {
                    throw new \Exception("Le produit {$product->name} est en rupture de stock.");
                }

                // Decrement Stock
                $product->decrement('stock', $item['quantity']);

                // Create Order Item
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $product->price
                ]);
            }

            return response()->json($order, 201);
        });
    }

    // List Customer's own orders
    public function index()
    {
        return Order::where('user_id', Auth::id())
                    ->with('items.product')
                    ->latest()
                    ->get();
    }
}