@extends('layouts.app')

@section('title', 'Track Cylinder: ' . $cylinder->cylinder_number)

@section('content')
<div class="space-y-6">

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">🔍 {{ $cylinder->cylinder_number }}</h1>
            <p class="text-sm text-gray-500">Cylinder Tracking Result</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('cylinders.show', $cylinder) }}" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-lg transition">
                <i class="fas fa-eye mr-2"></i> Full Cylinder Details
            </a>
            <a href="{{ route('cylinders.tracking') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-medium px-4 py-2 rounded-lg transition">
                <i class="fas fa-arrow-left mr-2"></i> Back to Tracking
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
        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
            <i class="fas fa-boxes mr-1.5"></i> Stock: {{ $cylinder->stock_quantity }}
        </span>
        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
            <i class="fas fa-hand-holding mr-1.5"></i> Issued: {{ $cylinder->issued_quantity }}
        </span>
        @if($currentHolders->count() > 0)
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800">
                <i class="fas fa-users mr-1.5"></i> {{ $currentHolders->count() }} current holder(s)
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
                        <span class="text-gray-500">Gas Product</span>
                        <span class="font-semibold">{{ $cylinder->gasProduct->name ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Type</span>
                        <span class="font-semibold">{{ $cylinder->type }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Capacity</span>
                        <span class="font-semibold">{{ $cylinder->capacity }} {{ $cylinder->gasProduct->uom ?? '' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Supplier</span>
                        <span class="font-semibold">{{ $cylinder->supplier->name ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>

            <!-- Current Holders -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-700">
                        <i class="fas fa-users text-gray-400 mr-2"></i> Current Holders
                    </h3>
                </div>
                <div class="p-4 space-y-3">
                    @forelse($currentHolders as $holder)
                        <div class="border border-gray-100 rounded-lg p-3 text-sm">
                            <div class="flex justify-between items-center">
                                <span class="font-semibold">{{ $holder->customer->name ?? 'N/A' }}</span>
                                <span class="text-xs text-gray-400">{{ $holder->customer->phone ?? '' }}</span>
                            </div>
                            <div class="flex justify-between mt-1 text-xs text-gray-500">
                                <span>Qty: {{ $holder->quantity }}</span>
                                <span>Since: {{ $holder->issue_date?->format('d-m-Y') ?? 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between mt-1 text-xs">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full font-medium
                                    @if($holder->days_out > 30) bg-red-100 text-red-800
                                    @elseif($holder->days_out > 14) bg-orange-100 text-orange-800
                                    @elseif($holder->days_out > 7) bg-yellow-100 text-yellow-800
                                    @else bg-green-100 text-green-800
                                    @endif">
                                    {{ $holder->days_out }} days out
                                </span>
                                <span class="text-gray-500">Deposit: Rs. {{ number_format($holder->security_deposit, 2) }}</span>
                            </div>
                        </div>
                    @empty
                        <p class="text-gray-500 text-sm">No units currently issued to any customer.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Journey Timeline -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">
                    <i class="fas fa-road mr-2 text-gray-400"></i> Journey Timeline
                </h3>
                @if(count($journey) > 0)
                    <div class="relative">
                        <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-gray-200"></div>
                        @foreach($journey as $item)
                            <div class="flex items-start mb-6 relative">
                                <div class="flex-shrink-0 w-8 h-8 bg-{{ $item->color }}-500 rounded-full flex items-center justify-center text-white text-xs z-10">
                                    <i class="fas {{ $item->icon }}"></i>
                                </div>
                                <div class="ml-4 flex-1">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-2">
                                            <span class="font-semibold text-sm">{{ $item->type_label }}</span>
                                            <span class="text-xs text-gray-400">{{ $item->date_formatted }}</span>
                                        </div>
                                        <span class="text-xs text-gray-500">{{ $item->party_name }}</span>
                                    </div>
                                    @if($item->remarks)
                                        <p class="text-sm text-gray-600 mt-1">{{ $item->remarks }}</p>
                                    @endif
                                    @if($item->reference)
                                        <p class="text-xs text-gray-400 mt-1">Reference: {{ $item->reference }}</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 text-sm">No journey data available.</p>
                @endif
            </div>

            <!-- Related Sales -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-700">
                        <i class="fas fa-file-invoice text-gray-400 mr-2"></i> Related Sales
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Invoice</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Grand Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($sales as $sale)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-3 text-sm">
                                        <a href="{{ route('sales.show', $sale) }}" class="text-blue-600 hover:underline">{{ $sale->invoice_no }}</a>
                                    </td>
                                    <td class="px-6 py-3 text-sm">{{ $sale->customer->name ?? 'N/A' }}</td>
                                    <td class="px-6 py-3 text-sm">{{ $sale->date->format('d-m-Y') }}</td>
                                    <td class="px-6 py-3 text-right text-sm font-semibold">Rs. {{ number_format($sale->grand_total, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-8 text-center text-gray-500">No related sales found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Full History -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-700">
                        <i class="fas fa-history text-gray-400 mr-2"></i> Full Transaction History
                    </h3>
                </div>
                <div class="overflow-x-auto max-h-96 overflow-y-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 sticky top-0">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reference</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Remarks</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($history as $item)
                                <tr>
                                    <td class="px-6 py-3 text-sm">{{ $item->date->format('d-m-Y H:i') }}</td>
                                    <td class="px-6 py-3">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            @if($item->type === 'issued') bg-yellow-100 text-yellow-800
                                            @elseif($item->type === 'returned') bg-green-100 text-green-800
                                            @elseif($item->type === 'sold') bg-blue-100 text-blue-800
                                            @elseif($item->type === 'purchased') bg-purple-100 text-purple-800
                                            @else bg-gray-100 text-gray-800
                                            @endif">
                                            {{ $item->type_label }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3 text-sm">{{ $item->customer->name ?? 'System' }}</td>
                                    <td class="px-6 py-3 text-sm">{{ $item->user->name ?? 'N/A' }}</td>
                                    <td class="px-6 py-3 text-sm">{{ $item->reference_document ?? '—' }}</td>
                                    <td class="px-6 py-3 text-sm text-gray-500">{{ $item->remarks ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-8 text-center text-gray-500">No history found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
