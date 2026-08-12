@extends('layouts.app')

@section('title', 'Sales')

@section('content')
<div class="space-y-6">
    
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">🧾 Sales</h1>
            <p class="text-sm text-gray-500">Manage your sales invoices</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('sales.create') }}" class="bg-green-600 hover:bg-green-700 text-white font-medium px-4 py-2 rounded-lg transition">
                <i class="fas fa-plus mr-2"></i> New Sale
            </a>
        </div>
    </div>

    <!-- Statistics -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-blue-500">
            <p class="text-xs text-gray-500">Total Sales</p>
            <p class="text-2xl font-bold">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-green-500">
            <p class="text-xs text-gray-500">Today's Sales</p>
            <p class="text-2xl font-bold text-green-600">Rs. {{ number_format($stats['today_sales'], 0) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-yellow-500">
            <p class="text-xs text-gray-500">Pending Payments</p>
            <p class="text-2xl font-bold text-yellow-600">{{ $stats['pending_payments'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-red-500">
            <p class="text-xs text-gray-500">Pending Amount</p>
            <p class="text-2xl font-bold text-red-600">Rs. {{ number_format($stats['pending_amount'], 0) }}</p>
        </div>
    </div>

    <!-- Search & Filter -->
    <div class="bg-white rounded-lg shadow p-4">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-3">
            <div>
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Search by invoice, customer..."
                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div>
                <select name="status" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">All Status</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <div>
                <select name="payment_status" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">All Payment</option>
                    <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Paid</option>
                    <option value="partial" {{ request('payment_status') == 'partial' ? 'selected' : '' }}>Partial</option>
                    <option value="unpaid" {{ request('payment_status') == 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                </select>
            </div>
            <div>
                <input type="date" name="from_date" value="{{ request('from_date') }}" 
                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                       placeholder="From Date">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-lg transition">
                    <i class="fas fa-search"></i>
                </button>
                <a href="{{ route('sales.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium px-4 py-2 rounded-lg transition">
                    <i class="fas fa-times"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Sales Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Invoice #</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Paid</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Balance</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Payment</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($sales as $sale)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-3 text-sm font-medium">
                            <a href="{{ route('sales.show', $sale) }}" class="text-blue-600 hover:underline">
                                {{ $sale->invoice_no }}
                            </a>
                        </td>
                        <td class="px-6 py-3 text-sm">{{ $sale->customer->name ?? 'N/A' }}</td>
                        <td class="px-6 py-3 text-sm">{{ $sale->date->format('d-m-Y') }}</td>
                        <td class="px-6 py-3 text-right font-semibold">Rs. {{ number_format($sale->grand_total, 2) }}</td>
                        <td class="px-6 py-3 text-right text-blue-600">Rs. {{ number_format($sale->amount_paid, 2) }}</td>
                        <td class="px-6 py-3 text-right font-bold {{ $sale->balance_due > 0 ? 'text-red-600' : 'text-green-600' }}">
                            Rs. {{ number_format($sale->balance_due, 2) }}
                        </td>
                        <td class="px-6 py-3 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($sale->status === 'confirmed') bg-green-100 text-green-800
                                @elseif($sale->status === 'draft') bg-yellow-100 text-yellow-800
                                @else bg-red-100 text-red-800
                                @endif">
                                {{ $sale->status_label }}
                            </span>
                        </td>
                        <td class="px-6 py-3 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($sale->payment_status === 'paid') bg-green-100 text-green-800
                                @elseif($sale->payment_status === 'partial') bg-yellow-100 text-yellow-800
                                @else bg-red-100 text-red-800
                                @endif">
                                {{ $sale->payment_status_label }}
                            </span>
                        </td>
                        <td class="px-6 py-3 text-center">
                            <div class="flex items-center justify-center space-x-1">
                                <a href="{{ route('sales.show', $sale) }}" class="text-blue-600 hover:text-blue-800" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('sales.print', $sale) }}" target="_blank" class="text-gray-600 hover:text-gray-800" title="Print">
                                    <i class="fas fa-print"></i>
                                </a>
                                @if($sale->payment_status !== 'paid' && $sale->status !== 'cancelled')
                                    <button onclick="openPaymentModal({{ $sale->id }}, '{{ $sale->invoice_no }}', '{{ $sale->customer->name ?? 'N/A' }}', {{ $sale->grand_total }}, {{ $sale->balance_due }})" 
                                            class="text-green-600 hover:text-green-800" title="Record Payment">
                                        <i class="fas fa-money-bill-wave"></i>
                                    </button>
                                @endif
                                @if($sale->status === 'draft')
                                    <form action="{{ route('sales.destroy', $sale) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" onclick="return confirm('Delete this sale?')" 
                                                class="text-red-600 hover:text-red-800" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-6 py-8 text-center text-gray-500">
                            <i class="fas fa-inbox text-3xl mb-2 text-gray-300 block"></i>
                            No sales found.
                            <a href="{{ route('sales.create') }}" class="text-blue-600 hover:underline ml-1">Create your first sale</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $sales->links() }}
        </div>
    </div>
</div>

<!-- ============================================
     PAYMENT MODAL
     ============================================ -->
<div id="paymentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-lg bg-white">
        <div class="flex justify-between items-center pb-3 border-b">
            <h3 class="text-lg font-semibold text-gray-900">
                <i class="fas fa-money-bill-wave text-green-600 mr-2"></i> Record Payment
            </h3>
            <button onclick="closePaymentModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <form id="paymentForm" class="mt-4 space-y-4">
            @csrf
            <input type="hidden" name="sale_id" id="payment_sale_id">

            <div class="grid grid-cols-2 gap-3 text-sm">
                <div>
                    <p class="text-gray-500">Invoice #</p>
                    <input type="text" id="payment_invoice_no" disabled class="w-full rounded-lg bg-gray-100 border-gray-300 shadow-sm">
                </div>
                <div>
                    <p class="text-gray-500">Customer</p>
                    <input type="text" id="payment_customer" disabled class="w-full rounded-lg bg-gray-100 border-gray-300 shadow-sm">
                </div>
            </div>

            <div class="bg-gray-50 p-3 rounded-lg grid grid-cols-2 gap-2 text-sm">
                <div>
                    <p class="text-gray-500">Total Amount</p>
                    <p class="font-bold" id="payment_total">Rs. 0.00</p>
                </div>
                <div>
                    <p class="text-gray-500">Balance Due</p>
                    <p class="font-bold text-red-600" id="payment_balance">Rs. 0.00</p>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Payment Amount *</label>
                <input type="number" step="0.01" name="amount" id="payment_amount" required
                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                       oninput="updatePaymentPreview()">
                <p class="text-xs text-gray-400 mt-1" id="payment_hint">Max: Rs. 0.00</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Payment Method *</label>
                <select name="payment_method" id="payment_method" required
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="cash">💵 Cash</option>
                    <option value="bank_transfer">🏦 Bank Transfer</option>
                    <option value="cheque">📝 Cheque</option>
                    <option value="online">💳 Online Payment</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Payment Date *</label>
                <input type="date" name="payment_date" id="payment_date" value="{{ date('Y-m-d') }}" required
                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                <textarea name="notes" id="payment_notes" rows="2" 
                          class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                          placeholder="Optional notes..."></textarea>
            </div>

            <div id="payment_preview" class="hidden bg-blue-50 p-3 rounded-lg">
                <p class="text-sm text-gray-700">
                    After payment: Balance will be <span id="new_balance" class="font-bold">Rs. 0.00</span>
                </p>
            </div>

            <div class="flex justify-end space-x-3 pt-4 border-t">
                <button type="button" onclick="closePaymentModal()" 
                        class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-4 rounded-lg transition">
                    Cancel
                </button>
                <button type="submit" id="payment_submit_btn"
                        class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg transition">
                    <i class="fas fa-check mr-2"></i> Record Payment
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    // ============================================
    // PAYMENT MODAL FUNCTIONS
    // ============================================
    let currentSaleId = null;
    let currentBalance = 0;

    function openPaymentModal(saleId, invoiceNo, customer, total, balance) {
        currentSaleId = saleId;
        currentBalance = balance;
        
        document.getElementById('payment_sale_id').value = saleId;
        document.getElementById('payment_invoice_no').value = invoiceNo;
        document.getElementById('payment_customer').value = customer;
        document.getElementById('payment_total').textContent = 'Rs. ' + total.toFixed(2);
        document.getElementById('payment_balance').textContent = 'Rs. ' + balance.toFixed(2);
        document.getElementById('payment_amount').value = '';
        document.getElementById('payment_amount').max = balance;
        document.getElementById('payment_hint').textContent = 'Max: Rs. ' + balance.toFixed(2);
        document.getElementById('payment_notes').value = '';
        
        document.getElementById('paymentModal').classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
        document.getElementById('payment_preview').classList.add('hidden');
        
        setTimeout(() => {
            document.getElementById('payment_amount').focus();
        }, 100);
    }

    function closePaymentModal() {
        document.getElementById('paymentModal').classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    document.getElementById('paymentModal').addEventListener('click', function(e) {
        if (e.target === this) closePaymentModal();
    });

    function updatePaymentPreview() {
        const amount = parseFloat(document.getElementById('payment_amount').value) || 0;
        const balance = currentBalance;
        const preview = document.getElementById('payment_preview');
        const newBalance = balance - amount;

        if (amount > 0) {
            preview.classList.remove('hidden');
            document.getElementById('new_balance').textContent = 'Rs. ' + newBalance.toFixed(2);
            document.getElementById('new_balance').className = 'font-bold ' + (newBalance > 0 ? 'text-red-600' : 'text-green-600');
            
            if (amount > balance) {
                document.getElementById('payment_amount').style.borderColor = '#ef4444';
                document.getElementById('payment_amount').style.backgroundColor = '#fef2f2';
            } else {
                document.getElementById('payment_amount').style.borderColor = '';
                document.getElementById('payment_amount').style.backgroundColor = '';
            }
        } else {
            preview.classList.add('hidden');
        }
    }

    // ============================================
    // PAYMENT FORM SUBMIT
    // ============================================
    document.getElementById('paymentForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const amount = parseFloat(document.getElementById('payment_amount').value);
        const balance = currentBalance;

        if (!amount || amount <= 0) {
            alert('Please enter a valid payment amount.');
            return;
        }

        if (amount > balance) {
            alert('Payment amount cannot exceed balance due.');
            return;
        }

        if (!confirm('Confirm payment of Rs. ' + amount.toFixed(2) + ' for invoice #' + document.getElementById('payment_invoice_no').value + '?')) {
            return;
        }

        const formData = new FormData(this);
        const submitBtn = document.getElementById('payment_submit_btn');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Processing...';
        submitBtn.disabled = true;

        fetch('/sales/' + currentSaleId + '/payment', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('✅ ' + data.message);
                closePaymentModal();
                location.reload();
            } else {
                alert('❌ Error: ' + data.message);
            }
        })
        .catch(error => {
            alert('❌ Error processing payment.');
            console.error(error);
        })
        .finally(() => {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
    });

    // ESC key to close
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closePaymentModal();
    });
</script>
@endpush
@endsection