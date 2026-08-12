@extends('layouts.app')

@section('title', 'Account: ' . $account->account_name)

@section('content')
<div class="space-y-6">
    
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $account->account_name }}</h1>
            <p class="text-sm text-gray-500">Account Details</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('accounts.edit', $account) }}" 
               class="bg-yellow-600 hover:bg-yellow-700 text-white font-medium px-4 py-2 rounded-lg transition">
                <i class="fas fa-edit mr-2"></i> Edit
            </a>
            <a href="{{ route('accounts.index') }}" 
               class="bg-gray-500 hover:bg-gray-600 text-white font-medium px-4 py-2 rounded-lg transition">
                <i class="fas fa-arrow-left mr-2"></i> Back
            </a>
        </div>
    </div>

    <!-- Account Info -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left Column -->
        <div class="lg:col-span-1 space-y-6">
            
            <div class="bg-white rounded-lg shadow p-6">
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Account Code</span>
                        <span class="font-mono font-semibold">{{ $account->account_code }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Account Type</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            @if($account->account_type === 'asset') bg-blue-100 text-blue-800
                            @elseif($account->account_type === 'liability') bg-red-100 text-red-800
                            @elseif($account->account_type === 'income') bg-green-100 text-green-800
                            @elseif($account->account_type === 'expense') bg-orange-100 text-orange-800
                            @elseif($account->account_type === 'equity') bg-purple-100 text-purple-800
                            @endif">
                            {{ $account->type_label }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Parent Account</span>
                        <span class="font-semibold">{{ $account->parent->account_name ?? 'None' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Status</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            @if($account->is_active) bg-green-100 text-green-800
                            @else bg-red-100 text-red-800
                            @endif">
                            {{ $account->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                    <div class="flex justify-between border-t pt-2">
                        <span class="text-sm font-medium">Opening Balance</span>
                        <span class="font-bold">Rs. {{ number_format($account->opening_balance, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm font-medium">Current Balance</span>
                        <span class="font-bold text-lg {{ $account->current_balance >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            Rs. {{ number_format($account->current_balance, 2) }}
                        </span>
                    </div>
                    @if($account->description)
                    <div class="border-t pt-2">
                        <p class="text-sm text-gray-500">Description</p>
                        <p class="text-sm">{{ $account->description }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Summary -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">Transaction Summary</h3>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Total Debit</span>
                        <span class="font-semibold text-red-600">Rs. {{ number_format($summary['total_debit'], 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Total Credit</span>
                        <span class="font-semibold text-green-600">Rs. {{ number_format($summary['total_credit'], 2) }}</span>
                    </div>
                    <div class="flex justify-between border-t pt-2">
                        <span class="text-sm text-gray-500">This Month Debit</span>
                        <span class="font-semibold text-red-600">Rs. {{ number_format($summary['month_debit'], 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">This Month Credit</span>
                        <span class="font-semibold text-green-600">Rs. {{ number_format($summary['month_credit'], 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Transactions -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-sm font-semibold text-gray-700">
                        <i class="fas fa-list mr-2 text-gray-400"></i> Transaction History
                    </h3>
                    <span class="text-xs text-gray-500">{{ $transactions->total() }} transactions</span>
                </div>
                <div class="overflow-x-auto max-h-96 overflow-y-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 sticky top-0">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Transaction #</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Debit</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Credit</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Opposite Account</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($transactions as $transaction)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-3 text-sm">{{ $transaction->date->format('d-m-Y') }}</td>
                                <td class="px-6 py-3 text-sm font-mono">{{ $transaction->transaction_no }}</td>
                                <td class="px-6 py-3">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @if($transaction->transaction_type === 'income') bg-green-100 text-green-800
                                        @elseif($transaction->transaction_type === 'expense') bg-red-100 text-red-800
                                        @elseif($transaction->transaction_type === 'transfer') bg-blue-100 text-blue-800
                                        @elseif($transaction->transaction_type === 'sale') bg-green-100 text-green-800
                                        @elseif($transaction->transaction_type === 'purchase') bg-orange-100 text-orange-800
                                        @else bg-gray-100 text-gray-800
                                        @endif">
                                        {{ $transaction->type_label }}
                                    </span>
                                </td>
                                <td class="px-6 py-3 text-sm">{{ Str::limit($transaction->description, 20) }}</td>
                                <td class="px-6 py-3 text-right text-sm text-red-600">
                                    @if($transaction->debit > 0)
                                        Rs. {{ number_format($transaction->debit, 2) }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-6 py-3 text-right text-sm text-green-600">
                                    @if($transaction->credit > 0)
                                        Rs. {{ number_format($transaction->credit, 2) }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-6 py-3 text-sm">{{ $transaction->oppositeAccount->account_name ?? 'N/A' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                    <i class="fas fa-inbox text-3xl mb-2 text-gray-300 block"></i>
                                    No transactions found.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $transactions->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection