<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->string('supplier_code')->nullable()->unique()->after('id')
                ->comment('System-generated ID, e.g. SUPP-000001 — distinct from erp_supplier_id (legacy system reference)');
        });

        DB::table('suppliers')->whereNull('supplier_code')->orderBy('id')->get(['id'])->each(function ($supplier) {
            DB::table('suppliers')->where('id', $supplier->id)->update([
                'supplier_code' => 'SUPP-' . str_pad($supplier->id, 6, '0', STR_PAD_LEFT),
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn('supplier_code');
        });
    }
};
