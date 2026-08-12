<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cylinder_issued_details', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('cylinder_id')->constrained()->onDelete('cascade');
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            
            $table->integer('quantity')->default(1);
            $table->date('issue_date');
            $table->date('return_date')->nullable();
            
            $table->decimal('security_deposit', 15, 2)->default(0);
            $table->string('reference_document')->nullable();
            
            $table->enum('status', ['issued', 'returned'])->default('issued');
            
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['cylinder_id', 'customer_id']);
            $table->index('status');
            $table->index('issue_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cylinder_issued_details');
    }
};