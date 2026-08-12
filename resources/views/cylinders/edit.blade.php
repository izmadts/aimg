@extends('layouts.app')

@section('title', 'Edit Cylinder')

@section('content')
<div class="space-y-6">
    
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Edit Cylinder</h1>
            <p class="text-sm text-gray-500">Update: <strong>{{ $cylinder->cylinder_number }}</strong></p>
        </div>
        <a href="{{ route('cylinders.index') }}" class="text-gray-600 hover:text-gray-900">
            <i class="fas fa-arrow-left mr-2"></i> Back
        </a>
    </div>

    <div class="bg-white rounded-lg shadow p-6 max-w-3xl">
        <form method="POST" action="{{ route('cylinders.update', $cylinder) }}">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cylinder Number *</label>
                    <input type="text" name="cylinder_number" value="{{ old('cylinder_number', $cylinder->cylinder_number) }}" required
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('cylinder_number')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Gas Product *</label>
                    <select name="gas_product_id" id="gas_product_id" required
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            onchange="autoDetectPrice()">
                        <option value="">Select Gas</option>
                        @foreach($gasProducts as $product)
                            <option value="{{ $product->id }}" 
                                    data-purchase-price="{{ $product->purchase_price }}"
                                    data-sale-price="{{ $product->sale_price }}"
                                    {{ old('gas_product_id', $cylinder->gas_product_id) == $product->id ? 'selected' : '' }}>
                                {{ $product->name }} ({{ $product->uom }})
                            </option>
                        @endforeach
                    </select>
                    @error('gas_product_id')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Type *</label>
                    <select name="type" id="type" required
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            onchange="autoDetectPrice()">
                        <option value="">Select Type</option>
                        <option value="Extra Small" data-premium="1500" {{ old('type', $cylinder->type) == 'Extra Small' ? 'selected' : '' }}>Extra Small (1.7 m3)</option>
                        <option value="Small" data-premium="2000" {{ old('type', $cylinder->type) == 'Small' ? 'selected' : '' }}>Small (3.4 m3)</option>
                        <option value="Medium" data-premium="3000" {{ old('type', $cylinder->type) == 'Medium' ? 'selected' : '' }}>Medium (8.8 m3)</option>
                        <option value="Large" data-premium="3500" {{ old('type', $cylinder->type) == 'Large' ? 'selected' : '' }}>Large (9.9 m3)</option>
                    </select>
                    @error('type')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Manufacturer</label>
                    <input type="text" name="manufacturer" value="{{ old('manufacturer', $cylinder->manufacturer) }}"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('manufacturer')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tare Weight (KG) *</label>
                    <input type="number" step="0.01" name="tare_weight" value="{{ old('tare_weight', $cylinder->tare_weight) }}" required
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('tare_weight')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Capacity *</label>
                    <input type="number" step="0.01" name="capacity" value="{{ old('capacity', $cylinder->capacity) }}" required
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('capacity')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Stock Quantity *</label>
                    <input type="number" name="stock_quantity" value="{{ old('stock_quantity', $cylinder->stock_quantity) }}" required
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                           oninput="calculatePrices()">
                    @error('stock_quantity')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Price Section -->
                <div class="md:col-span-2 bg-blue-50 p-4 rounded-lg border border-blue-200">
                    <h3 class="text-sm font-semibold text-blue-700 mb-3">💰 Price Details</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Purchase Price (Rs.) *</label>
                            <input type="number" step="0.01" name="purchase_price" value="{{ old('purchase_price', $cylinder->purchase_price) }}" required
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                   oninput="calculatePrices()">
                            @error('purchase_price')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Sale Price (Rs.) *</label>
                            <input type="number" step="0.01" name="sale_price" value="{{ old('sale_price', $cylinder->sale_price) }}" required
                                   class="w-full rounded-lg border-green-300 bg-green-50 shadow-sm focus:border-blue-500 focus:ring-blue-500 font-bold text-green-700"
                                   oninput="calculatePrices()">
                            @error('sale_price')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Margin (%)</label>
                            <input type="text" id="margin_percentage" readonly
                                   class="w-full rounded-lg bg-gray-100 border-gray-300 shadow-sm font-semibold text-blue-600">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-3 pt-3 border-t border-blue-200">
                        <div>
                            <label class="block text-xs font-medium text-gray-500">Total Asset Value</label>
                            <p class="text-lg font-bold text-blue-600" id="totalAssetValue">Rs. 0.00</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500">Total Sale Value</label>
                            <p class="text-lg font-bold text-green-600" id="totalSaleValue">Rs. 0.00</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500">Total Profit</label>
                            <p class="text-lg font-bold text-purple-600" id="totalProfit">Rs. 0.00</p>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Supplier</label>
                    <select name="supplier_id" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Select Supplier</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" {{ old('supplier_id', $cylinder->supplier_id) == $supplier->id ? 'selected' : '' }}>
                                {{ $supplier->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('supplier_id')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Purchase Date</label>
                    <input type="date" name="purchase_date" value="{{ old('purchase_date', $cylinder->purchase_date?->format('Y-m-d')) }}"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('purchase_date')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea name="notes" rows="2"
                              class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('notes', $cylinder->notes) }}</textarea>
                    @error('notes')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex justify-end space-x-4 mt-6 pt-6 border-t border-gray-200">
                <a href="{{ route('cylinders.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-6 rounded-lg transition">
                    Cancel
                </a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-lg transition">
                    <i class="fas fa-save mr-2"></i> Update Cylinder
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function autoDetectPrice() {
        const gasSelect = document.getElementById('gas_product_id');
        const typeSelect = document.getElementById('type');
        const purchasePriceInput = document.getElementById('purchase_price');
        const salePriceInput = document.getElementById('sale_price');

        const selectedOption = gasSelect.options[gasSelect.selectedIndex];
        const gasPurchasePrice = parseFloat(selectedOption.dataset.purchasePrice) || 0;
        const gasSalePrice = parseFloat(selectedOption.dataset.salePrice) || 0;

        const typeOption = typeSelect.options[typeSelect.selectedIndex];
        const premium = parseFloat(typeOption.dataset.premium) || 500;

        if (gasPurchasePrice > 0) {
            purchasePriceInput.value = gasPurchasePrice.toFixed(2);
        }

        let salePrice = 0;
        if (gasSalePrice > 0) {
            salePrice = gasSalePrice + premium;
        } else {
            salePrice = (gasPurchasePrice * 1.2) + premium;
        }
        salePriceInput.value = salePrice.toFixed(2);

        calculatePrices();
    }

    function calculatePrices() {
        const purchasePrice = parseFloat(document.getElementById('purchase_price').value) || 0;
        const salePrice = parseFloat(document.getElementById('sale_price').value) || 0;
        const stockQty = parseInt(document.getElementById('stock_quantity').value) || 1;

        let margin = 0;
        if (purchasePrice > 0) {
            margin = ((salePrice - purchasePrice) / purchasePrice) * 100;
        }
        document.getElementById('margin_percentage').value = margin.toFixed(2) + '%';

        const totalAsset = purchasePrice * stockQty;
        const totalSale = salePrice * stockQty;
        const totalProfit = totalSale - totalAsset;

        document.getElementById('totalAssetValue').textContent = 'Rs. ' + totalAsset.toFixed(2);
        document.getElementById('totalSaleValue').textContent = 'Rs. ' + totalSale.toFixed(2);
        document.getElementById('totalProfit').textContent = 'Rs. ' + totalProfit.toFixed(2);
    }

    document.addEventListener('DOMContentLoaded', function() {
        if (document.getElementById('gas_product_id').value && document.getElementById('type').value) {
            autoDetectPrice();
        } else {
            calculatePrices();
        }
    });
</script>
@endpush
@endsection