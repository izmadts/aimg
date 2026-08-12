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

    <form id="saleForm" action="{{ route('sales.store') }}" method="POST" class="space-y-6">
        @csrf

        <!-- ============================================
             CUSTOMER & INVOICE DETAILS
             ============================================ -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
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
                        <strong id="outstandingCount">0</strong> cylinders issued to this customer.
                        <span id="outstandingDeposit">Total Deposit: Rs. 0</span>
                    </p>
                    <ul class="mt-1 text-xs text-yellow-600" id="outstandingList"></ul>
                </div>
            </div>
        </div>

        <!-- ============================================
             GAS ITEMS
             ============================================ -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">🧪 Gas Items</h3>
                <button type="button" onclick="addItem()" 
                        class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-lg transition">
                    <i class="fas fa-plus mr-2"></i> Add Item
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Qty</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rate</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Action</th>
                        </tr>
                    </thead>
                    <tbody id="itemsContainer">
                        <!-- Item rows will be added here -->
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="5" class="px-4 py-3 text-center text-gray-500 text-sm" id="emptyItemsMsg">
                                <i class="fas fa-plus-circle mr-2"></i> Click "Add Item" to add gas products
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- ============================================
             CYLINDER SELECTION
             ============================================ -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">🛢️ Cylinders</h3>
                <span class="text-sm text-gray-500" id="selectedCylinderCount">0 cylinders selected</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Action</label>
                    <select id="cylinderAction" 
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            onchange="updateActionForAll()">
                        <option value="issue">Issue (Temporary/Rent)</option>
                        <option value="sell">Sell (Permanent)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Security Deposit Per Cylinder (Rs.)</label>
                    <input type="number" step="0.01" name="security_deposit" id="securityDeposit" value="0"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                           oninput="updateSelectedCylinders()">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cylinder Sale Price (Rs.)</label>
                    <input type="number" step="0.01" name="cylinder_sale_price" id="cylinderSalePrice" value="0"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                           oninput="updateSelectedCylinders()">
                    <p class="text-xs text-gray-400 mt-1">For Sell action only</p>
                </div>
            </div>

            <!-- Cylinder Table -->
            <div class="overflow-x-auto border rounded-lg">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                <input type="checkbox" id="selectAllCylinders" onchange="toggleAllCylinders()">
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cylinder #</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Gas</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Stock</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Issued</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Available</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Action</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Sale Price</th>
                        </tr>
                    </thead>
                    <tbody id="cylinderListBody">
                        <!-- Cylinders loaded here -->
                    </tbody>
                </table>
            </div>

            <div class="mt-2 flex justify-between items-center">
                <span class="text-sm text-gray-500" id="selectedCount">0 cylinders selected</span>
                <span class="text-sm font-semibold text-yellow-600" id="totalDepositText">Total Deposit: Rs. 0</span>
                <span class="text-sm font-semibold text-blue-600" id="totalCylinderPriceText">Total Cylinder Price: Rs. 0</span>
            </div>
        </div>

        <!-- ============================================
             FINANCIAL SUMMARY
             ============================================ -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">💰 Financial Summary</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Subtotal</label>
                    <input type="number" step="0.01" name="subtotal" id="subtotal" readonly
                           class="w-full rounded-lg bg-gray-100 border-gray-300 shadow-sm font-semibold">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cylinder Deposit</label>
                    <input type="number" step="0.01" name="cylinder_deposit_total" id="cylinderDepositTotal" readonly
                           class="w-full rounded-lg bg-gray-100 border-gray-300 shadow-sm font-semibold">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cylinder Sale Total</label>
                    <input type="number" step="0.01" name="cylinder_sale_total" id="cylinderSaleTotal" readonly
                           class="w-full rounded-lg bg-blue-100 border-blue-300 shadow-sm font-semibold text-blue-700">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4 pt-4 border-t border-gray-200">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Discount (Rs.)</label>
                    <input type="number" step="0.01" name="discount" id="discount" value="{{ old('discount', 0) }}"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                           oninput="calculateTotals()">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tax (Rs.)</label>
                    <input type="number" step="0.01" name="tax" id="tax" value="{{ old('tax', 0) }}"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                           oninput="calculateTotals()">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Amount Paid</label>
                    <input type="number" step="0.01" name="amount_paid" id="amountPaid" value="{{ old('amount_paid', 0) }}"
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
                    <label class="block text-sm font-medium text-gray-700 mb-1">Balance Due</label>
                    <input type="text" id="balanceDisplay" readonly
                           class="w-full rounded-lg bg-gray-100 border-gray-300 shadow-sm font-semibold text-red-600">
                </div>
            </div>
        </div>

        <!-- ============================================
             PAYMENT METHOD & ACCOUNT
             ============================================ -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">💳 Payment Details</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Payment Method -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Payment Method *</label>
                    <select name="payment_method" id="payment_method" required
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            onchange="updatePaymentAccount()">
                        <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>💵 Cash</option>
                        <option value="bank_transfer" {{ old('payment_method') == 'bank_transfer' ? 'selected' : '' }}>🏦 Bank Transfer</option>
                        <option value="cheque" {{ old('payment_method') == 'cheque' ? 'selected' : '' }}>📝 Cheque</option>
                        <option value="online" {{ old('payment_method') == 'online' ? 'selected' : '' }}>💳 Online Payment</option>
                        <option value="credit" {{ old('payment_method') == 'credit' ? 'selected' : '' }}>📋 Credit (Invoice)</option>
                    </select>
                    @error('payment_method')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Payment Account (Auto-assigned) -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Payment Account (Auto)</label>
                    <input type="text" id="paymentAccountDisplay" readonly
                           value="Cash Account"
                           class="w-full rounded-lg bg-gray-100 border-gray-300 shadow-sm font-semibold text-blue-600">
                    <p class="text-xs text-gray-400 mt-1">💡 Auto-assigned based on payment method</p>
                </div>
            </div>

            <!-- Payment Summary -->
            <div class="mt-4 p-4 bg-blue-50 rounded-lg border border-blue-200">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                    <div>
                        <span class="text-gray-500">💳 Payment Method:</span>
                        <span class="font-semibold text-blue-700" id="paymentMethodDisplay">Not Selected</span>
                    </div>
                    <div>
                        <span class="text-gray-500">💰 Amount Paid:</span>
                        <span class="font-semibold text-green-700" id="amountPaidDisplay">Rs. 0.00</span>
                    </div>
                    <div>
                        <span class="text-gray-500">📊 Payment Account:</span>
                        <span class="font-semibold text-purple-700" id="paymentAccountFinal">-</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================
             ACCOUNT DETAILS (Credit Account)
             ============================================ -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">📋 Account Details</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Credit Account (Sales Revenue) -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Credit Account (Sales Revenue) *</label>
                    <select name="credit_account_id" id="credit_account_id" required
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            onchange="updateAccountName()">
                        <option value="">Select Account</option>
                        @foreach($accounts as $account)
                            <option value="{{ $account->id }}" 
                                    data-type="{{ $account->account_type }}"
                                    {{ old('credit_account_id') == $account->id ? 'selected' : '' }}>
                                {{ $account->account_code }} - {{ $account->account_name }}
                                @if($account->account_type === 'income')
                                    <span class="text-xs text-green-600">(Income)</span>
                                @endif
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-400 mt-1">💡 Account to credit (Sales Revenue/Income)</p>
                    @error('credit_account_id')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Debit Account (Auto) -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Debit Account (Auto)</label>
                    <input type="text" id="debitAccountDisplay" readonly
                           value="Accounts Receivable / Cash (Auto)"
                           class="w-full rounded-lg bg-gray-100 border-gray-300 shadow-sm font-semibold text-blue-600">
                    <p class="text-xs text-gray-400 mt-1">💡 Auto-assigned based on payment status</p>
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

        <!-- Add these hidden fields after the summary section -->
<input type="hidden" name="subtotal" id="subtotal_hidden" value="0">
<input type="hidden" name="cylinder_deposit_total" id="cylinder_deposit_total_hidden" value="0">
<input type="hidden" name="cylinder_sale_total" id="cylinder_sale_total_hidden" value="0">
<input type="hidden" name="grand_total" id="grand_total_hidden" value="0">

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
    // VARIABLES
    // ============================================
    let itemCount = 0;
    let selectedCylinders = [];
    const gasProducts = @json($gasProducts);
    const allCylinders = @json($availableCylinders);
    let cylinderAction = 'issue';

    // ============================================
    // PAYMENT ACCOUNT UPDATE
    // ============================================
    function updatePaymentAccount() {
        const method = document.getElementById('payment_method').value;
        const display = document.getElementById('paymentAccountDisplay');
        const methodDisplay = document.getElementById('paymentMethodDisplay');
        const accountFinal = document.getElementById('paymentAccountFinal');
        const amountPaid = parseFloat(document.getElementById('amountPaid').value) || 0;
        
        const accounts = {
            'cash': { label: '💵 Cash Account', account: 'Cash' },
            'bank_transfer': { label: '🏦 Bank Transfer Account', account: 'Bank Account' },
            'cheque': { label: '📝 Cheque Account', account: 'Bank Account' },
            'online': { label: '💳 Online Payment Account', account: 'Bank Account' },
            'credit': { label: '📋 Credit (Receivable)', account: 'Accounts Receivable' }
        };
        
        const selected = accounts[method] || accounts['cash'];
        display.value = selected.label;
        methodDisplay.textContent = selected.label;
        accountFinal.textContent = selected.account;
        
        updateDebitAccount();
    }

    // ============================================
    // UPDATE DEBIT ACCOUNT (Auto)
    // ============================================
    function updateDebitAccount() {
        const method = document.getElementById('payment_method').value;
        const amountPaid = parseFloat(document.getElementById('amountPaid').value) || 0;
        const grandTotal = parseFloat(document.getElementById('grandTotal').value) || 0;
        const debitDisplay = document.getElementById('debitAccountDisplay');
        const accountFinal = document.getElementById('paymentAccountFinal');
        
        let debitAccount = '';
        
        if (method === 'credit') {
            debitAccount = 'Accounts Receivable (Credit)';
        } else if (amountPaid >= grandTotal && grandTotal > 0) {
            debitAccount = accountFinal.textContent + ' (Fully Paid)';
        } else if (amountPaid > 0) {
            debitAccount = accountFinal.textContent + ' (Partial) / Accounts Receivable';
        } else {
            debitAccount = 'Accounts Receivable (Unpaid)';
        }
        
        debitDisplay.textContent = debitAccount;
        debitDisplay.className = 'w-full rounded-lg bg-gray-100 border-gray-300 shadow-sm font-semibold text-blue-600';
    }

    // ============================================
    // ADD GAS ITEM
    // ============================================
    function addItem() {
        const container = document.getElementById('itemsContainer');
        const emptyMsg = document.getElementById('emptyItemsMsg');
        if (emptyMsg) emptyMsg.style.display = 'none';
        
        itemCount++;
        const rowId = 'item_' + itemCount;

        const row = document.createElement('tr');
        row.id = rowId;
        row.className = 'border-b border-gray-100 hover:bg-gray-50 transition';
        row.innerHTML = `
            <td class="px-4 py-2">
                <select name="items[${itemCount}][gas_product_id]" 
                        id="product_${itemCount}"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                        onchange="autoFillPrice(${itemCount})">
                    <option value="">Select Product</option>
                    ${gasProducts.map(p => `<option value="${p.id}" data-price="${p.sale_price || 0}">${p.name} (${p.uom}) - Rs. ${p.sale_price || 0}</option>`).join('')}
                </select>
            </td>
            <td class="px-4 py-2">
                <input type="number" step="0.01" name="items[${itemCount}][quantity]" 
                       id="qty_${itemCount}"
                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                       oninput="calculateTotals()" min="0.01" value="1">
            </td>
            <td class="px-4 py-2">
                <input type="number" step="0.01" name="items[${itemCount}][price]" 
                       id="price_${itemCount}"
                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                       oninput="calculateTotals()" min="0" value="0">
            </td>
            <td class="px-4 py-2 text-right font-medium" id="total_${itemCount}">
                0.00
            </td>
            <td class="px-4 py-2 text-center">
                <button type="button" onclick="removeItem('${rowId}')" 
                        class="text-red-600 hover:text-red-800 transition">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;

        container.appendChild(row);
        calculateTotals();
    }

    function removeItem(rowId) {
        const row = document.getElementById(rowId);
        if (row) {
            row.remove();
            calculateTotals();
            
            const container = document.getElementById('itemsContainer');
            if (container.children.length === 0) {
                document.getElementById('emptyItemsMsg').style.display = '';
            }
        }
    }

    function autoFillPrice(index) {
        const select = document.getElementById('product_' + index);
        const priceInput = document.getElementById('price_' + index);
        const selectedOption = select.options[select.selectedIndex];
        
        if (selectedOption && selectedOption.value) {
            const price = parseFloat(selectedOption.dataset.price) || 0;
            priceInput.value = price.toFixed(2);
            calculateTotals();
        }
    }

    // ============================================
    // LOAD CYLINDERS
    // ============================================
    function loadCylinders() {
        const tbody = document.getElementById('cylinderListBody');
        
        const available = allCylinders.filter(c => {
            const availableQty = (c.stock_quantity || 0) - (c.issued_quantity || 0);
            return availableQty > 0;
        });
        
        if (available.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="9" class="px-4 py-8 text-center text-gray-500">
                        <i class="fas fa-inbox text-2xl mb-2 block"></i>
                        No cylinders available in stock.
                    </td>
                </tr>
            `;
            return;
        }

        let html = '';
        available.forEach((cylinder) => {
            const isSelected = selectedCylinders.some(c => c.id === cylinder.id);
            const gasName = cylinder.gasProduct?.name || cylinder.gas_name || 'N/A';
            const stockQty = parseInt(cylinder.stock_quantity) || 0;
            const issuedQty = parseInt(cylinder.issued_quantity) || 0;
            const availableQty = stockQty - issuedQty;
            
            let salePrice = 0;
            if (cylinder.sale_price !== null && cylinder.sale_price !== undefined) {
                salePrice = parseFloat(cylinder.sale_price) || 0;
            } else if (cylinder.purchase_price) {
                salePrice = parseFloat(cylinder.purchase_price) * 1.2 || 0;
            }
            salePrice = isNaN(salePrice) ? 0 : salePrice;
            
            html += `
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-4 py-3">
                        <input type="checkbox" class="cylinder-checkbox" value="${cylinder.id}"
                               ${isSelected ? 'checked' : ''}
                               onchange="toggleCylinder(${cylinder.id}, this.checked)">
                    </td>
                    <td class="px-4 py-3 font-mono font-medium">${cylinder.cylinder_number || 'N/A'}</td>
                    <td class="px-4 py-3">${gasName}</td>
                    <td class="px-4 py-3">${cylinder.type || 'N/A'}</td>
                    <td class="px-4 py-3 text-center font-bold">${stockQty}</td>
                    <td class="px-4 py-3 text-center text-yellow-600">${issuedQty}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium 
                            ${availableQty > 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                            ${availableQty}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <select class="cylinder-action-select w-full rounded border-gray-300 text-sm" 
                                data-cylinder-id="${cylinder.id}"
                                onchange="updateSelectedCylinders()">
                            <option value="issue" ${cylinderAction === 'issue' ? 'selected' : ''}>Issue</option>
                            <option value="sell" ${cylinderAction === 'sell' ? 'selected' : ''}>Sell</option>
                        </select>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <input type="number" step="0.01" 
                               class="cylinder-price-input w-24 rounded border-gray-300 text-sm text-right"
                               data-cylinder-id="${cylinder.id}"
                               value="${salePrice.toFixed(2)}"
                               oninput="updateSelectedCylinders()">
                    </td>
                </tr>
            `;
        });

        tbody.innerHTML = html;
        updateSelectedCount();
    }

    // ============================================
    // CYLINDER FUNCTIONS
    // ============================================
    function updateActionForAll() {
        cylinderAction = document.getElementById('cylinderAction').value;
        document.querySelectorAll('.cylinder-action-select').forEach(select => {
            select.value = cylinderAction;
        });
        updateSelectedCylinders();
    }

    function toggleCylinder(cylinderId, checked) {
        const cylinder = allCylinders.find(c => c.id === cylinderId);
        if (!cylinder) return;

        if (checked) {
            if (!selectedCylinders.some(c => c.id === cylinderId)) {
                selectedCylinders.push(cylinder);
            }
        } else {
            selectedCylinders = selectedCylinders.filter(c => c.id !== cylinderId);
        }

        updateSelectedCount();
        calculateTotals();
    }

    function toggleAllCylinders() {
        const checkboxes = document.querySelectorAll('.cylinder-checkbox');
        const isChecked = document.getElementById('selectAllCylinders').checked;
        
        checkboxes.forEach(checkbox => {
            checkbox.checked = isChecked;
            toggleCylinder(parseInt(checkbox.value), isChecked);
        });
    }

    function updateSelectedCount() {
        const count = selectedCylinders.length;
        document.getElementById('selectedCount').textContent = count + ' cylinders selected';
        document.getElementById('selectedCylinderCount').textContent = count + ' cylinders selected';
        
        const totalCheckboxes = document.querySelectorAll('.cylinder-checkbox').length;
        const checkedCheckboxes = document.querySelectorAll('.cylinder-checkbox:checked').length;
        document.getElementById('selectAllCylinders').checked = totalCheckboxes > 0 && totalCheckboxes === checkedCheckboxes;
        
        updateSelectedCylinders();
    }

    function updateSelectedCylinders() {
        const depositPerCylinder = parseFloat(document.getElementById('securityDeposit').value) || 0;
        let totalDeposit = 0;
        let totalCylinderPrice = 0;
        
        const actionSelects = document.querySelectorAll('.cylinder-action-select');
        const priceInputs = document.querySelectorAll('.cylinder-price-input');
        
        const cylinderActions = {};
        const cylinderPrices = {};
        
        actionSelects.forEach(select => {
            const cylinderId = parseInt(select.dataset.cylinderId);
            cylinderActions[cylinderId] = select.value;
        });
        
        priceInputs.forEach(input => {
            const cylinderId = parseInt(input.dataset.cylinderId);
            cylinderPrices[cylinderId] = parseFloat(input.value) || 0;
        });

        selectedCylinders.forEach(cylinder => {
            const action = cylinderActions[cylinder.id] || 'issue';
            const price = cylinderPrices[cylinder.id] || 0;
            
            if (action === 'issue') {
                totalDeposit += depositPerCylinder;
            }
            if (action === 'sell') {
                totalCylinderPrice += price;
            }
        });

        document.getElementById('cylinderDepositTotal').value = totalDeposit.toFixed(2);
        document.getElementById('cylinderSaleTotal').value = totalCylinderPrice.toFixed(2);
        document.getElementById('totalDepositText').textContent = 'Total Deposit: Rs. ' + totalDeposit.toFixed(2);
        document.getElementById('totalCylinderPriceText').textContent = 'Total Cylinder Price: Rs. ' + totalCylinderPrice.toFixed(2);
        
        calculateTotals();
    }

    // ============================================
    // CALCULATE TOTALS
    // ============================================
    /*function calculateTotals() {
        let subtotal = 0;
        const rows = document.querySelectorAll('#itemsContainer tr');
        
        rows.forEach((row, index) => {
            const rowNum = index + 1;
            const qtyInput = document.getElementById('qty_' + rowNum);
            const priceInput = document.getElementById('price_' + rowNum);
            const totalSpan = document.getElementById('total_' + rowNum);
            
            if (qtyInput && priceInput && totalSpan) {
                const qty = parseFloat(qtyInput.value) || 0;
                const price = parseFloat(priceInput.value) || 0;
                const total = qty * price;
                subtotal += total;
                totalSpan.textContent = total.toFixed(2);
            }
        });

        const deposit = parseFloat(document.getElementById('cylinderDepositTotal').value) || 0;
        const cylinderSaleTotal = parseFloat(document.getElementById('cylinderSaleTotal').value) || 0;
        const discount = parseFloat(document.getElementById('discount').value) || 0;
        const tax = parseFloat(document.getElementById('tax').value) || 0;
        const amountPaid = parseFloat(document.getElementById('amountPaid').value) || 0;
        
        const grandTotal = subtotal + deposit + cylinderSaleTotal - discount + tax;
        const balanceDue = grandTotal - amountPaid;
        
        document.getElementById('subtotal').value = subtotal.toFixed(2);
        document.getElementById('grandTotal').value = grandTotal.toFixed(2);
        document.getElementById('balanceDisplay').value = 'Rs. ' + balanceDue.toFixed(2);
        document.getElementById('balanceDisplay').className = 'w-full rounded-lg bg-gray-100 border-gray-300 shadow-sm font-semibold ' + 
            (balanceDue > 0 ? 'text-red-600' : 'text-green-600');
        
        // Update payment display
        document.getElementById('amountPaidDisplay').textContent = 'Rs. ' + amountPaid.toFixed(2);
        updateDebitAccount();
    }*/

    // ============================================
// CALCULATE TOTALS (FIXED)
// ============================================
function calculateTotals() {
    let subtotal = 0;
    const rows = document.querySelectorAll('#itemsContainer tr');
    
    rows.forEach((row, index) => {
        const rowNum = index + 1;
        const qtyInput = document.getElementById('qty_' + rowNum);
        const priceInput = document.getElementById('price_' + rowNum);
        const totalSpan = document.getElementById('total_' + rowNum);
        
        if (qtyInput && priceInput && totalSpan) {
            const qty = parseFloat(qtyInput.value) || 0;
            const price = parseFloat(priceInput.value) || 0;
            const total = qty * price;
            subtotal += total;
            totalSpan.textContent = total.toFixed(2);
        }
    });

    const deposit = parseFloat(document.getElementById('cylinderDepositTotal').value) || 0;
    const cylinderSaleTotal = parseFloat(document.getElementById('cylinderSaleTotal').value) || 0;
    const discount = parseFloat(document.getElementById('discount').value) || 0;
    const tax = parseFloat(document.getElementById('tax').value) || 0;
    const amountPaid = parseFloat(document.getElementById('amountPaid').value) || 0;
    
    const grandTotal = subtotal + deposit + cylinderSaleTotal - discount + tax;
    const balanceDue = grandTotal - amountPaid;
    
    // ✅ Update display
    document.getElementById('subtotal').value = subtotal.toFixed(2);
    document.getElementById('grandTotal').value = grandTotal.toFixed(2);
    document.getElementById('balanceDisplay').value = 'Rs. ' + balanceDue.toFixed(2);
    document.getElementById('balanceDisplay').className = 'w-full rounded-lg bg-gray-100 border-gray-300 shadow-sm font-semibold ' + 
        (balanceDue > 0 ? 'text-red-600' : 'text-green-600');
    
    // ✅ Update hidden fields for form submission
    document.getElementById('subtotal_hidden').value = subtotal.toFixed(2);
    document.getElementById('cylinder_deposit_total_hidden').value = deposit.toFixed(2);
    document.getElementById('cylinder_sale_total_hidden').value = cylinderSaleTotal.toFixed(2);
    document.getElementById('grand_total_hidden').value = grandTotal.toFixed(2);
    
    // ✅ Update payment display
    document.getElementById('amountPaidDisplay').textContent = 'Rs. ' + amountPaid.toFixed(2);
    updateDebitAccount();
}

    // ============================================
    // ACCOUNT NAME UPDATE
    // ============================================
    function updateAccountName() {
        const creditSelect = document.getElementById('credit_account_id');
        const creditOption = creditSelect.options[creditSelect.selectedIndex];
        
        const creditDisplay = document.getElementById('creditAccountDisplay');
        if (creditOption && creditOption.value) {
            const creditText = creditOption.text.split('(')[0].trim();
            creditDisplay.textContent = creditText;
            creditDisplay.className = 'font-semibold text-red-700';
        } else {
            creditDisplay.textContent = 'Not Selected';
            creditDisplay.className = 'font-semibold text-gray-400';
        }
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
                    countSpan.textContent = data.cylinders.length;
                    depositSpan.textContent = `Total Deposit: Rs. ${data.total_deposit || 0}`;
                    
                    list.innerHTML = data.cylinders.map(c => 
                        `<li>${c.cylinder_number} (${c.gas_product?.name || 'N/A'}) - ${c.days_out} days out</li>`
                    ).join('');
                } else {
                    alertDiv.classList.add('hidden');
                }
            })
            .catch(() => alertDiv.classList.add('hidden'));
    });

    // ============================================
    // EVENT LISTENERS
    // ============================================
    document.getElementById('amountPaid').addEventListener('input', function() {
        calculateTotals();
        updatePaymentAccount();
    });

    document.getElementById('payment_method').addEventListener('change', function() {
        updatePaymentAccount();
    });

    // ============================================
    // FORM SUBMIT
    // ============================================
    document.getElementById('saleForm').addEventListener('submit', function(e) {
        // Validate gas items
        const items = document.querySelectorAll('#itemsContainer tr');
        let hasValidItem = false;
        items.forEach(row => {
            const select = row.querySelector('select');
            const qty = row.querySelector('input[name*="[quantity]"]');
            if (select && select.value && parseFloat(qty?.value) > 0) {
                hasValidItem = true;
            }
        });

        if (!hasValidItem) {
            e.preventDefault();
            alert('Please add at least one gas item with quantity.');
            return;
        }

        // Validate cylinders
        if (selectedCylinders.length === 0) {
            e.preventDefault();
            alert('Please select at least one cylinder.');
            return;
        }

        // Validate credit account
        const creditAccount = document.getElementById('credit_account_id').value;
        if (!creditAccount) {
            e.preventDefault();
            alert('Please select a credit account.');
            return;
        }

        // Validate payment method
        const paymentMethod = document.getElementById('payment_method').value;
        if (!paymentMethod) {
            e.preventDefault();
            alert('Please select a payment method.');
            return;
        }

        // Add selected cylinders
        const container = document.createElement('div');
        
        const actionSelects = document.querySelectorAll('.cylinder-action-select');
        const priceInputs = document.querySelectorAll('.cylinder-price-input');
        
        const cylinderActions = {};
        const cylinderPrices = {};
        
        actionSelects.forEach(select => {
            const cylinderId = parseInt(select.dataset.cylinderId);
            cylinderActions[cylinderId] = select.value;
        });
        
        priceInputs.forEach(input => {
            const cylinderId = parseInt(input.dataset.cylinderId);
            cylinderPrices[cylinderId] = parseFloat(input.value) || 0;
        });

        selectedCylinders.forEach(cylinder => {
            const action = cylinderActions[cylinder.id] || 'issue';
            const price = cylinderPrices[cylinder.id] || 0;
            
            const idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'cylinder_ids[]';
            idInput.value = cylinder.id;
            container.appendChild(idInput);
            
            const actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'cylinder_actions[]';
            actionInput.value = action;
            container.appendChild(actionInput);
            
            const priceInput = document.createElement('input');
            priceInput.type = 'hidden';
            priceInput.name = 'cylinder_prices[]';
            priceInput.value = price;
            container.appendChild(priceInput);
        });

        this.appendChild(container);
    });

    // ============================================
    // INITIALIZE
    // ============================================
    document.addEventListener('DOMContentLoaded', function() {
        addItem();
        document.getElementById('date').valueAsDate = new Date();
        loadCylinders();
        document.getElementById('cylinderAction').value = 'issue';
        updateAccountName();
        updatePaymentAccount();
    });
</script>
@endpush
@endsection