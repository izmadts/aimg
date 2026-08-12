<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 'payment' is used by AccountingService::recordSalePayment() and
     * recordPurchasePayment() (and HRMController's salary payment posting)
     * but was never in this enum, so every payment recorded against a sale
     * or purchase invoice has been failing with a DB truncation error since
     * the very first payment was ever attempted. This restores it, on top
     * of the original allowed list, without narrowing anything.
     */
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE accounting_entries MODIFY transaction_type ENUM(
                'sale', 'purchase', 'expense', 'income',
                'deposit', 'refund', 'salary', 'advance', 'damage', 'payment'
            ) NOT NULL DEFAULT 'sale'");
        }
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE accounting_entries MODIFY transaction_type ENUM(
                'sale', 'purchase', 'expense', 'income',
                'deposit', 'refund', 'salary', 'advance', 'damage'
            ) NOT NULL DEFAULT 'sale'");
        }
    }
};
