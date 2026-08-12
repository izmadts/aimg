@extends('layouts.app')

@section('title', 'Gas Product: ' . $gasProduct->name)

@section('content')
<div class="space-y-6">
    
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $gasProduct->name }}</h1>
            <p class="text-sm text-gray-500">Product Details</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('gas-products.edit', $gasProduct) }}" 
               class="bg-yellow-600 hover:bg-yellow-700 text-white font-medium px-4 py-2 rounded-lg transition">
                <i class="fas fa-edit mr-2"></i> Edit
            </a>
            <a href="{{ route('gas-products.index') }}" 
               class="bg-gray-500 hover:bg-gray-600 text-white font-medium px-4 py-2 rounded-lg transition">
                <i class="fas fa-arrow-left mr-2"></i> Back
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Code</span>
                        <span class="font-mono font-semibold">{{ $gasProduct->code }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">UOM</span>
                        <span class="font-semibold">{{ $gasProduct->uom }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Purchase Price</span>
                        <span class="font-semibold">Rs. {{ number_format($gasProduct->purchase_price, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Sale Price</span>
                        <span class="font-semibold">Rs. {{ number_format($gasProduct->sale_price, 2) }}</span>
                    </div>
                    <div class="flex justify-between border-t pt-2">
                        <span class="text-sm font-medium">Current Stock</span>
                        <span class="font-bold text-lg 
                            @if($gasProduct->stock_status === 'good') text-green-600
                            @elseif($gasProduct->stock_status === 'medium') text-blue-600
                            @elseif($gasProduct->stock_status === 'low') text-yellow-600
                            @else text-red-600
                            @endif">
                            {{ number_format($gasProduct->current_stock, 2) }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Min Stock Level</span>
                        <span class="font-semibold">{{ number_format($gasProduct->minimum_stock_level, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Stock Value</span>
                        <span class="font-semibold">Rs. {{ number_format($gasProduct->stock_value, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Status</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            @if($gasProduct->is_active) bg-green-100 text-green-800
                            @else bg-red-100 text-red-800
                            @endif">
                            {{ $gasProduct->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                    @if($gasProduct->description)
                    <div class="border-t pt-2">
                        <p class="text-sm text-gray-500">Description</p>
                        <p class="text-sm">{{ $gasProduct->description }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">Statistics</h3>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Total Cylinders</span>
                        <span class="font-semibold">{{ $stats['total_cylinders'] }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Issued Cylinders</span>
                        <span class="font-semibold text-yellow-600">{{ $stats['issued_cylinders'] }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">In House Cylinders</span>
                        <span class="font-semibold text-blue-600">{{ $stats['in_house_cylinders'] }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-sm font-semibold text-gray-700">
                        <i class="fas fa-cylinder mr-2 text-gray-400"></i> Cylinders with this Gas
                    </h3>
                    <span class="text-xs text-gray-500">{{ $gasProduct->cylinders->count() }} cylinders</span>
                </div>
                <div class="overflow-x-auto max-h-64 overflow-y-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 sticky top-0">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cylinder #</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Location</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($gasProduct->cylinders as $cylinder)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-3 text-sm font-mono">
                                    <a href="{{ route('cylinders.show', $cylinder) }}" class="text-blue-600 hover:underline">
                                        {{ $cylinder->cylinder_number }}
                                    </a>
                                </td>
                                <td class="px-6 py-3 text-sm">{{ $cylinder->type }}</td>
                                <td class="px-6 py-3">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @if($cylinder->status === 'in_house') bg-blue-100 text-blue-800
                                        @elseif($cylinder->status === 'partial_issued') bg-yellow-100 text-yellow-800
                                        @elseif($cylinder->status === 'all_issued') bg-orange-100 text-orange-800
                                        @elseif($cylinder->status === 'out_of_stock') bg-red-100 text-red-800
                                        @elseif($cylinder->status === 'under_maintenance') bg-purple-100 text-purple-800
                                        @else bg-gray-100 text-gray-800
                                        @endif">
                                        {{ $cylinder->status_label }}
                                    </span>
                                </td>
                                <td class="px-6 py-3 text-sm">
                                    @if($cylinder->issued_quantity > 0)
                                        <span class="text-yellow-600">{{ $cylinder->issued_quantity }} issued</span>
                                    @elseif($cylinder->supplier)
                                        <span class="text-blue-600">{{ $cylinder->supplier->name }}</span>
                                    @else
                                        <span class="text-gray-400">In House</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-gray-500">No cylinders for this gas.</td>
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