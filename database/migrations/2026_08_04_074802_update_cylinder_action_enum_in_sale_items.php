<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Drop the old enum and recreate with more values
        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropColumn('cylinder_action');
        });
        
        Schema::table('sale_items', function (Blueprint $table) {
            $table->enum('cylinder_action', ['none', 'issued', 'sold', 'returned', 'issue', 'sell'])->default('none');
        });
    }

    public function down(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropColumn('cylinder_action');
        });
        
        Schema::table('sale_items', function (Blueprint $table) {
            $table->enum('cylinder_action', ['none', 'issued', 'sold', 'returned'])->default('none');
        });
    }
};