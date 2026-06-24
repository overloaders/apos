<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GiftCard extends Model
{
    protected $fillable = [
        'code', 'initial_balance', 'current_balance',
        'expires_at', 'is_active', 'issued_by', 'notes',
    ];

    protected $casts = [
        'initial_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'expires_at' => 'date',
        'is_active' => 'boolean',
    ];

    public function issuer()
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
            });
    }

    public function scopeValid($query)
    {
        return $query->where('current_balance', '>', 0);
    }

    public function hasExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function redeem(float $amount): bool
    {
        if (!$this->is_active || $this->hasExpired() || $this->current_balance <= 0) {
            return false;
        }

        $deduct = min($amount, $this->current_balance);
        $this->current_balance -= $deduct;
        $this->save();

        return true;
    }
}
