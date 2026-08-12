@extends('layouts.app')

@section('title', 'Settings')

@section('content')
<div class="space-y-6">

    <div>
        <h1 class="text-2xl font-bold text-gray-900">⚙️ System Settings</h1>
        <p class="text-sm text-gray-500">Security and access settings for the whole system</p>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded-lg shadow p-6 max-w-2xl">
        <form method="POST" action="{{ route('settings.update') }}">
            @csrf
            @method('PUT')

            <div class="flex items-start justify-between py-4 border-b border-gray-200">
                <div class="pr-6">
                    <p class="font-medium text-gray-900">Allow Public Registration</p>
                    <p class="text-sm text-gray-500 mt-1">
                        When off, the <code class="bg-gray-100 px-1 rounded">/register</code> page is disabled and
                        the only way to create a new account is from the Users page. Recommended: off for an
                        internal system that handles financial data.
                    </p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer shrink-0 mt-1">
                    <input type="checkbox" name="allow_registration" value="1"
                           {{ $settings['allow_registration'] ? 'checked' : '' }}
                           class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-300 rounded-full peer peer-checked:bg-blue-600 transition-colors"></div>
                    <div class="absolute left-1 top-1 bg-white w-4 h-4 rounded-full transition-transform peer-checked:translate-x-5"></div>
                </label>
            </div>

            <div class="flex justify-end mt-6">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-lg transition">
                    <i class="fas fa-save mr-2"></i> Save Settings
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
