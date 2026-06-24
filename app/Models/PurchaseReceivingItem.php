<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseReceivingItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_receiving_id',
        'product_id',
        'quantity',
        'unit_price',
        'subtotal',
    ];

    protected $casts = [
        'quantity' => 'float',
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function purchaseReceiving()
    {
        return $this->belongsTo(PurchaseReceiving::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
