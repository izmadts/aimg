@extends('layouts.app')

@section('title', 'Balance Sheet')

@section('content')
<div class="space-y-6">

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">⚖️ Balance Sheet</h1>
            <p class="text-sm text-gray-500">As of {{ now()->format('d-m-Y') }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <button onclick="window.print()" class="bg-gray-600 hover:bg-gray-700 text-white font-medium px-4 py-2 rounded-lg transition">
                <i class="fas fa-print mr-2"></i> Print
            </button>
            <a href="{{ route('accounting.trial-balance') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-lg transition">
                <i class="fas fa-book mr-2"></i> Trial Balance
            </a>
            <a href="{{ route('accounting.income-statement') }}" class="bg-green-600 hover:bg-green-700 text-white font-medium px-4 py-2 rounded-lg transition">
                <i class="fas fa-chart-line mr-2"></i> Income Statement
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Assets -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200 bg-blue-50">
                <h3 class="text-sm font-bold text-blue-700">ASSETS</h3>
            </div>
            <div class="p-4">
                @forelse($balanceSheet->assets as $asset)
                    <div class="flex justify-between py-2 border-b border-gray-100 text-sm">
                        <span>{{ $asset->account_name }} <span class="text-xs text-gray-400 font-mono">{{ $asset->account_code }}</span></span>
                        <span class="font-semibold">Rs. {{ number_format($asset->balance, 2) }}</span>
                    </div>
                @empty
                    <p class="text-gray-500 text-sm">No assets found.</p>
                @endforelse
                <div class="flex justify-between py-3 border-t-2 border-blue-500 font-bold text-blue-700">
                    <span>TOTAL ASSETS</span>
                    <span>Rs. {{ number_format($balanceSheet->total_assets, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Liabilities & Equity -->
        <div class="space-y-6">

            <!-- Liabilities -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200 bg-red-50">
                    <h3 class="text-sm font-bold text-red-700">LIABILITIES</h3>
                </div>
                <div class="p-4">
                    @forelse($balanceSheet->liabilities as $liability)
                        <div class="flex justify-between py-2 border-b border-gray-100 text-sm">
                            <span>{{ $liability->account_name }} <span class="text-xs text-gray-400 font-mono">{{ $liability->account_code }}</span></span>
                            <span class="font-semibold">Rs. {{ number_format($liability->balance, 2) }}</span>
                        </div>
                    @empty
                        <p class="text-gray-500 text-sm">No liabilities found.</p>
                    @endforelse
                    <div class="flex justify-between py-3 border-t-2 border-red-500 font-bold text-red-700">
                        <span>TOTAL LIABILITIES</span>
                        <span>Rs. {{ number_format($balanceSheet->total_liabilities, 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Equity -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200 bg-purple-50">
                    <h3 class="text-sm font-bold text-purple-700">EQUITY</h3>
                </div>
                <div class="p-4">
                    @forelse($balanceSheet->equity as $equity)
                        <div class="flex justify-between py-2 border-b border-gray-100 text-sm">
                            <span>{{ $equity->account_name }} <span class="text-xs text-gray-400 font-mono">{{ $equity->account_code }}</span></span>
                            <span class="font-semibold">Rs. {{ number_format($equity->balance, 2) }}</span>
                        </div>
                    @empty
                        <p class="text-gray-500 text-sm">No equity accounts found.</p>
                    @endforelse
                    <div class="flex justify-between py-2 border-b border-gray-100 text-sm">
                        <span>Current Earnings (Net Profit/Loss)</span>
                        <span class="font-semibold {{ $balanceSheet->current_earnings >= 0 ? '' : 'text-red-600' }}">
                            Rs. {{ number_format($balanceSheet->current_earnings, 2) }}
                        </span>
                    </div>
                    <div class="flex justify-between py-3 border-t-2 border-purple-500 font-bold text-purple-700">
                        <span>TOTAL EQUITY</span>
                        <span>Rs. {{ number_format($balanceSheet->total_equity, 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Summary -->
            <div class="bg-white rounded-lg shadow p-4 border-l-4 {{ $balanceSheet->is_balanced ? 'border-green-500' : 'border-red-500' }}">
                <div class="flex justify-between text-sm">
                    <span class="font-bold text-gray-700">Total Assets</span>
                    <span class="font-bold text-blue-600">Rs. {{ number_format($balanceSheet->total_assets, 2) }}</span>
                </div>
                <div class="flex justify-between text-sm mt-1">
                    <span class="font-bold text-gray-700">Total Liabilities + Equity</span>
                    <span class="font-bold text-purple-600">Rs. {{ number_format($balanceSheet->total_liabilities_and_equity, 2) }}</span>
                </div>
                <div class="flex justify-between mt-2 pt-2 border-t border-gray-200">
                    <span class="font-bold text-gray-700">Difference</span>
                    <span class="font-bold {{ $balanceSheet->is_balanced ? 'text-green-600' : 'text-red-600' }}">
                        Rs. {{ number_format($balanceSheet->total_assets - $balanceSheet->total_liabilities_and_equity, 2) }}
                    </span>
                </div>
                @if($balanceSheet->is_balanced)
                    <p class="text-xs text-green-600 mt-2">✅ Balance Sheet is balanced!</p>
                @else
                    <p class="text-xs text-red-600 mt-2">⚠️ Balance Sheet is not balanced!</p>
                @endif
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
