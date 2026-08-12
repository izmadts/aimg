<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cylinders', function (Blueprint $table) {
            if (!Schema::hasColumn('cylinders', 'sale_price')) {
                $table->decimal('sale_price', 15, 2)->nullable()->after('purchase_price');
            }
        });
    }

    public function down(): void
    {
        Schema::table('cylinders', function (Blueprint $table) {
            if (Schema::hasColumn('cylinders', 'sale_price')) {
                $table->dropColumn('sale_price');
            }
        });
    }
};