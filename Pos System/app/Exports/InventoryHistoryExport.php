<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class InventoryHistoryExport implements FromCollection, WithHeadings, WithMapping
{
    protected $movements;

    public function __construct($movements)
    {
        $this->movements = $movements;
    }

    public function collection()
    {
        return $this->movements;
    }

    public function headings(): array
    {
        return [
            'Date',
            'Product',
            'Barcode',
            'Showroom',
            'Type',
            'Quantity Changed',
            'Before Quantity',
            'After Quantity',
            'User',
            'Reference',
            'Notes',
        ];
    }

    public function map($movement): array
    {
        return [
            $movement->created_at->format('Y-m-d H:i:s'),
            $movement->product->name ?? 'Unknown',
            $movement->product->barcode ?? 'N/A',
            $movement->showroom->name ?? 'Unknown Showroom',
            ucfirst(str_replace('_', ' ', $movement->type)),
            $movement->quantity,
            $movement->before_quantity,
            $movement->after_quantity,
            $movement->user->name ?? 'System',
            $movement->reference,
            $movement->notes,
        ];
    }
}
