<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromoCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'type',
        'value',
        'min_spend',
        'max_uses',
        'uses_count',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function isValid($cartTotal = 0)
    {
        if (!$this->is_active) {
            return false;
        }
        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }
        if ($this->max_uses && $this->uses_count >= $this->max_uses) {
            return false;
        }
        if ($this->min_spend > 0 && $cartTotal < $this->min_spend) {
            return false;
        }
        return true;
    }
}
