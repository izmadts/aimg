@extends('layouts.app')

@section('title', 'Purchase #' . $purchase->purchase_invoice_no)

@section('content')
<div class="space-y-6">
    
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Purchase Order</h1>
            <p class="text-sm text-gray-500">#{{ $purchase->purchase_invoice_no }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('purchases.print', $purchase) }}" target="_blank" 
               class="bg-gray-600 hover:bg-gray-700 text-white font-medium px-4 py-2 rounded-lg transition">
                <i class="fas fa-print mr-2"></i> Print
            </a>
            <a href="{{ route('purchases.index') }}" 
               class="bg-gray-500 hover:bg-gray-600 text-white font-medium px-4 py-2 rounded-lg transition">
                <i class="fas fa-arrow-left mr-2"></i> Back
            </a>
        </div>
    </div>

    <!-- Status Badges -->
    <div class="flex flex-wrap gap-2">
        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
            @if($purchase->status === 'confirmed') bg-green-100 text-green-800
            @elseif($purchase->status === 'draft') bg-yellow-100 text-yellow-800
            @else bg-red-100 text-red-800
            @endif">
            <i class="fas fa-circle text-xs mr-1.5"></i> {{ $purchase->status_label }}
        </span>
        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
            @if($purchase->payment_status === 'paid') bg-green-100 text-green-800
            @elseif($purchase->payment_status === 'partial') bg-yellow-100 text-yellow-800
            @else bg-red-100 text-red-800
            @endif">
            <i class="fas fa-circle text-xs mr-1.5"></i> {{ $purchase->payment_status_label }}
        </span>
        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
            <i class="fas fa-tag text-xs mr-1.5"></i> {{ $purchase->purchase_type_label }}
        </span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left Column -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Purchase Info -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <p class="text-xs text-gray-500">PO #</p>
                        <p class="font-semibold">{{ $purchase->purchase_invoice_no }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Date</p>
                        <p class="font-semibold">{{ $purchase->date->format('d-m-Y') }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Supplier</p>
                        <p class="font-semibold">{{ $purchase->supplier->name ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Created By</p>
                        <p class="font-semibold">{{ $purchase->creator->name ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>

            <!-- Items Details -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">📋 Items Details</h3>
                <div class="space-y-4">
                    
                    <!-- Gas Details -->
                    @if($purchase->gas_product_id)
                    <div class="border-b border-gray-100 pb-3">
                        <p class="text-xs text-gray-500">Gas Product</p>
                        <div class="grid grid-cols-2 gap-4 mt-1">
                            <div>
                                <span class="font-semibold">{{ $purchase->gasProduct->name ?? 'N/A' }}</span>
                                <span class="text-sm text-gray-500 ml-2">({{ $purchase->gasProduct->uom ?? '' }})</span>
                            </div>
                            <div class="text-right">
                                <span class="text-sm">{{ $purchase->gas_quantity }} × Rs. {{ number_format($purchase->gas_price, 2) }}</span>
                                <span class="font-semibold text-blue-600 ml-2">= Rs. {{ number_format($purchase->gas_total, 2) }}</span>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Cylinder Details -->
                    @if($purchase->cylinder_id)
                    <div>
                        <p class="text-xs text-gray-500">Cylinder</p>
                        <div class="grid grid-cols-2 gap-4 mt-1">
                            <div>
                                <span class="font-semibold">{{ $purchase->cylinder->cylinder_number ?? 'N/A' }}</span>
                                <span class="text-sm text-gray-500 ml-2">({{ $purchase->cylinder_quantity }} pieces)</span>
                            </div>
                            <div class="text-right">
                                <span class="text-sm">Rs. {{ number_format($purchase->cylinder_purchase_price, 2) }} each</span>
                                <span class="font-semibold text-purple-600 ml-2">= Rs. {{ number_format($purchase->cylinder_total, 2) }}</span>
                            </div>
                        </div>
                        @if($purchase->cylinder_sale_price > 0)
                        <div class="mt-1 text-right text-sm">
                            <span class="text-gray-500">Sale Price: Rs. {{ number_format($purchase->cylinder_sale_price, 2) }} each</span>
                        </div>
                        @endif
                    </div>
                    @endif
                </div>
            </div>

            <!-- ============================================
                 CYLINDER TRANSACTIONS (FIXED)
                 ============================================ -->
            @if(isset($cylinderTransactions) && $cylinderTransactions->count() > 0)
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">
                    <i class="fas fa-cylinder mr-2 text-gray-400"></i> Cylinder Transactions
                </h3>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Cylinder</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Remarks</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($cylinderTransactions as $transaction)
                            <tr>
                                <td class="px-4 py-2 text-sm">
                                    <a href="{{ route('cylinders.show', $transaction->cylinder) }}" class="text-blue-600 hover:underline">
                                        {{ $transaction->cylinder->cylinder_number ?? 'N/A' }}
                                    </a>
                                </td>
                                <td class="px-4 py-2 text-sm">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                        @if($transaction->transaction_type === 'received_filled') bg-green-100 text-green-800
                                        @elseif($transaction->transaction_type === 'received_empty') bg-gray-100 text-gray-800
                                        @elseif($transaction->transaction_type === 'returned_empty') bg-orange-100 text-orange-800
                                        @elseif($transaction->transaction_type === 'purchased_new') bg-purple-100 text-purple-800
                                        @elseif($transaction->transaction_type === 'exchanged') bg-blue-100 text-blue-800
                                        @else bg-gray-100 text-gray-800
                                        @endif">
                                        {{ str_replace('_', ' ', ucfirst($transaction->transaction_type ?? 'N/A')) }}
                                    </span>
                                </td>
                                <td class="px-4 py-2 text-sm">{{ $transaction->transaction_date ? $transaction->transaction_date->format('d-m-Y') : 'N/A' }}</td>
                                <td class="px-4 py-2 text-sm text-gray-500">{{ $transaction->remarks ?? '—' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            <!-- Accounting Entries -->
            @if(isset($journalEntries) && $journalEntries->count() > 0)
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">📊 Accounting Entries</h3>
                <div class="space-y-2">
                    @foreach($journalEntries as $entry)
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
            
            <!-- Summary Card -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">💰 Summary</h3>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Subtotal</span>
                        <span class="font-semibold">Rs. {{ number_format($purchase->subtotal, 2) }}</span>
                    </div>
                    @if($purchase->discount > 0)
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Discount</span>
                        <span class="font-semibold text-red-600">- Rs. {{ number_format($purchase->discount, 2) }}</span>
                    </div>
                    @endif
                    @if($purchase->tax > 0)
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Tax</span>
                        <span class="font-semibold">Rs. {{ number_format($purchase->tax, 2) }}</span>
                    </div>
                    @endif
                    <div class="border-t border-gray-200 pt-2">
                        <div class="flex justify-between">
                            <span class="text-base font-bold">Grand Total</span>
                            <span class="text-base font-bold text-green-600">Rs. {{ number_format($purchase->grand_total, 2) }}</span>
                        </div>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Paid</span>
                        <span class="font-semibold text-blue-600">Rs. {{ number_format($purchase->amount_paid, 2) }}</span>
                    </div>
                    <div class="flex justify-between border-t border-gray-200 pt-2">
                        <span class="text-sm font-semibold">Balance Due</span>
                        <span class="text-sm font-bold {{ $purchase->balance_due > 0 ? 'text-red-600' : 'text-green-600' }}">
                            Rs. {{ number_format($purchase->balance_due, 2) }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Account Details -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">📋 Account Details</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Debit Account</span>
                        <span class="font-semibold text-blue-600">{{ $purchase->debitAccount->account_name ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Credit Account</span>
                        <span class="font-semibold text-red-600">{{ $purchase->creditAccount->account_name ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>

            <!-- Payment History -->
            @if(isset($purchase->payments) && $purchase->payments->count() > 0)
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">
                    <i class="fas fa-money-bill-wave mr-2 text-gray-400"></i> Payments
                </h3>
                <div class="space-y-2">
                    @foreach($purchase->payments as $payment)
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
                    <span class="text-sm text-green-600">Rs. {{ number_format($purchase->amount_paid, 2) }}</span>
                </div>
            </div>
            @endif

            <!-- Quick Actions -->
            @if($purchase->balance_due > 0 && $purchase->status !== 'cancelled')
            <div class="mt-4">
                <button onclick="openPaymentModal()" 
                        class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg transition">
                    <i class="fas fa-money-bill-wave mr-2"></i> Record Payment
                </button>
            </div>
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
            <input type="hidden" name="purchase_id" value="{{ $purchase->id }}">

            <div class="grid grid-cols-2 gap-3 text-sm">
                <div>
                    <p class="text-gray-500">Invoice #</p>
                    <p class="font-semibold">{{ $purchase->purchase_invoice_no }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Supplier</p>
                    <p class="font-semibold">{{ $purchase->supplier->name ?? 'N/A' }}</p>
                </div>
            </div>

            <div class="bg-gray-50 p-3 rounded-lg grid grid-cols-2 gap-2 text-sm">
                <div>
                    <p class="text-gray-500">Total Amount</p>
                    <p class="font-bold">Rs. {{ number_format($purchase->grand_total, 2) }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Balance Due</p>
                    <p class="font-bold text-red-600">Rs. {{ number_format($purchase->balance_due, 2) }}</p>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Payment Amount *</label>
                <input type="number" step="0.01" name="amount" id="paymentAmount" required
                       max="{{ $purchase->balance_due }}"
                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <p class="text-xs text-gray-400 mt-1">Max: Rs. {{ number_format($purchase->balance_due, 2) }}</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Payment Method *</label>
                <select name="payment_method" id="paymentMethod" required
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="cash">💵 Cash</option>
                    <option value="bank_transfer">🏦 Bank Transfer</option>
                    <option value="cheque">📝 Cheque</option>
                    <option value="online">💳 Online Payment</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Payment Date *</label>
                <input type="date" name="payment_date" id="paymentDate" value="{{ date('Y-m-d') }}" required
                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                <textarea name="notes" id="paymentNotes" rows="2" 
                          class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                          placeholder="Optional notes..."></textarea>
            </div>

            <div class="flex justify-end space-x-3 pt-4 border-t">
                <button type="button" onclick="closePaymentModal()" 
                        class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-4 rounded-lg transition">
                    Cancel
                </button>
                <button type="submit" id="paymentSubmitBtn"
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
    function openPaymentModal() {
        document.getElementById('paymentModal').classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
        document.getElementById('paymentAmount').focus();
    }

    function closePaymentModal() {
        document.getElementById('paymentModal').classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    document.getElementById('paymentModal').addEventListener('click', function(e) {
        if (e.target === this) closePaymentModal();
    });

    // ============================================
    // PAYMENT FORM SUBMIT (AJAX)
    // ============================================
    document.getElementById('paymentForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const amount = parseFloat(document.getElementById('paymentAmount').value);
        const balance = {{ $purchase->balance_due }};

        if (!amount || amount <= 0) {
            alert('Please enter a valid payment amount.');
            return;
        }

        if (amount > balance) {
            alert('Payment amount cannot exceed balance due.');
            return;
        }

        if (!confirm('Confirm payment of Rs. ' + amount.toFixed(2) + ' for purchase #{{ $purchase->purchase_invoice_no }}?')) {
            return;
        }

        const formData = new FormData(this);
        const submitBtn = document.getElementById('paymentSubmitBtn');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Processing...';
        submitBtn.disabled = true;

        fetch('{{ route("purchases.payment", $purchase) }}', {
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