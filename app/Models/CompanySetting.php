<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanySetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_name',
        'phone',
        'email',
        'website',
        'address',
        'city',
        'province',
        'postal_code',
        'npwp',
        'fax',
        'tax_rate',
        'receipt_message',
        'receipt_header',
        'receipt_footer',
        'logo',
    ];

    public static function instance(): static
    {
        return static::firstOrCreate(['id' => 1]);
    }
}
