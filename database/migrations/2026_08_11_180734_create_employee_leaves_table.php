<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_leaves', function (Blueprint $table) {
            $table->id();
            
            // Employee reference
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            
            // Leave details
            $table->enum('leave_type', [
                'annual',      // سالانہ چھٹی
                'sick',        // بیماری کی چھٹی
                'casual',      // عام چھٹی
                'maternity',   // زچگی کی چھٹی
                'paternity',   // والدیت کی چھٹی
                'unpaid',      // بلا تنخواہ چھٹی
                'other'        // دیگر چھٹی
            ])->default('annual');
            
            // Date range
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('days')->default(0);
            
            // Reason and details
            $table->string('reason')->nullable();
            $table->text('notes')->nullable();
            
            // Status
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending');
            
            // Approval details
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            
            // Audit
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
            
            // Indexes
            $table->index('employee_id');
            $table->index('status');
            $table->index('start_date');
            $table->index('end_date');
            $table->index(['employee_id', 'status']);
            $table->index(['start_date', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_leaves');
    }
};