<?php

namespace App\Http\Controllers;

use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StoreController extends Controller
{
    // 1. Get Authenticated Vendor's Store (Private)
    public function myStore()
    {
        $store = Store::where('user_id', Auth::id())->first();
        
        if (!$store) {
            return response()->json(['message' => 'Aucune boutique trouvée'], 404);
        }

        return response()->json($store);
    }

    // 2. Update (or Create) Store Settings (Private)
    public function update(Request $request)
    {
        // 1. Validate
        $request->validate([
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'layout' => 'nullable|string',
            'primary_color' => 'nullable|string',
            'featured_products' => 'nullable|array', // Allow array
            'banner' => 'nullable|image|max:2048',   // Validate image
        ]);

        // 2. Find or Create Store
        $store = Store::firstOrCreate(
            ['user_id' => Auth::id()],
            ['name' => 'My Store'] // Default name if creating new
        );

        // 3. Update Text Fields
        $data = $request->only(['name', 'description', 'layout', 'primary_color', 'featured_products']);
        $store->fill($data);

        // 4. Handle Banner Upload
        if ($request->hasFile('banner')) {
            // Delete old banner if exists (optional but good practice)
            // if($store->banner) Storage::disk('public')->delete(...) 

            $path = $request->file('banner')->store('banners', 'public');
            $store->banner = asset('storage/' . $path);
        }

        $store->save();

        return response()->json($store);
    }

    // 3. Create Store (Public/Admin use)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:stores',
            'description' => 'nullable|string',
        ]);

        $store = Store::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'user_id' => Auth::id(),
        ]);

        return response()->json($store, 201);
    }

    // 4. List All Stores (Public)
    public function index()
    {
        return Store::all();
    }

    // 5. Show Specific Store (Public)
    public function show($id)
    {
        return Store::with('products')->findOrFail($id);
    }
}