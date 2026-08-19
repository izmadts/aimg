<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Reparable and Scrap become real quantity pools (like filled_quantity),
     * not a whole-row status — a cylinder type can have some units in repair
     * or scrapped while the rest stay in normal filled/empty rotation. Both
     * are subsets of stock_quantity, so empty_quantity's derivation grows to
     * subtract them too (see Cylinder::getEmptyQuantityAttribute()).
     */
    public function up(): void
    {
        Schema::table('cylinders', function (Blueprint $table) {
            $table->integer('maintenance_quantity')->default(0)->after('filled_quantity')
                ->comment('Units currently out for repair, still owned');
            $table->integer('scrap_quantity')->default(0)->after('maintenance_quantity')
                ->comment('Units flagged as scrap, still owned until formally disposed');
        });
    }

    public function down(): void
    {
        Schema::table('cylinders', function (Blueprint $table) {
            $table->dropColumn(['maintenance_quantity', 'scrap_quantity']);
        });
    }
};
