@extends('layouts.app')

@section('title', 'Edit Gas Product')

@section('content')
<div class="space-y-6">
    
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Edit Gas Product</h1>
            <p class="text-sm text-gray-500">Update: <strong>{{ $gasProduct->name }}</strong></p>
        </div>
        <a href="{{ route('gas-products.index') }}" class="text-gray-600 hover:text-gray-900">
            <i class="fas fa-arrow-left mr-2"></i> Back
        </a>
    </div>

    <div class="bg-white rounded-lg shadow p-6 max-w-3xl">
        <form method="POST" action="{{ route('gas-products.update', $gasProduct) }}">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Name *
                        <x-help-tip text="The gas's real name (e.g. Oxygen, Nitrogen)." />
                    </label>
                    <input type="text" name="name" value="{{ old('name', $gasProduct->name) }}" required
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('name')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Code *
                        <x-help-tip text="A short unique reference code for this product. Used internally and on exports/reports." />
                    </label>
                    <input type="text" name="code" value="{{ old('code', $gasProduct->code) }}" required
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('code')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Unit of Measure (UOM) *
                        <x-help-tip text="What unit this gas's stock and price are counted in. Choosing Cubic Meter or KG unlocks live conversion between the two elsewhere in the app." />
                    </label>
                    <select name="uom" id="gas_uom" required
                            onchange="toggleDensityField()"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @foreach($units as $unit)
                            <option value="{{ $unit->name }}" {{ old('uom', $gasProduct->uom) == $unit->name ? 'selected' : '' }}>{{ $unit->name }}</option>
                        @endforeach
                    </select>
                    @can('units.manage')
                    <p class="text-xs text-gray-400 mt-1">
                        Don't see the unit you need? <a href="{{ route('units.index') }}" class="text-blue-600 hover:underline" target="_blank">Manage units</a>.
                    </p>
                    @endcan
                    @error('uom')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div id="density_wrap">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Density (KG per Cubic Meter)
                        <x-help-tip text="How much 1 m³ of this specific gas weighs. Different gases weigh differently, so this must be accurate for the KG conversion to be right." />
                    </label>
                    <input type="number" step="0.0001" min="0.0001" name="density_kg_per_m3" id="density_kg_per_m3"
                           value="{{ old('density_kg_per_m3', $gasProduct->density_kg_per_m3) }}"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <p class="text-xs text-gray-400 mt-1">Used to show this gas's Cubic Meter stock in KG automatically.</p>
                    @error('density_kg_per_m3')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Purchase Price (Rs.) *
                        <x-help-tip text="What you pay per unit (per your chosen UOM) when buying this gas from a supplier." />
                    </label>
                    <input type="number" step="0.01" name="purchase_price" value="{{ old('purchase_price', $gasProduct->purchase_price) }}" required
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('purchase_price')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Sale Price (Rs.) *
                        <x-help-tip text="What you charge customers per unit. Must be at or above Purchase Price." />
                    </label>
                    <input type="number" step="0.01" name="sale_price" value="{{ old('sale_price', $gasProduct->sale_price) }}" required
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('sale_price')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        <span id="current_stock_label">Current Stock</span>
                        <x-help-tip text="How much of this gas you currently have. Feeds Stock Value, low-stock alerts, and how much can be sold/transferred." />
                    </label>
                    <input type="number" step="0.01" name="current_stock" id="current_stock" value="{{ old('current_stock', $gasProduct->current_stock) }}"
                           oninput="updateStockUnitFields()"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('current_stock')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div id="current_stock_other_wrap" class="hidden">
                    <label class="block text-sm font-medium text-gray-700 mb-1" id="current_stock_other_label">Equivalent</label>
                    <input type="text" id="current_stock_other" disabled
                           class="w-full rounded-lg bg-gray-100 border-gray-300 shadow-sm text-gray-500">
                    <p class="text-xs text-gray-400 mt-1">Auto-calculated from the density above — not entered directly.</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Minimum Stock Level
                        <x-help-tip text="The threshold below which this product shows as Low Stock / Out of Stock status on the list page. Doesn't block sales." />
                    </label>
                    <input type="number" step="0.01" name="minimum_stock_level" value="{{ old('minimum_stock_level', $gasProduct->minimum_stock_level) }}"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('minimum_stock_level')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <div class="flex items-center mt-2">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $gasProduct->is_active) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <label class="ml-2 text-sm text-gray-700">Active</label>
                    </div>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="2" 
                              class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('description', $gasProduct->description) }}</textarea>
                    @error('description')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex justify-end space-x-4 mt-6 pt-6 border-t border-gray-200">
                <a href="{{ route('gas-products.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-6 rounded-lg transition">
                    Cancel
                </a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-lg transition">
                    <i class="fas fa-save mr-2"></i> Update Product
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function toggleDensityField() {
        const uom = document.getElementById('gas_uom').value;
        const wrap = document.getElementById('density_wrap');
        wrap.classList.toggle('hidden', !uom.toLowerCase().includes('cubic meter'));
        updateStockUnitFields();
    }

    // ============================================
    // CURRENT STOCK: KG <-> Cubic Meter (one is always the entered unit,
    // matching the product's own UOM; the other is disabled and computed
    // live from density, never entered directly)
    // ============================================
    function updateStockUnitFields() {
        const uom = (document.getElementById('gas_uom').value || '').toLowerCase();
        const density = parseFloat(document.getElementById('density_kg_per_m3')?.value) || 0;
        const stock = parseFloat(document.getElementById('current_stock').value) || 0;

        const primaryLabel = document.getElementById('current_stock_label');
        const otherWrap = document.getElementById('current_stock_other_wrap');
        const otherLabel = document.getElementById('current_stock_other_label');
        const otherInput = document.getElementById('current_stock_other');

        if (uom.includes('cubic meter') && density > 0) {
            primaryLabel.textContent = 'Current Stock (Cubic Meter)';
            otherLabel.textContent = 'Current Stock (KG) — disabled, auto-calculated';
            otherInput.value = (stock * density).toFixed(2);
            otherWrap.classList.remove('hidden');
        } else if ((uom === 'kg' || uom === 'kilogram') && density > 0) {
            primaryLabel.textContent = 'Current Stock (KG)';
            otherLabel.textContent = 'Current Stock (Cubic Meter) — disabled, auto-calculated';
            otherInput.value = (stock / density).toFixed(2);
            otherWrap.classList.remove('hidden');
        } else {
            primaryLabel.textContent = 'Current Stock';
            otherWrap.classList.add('hidden');
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        toggleDensityField();
        document.getElementById('density_kg_per_m3').addEventListener('input', updateStockUnitFields);
    });
</script>
@endpush
@endsection