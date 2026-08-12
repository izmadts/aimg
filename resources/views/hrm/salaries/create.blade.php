@extends('layouts.app')

@section('title', 'Process Salary')

@section('content')
<div class="space-y-6">
    
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">💰 Process Salary</h1>
            <p class="text-sm text-gray-500">Process salary for an employee</p>
        </div>
        <a href="{{ route('hrm.salaries') }}" class="text-gray-600 hover:text-gray-900">
            <i class="fas fa-arrow-left mr-2"></i> Back
        </a>
    </div>

    <div class="bg-white rounded-lg shadow p-6 max-w-2xl">
        <form action="{{ route('hrm.salaries.process') }}" method="POST">
            @csrf

            <div class="grid grid-cols-1 gap-4">
                
                <!-- Employee -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Select Employee *</label>
                    <select name="employee_id" id="employee_id" required
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            onchange="updateEmployeeSalary()">
                        <option value="">Select Employee</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" 
                                    data-salary="{{ $emp->net_salary }}"
                                    data-name="{{ $emp->full_name }}"
                                    {{ old('employee_id') == $emp->id ? 'selected' : '' }}>
                                {{ $emp->full_name }} ({{ $emp->employee_code }}) - Rs. {{ number_format($emp->net_salary, 0) }}
                            </option>
                        @endforeach
                    </select>
                    @error('employee_id')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Employee Info -->
                <div id="employeeSalaryInfo" class="p-3 bg-gray-50 rounded-lg border border-gray-200 text-sm">
                    <p class="text-gray-500">Select an employee to view salary details</p>
                </div>

                <!-- Month -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Month *</label>
                    <select name="month" required
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ old('month', $currentMonth) == $m ? 'selected' : '' }}>
                                {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                            </option>
                        @endfor
                    </select>
                    @error('month')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Year -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Year *</label>
                    <select name="year" required
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @for($y = date('Y') - 2; $y <= date('Y') + 1; $y++)
                            <option value="{{ $y }}" {{ old('year', $currentYear) == $y ? 'selected' : '' }}>
                                {{ $y }}
                            </option>
                        @endfor
                    </select>
                    @error('year')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Salary Breakdown Preview -->
            <div id="salaryBreakdown" class="mt-6 p-4 bg-blue-50 rounded-lg border border-blue-200 hidden">
                <h4 class="text-sm font-semibold text-blue-700 mb-3">💰 Salary Breakdown</h4>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                    <div>
                        <span class="text-gray-500">Basic Salary:</span>
                        <span class="font-semibold text-gray-900" id="previewBasic">Rs. 0.00</span>
                    </div>
                    <div>
                        <span class="text-gray-500">Allowances:</span>
                        <span class="font-semibold text-green-600" id="previewAllowances">Rs. 0.00</span>
                    </div>
                    <div>
                        <span class="text-gray-500">Deductions:</span>
                        <span class="font-semibold text-red-600" id="previewDeductions">Rs. 0.00</span>
                    </div>
                    <div>
                        <span class="text-gray-500">Net Salary:</span>
                        <span class="font-semibold text-blue-600" id="previewNet">Rs. 0.00</span>
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-4 mt-6 pt-6 border-t border-gray-200">
                <a href="{{ route('hrm.salaries') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-6 rounded-lg transition">
                    Cancel
                </a>
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-6 rounded-lg transition">
                    <i class="fas fa-cogs mr-2"></i> Process Salary
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    // ============================================
    // UPDATE EMPLOYEE SALARY INFO
    // ============================================
    function updateEmployeeSalary() {
        const select = document.getElementById('employee_id');
        const infoDiv = document.getElementById('employeeSalaryInfo');
        const breakdownDiv = document.getElementById('salaryBreakdown');
        const selectedOption = select.options[select.selectedIndex];
        
        if (selectedOption && selectedOption.value) {
            const name = selectedOption.dataset.name || 'N/A';
            const salary = parseFloat(selectedOption.dataset.salary) || 0;
            
            infoDiv.innerHTML = `
                <p><strong>Name:</strong> ${name}</p>
                <p><strong>Net Salary:</strong> Rs. ${salary.toFixed(2)}</p>
                <p class="text-xs text-gray-400 mt-1">💡 This is the calculated net salary after all deductions</p>
            `;
            
            // Show breakdown preview
            breakdownDiv.classList.remove('hidden');
            document.getElementById('previewBasic').textContent = 'Rs. ' + (salary * 0.6).toFixed(2);
            document.getElementById('previewAllowances').textContent = 'Rs. ' + (salary * 0.2).toFixed(2);
            document.getElementById('previewDeductions').textContent = 'Rs. ' + (salary * 0.05).toFixed(2);
            document.getElementById('previewNet').textContent = 'Rs. ' + salary.toFixed(2);
            
        } else {
            infoDiv.innerHTML = '<p class="text-gray-500">Select an employee to view salary details</p>';
            breakdownDiv.classList.add('hidden');
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        updateEmployeeSalary();
    });
</script>
@endpush
@endsection