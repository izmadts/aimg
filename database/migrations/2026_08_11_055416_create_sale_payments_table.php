<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_payments', function (Blueprint $table) {
            $table->id();
            
            // Foreign key to sale
            $table->foreignId('sale_id')->constrained('sales')->onDelete('cascade');
            
            // Payment details
            $table->decimal('amount', 15, 2);
            $table->enum('payment_method', [
                'cash', 
                'bank_transfer', 
                'cheque', 
                'online', 
                'credit'
            ])->default('cash');
            
            $table->date('payment_date');
            $table->string('transaction_no')->unique();
            
            // Bank/Cheque details (optional)
            $table->string('bank_name')->nullable();
            $table->string('cheque_no')->nullable();
            $table->string('reference_no')->nullable();
            
            // Additional info
            $table->text('notes')->nullable();
            
            // Status
            $table->enum('status', ['pending', 'completed', 'failed', 'refunded'])->default('completed');
            
            // Audit
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
            
            // Indexes
            $table->index('sale_id');
            $table->index('payment_date');
            $table->index('status');
            $table->index('payment_method');
            $table->index('transaction_no');
            $table->index(['sale_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_payments');
    }
};