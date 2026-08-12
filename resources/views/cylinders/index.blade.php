@extends('layouts.app')

@section('title', 'Cylinders')

@section('content')
<div class="space-y-6">
    
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">🛢️ Cylinders</h1>
            <p class="text-sm text-gray-500">Manage your cylinder inventory</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('cylinders.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-lg transition">
                <i class="fas fa-plus mr-2"></i> Add Cylinder
            </a>
            <a href="{{ route('cylinders.export') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-medium px-4 py-2 rounded-lg transition">
                <i class="fas fa-file-export mr-2"></i> Export
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-8 gap-2">
        <div class="bg-white rounded-lg shadow p-2 text-center">
            <p class="text-xs text-gray-500">Total</p>
            <p class="text-lg font-bold">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-2 text-center border-l-4 border-blue-500">
            <p class="text-xs text-gray-500">In House</p>
            <p class="text-lg font-bold text-blue-600">{{ $stats['in_house'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-2 text-center border-l-4 border-yellow-500">
            <p class="text-xs text-gray-500">Partial</p>
            <p class="text-lg font-bold text-yellow-600">{{ $stats['partial_issued'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-2 text-center border-l-4 border-orange-500">
            <p class="text-xs text-gray-500">All Issued</p>
            <p class="text-lg font-bold text-orange-600">{{ $stats['all_issued'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-2 text-center border-l-4 border-red-500">
            <p class="text-xs text-gray-500">Out of Stock</p>
            <p class="text-lg font-bold text-red-600">{{ $stats['out_of_stock'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-2 text-center border-l-4 border-purple-500">
            <p class="text-xs text-gray-500">Maintenance</p>
            <p class="text-lg font-bold text-purple-600">{{ $stats['under_maintenance'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-2 text-center border-l-4 border-gray-500">
            <p class="text-xs text-gray-500">Scrapped</p>
            <p class="text-lg font-bold text-gray-600">{{ $stats['scrapped'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-2 text-center border-l-4 border-teal-500">
            <p class="text-xs text-gray-500">Total Stock</p>
            <p class="text-lg font-bold text-teal-600">{{ $stats['total_stock'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-2 text-center border-l-4 border-green-500">
            <p class="text-xs text-gray-500">Total Value</p>
            <p class="text-lg font-bold text-green-600">Rs. {{ number_format($stats['total_value'], 0) }}</p>
        </div>
    </div>

    <!-- Search & Filter -->
    <div class="bg-white rounded-lg shadow p-4">
        <form method="GET" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Search by number, type..."
                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div>
                <select name="status" class="rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">All Status</option>
                    <option value="in_house" {{ request('status') == 'in_house' ? 'selected' : '' }}>In House</option>
                    <option value="partial_issued" {{ request('status') == 'partial_issued' ? 'selected' : '' }}>Partial</option>
                    <option value="all_issued" {{ request('status') == 'all_issued' ? 'selected' : '' }}>All Issued</option>
                    <option value="out_of_stock" {{ request('status') == 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                    <option value="under_maintenance" {{ request('status') == 'under_maintenance' ? 'selected' : '' }}>Maintenance</option>
                    <option value="scrapped" {{ request('status') == 'scrapped' ? 'selected' : '' }}>Scrapped</option>
                </select>
            </div>
            <div>
                <select name="gas_product_id" class="rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">All Gas</option>
                    @foreach($gasProducts as $product)
                        <option value="{{ $product->id }}" {{ request('gas_product_id') == $product->id ? 'selected' : '' }}>
                            {{ $product->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-lg transition">
                    <i class="fas fa-search mr-2"></i> Search
                </button>
                <a href="{{ route('cylinders.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium px-4 py-2 rounded-lg transition">
                    <i class="fas fa-times mr-2"></i> Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Cylinders Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cylinder #</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Gas</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Stock</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Issued</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Available</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Value</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($cylinders as $cylinder)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-3 text-sm text-gray-500">{{ $loop->iteration }}</td>
                        <td class="px-6 py-3 font-mono font-medium">{{ $cylinder->cylinder_number }}</td>
                        <td class="px-6 py-3 text-sm">{{ $cylinder->gasProduct->name ?? 'N/A' }}</td>
                        <td class="px-6 py-3 text-sm">{{ $cylinder->type }}</td>
                        <td class="px-6 py-3 text-center font-bold">{{ $cylinder->stock_quantity }}</td>
                        <td class="px-6 py-3 text-center text-yellow-600">{{ $cylinder->issued_quantity }}</td>
                        <td class="px-6 py-3 text-center">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium 
                                {{ $cylinder->available_quantity > 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $cylinder->available_quantity }}
                            </span>
                        </td>
                        <td class="px-6 py-3 text-center text-sm">Rs. {{ number_format($cylinder->purchase_price * $cylinder->stock_quantity, 0) }}</td>
                        <td class="px-6 py-3 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($cylinder->status === 'in_house') bg-blue-100 text-blue-800
                                @elseif($cylinder->status === 'partial_issued') bg-yellow-100 text-yellow-800
                                @elseif($cylinder->status === 'all_issued') bg-orange-100 text-orange-800
                                @elseif($cylinder->status === 'out_of_stock') bg-red-100 text-red-800
                                @elseif($cylinder->status === 'under_maintenance') bg-purple-100 text-purple-800
                                @elseif($cylinder->status === 'scrapped') bg-gray-100 text-gray-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ $cylinder->status_label }}
                            </span>
                        </td>
                        <td class="px-6 py-3 text-center">
                            <div class="flex items-center justify-center space-x-1">
                                <a href="{{ route('cylinders.show', $cylinder) }}" class="text-blue-600 hover:text-blue-800" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if($cylinder->issued_quantity == 0)
                                    <a href="{{ route('cylinders.edit', $cylinder) }}" class="text-yellow-600 hover:text-yellow-800" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('cylinders.destroy', $cylinder) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" onclick="return confirm('Delete this cylinder?')" class="text-red-600 hover:text-red-800" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                @endif
                                @if(in_array($cylinder->status, ['in_house', 'partial_issued']))
                                    <button onclick="openIssueModal('{{ $cylinder->id }}', '{{ $cylinder->cylinder_number }}')"
                                            class="text-green-600 hover:text-green-800" title="Issue">
                                        <i class="fas fa-hand-holding"></i>
                                    </button>
                                @endif
                                <button onclick="openStockModal({{ $cylinder->id }}, '{{ $cylinder->cylinder_number }}', {{ $cylinder->stock_quantity }})"
                                        class="text-teal-600 hover:text-teal-800" title="Update Stock">
                                    <i class="fas fa-boxes"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="px-6 py-8 text-center text-gray-500">
                            <i class="fas fa-inbox text-3xl mb-2 text-gray-300 block"></i>
                            No cylinders found.
                            <a href="{{ route('cylinders.create') }}" class="text-blue-600 hover:underline ml-1">Add your first cylinder</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $cylinders->links() }}
        </div>
    </div>
</div>

<!-- Issue Modal -->
<div id="issueModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-lg bg-white">
        <div class="flex justify-between items-center pb-3 border-b">
            <h3 class="text-lg font-semibold text-gray-900">
                <i class="fas fa-hand-holding text-green-600 mr-2"></i> Issue Cylinder
            </h3>
            <button onclick="closeIssueModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <form id="issueForm" class="mt-4 space-y-4">
            @csrf
            <input type="hidden" name="cylinder_id" id="issue_cylinder_id">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Cylinder</label>
                <input type="text" id="issue_cylinder_number" disabled class="w-full rounded-lg bg-gray-100 border-gray-300 shadow-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Customer *</label>
                <select name="customer_id" id="issue_customer_id" required class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Select Customer</option>
                    @foreach($customers ?? [] as $customer)
                        <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Security Deposit (Rs.)</label>
                <input type="number" step="0.01" name="security_deposit" id="issue_security_deposit" value="0" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                <textarea name="notes" id="issue_notes" rows="2" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
            </div>
            <div class="flex justify-end space-x-3 pt-4 border-t">
                <button type="button" onclick="closeIssueModal()" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-4 rounded-lg transition">Cancel</button>
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg transition">
                    <i class="fas fa-hand-holding mr-2"></i> Issue Cylinder
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Update Stock Modal -->
<div id="stockModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-lg bg-white">
        <div class="flex justify-between items-center pb-3 border-b">
            <h3 class="text-lg font-semibold text-gray-900">
                <i class="fas fa-boxes text-teal-600 mr-2"></i> Update Stock
            </h3>
            <button onclick="closeStockModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <form id="stockForm" class="mt-4 space-y-4">
            @csrf
            <input type="hidden" name="cylinder_id" id="stock_cylinder_id">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Cylinder</label>
                <input type="text" id="stock_cylinder_number" disabled class="w-full rounded-lg bg-gray-100 border-gray-300 shadow-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Current Stock</label>
                <input type="text" id="stock_current" disabled class="w-full rounded-lg bg-gray-100 border-gray-300 shadow-sm font-bold">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Action *</label>
                <select name="action" id="stock_action" required class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="add">➕ Add Stock</option>
                    <option value="remove">➖ Remove Stock</option>
                    <option value="set">📝 Set Exact Stock</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Quantity *</label>
                <input type="number" name="quantity" id="stock_quantity" required class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                <textarea name="notes" id="stock_notes" rows="2" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
            </div>
            <div class="flex justify-end space-x-3 pt-4 border-t">
                <button type="button" onclick="closeStockModal()" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-4 rounded-lg transition">Cancel</button>
                <button type="submit" class="bg-teal-600 hover:bg-teal-700 text-white font-medium py-2 px-4 rounded-lg transition">
                    <i class="fas fa-save mr-2"></i> Update Stock
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openIssueModal(cylinderId, cylinderNumber) {
        document.getElementById('issue_cylinder_id').value = cylinderId;
        document.getElementById('issue_cylinder_number').value = cylinderNumber;
        document.getElementById('issueModal').classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function closeIssueModal() {
        document.getElementById('issueModal').classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    document.getElementById('issueModal').addEventListener('click', function(e) {
        if (e.target === this) closeIssueModal();
    });

    document.getElementById('issueForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        fetch('{{ route("cylinders.issue") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('✅ ' + data.message);
                closeIssueModal();
                location.reload();
            } else {
                alert('❌ ' + data.message);
            }
        })
        .catch(error => alert('❌ Error processing request.'));
    });

    function openStockModal(cylinderId, cylinderNumber, currentStock) {
        document.getElementById('stock_cylinder_id').value = cylinderId;
        document.getElementById('stock_cylinder_number').value = cylinderNumber;
        document.getElementById('stock_current').value = currentStock + ' pieces';
        document.getElementById('stock_quantity').value = '';
        document.getElementById('stock_notes').value = '';
        document.getElementById('stockModal').classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function closeStockModal() {
        document.getElementById('stockModal').classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    document.getElementById('stockModal').addEventListener('click', function(e) {
        if (e.target === this) closeStockModal();
    });

    document.getElementById('stockForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const cylinderId = document.getElementById('stock_cylinder_id').value;
        const action = document.getElementById('stock_action').value;
        const quantity = parseInt(document.getElementById('stock_quantity').value);
        const notes = document.getElementById('stock_notes').value;

        if (!quantity || quantity < 0) {
            alert('Please enter a valid quantity.');
            return;
        }

        const formData = new FormData();
        formData.append('cylinder_id', cylinderId);
        formData.append('action', action);
        formData.append('quantity', quantity);
        formData.append('notes', notes);
        formData.append('_token', '{{ csrf_token() }}');

        fetch('{{ route("cylinders.update-stock") }}', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('✅ ' + data.message);
                closeStockModal();
                location.reload();
            } else {
                alert('❌ ' + data.message);
            }
        })
        .catch(error => alert('❌ Error processing request.'));
    });
</script>
@endpush
@endsection