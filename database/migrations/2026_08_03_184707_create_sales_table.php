<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_no')->unique();
            $table->foreignId('customer_id')->constrained()->onDelete('restrict');
            $table->date('date');
            $table->date('delivery_date')->nullable();
            $table->decimal('subtotal', 15, 2)->default(0)->comment('Gas total');
            $table->decimal('cylinder_deposit_total', 15, 2)->default(0)->comment('Security deposits for issued cylinders');
            $table->decimal('cylinder_sale_total', 15, 2)->default(0)->comment('Revenue from cylinders sold outright');
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('tax', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2)->default(0);
            $table->decimal('amount_paid', 15, 2)->default(0);
            $table->decimal('balance_due', 15, 2)->default(0);
            $table->enum('payment_method', ['cash', 'bank_transfer', 'cheque', 'online', 'credit'])->default('cash');
            $table->enum('payment_status', ['paid', 'partial', 'unpaid'])->default('unpaid');
            $table->enum('status', ['draft', 'confirmed', 'delivered', 'cancelled'])->default('draft');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index(['customer_id', 'date']);
            $table->index('invoice_no');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
