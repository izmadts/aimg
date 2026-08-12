<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            
            // Transaction identification
            $table->string('transaction_no')->unique();
            $table->enum('transaction_type', [
                'income', 'expense', 'transfer', 'journal', 
                'sale', 'purchase', 'payment', 'receipt'
            ]);
            
            // Account references
            $table->foreignId('account_id')->constrained()->onDelete('restrict');
            $table->foreignId('opposite_account_id')->nullable()->constrained('accounts')->onDelete('set null');
            
            // Date
            $table->date('date');
            
            // Amounts
            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('credit', 15, 2)->default(0);
            
            // Description
            $table->string('description');
            $table->string('reference_no')->nullable();
            
            // Polymorphic reference (for linking to sales, purchases, etc.)
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            
            // Status
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            
            // Additional info
            $table->text('notes')->nullable();
            
            // Audit
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['account_id', 'date']);
            $table->index('transaction_type');
            $table->index('reference_type', 'reference_id');
            $table->index('status');
            $table->index('date');
            $table->index(['reference_type', 'reference_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};