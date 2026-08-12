@extends('layouts.app')

@section('title', 'Customer: ' . $customer->name)

@section('content')
<div class="space-y-6">
    
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $customer->name }}</h1>
            <p class="text-sm text-gray-500">Customer Details</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('customers.statement', $customer) }}" 
               class="bg-purple-600 hover:bg-purple-700 text-white font-medium px-4 py-2 rounded-lg transition">
                <i class="fas fa-file-invoice mr-2"></i> Statement
            </a>
            <a href="{{ route('customers.edit', $customer) }}" 
               class="bg-yellow-600 hover:bg-yellow-700 text-white font-medium px-4 py-2 rounded-lg transition">
                <i class="fas fa-edit mr-2"></i> Edit
            </a>
            <a href="{{ route('customers.index') }}" 
               class="bg-gray-500 hover:bg-gray-600 text-white font-medium px-4 py-2 rounded-lg transition">
                <i class="fas fa-arrow-left mr-2"></i> Back
            </a>
        </div>
    </div>

    <!-- Status Badge -->
    <div class="flex flex-wrap gap-2">
        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
            @if($customer->is_active) bg-green-100 text-green-800
            @else bg-red-100 text-red-800
            @endif">
            <i class="fas fa-circle text-xs mr-1.5"></i> {{ $customer->is_active ? 'Active' : 'Inactive' }}
        </span>
        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
            <i class="fas fa-cylinder text-xs mr-1.5"></i> {{ $stats['total_issued_cylinders'] }} Issued Cylinders
        </span>
        @if($stats['pending_balance'] > 0)
        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
            <i class="fas fa-clock text-xs mr-1.5"></i> Pending: Rs. {{ number_format($stats['pending_balance'], 2) }}
        </span>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left Column: Customer Info -->
        <div class="lg:col-span-1 space-y-6">
            
            <!-- Customer Card -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center space-x-4">
                    <div class="w-16 h-16 rounded-full bg-gradient-to-r from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold text-2xl">
                        {{ substr($customer->name, 0, 1) }}
                    </div>
                    <div>
                        <h2 class="text-xl font-bold">{{ $customer->name }}</h2>
                        @if($customer->erp_customer_id)
                            <p class="text-sm text-gray-500">ID: {{ $customer->erp_customer_id }}</p>
                        @endif
                    </div>
                </div>

                <div class="mt-4 space-y-2 text-sm">
                    @if($customer->phone)
                        <p><i class="fas fa-phone w-5 text-gray-400"></i> {{ $customer->phone }}</p>
                    @endif
                    @if($customer->email)
                        <p><i class="fas fa-envelope w-5 text-gray-400"></i> {{ $customer->email }}</p>
                    @endif
                    @if($customer->address)
                        <p><i class="fas fa-map-marker-alt w-5 text-gray-400"></i> {{ $customer->address }}</p>
                    @endif
                    @if($customer->cnic)
                        <p><i class="fas fa-id-card w-5 text-gray-400"></i> CNIC: {{ $customer->cnic }}</p>
                    @endif
                    @if($customer->ntn_number)
                        <p><i class="fas fa-file-invoice w-5 text-gray-400"></i> NTN: {{ $customer->ntn_number }}</p>
                    @endif
                    <p><i class="fas fa-calendar-alt w-5 text-gray-400"></i> Since: {{ $customer->created_at->format('d-m-Y') }}</p>
                </div>
            </div>

            <!-- Statistics -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">Statistics</h3>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Total Sales</span>
                        <span class="font-semibold">{{ $stats['total_sales'] }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Total Sales Amount</span>
                        <span class="font-semibold text-green-600">Rs. {{ number_format($stats['total_sales_amount'], 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Issued Cylinders</span>
                        <span class="font-semibold text-yellow-600">{{ $stats['total_issued_cylinders'] }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Cylinder Transactions</span>
                        <span class="font-semibold">{{ $stats['total_cylinder_transactions'] }}</span>
                    </div>
                    <div class="flex justify-between border-t pt-2">
                        <span class="text-sm font-medium">Pending Balance</span>
                        <span class="font-bold text-red-600">Rs. {{ number_format($stats['pending_balance'], 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Issued Cylinders -->
            @if($customer->activeCylinderIssues->count() > 0)
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">
                    <i class="fas fa-cylinder mr-2 text-yellow-600"></i> Issued Cylinders
                </h3>
                <div class="space-y-2">
                    @foreach($customer->activeCylinderIssues as $detail)
                    <div class="flex justify-between text-sm border-b border-gray-100 pb-2">
                        <span>{{ $detail->cylinder->cylinder_number ?? 'N/A' }}</span>
                        <span class="text-xs text-gray-500">{{ $detail->cylinder->gasProduct->name ?? 'N/A' }} × {{ $detail->quantity }}</span>
                        <span class="text-xs text-gray-500">{{ $detail->days_out }} days out</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Security Deposit -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">
                    <i class="fas fa-coins mr-2 text-gray-400"></i> Security Deposit
                </h3>
                <div class="text-center">
                    <p class="text-3xl font-bold text-blue-600">Rs. {{ number_format($customer->security_deposit, 2) }}</p>
                    <p class="text-xs text-gray-500 mt-1">Total deposit held</p>
                </div>
            </div>
        </div>

        <!-- Right Column: Sales & History -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Recent Sales -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-sm font-semibold text-gray-700">
                        <i class="fas fa-file-invoice mr-2 text-gray-400"></i> Recent Sales
                    </h3>
                    <span class="text-xs text-gray-500">Last 10 invoices</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Invoice</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Amount</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($recentSales as $sale)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-3 text-sm font-medium">{{ $sale->invoice_no }}</td>
                                <td class="px-6 py-3 text-sm">{{ $sale->date->format('d-m-Y') }}</td>
                                <td class="px-6 py-3 text-right font-semibold">Rs. {{ number_format($sale->grand_total, 2) }}</td>
                                <td class="px-6 py-3 text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @if($sale->payment_status === 'paid') bg-green-100 text-green-800
                                        @elseif($sale->payment_status === 'partial') bg-yellow-100 text-yellow-800
                                        @else bg-red-100 text-red-800
                                        @endif">
                                        {{ ucfirst($sale->payment_status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-3 text-center">
                                    <a href="{{ route('sales.show', $sale) }}" class="text-blue-600 hover:text-blue-800">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-gray-500">No sales found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Cylinder Transaction History -->
            @if($cylinderHistory->count() > 0)
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-700">
                        <i class="fas fa-history mr-2 text-gray-400"></i> Cylinder Transaction History
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
                            @foreach($cylinderHistory as $transaction)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-3 text-sm">{{ $transaction->cylinder->cylinder_number ?? 'N/A' }}</td>
                                <td class="px-6 py-3">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @if($transaction->transaction_type === 'issued') bg-yellow-100 text-yellow-800
                                        @elseif($transaction->transaction_type === 'returned') bg-green-100 text-green-800
                                        @elseif($transaction->transaction_type === 'sold') bg-blue-100 text-blue-800
                                        @else bg-gray-100 text-gray-800
                                        @endif">
                                        {{ ucfirst($transaction->transaction_type) }}
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
        </div>
    </div>
</div>
@endsection