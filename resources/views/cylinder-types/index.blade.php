@extends('layouts.app')

@section('title', 'Cylinder Types')

@section('content')
<div class="space-y-6">

    <!-- Page Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">🛢️ Cylinder Types</h1>
            <p class="text-sm text-gray-500">Manage the cylinder sizes/types available across all gas products</p>
        </div>
        <button onclick="openTypeModal()" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-lg transition">
            <i class="fas fa-plus mr-2"></i> Add Type
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
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Default Capacity (m3)</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Used By</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($cylinderTypes as $type)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-3 font-mono font-medium">{{ $type->name }}</td>
                        <td class="px-6 py-3 text-right text-sm">{{ $type->capacity ?? '—' }}</td>
                        <td class="px-6 py-3 text-center text-sm">{{ $type->usage_count }} cylinder(s)</td>
                        <td class="px-6 py-3 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $type->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $type->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-6 py-3 text-center">
                            <div class="flex items-center justify-center space-x-2">
                                <button type="button"
                                        onclick="openTypeModal({{ $type->id }}, '{{ addslashes($type->name) }}', {{ $type->capacity ?? 'null' }}, {{ $type->is_active ? 'true' : 'false' }})"
                                        class="text-yellow-600 hover:text-yellow-800 transition" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                @if($type->usage_count == 0)
                                <form action="{{ route('cylinder-types.destroy', $type) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" onclick="return confirm('Delete this cylinder type?')"
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
                            No cylinder types found.
                            <button type="button" onclick="openTypeModal()" class="text-blue-600 hover:underline ml-1">Add your first type</button>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add/Edit Cylinder Type Modal -->
<div id="typeModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-lg bg-white">
        <div class="flex justify-between items-center pb-3 border-b">
            <h3 class="text-lg font-semibold text-gray-900" id="typeModalTitle">
                <i class="fas fa-plus text-blue-600 mr-2"></i> Add Cylinder Type
            </h3>
            <button type="button" onclick="closeTypeModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <form id="typeForm" method="POST" action="{{ route('cylinder-types.store') }}" class="mt-4 space-y-4">
            @csrf
            <input type="hidden" name="_method" id="type_method" value="POST">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
                <input type="text" name="name" id="type_name" required maxlength="50"
                       placeholder="e.g. Large, Medium, Small"
                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Capacity (m3) *</label>
                <input type="number" step="0.01" min="0.01" name="capacity" id="type_capacity" required
                       placeholder="e.g. 9.9"
                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <p class="text-xs text-gray-400 mt-1">Every cylinder created under this type gets this capacity automatically.</p>
            </div>
            <div class="flex items-center">
                <input type="checkbox" name="is_active" id="type_is_active" value="1" checked
                       class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <label class="ml-2 text-sm text-gray-700">Active</label>
            </div>
            <div class="flex justify-end space-x-3 pt-4 border-t">
                <button type="button" onclick="closeTypeModal()" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-4 rounded-lg transition">Cancel</button>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition">
                    <i class="fas fa-save mr-2"></i> Save Type
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    const typeBaseUrl = '{{ url('cylinder-types') }}';

    function openTypeModal(id, name, capacity, isActive) {
        const form = document.getElementById('typeForm');
        const title = document.getElementById('typeModalTitle');

        if (id) {
            form.action = typeBaseUrl + '/' + id;
            document.getElementById('type_method').value = 'PUT';
            title.innerHTML = '<i class="fas fa-edit text-yellow-600 mr-2"></i> Edit Cylinder Type';
            document.getElementById('type_name').value = name;
            document.getElementById('type_capacity').value = capacity ?? '';
            document.getElementById('type_is_active').checked = isActive;
        } else {
            form.action = typeBaseUrl;
            document.getElementById('type_method').value = 'POST';
            title.innerHTML = '<i class="fas fa-plus text-blue-600 mr-2"></i> Add Cylinder Type';
            form.reset();
            document.getElementById('type_is_active').checked = true;
        }

        document.getElementById('typeModal').classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function closeTypeModal() {
        document.getElementById('typeModal').classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    document.getElementById('typeModal').addEventListener('click', function(e) {
        if (e.target === this) closeTypeModal();
    });

    @if(isset($errors) && $errors->any())
        openTypeModal();
    @endif
</script>
@endpush
@endsection
