<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'phone', 'email', 'address', 'city',
        'credit_limit', 'balance', 'loyalty_points', 'notes', 'is_active', 'showroom_id'
    ];

    public function showroom()
    {
        return $this->belongsTo(Showroom::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    protected $casts = [
        'is_active'    => 'boolean',
        'credit_limit' => 'decimal:2',
        'balance'      => 'decimal:2',
    ];

    public function totalPurchases(): float
    {
        // Use FK relationship if available, fallback to phone matching for historical data
        $fkTotal = (float) $this->sales()->sum('total_amount');
        if ($fkTotal > 0 || !$this->phone) {
            return $fkTotal;
        }
        return (float) Sale::where('customer_phone', $this->phone)->sum('total_amount');
    }
}
