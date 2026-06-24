<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sale extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'cash_register_id',
        'shift_id',
        'member_id',
        'user_id',
        'sale_date',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'total',
        'amount_paid',
        'change_amount',
        'payment_method',
        'status',
        'notes',
        'points_earned',
        'points_redeemed',
        'points_discount',
        'member_discount',
        'gift_card_id',
        'gift_card_amount',
    ];

    protected $casts = [
        'sale_date' => 'date',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'change_amount' => 'decimal:2',
        'points_earned' => 'integer',
        'points_redeemed' => 'integer',
        'points_discount' => 'decimal:2',
        'member_discount' => 'decimal:2',
        'gift_card_amount' => 'decimal:2',
    ];

    public function cashRegister()
    {
        return $this->belongsTo(CashRegister::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function giftCard()
    {
        return $this->belongsTo(GiftCard::class);
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function payments()
    {
        return $this->morphMany(Payment::class, 'referenceable');
    }

    public function saleReturns()
    {
        return $this->hasMany(SaleReturn::class);
    }

    public function getTotalReturnedAttribute(): float
    {
        return $this->saleReturns()->sum('total_refund');
    }

    public function getRefundableAmountAttribute(): float
    {
        return max(0, $this->total - $this->total_returned);
    }

    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeSearch($query, ?string $search)
    {
        if ($search) {
            return $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        return $query;
    }

    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('sale_date', [$startDate, $endDate]);
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function isRefunded(): bool
    {
        return $this->status === 'refunded';
    }
}
