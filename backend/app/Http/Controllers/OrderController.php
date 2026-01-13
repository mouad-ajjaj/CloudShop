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
    // Checkout (Create Order)
    public function store(Request $request)
    {
        $request->validate([
            'cart' => 'required|array',
            'cart.*.id' => 'required|exists:products,id',
            'cart.*.quantity' => 'required|integer|min:1',
            'address' => 'required',
            'total' => 'required|numeric'
        ]);

        return DB::transaction(function () use ($request) {
            // 1. Create Order
            $order = Order::create([
                'user_id' => Auth::id(),
                'total_amount' => $request->total,
                'status' => 'pending',
                'address' => json_encode($request->address)
            ]);

            // 2. Create Order Items & Update Stock
            foreach ($request->cart as $item) {
                $product = Product::lockForUpdate()->find($item['id']);
                
                if ($product->stock < $item['quantity']) {
                    throw new \Exception("Product {$product->name} is out of stock.");
                }

                $product->decrement('stock', $item['quantity']);

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

    // List Customer Orders
    public function index()
    {
        return Order::with('items.product')->where('user_id', Auth::id())->latest()->get();
    }
}