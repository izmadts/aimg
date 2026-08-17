<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class ReconcileMigrations extends Command
{
    /**
     * For a database whose schema was bootstrapped from a raw SQL dump
     * instead of `php artisan migrate` — so the `migrations` ledger is
     * empty/incomplete even though the tables already exist. For every
     * migration not yet recorded, this actually tries to run it:
     *   - if the database says the table/column/index it creates already
     *     exists (MySQL 1050/1060/1061), nothing changes — it's just
     *     recorded as applied, since that error can only mean it already is.
     *   - any other error stops the whole run immediately for manual review,
     *     rather than silently marking something broken as done.
     *   - if it doesn't error at all, it just genuinely ran, exactly like a
     *     normal migration.
     */
    protected $signature = 'migrate:reconcile {--dry-run : List what is pending without applying or marking anything}';

    protected $description = 'Sync the migrations table with a database whose schema already exists but has no migration history';

    private const ALREADY_EXISTS_CODES = [1050, 1060, 1061];

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        if (! Schema::hasTable('migrations')) {
            $this->call('migrate:install');
        }

        $already = DB::table('migrations')->pluck('migration')->all();
        $batch = (int) (DB::table('migrations')->max('batch')) + 1;

        $pending = collect(File::files(database_path('migrations')))
            ->map(fn ($f) => $f->getFilename())
            ->filter(fn ($f) => str_ends_with($f, '.php'))
            ->map(fn ($f) => substr($f, 0, -4))
            ->sort()
            ->values()
            ->reject(fn ($name) => in_array($name, $already, true));

        if ($pending->isEmpty()) {
            $this->info('Nothing to reconcile — every migration file is already recorded.');
            return self::SUCCESS;
        }

        $this->info(($dryRun ? 'DRY RUN — ' : '') . "{$pending->count()} migration(s) not yet in the ledger:");
        foreach ($pending as $name) {
            $this->line("  - {$name}");
        }
        if ($dryRun) {
            $this->newLine();
            $this->info('Re-run without --dry-run to process these (each is either genuinely applied or, if its table/column already exists, just marked done without changing anything).');
            return self::SUCCESS;
        }

        $this->newLine();
        $appliedForReal = 0;
        $markedExisting = 0;

        foreach ($pending as $name) {
            $path = database_path("migrations/{$name}.php");
            $migration = require $path;

            if (! is_object($migration) || ! method_exists($migration, 'up')) {
                $this->error("Stopping — {$name} doesn't look like a standard migration file. Check it manually.");
                return self::FAILURE;
            }

            try {
                $migration->up();
                DB::table('migrations')->insert(['migration' => $name, 'batch' => $batch]);
                $this->info("  Applied (was genuinely new): {$name}");
                $appliedForReal++;
            } catch (QueryException $e) {
                $code = (int) ($e->errorInfo[1] ?? 0);

                if (in_array($code, self::ALREADY_EXISTS_CODES, true)) {
                    DB::table('migrations')->insert(['migration' => $name, 'batch' => $batch]);
                    $this->warn("  Already present in the database — marked done, nothing changed: {$name}");
                    $markedExisting++;
                } else {
                    $this->error("  Stopping — {$name} failed with an unexpected error, needs manual review:");
                    $this->error('  ' . $e->getMessage());
                    $this->newLine();
                    $this->error("Progress so far: {$appliedForReal} applied, {$markedExisting} marked as already present. Fix the issue above, then re-run this command — it will resume from here.");
                    return self::FAILURE;
                }
            }
        }

        $this->newLine();
        $this->info("Done. {$appliedForReal} migration(s) genuinely applied, {$markedExisting} marked as already present.");

        return self::SUCCESS;
    }
}
