<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // For MySQL - Modify ENUM column
        DB::statement("ALTER TABLE cylinder_transactions 
            MODIFY transaction_type ENUM(
                'issued', 'returned', 'sold', 'purchased',
                'filled', 'emptied', 'maintenance_in', 'maintenance_out',
                'scrapped', 'received_filled', 'received_empty',
                'returned_empty', 'exchanged', 'stock_update'
            ) DEFAULT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE cylinder_transactions 
            MODIFY transaction_type ENUM(
                'issued', 'returned', 'sold', 'purchased',
                'filled', 'emptied', 'maintenance_in', 'maintenance_out',
                'scrapped', 'received_filled', 'received_empty',
                'returned_empty', 'exchanged'
            ) DEFAULT NULL");
    }
};