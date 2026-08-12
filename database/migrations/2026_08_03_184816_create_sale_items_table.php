<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained()->onDelete('cascade');

            // Gas side of the line (nullable: a line can be cylinder-only)
            $table->foreignId('gas_product_id')->nullable()->constrained('gas_products')->onDelete('restrict');
            $table->decimal('gas_quantity', 10, 2)->nullable();
            $table->decimal('gas_price', 15, 2)->nullable();
            $table->decimal('gas_total', 15, 2)->default(0);

            // Cylinder side of the line (nullable: a line can be gas-only / refill)
            $table->foreignId('cylinder_id')->nullable()->constrained('cylinders')->onDelete('restrict');
            $table->enum('cylinder_action', ['issue', 'sell'])->nullable();
            $table->integer('cylinder_quantity')->nullable();
            $table->decimal('cylinder_unit_price', 15, 2)->nullable()->comment('Deposit per unit if issue, sale price per unit if sell');
            $table->decimal('cylinder_total', 15, 2)->default(0);

            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_items');
    }
};
