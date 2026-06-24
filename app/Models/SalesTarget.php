<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesTarget extends Model
{
    protected $fillable = [
        'user_id', 'target_amount', 'period',
        'start_date', 'end_date', 'achieved_amount', 'notes',
    ];

    protected $casts = [
        'target_amount' => 'decimal:2',
        'achieved_amount' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getProgressAttribute(): float
    {
        return $this->target_amount > 0
            ? round(($this->achieved_amount / $this->target_amount) * 100, 2)
            : 0;
    }

    public function scopeActive($query)
    {
        return $query->where('start_date', '<=', now())
            ->where('end_date', '>=', now());
    }

    public function scopeByPeriod($query, string $period)
    {
        return $query->where('period', $period);
    }
}
