<?php

namespace App\Http\Controllers;

use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StoreController extends Controller
{
    // Create a new Store
    public function store(Request $request)
    {
        // 1. Validate the input
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:stores',
            'description' => 'nullable|string',
        ]);

        // 2. Create the store linked to the logged-in user
        $store = Store::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'user_id' => Auth::id(), // Get the ID of the user sending the request
        ]);

        return response()->json($store, 201);
    }

    // List all stores (Public)
    public function index()
    {
        return Store::all();
    }

    // Show specific store with its products (Public)
    public function show($id)
    {
        return Store::with('products')->findOrFail($id);
    }

    // Update Vendor's own store details (Vendor Only)
    public function update(Request $request)
    {
        // Find the store belonging to the currently logged-in user
        $store = Store::where('user_id', Auth::id())->firstOrFail();
        
        $request->validate([
            'name' => 'string|max:255',
            'description' => 'nullable|string'
        ]);

        $store->update($request->only(['name', 'description']));
        
        return response()->json($store);
    }
}