@extends('layouts.app')

@section('title', 'Cylinder History Tracking')

@section('content')
<div class="space-y-6">
    
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">📜 Cylinder History Tracking</h1>
            <p class="text-sm text-gray-500">Complete history and journey of all cylinders</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('cylinders.tracking') }}" 
               class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-lg transition">
                <i class="fas fa-arrow-left mr-2"></i> Back to Tracking
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3">
        <div class="bg-white rounded-lg shadow p-3 border-l-4 border-blue-500">
            <p class="text-xs text-gray-500">Total Cylinders</p>
            <p class="text-xl font-bold">{{ $stats['total_cylinders'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-3 border-l-4 border-yellow-500">
            <p class="text-xs text-gray-500">Issued</p>
            <p class="text-xl font-bold text-yellow-600">{{ $stats['issued_cylinders'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-3 border-l-4 border-blue-500">
            <p class="text-xs text-gray-500">Sold</p>
            <p class="text-xl font-bold text-blue-600">{{ $stats['sold_cylinders'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-3 border-l-4 border-red-500">
            <p class="text-xs text-gray-500">Scrapped</p>
            <p class="text-xl font-bold text-red-600">{{ $stats['scrapped_cylinders'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-3 border-l-4 border-green-500">
            <p class="text-xs text-gray-500">Total Issues</p>
            <p class="text-xl font-bold text-green-600">{{ $stats['total_issues'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-3 border-l-4 border-purple-500">
            <p class="text-xs text-gray-500">Total Returns</p>
            <p class="text-xl font-bold text-purple-600">{{ $stats['total_returns'] }}</p>
        </div>
    </div>

    <!-- Search Cylinder -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">
            <i class="fas fa-search mr-2 text-gray-400"></i> Search Cylinder History
        </h3>
        <div class="flex gap-4">
            <div class="flex-1">
                <select id="cylinderSelect" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Select Cylinder</option>
                    @foreach($cylinders as $cylinder)
                        <option value="{{ $cylinder->id }}">
                            {{ $cylinder->cylinder_number }} - {{ $cylinder->gasProduct->name ?? 'N/A' }} ({{ $cylinder->status_label }})
                        </option>
                    @endforeach
                </select>
            </div>
            <button onclick="viewHistory()" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-6 py-2 rounded-lg transition">
                <i class="fas fa-eye mr-2"></i> View History
            </button>
            <button onclick="exportHistory()" class="bg-green-600 hover:bg-green-700 text-white font-medium px-6 py-2 rounded-lg transition">
                <i class="fas fa-file-export mr-2"></i> Export
            </button>
        </div>
    </div>

    <!-- Cylinder History Result -->
    <div id="historyResult" class="hidden">
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <!-- Cylinder Header -->
            <div class="p-6 border-b border-gray-200" id="cylinderHeader">
                <div class="flex justify-between items-start">
                    <div>
                        <h2 class="text-xl font-bold" id="cylNumber">CYL-0001</h2>
                        <p class="text-sm text-gray-500" id="cylGas">Oxygen (KG)</p>
                    </div>
                    <div>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium" id="cylStatus">
                            <i class="fas fa-circle text-xs mr-1.5"></i> In House - Empty
                        </span>
                    </div>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4 text-sm">
                    <div>
                        <span class="text-gray-500">Type:</span>
                        <span class="font-semibold" id="cylType">-</span>
                    </div>
                    <div>
                        <span class="text-gray-500">Capacity:</span>
                        <span class="font-semibold" id="cylCapacity">-</span>
                    </div>
                    <div>
                        <span class="text-gray-500">Current Gas:</span>
                        <span class="font-semibold" id="cylGasQty">-</span>
                    </div>
                    <div>
                        <span class="text-gray-500">Purchase Price:</span>
                        <span class="font-semibold" id="cylPrice">-</span>
                    </div>
                </div>
            </div>

            <!-- Journey Timeline -->
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">
                    <i class="fas fa-road mr-2 text-gray-400"></i> Cylinder Journey Timeline
                </h3>
                <div id="journeyTimeline" class="relative">
                    <!-- Timeline will be rendered here -->
                </div>
            </div>

            <!-- Transaction History -->
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-sm font-semibold text-gray-700">
                        <i class="fas fa-list mr-2 text-gray-400"></i> Transaction History
                    </h3>
                    <span class="text-xs text-gray-500" id="totalTransactions">0 transactions</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Party</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Gas Qty</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Deposit</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Damage</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reference</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Remarks</th>
                            </tr>
                        </thead>
                        <tbody id="transactionsBody" class="divide-y divide-gray-200">
                            <!-- Transactions will be rendered here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-sm font-semibold text-gray-700">
                <i class="fas fa-clock mr-2 text-gray-400"></i> Recent Transactions (Last 50)
            </h3>
        </div>
        <div class="overflow-x-auto max-h-96 overflow-y-auto">
            <table class="w-full">
                <thead class="bg-gray-50 sticky top-0">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cylinder</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer/Supplier</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reference</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Remarks</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($recentTransactions as $transaction)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-3 font-mono text-sm">
                            <a href="#" onclick="loadHistory({{ $transaction->cylinder_id }})" class="text-blue-600 hover:underline">
                                {{ $transaction->cylinder->cylinder_number ?? 'N/A' }}
                            </a>
                        </td>
                        <td class="px-6 py-3">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($transaction->transaction_type === 'issued') bg-yellow-100 text-yellow-800
                                @elseif($transaction->transaction_type === 'returned') bg-green-100 text-green-800
                                @elseif($transaction->transaction_type === 'sold') bg-blue-100 text-blue-800
                                @elseif($transaction->transaction_type === 'purchased') bg-purple-100 text-purple-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ $transaction->type_label }}
                            </span>
                        </td>
                        <td class="px-6 py-3 text-sm">
                            {{ $transaction->customer->name ?? ($transaction->supplier->name ?? 'System') }}
                        </td>
                        <td class="px-6 py-3 text-sm">{{ $transaction->created_at->format('d-m-Y H:i') }}</td>
                        <td class="px-6 py-3 text-sm">
                            @if($transaction->reference_document)
                                <span class="text-blue-600">{{ $transaction->reference_document }}</span>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-3 text-sm text-gray-500">{{ $transaction->remarks ?? '—' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">No transactions found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@push('scripts')
<script>
    // ============================================
    // HELPER FUNCTIONS
    // ============================================
    function formatCurrency(value) {
        if (value === null || value === undefined || isNaN(value) || value === '') {
            return '0.00';
        }
        return parseFloat(value).toFixed(2);
    }

    function formatNumber(value) {
        if (value === null || value === undefined || isNaN(value) || value === '') {
            return '0';
        }
        return parseFloat(value).toFixed(2);
    }

    function formatDate(dateStr) {
        if (!dateStr || dateStr === 'N/A') {
            return 'N/A';
        }
        try {
            const date = new Date(dateStr);
            return date.toLocaleDateString('en-GB', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            });
        } catch (e) {
            return dateStr;
        }
    }

    function getStatusColor(status) {
        const colors = {
            'in_house_empty': 'bg-gray-100 text-gray-800',
            'in_house_filled': 'bg-green-100 text-green-800',
            'issued': 'bg-yellow-100 text-yellow-800',
            'sold': 'bg-blue-100 text-blue-800',
            'under_maintenance': 'bg-orange-100 text-orange-800',
            'scrapped': 'bg-red-100 text-red-800'
        };
        return colors[status] || 'bg-gray-100 text-gray-800';
    }

    function getStatusLabel(status) {
        const labels = {
            'in_house_empty': 'In House - Empty',
            'in_house_filled': 'In House - Filled',
            'issued': 'Issued to Customer',
            'sold': 'Sold',
            'under_maintenance': 'Under Maintenance',
            'scrapped': 'Scrapped'
        };
        return labels[status] || status || 'Unknown';
    }

    function getTypeLabel(type) {
        const labels = {
            'issued': 'Issued to Customer',
            'returned': 'Returned from Customer',
            'sold': 'Sold to Customer',
            'purchased': 'Purchased New',
            'filled': 'Gas Filled',
            'emptied': 'Gas Emptied',
            'maintenance_in': 'Sent to Maintenance',
            'maintenance_out': 'Returned from Maintenance',
            'scrapped': 'Scrapped',
            'received_filled': 'Received Filled from Supplier',
            'received_empty': 'Received Empty from Supplier',
            'returned_empty': 'Returned Empty to Supplier',
            'exchanged': 'Exchanged with Supplier'
        };
        return labels[type] || type || 'Unknown';
    }

    function getTypeColor(type) {
        const colors = {
            'issued': 'bg-yellow-100 text-yellow-800',
            'returned': 'bg-green-100 text-green-800',
            'sold': 'bg-blue-100 text-blue-800',
            'purchased': 'bg-purple-100 text-purple-800',
            'filled': 'bg-green-100 text-green-800',
            'emptied': 'bg-gray-100 text-gray-800',
            'maintenance_in': 'bg-orange-100 text-orange-800',
            'maintenance_out': 'bg-blue-100 text-blue-800',
            'scrapped': 'bg-red-100 text-red-800',
            'received_filled': 'bg-green-100 text-green-800',
            'received_empty': 'bg-gray-100 text-gray-800',
            'returned_empty': 'bg-orange-100 text-orange-800',
            'exchanged': 'bg-purple-100 text-purple-800'
        };
        return colors[type] || 'bg-gray-100 text-gray-800';
    }

    function getJourneyIcon(type) {
        const icons = {
            'purchased': 'fa-shopping-cart',
            'received_filled': 'fa-arrow-down',
            'received_empty': 'fa-arrow-down',
            'filled': 'fa-plus-circle',
            'issued': 'fa-hand-holding',
            'returned': 'fa-undo',
            'sold': 'fa-check-circle',
            'maintenance_in': 'fa-tools',
            'maintenance_out': 'fa-check',
            'scrapped': 'fa-trash',
            'returned_empty': 'fa-undo',
            'exchanged': 'fa-exchange-alt',
            'emptied': 'fa-minus-circle'
        };
        return icons[type] || 'fa-circle';
    }

    function getJourneyColor(type) {
        const colors = {
            'purchased': 'purple',
            'received_filled': 'green',
            'received_empty': 'gray',
            'filled': 'green',
            'issued': 'yellow',
            'returned': 'blue',
            'sold': 'indigo',
            'maintenance_in': 'orange',
            'maintenance_out': 'green',
            'scrapped': 'red',
            'returned_empty': 'gray',
            'exchanged': 'purple',
            'emptied': 'gray'
        };
        return colors[type] || 'gray';
    }

    // ============================================
    // VIEW CYLINDER HISTORY
    // ============================================
    function viewHistory() {
        const cylinderId = document.getElementById('cylinderSelect').value;
        if (!cylinderId) {
            alert('Please select a cylinder.');
            return;
        }
        loadHistory(cylinderId);
    }

    function loadHistory(cylinderId) {
        // Show loading
        document.getElementById('historyResult').classList.remove('hidden');
        document.getElementById('transactionsBody').innerHTML = `
            <tr>
                <td colspan="9" class="px-6 py-8 text-center text-gray-500">
                    <i class="fas fa-spinner fa-spin text-2xl mb-2 block"></i>
                    Loading history...
                </td>
            </tr>
        `;
        document.getElementById('journeyTimeline').innerHTML = `
            <div class="text-center text-gray-500 py-4">
                <i class="fas fa-spinner fa-spin text-2xl mb-2 block"></i>
                Loading journey...
            </div>
        `;

        const root = "{{url('')}}";

        const url = `${root}/cylinders/tracking/history/${cylinderId}`;
        
        fetch(url, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (!data.success) {
                throw new Error(data.message || 'Unknown error');
            }

            // Update cylinder header
            document.getElementById('cylNumber').textContent = data.cylinder.number || '-';
            document.getElementById('cylGas').textContent = (data.cylinder.gas_name || 'N/A') + ' (' + formatNumber(data.cylinder.capacity) + ')';
            document.getElementById('cylType').textContent = data.cylinder.type || '-';
            document.getElementById('cylCapacity').textContent = formatNumber(data.cylinder.capacity) + ' KG';
            document.getElementById('cylGasQty').textContent = formatNumber(data.cylinder.current_gas_quantity) + ' KG';
            document.getElementById('cylPrice').textContent = 'Rs. ' + formatCurrency(data.cylinder.purchase_price);
            document.getElementById('totalTransactions').textContent = (data.total_transactions || 0) + ' transactions';

            // Update status
            const statusEl = document.getElementById('cylStatus');
            statusEl.className = 'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium ' + 
                getStatusColor(data.cylinder.status);
            statusEl.innerHTML = '<i class="fas fa-circle text-xs mr-1.5"></i> ' + 
                getStatusLabel(data.cylinder.status);

            // Render journey timeline
            renderJourney(data.journey);

            // Render transactions
            renderTransactions(data.transactions);

            // Scroll to result
            document.getElementById('historyResult').scrollIntoView({ behavior: 'smooth' });
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('transactionsBody').innerHTML = `
                <tr>
                    <td colspan="9" class="px-6 py-8 text-center text-red-500">
                        <i class="fas fa-exclamation-circle text-2xl mb-2 block"></i>
                        Error: ${error.message}
                    </td>
                </tr>
            `;
            document.getElementById('journeyTimeline').innerHTML = `
                <div class="text-center text-red-500 py-4">
                    <i class="fas fa-exclamation-circle text-2xl mb-2 block"></i>
                    Error loading journey
                </div>
            `;
        });
    }

    // ============================================
    // RENDER JOURNEY TIMELINE
    // ============================================
    function renderJourney(journey) {
        const container = document.getElementById('journeyTimeline');
        
        if (!journey || journey.length === 0) {
            container.innerHTML = '<p class="text-gray-500 text-sm text-center py-4">No journey data available.</p>';
            return;
        }

        let html = '<div class="relative">';
        html += '<div class="absolute left-4 top-0 bottom-0 w-0.5 bg-gray-200"></div>';

        journey.forEach((item, index) => {
            const isLast = index === journey.length - 1;
            const colorMap = {
                'purple': 'bg-purple-500',
                'green': 'bg-green-500',
                'yellow': 'bg-yellow-500',
                'blue': 'bg-blue-500',
                'indigo': 'bg-indigo-500',
                'orange': 'bg-orange-500',
                'red': 'bg-red-500',
                'gray': 'bg-gray-500'
            };
            const colorClass = colorMap[item.color] || 'bg-gray-500';
            const icon = item.icon || 'fa-circle';
            
            html += `
                <div class="flex items-start mb-6 relative">
                    <div class="flex-shrink-0 w-8 h-8 ${colorClass} rounded-full flex items-center justify-center text-white text-xs z-10">
                        <i class="fas ${icon}"></i>
                    </div>
                    <div class="ml-4 flex-1">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                <span class="font-semibold text-sm">${item.type_label || item.type || 'Unknown'}</span>
                                <span class="text-xs text-gray-400">${item.date_formatted || ''}</span>
                            </div>
                            ${item.party_name ? `<span class="text-xs text-gray-500">${item.party_name}</span>` : ''}
                        </div>
                        ${item.remarks ? `<p class="text-sm text-gray-600 mt-1">${item.remarks}</p>` : ''}
                        ${item.reference ? `<p class="text-xs text-gray-400 mt-1">Reference: ${item.reference}</p>` : ''}
                    </div>
                </div>
            `;
        });

        html += '</div>';
        container.innerHTML = html;
    }

    // ============================================
    // RENDER TRANSACTIONS
    // ============================================
    function renderTransactions(transactions) {
        const tbody = document.getElementById('transactionsBody');
        
        if (!transactions || transactions.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="9" class="px-6 py-8 text-center text-gray-500">
                        No transactions found for this cylinder.
                    </td>
                </tr>
            `;
            return;
        }

        let html = '';
        transactions.forEach(trans => {
            const partyName = trans.customer ? trans.customer.name : (trans.supplier ? trans.supplier.name : 'System');
            const referenceHtml = trans.reference_url 
                ? `<a href="${trans.reference_url}" target="_blank" class="text-blue-600 hover:underline">${trans.reference_document || ''}</a>`
                : (trans.reference_document || '—');

            html += `
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-3 text-sm">${trans.date_formatted || ''}</td>
                    <td class="px-6 py-3">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getTypeColor(trans.type)}">
                            ${getTypeLabel(trans.type)}
                        </span>
                    </td>
                    <td class="px-6 py-3 text-sm">${partyName}</td>
                    <td class="px-6 py-3 text-sm">${trans.user ? trans.user.name : 'N/A'}</td>
                    <td class="px-6 py-3 text-right text-sm">${formatNumber(trans.gas_quantity)}</td>
                    <td class="px-6 py-3 text-right text-sm">${formatCurrency(trans.security_deposit_charged)}</td>
                    <td class="px-6 py-3 text-right text-sm text-red-600">${formatCurrency(trans.damage_charge)}</td>
                    <td class="px-6 py-3 text-sm">${referenceHtml}</td>
                    <td class="px-6 py-3 text-sm text-gray-500">${trans.remarks || '—'}</td>
                </tr>
            `;
        });

        tbody.innerHTML = html;
    }

    // ============================================
    // EXPORT HISTORY
    // ============================================
    function exportHistory() {
        const cylinderId = document.getElementById('cylinderSelect').value;
        if (!cylinderId) {
            alert('Please select a cylinder to export.');
            return;
        }
        window.location.href = `cylinders/tracking/history/export?cylinder_id=${cylinderId}`;
    }

    // ============================================
    // AUTO-LOAD FIRST CYLINDER
    // ============================================
    document.addEventListener('DOMContentLoaded', function() {
        const select = document.getElementById('cylinderSelect');
        if (select && select.options.length > 0) {
            // Auto-select first cylinder
            select.selectedIndex = 0;
        }
    });
</script>
@endpush











<?php /* @push('scripts')
<script>
    // ============================================
// VIEW CYLINDER HISTORY
// ============================================
function viewHistory() {
    const cylinderId = document.getElementById('cylinderSelect').value;
    if (!cylinderId) {
        alert('Please select a cylinder.');
        return;
    }
    loadHistory(cylinderId);
}

function loadHistory(cylinderId) {
    // Show loading
    document.getElementById('historyResult').classList.remove('hidden');
    document.getElementById('transactionsBody').innerHTML = `
        <tr>
            <td colspan="9" class="px-6 py-8 text-center text-gray-500">
                <i class="fas fa-spinner fa-spin text-2xl mb-2 block"></i>
                Loading history...
            </td>
        </tr>
    `;
    document.getElementById('journeyTimeline').innerHTML = `
        <div class="text-center text-gray-500 py-4">
            <i class="fas fa-spinner fa-spin text-2xl mb-2 block"></i>
            Loading journey...
        </div>
    `;

    // ✅ Use the correct route with proper URL
    const root = "{{url('')}}";
    const url = `${root}/cylinders/tracking/history/${cylinderId}`;
    
    fetch(url, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (!data.success) {
            throw new Error(data.message || 'Unknown error');
        }

        // Update cylinder header
        document.getElementById('cylNumber').textContent = data.cylinder.number;
        document.getElementById('cylGas').textContent = data.cylinder.gas_name + ' (' + data.cylinder.capacity + ')';
        document.getElementById('cylType').textContent = data.cylinder.type || '-';
        document.getElementById('cylCapacity').textContent = data.cylinder.capacity + ' KG';
        document.getElementById('cylGasQty').textContent = data.cylinder.current_gas_quantity + ' KG';
        document.getElementById('cylPrice').textContent = 'Rs. ' + (data.cylinder.purchase_price || 0).toFixed(2);
        document.getElementById('totalTransactions').textContent = data.total_transactions + ' transactions';

        // Update status
        const statusEl = document.getElementById('cylStatus');
        const statusColors = {
            'in_house_empty': 'bg-gray-100 text-gray-800',
            'in_house_filled': 'bg-green-100 text-green-800',
            'issued': 'bg-yellow-100 text-yellow-800',
            'sold': 'bg-blue-100 text-blue-800',
            'under_maintenance': 'bg-orange-100 text-orange-800',
            'scrapped': 'bg-red-100 text-red-800'
        };
        statusEl.className = 'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium ' + 
            (statusColors[data.cylinder.status] || 'bg-gray-100 text-gray-800');
        statusEl.innerHTML = '<i class="fas fa-circle text-xs mr-1.5"></i> ' + data.cylinder.status_label;

        // Render journey timeline
        renderJourney(data.journey);

        // Render transactions
        renderTransactions(data.transactions);

        // Scroll to result
        document.getElementById('historyResult').scrollIntoView({ behavior: 'smooth' });
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('transactionsBody').innerHTML = `
            <tr>
                <td colspan="9" class="px-6 py-8 text-center text-red-500">
                    <i class="fas fa-exclamation-circle text-2xl mb-2 block"></i>
                    Error loading cylinder history: ${error.message}
                </td>
            </tr>
        `;
        document.getElementById('journeyTimeline').innerHTML = `
            <div class="text-center text-red-500 py-4">
                <i class="fas fa-exclamation-circle text-2xl mb-2 block"></i>
                Error loading journey
            </div>
        `;
        alert('Error loading cylinder history: ' + error.message);
    });
}

    // ============================================
    // RENDER JOURNEY TIMELINE
    // ============================================
    function renderJourney(journey) {
        const container = document.getElementById('journeyTimeline');
        
        if (!journey || journey.length === 0) {
            container.innerHTML = '<p class="text-gray-500 text-sm">No journey data available.</p>';
            return;
        }

        let html = '<div class="relative">';
        html += '<div class="absolute left-4 top-0 bottom-0 w-0.5 bg-gray-200"></div>';

        journey.forEach((item, index) => {
            const isLast = index === journey.length - 1;
            const colors = {
                'purple': 'bg-purple-500',
                'green': 'bg-green-500',
                'yellow': 'bg-yellow-500',
                'blue': 'bg-blue-500',
                'indigo': 'bg-indigo-500',
                'orange': 'bg-orange-500',
                'red': 'bg-red-500',
                'gray': 'bg-gray-500'
            };
            const colorClass = colors[item.color] || 'bg-gray-500';
            
            html += `
                <div class="flex items-start mb-6 relative">
                    <div class="flex-shrink-0 w-8 h-8 ${colorClass} rounded-full flex items-center justify-center text-white text-xs z-10">
                        <i class="fas ${item.icon}"></i>
                    </div>
                    <div class="ml-4 flex-1">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                <span class="font-semibold text-sm">${item.type_label}</span>
                                <span class="text-xs text-gray-400">${item.date_formatted}</span>
                            </div>
                            ${item.party_name ? `<span class="text-xs text-gray-500">${item.party_name}</span>` : ''}
                        </div>
                        ${item.remarks ? `<p class="text-sm text-gray-600 mt-1">${item.remarks}</p>` : ''}
                        ${item.reference ? `<p class="text-xs text-gray-400 mt-1">Reference: ${item.reference}</p>` : ''}
                    </div>
                </div>
            `;
        });

        html += '</div>';
        container.innerHTML = html;
    }

    // ============================================
    // RENDER TRANSACTIONS
    // ============================================
    function renderTransactions(transactions) {
        const tbody = document.getElementById('transactionsBody');
        
        if (!transactions || transactions.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="9" class="px-6 py-8 text-center text-gray-500">
                        No transactions found for this cylinder.
                    </td>
                </tr>
            `;
            return;
        }

        let html = '';
        transactions.forEach(trans => {
            const typeColors = {
                'issued': 'bg-yellow-100 text-yellow-800',
                'returned': 'bg-green-100 text-green-800',
                'sold': 'bg-blue-100 text-blue-800',
                'purchased': 'bg-purple-100 text-purple-800',
                'filled': 'bg-green-100 text-green-800',
                'emptied': 'bg-gray-100 text-gray-800',
                'maintenance_in': 'bg-orange-100 text-orange-800',
                'maintenance_out': 'bg-blue-100 text-blue-800',
                'scrapped': 'bg-red-100 text-red-800',
                'received_filled': 'bg-green-100 text-green-800',
                'received_empty': 'bg-gray-100 text-gray-800',
                'returned_empty': 'bg-orange-100 text-orange-800',
                'exchanged': 'bg-purple-100 text-purple-800'
            };
            const colorClass = typeColors[trans.type] || 'bg-gray-100 text-gray-800';

            const partyName = trans.customer ? trans.customer.name : (trans.supplier ? trans.supplier.name : 'System');
            const referenceHtml = trans.reference_url 
                ? `<a href="${trans.reference_url}" target="_blank" class="text-blue-600 hover:underline">${trans.reference_document}</a>`
                : (trans.reference_document || '—');

            html += `
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-3 text-sm">${trans.date_formatted}</td>
                    <td class="px-6 py-3">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${colorClass}">
                            ${trans.type_label}
                        </span>
                    </td>
                    <td class="px-6 py-3 text-sm">${partyName}</td>
                    <td class="px-6 py-3 text-sm">${trans.user ? trans.user.name : 'N/A'}</td>
                    <td class="px-6 py-3 text-right text-sm">${trans.gas_quantity || 0}</td>
                    <td class="px-6 py-3 text-right text-sm">${trans.security_deposit_charged || 0}</td>
                    <td class="px-6 py-3 text-right text-sm text-red-600">${trans.damage_charge || 0}</td>
                    <td class="px-6 py-3 text-sm">${referenceHtml}</td>
                    <td class="px-6 py-3 text-sm text-gray-500">${trans.remarks || '—'}</td>
                </tr>
            `;
        });

        tbody.innerHTML = html;
    }

    // ============================================
    // EXPORT HISTORY
    // ============================================
    function exportHistory() {
        const cylinderId = document.getElementById('cylinderSelect').value;
        if (!cylinderId) {
            alert('Please select a cylinder to export.');
            return;
        }
        window.location.href = `/cylinders/tracking/history/export?cylinder_id=${cylinderId}`;
    }

    // ============================================
    // AUTO-LOAD FIRST CYLINDER
    // ============================================
    document.addEventListener('DOMContentLoaded', function() {
        const select = document.getElementById('cylinderSelect');
        if (select.options.length > 1) {
            // Auto-load first cylinder with issued status
            for (let i = 0; i < select.options.length; i++) {
                if (select.options[i].text.includes('issued')) {
                    select.selectedIndex = i;
                    break;
                }
            }
            // loadHistory(select.value);
        }
    });
</script>

@endpush
*/ ?>
@endsection