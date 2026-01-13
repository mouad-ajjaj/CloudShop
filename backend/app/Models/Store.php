<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'banner',
        'user_id'
    ];

    // --- RELATIONSHIPS ---

    // A Store belongs to a User (Owner)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // A Store has many Products
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}