<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_advances', function (Blueprint $table) {
            $table->id();
            
            // Employee reference
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            
            // Advance details
            $table->decimal('amount', 15, 2);
            $table->date('advance_date');
            
            // Deduction details
            $table->date('deduction_start_month');
            $table->integer('deduction_installments')->default(1);
            $table->decimal('deduction_amount_per_month', 15, 2)->default(0);
            
            // Status
            $table->enum('status', ['pending', 'approved', 'rejected', 'completed'])->default('pending');
            $table->string('purpose')->nullable();
            
            // Approval
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            
            // Additional info
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
            
            // Indexes
            $table->index('employee_id');
            $table->index('status');
            $table->index('advance_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_advances');
    }
};