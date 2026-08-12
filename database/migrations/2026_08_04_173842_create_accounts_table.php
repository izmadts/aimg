<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            
            // Account identification
            $table->string('account_code')->unique();
            $table->string('account_name');
            
            // Account type: asset, liability, income, expense, equity
            $table->enum('account_type', ['asset', 'liability', 'income', 'expense', 'equity']);
            
            // Parent-child relationship (for hierarchical accounts)
            $table->foreignId('parent_id')->nullable()->constrained('accounts')->onDelete('set null');
            
            // Balances
            $table->decimal('opening_balance', 15, 2)->default(0);
            $table->decimal('current_balance', 15, 2)->default(0);
            
            // Status
            $table->boolean('is_active')->default(true);
            
            // Additional info
            $table->text('description')->nullable();
            
            // Audit
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
            
            // Indexes
            $table->index('account_type');
            $table->index('is_active');
            $table->index(['parent_id', 'account_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};