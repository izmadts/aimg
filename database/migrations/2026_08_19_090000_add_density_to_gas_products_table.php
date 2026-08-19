<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Lets a gas product's Cubic Meter stock be auto-converted to KG on
     * screen. Density is a property of the specific gas (Oxygen and
     * Nitrogen don't weigh the same per m3), so it's stored per product,
     * not hardcoded — the form pre-fills a sensible default for common
     * gases but it's always editable.
     */
    public function up(): void
    {
        Schema::table('gas_products', function (Blueprint $table) {
            $table->decimal('density_kg_per_m3', 10, 4)->nullable()->after('uom')
                ->comment('Weight in KG of 1 cubic meter of this gas, used to auto-show a KG equivalent for Cubic Meter stock');
        });
    }

    public function down(): void
    {
        Schema::table('gas_products', function (Blueprint $table) {
            $table->dropColumn('density_kg_per_m3');
        });
    }
};
