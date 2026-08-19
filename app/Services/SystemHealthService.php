<?php

namespace App\Services;

use App\Models\Account;
use App\Models\AccountingEntry;
use App\Models\Cylinder;
use App\Models\CylinderIssuedDetail;
use App\Models\GasProduct;
use App\Models\Purchase;
use App\Models\Sale;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Read-only audit of accounting and inventory data, plus a small set of
 * deterministic, single-correct-answer fixes (recompute a cached total from
 * its own source-of-truth records; post a ledger entry that's provably
 * missing). Anything without one obviously correct fix — an entry that
 * doesn't balance, negative stock, over-allocated cylinder pools — is
 * reported for manual review only. This never guesses at a number.
 */
class SystemHealthService
{
    public function run(): array
    {
        $issues = collect()
            ->merge($this->checkUnbalancedEntryGroups())
            ->merge($this->checkAccountBalanceDrift())
            ->merge($this->checkSalesMissingEntries())
            ->merge($this->checkPurchasesMissingEntries())
            ->merge($this->checkGlobalTrialBalance())
            ->merge($this->checkNegativeGasStock())
            ->merge($this->checkCylinderOverAllocation())
            ->merge($this->checkCylinderIssuedDrift())
            ->values();

        return [
            'issues' => $issues,
            'summary' => [
                'total' => $issues->count(),
                'critical' => $issues->where('severity', 'critical')->count(),
                'warning' => $issues->where('severity', 'warning')->count(),
                'fixable' => $issues->where('fixable', true)->count(),
            ],
        ];
    }

    /**
     * Apply only the deterministic fixes found above. Every write re-checks
     * its own precondition immediately before acting (e.g. "does this sale
     * really still have zero entries?") so nothing can be double-applied
     * even if this runs twice in a row.
     */
    public function reconcile(): array
    {
        $applied = [];

        DB::transaction(function () use (&$applied) {
            foreach ($this->checkAccountBalanceDrift() as $issue) {
                $account = Account::find($issue['context']['account_id']);
                if ($account && round((float) $account->current_balance, 2) !== round((float) $account->balance, 2)) {
                    $account->updateBalance();
                    $applied[] = "Recomputed cached balance for account \"{$account->account_name}\"";
                }
            }

            foreach ($this->checkCylinderIssuedDrift() as $issue) {
                $cylinder = Cylinder::find($issue['context']['cylinder_id']);
                if ($cylinder) {
                    $actualIssued = (int) CylinderIssuedDetail::where('cylinder_id', $cylinder->id)->where('status', 'issued')->sum('quantity');
                    if ($actualIssued !== (int) $cylinder->issued_quantity) {
                        $cylinder->update(['issued_quantity' => $actualIssued]);
                        $cylinder->updateStatus();
                        $applied[] = "Corrected issued count for cylinder \"{$cylinder->cylinder_number}\" ({$cylinder->type}) to {$actualIssued}";
                    }
                }
            }

            $accountingService = app(AccountingService::class);

            foreach ($this->checkSalesMissingEntries() as $issue) {
                $sale = Sale::find($issue['context']['sale_id']);
                if ($sale && $sale->accountingEntries()->count() === 0) {
                    $accountingService->recordSale($sale);
                    $applied[] = "Posted the missing accounting entry for Sale {$sale->invoice_no}";
                }
            }

            foreach ($this->checkPurchasesMissingEntries() as $issue) {
                $purchase = Purchase::find($issue['context']['purchase_id']);
                if ($purchase && $purchase->accountingEntries()->count() === 0) {
                    $accountingService->recordPurchase($purchase);
                    $applied[] = "Posted the missing accounting entry for Purchase {$purchase->purchase_invoice_no}";
                }
            }
        });

        return $applied;
    }

    // ============================================
    // ACCOUNTING CHECKS
    // ============================================

    private function checkUnbalancedEntryGroups(): Collection
    {
        return AccountingEntry::selectRaw('entry_no, SUM(debit) as total_debit, SUM(credit) as total_credit')
            ->where('status', 'approved')
            ->groupBy('entry_no')
            ->havingRaw('ROUND(SUM(debit), 2) != ROUND(SUM(credit), 2)')
            ->get()
            ->map(fn ($g) => [
                'category' => 'accounting',
                'severity' => 'critical',
                'title' => "Unbalanced ledger entry {$g->entry_no}",
                'description' => 'Debit Rs. ' . number_format($g->total_debit, 2) . ' does not equal credit Rs. ' . number_format($g->total_credit, 2) . '. This should be structurally impossible through the app — likely data edited directly in the database.',
                'fixable' => false,
                'fix_type' => null,
                'context' => ['entry_no' => $g->entry_no],
            ]);
    }

    private function checkAccountBalanceDrift(): Collection
    {
        return Account::all()
            ->filter(fn ($account) => round((float) $account->current_balance, 2) !== round((float) $account->balance, 2))
            ->map(fn ($account) => [
                'category' => 'accounting',
                'severity' => 'warning',
                'title' => "Cached balance out of date: {$account->account_name}",
                'description' => 'Stored balance Rs. ' . number_format($account->current_balance, 2) . ' vs. the true balance from the ledger, Rs. ' . number_format($account->balance, 2) . '.',
                'fixable' => true,
                'fix_type' => 'account_balance',
                'context' => ['account_id' => $account->id],
            ])
            ->values();
    }

    private function checkSalesMissingEntries(): Collection
    {
        return Sale::where('status', 'confirmed')
            ->whereDoesntHave('accountingEntries')
            ->get()
            ->map(fn ($sale) => [
                'category' => 'accounting',
                'severity' => 'critical',
                'title' => "Sale {$sale->invoice_no} never posted to the ledger",
                'description' => 'A confirmed sale for Rs. ' . number_format($sale->grand_total, 2) . ' has zero accounting entries.',
                'fixable' => true,
                'fix_type' => 'post_sale',
                'context' => ['sale_id' => $sale->id],
            ]);
    }

    private function checkPurchasesMissingEntries(): Collection
    {
        return Purchase::where('status', 'confirmed')
            ->whereDoesntHave('accountingEntries')
            ->get()
            ->map(fn ($purchase) => [
                'category' => 'accounting',
                'severity' => 'critical',
                'title' => "Purchase {$purchase->purchase_invoice_no} never posted to the ledger",
                'description' => 'A confirmed purchase for Rs. ' . number_format($purchase->grand_total, 2) . ' has zero accounting entries.',
                'fixable' => true,
                'fix_type' => 'post_purchase',
                'context' => ['purchase_id' => $purchase->id],
            ]);
    }

    private function checkGlobalTrialBalance(): Collection
    {
        $totalDebit = round((float) AccountingEntry::where('status', 'approved')->sum('debit'), 2);
        $totalCredit = round((float) AccountingEntry::where('status', 'approved')->sum('credit'), 2);

        if ($totalDebit === $totalCredit) {
            return collect();
        }

        return collect([[
            'category' => 'accounting',
            'severity' => 'critical',
            'title' => 'The ledger is out of balance overall',
            'description' => 'Total debits Rs. ' . number_format($totalDebit, 2) . ' vs. total credits Rs. ' . number_format($totalCredit, 2) . ' across every approved entry.',
            'fixable' => false,
            'fix_type' => null,
            'context' => [],
        ]]);
    }

    // ============================================
    // INVENTORY CHECKS
    // ============================================

    private function checkNegativeGasStock(): Collection
    {
        return GasProduct::where('current_stock', '<', 0)->get()->map(fn ($p) => [
            'category' => 'inventory',
            'severity' => 'critical',
            'title' => "Negative stock: {$p->name}",
            'description' => 'Current stock is ' . number_format($p->current_stock, 2) . " {$p->uom} — a sale or transfer went through without enough stock on hand.",
            'fixable' => false,
            'fix_type' => null,
            'context' => ['gas_product_id' => $p->id],
        ]);
    }

    private function checkCylinderOverAllocation(): Collection
    {
        $issues = collect();

        Cylinder::all()->each(function ($c) use ($issues) {
            $allocated = $c->issued_quantity + $c->filled_quantity + $c->maintenance_quantity + $c->scrap_quantity;
            if ($allocated > $c->stock_quantity) {
                $issues->push([
                    'category' => 'inventory',
                    'severity' => 'critical',
                    'title' => "{$c->cylinder_number} ({$c->type}) is over-allocated",
                    'description' => "Issued + Filled + Maintenance + Scrap ({$allocated}) exceeds Total Stock ({$c->stock_quantity}).",
                    'fixable' => false,
                    'fix_type' => null,
                    'context' => ['cylinder_id' => $c->id],
                ]);
            }

            foreach (['issued_quantity', 'filled_quantity', 'maintenance_quantity', 'scrap_quantity', 'stock_quantity'] as $field) {
                if ($c->$field < 0) {
                    $issues->push([
                        'category' => 'inventory',
                        'severity' => 'critical',
                        'title' => "{$c->cylinder_number} ({$c->type}) has a negative {$field}",
                        'description' => "{$field} is {$c->$field}, which should never be negative.",
                        'fixable' => false,
                        'fix_type' => null,
                        'context' => ['cylinder_id' => $c->id],
                    ]);
                }
            }
        });

        return $issues;
    }

    private function checkCylinderIssuedDrift(): Collection
    {
        $issues = collect();

        Cylinder::all()->each(function ($c) use ($issues) {
            $actualIssued = (int) CylinderIssuedDetail::where('cylinder_id', $c->id)->where('status', 'issued')->sum('quantity');
            if ($actualIssued !== (int) $c->issued_quantity) {
                $issues->push([
                    'category' => 'inventory',
                    'severity' => 'warning',
                    'title' => "{$c->cylinder_number} ({$c->type}) issued count is out of sync",
                    'description' => "The cylinder record says {$c->issued_quantity} issued, but the actual per-customer records sum to {$actualIssued}.",
                    'fixable' => true,
                    'fix_type' => 'cylinder_issued_quantity',
                    'context' => ['cylinder_id' => $c->id, 'correct_value' => $actualIssued],
                ]);
            }
        });

        return $issues;
    }
}
