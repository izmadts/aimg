<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gas_products', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Oxygen, Nitrogen, Argon, CO2
            $table->string('code')->unique(); // O2, N2, AR, CO2
            $table->string('uom'); // KG, Cubic Meter, Liters
            $table->decimal('purchase_price', 15, 2);
            $table->decimal('sale_price', 15, 2);
            $table->decimal('current_stock', 15, 2)->default(0)->comment('KG/CBM mein available gas');
            $table->decimal('minimum_stock_level', 15, 2)->default(0);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gas_products');
    }
};