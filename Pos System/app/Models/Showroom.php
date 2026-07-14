<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Showroom extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'location', 'phone'];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'showroom_product')
            ->withPivot('stock_quantity')
            ->withTimestamps();
    }

    public function returns()
    {
        return $this->hasMany(ShowroomReturn::class);
    }
}
