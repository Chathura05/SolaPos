<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShowroomDispatchItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'showroom_dispatch_id',
        'product_id',
        'quantity',
    ];

    public function dispatch()
    {
        return $this->belongsTo(ShowroomDispatch::class, 'showroom_dispatch_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
