<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cylinders', function (Blueprint $table) {
            if (!Schema::hasColumn('cylinders', 'stock_quantity')) {
                $table->integer('stock_quantity')->default(0)->after('current_gas_quantity');
            }
        });
    }

    public function down(): void
    {
        Schema::table('cylinders', function (Blueprint $table) {
            if (Schema::hasColumn('cylinders', 'stock_quantity')) {
                $table->dropColumn('stock_quantity');
            }
        });
    }
};