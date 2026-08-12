@php
    $role = $role ?? null;
    $isAdminRole = $role && $role->slug === 'admin';
@endphp

<div class="bg-white rounded-lg shadow p-6 max-w-4xl">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Role Name *</label>
            <input type="text" name="name" value="{{ old('name', $role->name ?? '') }}" required
                   {{ $isAdminRole ? 'readonly' : '' }}
                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 {{ $isAdminRole ? 'bg-gray-100' : '' }}">
            @error('name')
                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <input type="text" name="description" value="{{ old('description', $role->description ?? '') }}"
                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            @error('description')
                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>

<div class="bg-white rounded-lg shadow p-6 max-w-4xl mt-6">
    <h2 class="text-lg font-semibold text-gray-900 mb-1">Permissions</h2>

    @if($isAdminRole)
        <p class="text-sm text-gray-500 mb-4">The Administrator role always has every permission and cannot be limited.</p>
    @else
        <p class="text-sm text-gray-500 mb-4">Pick exactly what this role is allowed to do.</p>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($permissionsByModule as $module => $permissions)
        <div class="border border-gray-200 rounded-lg p-3">
            <div class="flex items-center justify-between mb-2">
                <p class="text-sm font-semibold text-gray-700 capitalize">{{ str_replace('_', ' ', $module) }}</p>
                @unless($isAdminRole)
                <button type="button" onclick="toggleModule('{{ $module }}')" class="text-xs text-blue-600 hover:underline">Toggle all</button>
                @endunless
            </div>
            <div class="space-y-1" data-module="{{ $module }}">
                @foreach($permissions as $permission)
                <label class="flex items-center text-sm text-gray-600">
                    <input type="checkbox" name="permissions[]" value="{{ $permission->id }}"
                           {{ $isAdminRole ? 'checked disabled' : '' }}
                           {{ in_array($permission->id, old('permissions', $assignedPermissionIds)) ? 'checked' : '' }}
                           class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 mr-2">
                    {{ $permission->name }}
                </label>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>
</div>

<div class="flex justify-end space-x-4 mt-6 max-w-4xl">
    <a href="{{ route('roles.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-6 rounded-lg transition">
        Cancel
    </a>
    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-lg transition">
        <i class="fas fa-save mr-2"></i> Save Role
    </button>
</div>

<script>
function toggleModule(module) {
    const box = document.querySelector('[data-module="' + module + '"]');
    const checkboxes = box.querySelectorAll('input[type="checkbox"]');
    const allChecked = Array.from(checkboxes).every(cb => cb.checked);
    checkboxes.forEach(cb => cb.checked = !allChecked);
}
</script>
