<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id',
        'sub_category_id',
        'name',
        'barcode',
        'description',
        'cost_price',
        'selling_price',
        'reorder_level',
        'unit',
        'image',
        'is_active',
    ];

    protected $casts = [
        'is_active'      => 'boolean',
        'cost_price'     => 'decimal:2',
        'selling_price'  => 'decimal:2',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function subCategory(): BelongsTo
    {
        return $this->belongsTo(SubCategory::class);
    }

    public function showrooms(): BelongsToMany
    {
        return $this->belongsToMany(Showroom::class, 'showroom_product')
            ->withPivot('stock_quantity')
            ->withTimestamps();
    }

    public function stockMovements(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function isLowStock(): bool
    {
        $stock = $this->total_stock ?? $this->showrooms()->sum('stock_quantity');
        return $stock <= $this->reorder_level;
    }
}
