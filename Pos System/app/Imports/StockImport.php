<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\StockMovement;
use App\Traits\StockTrait;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class StockImport implements ToCollection, WithHeadingRow
{
    use StockTrait;

    protected $showroomId;

    public function __construct($showroomId)
    {
        $this->showroomId = $showroomId;
    }

    public function collection(Collection $rows)
    {
        $imported = 0;
        $skipped = 0;
        
        DB::beginTransaction();

        try {
            // First, find all valid products to avoid empty dispatches
            $validItems = [];

            foreach ($rows as $index => $row) {
                if (!isset($row['barcode']) || !isset($row['quantity'])) {
                    continue; // Skip empty/invalid rows
                }

                $barcode = (string) $row['barcode'];
                $quantity = (int) $row['quantity'];

                if (empty($barcode) || $quantity <= 0) {
                    $skipped++;
                    continue;
                }

                $product = Product::where('barcode', $barcode)->first();
                
                if (!$product) {
                    $skipped++;
                    continue;
                }

                $validItems[] = [
                    'product_id' => $product->id,
                    'quantity'   => $quantity,
                ];
            }

            if (count($validItems) > 0) {
                // Create a pending Showroom Dispatch
                $dispatch = \App\Models\ShowroomDispatch::create([
                    'reference_number' => \App\Models\ShowroomDispatch::generateReferenceNumber(),
                    'admin_id'         => auth()->id(),
                    'showroom_id'      => $this->showroomId,
                    'status'           => 'pending',
                    'notes'            => 'Generated via Bulk Excel Import',
                ]);

                // Attach items
                foreach ($validItems as $item) {
                    \App\Models\ShowroomDispatchItem::create([
                        'showroom_dispatch_id' => $dispatch->id,
                        'product_id'           => $item['product_id'],
                        'quantity'             => $item['quantity'],
                    ]);
                    $imported++;
                }
            }

            DB::commit();

            if ($imported === 0 && $skipped > 0) {
                throw new \Exception("No dispatch was created. $skipped row(s) were skipped (either invalid quantities or barcodes not found).");
            } elseif ($imported === 0) {
                throw new \Exception("The uploaded file appears to be empty or does not match the template format (ensure columns are 'barcode' and 'quantity').");
            }

            session()->flash('success', "Successfully created a Pending Dispatch with $imported item(s). The showroom cashier must accept it to update stock." . ($skipped > 0 ? " ($skipped rows skipped)" : ''));

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
