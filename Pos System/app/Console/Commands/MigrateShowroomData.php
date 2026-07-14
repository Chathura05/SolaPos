<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MigrateShowroomData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:migrate-showroom-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrates existing global stock and sales to a default Main Showroom.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting showroom data migration...');

        // 1. Create Main Showroom
        $showroom = \App\Models\Showroom::firstOrCreate(
            ['name' => 'Main Showroom'],
            ['location' => 'Main Branch', 'phone' => '1234567890']
        );
        $this->info("Main Showroom ensured. ID: {$showroom->id}");

        // 2. Assign all existing users to the main showroom (if not already assigned)
        \App\Models\User::whereNull('showroom_id')->update(['showroom_id' => $showroom->id]);
        $this->info('Users assigned to main showroom.');

        // 3. Assign all existing sales to the main showroom
        \App\Models\Sale::whereNull('showroom_id')->update(['showroom_id' => $showroom->id]);
        $this->info('Sales assigned to main showroom.');

        // 4. Migrate stock quantities from `products` to `showroom_product` pivot table
        // We read from the DB facade since the model might not have stock_quantity if the migration runs after model update,
        // but if we are running this before dropping the column, it's fine.
        $products = \Illuminate\Support\Facades\DB::table('products')->get();
        $migratedCount = 0;

        foreach ($products as $product) {
            // Check if already in pivot
            $exists = \Illuminate\Support\Facades\DB::table('showroom_product')
                ->where('showroom_id', $showroom->id)
                ->where('product_id', $product->id)
                ->exists();

            if (!$exists) {
                // If stock_quantity property exists, use it, else default to 0
                $stock = isset($product->stock_quantity) ? $product->stock_quantity : 0;
                
                \Illuminate\Support\Facades\DB::table('showroom_product')->insert([
                    'showroom_id' => $showroom->id,
                    'product_id' => $product->id,
                    'stock_quantity' => $stock,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $migratedCount++;
            }
        }

        $this->info("Migrated stock for {$migratedCount} products to the Main Showroom.");
        $this->info('Migration complete!');
    }
}
