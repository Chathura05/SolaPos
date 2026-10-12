<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Sale;
use App\Models\StockMovement;
use App\Traits\StockTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaleRefundService
{
    use StockTrait;

    /** Refund final charged item values; null means return every remaining item. */
    public function refund(Sale $sale, ?array $requestedItems = null): array
    {
        return DB::transaction(function () use ($sale, $requestedItems) {
            $sale = Sale::query()->lockForUpdate()->findOrFail($sale->id);
            if (!in_array($sale->status, ['completed', 'partially_refunded'], true)) {
                throw ValidationException::withMessages(['items' => 'This sale has already been refunded.']);
            }

            $items = $sale->items()->orderBy('id')->lockForUpdate()->get();
            $quantities = $requestedItems === null
                ? $items->mapWithKeys(fn ($item) => [$item->id => $item->quantity - $item->refunded_quantity])->filter()->all()
                : collect($requestedItems)->pluck('qty', 'id')->all();

            if (!$quantities || count($quantities) !== count($requestedItems ?? $quantities)) {
                throw ValidationException::withMessages(['items' => 'Select distinct items to refund.']);
            }

            foreach ($quantities as $id => $quantity) {
                $item = $items->firstWhere('id', $id);
                if (!$item || $quantity < 1 || $quantity > $item->quantity - $item->refunded_quantity) {
                    throw ValidationException::withMessages(['items' => 'The selected quantity is not available to refund.']);
                }
            }

            // Allocate the final total, including discount, redemption and tax, in cents.
            // Cumulative rounding keeps all item allocations equal to the invoice total.
            $totalCents = $this->cents($sale->total_amount);
            $subtotalCents = $items->sum(fn ($item) => $this->cents($item->subtotal));
            $weightCents = 0;
            $allocatedCents = 0;
            $refundCents = 0;
            $previousReturnedSubtotal = $items->sum(fn ($item) => $this->cents($item->subtotal) * $item->refunded_quantity / $item->quantity);

            foreach ($items as $item) {
                $weightCents += $this->cents($item->subtotal);
                $cumulativeCents = $subtotalCents > 0 ? (int) round($totalCents * $weightCents / $subtotalCents) : 0;
                $lineCents = $cumulativeCents - $allocatedCents;
                $allocatedCents = $cumulativeCents;
                $quantity = (int) ($quantities[$item->id] ?? 0);
                if (!$quantity) {
                    continue;
                }

                $previousQuantity = (int) $item->refunded_quantity;
                $refundCents += (int) round($lineCents * ($previousQuantity + $quantity) / $item->quantity)
                    - (int) round($lineCents * $previousQuantity / $item->quantity);
                $item->update(['refunded_quantity' => $previousQuantity + $quantity]);

                // Hard-deleted historical products have no stock to restore.
                if ($item->product_id !== null) {
                    $currentStock = DB::table('showroom_product')
                        ->where('showroom_id', $sale->showroom_id)
                        ->where('product_id', $item->product_id)
                        ->lockForUpdate()->value('stock_quantity') ?? 0;
                    $stock = $this->upsertShowroomStock($sale->showroom_id, $item->product_id, $currentStock + $quantity);
                    StockMovement::create([
                        'product_id' => $item->product_id,
                        'user_id' => auth()->id(),
                        'showroom_id' => $sale->showroom_id,
                        'type' => 'return',
                        'quantity' => $quantity,
                        'before_quantity' => $stock['before'],
                        'after_quantity' => $stock['after'],
                        'reference' => 'Refund - ' . $sale->invoice_number,
                        'notes' => "Refunded {$quantity}x {$item->product_name}",
                    ]);
                }
            }

            $fullyRefunded = $items->every(fn ($item) => $item->refunded_quantity >= $item->quantity);
            $previousCents = $this->cents($sale->refunded_amount);
            $refundCents = min(max(0, $refundCents), max(0, $totalCents - $previousCents));
            $newCents = $previousCents + $refundCents;
            $debtCancelledCents = 0;

            $customer = $sale->customer_id
                ? Customer::withTrashed()->lockForUpdate()->find($sale->customer_id)
                : null;
            if ($customer) {
                $originalDebtCents = max(0, $totalCents - $this->cents($sale->paid_amount));
                $debtCancelledCents = min($refundCents, max(0, $originalDebtCents - $previousCents), max(0, $this->cents($customer->balance)));
                $customer->balance = ($this->cents($customer->balance) - $debtCancelledCents) / 100;

                // A sale paid entirely with points still needs proportional point reversals.
                $newReturnedSubtotal = $items->sum(fn ($item) => $this->cents($item->subtotal) * $item->refunded_quantity / $item->quantity);
                $previousFraction = $totalCents > 0 ? min(1, $previousCents / $totalCents)
                    : $previousReturnedSubtotal / max(1, $subtotalCents);
                $newFraction = $fullyRefunded ? 1 : ($totalCents > 0 ? min(1, $newCents / $totalCents)
                    : $newReturnedSubtotal / max(1, $subtotalCents));
                $earnedReversed = (int) round($sale->points_earned * $newFraction) - (int) round($sale->points_earned * $previousFraction);
                $redeemedRestored = (int) round($sale->points_redeemed * $newFraction) - (int) round($sale->points_redeemed * $previousFraction);
                $customer->loyalty_points = (int) $customer->loyalty_points - $earnedReversed + $redeemedRestored;
                $customer->save();
            }

            $sale->update([
                'refunded_amount' => $newCents / 100,
                'status' => $fullyRefunded ? 'refunded' : 'partially_refunded',
            ]);

            return [
                'refunded_amount' => $refundCents / 100,
                'balance_cancelled' => $debtCancelledCents / 100,
                'cash_refund' => ($refundCents - $debtCancelledCents) / 100,
            ];
        }, 3);
    }

    private function cents($amount): int
    {
        return (int) round((float) $amount * 100);
    }
}
