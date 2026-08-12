@extends('layouts.app')

@section('title', 'Statement - ' . $customer->name)

@section('content')
<div class="space-y-6">
    
    <!-- Page Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Customer Statement</h1>
            <p class="text-sm text-gray-500">{{ $customer->name }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <button onclick="window.print()" class="bg-gray-600 hover:bg-gray-700 text-white font-medium px-4 py-2 rounded-lg transition">
                <i class="fas fa-print mr-2"></i> Print
            </button>
            <a href="{{ route('customers.show', $customer) }}" class="text-gray-600 hover:text-gray-900">
                <i class="fas fa-arrow-left mr-2"></i> Back
            </a>
        </div>
    </div>

    <!-- Summary -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-xs text-gray-500">Total Sales</p>
            <p class="text-xl font-bold">{{ $sales->count() }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-xs text-gray-500">Total Amount</p>
            <p class="text-xl font-bold text-green-600">Rs. {{ number_format($sales->sum('grand_total'), 2) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-xs text-gray-500">Balance Due</p>
            <p class="text-xl font-bold text-red-600">Rs. {{ number_format($balance, 2) }}</p>
        </div>
    </div>

    <!-- Statement Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Invoice #</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Items</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Paid</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Balance</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($sales as $sale)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-3 text-sm">{{ $sale->date->format('d-m-Y') }}</td>
                        <td class="px-6 py-3 text-sm font-medium">
                            <a href="{{ route('sales.show', $sale) }}" class="text-blue-600 hover:underline">
                                {{ $sale->invoice_no }}
                            </a>
                        </td>
                        <td class="px-6 py-3 text-sm">{{ $sale->items->count() }}</td>
                        <td class="px-6 py-3 text-right font-semibold">Rs. {{ number_format($sale->grand_total, 2) }}</td>
                        <td class="px-6 py-3 text-right">Rs. {{ number_format($sale->amount_paid, 2) }}</td>
                        <td class="px-6 py-3 text-right font-semibold text-red-600">Rs. {{ number_format($sale->balance_due, 2) }}</td>
                        <td class="px-6 py-3 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($sale->payment_status === 'paid') bg-green-100 text-green-800
                                @elseif($sale->payment_status === 'partial') bg-yellow-100 text-yellow-800
                                @else bg-red-100 text-red-800
                                @endif">
                                {{ ucfirst($sale->payment_status) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-gray-500">No sales found.</td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-gray-50 font-bold">
                    <tr>
                        <td colspan="3" class="px-6 py-3 text-right">Totals</td>
                        <td class="px-6 py-3 text-right">Rs. {{ number_format($sales->sum('grand_total'), 2) }}</td>
                        <td class="px-6 py-3 text-right">Rs. {{ number_format($sales->sum('amount_paid'), 2) }}</td>
                        <td class="px-6 py-3 text-right text-red-600">Rs. {{ number_format($balance, 2) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
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