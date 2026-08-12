@extends('layouts.app')

@section('title', 'Supplier: ' . $supplier->name)

@section('content')
<div class="space-y-6">
    
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $supplier->name }}</h1>
            <p class="text-sm text-gray-500">Supplier Details</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('suppliers.statement', $supplier) }}" 
               class="bg-purple-600 hover:bg-purple-700 text-white font-medium px-4 py-2 rounded-lg transition">
                <i class="fas fa-file-invoice mr-2"></i> Statement
            </a>
            <a href="{{ route('suppliers.edit', $supplier) }}" 
               class="bg-yellow-600 hover:bg-yellow-700 text-white font-medium px-4 py-2 rounded-lg transition">
                <i class="fas fa-edit mr-2"></i> Edit
            </a>
            <a href="{{ route('suppliers.index') }}" 
               class="bg-gray-500 hover:bg-gray-600 text-white font-medium px-4 py-2 rounded-lg transition">
                <i class="fas fa-arrow-left mr-2"></i> Back
            </a>
        </div>
    </div>

    <!-- Status Badge -->
    <div class="flex flex-wrap gap-2">
        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
            @if($supplier->is_active) bg-green-100 text-green-800
            @else bg-red-100 text-red-800
            @endif">
            <i class="fas fa-circle text-xs mr-1.5"></i> {{ $supplier->is_active ? 'Active' : 'Inactive' }}
        </span>
        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
            <i class="fas fa-shopping-cart text-xs mr-1.5"></i> {{ $stats['total_purchases'] }} Purchases
        </span>
        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
            <i class="fas fa-cylinder text-xs mr-1.5"></i> {{ $stats['total_cylinders'] }} Cylinders
        </span>
        @if($stats['pending_payable'] > 0)
        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
            <i class="fas fa-clock text-xs mr-1.5"></i> Payable: Rs. {{ number_format($stats['pending_payable'], 2) }}
        </span>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left Column -->
        <div class="lg:col-span-1 space-y-6">
            
            <!-- Supplier Card -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center space-x-4">
                    <div class="w-16 h-16 rounded-full bg-gradient-to-r from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold text-2xl">
                        {{ substr($supplier->name, 0, 1) }}
                    </div>
                    <div>
                        <h2 class="text-xl font-bold">{{ $supplier->name }}</h2>
                        @if($supplier->erp_supplier_id)
                            <p class="text-sm text-gray-500">ID: {{ $supplier->erp_supplier_id }}</p>
                        @endif
                        @if($supplier->company_name)
                            <p class="text-sm text-gray-500">{{ $supplier->company_name }}</p>
                        @endif
                    </div>
                </div>

                <div class="mt-4 space-y-2 text-sm">
                    @if($supplier->phone)
                        <p><i class="fas fa-phone w-5 text-gray-400"></i> {{ $supplier->phone }}</p>
                    @endif
                    @if($supplier->email)
                        <p><i class="fas fa-envelope w-5 text-gray-400"></i> {{ $supplier->email }}</p>
                    @endif
                    @if($supplier->address)
                        <p><i class="fas fa-map-marker-alt w-5 text-gray-400"></i> {{ $supplier->address }}</p>
                    @endif
                    @if($supplier->contact_person)
                        <p><i class="fas fa-user w-5 text-gray-400"></i> Contact: {{ $supplier->contact_person }}</p>
                    @endif
                    @if($supplier->contact_person_phone)
                        <p><i class="fas fa-phone w-5 text-gray-400"></i> {{ $supplier->contact_person_phone }}</p>
                    @endif
                    @if($supplier->ntn_number)
                        <p><i class="fas fa-file-invoice w-5 text-gray-400"></i> NTN: {{ $supplier->ntn_number }}</p>
                    @endif
                    <p><i class="fas fa-calendar-alt w-5 text-gray-400"></i> Since: {{ $supplier->created_at->format('d-m-Y') }}</p>
                </div>
            </div>

            <!-- Statistics -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">Statistics</h3>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Total Purchases</span>
                        <span class="font-semibold">{{ $stats['total_purchases'] }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Total Purchase Amount</span>
                        <span class="font-semibold text-blue-600">Rs. {{ number_format($stats['total_purchase_amount'], 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Total Cylinders</span>
                        <span class="font-semibold text-yellow-600">{{ $stats['total_cylinders'] }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Cylinder Transactions</span>
                        <span class="font-semibold">{{ $stats['total_cylinder_transactions'] }}</span>
                    </div>
                    <div class="flex justify-between border-t pt-2">
                        <span class="text-sm font-medium">Pending Payable</span>
                        <span class="font-bold text-red-600">Rs. {{ number_format($stats['pending_payable'], 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Balance Info -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">
                    <i class="fas fa-coins mr-2 text-gray-400"></i> Balance Information
                </h3>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Opening Balance</span>
                        <span class="font-semibold">Rs. {{ number_format($supplier->opening_balance, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Balance Type</span>
                        <span class="font-semibold">{{ $supplier->balance_label }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Cylinder Deposit Paid</span>
                        <span class="font-semibold">Rs. {{ number_format($supplier->cylinder_deposit_paid, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Cylinder Return Days</span>
                        <span class="font-semibold">{{ $supplier->cylinder_return_days }} days</span>
                    </div>
                </div>
            </div>

            <!-- Notes -->
            @if($supplier->notes)
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-sticky-note mr-2 text-gray-400"></i> Notes
                </h3>
                <p class="text-sm text-gray-600">{{ $supplier->notes }}</p>
            </div>
            @endif
        </div>

        <!-- Right Column -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Recent Purchases -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-sm font-semibold text-gray-700">
                        <i class="fas fa-shopping-cart mr-2 text-gray-400"></i> Recent Purchases
                    </h3>
                    <span class="text-xs text-gray-500">Last 10 purchases</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">PO #</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Amount</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Payment</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($recentPurchases as $purchase)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-3 text-sm font-medium">
                                    <a href="{{ route('purchases.show', $purchase) }}" class="text-blue-600 hover:underline">
                                        {{ $purchase->purchase_invoice_no }}
                                    </a>
                                </td>
                                <td class="px-6 py-3 text-sm">{{ $purchase->date->format('d-m-Y') }}</td>
                                <td class="px-6 py-3 text-right font-semibold">Rs. {{ number_format($purchase->grand_total, 2) }}</td>
                                <td class="px-6 py-3 text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @if($purchase->status === 'confirmed') bg-green-100 text-green-800
                                        @elseif($purchase->status === 'draft') bg-yellow-100 text-yellow-800
                                        @else bg-red-100 text-red-800
                                        @endif">
                                        {{ ucfirst($purchase->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-3 text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @if($purchase->payment_status === 'paid') bg-green-100 text-green-800
                                        @elseif($purchase->payment_status === 'partial') bg-yellow-100 text-yellow-800
                                        @else bg-red-100 text-red-800
                                        @endif">
                                        {{ ucfirst($purchase->payment_status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-3 text-center">
                                    <a href="{{ route('purchases.show', $purchase) }}" class="text-blue-600 hover:text-blue-800">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500">No purchases found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Cylinder Transactions -->
            @if($cylinderTransactions->count() > 0)
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-700">
                        <i class="fas fa-cylinder mr-2 text-gray-400"></i> Cylinder Transactions
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cylinder</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Remarks</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($cylinderTransactions as $transaction)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-3 text-sm">
                                    <a href="{{ route('cylinders.show', $transaction->cylinder) }}" class="text-blue-600 hover:underline">
                                        {{ $transaction->cylinder->cylinder_number ?? 'N/A' }}
                                    </a>
                                </td>
                                <td class="px-6 py-3">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @if($transaction->transaction_type === 'received_filled') bg-green-100 text-green-800
                                        @elseif($transaction->transaction_type === 'received_empty') bg-gray-100 text-gray-800
                                        @elseif($transaction->transaction_type === 'returned_empty') bg-orange-100 text-orange-800
                                        @elseif($transaction->transaction_type === 'purchased_new') bg-purple-100 text-purple-800
                                        @elseif($transaction->transaction_type === 'exchanged') bg-blue-100 text-blue-800
                                        @else bg-gray-100 text-gray-800
                                        @endif">
                                        {{ ucfirst(str_replace('_', ' ', $transaction->transaction_type)) }}
                                    </span>
                                </td>
                                <td class="px-6 py-3 text-sm">{{ $transaction->transaction_date->format('d-m-Y') }}</td>
                                <td class="px-6 py-3 text-sm text-gray-500">{{ $transaction->remarks ?? '—' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            <!-- Cylinders with Supplier -->
            @if($supplier->cylinders_with_supplier->count() > 0)
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-700">
                        <i class="fas fa-cylinder mr-2 text-yellow-600"></i> Cylinders with Supplier
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cylinder #</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Gas</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($supplier->cylinders_with_supplier as $cylinder)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-3 text-sm font-mono font-medium">{{ $cylinder->cylinder_number }}</td>
                                <td class="px-6 py-3 text-sm">{{ $cylinder->gasProduct->name ?? 'N/A' }}</td>
                                <td class="px-6 py-3 text-sm">{{ $cylinder->type }}</td>
                                <td class="px-6 py-3 text-sm">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        {{ $cylinder->status_label }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection