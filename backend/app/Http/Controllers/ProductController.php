<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    // 1. LIST PRODUCTS (Public)
    public function index(Request $request)
    {
        // Load 'store' AND 'reviews' so frontend can calculate stars
        $query = Product::with(['store', 'reviews'])->latest();

        // Limit results
        if ($request->has('limit')) {
            $query->limit($request->get('limit'));
        }

        // Search
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->get('search') . '%');
        }

        // Category Filter
        if ($request->has('categories')) {
            $cats = explode(',', $request->get('categories'));
            $query->whereIn('category', $cats);
        }
        
        return $query->get();
    }

    // 2. SHOW SINGLE PRODUCT (Public)
    public function show($id)
    {
        // Get Product + Store + Reviews + User who wrote review
        return Product::with(['store', 'reviews.user'])->findOrFail($id);
    }

    // 3. CREATE PRODUCT (Vendor Only)
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'price' => 'required|numeric',
            'stock' => 'required|integer',
            'category' => 'required|string',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048', 
            'images.*' => 'nullable|image|max:2048'
        ]);

        $store = Store::firstOrCreate(
            ['user_id' => Auth::id()],
            ['name' => Auth::user()->name . "'s Store"]
        );

        $mainImagePath = null;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            $mainImagePath = asset('storage/' . $path);
        }

        $galleryPaths = [];
        if ($request->hasFile('images')) {
            foreach($request->file('images') as $img) {
                $path = $img->store('products', 'public');
                $galleryPaths[] = asset('storage/' . $path);
            }
        }

        $product = Product::create([
            'store_id' => $store->id,
            'name' => $request->name,
            'price' => $request->price,
            'stock' => $request->stock,
            'category' => $request->category,
            'description' => $request->description,
            'image' => $mainImagePath,
            'images' => $galleryPaths,
            'status' => 'active'
        ]);

        return response()->json($product, 201);
    }

    // 4. UPDATE PRODUCT (Vendor Only)
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        if ($product->store->user_id !== Auth::id() && Auth::user()->role !== 'admin') {
             return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            $product->image = asset('storage/' . $path);
        }

        if ($request->hasFile('images')) {
            $galleryPaths = [];
            foreach($request->file('images') as $img) {
                $path = $img->store('products', 'public');
                $galleryPaths[] = asset('storage/' . $path);
            }
            $product->images = $galleryPaths;
        }

        $product->update($request->except(['image', 'images']));
        $product->save();
        
        return response()->json($product);
    }

    // 5. DELETE PRODUCT (Vendor Only)
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        
        if ($product->store->user_id !== Auth::id() && Auth::user()->role !== 'admin') {
             return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $product->delete();
        return response()->noContent();
    }
}