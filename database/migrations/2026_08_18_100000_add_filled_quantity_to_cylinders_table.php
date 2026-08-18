<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Splits "in warehouse, not issued" stock into filled (ready to
     * issue/sell) vs empty (needs a gas transfer before it can be sold).
     * empty_quantity is intentionally not a stored column — it's always
     * (stock_quantity - issued_quantity - filled_quantity), computed on the
     * model, so the two can never drift out of sync with each other.
     */
    public function up(): void
    {
        Schema::table('cylinders', function (Blueprint $table) {
            $table->unsignedInteger('filled_quantity')->default(0)->after('issued_quantity');
        });

        // Backfill: every cylinder that was previously counted as "available"
        // (stock - issued) was already being sold/issued as if it were ready,
        // so treat it as filled — otherwise every existing cylinder type would
        // suddenly show 0 available and block sales until someone runs a Gas
        // Transfer for stock that was already sellable. issued_quantity can
        // never exceed stock_quantity (enforced everywhere it changes), so a
        // plain subtraction is safe without a GREATEST()/MAX() (MySQL-only).
        DB::statement('UPDATE cylinders SET filled_quantity = stock_quantity - issued_quantity');
    }

    public function down(): void
    {
        Schema::table('cylinders', function (Blueprint $table) {
            $table->dropColumn('filled_quantity');
        });
    }
};
