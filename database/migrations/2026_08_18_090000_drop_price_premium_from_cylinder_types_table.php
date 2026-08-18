<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * price_premium only ever fed a client-side sale-price suggestion on the
     * Add/Edit Cylinder form (Cylinder::autoDetectSalePrice() that used it was
     * never actually called anywhere). Each cylinder already carries its own
     * real purchase_price/sale_price, so this was a second, unused notion of
     * "price" living on the type instead of the actual record.
     */
    public function up(): void
    {
        Schema::table('cylinder_types', function (Blueprint $table) {
            $table->dropColumn('price_premium');
        });
    }

    public function down(): void
    {
        Schema::table('cylinder_types', function (Blueprint $table) {
            $table->decimal('price_premium', 10, 2)->default(0)->after('capacity');
        });
    }
};
