<?php

namespace App\Services;

use App\Models\Account;
use App\Models\AccountingEntry;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\Employee;
use App\Models\Cylinder;
use Illuminate\Support\Facades\DB;

class AccountingService
{
    /**
     * Record Sale Accounting Entry
     */
    public function recordSale($sale)
    {
        // Get accounts
        $receivable = Account::where('account_code', '1003')->first(); // Accounts Receivable
        $salesRevenue = Account::where('account_code', '4001')->first(); // Sales Revenue
        $depositLiability = Account::where('account_code', '2002')->first(); // Cylinder Deposit Liability

        // Total amount
        $total = $sale->grand_total;
        $subtotal = $sale->subtotal;
        $deposit = $sale->cylinder_deposit_total ?? 0;

        // Create entries
        $entries = [];

        // 1. Debit: Accounts Receivable (or Cash if paid)
        $accountId = $sale->payment_status === 'paid' 
            ? Account::where('account_code', '1001')->first()->id  // Cash
            : $receivable->id;
        
        $entries[] = [
            'account_id' => $accountId,
            'opposite_account_id' => $salesRevenue->id,
            'debit' => $total,
            'credit' => 0,
            'description' => 'Sale Invoice: ' . $sale->invoice_no,
        ];

        // 2. Credit: Sales Revenue
        $entries[] = [
            'account_id' => $salesRevenue->id,
            'opposite_account_id' => $accountId,
            'debit' => 0,
            'credit' => $subtotal,
            'description' => 'Sales Revenue for: ' . $sale->invoice_no,
        ];

        // 3. Credit: Deposit Liability (if deposit taken)
        if ($deposit > 0) {
            $entries[] = [
                'account_id' => $depositLiability->id,
                'opposite_account_id' => $accountId,
                'debit' => 0,
                'credit' => $deposit,
                'description' => 'Cylinder Deposit for: ' . $sale->invoice_no,
            ];
        }

        // Save entries
        foreach ($entries as $entry) {
            $this->createEntry($entry, 'sale', $sale);
        }

        // Update account balances
        $this->updateBalances($entries);
    }

    /**
     * Record Purchase Accounting Entry
     */
    public function recordPurchase($purchase)
    {
        $inventory = Account::where('account_code', '1005')->first(); // Inventory - Gas
        $payable = Account::where('account_code', '2001')->first(); // Accounts Payable
        $cylinderAsset = Account::where('account_code', '1004')->first(); // Cylinder Asset

        $total = $purchase->grand_total;
        $subtotal = $purchase->subtotal;
        $cylinderPurchase = $purchase->cylinder_purchase_total ?? 0;
        $depositPaid = $purchase->cylinder_deposit_paid ?? 0;

        $entries = [];

        // 1. Debit: Inventory
        $entries[] = [
            'account_id' => $inventory->id,
            'opposite_account_id' => $payable->id,
            'debit' => $subtotal,
            'credit' => 0,
            'description' => 'Purchase: ' . $purchase->purchase_invoice_no,
        ];

        // 2. Debit: Cylinder Asset (if purchased)
        if ($cylinderPurchase > 0) {
            $entries[] = [
                'account_id' => $cylinderAsset->id,
                'opposite_account_id' => $payable->id,
                'debit' => $cylinderPurchase,
                'credit' => 0,
                'description' => 'Cylinder Purchase: ' . $purchase->purchase_invoice_no,
            ];
        }

        // 3. Credit: Accounts Payable
        $entries[] = [
            'account_id' => $payable->id,
            'opposite_account_id' => $inventory->id,
            'debit' => 0,
            'credit' => $total,
            'description' => 'Payable for: ' . $purchase->purchase_invoice_no,
        ];

        foreach ($entries as $entry) {
            $this->createEntry($entry, 'purchase', $purchase);
        }

        $this->updateBalances($entries);
    }

    /**
     * Record Cylinder Deposit Refund
     */
    public function recordDepositRefund($sale, $amount, $customer)
    {
        $depositLiability = Account::where('account_code', '2002')->first();
        $cash = Account::where('account_code', '1001')->first();

        $entries = [
            [
                'account_id' => $depositLiability->id,
                'opposite_account_id' => $cash->id,
                'debit' => $amount,
                'credit' => 0,
                'description' => 'Deposit Refund for: ' . $sale->invoice_no,
            ],
            [
                'account_id' => $cash->id,
                'opposite_account_id' => $depositLiability->id,
                'debit' => 0,
                'credit' => $amount,
                'description' => 'Cash refund for: ' . $sale->invoice_no,
            ]
        ];

        foreach ($entries as $entry) {
            $this->createEntry($entry, 'refund', $sale);
        }

        $this->updateBalances($entries);
    }

    /**
     * Record Damage Charge Income
     */
    public function recordDamageCharge($sale, $amount)
    {
        $cash = Account::where('account_code', '1001')->first();
        $damageIncome = Account::where('account_code', '4005')->first();

        $entries = [
            [
                'account_id' => $cash->id,
                'opposite_account_id' => $damageIncome->id,
                'debit' => $amount,
                'credit' => 0,
                'description' => 'Damage Charge: ' . $sale->invoice_no,
            ],
            [
                'account_id' => $damageIncome->id,
                'opposite_account_id' => $cash->id,
                'debit' => 0,
                'credit' => $amount,
                'description' => 'Damage Income: ' . $sale->invoice_no,
            ]
        ];

        foreach ($entries as $entry) {
            $this->createEntry($entry, 'damage', $sale);
        }

        $this->updateBalances($entries);
    }

    /**
     * Record Expense
     */
    public function recordExpense($accountId, $amount, $description, $reference = null)
    {
        $cash = Account::where('account_code', '1001')->first();

        $entries = [
            [
                'account_id' => $accountId,
                'opposite_account_id' => $cash->id,
                'debit' => $amount,
                'credit' => 0,
                'description' => $description,
            ],
            [
                'account_id' => $cash->id,
                'opposite_account_id' => $accountId,
                'debit' => 0,
                'credit' => $amount,
                'description' => 'Cash Payment: ' . $description,
            ]
        ];

        foreach ($entries as $entry) {
            $this->createEntry($entry, 'expense', $reference);
        }

        $this->updateBalances($entries);
    }

    /**
     * Record Income
     */
    public function recordIncome($accountId, $amount, $description, $reference = null)
    {
        $cash = Account::where('account_code', '1001')->first();

        $entries = [
            [
                'account_id' => $cash->id,
                'opposite_account_id' => $accountId,
                'debit' => $amount,
                'credit' => 0,
                'description' => $description,
            ],
            [
                'account_id' => $accountId,
                'opposite_account_id' => $cash->id,
                'debit' => 0,
                'credit' => $amount,
                'description' => 'Income: ' . $description,
            ]
        ];

        foreach ($entries as $entry) {
            $this->createEntry($entry, 'income', $reference);
        }

        $this->updateBalances($entries);
    }

    /**
     * Create Entry
     */
    private function createEntry($data, $type, $reference)
    {
        $referenceType = $reference ? get_class($reference) : null;
        $referenceId = $reference ? $reference->id : null;

        return AccountingEntry::create([
            'entry_no' => AccountingEntry::generateEntryNo(),
            'date' => now(),
            'description' => $data['description'],
            'transaction_type' => $type,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'account_id' => $data['account_id'],
            'opposite_account_id' => $data['opposite_account_id'] ?? null,
            'debit' => $data['debit'],
            'credit' => $data['credit'],
            'status' => 'approved',
            'created_by' => auth()->id(),
        ]);
    }

    /**
     * Update Account Balances
     */
    private function updateBalances($entries)
    {
        foreach ($entries as $entry) {
            $account = Account::find($entry['account_id']);
            if ($account) {
                $account->updateBalance();
            }
            if (isset($entry['opposite_account_id']) && $entry['opposite_account_id']) {
                $oppositeAccount = Account::find($entry['opposite_account_id']);
                if ($oppositeAccount) {
                    $oppositeAccount->updateBalance();
                }
            }
        }
    }

    /**
     * Get Trial Balance
     */
    public function getTrialBalance()
    {
        return Account::withSum(['entries' => function ($q) {
            $q->where('status', 'approved');
        }], 'debit')
        ->withSum(['entries' => function ($q) {
            $q->where('status', 'approved');
        }], 'credit')
        ->get()
        ->map(function ($account) {
            return [
                'account_name' => $account->account_name,
                'account_code' => $account->account_code,
                'debit' => $account->entries_sum_debit ?? 0,
                'credit' => $account->entries_sum_credit ?? 0,
                'balance' => ($account->entries_sum_debit ?? 0) - ($account->entries_sum_credit ?? 0),
            ];
        });
    }

    /**
     * Get Income Statement
     */
    public function getIncomeStatement($startDate = null, $endDate = null)
    {
        $query = AccountingEntry::where('status', 'approved')
            ->whereIn('transaction_type', ['sale', 'income', 'damage']);

        if ($startDate && $endDate) {
            $query->whereBetween('date', [$startDate, $endDate]);
        }

        $income = $query->sum('credit');

        $expenseQuery = AccountingEntry::where('status', 'approved')
            ->whereIn('transaction_type', ['purchase', 'expense', 'salary']);

        if ($startDate && $endDate) {
            $expenseQuery->whereBetween('date', [$startDate, $endDate]);
        }

        $expense = $expenseQuery->sum('debit');

        return (object) [
            'total_income' => $income,
            'total_expense' => $expense,
            'net_profit' => $income - $expense,
        ];
    }
}