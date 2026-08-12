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
            $table->string('cylinder_number')->unique()->comment('Unique barcode/QR code');
            $table->foreignId('gas_product_id')->constrained()->onDelete('restrict');
            $table->string('type'); // B-D, D-Type, Jumbo, Small
            $table->string('manufacturer')->nullable();
            $table->decimal('tare_weight', 10, 2)->comment('Khali cylinder ka weight (KG)');
            $table->decimal('capacity', 10, 2)->comment('Kitni gas capacity hai (KG/CBM)');
            $table->decimal('current_gas_quantity', 10, 2)->default(0)->comment('Is waqt kitni gas hai');
            $table->enum('status', [
                'in_house_empty',      // Ghar mein khali pada hai
                'in_house_filled',     // Ghar mein gas bhara hai
                'issued',              // Customer ke paas hai
                'sold',                // Permanent bech diya
                'under_maintenance',   // Repair/testing
                'scrapped'             // Kharab ho gaya
            ])->default('in_house_empty');
            $table->foreignId('current_customer_id')->nullable()->constrained('customers')->onDelete('set null');
            $table->date('last_hydro_test_date')->nullable()->comment('Last safety test');
            $table->date('next_hydro_test_date')->nullable();
            $table->decimal('purchase_price', 15, 2)->default(0);
            $table->date('purchase_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Indexes for faster queries
            $table->index(['status', 'current_customer_id']);
            $table->index('cylinder_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cylinders');
    }
};