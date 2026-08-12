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
                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date *</label>
                    <input type="date" name="date" id="date" value="{{ date('Y-m-d') }}" required
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Purchase Type</label>
                    <select name="purchase_type" id="purchase_type" required
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            onchange="toggleFields()">
                        <option value="gas_only">Gas Only</option>
                        <option value="gas_with_cylinder">Gas + Cylinder</option>
                        <option value="cylinder_only">Cylinder Only</option>
                        <option value="exchange">Exchange</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Gas Section -->
        <div id="gasSection" class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">🧪 Gas Details</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Gas Product</label>
                    <select name="gas_product_id" id="gas_product_id"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            onchange="autoFillGasPrice()">
                        <option value="">Select Gas</option>
                        @foreach($gasProducts as $product)
                            <option value="{{ $product->id }}" data-price="{{ $product->purchase_price }}">
                                {{ $product->name }} ({{ $product->uom }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
                    <input type="number" step="0.01" name="gas_quantity" id="gas_quantity" value="1"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                           oninput="calculateTotals()">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Price per Unit (Rs.)</label>
                    <input type="number" step="0.01" name="gas_price" id="gas_price"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                           oninput="calculateTotals()">
                </div>
            </div>
            <div class="mt-2 text-right">
                <span class="text-sm text-gray-500">Gas Total: </span>
                <span class="text-lg font-bold text-blue-600" id="gasTotalDisplay">Rs. 0.00</span>
                <input type="hidden" name="gas_total" id="gas_total" value="0">
            </div>
        </div>

        <!-- Cylinder Section -->
        <div id="cylinderSection" class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">🛢️ Cylinder Details</h3>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Select Cylinder</label>
                    <select name="cylinder_id" id="cylinder_id"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            onchange="autoFillCylinderPrice()">
                        <option value="">Select Cylinder</option>
                        @foreach($cylinders as $cylinder)
                            <option value="{{ $cylinder->id }}" 
                                    data-purchase-price="{{ $cylinder->purchase_price }}"
                                    data-sale-price="{{ $cylinder->sale_price }}"
                                    data-gas="{{ $cylinder->gasProduct->name ?? 'N/A' }}">
                                {{ $cylinder->cylinder_number }} - {{ $cylinder->gasProduct->name ?? 'N/A' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Quantity (Pieces)</label>
                    <input type="number" name="cylinder_quantity" id="cylinder_quantity" value="1"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                           oninput="calculateTotals()">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Purchase Price (Rs.)</label>
                    <input type="number" step="0.01" name="cylinder_purchase_price" id="cylinder_purchase_price"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                           oninput="calculateTotals()">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Sale Price (Rs.)</label>
                    <input type="number" step="0.01" name="cylinder_sale_price" id="cylinder_sale_price"
                           class="w-full rounded-lg border-green-300 bg-green-50 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                           oninput="calculateTotals()">
                </div>
            </div>
            <div class="mt-2 text-right">
                <span class="text-sm text-gray-500">Cylinder Total: </span>
                <span class="text-lg font-bold text-purple-600" id="cylinderTotalDisplay">Rs. 0.00</span>
                <input type="hidden" name="cylinder_total" id="cylinder_total" value="0">
            </div>
        </div>

        <!-- Financial Summary -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">💰 Financial Summary</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Subtotal</label>
                    <input type="number" step="0.01" name="subtotal" id="subtotal" readonly
                           class="w-full rounded-lg bg-gray-100 border-gray-300 shadow-sm font-semibold">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Discount (Rs.)</label>
                    <input type="number" step="0.01" name="discount" id="discount" value="0"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                           oninput="calculateTotals()">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tax (Rs.)</label>
                    <input type="number" step="0.01" name="tax" id="tax" value="0"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                           oninput="calculateTotals()">
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4 pt-4 border-t border-gray-200">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Grand Total</label>
                    <input type="number" step="0.01" name="grand_total" id="grandTotal" readonly
                           class="w-full rounded-lg bg-green-100 border-green-300 shadow-sm font-bold text-lg text-green-700">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Amount Paid</label>
                    <input type="number" step="0.01" name="amount_paid" id="amountPaid" value="0"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                           oninput="calculateTotals()">
                    <p class="text-xs text-gray-400 mt-1" id="balanceDisplay">Balance Due: Rs. 0.00</p>
                </div>
            </div>
        </div>

        <!-- Account Selection -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">📋 Account Details</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Debit Account *</label>
                    <select name="debit_account_id" id="debit_account_id" required
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            onchange="updateAccountName()">
                        <option value="">Select Account</option>
                        @foreach($accounts as $account)
                            <option value="{{ $account->id }}">{{ $account->account_code }} - {{ $account->account_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Credit Account *</label>
                    <select name="credit_account_id" id="credit_account_id" required
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            onchange="updateAccountName()">
                        <option value="">Select Account</option>
                        @foreach($accounts as $account)
                            <option value="{{ $account->id }}">{{ $account->account_code }} - {{ $account->account_name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="mt-4 p-3 bg-blue-50 rounded-lg border border-blue-200">
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-gray-500">Debit Account:</span>
                        <span class="font-semibold text-blue-700" id="debitAccountDisplay">Not Selected</span>
                    </div>
                    <div>
                        <span class="text-gray-500">Credit Account:</span>
                        <span class="font-semibold text-red-700" id="creditAccountDisplay">Not Selected</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notes -->
        <div class="bg-white rounded-lg shadow p-6">
            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
            <textarea name="notes" rows="2" 
                      class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                      placeholder="Any special instructions..."></textarea>
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
    function toggleFields() {
        const type = document.getElementById('purchase_type').value;
        document.getElementById('gasSection').style.display = 
            (type === 'gas_only' || type === 'gas_with_cylinder') ? 'block' : 'none';
        document.getElementById('cylinderSection').style.display = 
            (type === 'cylinder_only' || type === 'gas_with_cylinder') ? 'block' : 'none';
        calculateTotals();
    }

    function autoFillGasPrice() {
        const select = document.getElementById('gas_product_id');
        const priceInput = document.getElementById('gas_price');
        const selectedOption = select.options[select.selectedIndex];
        if (selectedOption && selectedOption.value) {
            priceInput.value = parseFloat(selectedOption.dataset.price).toFixed(2);
            calculateTotals();
        }
    }

    function autoFillCylinderPrice() {
        const select = document.getElementById('cylinder_id');
        const purchasePriceInput = document.getElementById('cylinder_purchase_price');
        const salePriceInput = document.getElementById('cylinder_sale_price');
        const selectedOption = select.options[select.selectedIndex];
        if (selectedOption && selectedOption.value) {
            purchasePriceInput.value = parseFloat(selectedOption.dataset.purchasePrice).toFixed(2);
            salePriceInput.value = parseFloat(selectedOption.dataset.salePrice).toFixed(2);
            calculateTotals();
        }
    }

    function updateAccountName() {
        const debitSelect = document.getElementById('debit_account_id');
        const creditSelect = document.getElementById('credit_account_id');
        const debitOption = debitSelect.options[debitSelect.selectedIndex];
        const creditOption = creditSelect.options[creditSelect.selectedIndex];
        document.getElementById('debitAccountDisplay').textContent = debitOption ? debitOption.text : 'Not Selected';
        document.getElementById('creditAccountDisplay').textContent = creditOption ? creditOption.text : 'Not Selected';
    }

    function calculateTotals() {
        const gasQty = parseFloat(document.getElementById('gas_quantity').value) || 0;
        const gasPrice = parseFloat(document.getElementById('gas_price').value) || 0;
        const gasTotal = gasQty * gasPrice;
        document.getElementById('gasTotalDisplay').textContent = 'Rs. ' + gasTotal.toFixed(2);
        document.getElementById('gas_total').value = gasTotal.toFixed(2);

        const cylQty = parseInt(document.getElementById('cylinder_quantity').value) || 0;
        const cylPurchasePrice = parseFloat(document.getElementById('cylinder_purchase_price').value) || 0;
        const cylTotal = cylQty * cylPurchasePrice;
        document.getElementById('cylinderTotalDisplay').textContent = 'Rs. ' + cylTotal.toFixed(2);
        document.getElementById('cylinder_total').value = cylTotal.toFixed(2);

        const subtotal = gasTotal + cylTotal;
        document.getElementById('subtotal').value = subtotal.toFixed(2);

        const discount = parseFloat(document.getElementById('discount').value) || 0;
        const tax = parseFloat(document.getElementById('tax').value) || 0;
        const grandTotal = subtotal - discount + tax;
        document.getElementById('grandTotal').value = grandTotal.toFixed(2);

        const amountPaid = parseFloat(document.getElementById('amountPaid').value) || 0;
        const balanceDue = grandTotal - amountPaid;
        document.getElementById('balanceDisplay').textContent = 'Balance Due: Rs. ' + balanceDue.toFixed(2);
    }

    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('date').valueAsDate = new Date();
        toggleFields();
        autoFillGasPrice();
        autoFillCylinderPrice();
        calculateTotals();
        updateAccountName();
    });
</script>
@endpush
@endsection