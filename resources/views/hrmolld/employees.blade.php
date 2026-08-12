@extends('layouts.app')

@section('title', 'Employees')

@section('content')
<div class="space-y-6">
    
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">👥 Employees</h1>
            <p class="text-sm text-gray-500">Manage your employees</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <button onclick="openCreateModal()" 
                    class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-lg transition">
                <i class="fas fa-plus mr-2"></i> Add Employee
            </button>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-blue-500">
            <p class="text-xs text-gray-500">Total Employees</p>
            <p class="text-2xl font-bold">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-green-500">
            <p class="text-xs text-gray-500">Active</p>
            <p class="text-2xl font-bold text-green-600">{{ $stats['active'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-yellow-500">
            <p class="text-xs text-gray-500">Total Salary</p>
            <p class="text-2xl font-bold text-yellow-600">Rs. {{ number_format($stats['total_salary'], 0) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-red-500">
            <p class="text-xs text-gray-500">Total Advances</p>
            <p class="text-2xl font-bold text-red-600">Rs. {{ number_format($stats['total_advance'], 0) }}</p>
        </div>
    </div>

    <!-- Search -->
    <div class="bg-white rounded-lg shadow p-4">
        <form method="GET" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Search by name, email, designation..."
                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div>
                <select name="department" class="rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">All Departments</option>
                    <option value="sales" {{ request('department') == 'sales' ? 'selected' : '' }}>Sales</option>
                    <option value="operations" {{ request('department') == 'operations' ? 'selected' : '' }}>Operations</option>
                    <option value="finance" {{ request('department') == 'finance' ? 'selected' : '' }}>Finance</option>
                    <option value="hr" {{ request('department') == 'hr' ? 'selected' : '' }}>HR</option>
                </select>
            </div>
            <div>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-lg transition">
                    <i class="fas fa-search mr-2"></i> Search
                </button>
                <a href="{{ route('hrm.employees') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium px-4 py-2 rounded-lg transition">
                    <i class="fas fa-times mr-2"></i> Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Employees Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Code</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Designation</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Department</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Salary</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($employees as $employee)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-3 text-sm font-mono">{{ $employee->employee_code }}</td>
                        <td class="px-6 py-3">
                            <span class="font-medium">{{ $employee->full_name }}</span>
                            <span class="text-xs text-gray-400 block">{{ $employee->email }}</span>
                        </td>
                        <td class="px-6 py-3 text-sm">{{ $employee->designation }}</td>
                        <td class="px-6 py-3 text-sm">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ ucfirst($employee->department) }}
                            </span>
                        </td>
                        <td class="px-6 py-3 text-right font-semibold">Rs. {{ number_format($employee->net_salary, 0) }}</td>
                        <td class="px-6 py-3 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($employee->is_active) bg-green-100 text-green-800
                                @else bg-red-100 text-red-800
                                @endif">
                                {{ $employee->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <!-- <td class="px-6 py-3 text-center">
                            <div class="flex items-center justify-center space-x-2">
                                <button onclick="viewEmployee({{ $employee->id }})" 
                                        class="text-blue-600 hover:text-blue-800 transition" title="View">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button onclick="editEmployee({{ $employee->id }})" 
                                        class="text-yellow-600 hover:text-yellow-800 transition" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <a href="{{ route('hrm.salaries.process', $employee->id) }}" 
                                   class="text-green-600 hover:text-green-800 transition" title="Process Salary">
                                    <i class="fas fa-money-bill-wave"></i>
                                </a>
                            </div>
                        </td> -->

                        <!-- ✅ FIXED: Use form instead of link -->
						<td class="px-6 py-3 text-center">
						    <div class="flex items-center justify-center space-x-2">
						        <!-- ✅ View Button -->
						        <a href="{{ route('hrm.employees.view', $employee->id) }}" 
						           class="text-blue-600 hover:text-blue-800 transition" title="View">
						            <i class="fas fa-eye"></i>
						        </a>
						        
						        <!-- ✅ Edit Button -->
						        <a href="{{ route('hrm.employees.edit', $employee->id) }}" 
						           class="text-yellow-600 hover:text-yellow-800 transition" title="Edit">
						            <i class="fas fa-edit"></i>
						        </a>
						        
						        <!-- ✅ Process Salary Button -->
						        <form action="{{ route('hrm.salaries.process') }}" method="POST" class="inline">
						            @csrf
						            <input type="hidden" name="employee_id" value="{{ $employee->id }}">
						            <input type="hidden" name="month" value="{{ date('m') }}">
						            <input type="hidden" name="year" value="{{ date('Y') }}">
						            <button type="submit" 
						                    class="text-green-600 hover:text-green-800 transition" 
						                    title="Process Salary"
						                    onclick="return confirm('Process salary for {{ $employee->full_name }}?')">
						                <i class="fas fa-money-bill-wave"></i>
						            </button>
						        </form>
						        
						        <!-- ✅ Delete Button -->
						        <form action="{{ route('hrm.employees.delete', $employee->id) }}" method="POST" class="inline">
						            @csrf
						            @method('DELETE')
						            <button type="submit" onclick="return confirm('Delete this employee?')" 
						                    class="text-red-600 hover:text-red-800 transition" title="Delete">
						                <i class="fas fa-trash"></i>
						            </button>
						        </form>
						    </div>
						</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                            <i class="fas fa-inbox text-3xl mb-2 text-gray-300 block"></i>
                            No employees found.
                            <button onclick="openCreateModal()" class="text-blue-600 hover:underline ml-1">Add your first employee</button>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $employees->links() }}
        </div>
    </div>
</div>

<!-- ============================================
     CREATE EMPLOYEE MODAL
     ============================================ -->
<div id="createModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="relative top-10 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-lg bg-white max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center pb-3 border-b sticky top-0 bg-white">
            <h3 class="text-lg font-semibold text-gray-900">
                <i class="fas fa-user-plus text-blue-600 mr-2"></i> Add Employee
            </h3>
            <button onclick="closeCreateModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <form action="{{ route('hrm.employees.store') }}" method="POST" id="employeeForm" class="mt-4 space-y-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">First Name *</label>
                    <input type="text" name="first_name" required class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Last Name *</label>
                    <input type="text" name="last_name" required class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                    <input type="email" name="email" required class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                    <input type="text" name="phone" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">CNIC</label>
                    <input type="text" name="cnic" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Designation *</label>
                    <input type="text" name="designation" required class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Department *</label>
                    <select name="department" required class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Select Department</option>
                        <option value="sales">Sales</option>
                        <option value="operations">Operations</option>
                        <option value="finance">Finance</option>
                        <option value="hr">HR</option>
                        <option value="admin">Admin</option>
                        <option value="technical">Technical</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Joining Date *</label>
                    <input type="date" name="joining_date" required class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Basic Salary *</label>
                    <input type="number" step="0.01" name="basic_salary" required class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Allowances</label>
                    <input type="number" step="0.01" name="allowances" value="0" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Deductions</label>
                    <input type="number" step="0.01" name="deductions" value="0" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Bank Name</label>
                    <input type="text" name="bank_name" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Bank Account</label>
                    <input type="text" name="bank_account" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                    <textarea name="address" rows="2" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea name="notes" rows="2" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                </div>
                <div class="md:col-span-2">
                    <div class="flex items-center">
                        <input type="checkbox" name="is_active" value="1" checked class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <label class="ml-2 text-sm text-gray-700">Active</label>
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-3 pt-4 border-t">
                <button type="button" onclick="closeCreateModal()" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-4 rounded-lg transition">
                    Cancel
                </button>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition">
                    <i class="fas fa-save mr-2"></i> Save Employee
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openCreateModal() {
        document.getElementById('createModal').classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function closeCreateModal() {
        document.getElementById('createModal').classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    document.getElementById('createModal').addEventListener('click', function(e) {
        if (e.target === this) closeCreateModal();
    });

    function viewEmployee(id) {
        alert('View employee: ' + id);
    }

    function editEmployee(id) {
        alert('Edit employee: ' + id);
    }


    // ============================================
// ✅ FIXED: CREATE EMPLOYEE FORM SUBMIT (AJAX)
// ============================================
document.getElementById('employeeForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Saving...';
    submitBtn.disabled = true;
    
    fetch('{{ route("hrm.employees.store") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ ' + data.message);
            closeCreateModal();
            location.reload();
        } else {
            let errorMsg = '❌ Error:\n';
            if (data.errors) {
                Object.values(data.errors).forEach(err => {
                    errorMsg += err.join('\n') + '\n';
                });
            } else {
                errorMsg += data.message;
            }
            alert(errorMsg);
        }
    })
    .catch(error => {
        alert('❌ Error: ' + error.message);
    })
    .finally(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
});
</script>
@endpush
@endsection