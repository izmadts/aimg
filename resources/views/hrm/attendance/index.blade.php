@extends('layouts.app')

@section('title', 'Attendance')

@section('content')
<div class="space-y-6">
    
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">📋 Attendance</h1>
            <p class="text-sm text-gray-500">Mark and manage employee attendance</p>
        </div>
        <button onclick="openAttendanceModal()" 
                class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-lg transition">
            <i class="fas fa-check-circle mr-2"></i> Mark Attendance
        </button>
    </div>

    <!-- Today's Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-green-500">
            <p class="text-xs text-gray-500">Present</p>
            <p class="text-2xl font-bold text-green-600">{{ $todayStats['present'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-red-500">
            <p class="text-xs text-gray-500">Absent</p>
            <p class="text-2xl font-bold text-red-600">{{ $todayStats['absent'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-yellow-500">
            <p class="text-xs text-gray-500">Late</p>
            <p class="text-2xl font-bold text-yellow-600">{{ $todayStats['late'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-blue-500">
            <p class="text-xs text-gray-500">Leave/Holiday</p>
            <p class="text-2xl font-bold text-blue-600">{{ $todayStats['leave'] + $todayStats['holiday'] }}</p>
        </div>
    </div>

    <!-- Filter -->
    <div class="bg-white rounded-lg shadow p-4">
        <form method="GET" class="flex flex-wrap gap-4">
            <div>
                <select name="employee_id" class="rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">All Employees</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>
                            {{ $emp->full_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <input type="date" name="from_date" value="{{ request('from_date') }}" 
                       class="rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div>
                <input type="date" name="to_date" value="{{ request('to_date') }}" 
                       class="rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div>
                <select name="status" class="rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">All Status</option>
                    <option value="present" {{ request('status') == 'present' ? 'selected' : '' }}>Present</option>
                    <option value="absent" {{ request('status') == 'absent' ? 'selected' : '' }}>Absent</option>
                    <option value="late" {{ request('status') == 'late' ? 'selected' : '' }}>Late</option>
                    <option value="leave" {{ request('status') == 'leave' ? 'selected' : '' }}>Leave</option>
                    <option value="holiday" {{ request('status') == 'holiday' ? 'selected' : '' }}>Holiday</option>
                </select>
            </div>
            <div>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-lg transition">
                    <i class="fas fa-search mr-2"></i> Filter
                </button>
                <a href="{{ route('hrm.attendance') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium px-4 py-2 rounded-lg transition">
                    <i class="fas fa-times mr-2"></i> Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Attendance Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto max-h-96 overflow-y-auto">
            <table class="w-full">
                <thead class="bg-gray-50 sticky top-0">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employee</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Check In</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Check Out</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Late</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($attendances as $attendance)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-3 text-sm">{{ $attendance->employee->full_name ?? 'N/A' }}</td>
                        <td class="px-6 py-3 text-sm">{{ $attendance->date->format('d-m-Y') }}</td>
                        <td class="px-6 py-3 text-sm">{{ $attendance->check_in_time ? $attendance->check_in_time->format('h:i A') : '-' }}</td>
                        <td class="px-6 py-3 text-sm">{{ $attendance->check_out_time ? $attendance->check_out_time->format('h:i A') : '-' }}</td>
                        <td class="px-6 py-3 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($attendance->status === 'present') bg-green-100 text-green-800
                                @elseif($attendance->status === 'absent') bg-red-100 text-red-800
                                @elseif($attendance->status === 'late') bg-yellow-100 text-yellow-800
                                @elseif($attendance->status === 'leave') bg-blue-100 text-blue-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ ucfirst($attendance->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-3 text-right">{{ $attendance->late_minutes }}m</td>
                        <td class="px-6 py-3 text-center">
                            <form action="{{ route('hrm.attendance.delete', $attendance->id) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" onclick="return confirm('Delete this attendance record?')" 
                                        class="text-red-600 hover:text-red-800 transition" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-gray-500">No attendance records found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $attendances->links() }}
        </div>
    </div>
</div>

<!-- Attendance Modal -->
<div id="attendanceModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-lg bg-white">
        <div class="flex justify-between items-center pb-3 border-b">
            <h3 class="text-lg font-semibold text-gray-900">
                <i class="fas fa-check-circle text-blue-600 mr-2"></i> Mark Attendance
            </h3>
            <button onclick="closeAttendanceModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <form action="{{ route('hrm.attendance.store') }}" method="POST" class="mt-4 space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Employee *</label>
                <select name="employee_id" required class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Select Employee</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}">{{ $emp->full_name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Date *</label>
                <input type="date" name="date" value="{{ date('Y-m-d') }}" required
                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status *</label>
                <select name="status" required class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="present">✅ Present</option>
                    <option value="absent">❌ Absent</option>
                    <option value="late">⏰ Late</option>
                    <option value="leave">🏖️ Leave</option>
                    <option value="holiday">🎉 Holiday</option>
                </select>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Check In</label>
                    <input type="time" name="check_in_time" value="{{ date('H:i') }}"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Check Out</label>
                    <input type="time" name="check_out_time"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                <textarea name="notes" rows="2" 
                          class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                          placeholder="Any remarks..."></textarea>
            </div>
            <div class="flex justify-end space-x-3 pt-4 border-t">
                <button type="button" onclick="closeAttendanceModal()" 
                        class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-4 rounded-lg transition">
                    Cancel
                </button>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition">
                    <i class="fas fa-save mr-2"></i> Save
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openAttendanceModal() {
        document.getElementById('attendanceModal').classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function closeAttendanceModal() {
        document.getElementById('attendanceModal').classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    document.getElementById('attendanceModal').addEventListener('click', function(e) {
        if (e.target === this) closeAttendanceModal();
    });
</script>
@endpush
@endsection