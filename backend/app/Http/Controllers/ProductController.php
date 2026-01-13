<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    // 1. List all products (Public)
    public function index()
    {
        return Product::all();
    }

    // 2. Show one product (Public)
    public function show($id)
    {
        return Product::findOrFail($id);
    }

    // 3. Create a product (Store Owner only)
    public function store(Request $request)
    {
        // A. Validate inputs
        $request->validate([
            'name' => 'required|string',
            'price' => 'required|numeric',
            'stock' => 'required|integer',
            'category' => 'required|string',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048', // 2MB Max
        ]);

        // B. Get (or Create) the Store for this User
        // This fixes the missing 'store_id' error
        $store = Store::firstOrCreate(
            ['user_id' => Auth::id()],
            ['name' => Auth::user()->name . "'s Store"]
        );

        // C. Handle Image Upload
        $imagePath = null;
        if ($request->hasFile('image')) {
            // Save to storage/app/public/products
            $path = $request->file('image')->store('products', 'public');
            // Generate URL: http://localhost:8000/storage/products/filename.jpg
            $imagePath = asset('storage/' . $path);
        }

        // D. Save to Database
        $product = Product::create([
            'store_id' => $store->id,
            'name' => $request->name,
            'price' => $request->price,
            'stock' => $request->stock,
            'category' => $request->category,
            'description' => $request->description,
            'image' => $imagePath ?? 'https://via.placeholder.com/200',
            'status' => 'active'
        ]);

        return response()->json($product, 201);
    }
}