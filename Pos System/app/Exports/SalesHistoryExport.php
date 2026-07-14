<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SalesHistoryExport implements FromCollection, WithHeadings, WithMapping
{
    protected $sales;

    public function __construct($sales)
    {
        $this->sales = $sales;
    }

    public function collection()
    {
        return $this->sales;
    }

    public function headings(): array
    {
        return [
            'Invoice Number',
            'Date',
            'Customer Name',
            'Customer Phone',
            'Cashier',
            'Showroom',
            'Status',
            'Payment Method',
            'Subtotal',
            'Discount',
            'Tax',
            'Total Amount',
            'Refunded Amount',
        ];
    }

    public function map($sale): array
    {
        return [
            $sale->invoice_number,
            $sale->created_at->format('Y-m-d H:i:s'),
            $sale->customer_name ?? 'Walk-in Customer',
            $sale->customer_phone ?? 'N/A',
            $sale->cashier->name ?? 'Unknown',
            $sale->showroom->name ?? 'Unknown Showroom',
            ucfirst($sale->status),
            ucfirst($sale->payment_method),
            $sale->subtotal,
            $sale->discount_amount,
            $sale->tax_amount,
            $sale->total_amount,
            $sale->refunded_amount,
        ];
    }
}
