<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemberCreditPayment extends Model
{
    protected $fillable = [
        'member_id', 'amount', 'payment_date', 'notes', 'user_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
