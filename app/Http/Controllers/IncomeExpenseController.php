<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IncomeExpenseController extends Controller
{
    /**
     * Display income/expense dashboard.
     */
    public function index(Request $request)
    {
        // Get date range
        $startDate = $request->filled('start_date') ? $request->start_date : now()->startOfMonth()->format('Y-m-d');
        $endDate = $request->filled('end_date') ? $request->end_date : now()->format('Y-m-d');

        // Get income accounts
        $incomeAccounts = Account::income()->active()->get();
        $expenseAccounts = Account::expense()->active()->get();

        // Get income transactions
        $incomeTransactions = Transaction::income()
            ->whereBetween('date', [$startDate, $endDate])
            ->where('status', 'approved')
            ->with(['account', 'oppositeAccount'])
            ->get();

        // Get expense transactions
        $expenseTransactions = Transaction::expense()
            ->whereBetween('date', [$startDate, $endDate])
            ->where('status', 'approved')
            ->with(['account', 'oppositeAccount'])
            ->get();

        // ✅ Calculate totals using debit/credit
        $totalIncome = $incomeTransactions->sum('debit'); // Income has debit
        $totalExpense = $expenseTransactions->sum('credit'); // Expense has credit
        $netProfit = $totalIncome - $totalExpense;

        // Income by account
        $incomeByAccount = $incomeTransactions->groupBy('account_id')->map(function ($items) {
            return [
                'account' => $items->first()->account,
                'total' => $items->sum('debit') // ✅ Use debit
            ];
        });

        // Expense by account
        $expenseByAccount = $expenseTransactions->groupBy('account_id')->map(function ($items) {
            return [
                'account' => $items->first()->account,
                'total' => $items->sum('credit') // ✅ Use credit
            ];
        });

        // Monthly summary
        $monthlySummary = $this->getMonthlySummary($startDate, $endDate);

        // Recent transactions
        $recentTransactions = Transaction::whereIn('transaction_type', ['income', 'expense'])
            ->where('status', 'approved')
            ->with(['account', 'oppositeAccount', 'creator'])
            ->latest()
            ->limit(20)
            ->get();

        return view('income-expense.index', compact(
            'incomeAccounts',
            'expenseAccounts',
            'incomeTransactions',
            'expenseTransactions',
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
        $bankAccounts = Account::asset()->active()->get();
        return view('income-expense.create-income', compact('accounts', 'bankAccounts'));
    }

    /**
     * Show expense entry form.
     */
    public function createExpense()
    {
        $accounts = Account::expense()->active()->get();
        $bankAccounts = Account::asset()->active()->get();
        return view('income-expense.create-expense', compact('accounts', 'bankAccounts'));
    }

    /**
     * Store income transaction.
     */
    public function storeIncome(Request $request)
    {
        $validated = $request->validate([
            'account_id' => 'required|exists:accounts,id',
            'opposite_account_id' => 'required|exists:accounts,id',
            'date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:500',
            'reference_no' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500'
        ]);

        DB::transaction(function () use ($validated) {
            // Create transaction - Income = Debit
            $transaction = Transaction::create([
                'transaction_no' => Transaction::generateTransactionNo('income'),
                'transaction_type' => 'income',
                'account_id' => $validated['account_id'],
                'opposite_account_id' => $validated['opposite_account_id'],
                'date' => $validated['date'],
                'debit' => $validated['amount'],  // ✅ Income = Debit
                'credit' => 0,
                'description' => $validated['description'],
                'reference_no' => $validated['reference_no'],
                'notes' => $validated['notes'],
                'created_by' => auth()->id(),
                'status' => 'approved'
            ]);

            // Update account balances
            $account = Account::find($validated['account_id']);
            $account->updateBalance();

            $oppositeAccount = Account::find($validated['opposite_account_id']);
            $oppositeAccount->updateBalance();
        });

        return redirect()->route('income-expense.index')
            ->with('success', 'Income recorded successfully!');
    }

    /**
     * Store expense transaction.
     */
    public function storeExpense(Request $request)
    {
        $validated = $request->validate([
            'account_id' => 'required|exists:accounts,id',
            'opposite_account_id' => 'required|exists:accounts,id',
            'date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:500',
            'reference_no' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500'
        ]);

        DB::transaction(function () use ($validated) {
            // Create transaction - Expense = Credit
            $transaction = Transaction::create([
                'transaction_no' => Transaction::generateTransactionNo('expense'),
                'transaction_type' => 'expense',
                'account_id' => $validated['account_id'],
                'opposite_account_id' => $validated['opposite_account_id'],
                'date' => $validated['date'],
                'debit' => 0,
                'credit' => $validated['amount'], // ✅ Expense = Credit
                'description' => $validated['description'],
                'reference_no' => $validated['reference_no'],
                'notes' => $validated['notes'],
                'created_by' => auth()->id(),
                'status' => 'approved'
            ]);

            // Update account balances
            $account = Account::find($validated['account_id']);
            $account->updateBalance();

            $oppositeAccount = Account::find($validated['opposite_account_id']);
            $oppositeAccount->updateBalance();
        });

        return redirect()->route('income-expense.index')
            ->with('success', 'Expense recorded successfully!');
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

            // ✅ Income = sum of debit, Expense = sum of credit
            $income = Transaction::income()
                ->whereBetween('date', [$monthStart, $monthEnd])
                ->where('status', 'approved')
                ->sum('debit');

            $expense = Transaction::expense()
                ->whereBetween('date', [$monthStart, $monthEnd])
                ->where('status', 'approved')
                ->sum('credit');

            $months->push([
                'month' => $start->format('M Y'),
                'income' => $income,
                'expense' => $expense,
                'profit' => $income - $expense
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

        $incomeTransactions = Transaction::income()
            ->whereBetween('date', [$startDate, $endDate])
            ->where('status', 'approved')
            ->with(['account', 'oppositeAccount', 'creator'])
            ->get();

        $expenseTransactions = Transaction::expense()
            ->whereBetween('date', [$startDate, $endDate])
            ->where('status', 'approved')
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

            // ✅ Income = sum of debit, Expense = sum of credit
            $income = Transaction::income()
                ->whereBetween('date', [$startDate, $endDate])
                ->where('status', 'approved')
                ->sum('debit');

            $expense = Transaction::expense()
                ->whereBetween('date', [$startDate, $endDate])
                ->where('status', 'approved')
                ->sum('credit');

            $data[] = [
                'month' => $startDate->format('M'),
                'income' => $income,
                'expense' => $expense
            ];
        }

        return response()->json($data);
    }
}