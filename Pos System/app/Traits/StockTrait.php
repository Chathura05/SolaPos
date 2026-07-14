<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;

trait StockTrait
{
    /**
     * Build a showroom-aware stock subquery with parameterized bindings.
     * Returns [sql, bindings] to use with selectRaw / whereRaw.
     */
    protected function stockSubquery(?int $showroomId): array
    {
        if ($showroomId) {
            return [
                '(SELECT COALESCE(SUM(stock_quantity),0) FROM showroom_product WHERE product_id = products.id AND showroom_id = ?)',
                [$showroomId],
            ];
        }

        return [
            '(SELECT COALESCE(SUM(stock_quantity),0) FROM showroom_product WHERE product_id = products.id)',
            [],
        ];
    }

    /**
     * Upsert stock in showroom_product pivot table.
     * Returns ['before' => int, 'after' => int].
     */
    protected function upsertShowroomStock(int $showroomId, int $productId, int $newQuantity): array
    {
        $pivot = DB::table('showroom_product')
            ->where('showroom_id', $showroomId)
            ->where('product_id', $productId)
            ->lockForUpdate()
            ->first();

        $before = $pivot ? (int) $pivot->stock_quantity : 0;

        if ($pivot) {
            DB::table('showroom_product')
                ->where('showroom_id', $showroomId)
                ->where('product_id', $productId)
                ->update(['stock_quantity' => $newQuantity, 'updated_at' => now()]);
        } else {
            DB::table('showroom_product')->insert([
                'showroom_id'    => $showroomId,
                'product_id'     => $productId,
                'stock_quantity' => $newQuantity,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        }

        return ['before' => $before, 'after' => $newQuantity];
    }
}

