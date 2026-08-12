<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained()->onDelete('cascade');
            $table->foreignId('gas_product_id')->constrained()->onDelete('restrict');
            $table->decimal('quantity', 10, 2);
            $table->decimal('price', 15, 2);
            $table->decimal('total', 15, 2);
            
            // Cylinder tracking (agar cylinder bhi khareeda toh)
            $table->foreignId('cylinder_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('cylinder_action', ['none', 'purchased', 'returned_to_supplier'])->default('none');
            
            // Quality tracking
            $table->string('batch_number')->nullable();
            $table->date('manufacturing_date')->nullable();
            $table->date('expiry_date')->nullable();
            
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_items');
    }
};