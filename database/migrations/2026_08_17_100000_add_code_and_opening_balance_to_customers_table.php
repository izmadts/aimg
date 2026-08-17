<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('customer_code')->nullable()->unique()->after('id')
                ->comment('System-generated ID, e.g. CUST-000001 — distinct from erp_customer_id (legacy system reference)');
            $table->decimal('opening_balance', 15, 2)->default(0)->after('security_deposit')
                ->comment('Amount the customer already owed before onboarding onto this system');
        });

        DB::table('customers')->whereNull('customer_code')->orderBy('id')->get(['id'])->each(function ($customer) {
            DB::table('customers')->where('id', $customer->id)->update([
                'customer_code' => 'CUST-' . str_pad($customer->id, 6, '0', STR_PAD_LEFT),
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['customer_code', 'opening_balance']);
        });
    }
};
