<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'name',
        'price',
        'stock',
        'category',
        'description',
        'image',
        'images',
        'status'
    ];

    protected $casts = [
        'images' => 'array',
        'price' => 'decimal:2'
    ];

    // Link to Store
    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    // Link to Reviews (THIS WAS MISSING)
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}