@extends('layouts.app')

@section('title', 'New Advance Request')

@section('content')
<div class="space-y-6">
    
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">💳 New Advance Request</h1>
            <p class="text-sm text-gray-500">Create a new employee advance request</p>
        </div>
        <a href="{{ route('hrm.advances') }}" class="text-gray-600 hover:text-gray-900">
            <i class="fas fa-arrow-left mr-2"></i> Back
        </a>
    </div>

    <div class="bg-white rounded-lg shadow p-6 max-w-3xl">
        <form action="{{ route('hrm.advances.store') }}" method="POST">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                
                <!-- Employee -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Employee *</label>
                    <select name="employee_id" id="employee_id" required
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            onchange="updateEmployeeInfo()">
                        <option value="">Select Employee</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" 
                                    data-salary="{{ $emp->net_salary }}"
                                    data-name="{{ $emp->full_name }}"
                                    {{ old('employee_id') == $emp->id ? 'selected' : '' }}>
                                {{ $emp->full_name }} ({{ $emp->employee_code }})
                            </option>
                        @endforeach
                    </select>
                    @error('employee_id')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Employee Info Display -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Employee Info</label>
                    <div id="employeeInfo" class="p-3 bg-gray-50 rounded-lg border border-gray-200 text-sm">
                        <p class="text-gray-500">Select an employee to view details</p>
                    </div>
                </div>

                <!-- Amount -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Amount (Rs.) *</label>
                    <input type="number" step="0.01" name="amount" id="amount" 
                           value="{{ old('amount') }}" required
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                           oninput="calculateInstallments()">
                    @error('amount')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-400 mt-1" id="maxAmountHint">Max amount: Depends on employee salary</p>
                </div>

                <!-- Advance Date -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Advance Date *</label>
                    <input type="date" name="advance_date" id="advance_date" 
                           value="{{ old('advance_date', date('Y-m-d')) }}" required
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('advance_date')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Deduction Start Month -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Deduction Start Month *</label>
                    <input type="date" name="deduction_start_month" id="deduction_start_month" 
                           value="{{ old('deduction_start_month', date('Y-m-d')) }}" required
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('deduction_start_month')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Deduction Installments -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Number of Installments *</label>
                    <input type="number" name="deduction_installments" id="deduction_installments" 
                           value="{{ old('deduction_installments', 6) }}" required
                           min="1" max="12"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                           oninput="calculateInstallments()">
                    @error('deduction_installments')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-400 mt-1">Between 1 to 12 months</p>
                </div>

                <!-- Per Month Deduction (Auto-calculated) -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Per Month Deduction (Auto)</label>
                    <input type="text" id="perMonthDeduction" readonly
                           class="w-full rounded-lg bg-gray-100 border-gray-300 shadow-sm font-semibold text-blue-600">
                    <p class="text-xs text-gray-400 mt-1">Auto-calculated: Amount / Installments</p>
                </div>

                <!-- Purpose -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Purpose *</label>
                    <textarea name="purpose" id="purpose" rows="2" required
                              class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('purpose') }}</textarea>
                    @error('purpose')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Notes -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea name="notes" rows="2" 
                              class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('notes') }}</textarea>
                    @error('notes')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Summary Box -->
            <div class="mt-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
                <h4 class="text-sm font-semibold text-blue-700 mb-3">📊 Advance Summary</h4>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                    <div>
                        <span class="text-gray-500">Employee:</span>
                        <span class="font-semibold text-gray-900" id="summaryEmployee">Not Selected</span>
                    </div>
                    <div>
                        <span class="text-gray-500">Amount:</span>
                        <span class="font-semibold text-blue-600" id="summaryAmount">Rs. 0.00</span>
                    </div>
                    <div>
                        <span class="text-gray-500">Installments:</span>
                        <span class="font-semibold text-purple-600" id="summaryInstallments">0</span>
                    </div>
                    <div>
                        <span class="text-gray-500">Per Month:</span>
                        <span class="font-semibold text-green-600" id="summaryPerMonth">Rs. 0.00</span>
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-4 mt-6 pt-6 border-t border-gray-200">
                <a href="{{ route('hrm.advances') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-6 rounded-lg transition">
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
    // UPDATE EMPLOYEE INFO
    // ============================================
    function updateEmployeeInfo() {
        const select = document.getElementById('employee_id');
        const infoDiv = document.getElementById('employeeInfo');
        const selectedOption = select.options[select.selectedIndex];
        
        if (selectedOption && selectedOption.value) {
            const name = selectedOption.dataset.name || 'N/A';
            const salary = parseFloat(selectedOption.dataset.salary) || 0;
            
            infoDiv.innerHTML = `
                <p><strong>Name:</strong> ${name}</p>
                <p><strong>Monthly Salary:</strong> Rs. ${salary.toFixed(2)}</p>
                <p><strong>Max Advance:</strong> Rs. ${(salary * 0.5).toFixed(2)} (50% of salary)</p>
                <p class="text-xs text-gray-400 mt-1">💡 Maximum advance is 50% of monthly salary</p>
            `;
            
            // Update max amount hint
            const maxAmount = salary * 0.5;
            document.getElementById('maxAmountHint').textContent = `Max amount: Rs. ${maxAmount.toFixed(2)} (50% of salary)`;
            document.getElementById('amount').max = maxAmount;
            
            // Update summary
            updateSummary();
        } else {
            infoDiv.innerHTML = '<p class="text-gray-500">Select an employee to view details</p>';
            document.getElementById('maxAmountHint').textContent = 'Max amount: Depends on employee salary';
        }
    }

    // ============================================
    // CALCULATE INSTALLMENTS
    // ============================================
    function calculateInstallments() {
        const amount = parseFloat(document.getElementById('amount').value) || 0;
        const installments = parseInt(document.getElementById('deduction_installments').value) || 1;
        
        const perMonth = installments > 0 ? amount / installments : 0;
        document.getElementById('perMonthDeduction').value = 'Rs. ' + perMonth.toFixed(2);
        
        updateSummary();
    }

    // ============================================
    // UPDATE SUMMARY
    // ============================================
    function updateSummary() {
        const select = document.getElementById('employee_id');
        const selectedOption = select.options[select.selectedIndex];
        const employeeName = selectedOption ? (selectedOption.dataset.name || 'Not Selected') : 'Not Selected';
        
        const amount = parseFloat(document.getElementById('amount').value) || 0;
        const installments = parseInt(document.getElementById('deduction_installments').value) || 0;
        const perMonth = installments > 0 ? amount / installments : 0;
        
        document.getElementById('summaryEmployee').textContent = employeeName;
        document.getElementById('summaryAmount').textContent = 'Rs. ' + amount.toFixed(2);
        document.getElementById('summaryInstallments').textContent = installments;
        document.getElementById('summaryPerMonth').textContent = 'Rs. ' + perMonth.toFixed(2);
    }

    // ============================================
    // EVENT LISTENERS
    // ============================================
    document.addEventListener('DOMContentLoaded', function() {
        // Initial calculations
        updateEmployeeInfo();
        calculateInstallments();
        
        // Auto-deduct amount validation
        document.getElementById('amount').addEventListener('input', function() {
            const max = parseFloat(this.max) || 0;
            const value = parseFloat(this.value) || 0;
            
            if (value > max && max > 0) {
                this.style.borderColor = '#ef4444';
                this.style.backgroundColor = '#fef2f2';
                document.getElementById('maxAmountHint').className = 'text-xs text-red-600 mt-1';
            } else {
                this.style.borderColor = '';
                this.style.backgroundColor = '';
                document.getElementById('maxAmountHint').className = 'text-xs text-gray-400 mt-1';
            }
            calculateInstallments();
        });
    });
</script>
@endpush
@endsection