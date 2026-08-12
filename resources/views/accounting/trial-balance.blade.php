@extends('layouts.app')

@section('title', 'Trial Balance')

@section('content')
<div class="space-y-6">
    
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">📊 Trial Balance</h1>
            <p class="text-sm text-gray-500">As of {{ now()->format('d-m-Y') }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <button onclick="window.print()" class="bg-gray-600 hover:bg-gray-700 text-white font-medium px-4 py-2 rounded-lg transition">
                <i class="fas fa-print mr-2"></i> Print
            </button>
            <a href="{{ route('accounting.income-statement') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-lg transition">
                <i class="fas fa-chart-line mr-2"></i> Income Statement
            </a>
            <a href="{{ route('accounting.balance-sheet') }}" class="bg-green-600 hover:bg-green-700 text-white font-medium px-4 py-2 rounded-lg transition">
                <i class="fas fa-balance-scale mr-2"></i> Balance Sheet
            </a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-blue-500">
            <p class="text-xs text-gray-500">Total Accounts</p>
            <p class="text-2xl font-bold">{{ $trialBalance->count() }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-green-500">
            <p class="text-xs text-gray-500">Total Debit</p>
            <p class="text-2xl font-bold text-green-600">Rs. {{ number_format($trialBalance->sum('debit'), 2) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-red-500">
            <p class="text-xs text-gray-500">Total Credit</p>
            <p class="text-2xl font-bold text-red-600">Rs. {{ number_format($trialBalance->sum('credit'), 2) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-purple-500">
            <p class="text-xs text-gray-500">Difference</p>
            <p class="text-2xl font-bold text-purple-600">
                Rs. {{ number_format($trialBalance->sum('debit') - $trialBalance->sum('credit'), 2) }}
            </p>
        </div>
    </div>

    <!-- Trial Balance Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Account Code</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Account Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Debit</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Credit</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Balance</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @php
                        $totalDebit = 0;
                        $totalCredit = 0;
                        $totalBalance = 0;
                    @endphp
                    @forelse($trialBalance as $index => $account)
                        @php
                            $totalDebit += $account['debit'];
                            $totalCredit += $account['credit'];
                            $totalBalance += $account['balance'];
                        @endphp
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-3 text-sm text-gray-500">{{ $loop->iteration }}</td>
                            <td class="px-6 py-3 text-sm font-mono">{{ $account['account_code'] }}</td>
                            <td class="px-6 py-3 text-sm">{{ $account['account_name'] }}</td>
                            <td class="px-6 py-3">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{--@if($account['account_type'] === 'asset') bg-blue-100 text-blue-800
                                    @elseif($account['account_type'] === 'liability') bg-red-100 text-red-800
                                    @elseif($account['account_type'] === 'income') bg-green-100 text-green-800
                                    @elseif($account['account_type'] === 'expense') bg-orange-100 text-orange-800
                                    @elseif($account['account_type'] === 'equity') bg-purple-100 text-purple-800
                                    @endif--}}">
                                    {{--{{ ucfirst($account['account_type']) }}--}}
                                </span>
                            </td>
                            <td class="px-6 py-3 text-right text-sm {{ $account['debit'] > 0 ? 'text-red-600 font-semibold' : '' }}">
                                {{ $account['debit'] > 0 ? 'Rs. ' . number_format($account['debit'], 2) : '-' }}
                            </td>
                            <td class="px-6 py-3 text-right text-sm {{ $account['credit'] > 0 ? 'text-green-600 font-semibold' : '' }}">
                                {{ $account['credit'] > 0 ? 'Rs. ' . number_format($account['credit'], 2) : '-' }}
                            </td>
                            <td class="px-6 py-3 text-right text-sm font-semibold {{ $account['balance'] >= 0 ? 'text-blue-600' : 'text-red-600' }}">
                                Rs. {{ number_format($account['balance'], 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                <i class="fas fa-inbox text-3xl mb-2 text-gray-300 block"></i>
                                No accounts found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-gray-50 font-bold">
                    <tr>
                        <td colspan="4" class="px-6 py-3 text-right">TOTAL</td>
                        <td class="px-6 py-3 text-right text-red-600">Rs. {{ number_format($totalDebit, 2) }}</td>
                        <td class="px-6 py-3 text-right text-green-600">Rs. {{ number_format($totalCredit, 2) }}</td>
                        <td class="px-6 py-3 text-right text-blue-600">Rs. {{ number_format($totalBalance, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Chart Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-sm font-semibold text-gray-700 mb-4">
                <i class="fas fa-chart-pie mr-2 text-gray-400"></i> Accounts by Type
            </h3>
            <div class="space-y-2">
                @php
                    $types = $trialBalance->groupBy('account_type')->map(function ($items) {
                        return $items->sum('balance');
                    });
                @endphp
                @foreach($types as $type => $amount)
                    <div>
                        <div class="flex justify-between text-sm">
                            <span class="capitalize">{{ $type }}</span>
                            <span class="font-semibold">Rs. {{ number_format($amount, 2) }}</span>
                        </div>
                        @php
                            $total = $types->sum();
                            $percentage = $total > 0 ? ($amount / $total) * 100 : 0;
                        @endphp
                        <div class="w-full bg-gray-200 rounded-full h-1.5 mt-1">
                            <div class="rounded-full h-1.5 
                                @if($type === 'asset') bg-blue-500
                                @elseif($type === 'liability') bg-red-500
                                @elseif($type === 'income') bg-green-500
                                @elseif($type === 'expense') bg-orange-500
                                @else bg-purple-500
                                @endif" 
                                style="width: {{ min($percentage, 100) }}%">
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-sm font-semibold text-gray-700 mb-4">
                <i class="fas fa-balance-scale mr-2 text-gray-400"></i> Account Balance Summary
            </h3>
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-green-50 p-4 rounded-lg text-center">
                    <p class="text-xs text-gray-500">Total Debit</p>
                    <p class="text-xl font-bold text-green-600">Rs. {{ number_format($totalDebit, 2) }}</p>
                </div>
                <div class="bg-red-50 p-4 rounded-lg text-center">
                    <p class="text-xs text-gray-500">Total Credit</p>
                    <p class="text-xl font-bold text-red-600">Rs. {{ number_format($totalCredit, 2) }}</p>
                </div>
                <div class="bg-blue-50 p-4 rounded-lg text-center col-span-2">
                    <p class="text-xs text-gray-500">Net Balance</p>
                    <p class="text-xl font-bold text-blue-600">Rs. {{ number_format($totalBalance, 2) }}</p>
                    @if($totalBalance == 0)
                        <p class="text-xs text-green-600 mt-1">✅ Trial Balance is balanced!</p>
                    @else
                        <p class="text-xs text-red-600 mt-1">⚠️ Trial Balance is not balanced!</p>
                    @endif
                </div>
            </div>
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