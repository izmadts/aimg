<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * transaction_type was an enum that had to be widened by raw ALTER every
     * time a new posting type was introduced (see the 'payment' migration) —
     * and that ALTER only ever applied on MySQL, silently no-op'ing on SQLite
     * (the driver phpunit runs on), so any new type added there would fail
     * feature tests with a CHECK-constraint violation instead of exercising
     * the real code path. Converting to a plain string removes this whole
     * class of trap for every future transaction type, on both drivers, the
     * same way cylinder_transactions.transaction_type was already built.
     * AccountingEntry itself never restricted values beyond the DB column,
     * so this changes no application behavior.
     */
    public function up(): void
    {
        Schema::table('accounting_entries', function (Blueprint $table) {
            $table->string('transaction_type', 30)->default('sale')->change();
        });
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE accounting_entries MODIFY transaction_type ENUM(
                'sale', 'purchase', 'expense', 'income',
                'deposit', 'refund', 'salary', 'advance', 'damage', 'payment'
            ) NOT NULL DEFAULT 'sale'");
        }
    }
};
