<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    // Get reviews for a product
    public function index($productId)
    {
        $reviews = Review::where('product_id', $productId)->with('user')->latest()->get();
        return response()->json($reviews);
    }

    // Submit a review
    public function store(Request $request, $productId)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|max:1000'
        ]);

        // Optional: Check if user already reviewed this product
        $existing = Review::where('user_id', Auth::id())->where('product_id', $productId)->first();
        if ($existing) {
            return response()->json(['message' => 'Vous avez déjà noté ce produit.'], 400);
        }

        $review = Review::create([
            'user_id' => Auth::id(),
            'product_id' => $productId,
            'rating' => $request->rating,
            'comment' => $request->comment
        ]);

        return response()->json($review->load('user'), 201);
    }
}