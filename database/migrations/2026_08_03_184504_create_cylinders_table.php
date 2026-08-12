<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cylinders', function (Blueprint $table) {
            $table->id();
            $table->string('cylinder_number')->unique()->comment('Cylinder type code, e.g. OX-D40');
            $table->foreignId('gas_product_id')->constrained()->onDelete('restrict');
            $table->string('type')->comment('Size/category, e.g. D-Type, Jumbo, Small');
            $table->string('manufacturer')->nullable();
            $table->decimal('tare_weight', 10, 2)->comment('Empty cylinder weight (KG)');
            $table->decimal('capacity', 10, 2)->comment('Gas capacity when full (KG/CBM)');
            $table->integer('stock_quantity')->default(0)->comment('Total units of this type owned');
            $table->integer('issued_quantity')->default(0)->comment('Units currently issued to customers');
            $table->enum('status', [
                'in_house',
                'partial_issued',
                'all_issued',
                'out_of_stock',
                'under_maintenance',
                'scrapped',
            ])->default('in_house');
            $table->decimal('purchase_price', 15, 2)->default(0);
            $table->decimal('sale_price', 15, 2)->nullable();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->onDelete('set null');
            $table->date('purchase_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('cylinder_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cylinders');
    }
};
