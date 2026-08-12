<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * cylinder_types/units are lookup tables added after cylinders.type and
     * gas_products.uom were already free-text. This backfills any value
     * already in use on real records but missing from the lookup tables, so
     * existing data shows up on the Cylinder Types / Units screens instead
     * of only the handful of names the seeders ship with.
     */
    public function up(): void
    {
        $existingTypes = DB::table('cylinder_types')->pluck('name')
            ->map(fn ($n) => mb_strtolower(trim($n)))->all();

        DB::table('cylinders')
            ->whereNotNull('type')
            ->where('type', '!=', '')
            ->distinct()
            ->pluck('type')
            ->each(function ($type) use (&$existingTypes) {
                $trimmed = trim($type);
                if ($trimmed === '' || in_array(mb_strtolower($trimmed), $existingTypes, true)) {
                    return;
                }

                DB::table('cylinder_types')->insert([
                    'name' => mb_substr($trimmed, 0, 50),
                    'capacity' => null,
                    'price_premium' => 0,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $existingTypes[] = mb_strtolower($trimmed);
            });

        $existingUnits = DB::table('units')->pluck('name')
            ->map(fn ($n) => mb_strtolower(trim($n)))->all();

        DB::table('gas_products')
            ->whereNotNull('uom')
            ->where('uom', '!=', '')
            ->distinct()
            ->pluck('uom')
            ->each(function ($uom) use (&$existingUnits) {
                $trimmed = trim($uom);
                if ($trimmed === '' || in_array(mb_strtolower($trimmed), $existingUnits, true)) {
                    return;
                }

                DB::table('units')->insert([
                    'name' => mb_substr($trimmed, 0, 50),
                    'description' => null,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $existingUnits[] = mb_strtolower($trimmed);
            });
    }

    public function down(): void
    {
        // Data backfill only; not reversible.
    }
};
