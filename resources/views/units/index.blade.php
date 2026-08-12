@extends('layouts.app')

@section('title', 'Units of Measure')

@section('content')
<div class="space-y-6">

    <!-- Page Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">📏 Units of Measure</h1>
            <p class="text-sm text-gray-500">Manage the units gas products can be sold/stocked in</p>
        </div>
        <button onclick="openUnitModal()" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-lg transition">
            <i class="fas fa-plus mr-2"></i> Add Unit
        </button>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">{{ session('error') }}</div>
    @endif

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Used By</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($units as $unit)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-3 font-mono font-medium">{{ $unit->name }}</td>
                        <td class="px-6 py-3 text-sm text-gray-500">{{ $unit->description ?: '—' }}</td>
                        <td class="px-6 py-3 text-center text-sm">{{ $unit->usage_count }} product(s)</td>
                        <td class="px-6 py-3 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $unit->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $unit->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-6 py-3 text-center">
                            <div class="flex items-center justify-center space-x-2">
                                <button type="button"
                                        onclick="openUnitModal({{ $unit->id }}, '{{ addslashes($unit->name) }}', '{{ addslashes($unit->description ?? '') }}', {{ $unit->is_active ? 'true' : 'false' }})"
                                        class="text-yellow-600 hover:text-yellow-800 transition" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                @if($unit->usage_count == 0)
                                <form action="{{ route('units.destroy', $unit) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" onclick="return confirm('Delete this unit?')"
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
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                            <i class="fas fa-inbox text-3xl mb-2 text-gray-300 block"></i>
                            No units found.
                            <button type="button" onclick="openUnitModal()" class="text-blue-600 hover:underline ml-1">Add your first unit</button>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add/Edit Unit Modal -->
<div id="unitModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-lg bg-white">
        <div class="flex justify-between items-center pb-3 border-b">
            <h3 class="text-lg font-semibold text-gray-900" id="unitModalTitle">
                <i class="fas fa-plus text-blue-600 mr-2"></i> Add Unit
            </h3>
            <button type="button" onclick="closeUnitModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <form id="unitForm" method="POST" action="{{ route('units.store') }}" class="mt-4 space-y-4">
            @csrf
            <input type="hidden" name="_method" id="unit_method" value="POST">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
                <input type="text" name="name" id="unit_name" required maxlength="50"
                       placeholder="e.g. KG, Liters, Cubic Meter"
                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <input type="text" name="description" id="unit_description" maxlength="255"
                       placeholder="e.g. Kilogram"
                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div class="flex items-center">
                <input type="checkbox" name="is_active" id="unit_is_active" value="1" checked
                       class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <label class="ml-2 text-sm text-gray-700">Active</label>
            </div>
            <div class="flex justify-end space-x-3 pt-4 border-t">
                <button type="button" onclick="closeUnitModal()" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-4 rounded-lg transition">Cancel</button>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition">
                    <i class="fas fa-save mr-2"></i> Save Unit
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    const unitBaseUrl = '{{ url('units') }}';

    function openUnitModal(id, name, description, isActive) {
        const form = document.getElementById('unitForm');
        const title = document.getElementById('unitModalTitle');

        if (id) {
            form.action = unitBaseUrl + '/' + id;
            document.getElementById('unit_method').value = 'PUT';
            title.innerHTML = '<i class="fas fa-edit text-yellow-600 mr-2"></i> Edit Unit';
            document.getElementById('unit_name').value = name;
            document.getElementById('unit_description').value = description;
            document.getElementById('unit_is_active').checked = isActive;
        } else {
            form.action = unitBaseUrl;
            document.getElementById('unit_method').value = 'POST';
            title.innerHTML = '<i class="fas fa-plus text-blue-600 mr-2"></i> Add Unit';
            form.reset();
            document.getElementById('unit_is_active').checked = true;
        }

        document.getElementById('unitModal').classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function closeUnitModal() {
        document.getElementById('unitModal').classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    document.getElementById('unitModal').addEventListener('click', function(e) {
        if (e.target === this) closeUnitModal();
    });

    @if(isset($errors) && $errors->any())
        openUnitModal();
    @endif
</script>
@endpush
@endsection
