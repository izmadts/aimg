<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccountController extends Controller
{
    /**
     * Display accounts list.
     */
    public function index(Request $request)
    {
        $query = Account::with(['parent', 'children']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('account_name', 'LIKE', "%{$search}%")
                  ->orWhere('account_code', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        // Type filter
        if ($request->filled('type')) {
            $query->where('account_type', $request->type);
        }

        // Parent filter
        if ($request->filled('parent')) {
            if ($request->parent == 'null') {
                $query->whereNull('parent_id');
            } else {
                $query->where('parent_id', $request->parent);
            }
        }

        $accounts = $query->orderBy('account_code')->paginate(20);

        // Get statistics
        $stats = [
            'total' => Account::count(),
            'active' => Account::where('is_active', true)->count(),
            'asset' => Account::where('account_type', 'asset')->count(),
            'liability' => Account::where('account_type', 'liability')->count(),
            'income' => Account::where('account_type', 'income')->count(),
            'expense' => Account::where('account_type', 'expense')->count(),
            'equity' => Account::where('account_type', 'equity')->count(),
        ];

        return view('accounts.index', compact('accounts', 'stats'));
    }

    /**
     * Show account details.
     */
    public function show(Account $account)
    {
        $account->load(['parent', 'children', 'creator']);
        
        // Get recent transactions
        $transactions = $account->transactions()
            ->with(['oppositeAccount', 'creator'])
            ->latest()
            ->paginate(20);

        // Get summary
        $summary = [
            'total_debit' => $account->transactions()->sum('debit'),
            'total_credit' => $account->transactions()->sum('credit'),
            'current_balance' => $account->balance,
            'month_debit' => $account->transactions()
                ->whereMonth('date', now()->month)
                ->whereYear('date', now()->year)
                ->sum('debit'),
            'month_credit' => $account->transactions()
                ->whereMonth('date', now()->month)
                ->whereYear('date', now()->year)
                ->sum('credit'),
        ];

        return view('accounts.show', compact('account', 'transactions', 'summary'));
    }

    /**
     * Create account form.
     */
    public function create()
    {
        $parents = Account::whereNull('parent_id')->get();
        return view('accounts.create', compact('parents'));
    }

    /**
     * Store account.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'account_name' => 'required|string|max:255',
            'account_type' => 'required|in:asset,liability,income,expense,equity',
            'parent_id' => 'nullable|exists:accounts,id',
            'opening_balance' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
            'description' => 'nullable|string|max:500'
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['opening_balance'] = $validated['opening_balance'] ?? 0;
        $validated['current_balance'] = $validated['opening_balance'];
        $validated['created_by'] = auth()->id();

        $account = Account::create($validated);

        return redirect()->route('accounts.index')
            ->with('success', "Account '{$account->account_name}' created successfully!");
    }

    /**
     * Edit account form.
     */
    public function edit(Account $account)
    {
        $parents = Account::whereNull('parent_id')
            ->where('id', '!=', $account->id)
            ->get();
        return view('accounts.edit', compact('account', 'parents'));
    }

    /**
     * Update account.
     */
    public function update(Request $request, Account $account)
    {
        $validated = $request->validate([
            'account_name' => 'required|string|max:255',
            'account_type' => 'required|in:asset,liability,income,expense,equity',
            'parent_id' => 'nullable|exists:accounts,id',
            'opening_balance' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
            'description' => 'nullable|string|max:500'
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['opening_balance'] = $validated['opening_balance'] ?? 0;

        $account->update($validated);

        return redirect()->route('accounts.index')
            ->with('success', "Account '{$account->account_name}' updated successfully!");
    }

    /**
     * Delete account.
     */
    public function destroy(Account $account)
    {
        if ($account->transactions()->exists()) {
            return redirect()->route('accounts.index')
                ->with('error', "Cannot delete account '{$account->account_name}' because it has transactions.");
        }

        if ($account->children()->exists()) {
            return redirect()->route('accounts.index')
                ->with('error', "Cannot delete account '{$account->account_name}' because it has sub-accounts.");
        }

        $account->delete();

        return redirect()->route('accounts.index')
            ->with('success', "Account '{$account->account_name}' deleted successfully!");
    }
}