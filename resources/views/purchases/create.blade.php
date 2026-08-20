@extends('layouts.app')

@section('title', 'New Purchase')

@section('content')
<div class="space-y-6">

    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">📦 New Purchase Order</h1>
            <p class="text-sm text-gray-500">Create a new purchase order</p>
        </div>
        <a href="{{ route('purchases.index') }}" class="text-gray-600 hover:text-gray-900">
            <i class="fas fa-arrow-left mr-2"></i> Back
        </a>
    </div>

    @if ($errors->any())
        <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded-lg">
            <p class="text-sm font-semibold text-red-700 mb-1">Please fix the following:</p>
            <ul class="text-xs text-red-600 list-disc list-inside space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="purchaseForm" action="{{ route('purchases.store') }}" method="POST" class="space-y-6">
        @csrf

        <!-- Supplier & Invoice Details -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Supplier *</label>
                    <select name="supplier_id" id="supplier_id" required
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Select Supplier</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                {{ $supplier->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('supplier_id')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date *</label>
                    <input type="date" name="date" id="date" value="{{ old('date', date('Y-m-d')) }}" required
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('date')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Delivery Date</label>
                    <input type="date" name="delivery_date" id="delivery_date" value="{{ old('delivery_date') }}"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('delivery_date')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Purchase Type *</label>
                    <select name="purchase_type" id="purchase_type" required
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="gas_only" {{ old('purchase_type') == 'gas_only' ? 'selected' : '' }}>🧪 Gas Only</option>
                        <option value="gas_with_cylinder" {{ old('purchase_type') == 'gas_with_cylinder' ? 'selected' : '' }}>🧪 + 🛢️ Gas + Cylinder</option>
                        <option value="cylinder_only" {{ old('purchase_type') == 'cylinder_only' ? 'selected' : '' }}>🛢️ Cylinder Only</option>
                        <option value="exchange" {{ old('purchase_type') == 'exchange' ? 'selected' : '' }}>🔄 Exchange</option>
                    </select>
                    @error('purchase_type')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Reference No.</label>
                    <input type="text" name="reference_no" value="{{ old('reference_no') }}"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                           placeholder="Supplier invoice / reference #">
                    @error('reference_no')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- ============================================
             LINE ITEMS
             ============================================ -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex justify-between items-center mb-4">
                <div>
                    <h3 class="text-lg font-semibold">📋 Line Items</h3>
                    <p class="text-xs text-gray-400">Each line can carry gas, a cylinder, or both.</p>
                </div>
                <button type="button" onclick="addItem()"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-lg transition">
                    <i class="fas fa-plus mr-2"></i> Add Line
                </button>
            </div>

            <div id="itemsContainer" class="space-y-4"></div>

            <p id="emptyItemsMsg" class="text-center text-gray-500 text-sm py-6">
                <i class="fas fa-plus-circle mr-2"></i> Click "Add Line" to add a gas product and/or a cylinder
            </p>
        </div>

        <!-- ============================================
             FINANCIAL SUMMARY
             ============================================ -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">💰 Financial Summary</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Gas Subtotal</label>
                    <input type="text" id="subtotalDisplay" readonly
                           class="w-full rounded-lg bg-gray-100 border-gray-300 shadow-sm font-semibold">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cylinder Total (purchased)</label>
                    <input type="text" id="cylinderTotalDisplay" readonly
                           class="w-full rounded-lg bg-purple-50 border-purple-200 shadow-sm font-semibold text-purple-700">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4 pt-4 border-t border-gray-200">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Discount (Rs.)</label>
                    <input type="number" step="0.01" name="discount" id="discount" value="{{ old('discount', 0) }}"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                           oninput="calculateTotals()">
                    @error('discount')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tax (Rs.)</label>
                    <input type="number" step="0.01" name="tax" id="tax" value="{{ old('tax', 0) }}"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                           oninput="calculateTotals()">
                    @error('tax')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Amount Paid</label>
                    <input type="number" step="0.01" name="amount_paid" id="amountPaid" value="{{ old('amount_paid', 0) }}"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                           oninput="calculateTotals()">
                    @error('amount_paid')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4 pt-4 border-t border-gray-200">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Grand Total</label>
                    <input type="text" id="grandTotalDisplay" readonly
                           class="w-full rounded-lg bg-green-100 border-green-300 shadow-sm font-bold text-lg text-green-700">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Balance Due</label>
                    <input type="text" id="balanceDisplay" readonly
                           class="w-full rounded-lg bg-gray-100 border-gray-300 shadow-sm font-semibold text-red-600">
                </div>
            </div>
        </div>

        <!-- ============================================
             PAYMENT METHOD
             ============================================ -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">💳 Payment Details</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Payment Method *</label>
                    <select name="payment_method" id="payment_method" required
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="cash" {{ old('payment_method', 'cash') == 'cash' ? 'selected' : '' }}>💵 Cash</option>
                        <option value="bank_transfer" {{ old('payment_method') == 'bank_transfer' ? 'selected' : '' }}>🏦 Bank Transfer</option>
                        <option value="cheque" {{ old('payment_method') == 'cheque' ? 'selected' : '' }}>📝 Cheque</option>
                        <option value="online" {{ old('payment_method') == 'online' ? 'selected' : '' }}>💳 Online Payment</option>
                        <option value="credit" {{ old('payment_method') == 'credit' ? 'selected' : '' }}>📋 Credit</option>
                    </select>
                    @error('payment_method')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-400 mt-1">💡 Accounting entries are posted automatically based on this.</p>
                </div>
            </div>
        </div>

        <!-- Notes -->
        <div class="bg-white rounded-lg shadow p-6">
            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
            <textarea name="notes" rows="2"
                      class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                      placeholder="Any special instructions...">{{ old('notes') }}</textarea>
        </div>

        <!-- Submit -->
        <div class="flex justify-end space-x-4">
            <a href="{{ route('purchases.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-3 px-6 rounded-lg transition">
                Cancel
            </a>
            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-8 rounded-lg transition">
                <i class="fas fa-save mr-2"></i> Save Purchase
            </button>
        </div>

    </form>
</div>

@push('scripts')
<script>
    // ============================================
    // DATA
    // ============================================
    let itemCount = 0;
    const gasProducts = @json($gasProducts);
    const cylinders = @json($cylinders);

    // ============================================
    // ADD / REMOVE LINE
    // ============================================
    function addItem() {
        const container = document.getElementById('itemsContainer');
        document.getElementById('emptyItemsMsg').style.display = 'none';

        itemCount++;
        const n = itemCount;
        const rowId = 'item_' + n;

        const gasOptions = gasProducts.map(p =>
            `<option value="${p.id}" data-price="${p.purchase_price || 0}">${p.name} (${p.uom}) - Rs. ${Number(p.purchase_price || 0).toFixed(2)}</option>`
        ).join('');

        const cylinderOptions = cylinders.map(c => {
            const gasName = c.gas_product?.name || c.gasProduct?.name || 'N/A';
            return `<option value="${c.id}" data-purchase-price="${c.purchase_price || 0}" data-stock="${c.stock_quantity}">
                        ${c.cylinder_number} - ${gasName} (${c.type || 'N/A'}) - Stock: ${c.stock_quantity}
                    </option>`;
        }).join('');

        const row = document.createElement('div');
        row.id = rowId;
        row.className = 'border border-gray-200 rounded-lg p-4';
        row.innerHTML = `
            <div class="flex justify-between items-center mb-3">
                <h4 class="font-semibold text-gray-600 text-sm">Line #${n}</h4>
                <button type="button" onclick="removeItem('${rowId}')" class="text-red-600 hover:text-red-800 transition text-sm">
                    <i class="fas fa-trash mr-1"></i> Remove
                </button>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <!-- Gas -->
                <div class="bg-blue-50 rounded-lg p-3 border border-blue-100">
                    <p class="text-xs font-semibold text-blue-700 mb-2 uppercase">🧪 Gas (optional)</p>
                    <select name="items[${n}][gas_product_id]" id="gas_product_${n}"
                            class="w-full rounded-lg border-gray-300 shadow-sm text-sm mb-2"
                            onchange="autoFillGasPrice(${n}); updateGasConversion(${n})">
                        <option value="">— No gas on this line —</option>
                        ${gasOptions}
                    </select>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs text-gray-500 mb-0.5">Quantity</label>
                            <input type="number" step="0.01" min="0.01" name="items[${n}][gas_quantity]" id="gas_qty_${n}"
                                   class="w-full rounded-lg border-gray-300 shadow-sm text-sm" oninput="calculateTotals(); updateGasConversion(${n})">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-0.5">Price / Unit</label>
                            <input type="number" step="0.01" min="0" name="items[${n}][gas_price]" id="gas_price_${n}"
                                   class="w-full rounded-lg border-gray-300 shadow-sm text-sm" oninput="calculateTotals()">
                        </div>
                    </div>
                    <div id="gas_conversion_wrap_${n}" class="hidden mt-2">
                        <label class="block text-xs text-gray-500 mb-0.5" id="gas_conversion_label_${n}">Equivalent</label>
                        <input type="text" id="gas_conversion_${n}" disabled
                               class="w-full rounded-lg bg-gray-100 border-gray-300 shadow-sm text-sm text-gray-500">
                    </div>
                    <div class="text-right text-sm mt-2">
                        <span class="text-gray-500">Gas Total:</span>
                        <span class="font-semibold text-blue-700" id="gas_total_${n}">Rs. 0.00</span>
                    </div>
                </div>

                <!-- Cylinder -->
                <div class="bg-purple-50 rounded-lg p-3 border border-purple-100">
                    <p class="text-xs font-semibold text-purple-700 mb-2 uppercase">🛢️ Cylinder (optional)</p>
                    <select name="items[${n}][cylinder_id]" id="cylinder_${n}"
                            class="w-full rounded-lg border-gray-300 shadow-sm text-sm mb-2"
                            onchange="autoFillCylinderPrice(${n})">
                        <option value="">— No cylinder on this line —</option>
                        ${cylinderOptions}
                    </select>
                    <div class="grid grid-cols-3 gap-2">
                        <div>
                            <label class="block text-xs text-gray-500 mb-0.5">Action</label>
                            <select name="items[${n}][cylinder_action]" id="cylinder_action_${n}"
                                    class="w-full rounded-lg border-gray-300 shadow-sm text-sm" onchange="autoFillCylinderPrice(${n})">
                                <option value="purchase">Purchase (new/empty)</option>
                                <option value="exchange">Exchange (empty for filled)</option>
                                <option value="return_to_supplier">Return to supplier</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-0.5">Quantity</label>
                            <input type="number" step="1" min="1" name="items[${n}][cylinder_quantity]" id="cylinder_qty_${n}"
                                   class="w-full rounded-lg border-gray-300 shadow-sm text-sm" oninput="calculateTotals()">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-0.5" id="cylinder_price_label_${n}">Unit Price</label>
                            <input type="number" step="0.01" min="0" name="items[${n}][cylinder_unit_price]" id="cylinder_price_${n}"
                                   class="w-full rounded-lg border-gray-300 shadow-sm text-sm" oninput="calculateTotals()">
                        </div>
                    </div>
                    <p class="text-xs text-gray-400 mt-1">Unit price only affects totals for "Purchase" and "Return to supplier".</p>
                    <div class="text-right text-sm mt-2">
                        <span class="text-gray-500">Cylinder Total:</span>
                        <span class="font-semibold text-purple-700" id="cylinder_total_${n}">Rs. 0.00</span>
                    </div>
                </div>
            </div>

            <div class="mt-3">
                <input type="text" name="items[${n}][notes]" placeholder="Line notes (optional)"
                       class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
            </div>
        `;

        container.appendChild(row);
        calculateTotals();
    }

    function removeItem(rowId) {
        const row = document.getElementById(rowId);
        if (row) {
            row.remove();
            calculateTotals();

            if (document.getElementById('itemsContainer').children.length === 0) {
                document.getElementById('emptyItemsMsg').style.display = '';
            }
        }
    }

    function autoFillGasPrice(n) {
        const select = document.getElementById('gas_product_' + n);
        const priceInput = document.getElementById('gas_price_' + n);
        const qtyInput = document.getElementById('gas_qty_' + n);
        const selectedOption = select.options[select.selectedIndex];

        if (selectedOption && selectedOption.value) {
            priceInput.value = parseFloat(selectedOption.dataset.price || 0).toFixed(2);
            if (!qtyInput.value) qtyInput.value = 1;
        } else {
            priceInput.value = '';
        }
        calculateTotals();
    }

    // ============================================
    // GAS QUANTITY UNIT CONVERSION (KG <-> Cubic Meter)
    // ============================================
    function updateGasConversion(n) {
        const select = document.getElementById('gas_product_' + n);
        const qty = parseFloat(document.getElementById('gas_qty_' + n)?.value) || 0;
        const wrap = document.getElementById('gas_conversion_wrap_' + n);
        const label = document.getElementById('gas_conversion_label_' + n);
        const field = document.getElementById('gas_conversion_' + n);
        if (!wrap) return;

        const productId = select.value;
        const product = gasProducts.find(p => String(p.id) === String(productId));

        if (!product || !product.density_kg_per_m3) {
            wrap.classList.add('hidden');
            return;
        }

        const uom = (product.uom || '').toLowerCase();
        const density = parseFloat(product.density_kg_per_m3);

        if (uom.includes('cubic meter')) {
            label.textContent = 'Equivalent in KG — disabled, auto-calculated';
            field.value = qty ? (qty * density).toFixed(2) + ' kg' : '';
            wrap.classList.remove('hidden');
        } else if (uom === 'kg' || uom === 'kilogram') {
            label.textContent = 'Equivalent in Cubic Meter — disabled, auto-calculated';
            field.value = qty ? (qty / density).toFixed(2) + ' m³' : '';
            wrap.classList.remove('hidden');
        } else {
            wrap.classList.add('hidden');
        }
    }

    function autoFillCylinderPrice(n) {
        const select = document.getElementById('cylinder_' + n);
        const actionSelect = document.getElementById('cylinder_action_' + n);
        const priceInput = document.getElementById('cylinder_price_' + n);
        const qtyInput = document.getElementById('cylinder_qty_' + n);
        const label = document.getElementById('cylinder_price_label_' + n);
        const selectedOption = select.options[select.selectedIndex];

        label.textContent = actionSelect.value === 'return_to_supplier' ? 'Credit / Unit' : 'Unit Price';

        if (selectedOption && selectedOption.value) {
            if (!qtyInput.value) qtyInput.value = 1;
            if (actionSelect.value === 'purchase') {
                priceInput.value = parseFloat(selectedOption.dataset.purchasePrice || 0).toFixed(2);
            }
        }
        calculateTotals();
    }

    // ============================================
    // CALCULATE TOTALS
    // ============================================
    function calculateTotals() {
        let subtotal = 0;
        let cylinderTotal = 0;

        for (let i = 1; i <= itemCount; i++) {
            const row = document.getElementById('item_' + i);
            if (!row) continue;

            const gasQty = parseFloat(document.getElementById('gas_qty_' + i)?.value) || 0;
            const gasPrice = parseFloat(document.getElementById('gas_price_' + i)?.value) || 0;
            const gasTotal = gasQty * gasPrice;
            subtotal += gasTotal;
            const gasTotalEl = document.getElementById('gas_total_' + i);
            if (gasTotalEl) gasTotalEl.textContent = 'Rs. ' + gasTotal.toFixed(2);

            const cylQty = parseFloat(document.getElementById('cylinder_qty_' + i)?.value) || 0;
            const cylPrice = parseFloat(document.getElementById('cylinder_price_' + i)?.value) || 0;
            const cylAction = document.getElementById('cylinder_action_' + i)?.value || 'purchase';
            const cylTotal = cylQty * cylPrice;
            const cylTotalEl = document.getElementById('cylinder_total_' + i);
            if (cylTotalEl) cylTotalEl.textContent = 'Rs. ' + cylTotal.toFixed(2);

            // Only "purchase" actions add to the cylinder_total that feeds the grand total
            // (matches PurchaseItem::calculateTotals on the backend, which sums cylinder_action = purchase).
            if (cylAction === 'purchase') {
                cylinderTotal += cylTotal;
            }
        }

        const discount = parseFloat(document.getElementById('discount').value) || 0;
        const tax = parseFloat(document.getElementById('tax').value) || 0;
        const amountPaid = parseFloat(document.getElementById('amountPaid').value) || 0;

        const grandTotal = subtotal + cylinderTotal - discount + tax;
        const balanceDue = grandTotal - amountPaid;

        document.getElementById('subtotalDisplay').value = 'Rs. ' + subtotal.toFixed(2);
        document.getElementById('cylinderTotalDisplay').value = 'Rs. ' + cylinderTotal.toFixed(2);
        document.getElementById('grandTotalDisplay').value = 'Rs. ' + grandTotal.toFixed(2);
        document.getElementById('balanceDisplay').value = 'Rs. ' + balanceDue.toFixed(2);
        document.getElementById('balanceDisplay').className = 'w-full rounded-lg bg-gray-100 border-gray-300 shadow-sm font-semibold ' +
            (balanceDue > 0 ? 'text-red-600' : 'text-green-600');
    }

    // ============================================
    // FORM SUBMIT VALIDATION
    // ============================================
    document.getElementById('purchaseForm').addEventListener('submit', function(e) {
        if (itemCount === 0) {
            e.preventDefault();
            alert('Please add at least one line item.');
            return;
        }

        let hasAnyContent = false;
        for (let i = 1; i <= itemCount; i++) {
            const row = document.getElementById('item_' + i);
            if (!row) continue;
            const hasGas = document.getElementById('gas_product_' + i)?.value;
            const hasCylinder = document.getElementById('cylinder_' + i)?.value;
            if (hasGas || hasCylinder) hasAnyContent = true;
        }

        if (!hasAnyContent) {
            e.preventDefault();
            alert('Each line must have a gas product, a cylinder, or both. Please fill in at least one line.');
        }
    });

    // ============================================
    // INITIALIZE
    // ============================================
    document.addEventListener('DOMContentLoaded', function() {
        addItem();
        calculateTotals();
    });
</script>
@endpush
@endsection
