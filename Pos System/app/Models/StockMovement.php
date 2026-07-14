<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'user_id',
        'showroom_id',
        'type',
        'quantity',
        'before_quantity',
        'after_quantity',
        'reference',
        'notes',
    ];

    public function showroom(): BelongsTo
    {
        return $this->belongsTo(Showroom::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function typeBadge(): array
    {
        return match($this->type) {
            'stock_in'      => ['label' => 'Stock In',      'class' => 'bg-green-100 text-green-800'],
            'stock_out'     => ['label' => 'Stock Out',     'class' => 'bg-red-100 text-red-800'],
            'adjustment'    => ['label' => 'Adjustment',   'class' => 'bg-yellow-100 text-yellow-800'],
            'sale'          => ['label' => 'Sale',          'class' => 'bg-blue-100 text-blue-800'],
            'return'        => ['label' => 'Return',        'class' => 'bg-purple-100 text-purple-800'],
            'transfer_in'   => ['label' => 'Transfer In',  'class' => 'bg-teal-100 text-teal-800'],
            'transfer_out'  => ['label' => 'Transfer Out', 'class' => 'bg-orange-100 text-orange-800'],
            default         => ['label' => ucfirst($this->type), 'class' => 'bg-gray-100 text-gray-800'],
        };
    }
}
