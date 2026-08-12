<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->string('purchase_invoice_no')->unique();
            $table->foreignId('supplier_id')->constrained()->onDelete('restrict');
            $table->date('date');
            $table->date('delivery_date')->nullable();
            
            // Purchase Type (Important for Gas Business)
            $table->enum('purchase_type', [
                'gas_only',           // Sirf gas khareedi (cylinder already thi supplier ke paas)
                'gas_with_cylinder',   // Gas + Naya cylinder (dono khareede)
                'cylinder_only',       // Sirf cylinder khareeda (empty)
                'exchange'             // Empty cylinder de kar filled wali li
            ])->default('gas_only');
            
            // Financial
            $table->decimal('subtotal', 15, 2)->default(0)->comment('Gas ka total');
            $table->decimal('cylinder_deposit_paid', 15, 2)->default(0)->comment('Supplier ko cylinder deposit diya');
            $table->decimal('cylinder_purchase_total', 15, 2)->default(0)->comment('Naye cylinders ki price');
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('tax', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2);
            $table->decimal('amount_paid', 15, 2)->default(0);
            $table->decimal('balance_due', 15, 2)->default(0);
            
            // Payment Status
            $table->enum('payment_status', ['paid', 'partial', 'unpaid'])->default('unpaid');
            
            // Purchase Status
            $table->enum('status', ['draft', 'confirmed', 'delivered', 'cancelled'])->default('draft');
            
            // Additional Info
            $table->string('reference_no')->nullable()->comment('Supplier invoice no');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['supplier_id', 'date']);
            $table->index('purchase_invoice_no');
            $table->index('status');
            $table->index('payment_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};