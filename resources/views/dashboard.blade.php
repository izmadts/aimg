@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">
    
    <!-- Welcome Banner -->
    <div class="bg-gradient-to-r from-blue-500 to-indigo-600 rounded-lg shadow-lg p-6 text-white">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold">Welcome back, {{ Auth::user()->name }}!</h1>
                <p class="mt-1 text-blue-100">Here's what's happening with your medical gas business today.</p>
            </div>
            <div class="text-right">
                <div class="text-sm text-blue-100">Today's Date</div>
                <div class="text-lg font-bold">{{ now()->format('l, d F Y') }}</div>
            </div>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500">Total Customers</p>
                    <p class="text-2xl font-bold">{{ $stats->total_customers }}</p>
                    <p class="text-xs text-gray-400">Active customers</p>
                </div>
                <div class="bg-blue-100 rounded-full p-3">
                    <i class="fas fa-users text-blue-500 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-yellow-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500">Issued Cylinders</p>
                    <p class="text-2xl font-bold text-yellow-600">{{ $stats->issued_cylinders }}</p>
                    <p class="text-xs text-gray-400">Out with customers</p>
                </div>
                <div class="bg-yellow-100 rounded-full p-3">
                    <i class="fas fa-cylinder text-yellow-500 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500">Today's Sales</p>
                    <p class="text-2xl font-bold text-green-600">Rs. {{ number_format($stats->today_sales, 0) }}</p>
                    <p class="text-xs text-gray-400">{{ $stats->today_sales_count }} invoices today</p>
                </div>
                <div class="bg-green-100 rounded-full p-3">
                    <i class="fas fa-file-invoice text-green-500 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-red-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500">Pending Receivables</p>
                    <p class="text-2xl font-bold text-red-600">Rs. {{ number_format($stats->pending_receivables, 0) }}</p>
                    <p class="text-xs text-gray-400">From customers</p>
                </div>
                <div class="bg-red-100 rounded-full p-3">
                    <i class="fas fa-clock text-red-500 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Income & Expense Row -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-green-500">
            <p class="text-xs text-gray-500">Total Income</p>
            <p class="text-2xl font-bold text-green-600">Rs. {{ number_format($incomeExpense->total_income, 0) }}</p>
            <p class="text-xs text-gray-400">Today: Rs. {{ number_format($incomeExpense->today_income, 0) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-red-500">
            <p class="text-xs text-gray-500">Total Expense</p>
            <p class="text-2xl font-bold text-red-600">Rs. {{ number_format($incomeExpense->total_expense, 0) }}</p>
            <p class="text-xs text-gray-400">Today: Rs. {{ number_format($incomeExpense->today_expense, 0) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-blue-500">
            <p class="text-xs text-gray-500">Net Profit</p>
            <p class="text-2xl font-bold {{ $incomeExpense->net_profit >= 0 ? 'text-blue-600' : 'text-red-600' }}">
                Rs. {{ number_format($incomeExpense->net_profit, 0) }}
            </p>
            <p class="text-xs text-gray-400">Month: Rs. {{ number_format($incomeExpense->month_income - $incomeExpense->month_expense, 0) }}</p>
        </div>
    </div>

    <!-- Income & Expense by Account -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-sm font-semibold text-gray-700 mb-4">
                <i class="fas fa-arrow-up text-green-600 mr-2"></i> Income by Account
            </h3>
            @if($incomeExpense->income_by_account->count() > 0)
                <div class="space-y-2">
                    @foreach($incomeExpense->income_by_account as $item)
                        <div>
                            <div class="flex justify-between text-sm">
                                <span>{{ $item->account_name }}</span>
                                <span class="font-semibold text-green-600">Rs. {{ number_format($item->total, 0) }}</span>
                            </div>
                            @php
                                $percentage = $incomeExpense->total_income > 0 ? ($item->total / $incomeExpense->total_income) * 100 : 0;
                            @endphp
                            <div class="w-full bg-gray-200 rounded-full h-1.5 mt-1">
                                <div class="bg-green-500 h-1.5 rounded-full" style="width: {{ min($percentage, 100) }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500 text-sm">No income recorded.</p>
            @endif
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-sm font-semibold text-gray-700 mb-4">
                <i class="fas fa-arrow-down text-red-600 mr-2"></i> Expense by Account
            </h3>
            @if($incomeExpense->expense_by_account->count() > 0)
                <div class="space-y-2">
                    @foreach($incomeExpense->expense_by_account as $item)
                        <div>
                            <div class="flex justify-between text-sm">
                                <span>{{ $item->account_name }}</span>
                                <span class="font-semibold text-red-600">Rs. {{ number_format($item->total, 0) }}</span>
                            </div>
                            @php
                                $percentage = $incomeExpense->total_expense > 0 ? ($item->total / $incomeExpense->total_expense) * 100 : 0;
                            @endphp
                            <div class="w-full bg-gray-200 rounded-full h-1.5 mt-1">
                                <div class="bg-red-500 h-1.5 rounded-full" style="width: {{ min($percentage, 100) }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500 text-sm">No expense recorded.</p>
            @endif
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">📈 Monthly Revenue {{ now()->year }}</h3>
            <div class="h-64">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">🔄 Cylinder Movement (Last 7 Days)</h3>
            <div class="h-64">
                <canvas id="movementChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Issued Cylinders & Customers -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Issued Cylinders -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <h3 class="text-sm font-semibold text-gray-700">
                    <i class="fas fa-cylinder text-yellow-600 mr-2"></i> Recently Issued Cylinders
                </h3>
                <a href="{{ route('cylinders.index') }}" class="text-xs text-blue-600 hover:underline">View All</a>
            </div>
            <div class="overflow-x-auto max-h-64 overflow-y-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 sticky top-0">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Cylinder</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Gas</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                            <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Days Out</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($issuedCylinders as $cylinder)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-2 text-sm font-mono">{{ $cylinder->cylinder_number }}</td>
                            <td class="px-4 py-2 text-sm">{{ $cylinder->gas_name }}</td>
                            <td class="px-4 py-2 text-sm">{{ $cylinder->customer_name }}</td>
                            <td class="px-4 py-2 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                    @if($cylinder->days_out > 30) bg-red-100 text-red-800
                                    @elseif($cylinder->days_out > 14) bg-orange-100 text-orange-800
                                    @elseif($cylinder->days_out > 7) bg-yellow-100 text-yellow-800
                                    @else bg-green-100 text-green-800
                                    @endif">
                                    {{ $cylinder->days_out }} days
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-gray-500">No cylinders issued.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Customers with Cylinders -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <h3 class="text-sm font-semibold text-gray-700">
                    <i class="fas fa-users text-blue-600 mr-2"></i> Customers with Cylinders
                </h3>
                <a href="{{ route('customers.index') }}" class="text-xs text-blue-600 hover:underline">View All</a>
            </div>
            <div class="overflow-x-auto max-h-64 overflow-y-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 sticky top-0">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Phone</th>
                            <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Cylinders</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Deposit</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($customersWithCylinders as $customer)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-2 text-sm font-medium">{{ $customer->name }}</td>
                            <td class="px-4 py-2 text-sm">{{ $customer->phone ?? 'N/A' }}</td>
                            <td class="px-4 py-2 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    {{ $customer->total_issued }}
                                </span>
                            </td>
                            <td class="px-4 py-2 text-right text-sm">Rs. {{ number_format($customer->total_deposit, 0) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-gray-500">No customers with cylinders.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-sm font-semibold text-gray-700">
                <i class="fas fa-clock text-gray-400 mr-2"></i> Recent Activity
            </h3>
        </div>
        <div class="divide-y divide-gray-200 max-h-64 overflow-y-auto">
            @forelse($recentActivity as $activity)
            <div class="flex items-center px-6 py-3 hover:bg-gray-50 transition">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center
                        @if($activity->color === 'green') bg-green-100 text-green-600
                        @elseif($activity->color === 'blue') bg-blue-100 text-blue-600
                        @elseif($activity->color === 'yellow') bg-yellow-100 text-yellow-600
                        @else bg-gray-100 text-gray-600
                        @endif">
                        <i class="fas {{ $activity->icon }}"></i>
                    </div>
                </div>
                <div class="ml-3 flex-1">
                    <p class="text-sm font-medium text-gray-900">{{ $activity->title }}</p>
                    <p class="text-xs text-gray-500">{{ $activity->description }}</p>
                </div>
                <div class="text-right">
                    @if($activity->amount > 0)
                        <p class="text-sm font-semibold text-gray-900">Rs. {{ number_format($activity->amount, 0) }}</p>
                    @endif
                    <p class="text-xs text-gray-400">{{ $activity->date->diffForHumans() }}</p>
                </div>
                @if(isset($activity->url))
                    <a href="{{ $activity->url }}" class="ml-2 text-blue-600 hover:text-blue-800 text-sm">
                        <i class="fas fa-arrow-right"></i>
                    </a>
                @endif
            </div>
            @empty
            <div class="px-6 py-8 text-center text-gray-500">
                <i class="fas fa-inbox text-3xl mb-2 text-gray-300 block"></i>
                No recent activity.
            </div>
            @endforelse
        </div>
    </div>

    <!-- Low Stock & Pending Returns -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Low Stock Alert -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <h3 class="text-sm font-semibold text-red-600">
                    <i class="fas fa-exclamation-triangle mr-2"></i> Low Stock Alert
                </h3>
                <a href="{{ route('gas-products.index') }}" class="text-xs text-blue-600 hover:underline">View All</a>
            </div>
            <div class="divide-y divide-gray-200 max-h-48 overflow-y-auto">
                @forelse($lowStockProducts as $product)
                <div class="flex items-center justify-between px-6 py-3">
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ $product->name }}</p>
                        <p class="text-xs text-gray-500">Min: {{ $product->minimum_stock_level }} {{ $product->uom }}</p>
                    </div>
                    <div class="text-right">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                            @if($product->current_stock <= 0) bg-red-100 text-red-800
                            @else bg-yellow-100 text-yellow-800
                            @endif">
                            {{ $product->current_stock }} {{ $product->uom }}
                        </span>
                    </div>
                </div>
                @empty
                <div class="px-6 py-8 text-center text-green-600">
                    <i class="fas fa-check-circle text-2xl mb-2 block"></i>
                    All products are well stocked!
                </div>
                @endforelse
            </div>
        </div>

        <!-- Pending Returns -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <h3 class="text-sm font-semibold text-orange-600">
                    <i class="fas fa-clock mr-2"></i> Pending Returns (7+ Days)
                </h3>
                <a href="{{ route('cylinders.tracking') }}" class="text-xs text-blue-600 hover:underline">View All</a>
            </div>
            <div class="divide-y divide-gray-200 max-h-48 overflow-y-auto">
                @forelse($pendingReturns as $pending)
                <div class="flex items-center justify-between px-6 py-3">
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ $pending->customer_name }}</p>
                        <p class="text-xs text-gray-500">{{ $pending->cylinders }} ({{ $pending->cylinder_count }} cyl)</p>
                    </div>
                    <div class="text-right">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                            {{ $pending->days_out }} days
                        </span>
                    </div>
                </div>
                @empty
                <div class="px-6 py-8 text-center text-green-600">
                    <i class="fas fa-check-circle text-2xl mb-2 block"></i>
                    All cylinders returned on time!
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Footer Stats -->
    <div class="bg-white rounded-lg shadow p-4">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
            <div>
                <p class="text-xs text-gray-500">Month's Sales</p>
                <p class="text-lg font-bold text-green-600">Rs. {{ number_format($stats->month_sales, 0) }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Pending Payables</p>
                <p class="text-lg font-bold text-red-600">Rs. {{ number_format($stats->pending_payables, 0) }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Total Cylinders</p>
                <p class="text-lg font-bold">{{ $stats->total_cylinders }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Gas Stock Value</p>
                <p class="text-lg font-bold text-blue-600">Rs. {{ number_format($stats->gas_stock_value, 0) }}</p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ============================================
    // REVENUE CHART
    // ============================================
    const revenueData = @json($monthlyRevenue);
    
    new Chart(document.getElementById('revenueChart'), {
        type: 'bar',
        data: {
            labels: revenueData.map(d => d.month),
            datasets: [
                {
                    label: 'Income',
                    data: revenueData.map(d => d.income),
                    backgroundColor: 'rgba(34, 197, 94, 0.5)',
                    borderColor: 'rgba(34, 197, 94, 1)',
                    borderWidth: 1,
                    borderRadius: 4,
                },
                {
                    label: 'Expense',
                    data: revenueData.map(d => d.expense),
                    backgroundColor: 'rgba(239, 68, 68, 0.5)',
                    borderColor: 'rgba(239, 68, 68, 1)',
                    borderWidth: 1,
                    borderRadius: 4,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        pointStyle: 'circle'
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Rs. ' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });

    // ============================================
    // CYLINDER MOVEMENT CHART
    // ============================================
    const movementData = @json($cylinderMovement);
    
    new Chart(document.getElementById('movementChart'), {
        type: 'line',
        data: {
            labels: movementData.map(d => d.date),
            datasets: [
                {
                    label: 'Issued',
                    data: movementData.map(d => d.issued),
                    borderColor: 'rgb(34, 197, 94)',
                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointRadius: 4,
                },
                {
                    label: 'Returned',
                    data: movementData.map(d => d.returned),
                    borderColor: 'rgb(239, 68, 68)',
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointRadius: 4,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        pointStyle: 'circle'
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
});
</script>
@endpush
@endsection