<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::latest();
        if ($request->has('limit')) $query->limit($request->get('limit'));
        if ($request->has('search')) $query->where('name', 'like', '%' . $request->get('search') . '%');
        
        return $query->get();
    }

    public function show($id)
    {
        return Product::findOrFail($id);
    }

    // CREATE PRODUCT
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'price' => 'required|numeric',
            'stock' => 'required|integer',
            'category' => 'required|string',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048', 
            'images.*' => 'nullable|image|max:2048' // Validate array of images
        ]);

        $store = Store::firstOrCreate(
            ['user_id' => Auth::id()],
            ['name' => Auth::user()->name . "'s Store"]
        );

        // 1. Handle Main Image
        $mainImagePath = 'https://via.placeholder.com/200';
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            $mainImagePath = asset('storage/' . $path);
        }

        // 2. Handle Additional Images (Loop)
        $galleryPaths = [];
        if ($request->hasFile('images')) {
            foreach($request->file('images') as $img) {
                // Limit to 4 images max logic can be here or frontend
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
            'images' => $galleryPaths, // Save array
            'status' => 'active'
        ]);

        return response()->json($product, 201);
    }

    // UPDATE PRODUCT
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        if ($product->store->user_id !== Auth::id() && Auth::user()->role !== 'admin') {
             return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Handle Main Image Update
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            $product->image = asset('storage/' . $path);
        }

        // Handle Additional Images Update (Append or Replace logic)
        // Here we simplify: if new images are sent, we replace the gallery. 
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