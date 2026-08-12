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

            $table->enum('purchase_type', [
                'gas_only',           // Gas only, supplier already had the cylinder
                'gas_with_cylinder',  // Gas + new cylinder, both purchased
                'cylinder_only',      // Empty cylinder only
                'exchange',           // Gave empty, received filled (own cylinder, gas refill cost only)
            ])->default('gas_only');

            $table->decimal('subtotal', 15, 2)->default(0)->comment('Gas total');
            $table->decimal('cylinder_total', 15, 2)->default(0)->comment('Cylinder purchase total');
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('tax', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2)->default(0);
            $table->decimal('amount_paid', 15, 2)->default(0);
            $table->decimal('balance_due', 15, 2)->default(0);

            $table->enum('payment_method', ['cash', 'bank_transfer', 'cheque', 'online', 'credit'])->default('cash');
            $table->enum('payment_status', ['paid', 'partial', 'unpaid'])->default('unpaid');
            $table->enum('status', ['draft', 'confirmed', 'delivered', 'cancelled'])->default('draft');

            $table->string('reference_no')->nullable()->comment('Supplier invoice no');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();

            $table->timestamps();

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
