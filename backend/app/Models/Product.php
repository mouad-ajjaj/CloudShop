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
        'image',        // Main Image
        'images',       // New: Additional Images Array
        'status'
    ];

    // AUTOMATIC CASTING
    protected $casts = [
        'images' => 'array', // Convert JSON <-> Array automatically
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}