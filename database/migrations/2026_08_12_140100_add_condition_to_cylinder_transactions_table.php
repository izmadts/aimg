<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cylinder_transactions', function (Blueprint $table) {
            $table->enum('condition', ['good', 'damaged', 'expired'])->nullable()->after('transaction_type')
                ->comment('Physical condition recorded when a cylinder is returned from a customer');
        });
    }

    public function down(): void
    {
        Schema::table('cylinder_transactions', function (Blueprint $table) {
            $table->dropColumn('condition');
        });
    }
};
