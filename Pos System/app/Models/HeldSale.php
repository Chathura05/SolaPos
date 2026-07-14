<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HeldSale extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference_note',
        'customer_id',
        'showroom_id',
        'user_id',
        'total_amount',
    ];

    public function items()
    {
        return $this->hasMany(HeldSaleItem::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
