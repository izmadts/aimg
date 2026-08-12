@extends('layouts.app')

@section('title', 'Change Password')

@section('content')
<div class="space-y-6">
    
    <!-- Page Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Change Password</h1>
            <p class="text-sm text-gray-500">Update your account password</p>
        </div>
        <a href="{{ route('profile.index') }}" class="text-gray-600 hover:text-gray-900">
            <i class="fas fa-arrow-left mr-2"></i> Back
        </a>
    </div>

    <div class="bg-white rounded-lg shadow p-6 max-w-3xl">
        <form method="POST" action="{{ route('profile.update-password') }}">
            @csrf
            @method('PUT')

            <!-- Current Password -->
            <div class="mb-4">
                <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">Current Password *</label>
                <input type="password" name="current_password" id="current_password" 
                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                @error('current_password')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- New Password -->
            <div class="mb-4">
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">New Password *</label>
                <input type="password" name="password" id="password" 
                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <p class="text-xs text-gray-400 mt-1">Min 8 characters, with at least one uppercase, lowercase, number, and special character.</p>
                @error('password')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Confirm Password -->
            <div class="mb-4">
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password *</label>
                <input type="password" name="password_confirmation" id="password_confirmation" 
                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>

            <!-- Password Requirements -->
            <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                <p class="text-sm font-medium text-gray-700 mb-2">Password Requirements:</p>
                <ul class="text-xs text-gray-500 space-y-1">
                    <li><i class="fas fa-check-circle text-green-500 mr-2"></i> At least 8 characters long</li>
                    <li><i class="fas fa-check-circle text-green-500 mr-2"></i> Contains at least one uppercase letter (A-Z)</li>
                    <li><i class="fas fa-check-circle text-green-500 mr-2"></i> Contains at least one lowercase letter (a-z)</li>
                    <li><i class="fas fa-check-circle text-green-500 mr-2"></i> Contains at least one number (0-9)</li>
                    <li><i class="fas fa-check-circle text-green-500 mr-2"></i> Contains at least one special character (!@#$%^&*)</li>
                </ul>
            </div>

            <!-- Submit -->
            <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
                <a href="{{ route('profile.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-6 rounded-lg transition">
                    Cancel
                </a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-lg transition">
                    <i class="fas fa-key mr-2"></i> Update Password
                </button>
            </div>
        </form>
    </div>
</div>
@endsection