<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductPrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'price_level',
        'price',
        'min_qty',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'min_qty' => 'float',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function scopeSearch($query, ?string $search)
    {
        if ($search) {
            return $query->where('price_level', 'like', "%{$search}%");
        }

        return $query;
    }

    public function scopeForProduct($query, int $productId)
    {
        return $query->where('product_id', $productId);
    }

    public function scopePriceLevel($query, string $level)
    {
        return $query->where('price_level', $level);
    }
}
