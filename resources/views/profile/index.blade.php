@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
<div class="space-y-6">
    
    <!-- Page Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">My Profile</h1>
            <p class="text-sm text-gray-500">Manage your account settings and preferences</p>
        </div>
        <a href="{{ route('profile.edit') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-lg transition">
            <i class="fas fa-edit mr-2"></i> Edit Profile
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Profile Card -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-center">
                    <!-- Profile Image -->
                    <div class="relative inline-block">
                        <img src="{{ $user->profile_image_url }}" 
                             alt="{{ $user->name }}"
                             class="w-32 h-32 rounded-full mx-auto border-4 border-blue-100 object-cover">
                        
                        <a href="{{ route('profile.edit') }}" 
                           class="absolute bottom-0 right-0 bg-blue-600 rounded-full p-2 text-white hover:bg-blue-700 transition">
                            <i class="fas fa-camera text-sm"></i>
                        </a>
                    </div>

                    <!-- User Info -->
                    <h2 class="mt-4 text-xl font-bold text-gray-900">{{ $user->name }}</h2>
                    <p class="text-sm text-gray-500">{{ $user->email }}</p>
                    
                    @if($user->phone)
                        <p class="text-sm text-gray-500 mt-1">
                            <i class="fas fa-phone mr-2"></i> {{ $user->phone }}
                        </p>
                    @endif

                    @if($user->address)
                        <p class="text-sm text-gray-500 mt-1">
                            <i class="fas fa-map-marker-alt mr-2"></i> {{ $user->address }}
                        </p>
                    @endif

                    <div class="mt-4 flex justify-center space-x-2">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                            @if($user->isAdmin()) bg-purple-100 text-purple-800
                            @else bg-blue-100 text-blue-800
                            @endif">
                            <i class="fas fa-shield-alt mr-1"></i> {{ $user->role_name }}
                        </span>
                        
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                            @if($user->is_active) bg-green-100 text-green-800
                            @else bg-red-100 text-red-800
                            @endif">
                            <i class="fas fa-circle mr-1 text-xs"></i> 
                            {{ $user->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>

                    <div class="mt-4 text-sm text-gray-500 border-t border-gray-100 pt-4">
                        <p><i class="fas fa-calendar-alt mr-2"></i> Member since: <strong>{{ $stats->member_since }}</strong></p>
                        @if($user->last_login_at)
                            <p class="mt-1"><i class="fas fa-clock mr-2"></i> Last login: <strong>{{ $user->last_login_at->diffForHumans() }}</strong></p>
                        @endif
                    </div>

                    <!-- Quick Actions -->
                    <div class="mt-6 space-y-2">
                        <a href="{{ route('profile.edit') }}" 
                           class="block w-full text-center bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2.5 px-4 rounded-lg transition">
                            <i class="fas fa-user-edit mr-2"></i> Edit Profile
                        </a>
                        <a href="{{ route('profile.change-password') }}" 
                           class="block w-full text-center bg-gray-600 hover:bg-gray-700 text-white font-medium py-2.5 px-4 rounded-lg transition">
                            <i class="fas fa-key mr-2"></i> Change Password
                        </a>
                        <a href="{{ route('profile.security') }}" 
                           class="block w-full text-center bg-blue-600 hover:bg-blue-700 text-white font-medium py-2.5 px-4 rounded-lg transition">
                            <i class="fas fa-shield-alt mr-2"></i> Security Settings
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats and Activity -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Stats Grid -->
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-500">Sales Created</p>
                            <p class="text-xl font-bold text-indigo-600">{{ $stats->total_sales }}</p>
                        </div>
                        <div class="bg-indigo-100 rounded-full p-2">
                            <i class="fas fa-file-invoice text-indigo-600"></i>
                        </div>
                    </div>
                    <p class="text-xs text-gray-400 mt-1">Rs. {{ number_format($stats->total_sales_amount, 0) }}</p>
                </div>

                <div class="bg-white rounded-lg shadow p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-500">Purchases Created</p>
                            <p class="text-xl font-bold text-blue-600">{{ $stats->total_purchases }}</p>
                        </div>
                        <div class="bg-blue-100 rounded-full p-2">
                            <i class="fas fa-shopping-cart text-blue-600"></i>
                        </div>
                    </div>
                    <p class="text-xs text-gray-400 mt-1">Rs. {{ number_format($stats->total_purchase_amount, 0) }}</p>
                </div>

                <div class="bg-white rounded-lg shadow p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-500">Cylinder Transactions</p>
                            <p class="text-xl font-bold text-yellow-600">{{ $stats->total_cylinder_transactions }}</p>
                        </div>
                        <div class="bg-yellow-100 rounded-full p-2">
                            <i class="fas fa-cylinder text-yellow-600"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-500">Customers Created</p>
                            <p class="text-xl font-bold text-green-600">{{ $stats->total_customers_created }}</p>
                        </div>
                        <div class="bg-green-100 rounded-full p-2">
                            <i class="fas fa-users text-green-600"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-500">Suppliers Created</p>
                            <p class="text-xl font-bold text-purple-600">{{ $stats->total_suppliers_created }}</p>
                        </div>
                        <div class="bg-purple-100 rounded-full p-2">
                            <i class="fas fa-truck text-purple-600"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-500">Journal Entries</p>
                            <p class="text-xl font-bold text-red-600">{{ $stats->total_journal_entries }}</p>
                        </div>
                        <div class="bg-red-100 rounded-full p-2">
                            <i class="fas fa-book text-red-600"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-clock mr-2 text-gray-400"></i> Recent Activity
                    </h3>
                </div>
                
                @if($recentActivity->count() > 0)
                    <div class="space-y-4">
                        @foreach($recentActivity as $activity)
                            <div class="flex items-start space-x-3 pb-4 border-b border-gray-100 last:border-0 last:pb-0">
                                <!-- Icon -->
                                <div class="flex-shrink-0">
                                    @if($activity->type === 'sale')
                                        <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                            <i class="fas fa-file-invoice text-green-600"></i>
                                        </div>
                                    @elseif($activity->type === 'purchase')
                                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                            <i class="fas fa-shopping-cart text-blue-600"></i>
                                        </div>
                                    @else
                                        <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center">
                                            <i class="fas fa-cylinder text-yellow-600"></i>
                                        </div>
                                    @endif
                                </div>

                                <!-- Content -->
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900">
                                        {{ $activity->title }}
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        {{ $activity->description }}
                                        @if($activity->amount > 0)
                                            <span class="font-semibold text-gray-700">Rs. {{ number_format($activity->amount, 0) }}</span>
                                        @endif
                                    </p>
                                    <p class="text-xs text-gray-400">{{ $activity->date->diffForHumans() }}</p>
                                </div>

                                @if($activity->url)
                                    <a href="{{ $activity->url }}" class="text-sm text-blue-600 hover:text-blue-800">
                                        View <i class="fas fa-arrow-right ml-1"></i>
                                    </a>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-inbox text-4xl mb-3 text-gray-300"></i>
                        <p>No recent activity found.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection