@extends('layouts.app')

@section('title', 'Add Account')

@section('content')
<div class="space-y-6">
    
    <!-- Page Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Add Account</h1>
            <p class="text-sm text-gray-500">Create a new chart of account</p>
        </div>
        <a href="{{ route('accounts.index') }}" class="text-gray-600 hover:text-gray-900">
            <i class="fas fa-arrow-left mr-2"></i> Back
        </a>
    </div>

    <div class="bg-white rounded-lg shadow p-6 max-w-3xl">
        <form method="POST" action="{{ route('accounts.store') }}">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                
                <!-- Account Code (Auto-generated) -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Account Code</label>
                    <input type="text" value="Auto-generated" disabled
                           class="w-full rounded-lg bg-gray-100 border-gray-300 shadow-sm">
                    <p class="text-xs text-gray-400 mt-1">Code will be auto-generated based on account type</p>
                </div>

                <!-- Account Name -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Account Name *</label>
                    <input type="text" name="account_name" value="{{ old('account_name') }}" required
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('account_name')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Account Type -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Account Type *</label>
                    <select name="account_type" required
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Select Type</option>
                        <option value="asset" {{ old('account_type') == 'asset' ? 'selected' : '' }}>Asset</option>
                        <option value="liability" {{ old('account_type') == 'liability' ? 'selected' : '' }}>Liability</option>
                        <option value="income" {{ old('account_type') == 'income' ? 'selected' : '' }}>Income</option>
                        <option value="expense" {{ old('account_type') == 'expense' ? 'selected' : '' }}>Expense</option>
                        <option value="equity" {{ old('account_type') == 'equity' ? 'selected' : '' }}>Equity</option>
                    </select>
                    @error('account_type')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Parent Account -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Parent Account</label>
                    <select name="parent_id" 
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">None (Top Level)</option>
                        @foreach($parents as $parent)
                            <option value="{{ $parent->id }}" {{ old('parent_id') == $parent->id ? 'selected' : '' }}>
                                {{ $parent->account_name }} ({{ $parent->account_code }})
                            </option>
                        @endforeach
                    </select>
                    @error('parent_id')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Opening Balance -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Opening Balance (Rs.)</label>
                    <input type="number" step="0.01" name="opening_balance" value="{{ old('opening_balance', 0) }}"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('opening_balance')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Status -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <div class="flex items-center mt-2">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <label class="ml-2 text-sm text-gray-700">Active</label>
                    </div>
                </div>

                <!-- Description -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="2" 
                              class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Submit -->
            <div class="flex justify-end space-x-4 mt-6 pt-6 border-t border-gray-200">
                <a href="{{ route('accounts.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-6 rounded-lg transition">
                    Cancel
                </a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-lg transition">
                    <i class="fas fa-save mr-2"></i> Save Account
                </button>
            </div>
        </form>
    </div>
</div>
@endsection