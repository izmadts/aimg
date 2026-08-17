@extends('layouts.app')

@section('title', 'Suppliers')

@section('content')
<div class="space-y-6">
    
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Suppliers</h1>
            <p class="text-sm text-gray-500">Manage your suppliers</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('suppliers.export') }}" 
               class="bg-gray-600 hover:bg-gray-700 text-white font-medium px-4 py-2 rounded-lg transition">
                <i class="fas fa-file-export mr-2"></i> Export
            </a>
            <a href="{{ route('suppliers.create') }}" 
               class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-lg transition">
                <i class="fas fa-plus mr-2"></i> Add Supplier
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-blue-500">
            <p class="text-xs text-gray-500">Total Suppliers</p>
            <p class="text-2xl font-bold">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-green-500">
            <p class="text-xs text-gray-500">Active</p>
            <p class="text-2xl font-bold text-green-600">{{ $stats['active'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-yellow-500">
            <p class="text-xs text-gray-500">Pending Payments</p>
            <p class="text-2xl font-bold text-yellow-600">{{ $stats['with_pending_payments'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-red-500">
            <p class="text-xs text-gray-500">Total Payable</p>
            <p class="text-2xl font-bold text-red-600">Rs. {{ number_format($stats['total_payable'], 0) }}</p>
        </div>
    </div>

    <!-- Search & Filter -->
    <div class="bg-white rounded-lg shadow p-4">
        <form method="GET" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Search by name, company, phone..."
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
                <a href="{{ route('suppliers.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium px-4 py-2 rounded-lg transition">
                    <i class="fas fa-times mr-2"></i> Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Suppliers Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Company</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Phone</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contact Person</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Balance</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($suppliers as $supplier)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-3 text-sm text-gray-500 font-mono">{{ $supplier->supplier_code ?? '#'.$supplier->id }}</td>
                        <td class="px-6 py-3">
                            <span class="font-medium">{{ $supplier->name }}</span>
                            @if($supplier->erp_supplier_id)
                                <span class="text-xs text-gray-400 ml-1">[{{ $supplier->erp_supplier_id }}]</span>
                            @endif
                        </td>
                        <td class="px-6 py-3 text-sm">{{ $supplier->company_name ?? '—' }}</td>
                        <td class="px-6 py-3 text-sm">{{ $supplier->phone ?? '—' }}</td>
                        <td class="px-6 py-3 text-sm">{{ $supplier->contact_person ?? '—' }}</td>
                        <td class="px-6 py-3 text-right text-sm font-semibold {{ $supplier->balance_type === 'payable' ? 'text-red-600' : 'text-green-600' }}">
                            {{ $supplier->balance_type === 'payable' ? 'Payable: ' : 'Receivable: ' }}
                            Rs. {{ number_format($supplier->opening_balance, 2) }}
                        </td>
                        <td class="px-6 py-3 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($supplier->is_active) bg-green-100 text-green-800
                                @else bg-red-100 text-red-800
                                @endif">
                                {{ $supplier->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-6 py-3 text-center">
                            <div class="flex items-center justify-center space-x-2">
                                <a href="{{ route('suppliers.show', $supplier) }}" 
                                   class="text-blue-600 hover:text-blue-800 transition" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('suppliers.edit', $supplier) }}" 
                                   class="text-yellow-600 hover:text-yellow-800 transition" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="{{ route('suppliers.statement', $supplier) }}" 
                                   class="text-purple-600 hover:text-purple-800 transition" title="Statement">
                                    <i class="fas fa-file-invoice"></i>
                                </a>
                                <form action="{{ route('suppliers.toggle-status', $supplier) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="text-gray-600 hover:text-gray-800 transition" title="{{ $supplier->is_active ? 'Deactivate' : 'Activate' }}">
                                        <i class="fas {{ $supplier->is_active ? 'fa-pause' : 'fa-play' }}"></i>
                                    </button>
                                </form>
                                @if($supplier->purchases()->count() == 0 && $supplier->cylinders()->count() == 0)
                                <form action="{{ route('suppliers.destroy', $supplier) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" onclick="return confirm('Are you sure you want to delete this supplier?')" 
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
                            No suppliers found.
                            <a href="{{ route('suppliers.create') }}" class="text-blue-600 hover:underline ml-1">Add your first supplier</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $suppliers->links() }}
        </div>
    </div>
</div>
@endsection