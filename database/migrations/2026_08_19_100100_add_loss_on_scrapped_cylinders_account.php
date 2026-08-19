<?php

use App\Models\Account;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Adds the chart-of-accounts row AccountingService::recordCylinderDisposal()
     * needs (code 5014). Done as a migration rather than re-running AccountSeeder,
     * since that seeder already ran once in production and isn't idempotent
     * (would throw a duplicate-key error on every other account it re-inserts).
     */
    public function up(): void
    {
        Account::firstOrCreate(
            ['account_code' => '5014'],
            [
                'account_name' => 'Loss on Scrapped/Disposed Cylinders',
                'account_type' => 'expense',
                'opening_balance' => 0,
                'is_active' => true,
                'description' => 'Cylinders formally written off as unrepairable',
            ]
        );
    }

    public function down(): void
    {
        Account::where('account_code', '5014')->delete();
    }
};
