@extends('layouts.app')

@section('title', 'Security Settings')

@section('content')
<div class="space-y-6">
    
    <!-- Page Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Security Settings</h1>
            <p class="text-sm text-gray-500">Manage your account security</p>
        </div>
        <a href="{{ route('profile.index') }}" class="text-gray-600 hover:text-gray-900">
            <i class="fas fa-arrow-left mr-2"></i> Back
        </a>
    </div>

    <div class="space-y-6">
        
        <!-- Active Sessions -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-desktop mr-2 text-gray-400"></i> Active Sessions
            </h3>
            
            @if($sessions->count() > 0)
                <div class="space-y-3">
                    @foreach($sessions as $session)
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div>
                                <div class="flex items-center space-x-2">
                                    @if($session->is_current)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-circle text-xs mr-1"></i> Current
                                        </span>
                                    @endif
                                    <span class="font-medium">
                                        @if($session->device === 'Mobile')
                                            <i class="fas fa-mobile-alt mr-1"></i>
                                        @elseif($session->device === 'Tablet')
                                            <i class="fas fa-tablet-alt mr-1"></i>
                                        @else
                                            <i class="fas fa-desktop mr-1"></i>
                                        @endif
                                        {{ $session->device }}
                                    </span>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">
                                    IP: {{ $session->ip_address }} | 
                                    Last active: {{ $session->last_activity->diffForHumans() }}
                                </p>
                            </div>
                            @if(!$session->is_current)
                                <button type="button" 
                                        onclick="if(confirm('Are you sure you want to log out this session?')) { alert('Session logged out!'); }" 
                                        class="text-red-600 hover:text-red-900 text-sm font-medium">
                                    <i class="fas fa-sign-out-alt mr-1"></i> Logout
                                </button>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-6 text-gray-500">
                    <i class="fas fa-inbox text-3xl mb-2 text-gray-300"></i>
                    <p>No active sessions found.</p>
                </div>
            @endif
        </div>

        <!-- Two Factor Authentication -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-shield-alt mr-2 text-gray-400"></i> Two-Factor Authentication (2FA)
                    </h3>
                    <p class="text-sm text-gray-500 mt-1">Add an extra layer of security to your account</p>
                </div>
                <button class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-4 rounded-lg transition text-sm">
                    <i class="fas fa-plus mr-1"></i> Setup 2FA
                </button>
            </div>
        </div>

        <!-- Danger Zone -->
        <div class="border border-red-200 rounded-lg shadow-sm">
            <div class="bg-red-50 rounded-t-lg px-6 py-4 border-b border-red-200">
                <h3 class="text-lg font-semibold text-red-600">
                    <i class="fas fa-exclamation-triangle mr-2"></i> Danger Zone
                </h3>
            </div>
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="font-medium text-gray-900">Delete Account</h4>
                        <p class="text-sm text-gray-500">Once you delete your account, there is no going back. All data will be permanently removed.</p>
                    </div>
                    <form method="POST" action="{{ route('profile.destroy') }}" 
                          onsubmit="return confirm('Are you sure you want to delete your account? This action cannot be undone!')">
                        @csrf
                        @method('DELETE')
                        <div class="flex items-center space-x-2">
                            <input type="password" name="password" placeholder="Enter password" required
                                   class="text-sm rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-lg transition text-sm">
                                <i class="fas fa-trash mr-1"></i> Delete Account
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection