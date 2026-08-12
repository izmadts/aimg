@extends('layouts.app')

@section('title', 'Gas Products')

@section('content')
<div class="space-y-6">
    
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">🧪 Gas Products</h1>
            <p class="text-sm text-gray-500">Manage your gas products</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('gas-products.export') }}" 
               class="bg-gray-600 hover:bg-gray-700 text-white font-medium px-4 py-2 rounded-lg transition">
                <i class="fas fa-file-export mr-2"></i> Export
            </a>
            <a href="{{ route('gas-products.create') }}" 
               class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-lg transition">
                <i class="fas fa-plus mr-2"></i> Add Product
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-blue-500">
            <p class="text-xs text-gray-500">Total Products</p>
            <p class="text-2xl font-bold">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-green-500">
            <p class="text-xs text-gray-500">Active</p>
            <p class="text-2xl font-bold text-green-600">{{ $stats['active'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-yellow-500">
            <p class="text-xs text-gray-500">Total Stock Value</p>
            <p class="text-2xl font-bold text-yellow-600">Rs. {{ number_format($stats['total_stock_value'], 0) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-purple-500">
            <p class="text-xs text-gray-500">Total Cylinders</p>
            <p class="text-2xl font-bold text-purple-600">{{ $stats['total_cylinders'] }}</p>
        </div>
    </div>

    <!-- Search & Filter -->
    <div class="bg-white rounded-lg shadow p-4">
        <form method="GET" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Search by name, code..."
                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div>
                <select name="status" class="rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-lg transition">
                    <i class="fas fa-search mr-2"></i> Search
                </button>
                <a href="{{ route('gas-products.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium px-4 py-2 rounded-lg transition">
                    <i class="fas fa-times mr-2"></i> Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Products Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Code</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">UOM</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Purchase Price</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Sale Price</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Stock</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Related Cylinders</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($products as $product)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-3 text-sm font-mono font-medium">{{ $product->code }}</td>
                        <td class="px-6 py-3">
                            <span class="font-medium">{{ $product->name }}</span>
                            @if($product->description)
                                <span class="text-xs text-gray-400 block">{{ Str::limit($product->description, 30) }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-3 text-sm">{{ $product->uom }}</td>
                        <td class="px-6 py-3 text-right text-sm">Rs. {{ number_format($product->purchase_price, 2) }}</td>
                        <td class="px-6 py-3 text-right text-sm">Rs. {{ number_format($product->sale_price, 2) }}</td>
                        <td class="px-6 py-3 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($product->stock_status === 'good') bg-green-100 text-green-800
                                @elseif($product->stock_status === 'medium') bg-blue-100 text-blue-800
                                @elseif($product->stock_status === 'low') bg-yellow-100 text-yellow-800
                                @else bg-red-100 text-red-800
                                @endif">
                                {{ $product->current_stock }}
                            </span>
                        </td>
                        <td class="px-6 py-3">
                            @if($product->cylinders->isEmpty())
                                <span class="text-xs text-gray-400 italic">No cylinder types yet</span>
                            @else
                                <div class="flex flex-wrap gap-1 max-w-xs">
                                    @foreach($product->cylinders as $cylinder)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-50 text-indigo-700 border border-indigo-100"
                                              title="{{ $cylinder->cylinder_number }} &middot; Capacity {{ $cylinder->capacity }} m3">
                                            <i class="fas fa-gas-pump text-[10px]"></i>
                                            {{ $cylinder->type }}: {{ $cylinder->available_quantity }}
                                            @if($cylinder->issued_quantity > 0)
                                                <span class="text-yellow-700">({{ $cylinder->issued_quantity }} out)</span>
                                            @endif
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-3 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($product->is_active) bg-green-100 text-green-800
                                @else bg-red-100 text-red-800
                                @endif">
                                {{ $product->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-6 py-3 text-center">
                            <div class="flex items-center justify-center space-x-2">
                                <a href="{{ route('gas-products.show', $product) }}" 
                                   class="text-blue-600 hover:text-blue-800 transition" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('gas-products.edit', $product) }}" 
                                   class="text-yellow-600 hover:text-yellow-800 transition" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('gas-products.toggle-status', $product) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="text-gray-600 hover:text-gray-800 transition" title="{{ $product->is_active ? 'Deactivate' : 'Activate' }}">
                                        <i class="fas {{ $product->is_active ? 'fa-pause' : 'fa-play' }}"></i>
                                    </button>
                                </form>
                                @if($product->cylinders()->count() == 0)
                                <form action="{{ route('gas-products.destroy', $product) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" onclick="return confirm('Delete this product?')" 
                                            class="text-red-600 hover:text-red-800 transition" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-6 py-8 text-center text-gray-500">
                            <i class="fas fa-inbox text-3xl mb-2 text-gray-300 block"></i>
                            No gas products found.
                            <a href="{{ route('gas-products.create') }}" class="text-blue-600 hover:underline ml-1">Add your first product</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $products->links() }}
        </div>
    </div>
</div>
@endsection