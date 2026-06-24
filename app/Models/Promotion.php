<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'type',
        'value',
        'min_purchase',
        'buy_qty',
        'get_qty',
        'start_date',
        'end_date',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'min_purchase' => 'decimal:2',
        'buy_qty' => 'float',
        'get_qty' => 'float',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function products()
    {
        return $this->hasMany(PromotionProduct::class);
    }

    public function promotionProducts()
    {
        return $this->belongsToMany(Product::class, 'promotion_products');
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now());
    }

    public function scopeSearch($query, ?string $search)
    {
        if ($search) {
            return $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        return $query;
    }

    public function scopeType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function isCurrentlyActive(): bool
    {
        return $this->is_active
            && $this->start_date->lte(now())
            && $this->end_date->gte(now());
    }

    public function calculateDiscount(float $subtotal, int $quantity = 1): float
    {
        return match ($this->type) {
            'discount_percent' => $subtotal * ($this->value / 100),
            'discount_amount' => min($this->value, $subtotal),
            'buy_x_get_y' => $this->calculateBuyXGetY($subtotal, $quantity),
            'bundle' => $this->calculateBundle($subtotal, $quantity),
            'member_discount' => $subtotal * ($this->value / 100),
            default => 0,
        };
    }

    private function calculateBuyXGetY(float $subtotal, int $quantity): float
    {
        if ($quantity >= ($this->buy_qty + $this->get_qty)) {
            $sets = intdiv($quantity, ($this->buy_qty + $this->get_qty));
            $unitPrice = $subtotal / max($quantity, 1);
            return $sets * $this->get_qty * $unitPrice;
        }

        return 0;
    }

    private function calculateBundle(float $subtotal, int $quantity): float
    {
        if ($this->buy_qty > 0 && $quantity >= $this->buy_qty) {
            $unitPrice = $subtotal / max($quantity, 1);
            $discountedQty = intdiv($quantity, $this->buy_qty) * $this->get_qty;
            return min($discountedQty * $unitPrice, $subtotal);
        }

        return 0;
    }
}
