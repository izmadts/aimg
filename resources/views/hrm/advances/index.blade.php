@extends('layouts.app')

@section('title', 'Employee Advances')

@section('content')
<div class="space-y-6">
    
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">💳 Employee Advances</h1>
            <p class="text-sm text-gray-500">Manage employee advance requests</p>
        </div>
        <a href="{{ route('hrm.advances.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-lg transition">
            <i class="fas fa-plus mr-2"></i> New Request
        </a>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-blue-500">
            <p class="text-xs text-gray-500">Total Requests</p>
            <p class="text-2xl font-bold">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-yellow-500">
            <p class="text-xs text-gray-500">Pending</p>
            <p class="text-2xl font-bold text-yellow-600">{{ $stats['pending'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-green-500">
            <p class="text-xs text-gray-500">Approved</p>
            <p class="text-2xl font-bold text-green-600">{{ $stats['approved'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-red-500">
            <p class="text-xs text-gray-500">Total Amount</p>
            <p class="text-2xl font-bold text-red-600">Rs. {{ number_format($stats['total_amount'], 0) }}</p>
        </div>
    </div>

    <!-- Advances Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employee</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Amount</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Installments</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($advances as $advance)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-3 text-sm">{{ $advance->employee->full_name ?? 'N/A' }}</td>
                        <td class="px-6 py-3 text-sm">{{ $advance->advance_date->format('d-m-Y') }}</td>
                        <td class="px-6 py-3 text-right font-bold">Rs. {{ number_format($advance->amount, 0) }}</td>
                        <td class="px-6 py-3 text-center">{{ $advance->deduction_installments }}</td>
                        <td class="px-6 py-3 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($advance->status === 'approved') bg-green-100 text-green-800
                                @elseif($advance->status === 'pending') bg-yellow-100 text-yellow-800
                                @elseif($advance->status === 'completed') bg-blue-100 text-blue-800
                                @else bg-red-100 text-red-800
                                @endif">
                                {{ ucfirst($advance->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-3 text-center">
                            <div class="flex items-center justify-center space-x-2">
                                @if($advance->status === 'pending')
                                    <button onclick="approveAdvance({{ $advance->id }})"
                                            class="text-green-600 hover:text-green-800 transition" title="Approve">
                                        <i class="fas fa-check-circle"></i>
                                    </button>
                                    <button onclick="rejectAdvance({{ $advance->id }})"
                                            class="text-red-600 hover:text-red-800 transition" title="Reject">
                                        <i class="fas fa-times-circle"></i>
                                    </button>
                                @endif
                                <form action="{{ route('hrm.advances.delete', $advance) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            onclick="return confirm('{{ $advance->status === 'approved' ? 'This advance was approved and posted to accounting. Deleting it will reverse the related accounting entries. Continue?' : 'Delete this advance request?' }}')"
                                            class="text-gray-400 hover:text-red-600 transition" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">No advances found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $advances->links() }}
        </div>
    </div>
</div>

@push('scripts')
<script>
    function approveAdvance(id) {
        if (confirm('Approve this advance request?')) {
            fetch('/hrm/advances/' + id + '/approve', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('✅ ' + data.message);
                    location.reload();
                } else {
                    alert('❌ Error: ' + data.message);
                }
            });
        }
    }

    function rejectAdvance(id) {
        if (confirm('Reject this advance request?')) {
            fetch('/hrm/advances/' + id + '/reject', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('✅ ' + data.message);
                    location.reload();
                } else {
                    alert('❌ Error: ' + data.message);
                }
            });
        }
    }
</script>
@endpush
@endsection