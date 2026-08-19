@extends('layouts.app')

@section('title', 'New Sale')

@section('content')
<div class="space-y-6">

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">🧾 New Sale Invoice</h1>
            <p class="text-sm text-gray-500">Create a new sale invoice</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('sales.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-medium px-4 py-2 rounded-lg transition">
                <i class="fas fa-arrow-left mr-2"></i> Back
            </a>
        </div>
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

    <form id="saleForm" action="{{ route('sales.store') }}" method="POST" class="space-y-6">
        @csrf

        <!-- ============================================
             CUSTOMER & INVOICE DETAILS
             ============================================ -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Customer *</label>
                    <select name="customer_id" id="customer_id" required
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Select Customer</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                {{ $customer->name }} ({{ $customer->phone ?? 'N/A' }})
                            </option>
                        @endforeach
                    </select>
                    @error('customer_id')
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
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ECR # (Khata book)</label>
                    <input type="text" name="ecr_number" value="{{ old('ecr_number') }}" placeholder="e.g. 4521"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <p class="text-xs text-gray-400 mt-1">Your physical receipt book number. Optional — leave blank to use the system invoice number only.</p>
                    @error('ecr_number')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- ============================================
             CUSTOMER OUTSTANDING ALERT
             ============================================ -->
        <div id="outstandingAlert" class="hidden bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-lg">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-yellow-400 text-xl"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-yellow-700" id="outstandingText">
                        <strong id="outstandingCount">0</strong> cylinder issue(s) outstanding for this customer.
                        <span id="outstandingDeposit">Total Deposit: Rs. 0</span>
                    </p>
                    <ul class="mt-1 text-xs text-yellow-600 space-y-0.5" id="outstandingList"></ul>
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
                    <p class="text-xs text-gray-400">Each line can carry gas, a cylinder, or both. To handle a customer returning empties and taking new filled ones in the same invoice, add one line per cylinder type/size with the right Action (Return / Issue / Sell).</p>
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

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Gas Subtotal</label>
                    <input type="text" id="subtotalDisplay" readonly
                           class="w-full rounded-lg bg-gray-100 border-gray-300 shadow-sm font-semibold">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cylinder Deposit Total</label>
                    <input type="text" id="depositTotalDisplay" readonly
                           class="w-full rounded-lg bg-yellow-50 border-yellow-200 shadow-sm font-semibold text-yellow-700">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cylinder Sale Total</label>
                    <input type="text" id="cylinderSaleTotalDisplay" readonly
                           class="w-full rounded-lg bg-blue-50 border-blue-200 shadow-sm font-semibold text-blue-700">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Refund for Returns (paid separately)</label>
                    <input type="text" id="returnRefundTotalDisplay" readonly
                           class="w-full rounded-lg bg-red-50 border-red-200 shadow-sm font-semibold text-red-700">
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
                        <option value="credit" {{ old('payment_method') == 'credit' ? 'selected' : '' }}>📋 Credit (Invoice)</option>
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
            <a href="{{ route('sales.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-3 px-6 rounded-lg transition">
                Cancel
            </a>
            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-8 rounded-lg transition">
                <i class="fas fa-save mr-2"></i> Save Invoice
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
    const availableCylinders = @json($availableCylinders);

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
            `<option value="${p.id}" data-price="${p.sale_price || 0}">${p.name} (${p.uom}) - Rs. ${Number(p.sale_price || 0).toFixed(2)}</option>`
        ).join('');

        const cylinderOptions = availableCylinders.map(c => {
            const gasName = c.gasProduct?.name || 'N/A';
            return `<option value="${c.id}" data-sale-price="${c.sale_price || 0}" data-available="${c.available_quantity}">
                        ${c.cylinder_number} - ${gasName} (${c.type || 'N/A'}) - Avail: ${c.available_quantity}
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
                            onchange="autoFillGasPrice(${n}); toggleCustomerCylinderFlag(${n}); updateGasConversion(${n})">
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
                    <p class="text-xs text-blue-600 mt-1" id="gas_conversion_${n}"></p>
                    <div class="text-right text-sm mt-2">
                        <span class="text-gray-500">Gas Total:</span>
                        <span class="font-semibold text-blue-700" id="gas_total_${n}">Rs. 0.00</span>
                    </div>
                    <div id="customer_cylinder_wrap_${n}" class="hidden mt-2 pt-2 border-t border-blue-100">
                        <label class="flex items-start gap-2 text-xs text-blue-800">
                            <input type="checkbox" name="items[${n}][is_customer_cylinder]" value="1" id="is_customer_cylinder_${n}"
                                   class="mt-0.5 rounded border-gray-300">
                            <span>No cylinder from our stock on this line &mdash; confirm this gas was refilled into the <strong>customer's own cylinder</strong>.</span>
                        </label>
                    </div>
                </div>

                <!-- Cylinder -->
                <div class="bg-purple-50 rounded-lg p-3 border border-purple-100">
                    <p class="text-xs font-semibold text-purple-700 mb-2 uppercase">🛢️ Cylinder (optional)</p>
                    <select name="items[${n}][cylinder_id]" id="cylinder_${n}"
                            class="w-full rounded-lg border-gray-300 shadow-sm text-sm mb-2"
                            onchange="autoFillCylinderPrice(${n}); toggleCustomerCylinderFlag(${n})">
                        <option value="">— No cylinder on this line —</option>
                        ${cylinderOptions}
                    </select>
                    <div class="grid grid-cols-3 gap-2">
                        <div>
                            <label class="block text-xs text-gray-500 mb-0.5">Action</label>
                            <select name="items[${n}][cylinder_action]" id="cylinder_action_${n}"
                                    class="w-full rounded-lg border-gray-300 shadow-sm text-sm" onchange="autoFillCylinderPrice(${n})">
                                <option value="issue">Issue (deposit)</option>
                                <option value="sell">Sell</option>
                                <option value="return">Return (from customer)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-0.5">Quantity</label>
                            <input type="number" step="1" min="1" name="items[${n}][cylinder_quantity]" id="cylinder_qty_${n}"
                                   class="w-full rounded-lg border-gray-300 shadow-sm text-sm" oninput="calculateTotals()">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-0.5" id="cylinder_price_label_${n}">Deposit / Unit</label>
                            <input type="number" step="0.01" min="0" name="items[${n}][cylinder_unit_price]" id="cylinder_price_${n}"
                                   class="w-full rounded-lg border-gray-300 shadow-sm text-sm" oninput="calculateTotals()">
                        </div>
                    </div>
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

    function toggleCustomerCylinderFlag(n) {
        const wrap = document.getElementById('customer_cylinder_wrap_' + n);
        const checkbox = document.getElementById('is_customer_cylinder_' + n);
        if (!wrap || !checkbox) return;

        const hasGas = document.getElementById('gas_product_' + n)?.value;
        const hasCylinder = document.getElementById('cylinder_' + n)?.value;

        if (hasGas && !hasCylinder) {
            wrap.classList.remove('hidden');
        } else {
            wrap.classList.add('hidden');
            checkbox.checked = false;
        }
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
        const hint = document.getElementById('gas_conversion_' + n);
        if (!hint) return;

        const productId = select.value;
        const product = gasProducts.find(p => String(p.id) === String(productId));

        if (!product || !qty || !product.density_kg_per_m3) {
            hint.textContent = '';
            return;
        }

        const uom = (product.uom || '').toLowerCase();
        const density = parseFloat(product.density_kg_per_m3);

        if (uom.includes('cubic meter')) {
            hint.textContent = `≈ ${(qty * density).toFixed(2)} kg`;
        } else if (uom.includes('kg') || uom.includes('kilogram')) {
            hint.textContent = `≈ ${(qty / density).toFixed(2)} m³`;
        } else {
            hint.textContent = '';
        }
    }

    function autoFillCylinderPrice(n) {
        const select = document.getElementById('cylinder_' + n);
        const actionSelect = document.getElementById('cylinder_action_' + n);
        const priceInput = document.getElementById('cylinder_price_' + n);
        const qtyInput = document.getElementById('cylinder_qty_' + n);
        const label = document.getElementById('cylinder_price_label_' + n);
        const selectedOption = select.options[select.selectedIndex];

        const labels = { sell: 'Sale Price / Unit', return: 'Refund / Unit (optional)', issue: 'Deposit / Unit' };
        label.textContent = labels[actionSelect.value] || 'Deposit / Unit';

        if (selectedOption && selectedOption.value) {
            if (!qtyInput.value) qtyInput.value = 1;
            if (actionSelect.value === 'sell') {
                priceInput.value = parseFloat(selectedOption.dataset.salePrice || 0).toFixed(2);
            } else if (actionSelect.value === 'return' && !priceInput.value) {
                priceInput.value = '0.00';
            }
        }
        calculateTotals();
    }

    // ============================================
    // CALCULATE TOTALS
    // ============================================
    function calculateTotals() {
        let subtotal = 0;
        let depositTotal = 0;
        let cylinderSaleTotal = 0;
        let returnRefundTotal = 0;

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
            const cylAction = document.getElementById('cylinder_action_' + i)?.value || 'issue';
            const cylTotal = cylQty * cylPrice;
            const cylTotalEl = document.getElementById('cylinder_total_' + i);
            if (cylTotalEl) cylTotalEl.textContent = 'Rs. ' + cylTotal.toFixed(2);

            if (cylAction === 'issue') {
                depositTotal += cylTotal;
            } else if (cylAction === 'sell') {
                cylinderSaleTotal += cylTotal;
            } else if (cylAction === 'return') {
                returnRefundTotal += cylTotal;
            }
        }

        const discount = parseFloat(document.getElementById('discount').value) || 0;
        const tax = parseFloat(document.getElementById('tax').value) || 0;
        const amountPaid = parseFloat(document.getElementById('amountPaid').value) || 0;

        // Return refunds are shown for reference but not netted into the grand
        // total — they're a separate cash-out event, not part of what the
        // customer owes on this invoice.
        const grandTotal = subtotal + depositTotal + cylinderSaleTotal - discount + tax;
        const balanceDue = grandTotal - amountPaid;

        document.getElementById('subtotalDisplay').value = 'Rs. ' + subtotal.toFixed(2);
        document.getElementById('depositTotalDisplay').value = 'Rs. ' + depositTotal.toFixed(2);
        document.getElementById('cylinderSaleTotalDisplay').value = 'Rs. ' + cylinderSaleTotal.toFixed(2);
        document.getElementById('returnRefundTotalDisplay').value = 'Rs. ' + returnRefundTotal.toFixed(2);
        document.getElementById('grandTotalDisplay').value = 'Rs. ' + grandTotal.toFixed(2);
        document.getElementById('balanceDisplay').value = 'Rs. ' + balanceDue.toFixed(2);
        document.getElementById('balanceDisplay').className = 'w-full rounded-lg bg-gray-100 border-gray-300 shadow-sm font-semibold ' +
            (balanceDue > 0 ? 'text-red-600' : 'text-green-600');
    }

    // ============================================
    // CUSTOMER OUTSTANDING
    // ============================================
    document.getElementById('customer_id').addEventListener('change', function() {
        const customerId = this.value;
        const alertDiv = document.getElementById('outstandingAlert');
        const list = document.getElementById('outstandingList');
        const countSpan = document.getElementById('outstandingCount');
        const depositSpan = document.getElementById('outstandingDeposit');

        if (!customerId) {
            alertDiv.classList.add('hidden');
            return;
        }

        fetch(`/cylinders/customer-outstanding/${customerId}`)
            .then(response => response.json())
            .then(data => {
                if (data.cylinders && data.cylinders.length > 0) {
                    alertDiv.classList.remove('hidden');
                    countSpan.textContent = data.count;
                    depositSpan.textContent = `Total Deposit: Rs. ${Number(data.total_deposit || 0).toFixed(2)}`;

                    list.innerHTML = data.cylinders.map(c =>
                        `<li>${c.cylinder_number} (${c.gas_name || 'N/A'}) - Qty ${c.quantity} - ${c.days_out} days out</li>`
                    ).join('');
                } else {
                    alertDiv.classList.add('hidden');
                }
            })
            .catch(() => alertDiv.classList.add('hidden'));
    });

    // ============================================
    // FORM SUBMIT VALIDATION
    // ============================================
    document.getElementById('saleForm').addEventListener('submit', function(e) {
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

            if (hasGas && !hasCylinder) {
                const confirmed = document.getElementById('is_customer_cylinder_' + i)?.checked;
                if (!confirmed) {
                    e.preventDefault();
                    alert(`Line #${i}: this gas line has no cylinder from our stock. Please confirm it was refilled into the customer's own cylinder.`);
                    return;
                }
            }
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
