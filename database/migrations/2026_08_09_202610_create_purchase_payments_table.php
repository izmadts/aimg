<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_payments', function (Blueprint $table) {
            $table->id();
            
            // Foreign key to purchase
            $table->foreignId('purchase_id')->constrained()->onDelete('cascade');
            
            // Payment details
            $table->decimal('amount', 15, 2);
            $table->enum('payment_method', ['cash', 'bank_transfer', 'cheque', 'online']);
            $table->date('payment_date');
            $table->string('transaction_no')->unique();
            
            // Additional info
            $table->text('notes')->nullable();
            
            // Status
            $table->enum('status', ['pending', 'completed', 'failed'])->default('completed');
            
            // Audit
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
            
            // Indexes
            $table->index('purchase_id');
            $table->index('payment_date');
            $table->index('status');
            $table->index('payment_method');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_payments');
    }
};