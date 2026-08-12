<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cylinders', function (Blueprint $table) {
            if (!Schema::hasColumn('cylinders', 'issued_quantity')) {
                $table->integer('issued_quantity')->default(0)->after('stock_quantity');
            }
            
            // Update status enum
            $table->enum('status', [
                'in_house',
                'partial_issued',
                'all_issued',
                'out_of_stock',
                'sold',
                'under_maintenance',
                'scrapped'
            ])->default('in_house')->change();
        });
    }

    public function down(): void
    {
        Schema::table('cylinders', function (Blueprint $table) {
            $table->dropColumn('issued_quantity');
            // Revert status enum if needed
        });
    }
};