<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'phone',
        'email',
        'address',
        'gender',
        'birth_date',
        'membership_level',
        'points',
        'total_spent',
        'credit_limit',
        'outstanding_balance',
        'last_credit_at',
        'is_active',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'points' => 'float',
        'total_spent' => 'decimal:2',
        'credit_limit' => 'decimal:2',
        'outstanding_balance' => 'decimal:2',
        'last_credit_at' => 'date',
        'is_active' => 'boolean',
    ];

    public function getRemainingCreditAttribute(): float
    {
        return max(0, $this->credit_limit - $this->outstanding_balance);
    }

    public function isCreditEligible(float $amount): bool
    {
        return $this->remaining_credit >= $amount;
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeSearch($query, ?string $search)
    {
        if ($search) {
            return $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return $query;
    }

    public function scopeMembershipLevel($query, string $level)
    {
        return $query->where('membership_level', $level);
    }

    public function addPoints(int $points): void
    {
        $this->increment('points', $points);
    }

    public function deductPoints(int $points): void
    {
        $this->decrement('points', $points);
    }

    public function addSpent(float $amount): void
    {
        $this->increment('total_spent', $amount);
    }

    public function getDiscountPercent(): int
    {
        return match ($this->membership_level) {
            'platinum' => 10,
            'gold'     => 5,
            'silver'   => 2,
            default    => 0,
        };
    }

    public function getPointsValue(): int
    {
        return 100;
    }

    public function getMaxRedeemablePoints(): int
    {
        return (int) floor($this->points);
    }

    public function getLevelLabel(): string
    {
        return ucfirst($this->membership_level ?? 'bronze');
    }

    public function getPointsRupiahValue(): int
    {
        return $this->getPointsValue() * (int) $this->points;
    }
}
