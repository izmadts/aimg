@extends('layouts.app')

@section('title', 'Income & Expense')

@section('content')
<div class="space-y-6">
    
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">💰 Income & Expense</h1>
            <p class="text-sm text-gray-500">Track your income and expenses</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('income-expense.create-income') }}" 
               class="bg-green-600 hover:bg-green-700 text-white font-medium px-4 py-2 rounded-lg transition">
                <i class="fas fa-plus mr-2"></i> Add Income
            </a>
            <a href="{{ route('income-expense.create-expense') }}" 
               class="bg-red-600 hover:bg-red-700 text-white font-medium px-4 py-2 rounded-lg transition">
                <i class="fas fa-minus mr-2"></i> Add Expense
            </a>
            <a href="{{ route('income-expense.report') }}" 
               class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-lg transition">
                <i class="fas fa-file-invoice mr-2"></i> Report
            </a>
        </div>
    </div>

    <!-- Filter -->
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
                <a href="{{ route('income-expense.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium px-4 py-2 rounded-lg transition">
                    <i class="fas fa-times mr-2"></i> Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-green-500">
            <p class="text-xs text-gray-500">Total Income</p>
            <p class="text-2xl font-bold text-green-600">Rs. {{ number_format($totalIncome, 2) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-red-500">
            <p class="text-xs text-gray-500">Total Expense</p>
            <p class="text-2xl font-bold text-red-600">Rs. {{ number_format($totalExpense, 2) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-blue-500">
            <p class="text-xs text-gray-500">Net Profit</p>
            <p class="text-2xl font-bold {{ $netProfit >= 0 ? 'text-blue-600' : 'text-red-600' }}">
                Rs. {{ number_format($netProfit, 2) }}
            </p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-purple-500">
            <p class="text-xs text-gray-500">Recent Transactions</p>
            <p class="text-2xl font-bold">{{ $recentTransactions->count() }}</p>
        </div>
    </div>

    <!-- Income by Account -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-sm font-semibold text-gray-700 mb-4">
                <i class="fas fa-arrow-up text-green-600 mr-2"></i> Income by Account
            </h3>
            @if($incomeByAccount->count() > 0)
                <div class="space-y-2">
                    @foreach($incomeByAccount as $item)
                        <div>
                            <div class="flex justify-between text-sm">
                                <span>{{ $item['account']->account_name ?? 'N/A' }}</span>
                                <span class="font-semibold text-green-600">Rs. {{ number_format($item['total'], 2) }}</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2 mt-1">
                                @php
                                    $percentage = $totalIncome > 0 ? ($item['total'] / $totalIncome) * 100 : 0;
                                @endphp
                                <div class="bg-green-500 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500 text-sm">No income recorded.</p>
            @endif
        </div>

        <!-- Expense by Account -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-sm font-semibold text-gray-700 mb-4">
                <i class="fas fa-arrow-down text-red-600 mr-2"></i> Expense by Account
            </h3>
            @if($expenseByAccount->count() > 0)
                <div class="space-y-2">
                    @foreach($expenseByAccount as $item)
                        <div>
                            <div class="flex justify-between text-sm">
                                <span>{{ $item['account']->account_name ?? 'N/A' }}</span>
                                <span class="font-semibold text-red-600">Rs. {{ number_format($item['total'], 2) }}</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2 mt-1">
                                @php
                                    $percentage = $totalExpense > 0 ? ($item['total'] / $totalExpense) * 100 : 0;
                                @endphp
                                <div class="bg-red-500 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500 text-sm">No expense recorded.</p>
            @endif
        </div>
    </div>

    <!-- Monthly Summary -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-sm font-semibold text-gray-700 mb-4">
            <i class="fas fa-calendar-alt mr-2 text-gray-400"></i> Monthly Summary
        </h3>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Month</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Income</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Expense</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Profit/Loss</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($monthlySummary as $month)
                    <tr>
                        <td class="px-4 py-2 text-sm font-medium">{{ $month['month'] }}</td>
                        <td class="px-4 py-2 text-right text-sm text-green-600">Rs. {{ number_format($month['income'], 2) }}</td>
                        <td class="px-4 py-2 text-right text-sm text-red-600">Rs. {{ number_format($month['expense'], 2) }}</td>
                        <td class="px-4 py-2 text-right text-sm font-bold {{ $month['profit'] >= 0 ? 'text-blue-600' : 'text-red-600' }}">
                            Rs. {{ number_format($month['profit'], 2) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-sm font-semibold text-gray-700">
                <i class="fas fa-clock mr-2 text-gray-400"></i> Recent Transactions
            </h3>
        </div>
        <div class="overflow-x-auto max-h-80 overflow-y-auto">
            <table class="w-full">
                <thead class="bg-gray-50 sticky top-0">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Account</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reference</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($recentTransactions as $transaction)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-3 text-sm">{{ $transaction->date->format('d-m-Y') }}</td>
                        <td class="px-6 py-3">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($transaction->transaction_type === 'income') bg-green-100 text-green-800
                                @else bg-red-100 text-red-800
                                @endif">
                                {{ $transaction->type_label }}
                            </span>
                        </td>
                        <td class="px-6 py-3 text-sm">{{ $transaction->account->account_name ?? 'N/A' }}</td>
                        <td class="px-6 py-3 text-sm">{{ Str::limit($transaction->description, 30) }}</td>
                        <td class="px-6 py-3 text-right font-bold {{ $transaction->transaction_type === 'income' ? 'text-green-600' : 'text-red-600' }}">
                            Rs. {{ number_format($transaction->debit ?: $transaction->credit, 2) }}
                        </td>
                        <td class="px-6 py-3 text-xs font-mono">{{ $transaction->entry_no ?? '—' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">No transactions found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection