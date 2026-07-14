<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'company_name', 'phone', 'email', 'address',
        'city', 'tax_number', 'payable_balance', 'notes', 'is_active',
    ];

    protected $casts = [
        'is_active'       => 'boolean',
        'payable_balance' => 'decimal:2',
    ];
}
