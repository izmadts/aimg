<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('erp_supplier_id')->nullable()->comment('Purane ERP ki ID');
            $table->string('name');
            $table->string('company_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('ntn_number')->nullable();
            $table->string('cnic')->nullable();
            
            // Business details
            $table->string('contact_person')->nullable();
            $table->string('contact_person_phone')->nullable();
            
            // Financial
            $table->decimal('opening_balance', 15, 2)->default(0)->comment('Supplier ko dena hai ya lena hai');
            $table->enum('balance_type', ['payable', 'receivable'])->default('payable');
            
            // Cylinder related (important for gas business)
            $table->decimal('cylinder_deposit_paid', 15, 2)->default(0)->comment('Supplier ko cylinder deposit diya hua');
            $table->integer('cylinder_return_days')->default(7)->comment('Kitne din mein cylinder wapas karna hai');
            
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('name');
            $table->index('phone');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};