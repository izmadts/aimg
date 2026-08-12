<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->boolean('is_customer_cylinder')->default(false)->after('cylinder_total')
                ->comment('True when a gas-only line was refilled into a cylinder the customer already owns (not tracked in our inventory)');
        });
    }

    public function down(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropColumn('is_customer_cylinder');
        });
    }
};
