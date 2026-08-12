@extends('layouts.app')

@section('title', 'Employee: ' . $employee->full_name)

@section('content')
<div class="space-y-6">
    
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $employee->full_name }}</h1>
            <p class="text-sm text-gray-500">Employee Details</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('hrm.employees.edit', $employee->id) }}" 
               class="bg-yellow-600 hover:bg-yellow-700 text-white font-medium px-4 py-2 rounded-lg transition">
                <i class="fas fa-edit mr-2"></i> Edit
            </a>
            <form action="{{ route('hrm.employees.delete', $employee->id) }}" method="POST" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" onclick="return confirm('Delete this employee?')" 
                        class="bg-red-600 hover:bg-red-700 text-white font-medium px-4 py-2 rounded-lg transition">
                    <i class="fas fa-trash mr-2"></i> Delete
                </button>
            </form>
            <a href="{{ route('hrm.employees') }}" 
               class="bg-gray-500 hover:bg-gray-600 text-white font-medium px-4 py-2 rounded-lg transition">
                <i class="fas fa-arrow-left mr-2"></i> Back
            </a>
        </div>
    </div>

    <!-- Status Badge -->
    <div class="flex flex-wrap gap-2">
        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
            @if($employee->is_active) bg-green-100 text-green-800
            @else bg-red-100 text-red-800
            @endif">
            <i class="fas fa-circle text-xs mr-1.5"></i> {{ $employee->is_active ? 'Active' : 'Inactive' }}
        </span>
        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
            <i class="fas fa-briefcase text-xs mr-1.5"></i> {{ $employee->designation }}
        </span>
        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800">
            <i class="fas fa-building text-xs mr-1.5"></i> {{ ucfirst($employee->department) }}
        </span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left Column: Personal Info -->
        <div class="lg:col-span-1 space-y-6">
            
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center space-x-4">
                    <div class="w-20 h-20 rounded-full bg-gradient-to-r from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold text-2xl">
                        {{ substr($employee->first_name, 0, 1) }}{{ substr($employee->last_name, 0, 1) }}
                    </div>
                    <div>
                        <h2 class="text-xl font-bold">{{ $employee->full_name }}</h2>
                        <p class="text-sm text-gray-500">{{ $employee->employee_code }}</p>
                    </div>
                </div>

                <div class="mt-4 space-y-2 text-sm">
                    <p><i class="fas fa-envelope w-5 text-gray-400"></i> {{ $employee->email }}</p>
                    @if($employee->phone)
                        <p><i class="fas fa-phone w-5 text-gray-400"></i> {{ $employee->phone }}</p>
                    @endif
                    @if($employee->address)
                        <p><i class="fas fa-map-marker-alt w-5 text-gray-400"></i> {{ $employee->address }}</p>
                    @endif
                    @if($employee->cnic)
                        <p><i class="fas fa-id-card w-5 text-gray-400"></i> CNIC: {{ $employee->cnic }}</p>
                    @endif
                    <p><i class="fas fa-calendar-alt w-5 text-gray-400"></i> Joining: {{ $employee->joining_date->format('d-m-Y') }}</p>
                </div>
            </div>

            <!-- Bank Details -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">
                    <i class="fas fa-university mr-2 text-gray-400"></i> Bank Details
                </h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Bank Name</span>
                        <span class="font-semibold">{{ $employee->bank_name ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Account Number</span>
                        <span class="font-semibold">{{ $employee->bank_account ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>

            <!-- Statistics -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">📊 Statistics</h3>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Total Salary</span>
                        <span class="font-semibold text-blue-600">Rs. {{ number_format($stats['total_salary'], 0) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Total Advances</span>
                        <span class="font-semibold text-red-600">Rs. {{ number_format($stats['total_advance'], 0) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Total Attendance</span>
                        <span class="font-semibold">{{ $stats['total_attendance'] }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Present Days</span>
                        <span class="font-semibold text-green-600">{{ $stats['present'] }}</span>
                    </div>
                </div>
            </div>

            <!-- Notes -->
            @if($employee->notes)
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-sticky-note mr-2 text-gray-400"></i> Notes
                </h3>
                <p class="text-sm text-gray-600">{{ $employee->notes }}</p>
            </div>
            @endif
        </div>

        <!-- Right Column: Salary & Activity -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Salary Breakdown -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">💰 Salary Breakdown</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <p class="text-xs text-gray-500">Basic Salary</p>
                        <p class="text-xl font-bold">Rs. {{ number_format($employee->basic_salary, 0) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Allowances</p>
                        <p class="text-xl font-bold text-green-600">Rs. {{ number_format($employee->allowances, 0) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Deductions</p>
                        <p class="text-xl font-bold text-red-600">Rs. {{ number_format($employee->deductions, 0) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Net Salary</p>
                        <p class="text-xl font-bold text-blue-600">Rs. {{ number_format($employee->net_salary, 0) }}</p>
                    </div>
                </div>
            </div>

            <!-- Recent Salaries -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-sm font-semibold text-gray-700">
                        <i class="fas fa-list mr-2 text-gray-400"></i> Recent Salaries
                    </h3>
                    <a href="{{ route('hrm.salaries') }}" class="text-xs text-blue-600 hover:underline">View All</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Period</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Basic</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Net</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($employee->salaries as $salary)
                            <tr>
                                <td class="px-6 py-3 text-sm">{{ date('F', mktime(0,0,0,$salary->month,1)) }} {{ $salary->year }}</td>
                                <td class="px-6 py-3 text-right">Rs. {{ number_format($salary->basic_salary, 0) }}</td>
                                <td class="px-6 py-3 text-right font-semibold">Rs. {{ number_format($salary->net_salary, 0) }}</td>
                                <td class="px-6 py-3 text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @if($salary->status === 'paid') bg-green-100 text-green-800
                                        @else bg-yellow-100 text-yellow-800
                                        @endif">
                                        {{ ucfirst($salary->status) }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center text-gray-500">No salaries found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <form action="{{ route('hrm.salaries.process') }}" method="POST">
                    @csrf
                    <input type="hidden" name="employee_id" value="{{ $employee->id }}">
                    <input type="hidden" name="month" value="{{ date('m') }}">
                    <input type="hidden" name="year" value="{{ date('Y') }}">
                    <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-3 px-4 rounded-lg transition">
                        <i class="fas fa-cogs mr-2"></i> Process Salary
                    </button>
                </form>
                <button onclick="openAdvanceModal({{ $employee->id }})" 
                        class="w-full bg-yellow-600 hover:bg-yellow-700 text-white font-medium py-3 px-4 rounded-lg transition">
                    <i class="fas fa-hand-holding-usd mr-2"></i> Advance Request
                </button>
                <button onclick="openAttendanceModal({{ $employee->id }})" 
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-4 rounded-lg transition">
                    <i class="fas fa-calendar-check mr-2"></i> Mark Attendance
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function openAdvanceModal(employeeId) {
        alert('Advance request for employee: ' + employeeId);
    }

    function openAttendanceModal(employeeId) {
        alert('Mark attendance for employee: ' + employeeId);
    }
</script>
@endpush
@endsection