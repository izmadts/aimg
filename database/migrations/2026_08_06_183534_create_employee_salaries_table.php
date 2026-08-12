<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_salaries', function (Blueprint $table) {
            $table->id();
            
            // Employee reference
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            
            // Salary period
            $table->integer('month');
            $table->integer('year');
            
            // Salary breakdown
            $table->decimal('basic_salary', 15, 2)->default(0);
            $table->decimal('allowances', 15, 2)->default(0);
            $table->decimal('deductions', 15, 2)->default(0);
            $table->decimal('net_salary', 15, 2)->default(0);
            
            // Status
            $table->enum('status', ['pending', 'paid', 'cancelled'])->default('pending');
            $table->date('paid_date')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('transaction_no')->nullable();
            
            // Additional info
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
            
            // Indexes
            $table->index(['employee_id', 'month', 'year']);
            $table->index('status');
            $table->unique(['employee_id', 'month', 'year'], 'unique_salary_month');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_salaries');
    }
};