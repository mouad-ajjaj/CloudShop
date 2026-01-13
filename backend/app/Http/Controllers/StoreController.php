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
}