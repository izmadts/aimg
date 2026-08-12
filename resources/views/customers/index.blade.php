@extends('layouts.app')

@section('title', 'Customers')

@section('content')
<div class="space-y-6">
    
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Customers</h1>
            <p class="text-sm text-gray-500">Manage your customers</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('customers.export') }}" 
               class="bg-gray-600 hover:bg-gray-700 text-white font-medium px-4 py-2 rounded-lg transition">
                <i class="fas fa-file-export mr-2"></i> Export
            </a>
            <a href="{{ route('customers.create') }}" 
               class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-lg transition">
                <i class="fas fa-plus mr-2"></i> Add Customer
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-blue-500">
            <p class="text-xs text-gray-500">Total Customers</p>
            <p class="text-2xl font-bold">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-green-500">
            <p class="text-xs text-gray-500">Active</p>
            <p class="text-2xl font-bold text-green-600">{{ $stats['active'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-red-500">
            <p class="text-xs text-gray-500">Inactive</p>
            <p class="text-2xl font-bold text-red-600">{{ $stats['inactive'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-yellow-500">
            <p class="text-xs text-gray-500">With Issued Cylinders</p>
            <p class="text-2xl font-bold text-yellow-600">{{ $stats['with_issued_cylinders'] }}</p>
        </div>
    </div>

    <!-- Search & Filter -->
    <div class="bg-white rounded-lg shadow p-4">
        <form method="GET" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Search by name, phone, email..."
                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div>
                <select name="status" class="rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-lg transition">
                    <i class="fas fa-search mr-2"></i> Search
                </button>
                <a href="{{ route('customers.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium px-4 py-2 rounded-lg transition">
                    <i class="fas fa-times mr-2"></i> Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Customers Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                            <a href="{{ route('customers.index', ['sort' => 'id', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc'] + request()->except(['sort', 'direction'])) }}">
                                ID
                            </a>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Phone</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Issued Cylinders</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Security Deposit</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($customers as $customer)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-3 text-sm text-gray-500">#{{ $customer->id }}</td>
                        <td class="px-6 py-3">
                            <span class="font-medium">{{ $customer->name }}</span>
                            @if($customer->erp_customer_id)
                                <span class="text-xs text-gray-400 ml-1">[{{ $customer->erp_customer_id }}]</span>
                            @endif
                        </td>
                        <td class="px-6 py-3 text-sm">{{ $customer->phone ?? '—' }}</td>
                        <td class="px-6 py-3 text-sm">{{ $customer->email ?? '—' }}</td>
                        <td class="px-6 py-3 text-right text-sm font-medium text-yellow-600">
                            {{ $customer->total_issued_cylinders }}
                        </td>
                        <td class="px-6 py-3 text-right text-sm">Rs. {{ number_format($customer->security_deposit, 2) }}</td>
                        <td class="px-6 py-3 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($customer->is_active) bg-green-100 text-green-800
                                @else bg-red-100 text-red-800
                                @endif">
                                {{ $customer->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-6 py-3 text-center">
                            <div class="flex items-center justify-center space-x-2">
                                <a href="{{ route('customers.show', $customer) }}" 
                                   class="text-blue-600 hover:text-blue-800 transition" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('customers.edit', $customer) }}" 
                                   class="text-yellow-600 hover:text-yellow-800 transition" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="{{ route('customers.statement', $customer) }}" 
                                   class="text-purple-600 hover:text-purple-800 transition" title="Statement">
                                    <i class="fas fa-file-invoice"></i>
                                </a>
                                <form action="{{ route('customers.toggle-status', $customer) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="text-gray-600 hover:text-gray-800 transition" title="{{ $customer->is_active ? 'Deactivate' : 'Activate' }}">
                                        <i class="fas {{ $customer->is_active ? 'fa-pause' : 'fa-play' }}"></i>
                                    </button>
                                </form>
                                @if($customer->total_issued_cylinders == 0 && $customer->sales()->count() == 0)
                                <form action="{{ route('customers.destroy', $customer) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" onclick="return confirm('Are you sure you want to delete this customer?')" 
                                            class="text-red-600 hover:text-red-800 transition" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                            <i class="fas fa-inbox text-3xl mb-2 text-gray-300 block"></i>
                            No customers found.
                            <a href="{{ route('customers.create') }}" class="text-blue-600 hover:underline ml-1">Add your first customer</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $customers->links() }}
        </div>
    </div>
</div>
@endsection