<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShowroomReturnItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'showroom_return_id',
        'product_id',
        'quantity',
        'reason',
    ];

    public function showroomReturn()
    {
        return $this->belongsTo(ShowroomReturn::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
