<?php

namespace App\Services;

use App\Models\Account;
use App\Models\AccountingEntry;
use Illuminate\Support\Facades\DB;

/**
 * Single entry point for posting to the general ledger. Every business action
 * (sale, purchase, expense, income, cylinder asset addition, ...) builds a list
 * of {account_code, debit, credit, description} lines describing itself and
 * hands them to post(). post() is the only place accounting_entries rows get
 * written, and it refuses to write anything that doesn't balance.
 */
class AccountingService
{
    /** @var array<string, Account> */
    private array $accountCache = [];

    // ============================================
    // ACCOUNT CODES (chart of accounts, see AccountSeeder)
    // ============================================
    public const CASH = '1001';
    public const BANK = '1002';
    public const ACCOUNTS_RECEIVABLE = '1003';
    public const CYLINDER_ASSET = '1004';
    public const INVENTORY_GAS = '1005';
    public const ACCOUNTS_PAYABLE = '2001';
    public const CYLINDER_DEPOSIT_LIABILITY = '2002';
    public const SALES_REVENUE = '4001';
    public const CYLINDER_SALES_REVENUE = '4002';
    public const DAMAGE_INCOME = '4005';
    public const COST_OF_GOODS_SOLD = '5001';
    public const SALARY_EXPENSE = '5002';

    // ============================================
    // CORE POSTING ENGINE
    // ============================================

    /**
     * Post a balanced set of ledger lines for one business event.
     *
     * @param array<int, array{account_code: string, debit?: float, credit?: float, description: string}> $lines
     * @param string $type one of AccountingEntry's transaction_type values
     * @param mixed $reference the model this posting is for (Sale, Purchase, ...), or null
     * @throws \RuntimeException if the lines don't balance or an account code is missing
     */
    public function post(array $lines, string $type, $reference = null, ?string $date = null): void
    {
        $lines = array_values(array_filter($lines, function ($line) {
            return round(($line['debit'] ?? 0) - ($line['credit'] ?? 0), 2) !== 0.0
                || ($line['debit'] ?? 0) > 0 || ($line['credit'] ?? 0) > 0;
        }));

        if (empty($lines)) {
            return;
        }

        $totalDebit = round(array_sum(array_column($lines, 'debit')), 2);
        $totalCredit = round(array_sum(array_column($lines, 'credit')), 2);

        if ($totalDebit !== $totalCredit) {
            throw new \RuntimeException(sprintf(
                'Refusing to post unbalanced accounting entry (%s): debit %s != credit %s',
                $type,
                number_format($totalDebit, 2),
                number_format($totalCredit, 2)
            ));
        }

        $entryNo = AccountingEntry::generateEntryNo();
        $referenceType = $reference ? get_class($reference) : null;
        $referenceId = $reference ? $reference->id : null;
        $date = $date ?? now()->toDateString();

        DB::transaction(function () use ($lines, $type, $referenceType, $referenceId, $date, $entryNo) {
            $resolved = array_map(fn ($line) => [
                'account' => $this->resolveAccount($line['account_code']),
                'debit' => round($line['debit'] ?? 0, 2),
                'credit' => round($line['credit'] ?? 0, 2),
                'description' => $line['description'],
            ], $lines);

            $touchedAccounts = [];

            foreach ($resolved as $index => $line) {
                $opposite = count($resolved) === 2 ? $resolved[1 - $index]['account'] : null;

                AccountingEntry::create([
                    'entry_no' => $entryNo,
                    'date' => $date,
                    'description' => $line['description'],
                    'transaction_type' => $type,
                    'reference_type' => $referenceType,
                    'reference_id' => $referenceId,
                    'account_id' => $line['account']->id,
                    'opposite_account_id' => $opposite?->id,
                    'debit' => $line['debit'],
                    'credit' => $line['credit'],
                    'status' => 'approved',
                    'created_by' => auth()->id(),
                ]);

                $touchedAccounts[$line['account']->id] = $line['account'];
            }

            foreach ($touchedAccounts as $account) {
                $account->updateBalance();
            }
        });
    }

    private function resolveAccount(string $code): Account
    {
        if (!isset($this->accountCache[$code])) {
            $account = Account::where('account_code', $code)->first();
            if (!$account) {
                throw new \RuntimeException("Chart of accounts is missing required account '{$code}'. Ask an administrator to check Accounts setup.");
            }
            $this->accountCache[$code] = $account;
        }
        return $this->accountCache[$code];
    }

    /**
     * Which asset account represents "money" for a given payment method.
     * Returns null for 'credit' (no cash movement, it all sits on receivable/payable).
     */
    private function cashAccountCodeFor(string $paymentMethod): ?string
    {
        return match ($paymentMethod) {
            'bank_transfer', 'online', 'cheque' => self::BANK,
            'credit' => null,
            default => self::CASH,
        };
    }

    /**
     * Build the settlement side of an entry: how much of the total sits in
     * cash/bank right now vs. how much is still owed (receivable or payable).
     *
     * @return array<int, array{account_code:string, debit?:float, credit?:float, description:string}>
     */
    private function settlementLines(float $grandTotal, float $amountPaid, string $paymentMethod, string $owedAccountCode, bool $asDebit, string $description): array
    {
        $amountPaid = min($amountPaid, $grandTotal);
        $owed = round($grandTotal - $amountPaid, 2);
        $cashCode = $this->cashAccountCodeFor($paymentMethod);

        $lines = [];
        if ($amountPaid > 0 && $cashCode) {
            $lines[] = [
                'account_code' => $cashCode,
                $asDebit ? 'debit' : 'credit' => $amountPaid,
                'description' => $description,
            ];
        }
        if ($owed > 0) {
            $lines[] = [
                'account_code' => $owedAccountCode,
                $asDebit ? 'debit' : 'credit' => $owed,
                'description' => $description,
            ];
        }
        // credit-method sale/purchase paid in full via no cash account (edge case: amountPaid=0 but cashCode null and owed=0)
        if (empty($lines) && $grandTotal > 0) {
            $lines[] = [
                'account_code' => $owedAccountCode,
                $asDebit ? 'debit' : 'credit' => $grandTotal,
                'description' => $description,
            ];
        }
        return $lines;
    }

    // ============================================
    // SALES
    // ============================================

    public function recordSale($sale): void
    {
        $lines = $this->settlementLines(
            (float) $sale->grand_total,
            (float) $sale->amount_paid,
            $sale->payment_method,
            self::ACCOUNTS_RECEIVABLE,
            asDebit: true,
            description: "Sale {$sale->invoice_no}"
        );

        $gasRevenue = round($sale->subtotal - $sale->discount + $sale->tax, 2);
        if ($gasRevenue != 0) {
            $lines[] = [
                'account_code' => self::SALES_REVENUE,
                'credit' => $gasRevenue,
                'description' => "Gas sales revenue: {$sale->invoice_no}",
            ];
        }
        if ($sale->cylinder_deposit_total > 0) {
            $lines[] = [
                'account_code' => self::CYLINDER_DEPOSIT_LIABILITY,
                'credit' => (float) $sale->cylinder_deposit_total,
                'description' => "Cylinder deposit held: {$sale->invoice_no}",
            ];
        }
        if ($sale->cylinder_sale_total > 0) {
            $lines[] = [
                'account_code' => self::CYLINDER_SALES_REVENUE,
                'credit' => (float) $sale->cylinder_sale_total,
                'description' => "Cylinder sale revenue: {$sale->invoice_no}",
            ];
        }

        // Relieve the cylinder asset at cost for units permanently sold (fixed-asset disposal).
        $costOfCylindersSold = $sale->items()
            ->where('cylinder_action', 'sell')
            ->get()
            ->sum(fn ($item) => ($item->cylinder->purchase_price ?? 0) * ($item->cylinder_quantity ?? 0));

        if ($costOfCylindersSold > 0) {
            $lines[] = [
                'account_code' => self::COST_OF_GOODS_SOLD,
                'debit' => round($costOfCylindersSold, 2),
                'description' => "Cost of cylinders sold: {$sale->invoice_no}",
            ];
            $lines[] = [
                'account_code' => self::CYLINDER_ASSET,
                'credit' => round($costOfCylindersSold, 2),
                'description' => "Cylinder asset relieved (sold): {$sale->invoice_no}",
            ];
        }

        $this->post($lines, 'sale', $sale, $sale->date?->toDateString());
    }

    public function recordSalePayment($sale, float $amount, string $paymentMethod, ?string $date = null): void
    {
        $cashCode = $this->cashAccountCodeFor($paymentMethod) ?? self::CASH;
        $this->post([
            ['account_code' => $cashCode, 'debit' => $amount, 'description' => "Payment received: {$sale->invoice_no}"],
            ['account_code' => self::ACCOUNTS_RECEIVABLE, 'credit' => $amount, 'description' => "Payment received: {$sale->invoice_no}"],
        ], 'payment', $sale, $date);
    }

    public function recordDepositRefund($sale, float $amount): void
    {
        $this->post([
            ['account_code' => self::CYLINDER_DEPOSIT_LIABILITY, 'debit' => $amount, 'description' => "Deposit refund: {$sale->invoice_no}"],
            ['account_code' => self::CASH, 'credit' => $amount, 'description' => "Deposit refund: {$sale->invoice_no}"],
        ], 'refund', $sale);
    }

    public function recordDamageCharge($sale, float $amount): void
    {
        $this->post([
            ['account_code' => self::CASH, 'debit' => $amount, 'description' => "Damage charge: {$sale->invoice_no}"],
            ['account_code' => self::DAMAGE_INCOME, 'credit' => $amount, 'description' => "Damage charge: {$sale->invoice_no}"],
        ], 'damage', $sale);
    }

    // ============================================
    // PURCHASES
    // ============================================

    public function recordPurchase($purchase): void
    {
        $netAdjustment = round((float) $purchase->tax - (float) $purchase->discount, 2);
        $gasDebit = (float) $purchase->subtotal;
        $cylinderDebit = (float) $purchase->cylinder_total;

        if ($gasDebit > 0) {
            $gasDebit = round($gasDebit + $netAdjustment, 2);
        } else {
            $cylinderDebit = round($cylinderDebit + $netAdjustment, 2);
        }

        $lines = [];
        if ($gasDebit != 0) {
            $lines[] = [
                'account_code' => self::INVENTORY_GAS,
                'debit' => $gasDebit,
                'description' => "Gas purchased: {$purchase->purchase_invoice_no}",
            ];
        }
        if ($cylinderDebit != 0) {
            $lines[] = [
                'account_code' => self::CYLINDER_ASSET,
                'debit' => $cylinderDebit,
                'description' => "Cylinders purchased: {$purchase->purchase_invoice_no}",
            ];
        }

        $lines = array_merge($lines, $this->settlementLines(
            (float) $purchase->grand_total,
            (float) $purchase->amount_paid,
            $purchase->payment_method,
            self::ACCOUNTS_PAYABLE,
            asDebit: false,
            description: "Purchase {$purchase->purchase_invoice_no}"
        ));

        $this->post($lines, 'purchase', $purchase, $purchase->date?->toDateString());
    }

    public function recordPurchasePayment($purchase, float $amount, string $paymentMethod, ?string $date = null): void
    {
        $cashCode = $this->cashAccountCodeFor($paymentMethod) ?? self::CASH;
        $this->post([
            ['account_code' => self::ACCOUNTS_PAYABLE, 'debit' => $amount, 'description' => "Payment made: {$purchase->purchase_invoice_no}"],
            ['account_code' => $cashCode, 'credit' => $amount, 'description' => "Payment made: {$purchase->purchase_invoice_no}"],
        ], 'payment', $purchase, $date);
    }

    /**
     * A cylinder handed back to a supplier: relieve the asset and record what the
     * supplier owes back (credited against payable, or received as cash).
     */
    public function recordCylinderReturnToSupplier($purchase, float $creditAmount, string $paymentMethod = 'credit'): void
    {
        if ($creditAmount <= 0) {
            return;
        }
        $cashCode = $this->cashAccountCodeFor($paymentMethod);
        $owedCode = $cashCode ?? self::ACCOUNTS_PAYABLE;

        $this->post([
            ['account_code' => $owedCode, 'debit' => $creditAmount, 'description' => "Cylinder returned to supplier: {$purchase->purchase_invoice_no}"],
            ['account_code' => self::CYLINDER_ASSET, 'credit' => $creditAmount, 'description' => "Cylinder returned to supplier: {$purchase->purchase_invoice_no}"],
        ], 'purchase', $purchase);
    }

    // ============================================
    // CYLINDER ASSET (new stock registered directly, not via a Purchase)
    // ============================================

    public function recordCylinderAsset($cylinder, float $totalValue, string $paymentMethod = 'cash'): void
    {
        if ($totalValue <= 0) {
            return;
        }
        $cashCode = $this->cashAccountCodeFor($paymentMethod) ?? self::ACCOUNTS_PAYABLE;

        $this->post([
            ['account_code' => self::CYLINDER_ASSET, 'debit' => $totalValue, 'description' => "Cylinder stock added: {$cylinder->cylinder_number}"],
            ['account_code' => $cashCode, 'credit' => $totalValue, 'description' => "Cylinder stock added: {$cylinder->cylinder_number}"],
        ], 'purchase', $cylinder);
    }

    // ============================================
    // INCOME / EXPENSE
    // ============================================

    public function recordExpense(string $expenseAccountCode, float $amount, string $description, $reference = null, string $paymentMethod = 'cash', ?string $date = null): void
    {
        $cashCode = $this->cashAccountCodeFor($paymentMethod) ?? self::CASH;
        $this->post([
            ['account_code' => $expenseAccountCode, 'debit' => $amount, 'description' => $description],
            ['account_code' => $cashCode, 'credit' => $amount, 'description' => $description],
        ], 'expense', $reference, $date);
    }

    public function recordIncome(string $incomeAccountCode, float $amount, string $description, $reference = null, string $paymentMethod = 'cash', ?string $date = null): void
    {
        $cashCode = $this->cashAccountCodeFor($paymentMethod) ?? self::CASH;
        $this->post([
            ['account_code' => $cashCode, 'debit' => $amount, 'description' => $description],
            ['account_code' => $incomeAccountCode, 'credit' => $amount, 'description' => $description],
        ], 'income', $reference, $date);
    }

    /**
     * Reverse every approved entry posted for a reference (e.g. a cancelled sale)
     * by posting a new, opposite-signed balanced entry. Never mutates or deletes
     * the original rows, so the audit trail stays intact.
     */
    public function reverseEntries($reference, string $type, string $description): void
    {
        $entries = AccountingEntry::where('reference_type', get_class($reference))
            ->where('reference_id', $reference->id)
            ->where('status', 'approved')
            ->with('account')
            ->get();

        if ($entries->isEmpty()) {
            return;
        }

        $lines = $entries->map(fn ($entry) => [
            'account_code' => $entry->account->account_code,
            'debit' => (float) $entry->credit,
            'credit' => (float) $entry->debit,
            'description' => $description,
        ])->all();

        $this->post($lines, $type, $reference);
    }

    // ============================================
    // REPORTS
    // ============================================

    public function getTrialBalance()
    {
        return Account::withSum(['entries as entries_sum_debit' => function ($q) {
            $q->where('status', 'approved');
        }], 'debit')
        ->withSum(['entries as entries_sum_credit' => function ($q) {
            $q->where('status', 'approved');
        }], 'credit')
        ->orderBy('account_code')
        ->get()
        ->map(function ($account) {
            return [
                'account_name' => $account->account_name,
                'account_code' => $account->account_code,
                'account_type' => $account->account_type,
                'debit' => $account->entries_sum_debit ?? 0,
                'credit' => $account->entries_sum_credit ?? 0,
                'balance' => $account->balance,
            ];
        });
    }

    /**
     * Classified by the GL account actually hit (account_type), not by the
     * transaction label — an asset purchase (inventory, cylinder stock) must
     * never count as an expense, only Cost of Goods Sold does, or the
     * fundamental accounting identity (Assets = Liabilities + Equity) breaks.
     *
     * Nets both sides of each account (credit-debit for income, debit-credit
     * for expense) rather than summing one side — a reversal posts to the
     * same account on the *opposite* side to cancel the original out, and a
     * one-sided sum would ignore that cancellation and overstate the result.
     */
    public function getIncomeStatement($startDate = null, $endDate = null)
    {
        $incomeQuery = AccountingEntry::where('status', 'approved')
            ->whereHas('account', fn ($q) => $q->where('account_type', 'income'));

        $expenseQuery = AccountingEntry::where('status', 'approved')
            ->whereHas('account', fn ($q) => $q->where('account_type', 'expense'));

        if ($startDate && $endDate) {
            $incomeQuery->whereBetween('date', [$startDate, $endDate]);
            $expenseQuery->whereBetween('date', [$startDate, $endDate]);
        }

        $income = (clone $incomeQuery)->sum('credit') - (clone $incomeQuery)->sum('debit');
        $expense = (clone $expenseQuery)->sum('debit') - (clone $expenseQuery)->sum('credit');

        return (object) [
            'total_income' => $income,
            'total_expense' => $expense,
            'net_profit' => $income - $expense,
        ];
    }

    public function getBalanceSheet()
    {
        $accounts = Account::where('is_active', true)->orderBy('account_code')->get();

        $assets = $accounts->where('account_type', 'asset');
        $liabilities = $accounts->where('account_type', 'liability');
        $equity = $accounts->where('account_type', 'equity');

        $netEarnings = $this->getIncomeStatement()->net_profit;

        $totalAssets = round((float) $assets->sum('balance'), 2);
        $totalLiabilities = round((float) $liabilities->sum('balance'), 2);
        $totalEquity = round((float) $equity->sum('balance') + $netEarnings, 2);

        return (object) [
            'assets' => $assets->values(),
            'liabilities' => $liabilities->values(),
            'equity' => $equity->values(),
            'current_earnings' => round($netEarnings, 2),
            'total_assets' => $totalAssets,
            'total_liabilities' => $totalLiabilities,
            'total_equity' => $totalEquity,
            'total_liabilities_and_equity' => round($totalLiabilities + $totalEquity, 2),
            'is_balanced' => abs($totalAssets - ($totalLiabilities + $totalEquity)) < 0.01,
        ];
    }
}
