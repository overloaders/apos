<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockOpnameItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_opname_id',
        'product_id',
        'system_stock',
        'actual_stock',
        'difference',
        'unit_cost',
        'system_value',
        'actual_value',
        'difference_value',
        'notes',
    ];

    protected $casts = [
        'system_stock' => 'float',
        'actual_stock' => 'float',
        'difference' => 'float',
        'unit_cost' => 'decimal:2',
        'system_value' => 'decimal:2',
        'actual_value' => 'decimal:2',
        'difference_value' => 'decimal:2',
    ];

    public function stockOpname()
    {
        return $this->belongsTo(StockOpname::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
