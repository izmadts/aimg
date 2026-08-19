<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Widens cylinder_action to allow 'return' (a customer handing empties
     * back in the same invoice that issues/sells them new ones), on top of
     * the existing 'issue'/'sell'. Converted from enum to a plain string
     * rather than raw-ALTER-widening the enum, for the same cross-driver
     * reason as accounting_entries.transaction_type — an enum here would
     * only ever widen on MySQL, silently leaving the SQLite test DB's CHECK
     * constraint stuck on the old value list.
     */
    public function up(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->string('cylinder_action', 20)->nullable()->change();
        });
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE sale_items MODIFY cylinder_action ENUM('issue', 'sell') NULL");
        }
    }
};
