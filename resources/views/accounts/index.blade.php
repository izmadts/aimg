@extends('layouts.app')

@section('title', 'Accounts')

@section('content')
<div class="space-y-6">
    
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">📊 Chart of Accounts</h1>
            <p class="text-sm text-gray-500">Manage your chart of accounts</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('accounts.create') }}" 
               class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-lg transition">
                <i class="fas fa-plus mr-2"></i> Add Account
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3">
        <div class="bg-white rounded-lg shadow p-3 border-l-4 border-blue-500">
            <p class="text-xs text-gray-500">Total Accounts</p>
            <p class="text-xl font-bold">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-3 border-l-4 border-green-500">
            <p class="text-xs text-gray-500">Active</p>
            <p class="text-xl font-bold text-green-600">{{ $stats['active'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-3 border-l-4 border-blue-500">
            <p class="text-xs text-gray-500">Assets</p>
            <p class="text-xl font-bold text-blue-600">{{ $stats['asset'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-3 border-l-4 border-red-500">
            <p class="text-xs text-gray-500">Liabilities</p>
            <p class="text-xl font-bold text-red-600">{{ $stats['liability'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-3 border-l-4 border-green-500">
            <p class="text-xs text-gray-500">Income</p>
            <p class="text-xl font-bold text-green-600">{{ $stats['income'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-3 border-l-4 border-orange-500">
            <p class="text-xs text-gray-500">Expenses</p>
            <p class="text-xl font-bold text-orange-600">{{ $stats['expense'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-3 border-l-4 border-purple-500">
            <p class="text-xs text-gray-500">Equity</p>
            <p class="text-xl font-bold text-purple-600">{{ $stats['equity'] }}</p>
        </div>
    </div>

    <!-- Search & Filter -->
    <div class="bg-white rounded-lg shadow p-4">
        <form method="GET" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Search by name, code..."
                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div>
                <select name="type" class="rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">All Types</option>
                    <option value="asset" {{ request('type') == 'asset' ? 'selected' : '' }}>Asset</option>
                    <option value="liability" {{ request('type') == 'liability' ? 'selected' : '' }}>Liability</option>
                    <option value="income" {{ request('type') == 'income' ? 'selected' : '' }}>Income</option>
                    <option value="expense" {{ request('type') == 'expense' ? 'selected' : '' }}>Expense</option>
                    <option value="equity" {{ request('type') == 'equity' ? 'selected' : '' }}>Equity</option>
                </select>
            </div>
            <div>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-lg transition">
                    <i class="fas fa-search mr-2"></i> Search
                </button>
                <a href="{{ route('accounts.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium px-4 py-2 rounded-lg transition">
                    <i class="fas fa-times mr-2"></i> Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Accounts Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Code</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Account Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Parent</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Opening Balance</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Current Balance</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($accounts as $account)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-3 text-sm font-mono font-medium">{{ $account->account_code }}</td>
                        <td class="px-6 py-3">
                            <span class="font-medium">{{ $account->account_name }}</span>
                            @if($account->description)
                                <span class="text-xs text-gray-400 block">{{ Str::limit($account->description, 30) }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-3">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($account->account_type === 'asset') bg-blue-100 text-blue-800
                                @elseif($account->account_type === 'liability') bg-red-100 text-red-800
                                @elseif($account->account_type === 'income') bg-green-100 text-green-800
                                @elseif($account->account_type === 'expense') bg-orange-100 text-orange-800
                                @elseif($account->account_type === 'equity') bg-purple-100 text-purple-800
                                @endif">
                                {{ $account->type_label }}
                            </span>
                        </td>
                        <td class="px-6 py-3 text-sm">{{ $account->parent->account_name ?? '—' }}</td>
                        <td class="px-6 py-3 text-right text-sm">Rs. {{ number_format($account->opening_balance, 2) }}</td>
                        <td class="px-6 py-3 text-right text-sm font-semibold {{ $account->current_balance >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            Rs. {{ number_format($account->current_balance, 2) }}
                        </td>
                        <td class="px-6 py-3 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($account->is_active) bg-green-100 text-green-800
                                @else bg-red-100 text-red-800
                                @endif">
                                {{ $account->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-6 py-3 text-center">
                            <div class="flex items-center justify-center space-x-2">
                                <a href="{{ route('accounts.show', $account) }}" 
                                   class="text-blue-600 hover:text-blue-800 transition" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('accounts.edit', $account) }}" 
                                   class="text-yellow-600 hover:text-yellow-800 transition" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @if($account->transactions()->count() == 0 && $account->children()->count() == 0)
                                <form action="{{ route('accounts.destroy', $account) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" onclick="return confirm('Delete this account?')" 
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
                            No accounts found.
                            <a href="{{ route('accounts.create') }}" class="text-blue-600 hover:underline ml-1">Add your first account</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $accounts->links() }}
        </div>
    </div>
</div>
@endsection