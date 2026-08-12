@extends('layouts.app')

@section('title', 'New Leave Request')

@section('content')
<div class="space-y-6">
    
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">🏖️ New Leave Request</h1>
            <p class="text-sm text-gray-500">Create a new leave request</p>
        </div>
        <a href="{{ route('hrm.leaves') }}" class="text-gray-600 hover:text-gray-900">
            <i class="fas fa-arrow-left mr-2"></i> Back
        </a>
    </div>

    <div class="bg-white rounded-lg shadow p-6 max-w-3xl">
        <form action="{{ route('hrm.leaves.store') }}" method="POST">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                
                <!-- Employee -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Employee *</label>
                    <select name="employee_id" required
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Select Employee</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" {{ old('employee_id') == $emp->id ? 'selected' : '' }}>
                                {{ $emp->full_name }} ({{ $emp->employee_code }})
                            </option>
                        @endforeach
                    </select>
                    @error('employee_id')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Leave Type -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Leave Type *</label>
                    <select name="leave_type" required
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="annual" {{ old('leave_type') == 'annual' ? 'selected' : '' }}>📅 Annual Leave</option>
                        <option value="sick" {{ old('leave_type') == 'sick' ? 'selected' : '' }}>🤒 Sick Leave</option>
                        <option value="casual" {{ old('leave_type') == 'casual' ? 'selected' : '' }}>🏖️ Casual Leave</option>
                        <option value="maternity" {{ old('leave_type') == 'maternity' ? 'selected' : '' }}>👶 Maternity Leave</option>
                        <option value="paternity" {{ old('leave_type') == 'paternity' ? 'selected' : '' }}>👨‍👦 Paternity Leave</option>
                        <option value="unpaid" {{ old('leave_type') == 'unpaid' ? 'selected' : '' }}>💰 Unpaid Leave</option>
                        <option value="other" {{ old('leave_type') == 'other' ? 'selected' : '' }}>📋 Other Leave</option>
                    </select>
                    @error('leave_type')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Start Date -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Start Date *</label>
                    <input type="date" name="start_date" id="start_date" 
                           value="{{ old('start_date', date('Y-m-d')) }}" required
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                           onchange="calculateDays()">
                    @error('start_date')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- End Date -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">End Date *</label>
                    <input type="date" name="end_date" id="end_date" 
                           value="{{ old('end_date', date('Y-m-d')) }}" required
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                           onchange="calculateDays()">
                    @error('end_date')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Total Days (Auto) -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Total Days (Auto)</label>
                    <input type="text" id="totalDays" readonly
                           class="w-full rounded-lg bg-gray-100 border-gray-300 shadow-sm font-semibold text-blue-600">
                    <p class="text-xs text-gray-400 mt-1">Auto-calculated from dates</p>
                </div>

                <!-- Reason -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Reason *</label>
                    <textarea name="reason" rows="2" required
                              class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('reason') }}</textarea>
                    @error('reason')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Notes -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Additional Notes</label>
                    <textarea name="notes" rows="2" 
                              class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('notes') }}</textarea>
                    @error('notes')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Summary Box -->
            <div class="mt-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
                <h4 class="text-sm font-semibold text-blue-700 mb-3">📋 Leave Summary</h4>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                    <div>
                        <span class="text-gray-500">Leave Type:</span>
                        <span class="font-semibold text-gray-900" id="summaryType">Not Selected</span>
                    </div>
                    <div>
                        <span class="text-gray-500">Start Date:</span>
                        <span class="font-semibold" id="summaryStart">-</span>
                    </div>
                    <div>
                        <span class="text-gray-500">End Date:</span>
                        <span class="font-semibold" id="summaryEnd">-</span>
                    </div>
                    <div>
                        <span class="text-gray-500">Total Days:</span>
                        <span class="font-semibold text-blue-600" id="summaryDays">0</span>
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-4 mt-6 pt-6 border-t border-gray-200">
                <a href="{{ route('hrm.leaves') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-6 rounded-lg transition">
                    Cancel
                </a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-lg transition">
                    <i class="fas fa-paper-plane mr-2"></i> Submit Request
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    // ============================================
    // CALCULATE DAYS
    // ============================================
    function calculateDays() {
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;
        
        if (startDate && endDate) {
            const start = new Date(startDate);
            const end = new Date(endDate);
            
            if (end >= start) {
                const diffTime = Math.abs(end - start);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
                
                document.getElementById('totalDays').value = diffDays + ' days';
                document.getElementById('summaryDays').textContent = diffDays;
            } else {
                document.getElementById('totalDays').value = 'Invalid date range';
                document.getElementById('summaryDays').textContent = '0';
            }
        }
    }

    // ============================================
    // UPDATE SUMMARY
    // ============================================
    document.addEventListener('DOMContentLoaded', function() {
        // Update leave type summary
        document.getElementById('leave_type')?.addEventListener('change', function() {
            const labels = {
                'annual': '📅 Annual Leave',
                'sick': '🤒 Sick Leave',
                'casual': '🏖️ Casual Leave',
                'maternity': '👶 Maternity Leave',
                'paternity': '👨‍👦 Paternity Leave',
                'unpaid': '💰 Unpaid Leave',
                'other': '📋 Other Leave'
            };
            document.getElementById('summaryType').textContent = labels[this.value] || this.value;
        });

        // Update date summaries
        document.getElementById('start_date')?.addEventListener('change', function() {
            document.getElementById('summaryStart').textContent = this.value ? new Date(this.value).toLocaleDateString('en-GB') : '-';
            calculateDays();
        });

        document.getElementById('end_date')?.addEventListener('change', function() {
            document.getElementById('summaryEnd').textContent = this.value ? new Date(this.value).toLocaleDateString('en-GB') : '-';
            calculateDays();
        });

        // Initial calculation
        calculateDays();
        
        // Set initial summary
        const leaveType = document.getElementById('leave_type');
        if (leaveType) {
            const labels = {
                'annual': '📅 Annual Leave',
                'sick': '🤒 Sick Leave',
                'casual': '🏖️ Casual Leave',
                'maternity': '👶 Maternity Leave',
                'paternity': '👨‍👦 Paternity Leave',
                'unpaid': '💰 Unpaid Leave',
                'other': '📋 Other Leave'
            };
            document.getElementById('summaryType').textContent = labels[leaveType.value] || leaveType.value;
        }
    });
</script>
@endpush
@endsection