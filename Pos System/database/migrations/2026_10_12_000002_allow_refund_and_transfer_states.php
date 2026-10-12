<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->changeValues('sales', 'status', ['completed', 'refunded', 'partially_refunded'], 'completed');
        $this->changeValues('stock_movements', 'type', ['stock_in', 'stock_out', 'adjustment', 'sale', 'return', 'transfer_in', 'transfer_out']);
    }

    public function down(): void
    {
        $this->changeValues('sales', 'status', ['completed', 'refunded'], 'completed');
        $this->changeValues('stock_movements', 'type', ['stock_in', 'stock_out', 'adjustment', 'sale', 'return']);
    }

    private function changeValues(string $table, string $column, array $values, ?string $default = null): void
    {
        if (DB::getDriverName() === 'pgsql') {
            // PostgreSQL implements Laravel enums as varchar columns with CHECK constraints.
            $constraint = $table . '_' . $column . '_check';
            $allowed = implode(', ', array_map(fn ($value) => "'" . $value . "'", $values));
            DB::statement('ALTER TABLE "' . $table . '" DROP CONSTRAINT IF EXISTS "' . $constraint . '"');
            DB::statement('ALTER TABLE "' . $table . '" ADD CONSTRAINT "' . $constraint . '" CHECK ("' . $column . '" IN (' . $allowed . '))');

            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($column, $values, $default) {
            $definition = $blueprint->enum($column, $values);
            if ($default !== null) {
                $definition->default($default);
            }
            $definition->change();
        });
    }
};
