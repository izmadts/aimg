@extends('layouts.app')

@section('title', 'Track Customer: ' . $customer->name)

@section('content')
<div class="space-y-6">

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">🔍 {{ $customer->name }}</h1>
            <p class="text-sm text-gray-500">Cylinders currently issued to this customer</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('customers.show', $customer) }}" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-lg transition">
                <i class="fas fa-user mr-2"></i> Customer Profile
            </a>
            <a href="{{ route('cylinders.tracking') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-medium px-4 py-2 rounded-lg transition">
                <i class="fas fa-arrow-left mr-2"></i> Back to Tracking
            </a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-blue-500">
            <p class="text-xs text-gray-500">Customer</p>
            <p class="text-lg font-bold">{{ $customer->name }}</p>
            <p class="text-xs text-gray-400">{{ $customer->phone ?? 'N/A' }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-yellow-500">
            <p class="text-xs text-gray-500">Cylinder Types Held</p>
            <p class="text-2xl font-bold text-yellow-600">{{ $cylinders->count() }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-purple-500">
            <p class="text-xs text-gray-500">Total Deposit Held</p>
            <p class="text-2xl font-bold text-purple-600">Rs. {{ number_format($cylinders->sum('security_deposit'), 2) }}</p>
        </div>
    </div>

    <!-- Cylinders Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-sm font-semibold text-gray-700">
                <i class="fas fa-list mr-2 text-gray-400"></i> Outstanding Cylinders
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cylinder #</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Gas</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Quantity</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Issued Date</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Days Out</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Deposit</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reference</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($cylinders as $item)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-3 font-mono font-medium">{{ $item->cylinder->cylinder_number ?? 'N/A' }}</td>
                            <td class="px-6 py-3 text-sm">{{ $item->cylinder->gasProduct->name ?? 'N/A' }}</td>
                            <td class="px-6 py-3 text-sm">{{ $item->cylinder->type ?? 'N/A' }}</td>
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
                            <td class="px-6 py-3 text-sm">{{ $item->reference_document ?? '—' }}</td>
                            <td class="px-6 py-3 text-center">
                                @if($item->cylinder)
                                    <a href="{{ route('cylinders.show', $item->cylinder) }}" class="text-blue-600 hover:text-blue-800" title="View Cylinder">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-8 text-center text-gray-500">
                                <i class="fas fa-inbox text-3xl mb-2 text-gray-300 block"></i>
                                No cylinders currently issued to this customer.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
