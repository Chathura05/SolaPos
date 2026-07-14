<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'user_id',
        'showroom_id',
        'customer_id',
        'customer_name',
        'customer_phone',
        'subtotal',
        'discount_type',
        'discount_value',
        'discount_amount',
        'tax_percent',
        'tax_amount',
        'total_amount',
        'paid_amount',
        'change_amount',
        'refunded_amount',
        'payment_method',
        'notes',
        'status',
        'points_earned',
        'points_redeemed',
    ];

    protected $casts = [
        'subtotal'         => 'decimal:2',
        'discount_amount'  => 'decimal:2',
        'tax_amount'       => 'decimal:2',
        'total_amount'     => 'decimal:2',
        'paid_amount'      => 'decimal:2',
        'change_amount'    => 'decimal:2',
        'refunded_amount'  => 'decimal:2',
    ];

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function showroom(): BelongsTo
    {
        return $this->belongsTo(Showroom::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    /** Generate next invoice number like INV-00001 (race-condition safe) */
    public static function generateInvoiceNumber(): string
    {
        return \Illuminate\Support\Facades\DB::transaction(function () {
            $lastInvoice = self::lockForUpdate()
                ->orderByDesc('id')
                ->value('invoice_number');

            if ($lastInvoice && preg_match('/INV-(\d+)/', $lastInvoice, $matches)) {
                $nextNumber = (int) $matches[1] + 1;
            } else {
                $nextNumber = 1;
            }

            return 'INV-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
        });
    }
}
