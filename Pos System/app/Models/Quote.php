<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quote extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function items()
    {
        return $this->hasMany(QuoteItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function showroom()
    {
        return $this->belongsTo(Showroom::class);
    }

    public static function generateQuoteNumber()
    {
        $prefix = 'QT-';
        $lastQuote = self::orderBy('id', 'desc')->first();
        if (!$lastQuote) {
            return $prefix . '000001';
        }
        $lastNumber = intval(substr($lastQuote->quote_number, 3));
        return $prefix . str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
    }
}
