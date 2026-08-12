<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->string('entry_no')->unique();
            $table->date('date');
            $table->string('description');
            
            // Foreign keys
            $table->foreignId('sale_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('purchase_id')->nullable()->constrained()->onDelete('set null');
            
            // Type
            $table->enum('type', ['sale', 'purchase', 'expense', 'deposit', 'refund'])->default('sale');
            
            // Amounts
            $table->decimal('total_debit', 15, 2)->default(0);
            $table->decimal('total_credit', 15, 2)->default(0);
            
            // Entries (JSON)
            $table->json('entries')->comment('[{account: "Cash", debit: 100, credit: 0}]');
            
            // Status
            $table->enum('status', ['draft', 'pending', 'approved', 'rejected'])->default('pending');
            
            // Reference
            $table->string('reference_no')->nullable();
            $table->text('notes')->nullable();
            
            // Audit
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['date', 'type']);
            $table->index('entry_no');
            $table->index('status');
            $table->index(['sale_id', 'purchase_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_entries');
    }
};