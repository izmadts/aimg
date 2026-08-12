<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\AccountingEntry;
use App\Http\Requests\StoreIncomeExpenseRequest;
use App\Services\AccountingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IncomeExpenseController extends Controller
{
    protected $accountingService;

    public function __construct(AccountingService $accountingService)
    {
        $this->accountingService = $accountingService;
    }

    /**
     * Display income/expense dashboard.
     */
    public function index(Request $request)
    {
        $startDate = $request->filled('start_date') ? $request->start_date : now()->startOfMonth()->format('Y-m-d');
        $endDate = $request->filled('end_date') ? $request->end_date : now()->format('Y-m-d');

        $incomeAccounts = Account::income()->active()->get();
        $expenseAccounts = Account::expense()->active()->get();

        $incomeEntries = AccountingEntry::approved()
            ->whereHas('account', fn ($q) => $q->where('account_type', 'income'))
            ->whereBetween('date', [$startDate, $endDate])
            ->with(['account', 'oppositeAccount'])
            ->get();

        $expenseEntries = AccountingEntry::approved()
            ->whereHas('account', fn ($q) => $q->where('account_type', 'expense'))
            ->whereBetween('date', [$startDate, $endDate])
            ->with(['account', 'oppositeAccount'])
            ->get();

        $totalIncome = $incomeEntries->sum('credit');
        $totalExpense = $expenseEntries->sum('debit');
        $netProfit = $totalIncome - $totalExpense;

        $incomeByAccount = $incomeEntries->groupBy('account_id')->map(function ($items) {
            return [
                'account' => $items->first()->account,
                'total' => $items->sum('credit'),
            ];
        });

        $expenseByAccount = $expenseEntries->groupBy('account_id')->map(function ($items) {
            return [
                'account' => $items->first()->account,
                'total' => $items->sum('debit'),
            ];
        });

        $monthlySummary = $this->getMonthlySummary($startDate, $endDate);

        $recentTransactions = AccountingEntry::approved()
            ->whereIn('transaction_type', ['income', 'expense'])
            ->with(['account', 'oppositeAccount', 'creator'])
            ->latest()
            ->limit(20)
            ->get();

        return view('income-expense.index', compact(
            'incomeAccounts',
            'expenseAccounts',
            'totalIncome',
            'totalExpense',
            'netProfit',
            'incomeByAccount',
            'expenseByAccount',
            'monthlySummary',
            'recentTransactions',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Show income entry form.
     */
    public function createIncome()
    {
        $accounts = Account::income()->active()->get();
        return view('income-expense.create-income', compact('accounts'));
    }

    /**
     * Show expense entry form.
     */
    public function createExpense()
    {
        $accounts = Account::expense()->active()->get();
        return view('income-expense.create-expense', compact('accounts'));
    }

    /**
     * Store income transaction.
     */
    public function storeIncome(StoreIncomeExpenseRequest $request)
    {
        $validated = $request->validated();
        $account = Account::findOrFail($validated['account_id']);
        $description = $validated['description'] . (! empty($validated['reference_no']) ? " (Ref: {$validated['reference_no']})" : '');

        DB::transaction(function () use ($validated, $account, $description) {
            $this->accountingService->recordIncome(
                $account->account_code,
                (float) $validated['amount'],
                $description,
                null,
                $validated['payment_method'],
                $validated['date']
            );
        });

        return redirect()->route('income-expense.index')
            ->with('success', 'Income recorded successfully!');
    }

    /**
     * Store expense transaction.
     */
    public function storeExpense(StoreIncomeExpenseRequest $request)
    {
        $validated = $request->validated();
        $account = Account::findOrFail($validated['account_id']);
        $description = $validated['description'] . (! empty($validated['reference_no']) ? " (Ref: {$validated['reference_no']})" : '');

        DB::transaction(function () use ($validated, $account, $description) {
            $this->accountingService->recordExpense(
                $account->account_code,
                (float) $validated['amount'],
                $description,
                null,
                $validated['payment_method'],
                $validated['date']
            );
        });

        return redirect()->route('income-expense.index')
            ->with('success', 'Expense recorded successfully!');
    }

    /**
     * Delete an income/expense entry (removes both legs of the balanced
     * posting sharing the same entry_no, then recalculates the accounts hit).
     */
    public function destroy(AccountingEntry $entry)
    {
        if (! in_array($entry->transaction_type, ['income', 'expense'])) {
            return redirect()->route('income-expense.index')
                ->with('error', 'Only income/expense entries can be deleted from this screen.');
        }

        DB::transaction(function () use ($entry) {
            $group = AccountingEntry::where('entry_no', $entry->entry_no)->get();
            $accountIds = $group->pluck('account_id')->unique();

            AccountingEntry::where('entry_no', $entry->entry_no)->delete();

            Account::whereIn('id', $accountIds)->get()->each->updateBalance();
        });

        return redirect()->route('income-expense.index')
            ->with('success', 'Entry deleted and account balances updated.');
    }

    /**
     * Get monthly summary.
     */
    private function getMonthlySummary($startDate, $endDate)
    {
        $months = collect();
        $start = \Carbon\Carbon::parse($startDate);
        $end = \Carbon\Carbon::parse($endDate);

        while ($start <= $end) {
            $monthStart = $start->copy()->startOfMonth();
            $monthEnd = $start->copy()->endOfMonth();

            $statement = $this->accountingService->getIncomeStatement($monthStart->format('Y-m-d'), $monthEnd->format('Y-m-d'));

            $months->push([
                'month' => $start->format('M Y'),
                'income' => $statement->total_income,
                'expense' => $statement->total_expense,
                'profit' => $statement->net_profit
            ]);

            $start->addMonth();
        }

        return $months;
    }

    /**
     * Get income/expense report.
     */
    public function report(Request $request)
    {
        $startDate = $request->filled('start_date') ? $request->start_date : now()->startOfMonth()->format('Y-m-d');
        $endDate = $request->filled('end_date') ? $request->end_date : now()->format('Y-m-d');

        $incomeTransactions = AccountingEntry::approved()
            ->whereHas('account', fn ($q) => $q->where('account_type', 'income'))
            ->whereBetween('date', [$startDate, $endDate])
            ->with(['account', 'oppositeAccount', 'creator'])
            ->get();

        $expenseTransactions = AccountingEntry::approved()
            ->whereHas('account', fn ($q) => $q->where('account_type', 'expense'))
            ->whereBetween('date', [$startDate, $endDate])
            ->with(['account', 'oppositeAccount', 'creator'])
            ->get();

        return view('income-expense.report', compact(
            'incomeTransactions',
            'expenseTransactions',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Get income/expense chart data (AJAX).
     */
    public function chartData(Request $request)
    {
        $year = $request->year ?? now()->year;

        $data = [];
        for ($month = 1; $month <= 12; $month++) {
            $startDate = now()->setDate($year, $month, 1)->startOfMonth();
            $endDate = now()->setDate($year, $month, 1)->endOfMonth();

            $statement = $this->accountingService->getIncomeStatement($startDate->format('Y-m-d'), $endDate->format('Y-m-d'));

            $data[] = [
                'month' => $startDate->format('M'),
                'income' => $statement->total_income,
                'expense' => $statement->total_expense
            ];
        }

        return response()->json($data);
    }
}
