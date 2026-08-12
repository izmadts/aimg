@extends('layouts.app')

@section('title', 'Salaries')

@section('content')
<div class="space-y-6">
    
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">💰 Salaries</h1>
            <p class="text-sm text-gray-500">Manage employee salaries</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('hrm.salaries.create') }}" class="bg-green-600 hover:bg-green-700 text-white font-medium px-4 py-2 rounded-lg transition">
                <i class="fas fa-plus mr-2"></i> Process Salary
            </a>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-blue-500">
            <p class="text-xs text-gray-500">Total Salaries</p>
            <p class="text-2xl font-bold">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-yellow-500">
            <p class="text-xs text-gray-500">Pending</p>
            <p class="text-2xl font-bold text-yellow-600">{{ $stats['pending'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-green-500">
            <p class="text-xs text-gray-500">Paid</p>
            <p class="text-2xl font-bold text-green-600">{{ $stats['paid'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-purple-500">
            <p class="text-xs text-gray-500">Total Amount</p>
            <p class="text-2xl font-bold text-purple-600">Rs. {{ number_format($stats['total_amount'], 0) }}</p>
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
                <select name="status" class="rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                </select>
            </div>
            <div>
                <select name="month" class="rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">All Months</option>
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
                            {{ date('F', mktime(0,0,0,$m,1)) }}
                        </option>
                    @endfor
                </select>
            </div>
            <div>
                <select name="year" class="rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">All Years</option>
                    @for($y = date('Y') - 2; $y <= date('Y'); $y++)
                        <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>
                            {{ $y }}
                        </option>
                    @endfor
                </select>
            </div>
            <div>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-lg transition">
                    <i class="fas fa-search mr-2"></i> Filter
                </button>
                <a href="{{ route('hrm.salaries') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium px-4 py-2 rounded-lg transition">
                    <i class="fas fa-times mr-2"></i> Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Salaries Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employee</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Month/Year</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Basic</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Allowances</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Deductions</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Net</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($salaries as $salary)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-3 text-sm">{{ $salary->employee->full_name ?? 'N/A' }}</td>
                        <td class="px-6 py-3 text-sm">{{ $salary->month_name }} {{ $salary->year }}</td>
                        <td class="px-6 py-3 text-right">Rs. {{ number_format($salary->basic_salary, 0) }}</td>
                        <td class="px-6 py-3 text-right">Rs. {{ number_format($salary->allowances, 0) }}</td>
                        <td class="px-6 py-3 text-right">Rs. {{ number_format($salary->deductions, 0) }}</td>
                        <td class="px-6 py-3 text-right font-bold">Rs. {{ number_format($salary->net_salary, 0) }}</td>
                        <td class="px-6 py-3 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($salary->status === 'paid') bg-green-100 text-green-800
                                @else bg-yellow-100 text-yellow-800
                                @endif">
                                {{ ucfirst($salary->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-3 text-center">
                            @if($salary->status === 'pending')
                                <button onclick="paySalary({{ $salary->id }})" 
                                        class="text-green-600 hover:text-green-800 transition" title="Pay">
                                    <i class="fas fa-check-circle"></i>
                                </button>
                            @endif
                            <a href="#" class="text-blue-600 hover:text-blue-800 transition" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-8 text-center text-gray-500">No salaries found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $salaries->links() }}
        </div>
    </div>
</div>

@push('scripts')
<script>
    function paySalary(id) {
        if (confirm('Are you sure you want to pay this salary?')) {
            fetch('/hrm/salaries/' + id + '/pay', {
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
            })
            .catch(error => alert('❌ Error processing payment.'));
        }
    }
</script>
@endpush
@endsection