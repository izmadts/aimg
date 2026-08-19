<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * ecr_number: the shop's own pre-printed Khata-book receipt number,
     * entered by hand — separate from invoice_no, which always
     * auto-generates as the internal system reference regardless.
     * cylinder_return_refund_total: money handed back to a customer for
     * cylinders returned within this same invoice (see Sale::calculateTotals()).
     */
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->string('ecr_number')->nullable()->unique()->after('invoice_no');
            $table->decimal('cylinder_return_refund_total', 15, 2)->default(0)->after('cylinder_sale_total');
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['ecr_number', 'cylinder_return_refund_total']);
        });
    }
};
