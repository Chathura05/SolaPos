<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('showroom_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('showroom_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->integer('stock_quantity')->default(0);
            $table->timestamps();
            
            $table->unique(['showroom_id', 'product_id']);
        });

        // Run the data migration command immediately so we migrate data
        // BEFORE the next migration drops the stock_quantity column.
        \Illuminate\Support\Facades\Artisan::call('app:migrate-showroom-data');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('showroom_product');
    }
};
