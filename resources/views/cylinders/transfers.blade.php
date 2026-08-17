@extends('layouts.app')

@section('title', 'Gas Transfers')

@section('content')
<div class="space-y-6">

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">🔄 Gas Transfers</h1>
            <p class="text-sm text-gray-500">Move gas from bulk/bowser stock into your saleable cylinders</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('cylinders.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-medium px-4 py-2 rounded-lg transition">
                <i class="fas fa-list mr-2"></i> All Cylinders
            </a>
            @can('cylinders.edit')
            <button onclick="openTransferModal()" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-lg transition">
                <i class="fas fa-plus mr-2"></i> New Transfer
            </button>
            @endcan
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
            <ul class="list-disc list-inside text-sm">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Current Bulk Stock -->
    <div class="bg-white rounded-lg shadow p-4">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">
            <i class="fas fa-warehouse mr-2 text-gray-400"></i> Current Bulk / Bowser Stock
        </h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            @forelse($gasProducts as $gp)
            <div class="border border-gray-200 rounded-lg p-3 text-center">
                <p class="text-xs text-gray-500">{{ $gp->name }}</p>
                <p class="text-lg font-bold {{ $gp->current_stock > 0 ? 'text-blue-600' : 'text-red-500' }}">
                    {{ number_format($gp->current_stock, 2) }} <span class="text-xs font-normal text-gray-400">{{ $gp->uom }}</span>
                </p>
            </div>
            @empty
            <p class="text-sm text-gray-400 col-span-4">No gas products found.</p>
            @endforelse
        </div>
    </div>

    <!-- Filter -->
    <div class="bg-white rounded-lg shadow p-4">
        <form method="GET" action="{{ route('cylinders.transfers') }}" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Gas Product</label>
                <select name="gas_product_id" class="rounded-lg border-gray-300 shadow-sm text-sm">
                    <option value="">All</option>
                    @foreach($gasProducts as $gp)
                        <option value="{{ $gp->id }}" {{ request('gas_product_id') == $gp->id ? 'selected' : '' }}>{{ $gp->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Cylinder Type</label>
                <select name="cylinder_id" class="rounded-lg border-gray-300 shadow-sm text-sm">
                    <option value="">All</option>
                    @foreach($cylinders as $c)
                        <option value="{{ $c->id }}" {{ request('cylinder_id') == $c->id ? 'selected' : '' }}>{{ $c->cylinder_number }} — {{ $c->type }} ({{ $c->gasProduct->name ?? 'N/A' }})</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg transition">
                <i class="fas fa-filter mr-1"></i> Filter
            </button>
            @if(request('gas_product_id') || request('cylinder_id'))
                <a href="{{ route('cylinders.transfers') }}" class="text-sm text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times mr-1"></i> Clear
                </a>
            @endif
        </form>
    </div>

    <!-- Transfer History -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Gas</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cylinder Type</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Gas Transferred</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Details</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">By</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($transfers as $t)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-3 text-sm">{{ $t->transaction_date?->format('d-m-Y') }}</td>
                        <td class="px-6 py-3 text-sm">{{ $t->cylinder->gasProduct->name ?? 'N/A' }}</td>
                        <td class="px-6 py-3 text-sm font-mono">{{ $t->cylinder->cylinder_number ?? 'N/A' }} — {{ $t->cylinder->type ?? 'N/A' }}</td>
                        <td class="px-6 py-3 text-right text-sm font-semibold">{{ number_format($t->gas_quantity_at_transaction, 2) }}</td>
                        <td class="px-6 py-3 text-sm text-gray-500">{{ $t->remarks }}</td>
                        <td class="px-6 py-3 text-sm text-gray-500">{{ $t->user->name ?? 'System' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                            <i class="fas fa-inbox text-3xl mb-2 text-gray-300 block"></i>
                            No gas transfers recorded yet.
                            @can('cylinders.edit')
                                <button onclick="openTransferModal()" class="text-blue-600 hover:underline ml-1">Record your first transfer</button>
                            @endcan
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $transfers->appends(request()->query())->links() }}
        </div>
    </div>
</div>

<!-- New Transfer Modal -->
<div id="transferModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="relative top-16 mx-auto p-5 border w-full max-w-lg shadow-lg rounded-lg bg-white">
        <div class="flex justify-between items-center pb-3 border-b">
            <h3 class="text-lg font-semibold text-gray-900">
                <i class="fas fa-truck-loading text-blue-600 mr-2"></i> Transfer Gas to Cylinders
            </h3>
            <button type="button" onclick="closeTransferModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <form method="POST" action="{{ route('cylinders.transfer.store') }}" class="mt-4 space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Gas Product (from bulk/bowser stock) *</label>
                <select name="gas_product_id" id="transfer_gas_product_id" required
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        onchange="filterTransferCylinders()">
                    <option value="">Select gas product</option>
                    @foreach($gasProducts as $gp)
                        <option value="{{ $gp->id }}" data-stock="{{ $gp->current_stock }}" data-uom="{{ $gp->uom }}">
                            {{ $gp->name }} — {{ number_format($gp->current_stock, 2) }} {{ $gp->uom }} available
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Cylinder Type (being filled) *</label>
                <select name="cylinder_id" id="transfer_cylinder_id" required
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Select gas product first</option>
                </select>
                <p class="text-xs text-gray-400 mt-1" id="transfer_cylinder_hint"></p>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Gas Quantity Transferred *</label>
                    <input type="number" step="0.01" min="0.01" name="gas_quantity" id="transfer_gas_quantity" required
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cylinders Filled</label>
                    <input type="number" step="1" min="1" name="cylinder_quantity" id="transfer_cylinder_quantity"
                           placeholder="Optional"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                <input type="date" name="transfer_date" value="{{ now()->toDateString() }}"
                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                <textarea name="notes" rows="2" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
            </div>
            <p class="text-xs text-gray-400">
                This reduces your bulk/bowser gas stock and records which cylinder size received it. It does not change how
                many cylinders you own — only which ones are now filled.
            </p>
            <div class="flex justify-end space-x-3 pt-4 border-t">
                <button type="button" onclick="closeTransferModal()" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-4 rounded-lg transition">Cancel</button>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition">
                    <i class="fas fa-truck-loading mr-2"></i> Transfer
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    const transferCylinders = @json($cylinders->map(function ($c) {
        return [
            'id' => $c->id,
            'label' => $c->cylinder_number . ' — ' . $c->type . ' (' . $c->available_quantity . ' free of ' . $c->stock_quantity . ' owned)',
            'gas_product_id' => $c->gas_product_id,
        ];
    }));

    function filterTransferCylinders() {
        const gasSelect = document.getElementById('transfer_gas_product_id');
        const cylinderSelect = document.getElementById('transfer_cylinder_id');
        const hint = document.getElementById('transfer_cylinder_hint');
        const gasId = gasSelect.value ? parseInt(gasSelect.value) : null;

        cylinderSelect.innerHTML = '';

        if (!gasId) {
            cylinderSelect.innerHTML = '<option value="">Select gas product first</option>';
            hint.textContent = '';
            return;
        }

        const matches = transferCylinders.filter(c => c.gas_product_id === gasId);

        if (matches.length === 0) {
            cylinderSelect.innerHTML = '<option value="">No cylinder types set up for this gas yet</option>';
            hint.textContent = 'Add a cylinder type for this gas product first (Cylinders > Add Cylinder).';
            return;
        }

        cylinderSelect.innerHTML = '<option value="">Select cylinder type</option>' +
            matches.map(c => `<option value="${c.id}">${c.label}</option>`).join('');
        hint.textContent = '';
    }

    function openTransferModal() {
        document.getElementById('transferModal').classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
        filterTransferCylinders();
    }

    function closeTransferModal() {
        document.getElementById('transferModal').classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    document.getElementById('transferModal').addEventListener('click', function(e) {
        if (e.target === this) closeTransferModal();
    });

    @if($errors->any())
        openTransferModal();
    @endif
</script>
@endpush
@endsection
