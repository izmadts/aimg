@extends('layouts.app')

@section('title', 'Edit Customer')

@section('content')
<div class="space-y-6">
    
    <!-- Page Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Edit Customer</h1>
            <p class="text-sm text-gray-500">Update customer information</p>
        </div>
        <a href="{{ route('customers.index') }}" class="text-gray-600 hover:text-gray-900">
            <i class="fas fa-arrow-left mr-2"></i> Back
        </a>
    </div>

    <div class="bg-white rounded-lg shadow p-6 max-w-3xl">
        <form method="POST" action="{{ route('customers.update', $customer) }}">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                
                <!-- Customer ID -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Customer ID</label>
                    <input type="text" value="{{ $customer->customer_code }}" disabled
                           class="w-full rounded-lg bg-gray-100 border-gray-300 shadow-sm text-gray-500">
                </div>

                <!-- Name -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
                    <input type="text" name="name" value="{{ old('name', $customer->name) }}" required
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('name')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Phone -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone *</label>
                    <input type="text" name="phone" value="{{ old('phone', $customer->phone) }}" required
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('phone')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- ERP Customer ID (legacy system reference) -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Old System ID
                        <x-help-tip text="Only if migrated from a previous ERP/system — the ID this customer had there. Separate from the Customer ID above, which is fixed and auto-generated." />
                    </label>
                    <input type="text" name="erp_customer_id" value="{{ old('erp_customer_id', $customer->erp_customer_id) }}"
                           placeholder="Optional — ID from a previous system"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('erp_customer_id')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email', $customer->email) }}"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('email')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- CNIC -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">CNIC</label>
                    <input type="text" name="cnic" value="{{ old('cnic', $customer->cnic) }}"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('cnic')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- NTN Number -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">NTN Number</label>
                    <input type="text" name="ntn_number" value="{{ old('ntn_number', $customer->ntn_number) }}"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('ntn_number')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Security Deposit -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Security Deposit (Rs.)
                        <x-help-tip text="A general deposit on file for this customer overall — separate from per-cylinder deposits tracked on individual Sales." />
                    </label>
                    <input type="number" step="0.01" name="security_deposit" value="{{ old('security_deposit', $customer->security_deposit) }}"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('security_deposit')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Opening Balance -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Opening Balance (Rs.)
                        <x-help-tip text="What this customer already owed before joining this system. Adjusting it later changes their Pending Balance shown on their profile." />
                    </label>
                    <input type="number" step="0.01" name="opening_balance" value="{{ old('opening_balance', $customer->opening_balance) }}"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('opening_balance')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Status -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <div class="flex items-center mt-2">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $customer->is_active) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <label class="ml-2 text-sm text-gray-700">Active</label>
                    </div>
                </div>

                <!-- Address -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                    <textarea name="address" rows="2" 
                              class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('address', $customer->address) }}</textarea>
                    @error('address')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Notes -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea name="notes" rows="2" 
                              class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('notes', $customer->notes) }}</textarea>
                    @error('notes')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Submit -->
            <div class="flex justify-end space-x-4 mt-6 pt-6 border-t border-gray-200">
                <a href="{{ route('customers.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-6 rounded-lg transition">
                    Cancel
                </a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-lg transition">
                    <i class="fas fa-save mr-2"></i> Update Customer
                </button>
            </div>
        </form>
    </div>
</div>
@endsection