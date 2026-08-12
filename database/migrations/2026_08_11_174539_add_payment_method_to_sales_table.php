<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            // ✅ Check if column exists before adding
            if (!Schema::hasColumn('sales', 'payment_method')) {
                $table->enum('payment_method', ['cash', 'bank_transfer', 'cheque', 'online', 'credit'])
                    ->default('cash')
                    ->after('payment_status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            if (Schema::hasColumn('sales', 'payment_method')) {
                $table->dropColumn('payment_method');
            }
        });
    }
};