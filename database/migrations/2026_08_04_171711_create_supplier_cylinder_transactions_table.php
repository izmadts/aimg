<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_cylinder_transactions', function (Blueprint $table) {
            $table->id();
            
            // Foreign keys
            $table->foreignId('supplier_id')->constrained()->onDelete('cascade');
            $table->foreignId('cylinder_id')->constrained()->onDelete('cascade');
            $table->foreignId('purchase_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            
            // Transaction details
            $table->enum('transaction_type', [
                'received_filled',
                'received_empty',
                'returned_empty',
                'purchased_new',
                'exchanged'
            ]);
            
            $table->date('transaction_date');
            $table->decimal('gas_quantity_at_transaction', 10, 2)->nullable();
            $table->decimal('deposit_adjustment', 15, 2)->default(0);
            
            // Cylinder condition
            $table->enum('cylinder_condition', ['good', 'damaged', 'expired', 'under_maintenance'])->default('good');
            $table->decimal('damage_charge', 15, 2)->default(0);
            
            // Reference
            $table->string('reference_document')->nullable();
            $table->text('remarks')->nullable();
            
            $table->timestamps();
            
            // Indexes
           // $table->index(['supplier_id', 'transaction_date']);
            $table->index('cylinder_id');
            $table->index('transaction_type');
            $table->index('purchase_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_cylinder_transactions');
    }
};