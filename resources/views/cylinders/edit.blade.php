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
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cylinder Number *
                        <x-help-tip text="Identifies this cylinder TYPE record, not a single physical cylinder. Changing it doesn't affect any linked sales/purchases history." />
                    </label>
                    <input type="text" name="cylinder_number" value="{{ old('cylinder_number', $cylinder->cylinder_number) }}" required
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('cylinder_number')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Gas Product *
                        <x-help-tip text="Which gas this cylinder type holds. Changing it doesn't retroactively re-suggest prices unless you also touch the Type field." />
                    </label>
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
                    <label class="block text-sm font-medium text-gray-700 mb-1">Type *
                        <x-help-tip text="The size/category from your Cylinder Types list. Changing it also updates the Capacity field below automatically." />
                    </label>
                    <select name="type" id="type" required
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            onchange="autoDetectPrice()">
                        <option value="">Select Type</option>
                        @foreach($cylinderTypes as $cylinderType)
                            <option value="{{ $cylinderType->name }}"
                                    data-capacity="{{ $cylinderType->capacity }}"
                                    {{ old('type', $cylinder->type) == $cylinderType->name ? 'selected' : '' }}>
                                {{ $cylinderType->name }}{{ $cylinderType->capacity ? ' (' . $cylinderType->capacity . ' m3)' : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('type')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                    @can('cylinder_types.manage')
                    <p class="text-xs text-gray-400 mt-1">
                        Don't see the type you need? <a href="{{ route('cylinder-types.index') }}" class="text-blue-600 hover:underline" target="_blank">Manage cylinder types</a>.
                    </p>
                    @endcan
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Manufacturer
                        <x-help-tip text="Optional. Who made this cylinder — for your own records only, doesn't affect stock or pricing." />
                    </label>
                    <input type="text" name="manufacturer" value="{{ old('manufacturer', $cylinder->manufacturer) }}"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('manufacturer')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tare Weight (KG) *
                        <x-help-tip text="The empty cylinder's own weight. Used to work out how much gas is actually inside when weighed full vs. empty." />
                    </label>
                    <input type="number" step="0.01" name="tare_weight" value="{{ old('tare_weight', $cylinder->tare_weight) }}" required
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('tare_weight')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Capacity (m3)
                        <x-help-tip text="Read-only — comes from the Type selected above. To change it, edit the Type itself in Cylinder Types." />
                    </label>
                    <input type="number" step="0.01" name="capacity" id="capacity" value="{{ old('capacity', $cylinder->capacity) }}" required readonly
                           class="w-full rounded-lg bg-gray-100 border-gray-300 shadow-sm text-gray-600">
                    <p class="text-xs text-gray-400 mt-1">Comes from the selected Type — set it there if it's wrong.</p>
                    @error('capacity')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Stock Quantity *
                        <x-help-tip text="Total units of this type the shop owns. This form is only reachable when nothing is currently issued to a customer, so it's always safe to change freely here." />
                    </label>
                    <input type="number" name="stock_quantity" id="stock_quantity" value="{{ old('stock_quantity', $cylinder->stock_quantity) }}" required min="0"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                           oninput="calculatePrices(); syncFilledMax()">
                    @error('stock_quantity')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">— Of Which, Filled *
                        <x-help-tip text="How many of the Stock Quantity above currently have gas in them. The rest count as empty and can't be sold/issued until a Gas Transfer fills them." />
                    </label>
                    <input type="number" name="filled_quantity" id="filled_quantity" value="{{ old('filled_quantity', $cylinder->filled_quantity) }}" required min="0"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <p class="text-xs text-gray-400 mt-1">The rest are counted as empty, waiting to be filled.</p>
                    @error('filled_quantity')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Price Section -->
                <div class="md:col-span-2 bg-blue-50 p-4 rounded-lg border border-blue-200">
                    <h3 class="text-sm font-semibold text-blue-700 mb-3">💰 Price Details</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Purchase Price (Rs.) *
                                <x-help-tip text="What you paid per unit. Feeds Total Asset Value and the Margin % below — doesn't affect what you charge customers." />
                            </label>
                            <input type="number" step="0.01" name="purchase_price" id="purchase_price" value="{{ old('purchase_price', $cylinder->purchase_price) }}" required
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                   oninput="calculatePrices()">
                            @error('purchase_price')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Sale Price (Rs.) *
                                <x-help-tip text="What you charge per unit when selling this cylinder outright. Must be at or above Purchase Price." />
                            </label>
                            <input type="number" step="0.01" name="sale_price" id="sale_price" value="{{ old('sale_price', $cylinder->sale_price) }}" required
                                   class="w-full rounded-lg border-green-300 bg-green-50 shadow-sm focus:border-blue-500 focus:ring-blue-500 font-bold text-green-700"
                                   oninput="calculatePrices()">
                            @error('sale_price')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Margin (%)
                                <x-help-tip text="Read-only. Auto-calculated as (Sale Price − Purchase Price) ÷ Purchase Price × 100." />
                            </label>
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
                    <label class="block text-sm font-medium text-gray-700 mb-1">Supplier
                        <x-help-tip text="Optional. Who you bought this batch from — for your own records, shown on the cylinder's detail page." />
                    </label>
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
                    <label class="block text-sm font-medium text-gray-700 mb-1">Purchase Date
                        <x-help-tip text="Optional. When this batch was bought — for your own records only." />
                    </label>
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
        const typeCapacity = parseFloat(typeOption.dataset.capacity) || 0;

        const capacityInput = document.getElementById('capacity');
        if (capacityInput) {
            capacityInput.value = typeCapacity > 0 ? typeCapacity.toFixed(2) : '';
        }

        if (gasPurchasePrice > 0) {
            purchasePriceInput.value = gasPurchasePrice.toFixed(2);
        }

        let salePrice = gasSalePrice > 0 ? gasSalePrice : (gasPurchasePrice * 1.2);
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

    function syncFilledMax() {
        const stockInput = document.getElementById('stock_quantity');
        const filledInput = document.getElementById('filled_quantity');
        const stock = parseInt(stockInput.value) || 0;

        filledInput.max = stock;
        if (parseInt(filledInput.value) > stock) {
            filledInput.value = stock;
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Only recompute the read-only margin/totals here — don't call
        // autoDetectPrice() on load, or it would silently overwrite this
        // cylinder's actual stored capacity/prices with fresh suggestions
        // before the user has touched anything.
        calculatePrices();
        syncFilledMax();
    });
</script>
@endpush
@endsection