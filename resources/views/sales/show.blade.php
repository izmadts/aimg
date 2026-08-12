@extends('layouts.app')

@section('title', 'Sale #' . $sale->invoice_no)

@section('content')
<div class="space-y-6">
    
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Sale Invoice</h1>
            <p class="text-sm text-gray-500">#{{ $sale->invoice_no }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('sales.print', $sale) }}" target="_blank" class="bg-gray-600 hover:bg-gray-700 text-white font-medium px-4 py-2 rounded-lg transition">
                <i class="fas fa-print mr-2"></i> Print
            </a>
            <a href="{{ route('sales.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-medium px-4 py-2 rounded-lg transition">
                <i class="fas fa-arrow-left mr-2"></i> Back
            </a>
        </div>
    </div>

    <!-- Status Badges -->
    <div class="flex flex-wrap gap-2">
        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
            @if($sale->status === 'confirmed') bg-green-100 text-green-800
            @elseif($sale->status === 'draft') bg-yellow-100 text-yellow-800
            @else bg-red-100 text-red-800
            @endif">
            <i class="fas fa-circle text-xs mr-1.5"></i> {{ $sale->status_label }}
        </span>
        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
            @if($sale->payment_status === 'paid') bg-green-100 text-green-800
            @elseif($sale->payment_status === 'partial') bg-yellow-100 text-yellow-800
            @else bg-red-100 text-red-800
            @endif">
            <i class="fas fa-circle text-xs mr-1.5"></i> {{ $sale->payment_status_label }}
        </span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left Column -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Invoice Info -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <p class="text-xs text-gray-500">Invoice #</p>
                        <p class="font-semibold">{{ $sale->invoice_no }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Date</p>
                        <p class="font-semibold">{{ $sale->date->format('d-m-Y') }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Customer</p>
                        <p class="font-semibold">{{ $sale->customer->name ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Created By</p>
                        <p class="font-semibold">{{ $sale->creator->name ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>

            <!-- Items -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-700">📋 Items</h3>
                </div>
                <div class="divide-y divide-gray-100">
                    @forelse($sale->items as $item)
                        <div class="px-6 py-4">
                            @if($item->gas_product_id)
                            <div class="flex justify-between items-center {{ $item->cylinder_id ? 'border-b border-gray-100 pb-2 mb-2' : '' }}">
                                <div>
                                    <p class="text-xs text-gray-400">🧪 Gas</p>
                                    <span class="font-semibold">{{ $item->gasProduct->name ?? 'N/A' }}</span>
                                    <span class="text-sm text-gray-500 ml-2">({{ $item->gasProduct->uom ?? '' }})</span>
                                </div>
                                <div class="text-right">
                                    <span class="text-sm">{{ number_format($item->gas_quantity, 2) }} × Rs. {{ number_format($item->gas_price, 2) }}</span>
                                    <span class="font-semibold text-blue-600 ml-2">= Rs. {{ number_format($item->gas_total, 2) }}</span>
                                </div>
                            </div>
                            @endif

                            @if($item->cylinder_id)
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="text-xs text-gray-400">🛢️ Cylinder — {{ $item->cylinder_action_label }}</p>
                                    <span class="font-semibold">{{ $item->cylinder->cylinder_number ?? 'N/A' }}</span>
                                    <span class="text-sm text-gray-500 ml-2">({{ $item->cylinder_quantity }} pcs)</span>
                                </div>
                                <div class="text-right">
                                    <span class="text-sm">Rs. {{ number_format($item->cylinder_unit_price, 2) }} each</span>
                                    <span class="font-semibold text-purple-600 ml-2">= Rs. {{ number_format($item->cylinder_total, 2) }}</span>
                                </div>
                            </div>
                            @endif

                            @if($item->notes)
                                <p class="text-xs text-gray-400 mt-2">📝 {{ $item->notes }}</p>
                            @endif
                        </div>
                    @empty
                        <div class="px-6 py-8 text-center text-gray-500">No items found.</div>
                    @endforelse
                </div>
            </div>

            <!-- Accounting Entries -->
            @if(isset($accountingEntries) && $accountingEntries->count() > 0)
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">📊 Accounting Entries</h3>
                <div class="space-y-2">
                    @foreach($accountingEntries as $entry)
                    <div class="flex justify-between text-sm border-b border-gray-100 pb-2">
                        <div>
                            <span class="font-mono">{{ $entry->entry_no }}</span>
                            <span class="text-gray-500 ml-2">{{ $entry->description }}</span>
                        </div>
                        <div>
                            @if($entry->debit > 0)
                                <span class="text-red-600">Dr: Rs. {{ number_format($entry->debit, 2) }}</span>
                            @endif
                            @if($entry->credit > 0)
                                <span class="text-green-600 ml-2">Cr: Rs. {{ number_format($entry->credit, 2) }}</span>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <!-- Right Column -->
        <div class="lg:col-span-1 space-y-6">
            
            <!-- Summary -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">💰 Summary</h3>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Subtotal</span>
                        <span class="font-semibold">Rs. {{ number_format($sale->subtotal, 2) }}</span>
                    </div>
                    @if($sale->cylinder_deposit_total > 0)
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Cylinder Deposit</span>
                        <span class="font-semibold text-yellow-600">Rs. {{ number_format($sale->cylinder_deposit_total, 2) }}</span>
                    </div>
                    @endif
                    @if($sale->cylinder_sale_total > 0)
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Cylinder Sale</span>
                        <span class="font-semibold text-blue-600">Rs. {{ number_format($sale->cylinder_sale_total, 2) }}</span>
                    </div>
                    @endif
                    <div class="border-t border-gray-200 pt-2">
                        <div class="flex justify-between">
                            <span class="text-base font-bold">Grand Total</span>
                            <span class="text-base font-bold text-green-600">Rs. {{ number_format($sale->grand_total, 2) }}</span>
                        </div>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Paid</span>
                        <span class="font-semibold text-blue-600">Rs. {{ number_format($sale->amount_paid, 2) }}</span>
                    </div>
                    <div class="flex justify-between border-t border-gray-200 pt-2">
                        <span class="text-sm font-semibold">Balance Due</span>
                        <span class="text-sm font-bold {{ $sale->balance_due > 0 ? 'text-red-600' : 'text-green-600' }}">
                            Rs. {{ number_format($sale->balance_due, 2) }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Payments -->
            @if($sale->payments->count() > 0)
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">
                    <i class="fas fa-money-bill-wave mr-2 text-gray-400"></i> Payments
                </h3>
                <div class="space-y-2">
                    @foreach($sale->payments as $payment)
                    <div class="flex justify-between text-sm border-b border-gray-100 pb-2">
                        <div>
                            <span class="font-mono text-xs">{{ $payment->transaction_no }}</span>
                            <span class="text-xs text-gray-500 ml-2">{{ $payment->payment_date->format('d-m-Y') }}</span>
                        </div>
                        <div>
                            <span class="font-semibold text-green-600">Rs. {{ number_format($payment->amount, 2) }}</span>
                            <span class="text-xs text-gray-400 ml-2">{{ $payment->payment_method_label }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="mt-2 pt-2 border-t border-gray-200 flex justify-between font-bold">
                    <span class="text-sm">Total Paid</span>
                    <span class="text-sm text-green-600">Rs. {{ number_format($sale->amount_paid, 2) }}</span>
                </div>
            </div>
            @endif

            <!-- Quick Actions -->
            @if($sale->balance_due > 0 && $sale->status !== 'cancelled')
            <button onclick="openPaymentModal({{ $sale->id }}, '{{ $sale->invoice_no }}', '{{ $sale->customer->name ?? 'N/A' }}', {{ $sale->grand_total }}, {{ $sale->balance_due }})" 
                    class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg transition">
                <i class="fas fa-money-bill-wave mr-2"></i> Record Payment
            </button>
            @endif
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
</script>
@endpush
@endsection