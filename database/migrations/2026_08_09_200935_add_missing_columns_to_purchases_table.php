<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            
            // ✅ Check and add gas columns
            if (!Schema::hasColumn('purchases', 'gas_product_id')) {
                $table->foreignId('gas_product_id')->nullable()->constrained('gas_products')->onDelete('set null');
            }
            if (!Schema::hasColumn('purchases', 'gas_quantity')) {
                $table->decimal('gas_quantity', 10, 2)->default(0);
            }
            if (!Schema::hasColumn('purchases', 'gas_price')) {
                $table->decimal('gas_price', 15, 2)->default(0);
            }
            if (!Schema::hasColumn('purchases', 'gas_total')) {
                $table->decimal('gas_total', 15, 2)->default(0);
            }

            // ✅ Check and add cylinder columns
            if (!Schema::hasColumn('purchases', 'cylinder_id')) {
                $table->foreignId('cylinder_id')->nullable()->constrained('cylinders')->onDelete('set null');
            }
            if (!Schema::hasColumn('purchases', 'cylinder_quantity')) {
                $table->integer('cylinder_quantity')->default(0);
            }
            if (!Schema::hasColumn('purchases', 'cylinder_purchase_price')) {
                $table->decimal('cylinder_purchase_price', 15, 2)->default(0);
            }
            if (!Schema::hasColumn('purchases', 'cylinder_sale_price')) {
                $table->decimal('cylinder_sale_price', 15, 2)->default(0);
            }
            if (!Schema::hasColumn('purchases', 'cylinder_total')) {
                $table->decimal('cylinder_total', 15, 2)->default(0);
            }

            // ✅ Check and add account columns
            if (!Schema::hasColumn('purchases', 'debit_account_id')) {
                $table->foreignId('debit_account_id')->nullable()->constrained('accounts')->onDelete('set null');
            }
            if (!Schema::hasColumn('purchases', 'credit_account_id')) {
                $table->foreignId('credit_account_id')->nullable()->constrained('accounts')->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $columns = [
                'gas_product_id', 'gas_quantity', 'gas_price', 'gas_total',
                'cylinder_id', 'cylinder_quantity', 'cylinder_purchase_price',
                'cylinder_sale_price', 'cylinder_total',
                'debit_account_id', 'credit_account_id'
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('purchases', $column)) {
                    if (in_array($column, ['gas_product_id', 'cylinder_id', 'debit_account_id', 'credit_account_id'])) {
                        $table->dropForeign([$column]);
                    }
                    $table->dropColumn($column);
                }
            }
        });
    }
};