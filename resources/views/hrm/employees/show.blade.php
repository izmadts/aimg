@extends('layouts.app')

@section('title', 'Employee: ' . $employee->full_name)

@section('content')
<div class="space-y-6">
    
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $employee->full_name }}</h1>
            <p class="text-sm text-gray-500">Employee Details</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('hrm.employees.edit', $employee->id) }}" class="bg-yellow-600 hover:bg-yellow-700 text-white font-medium px-4 py-2 rounded-lg transition">
                <i class="fas fa-edit mr-2"></i> Edit
            </a>
            <a href="{{ route('hrm.employees') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-medium px-4 py-2 rounded-lg transition">
                <i class="fas fa-arrow-left mr-2"></i> Back
            </a>
        </div>
    </div>

    <!-- Status Badges -->
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
        
        <!-- Left Column -->
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
                        <span class="text-sm text-gray-500">Present Days</span>
                        <span class="font-semibold text-green-600">{{ $stats['present'] }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Absent Days</span>
                        <span class="font-semibold text-red-600">{{ $stats['absent'] }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Total Leaves</span>
                        <span class="font-semibold text-yellow-600">{{ $stats['total_leaves'] }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column -->
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

            <!-- Quick Actions -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <form action="{{ route('hrm.salaries.process') }}" method="POST" class="w-full">
                    @csrf
                    <input type="hidden" name="employee_id" value="{{ $employee->id }}">
                    <input type="hidden" name="month" value="{{ date('m') }}">
                    <input type="hidden" name="year" value="{{ date('Y') }}">
                    <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg transition">
                        <i class="fas fa-cogs mr-2"></i> Process Salary
                    </button>
                </form>
                <a href="{{ route('hrm.attendance') }}?employee_id={{ $employee->id }}" 
                   class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition text-center">
                    <i class="fas fa-calendar-check mr-2"></i> View Attendance
                </a>
                <a href="{{ route('hrm.advances.create') }}?employee_id={{ $employee->id }}" 
                   class="w-full bg-yellow-600 hover:bg-yellow-700 text-white font-medium py-2 px-4 rounded-lg transition text-center">
                    <i class="fas fa-hand-holding-usd mr-2"></i> Advance Request
                </a>
            </div>
        </div>
    </div>
</div>
@endsection