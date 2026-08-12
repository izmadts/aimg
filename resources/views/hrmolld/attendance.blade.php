@extends('layouts.app')

@section('title', 'Attendance')

@section('content')
<div class="space-y-6">
    
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">📋 Attendance</h1>
            <p class="text-sm text-gray-500">Mark and manage employee attendance</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <button onclick="openMarkModal()" 
                    class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-lg transition">
                <i class="fas fa-check-circle mr-2"></i> Mark Attendance
            </button>
            <button onclick="window.location.href='{{ route('hrm.attendance.export') }}'" 
                    class="bg-gray-600 hover:bg-gray-700 text-white font-medium px-4 py-2 rounded-lg transition">
                <i class="fas fa-file-export mr-2"></i> Export
            </button>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-3">
        <div class="bg-white rounded-lg shadow p-3 border-l-4 border-blue-500">
            <p class="text-xs text-gray-500">Total</p>
            <p class="text-xl font-bold">{{ $stats['total'] ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-3 border-l-4 border-green-500">
            <p class="text-xs text-gray-500">Present</p>
            <p class="text-xl font-bold text-green-600">{{ $stats['present'] ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-3 border-l-4 border-red-500">
            <p class="text-xs text-gray-500">Absent</p>
            <p class="text-xl font-bold text-red-600">{{ $stats['absent'] ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-3 border-l-4 border-yellow-500">
            <p class="text-xs text-gray-500">Late</p>
            <p class="text-xl font-bold text-yellow-600">{{ $stats['late'] ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-3 border-l-4 border-blue-500">
            <p class="text-xs text-gray-500">Leave</p>
            <p class="text-xl font-bold text-blue-600">{{ $stats['leave'] ?? 0 }}</p>
        </div>
    </div>

    <!-- Date Filter -->
    <div class="bg-white rounded-lg shadow p-4">
        <form method="GET" class="flex flex-wrap gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">From Date</label>
                <input type="date" name="from_date" value="{{ request('from_date', now()->startOfMonth()->format('Y-m-d')) }}"
                       class="rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">To Date</label>
                <input type="date" name="to_date" value="{{ request('to_date', now()->format('Y-m-d')) }}"
                       class="rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Department</label>
                <select name="department" class="rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">All</option>
                    <option value="sales" {{ request('department') == 'sales' ? 'selected' : '' }}>Sales</option>
                    <option value="operations" {{ request('department') == 'operations' ? 'selected' : '' }}>Operations</option>
                    <option value="finance" {{ request('department') == 'finance' ? 'selected' : '' }}>Finance</option>
                    <option value="hr" {{ request('department') == 'hr' ? 'selected' : '' }}>HR</option>
                    <option value="admin" {{ request('department') == 'admin' ? 'selected' : '' }}>Admin</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Status</label>
                <select name="status" class="rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">All</option>
                    <option value="present" {{ request('status') == 'present' ? 'selected' : '' }}>Present</option>
                    <option value="absent" {{ request('status') == 'absent' ? 'selected' : '' }}>Absent</option>
                    <option value="late" {{ request('status') == 'late' ? 'selected' : '' }}>Late</option>
                    <option value="leave" {{ request('status') == 'leave' ? 'selected' : '' }}>Leave</option>
                    <option value="holiday" {{ request('status') == 'holiday' ? 'selected' : '' }}>Holiday</option>
                </select>
            </div>
            <div class="flex items-end gap-2">
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
        <div class="overflow-x-auto max-h-[600px] overflow-y-auto">
            <table class="w-full">
                <thead class="bg-gray-50 sticky top-0">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employee</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Department</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Check In</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Check Out</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Overtime</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Late</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($attendances as $attendance)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-3 text-sm text-gray-500">{{ $loop->iteration }}</td>
                        <td class="px-6 py-3">
                            <span class="font-medium">{{ $attendance->employee->full_name ?? 'N/A' }}</span>
                            <span class="text-xs text-gray-400 block">{{ $attendance->employee->employee_code ?? '' }}</span>
                        </td>
                        <td class="px-6 py-3 text-sm">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ ucfirst($attendance->employee->department ?? 'N/A') }}
                            </span>
                        </td>
                        <td class="px-6 py-3 text-sm">{{ $attendance->date->format('d-m-Y') }}</td>
                        <td class="px-6 py-3 text-sm">{{ $attendance->check_in_time ? $attendance->check_in_time->format('h:i A') : '-' }}</td>
                        <td class="px-6 py-3 text-sm">{{ $attendance->check_out_time ? $attendance->check_out_time->format('h:i A') : '-' }}</td>
                        <td class="px-6 py-3 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($attendance->status === 'present') bg-green-100 text-green-800
                                @elseif($attendance->status === 'absent') bg-red-100 text-red-800
                                @elseif($attendance->status === 'late') bg-yellow-100 text-yellow-800
                                @elseif($attendance->status === 'leave') bg-blue-100 text-blue-800
                                @elseif($attendance->status === 'holiday') bg-purple-100 text-purple-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ ucfirst($attendance->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-3 text-right">{{ $attendance->overtime_hours }}h</td>
                        <td class="px-6 py-3 text-right">{{ $attendance->late_minutes }}m</td>
                        <td class="px-6 py-3 text-center">
                            <div class="flex items-center justify-center space-x-2">
                                <button onclick="editAttendance({{ $attendance->id }})" 
                                        class="text-yellow-600 hover:text-yellow-800 transition" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="{{ route('hrm.attendance.delete', $attendance->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" onclick="return confirm('Delete this attendance record?')" 
                                            class="text-red-600 hover:text-red-800 transition" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="px-6 py-8 text-center text-gray-500">
                            <i class="fas fa-inbox text-3xl mb-2 text-gray-300 block"></i>
                            No attendance records found.
                            <button onclick="openMarkModal()" class="text-blue-600 hover:underline ml-1">Mark attendance</button>
                        </td>
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

<!-- ============================================
     MARK ATTENDANCE MODAL
     ============================================ -->
<div id="markModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="relative top-10 mx-auto p-5 border w-full max-w-lg shadow-lg rounded-lg bg-white max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center pb-3 border-b sticky top-0 bg-white">
            <h3 class="text-lg font-semibold text-gray-900">
                <i class="fas fa-check-circle text-blue-600 mr-2"></i> Mark Attendance
            </h3>
            <button onclick="closeMarkModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <form action="{{ route('hrm.attendance.store') }}" method="POST" class="mt-4 space-y-4">
            @csrf

            <!-- Employee Selection -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Select Employee *</label>
                <select name="employee_id" required
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Select Employee</option>
                    @foreach($employees ?? [] as $employee)
                        <option value="{{ $employee->id }}">{{ $employee->full_name }} ({{ $employee->employee_code }})</option>
                    @endforeach
                </select>
            </div>

            <!-- Date -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Date *</label>
                <input type="date" name="date" value="{{ date('Y-m-d') }}" required
                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>

            <!-- Status -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status *</label>
                <select name="status" id="attendanceStatus" required
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        onchange="toggleTimeFields()">
                    <option value="present">✅ Present</option>
                    <option value="absent">❌ Absent</option>
                    <option value="late">⏰ Late</option>
                    <option value="leave">🏖️ Leave</option>
                    <option value="holiday">🎉 Holiday</option>
                </select>
            </div>

            <!-- Time Fields (Hidden for Absent, Leave, Holiday) -->
            <div id="timeFields" class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Check In Time</label>
                    <input type="time" name="check_in_time" value="{{ date('H:i') }}"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Check Out Time</label>
                    <input type="time" name="check_out_time" value="{{ date('H:i', strtotime('+8 hours')) }}"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
            </div>

            <!-- Notes -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                <textarea name="notes" rows="2" 
                          class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                          placeholder="Any remarks..."></textarea>
            </div>

            <div class="flex justify-end space-x-3 pt-4 border-t">
                <button type="button" onclick="closeMarkModal()" 
                        class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-4 rounded-lg transition">
                    Cancel
                </button>
                <button type="submit" 
                        class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition">
                    <i class="fas fa-save mr-2"></i> Save Attendance
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ============================================
     BULK ATTENDANCE MODAL
     ============================================ -->
<div id="bulkModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="relative top-10 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-lg bg-white max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center pb-3 border-b sticky top-0 bg-white">
            <h3 class="text-lg font-semibold text-gray-900">
                <i class="fas fa-users text-blue-600 mr-2"></i> Bulk Attendance
            </h3>
            <button onclick="closeBulkModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <form action="{{ route('hrm.attendance.bulk') }}" method="POST" class="mt-4 space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Date *</label>
                <input type="date" name="date" value="{{ date('Y-m-d') }}" required
                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status *</label>
                <select name="status" required
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="present">✅ Present</option>
                    <option value="absent">❌ Absent</option>
                    <option value="late">⏰ Late</option>
                    <option value="leave">🏖️ Leave</option>
                    <option value="holiday">🎉 Holiday</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Select Employees *</label>
                <div class="border rounded-lg p-3 max-h-48 overflow-y-auto">
                    @foreach($employees ?? [] as $employee)
                        <div class="flex items-center py-1">
                            <input type="checkbox" name="employee_ids[]" value="{{ $employee->id }}"
                                   class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <label class="ml-2 text-sm text-gray-700">{{ $employee->full_name }} ({{ $employee->employee_code }})</label>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="flex justify-end space-x-3 pt-4 border-t">
                <button type="button" onclick="closeBulkModal()" 
                        class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-4 rounded-lg transition">
                    Cancel
                </button>
                <button type="submit" 
                        class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition">
                    <i class="fas fa-save mr-2"></i> Save Bulk Attendance
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    // ============================================
    // MARK ATTENDANCE MODAL
    // ============================================
    function openMarkModal() {
        document.getElementById('markModal').classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function closeMarkModal() {
        document.getElementById('markModal').classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    document.getElementById('markModal').addEventListener('click', function(e) {
        if (e.target === this) closeMarkModal();
    });

    // ============================================
    // BULK ATTENDANCE MODAL
    // ============================================
    function openBulkModal() {
        document.getElementById('bulkModal').classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function closeBulkModal() {
        document.getElementById('bulkModal').classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    document.getElementById('bulkModal').addEventListener('click', function(e) {
        if (e.target === this) closeBulkModal();
    });

    // ============================================
    // TOGGLE TIME FIELDS
    // ============================================
    function toggleTimeFields() {
        const status = document.getElementById('attendanceStatus').value;
        const timeFields = document.getElementById('timeFields');
        
        if (status === 'absent' || status === 'leave' || status === 'holiday') {
            timeFields.style.display = 'none';
        } else {
            timeFields.style.display = 'grid';
        }
    }

    // ============================================
    // EDIT ATTENDANCE
    // ============================================
    function editAttendance(id) {
        alert('Edit attendance record: ' + id);
        // You can implement edit modal here
    }

    // ============================================
    // KEYBOARD SHORTCUTS
    // ============================================
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeMarkModal();
            closeBulkModal();
        }
    });

    // ============================================
    // INITIALIZE
    // ============================================
    document.addEventListener('DOMContentLoaded', function() {
        toggleTimeFields();
    });
</script>
@endpush
@endsection