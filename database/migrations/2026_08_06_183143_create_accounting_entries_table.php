<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounting_entries', function (Blueprint $table) {
            $table->id();
            
            // Entry identification. Not unique: every line of one posting
            // event (e.g. all legs of a single sale) shares the same entry_no.
            $table->string('entry_no');
            $table->date('date');
            $table->string('description');
            
            // Transaction type
            $table->enum('transaction_type', [
                'sale', 'purchase', 'expense', 'income', 
                'deposit', 'refund', 'salary', 'advance', 'damage'
            ])->default('sale');
            
            // Reference (polymorphic)
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            
            // Accounts
            $table->foreignId('account_id')->constrained('accounts')->onDelete('restrict');
            $table->foreignId('opposite_account_id')->nullable()->constrained('accounts')->onDelete('set null');
            
            // Amounts
            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('credit', 15, 2)->default(0);
            
            // Status
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            
            // Audit
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            
            // Additional info
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index('entry_no');
            $table->index(['account_id', 'date']);
            $table->index('transaction_type');
            $table->index('status');
            $table->index(['reference_type', 'reference_id']);
            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounting_entries');
    }
};