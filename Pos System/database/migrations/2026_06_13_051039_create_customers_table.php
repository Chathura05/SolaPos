<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->unique()->nullable();
            $table->string('email')->unique()->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->decimal('credit_limit', 12, 2)->default(0);
            $table->decimal('balance', 12, 2)->default(0); // outstanding balance
            $table->string('loyalty_points')->default(0);
            $table->string('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
