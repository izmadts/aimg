@extends('layouts.app')

@section('title', 'Cylinder Stock')

@section('content')
<div class="space-y-6">
    
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">📦 Cylinder Stock</h1>
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

    <!-- Stock Summary Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3">
        <div class="bg-white rounded-lg shadow p-3 border-l-4 border-blue-500">
            <p class="text-xs text-gray-500">Types</p>
            <p class="text-xl font-bold">{{ $stockSummary['total_types'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-3 border-l-4 border-green-500">
            <p class="text-xs text-gray-500">Total Stock</p>
            <p class="text-xl font-bold text-green-600">{{ $stockSummary['total_stock'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-3 border-l-4 border-yellow-500">
            <p class="text-xs text-gray-500">Issued</p>
            <p class="text-xl font-bold text-yellow-600">{{ $stockSummary['total_issued'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-3 border-l-4 border-purple-500">
            <p class="text-xs text-gray-500">Available</p>
            <p class="text-xl font-bold text-purple-600">{{ $stockSummary['available'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-3 border-l-4 border-orange-500">
            <p class="text-xs text-gray-500">Total Value</p>
            <p class="text-xl font-bold text-orange-600">Rs. {{ number_format($stockSummary['total_value'], 0) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-3 border-l-4 border-red-500">
            <p class="text-xs text-gray-500">Out of Stock</p>
            <p class="text-xl font-bold text-red-600">{{ $stockSummary['out_of_stock'] ?? 0 }}</p>
        </div>
    </div>

    <!-- Search -->
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
                </select>
            </div>
            <div>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-lg transition">
                    <i class="fas fa-search mr-2"></i> Search
                </button>
                <a href="{{ route('cylinders.stock') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium px-4 py-2 rounded-lg transition">
                    <i class="fas fa-times mr-2"></i> Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Stock Table -->
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
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($cylinders as $cylinder)
                    <tr>
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
                            <div class="flex items-center justify-center space-x-1">
                                <a href="{{ route('cylinders.show', $cylinder) }}" class="text-blue-600 hover:text-blue-800" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if($cylinder->issued_quantity == 0)
                                    <a href="{{ route('cylinders.edit', $cylinder) }}" class="text-yellow-600 hover:text-yellow-800" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endif
                                <button onclick="openStockModal({{ $cylinder->id }}, '{{ $cylinder->cylinder_number }}', {{ $cylinder->stock_quantity }})"
                                        class="text-green-600 hover:text-green-800" title="Update Stock">
                                    <i class="fas fa-boxes"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-6 py-8 text-center text-gray-500">No cylinders found.</td>
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

<!-- Stock Update Modal -->
<div id="stockModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-lg bg-white">
        <div class="flex justify-between items-center pb-3 border-b">
            <h3 class="text-lg font-semibold text-gray-900">
                <i class="fas fa-boxes text-green-600 mr-2"></i> Update Stock
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
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg transition">
                    <i class="fas fa-save mr-2"></i> Update Stock
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    let stockData = { cylinderId: null, currentStock: 0 };

    function openStockModal(cylinderId, cylinderNumber, currentStock) {
        stockData.cylinderId = cylinderId;
        stockData.currentStock = currentStock;
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