@extends('layouts.app')

@section('title', 'Cylinder Tracking')

@section('content')
<div class="space-y-6">
    
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">🔍 Cylinder Tracking</h1>
            <p class="text-sm text-gray-500">Track all cylinders issued to customers</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('cylinders.tracking.export') }}" 
               class="bg-gray-600 hover:bg-gray-700 text-white font-medium px-4 py-2 rounded-lg transition">
                <i class="fas fa-file-export mr-2"></i> Export
            </a>
            <a href="{{ route('cylinders.index') }}" 
               class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-lg transition">
                <i class="fas fa-list mr-2"></i> All Cylinders
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-yellow-500">
            <p class="text-xs text-gray-500">Total Issued Cylinders</p>
            <p class="text-2xl font-bold text-yellow-600">{{ $stats['total_issued'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-orange-500">
            <p class="text-xs text-gray-500">Pending Return (7+ days)</p>
            <p class="text-2xl font-bold text-orange-600">{{ $stats['pending_return_7'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-red-500">
            <p class="text-xs text-gray-500">Pending Return (14+ days)</p>
            <p class="text-2xl font-bold text-red-600">{{ $stats['pending_return_14'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-purple-500">
            <p class="text-xs text-gray-500">Customers with Cylinders</p>
            <p class="text-2xl font-bold text-purple-600">{{ $stats['total_customers'] }}</p>
        </div>
    </div>

    <!-- Track Cylinder Form -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">
            <i class="fas fa-search mr-2 text-gray-400"></i> Track Cylinder
        </h3>
        <form action="{{ route('cylinders.tracking.track') }}" method="POST" class="flex gap-4">
            @csrf
            <div class="flex-1">
                <input type="text" name="cylinder_number" 
                       placeholder="Enter cylinder number (e.g., CYL-0001)"
                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-6 py-2 rounded-lg transition">
                <i class="fas fa-search mr-2"></i> Track
            </button>
        </form>
    </div>

    <!-- Issued Cylinders List -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-sm font-semibold text-gray-700">
                <i class="fas fa-list mr-2 text-gray-400"></i> Issued Cylinders
            </h3>
            <span class="text-xs text-gray-500">{{ $issuedCylinders->count() }} cylinders issued</span>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cylinder #</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Gas</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Qty</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Issued Date</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Days Out</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Deposit</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($issuedCylinders as $item)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-3 font-mono font-medium">
                            {{ $item->cylinder->cylinder_number }}
                        </td>
                        <td class="px-6 py-3 text-sm">{{ $item->cylinder->gasProduct->name ?? 'N/A' }}</td>
                        <td class="px-6 py-3 text-sm">
                            @if($item->customer)
                                <span class="font-medium">{{ $item->customer->name }}</span>
                                <span class="text-xs text-gray-400 ml-1">({{ $item->customer->phone ?? 'N/A' }})</span>
                            @else
                                <span class="text-gray-400">N/A</span>
                            @endif
                        </td>
                        <td class="px-6 py-3 text-center font-semibold">{{ $item->quantity }}</td>
                        <td class="px-6 py-3 text-sm">{{ $item->issued_date?->format('d-m-Y') ?? 'N/A' }}</td>
                        <td class="px-6 py-3 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($item->days_out > 30) bg-red-100 text-red-800
                                @elseif($item->days_out > 14) bg-orange-100 text-orange-800
                                @elseif($item->days_out > 7) bg-yellow-100 text-yellow-800
                                @else bg-green-100 text-green-800
                                @endif">
                                {{ $item->days_out }} days
                            </span>
                        </td>
                        <td class="px-6 py-3 text-right text-sm">Rs. {{ number_format($item->security_deposit, 2) }}</td>
                        <td class="px-6 py-3 text-center">
                            <div class="flex items-center justify-center space-x-2">
                                <a href="{{ route('cylinders.show', $item->cylinder) }}" 
                                   class="text-blue-600 hover:text-blue-800 transition" title="View Cylinder">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if($item->customer)
                                    <a href="{{ route('customers.show', $item->customer) }}" 
                                       class="text-purple-600 hover:text-purple-800 transition" title="View Customer">
                                        <i class="fas fa-user"></i>
                                    </a>
                                @endif
                                <button onclick="openReturnModal({{ $item->cylinder->id }}, '{{ $item->cylinder->cylinder_number }}', {{ $item->customer->id ?? 0 }}, '{{ $item->customer->name ?? '' }}')" 
                                        class="text-green-600 hover:text-green-800 transition" title="Return Cylinder">
                                    <i class="fas fa-undo"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                            <i class="fas fa-inbox text-3xl mb-2 text-gray-300 block"></i>
                            No cylinders currently issued.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Return Modal (Same as before) -->
<!-- ... return modal code ... -->

@push('scripts')
<script>
    function openReturnModal(cylinderId, cylinderNumber, customerId, customerName) {
        // Your existing return modal logic
        alert('Return cylinder: ' + cylinderNumber + ' for customer: ' + customerName);
    }
</script>
@endpush
@endsection