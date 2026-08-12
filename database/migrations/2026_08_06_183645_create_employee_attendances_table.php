<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_attendances', function (Blueprint $table) {
            $table->id();
            
            // Employee reference
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            
            // Attendance details
            $table->date('date');
            $table->enum('status', ['present', 'absent', 'late', 'leave', 'holiday'])->default('present');
            $table->timestamp('check_in_time')->nullable();
            $table->timestamp('check_out_time')->nullable();
            
            // Calculations
            $table->decimal('overtime_hours', 8, 2)->default(0);
            $table->integer('late_minutes')->default(0);
            $table->integer('early_leave_minutes')->default(0);
            
            // Additional info
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
            
            // Indexes
            $table->index(['employee_id', 'date']);
            $table->index('date');
            $table->index('status');
            $table->unique(['employee_id', 'date'], 'unique_attendance_day');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_attendances');
    }
};