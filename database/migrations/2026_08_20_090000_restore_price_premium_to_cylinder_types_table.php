<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Restores the column dropped by
     * 2026_08_18_090000_drop_price_premium_from_cylinder_types_table.php, per
     * request. Added as a new migration rather than rolling back that one,
     * since it already ran in production and a straight re-add is simpler to
     * deploy safely than a rollback.
     */
    public function up(): void
    {
        Schema::table('cylinder_types', function (Blueprint $table) {
            $table->decimal('price_premium', 10, 2)->default(0)->after('capacity')
                ->comment('Reference value per type — e.g. added on top of gas sale price when pricing a cylinder');
        });
    }

    public function down(): void
    {
        Schema::table('cylinder_types', function (Blueprint $table) {
            $table->dropColumn('price_premium');
        });
    }
};
