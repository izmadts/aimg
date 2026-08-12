@extends('layouts.app')

@section('title', 'Income Statement')

@section('content')
<div class="space-y-6">
    
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">📈 Income Statement</h1>
            <p class="text-sm text-gray-500">
                {{ \Carbon\Carbon::parse($startDate)->format('d-m-Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('d-m-Y') }}
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            <button onclick="window.print()" class="bg-gray-600 hover:bg-gray-700 text-white font-medium px-4 py-2 rounded-lg transition">
                <i class="fas fa-print mr-2"></i> Print
            </button>
            <a href="{{ route('accounting.trial-balance') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-lg transition">
                <i class="fas fa-book mr-2"></i> Trial Balance
            </a>
            <a href="{{ route('accounting.balance-sheet') }}" class="bg-green-600 hover:bg-green-700 text-white font-medium px-4 py-2 rounded-lg transition">
                <i class="fas fa-balance-scale mr-2"></i> Balance Sheet
            </a>
        </div>
    </div>

    <!-- Date Filter -->
    <div class="bg-white rounded-lg shadow p-4">
        <form method="GET" class="flex flex-wrap gap-4 items-end">
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Start Date</label>
                <input type="date" name="start_date" value="{{ $startDate }}"
                       class="rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">End Date</label>
                <input type="date" name="end_date" value="{{ $endDate }}"
                       class="rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-lg transition">
                    <i class="fas fa-search mr-2"></i> Filter
                </button>
                <a href="{{ route('accounting.income-statement') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium px-4 py-2 rounded-lg transition">
                    <i class="fas fa-times mr-2"></i> Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-green-500">
            <p class="text-xs text-gray-500">Total Income</p>
            <p class="text-2xl font-bold text-green-600">Rs. {{ number_format($incomeStatement->total_income, 2) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-red-500">
            <p class="text-xs text-gray-500">Total Expense</p>
            <p class="text-2xl font-bold text-red-600">Rs. {{ number_format($incomeStatement->total_expense, 2) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-blue-500">
            <p class="text-xs text-gray-500">Net Profit / Loss</p>
            <p class="text-2xl font-bold {{ $incomeStatement->net_profit >= 0 ? 'text-blue-600' : 'text-red-600' }}">
                Rs. {{ number_format($incomeStatement->net_profit, 2) }}
            </p>
        </div>
    </div>

    <!-- Income Statement Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Account</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <!-- Income Section -->
                    <tr class="bg-green-50">
                        <td colspan="3" class="px-6 py-3 font-bold text-green-700">INCOME</td>
                    </tr>
                    @php
                        $incomeAccounts = \App\Models\AccountingEntry::where('status', 'approved')
                            ->whereIn('transaction_type', ['sale', 'income', 'damage'])
                            ->when($startDate && $endDate, function ($q) use ($startDate, $endDate) {
                                return $q->whereBetween('date', [$startDate, $endDate]);
                            })
                            ->with('account')
                            ->get()
                            ->groupBy('account_id');
                    @endphp
                    @forelse($incomeAccounts as $accountId => $entries)
                        @php
                            $account = $entries->first()->account;
                            $total = $entries->sum('credit');
                        @endphp
                        <tr>
                            <td class="px-6 py-3 text-sm text-gray-500"></td>
                            <td class="px-6 py-3 text-sm">{{ $account->account_name ?? 'N/A' }}</td>
                            <td class="px-6 py-3 text-right font-semibold text-green-600">
                                Rs. {{ number_format($total, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-3 text-center text-gray-500">No income found.</td>
                        </tr>
                    @endforelse
                    <tr class="bg-green-50 font-bold">
                        <td colspan="2" class="px-6 py-3 text-right">Total Income</td>
                        <td class="px-6 py-3 text-right text-green-700">Rs. {{ number_format($incomeStatement->total_income, 2) }}</td>
                    </tr>

                    <!-- Expense Section -->
                    <tr class="bg-red-50">
                        <td colspan="3" class="px-6 py-3 font-bold text-red-700">EXPENSES</td>
                    </tr>
                    @php
                        $expenseAccounts = \App\Models\AccountingEntry::where('status', 'approved')
                            ->whereIn('transaction_type', ['purchase', 'expense', 'salary'])
                            ->when($startDate && $endDate, function ($q) use ($startDate, $endDate) {
                                return $q->whereBetween('date', [$startDate, $endDate]);
                            })
                            ->with('account')
                            ->get()
                            ->groupBy('account_id');
                    @endphp
                    @forelse($expenseAccounts as $accountId => $entries)
                        @php
                            $account = $entries->first()->account;
                            $total = $entries->sum('debit');
                        @endphp
                        <tr>
                            <td class="px-6 py-3 text-sm text-gray-500"></td>
                            <td class="px-6 py-3 text-sm">{{ $account->account_name ?? 'N/A' }}</td>
                            <td class="px-6 py-3 text-right font-semibold text-red-600">
                                Rs. {{ number_format($total, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-3 text-center text-gray-500">No expenses found.</td>
                        </tr>
                    @endforelse
                    <tr class="bg-red-50 font-bold">
                        <td colspan="2" class="px-6 py-3 text-right">Total Expenses</td>
                        <td class="px-6 py-3 text-right text-red-700">Rs. {{ number_format($incomeStatement->total_expense, 2) }}</td>
                    </tr>

                    <!-- Net Profit -->
                    <tr class="bg-blue-50 font-bold text-lg">
                        <td colspan="2" class="px-6 py-4 text-right">NET PROFIT / LOSS</td>
                        <td class="px-6 py-4 text-right {{ $incomeStatement->net_profit >= 0 ? 'text-blue-700' : 'text-red-700' }}">
                            Rs. {{ number_format($incomeStatement->net_profit, 2) }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
@media print {
    .no-print { display: none !important; }
    .shadow { box-shadow: none !important; }
}
</style>
@endsection