@extends('layouts.app')

@section('title', 'Cylinder: ' . $cylinder->cylinder_number)

@section('content')
<div class="space-y-6">
    
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $cylinder->cylinder_number }}</h1>
            <p class="text-sm text-gray-500">Cylinder Details</p>
        </div>
        <div class="flex flex-wrap gap-2">
            @if($cylinder->issued_quantity == 0)
                <a href="{{ route('cylinders.edit', $cylinder) }}" class="bg-yellow-600 hover:bg-yellow-700 text-white font-medium px-4 py-2 rounded-lg transition">
                    <i class="fas fa-edit mr-2"></i> Edit
                </a>
            @endif
            <a href="{{ route('cylinders.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-medium px-4 py-2 rounded-lg transition">
                <i class="fas fa-arrow-left mr-2"></i> Back
            </a>
        </div>
    </div>

    <!-- Status Badge -->
    <div class="flex flex-wrap gap-2">
        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
            @if($cylinder->status === 'in_house') bg-blue-100 text-blue-800
            @elseif($cylinder->status === 'partial_issued') bg-yellow-100 text-yellow-800
            @elseif($cylinder->status === 'all_issued') bg-orange-100 text-orange-800
            @elseif($cylinder->status === 'out_of_stock') bg-red-100 text-red-800
            @elseif($cylinder->status === 'under_maintenance') bg-purple-100 text-purple-800
            @else bg-gray-100 text-gray-800
            @endif">
            <i class="fas fa-circle text-xs mr-1.5"></i> {{ $cylinder->status_label }}
        </span>
        @if($issuedDetails->count() > 0)
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800">
                <i class="fas fa-user mr-1.5"></i> Issued to {{ $issuedDetails->count() }} customer(s)
            </span>
        @endif
        @if($cylinder->stock_quantity > 0)
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                <i class="fas fa-boxes mr-1.5"></i> Stock: {{ $cylinder->stock_quantity }}
            </span>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left Column -->
        <div class="lg:col-span-1 space-y-6">
            
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">📋 Basic Information</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Cylinder Number</span>
                        <span class="font-mono font-semibold">{{ $cylinder->cylinder_number }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Gas Product</span>
                        <span class="font-semibold">{{ $cylinder->gasProduct->name ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Type</span>
                        <span class="font-semibold">{{ $cylinder->type }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Manufacturer</span>
                        <span class="font-semibold">{{ $cylinder->manufacturer ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Capacity</span>
                        <span class="font-semibold">{{ $cylinder->capacity }} {{ $cylinder->gasProduct->uom ?? '' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Tare Weight</span>
                        <span class="font-semibold">{{ $cylinder->tare_weight ?? 'N/A' }} KG</span>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">💰 Price Details</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Purchase Price</span>
                        <span class="font-semibold">Rs. {{ number_format($cylinder->purchase_price, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Sale Price</span>
                        <span class="font-semibold text-green-600">Rs. {{ number_format($cylinder->sale_price, 2) }}</span>
                    </div>
                    <div class="flex justify-between border-t pt-2">
                        <span class="text-gray-500">Total Asset Value</span>
                        <span class="font-bold text-blue-600">Rs. {{ number_format($cylinder->total_asset_value, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Total Sale Value</span>
                        <span class="font-bold text-green-600">Rs. {{ number_format($cylinder->total_sale_value, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Total Profit</span>
                        <span class="font-bold text-purple-600">Rs. {{ number_format($cylinder->total_profit, 2) }}</span>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">📊 Stock Summary</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Total Stock</span>
                        <span class="font-bold">{{ $cylinder->stock_quantity }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Issued</span>
                        <span class="font-bold text-yellow-600">{{ $cylinder->issued_quantity }}</span>
                    </div>
                    <div class="flex justify-between border-t pt-2">
                        <span class="text-gray-500">Filled (available)</span>
                        <span class="font-bold text-green-600">{{ $cylinder->filled_quantity }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Empty</span>
                        <span class="font-bold text-amber-600">{{ $cylinder->empty_quantity }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Issued Details -->
            @if($issuedDetails->count() > 0)
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-sm font-semibold text-gray-700">
                        <i class="fas fa-users text-gray-400 mr-2"></i> Issued to Customers
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Quantity</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Issue Date</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Days Out</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($issuedDetails as $detail)
                            <tr>
                                <td class="px-6 py-3">{{ $detail->customer->name ?? 'N/A' }}</td>
                                <td class="px-6 py-3 text-center">{{ $detail->quantity }}</td>
                                <td class="px-6 py-3 text-center">{{ $detail->issue_date->format('d-m-Y') }}</td>
                                <td class="px-6 py-3 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                        @if($detail->days_out > 30) bg-red-100 text-red-800
                                        @elseif($detail->days_out > 14) bg-orange-100 text-orange-800
                                        @elseif($detail->days_out > 7) bg-yellow-100 text-yellow-800
                                        @else bg-green-100 text-green-800
                                        @endif">
                                        {{ $detail->days_out }} days
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            <!-- Transaction History -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-sm font-semibold text-gray-700">
                        <i class="fas fa-history text-gray-400 mr-2"></i> Transaction History
                    </h3>
                </div>
                <div class="overflow-x-auto max-h-64 overflow-y-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 sticky top-0">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Party</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reference</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Remarks</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($transactions as $transaction)
                            <tr>
                                <td class="px-6 py-3 text-sm">{{ $transaction->created_at->format('d-m-Y H:i') }}</td>
                                <td class="px-6 py-3">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                        @if($transaction->transaction_type === 'issued') bg-yellow-100 text-yellow-800
                                        @elseif($transaction->transaction_type === 'returned') bg-green-100 text-green-800
                                        @elseif($transaction->transaction_type === 'sold') bg-blue-100 text-blue-800
                                        @elseif($transaction->transaction_type === 'purchased') bg-purple-100 text-purple-800
                                        @else bg-gray-100 text-gray-800
                                        @endif">
                                        {{ $transaction->type_label }}
                                    </span>
                                </td>
                                <td class="px-6 py-3 text-sm">{{ $transaction->customer->name ?? 'System' }}</td>
                                <td class="px-6 py-3 text-sm">{{ $transaction->reference_document ?? '—' }}</td>
                                <td class="px-6 py-3 text-sm">{{ $transaction->remarks ?? '—' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-gray-500">No transactions found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $transactions->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection