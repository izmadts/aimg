<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            
            // Employee identification
            $table->string('employee_code')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('cnic')->nullable()->unique();
            
            // Job details
            $table->string('designation');
            $table->string('department');
            $table->date('joining_date');
            $table->date('leaving_date')->nullable();
            
            // Salary details
            $table->decimal('basic_salary', 15, 2)->default(0);
            $table->decimal('allowances', 15, 2)->default(0);
            $table->decimal('deductions', 15, 2)->default(0);
            $table->decimal('net_salary', 15, 2)->default(0);
            
            // Bank details
            $table->string('bank_name')->nullable();
            $table->string('bank_account')->nullable();
            
            // Status
            $table->boolean('is_active')->default(true);
            
            // Additional info
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
            
            // Indexes
            $table->index('employee_code');
            $table->index('email');
            $table->index('designation');
            $table->index('department');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};