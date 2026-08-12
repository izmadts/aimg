@extends('layouts.app')

@section('title', 'Employee Leaves')

@section('content')
<div class="space-y-6">
    
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">🏖️ Employee Leaves</h1>
            <p class="text-sm text-gray-500">Manage employee leave requests</p>
        </div>
        <a href="{{ route('hrm.leaves.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-lg transition">
            <i class="fas fa-plus mr-2"></i> New Leave Request
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
            <p class="text-xs text-gray-500">Rejected</p>
            <p class="text-2xl font-bold text-red-600">{{ $stats['rejected'] }}</p>
        </div>
    </div>

    <!-- Leaves Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employee</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Start</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">End</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Days</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($leaves as $leave)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-3 text-sm">{{ $leave->employee->full_name ?? 'N/A' }}</td>
                        <td class="px-6 py-3">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($leave->leave_type === 'annual') bg-blue-100 text-blue-800
                                @elseif($leave->leave_type === 'sick') bg-red-100 text-red-800
                                @elseif($leave->leave_type === 'casual') bg-green-100 text-green-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ ucfirst($leave->leave_type) }}
                            </span>
                        </td>
                        <td class="px-6 py-3 text-sm">{{ $leave->start_date->format('d-m-Y') }}</td>
                        <td class="px-6 py-3 text-sm">{{ $leave->end_date->format('d-m-Y') }}</td>
                        <td class="px-6 py-3 text-center font-bold">{{ $leave->days }}</td>
                        <td class="px-6 py-3 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($leave->status === 'approved') bg-green-100 text-green-800
                                @elseif($leave->status === 'pending') bg-yellow-100 text-yellow-800
                                @else bg-red-100 text-red-800
                                @endif">
                                {{ ucfirst($leave->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-3 text-center">
                            @if($leave->status === 'pending')
                                <button onclick="approveLeave({{ $leave->id }})" 
                                        class="text-green-600 hover:text-green-800 transition" title="Approve">
                                    <i class="fas fa-check-circle"></i>
                                </button>
                                <button onclick="rejectLeave({{ $leave->id }})" 
                                        class="text-red-600 hover:text-red-800 transition" title="Reject">
                                    <i class="fas fa-times-circle"></i>
                                </button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-gray-500">No leave requests found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $leaves->links() }}
        </div>
    </div>
</div>

@push('scripts')
<script>
    function approveLeave(id) {
        if (confirm('Approve this leave request?')) {
            fetch('/hrm/leaves/' + id + '/approve', {
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

    function rejectLeave(id) {
        if (confirm('Reject this leave request?')) {
            fetch('/hrm/leaves/' + id + '/reject', {
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