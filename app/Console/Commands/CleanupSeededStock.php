<?php

namespace App\Console\Commands;

use App\Models\Cylinder;
use App\Models\GasProduct;
use Illuminate\Console\Command;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class CleanupSeededStock extends Command
{
    /**
     * Removes leftover demo gas products / cylinder types from the seeders
     * (Oxygen, Nitrogen, Argon, Carbon Dioxide + their standard sizes) — but
     * only rows that are genuinely empty (zero stock) AND never touched by a
     * real sale, purchase, or cylinder movement. Anything with stock or
     * history is left alone; the database's own foreign-key constraints
     * (onDelete('restrict')) are the final safety net if this logic misses
     * a reference.
     */
    protected $signature = 'cleanup:seeded-stock {--force : Actually delete rows. Without this flag, only reports what would be removed.}';

    protected $description = 'Remove empty, unused demo gas products/cylinders left over from the seeders';

    public function handle(): int
    {
        $dryRun = ! $this->option('force');

        if ($dryRun) {
            $this->warn('DRY RUN — nothing will be deleted. Re-run with --force to actually remove rows.');
        }

        $this->info('Checking gas products...');
        $removedGas = 0;
        $skippedGas = 0;

        foreach (GasProduct::where('current_stock', '<=', 0)->get() as $product) {
            $inUse = $product->cylinders()->exists()
                || DB::table('sale_items')->where('gas_product_id', $product->id)->exists()
                || DB::table('purchase_items')->where('gas_product_id', $product->id)->exists();

            if ($inUse) {
                $this->line("  Skipped (has history): {$product->name} [{$product->code}]");
                $skippedGas++;
                continue;
            }

            $this->line("  " . ($dryRun ? 'Would remove' : 'Removing') . ": {$product->name} [{$product->code}] — 0 stock, no history");
            if (! $dryRun) {
                try {
                    $product->delete();
                    $removedGas++;
                } catch (QueryException $e) {
                    $this->line("    Skipped — database refused (still referenced somewhere): {$product->name}");
                    $skippedGas++;
                }
            } else {
                $removedGas++;
            }
        }

        $this->info('Checking cylinder types...');
        $removedCyl = 0;
        $skippedCyl = 0;

        foreach (Cylinder::where('stock_quantity', '<=', 0)->where('issued_quantity', '<=', 0)->get() as $cylinder) {
            $inUse = $cylinder->transactions()->exists()
                || $cylinder->issuedDetails()->exists()
                || DB::table('sale_items')->where('cylinder_id', $cylinder->id)->exists()
                || DB::table('purchase_items')->where('cylinder_id', $cylinder->id)->exists();

            if ($inUse) {
                $this->line("  Skipped (has history): {$cylinder->cylinder_number} — {$cylinder->type}");
                $skippedCyl++;
                continue;
            }

            $this->line("  " . ($dryRun ? 'Would remove' : 'Removing') . ": {$cylinder->cylinder_number} — {$cylinder->type} — 0 stock, no history");
            if (! $dryRun) {
                try {
                    $cylinder->delete();
                    $removedCyl++;
                } catch (QueryException $e) {
                    $this->line("    Skipped — database refused (still referenced somewhere): {$cylinder->cylinder_number}");
                    $skippedCyl++;
                }
            } else {
                $removedCyl++;
            }
        }

        $this->newLine();
        $this->info($dryRun
            ? "Would remove {$removedGas} gas product(s) and {$removedCyl} cylinder type(s). {$skippedGas} gas product(s) and {$skippedCyl} cylinder type(s) have history and were left alone."
            : "Removed {$removedGas} gas product(s) and {$removedCyl} cylinder type(s). {$skippedGas} gas product(s) and {$skippedCyl} cylinder type(s) have history and were left alone.");

        return self::SUCCESS;
    }
}
