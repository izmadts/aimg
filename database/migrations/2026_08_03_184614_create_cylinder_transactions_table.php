<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cylinder_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cylinder_id')->constrained()->onDelete('cascade');
            $table->foreignId('customer_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null')->comment('Issued/Returned by');
            $table->string('transaction_type');
            $table->date('transaction_date');
            $table->decimal('gas_quantity_at_transaction', 10, 2)->nullable()->comment('Gas on hand at time of transaction');
            $table->decimal('security_deposit_charged', 15, 2)->default(0);
            $table->decimal('security_deposit_refunded', 15, 2)->default(0);
            $table->decimal('damage_charge', 15, 2)->default(0);
            $table->text('remarks')->nullable();
            $table->string('reference_document')->nullable()->comment('Sale invoice no, Purchase invoice no');
            $table->timestamps();

            $table->index(['cylinder_id', 'transaction_date']);
            $table->index('customer_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cylinder_transactions');
    }
};
