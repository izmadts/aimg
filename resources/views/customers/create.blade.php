@extends('layouts.app')

@section('title', 'Add Customer')

@section('content')
<div class="space-y-6">
    
    <!-- Page Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Add Customer</h1>
            <p class="text-sm text-gray-500">Create a new customer</p>
        </div>
        <a href="{{ route('customers.index') }}" class="text-gray-600 hover:text-gray-900">
            <i class="fas fa-arrow-left mr-2"></i> Back
        </a>
    </div>

    <div class="bg-white rounded-lg shadow p-6 max-w-3xl">
        <form method="POST" action="{{ route('customers.store') }}">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                
                <!-- Customer ID -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Customer ID</label>
                    <input type="text" value="Auto-generated on save" disabled
                           class="w-full rounded-lg bg-gray-100 border-gray-300 shadow-sm text-gray-400">
                </div>

                <!-- Name -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('name')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Phone -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone *</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" required
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('phone')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- ERP Customer ID (legacy system reference) -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Old System ID
                        <x-help-tip text="Only if migrating from a previous ERP/system — the ID this customer had there. Separate from the new Customer ID, which is always auto-generated." />
                    </label>
                    <input type="text" name="erp_customer_id" value="{{ old('erp_customer_id') }}"
                           placeholder="Optional — ID from a previous system"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('erp_customer_id')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('email')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- CNIC -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">CNIC</label>
                    <input type="text" name="cnic" value="{{ old('cnic') }}"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('cnic')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- NTN Number -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">NTN Number</label>
                    <input type="text" name="ntn_number" value="{{ old('ntn_number') }}"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('ntn_number')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Security Deposit -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Security Deposit (Rs.)
                        <x-help-tip text="A general deposit on file for this customer overall — separate from the per-cylinder deposits recorded below or on each Sale." />
                    </label>
                    <input type="number" step="0.01" name="security_deposit" value="{{ old('security_deposit', 0) }}"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('security_deposit')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Opening Balance -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Opening Balance (Rs.)</label>
                    <input type="number" step="0.01" name="opening_balance" value="{{ old('opening_balance', 0) }}"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <p class="text-xs text-gray-400 mt-1">What this customer already owed before joining this system, if any.</p>
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

                <!-- Address -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                    <textarea name="address" rows="2" 
                              class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('address') }}</textarea>
                    @error('address')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Notes -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea name="notes" rows="2" 
                              class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('notes') }}</textarea>
                    @error('notes')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Opening Cylinders -->
            <div class="mt-6 pt-6 border-t border-gray-200">
                <div class="flex justify-between items-center mb-2">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-700">Cylinders Already With This Customer</h3>
                        <p class="text-xs text-gray-500">If they already hold cylinders from before (e.g. moving from a paper record), list them here so stock and deposits stay accurate.</p>
                    </div>
                    <button type="button" onclick="addOpeningCylinderRow()" class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium px-3 py-1.5 rounded-lg transition">
                        <i class="fas fa-plus mr-1"></i> Add Cylinder
                    </button>
                </div>
                <div id="openingCylindersContainer" class="space-y-2"></div>
                <p id="emptyOpeningCylindersMsg" class="text-xs text-gray-400 mt-1">None added — leave empty if this is a brand-new customer.</p>
            </div>

            <!-- Submit -->
            <div class="flex justify-end space-x-4 mt-6 pt-6 border-t border-gray-200">
                <a href="{{ route('customers.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-6 rounded-lg transition">
                    Cancel
                </a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-lg transition">
                    <i class="fas fa-save mr-2"></i> Save Customer
                </button>
            </div>
        </form>
    </div>
</div>

@php
    $openingCylinderOptionsJson = $cylinders->map(function ($c) {
        $gasName = $c->gasProduct->name ?? 'N/A';
        $label = $c->cylinder_number . ' — ' . $c->type . ' (' . $gasName . ')';

        return [
            'id' => $c->id,
            'label' => $label,
        ];
    });
@endphp

@push('scripts')
<script>
    const openingCylinderOptions = @json($openingCylinderOptionsJson);

    let openingCylinderCount = 0;

    function addOpeningCylinderRow() {
        const container = document.getElementById('openingCylindersContainer');
        document.getElementById('emptyOpeningCylindersMsg').style.display = 'none';

        const n = openingCylinderCount++;
        const rowId = 'opening_cyl_' + n;
        const options = openingCylinderOptions.map(c => `<option value="${c.id}">${c.label}</option>`).join('');

        const row = document.createElement('div');
        row.id = rowId;
        row.className = 'grid grid-cols-12 gap-2 items-center';
        row.innerHTML = `
            <select name="opening_cylinders[${n}][cylinder_id]" class="col-span-6 rounded-lg border-gray-300 shadow-sm text-sm" required>
                <option value="">Select cylinder type</option>
                ${options}
            </select>
            <input type="number" min="1" step="1" name="opening_cylinders[${n}][quantity]" placeholder="Qty" required
                   class="col-span-2 rounded-lg border-gray-300 shadow-sm text-sm">
            <input type="number" min="0" step="0.01" name="opening_cylinders[${n}][security_deposit]" placeholder="Deposit Rs."
                   class="col-span-3 rounded-lg border-gray-300 shadow-sm text-sm">
            <button type="button" onclick="document.getElementById('${rowId}').remove()" class="col-span-1 text-red-600 hover:text-red-800 text-center">
                <i class="fas fa-trash"></i>
            </button>
        `;
        container.appendChild(row);
    }
</script>
@endpush
@endsection