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
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Qty</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Price</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($sale->items as $item)
                            <tr>
                                <td class="px-6 py-3 text-sm">{{ $item->gasProduct->name ?? 'N/A' }}</td>
                                <td class="px-6 py-3 text-right">{{ number_format($item->quantity, 2) }}</td>
                                <td class="px-6 py-3 text-right">Rs. {{ number_format($item->price, 2) }}</td>
                                <td class="px-6 py-3 text-right font-semibold">Rs. {{ number_format($item->total, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
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

<!-- Payment Modal (Same as index) -->
<!-- Include payment modal from index or create a separate include -->

@push('scripts')
<script>
    // Payment modal functions (same as index)
    // ... copy payment modal functions from index

    // Override openPaymentModal for this page
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
        
        document.getElementById('paymentModal').classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }
</script>
@endpush
@endsection