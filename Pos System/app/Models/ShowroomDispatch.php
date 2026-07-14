<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShowroomDispatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference_number',
        'admin_id',
        'showroom_id',
        'status',
        'notes',
    ];

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function showroom()
    {
        return $this->belongsTo(Showroom::class);
    }

    public function items()
    {
        return $this->hasMany(ShowroomDispatchItem::class);
    }

    public static function generateReferenceNumber(): string
    {
        return \Illuminate\Support\Facades\DB::transaction(function () {
            $lastRef = self::lockForUpdate()
                ->orderByDesc('id')
                ->value('reference_number');

            if ($lastRef && preg_match('/DIS-(\d+)/', $lastRef, $matches)) {
                $nextNumber = (int) $matches[1] + 1;
            } else {
                $nextNumber = 1;
            }

            return 'DIS-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
        });
    }
}
