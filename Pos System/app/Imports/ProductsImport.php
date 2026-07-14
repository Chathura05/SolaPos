<?php

namespace App\Imports;

use App\Models\Product;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\DB;

class ProductsImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        // Default to first showroom or admin's showroom
        $showroomId = auth()->user()->showroom_id ?? \App\Models\Showroom::first()->id ?? null;

        // Ensure a default category exists for imported products without one
        $defaultCategory = \App\Models\Category::firstOrCreate(
            ['name' => 'Uncategorized'],
            ['is_active' => true]
        );

        $imported = 0;
        $skipped = 0;

        foreach ($rows as $index => $row) {
            // Check if the row is entirely empty or if headers are malformed (e.g. wrong CSV delimiter)
            if (!isset($row['name']) && !isset($row['cost_price']) && !isset($row['selling_price'])) {
                continue;
            }

            if (empty($row['name']) || !isset($row['cost_price']) || !isset($row['selling_price']) || !isset($row['stock_quantity']) || empty($row['unit'])) {
                throw new \Exception("Row " . ($index + 2) . " is missing a required field. Ensure 'name', 'cost_price', 'selling_price', 'stock_quantity', and 'unit' are filled out and headers match the template.");
            }

            $barcode = !empty($row['barcode']) ? (string)$row['barcode'] : null;

            if ($barcode) {
                $existing = Product::where('barcode', $barcode)->first();
                if ($existing) {
                    $skipped++;
                    continue;
                }
            }

            $product = Product::create([
                'category_id'     => $defaultCategory->id,
                'sub_category_id' => null,
                'name'            => $row['name'],
                'barcode'         => $barcode,
                'description'     => $row['description'] ?? null,
                'cost_price'      => (float) $row['cost_price'],
                'selling_price'   => (float) $row['selling_price'],
                'reorder_level'   => isset($row['reorder_level']) ? (int) $row['reorder_level'] : 10,
                'unit'            => strtolower($row['unit']),
                'is_active'       => true,
            ]);

            if ($showroomId) {
                DB::table('showroom_product')->insert([
                    'showroom_id' => $showroomId,
                    'product_id' => $product->id,
                    'stock_quantity' => (int) $row['stock_quantity'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            $imported++;
        }

        if ($imported === 0 && $skipped > 0) {
            throw new \Exception("No products were imported. $skipped product(s) were skipped because their barcodes already exist in the system.");
        } elseif ($imported === 0) {
            throw new \Exception("No products were imported. The file appears to be empty or does not match the template format (check column headers or CSV delimiters).");
        }
        
        session()->flash('success', "Successfully imported $imported new product(s)." . ($skipped > 0 ? " ($skipped skipped due to duplicate barcodes)" : ''));
    }
}
