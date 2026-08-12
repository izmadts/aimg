@php
    $currentRoute = request()->route()->getName();
@endphp

<aside id="sidebar" class="sidebar">
    <div class="flex flex-col h-full">
        
        <!-- Logo -->
        <div class="flex items-center justify-between px-6 py-5 border-b border-gray-200 bg-gradient-to-r from-blue-600 to-indigo-600">
            <div class="flex items-center space-x-3">
                <div class="bg-white/20 rounded-lg p-2">
                    <i class="fas fa-cylinder text-white text-xl"></i>
                </div>
                <div>
                    <h1 class="text-white font-bold text-lg leading-tight">ERP System</h1>
                    <p class="text-blue-100 text-xs">Medical Gas</p>
                </div>
            </div>
            <button type="button" onclick="closeSidebar()" class="lg:hidden text-white hover:text-blue-100">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <!-- User Info -->
        <div class="px-4 py-4 border-b border-gray-200 bg-gray-50">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-full bg-gradient-to-r from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold text-sm">
                    {{ Auth::user()->initials ?? 'U' }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-900 truncate">{{ Auth::user()->name }}</p>
                    <p class="text-xs text-gray-500 truncate">{{ Auth::user()->email }}</p>
                </div>
            </div>
        </div>
        
        <!-- Navigation -->
        <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
            
            <!-- Dashboard -->
            <a href="{{ route('dashboard') }}" 
               class="nav-link {{ $currentRoute === 'dashboard' ? 'active' : '' }}">
                <i class="fas fa-chart-pie"></i>
                <span>Dashboard</span>
            </a>

            <!-- Gas Products -->
            <a href="{{ route('gas-products.index') }}" 
               class="nav-link {{ Str::startsWith($currentRoute, 'gas-products.') ? 'active' : '' }}">
                <i class="fas fa-flask"></i>
                <span>Gas Products</span>
                <span class="badge">{{ \App\Models\GasProduct::count() }}</span>
            </a>
            
            <!-- Sales -->
            <a href="{{ route('sales.index') }}" 
               class="nav-link {{ Str::startsWith($currentRoute, 'sales.') ? 'active' : '' }}">
                <i class="fas fa-file-invoice"></i>
                <span>Sales</span>
                <span class="badge">{{ \App\Models\Sale::count() }}</span>
            </a>
            
            <!-- Purchases -->
            <a href="{{ route('purchases.index') }}" 
               class="nav-link {{ Str::startsWith($currentRoute, 'purchases.') ? 'active' : '' }}">
                <i class="fas fa-shopping-cart"></i>
                <span>Purchases</span>
                <span class="badge">{{ \App\Models\Purchase::count() }}</span>
            </a>
            
            <!-- Cylinders -->
            <!-- Cylinders -->
            <a href="{{ route('cylinders.index') }}" 
               class="nav-link {{ Str::startsWith($currentRoute, 'cylinders.') ? 'active' : '' }}">
                <i class="fas fa-cylinder"></i>
                <span>Cylinders</span>
                <span class="badge" style="background: #fef3c7; color: #92400e;">
                    {{ \App\Models\Cylinder::sum('issued_quantity') }}
                </span>
            </a>

            <!-- Cylinder Stock -->
            <a href="{{ route('cylinders.stock') }}" 
               class="nav-link {{ Str::startsWith($currentRoute, 'cylinders.stock') ? 'active' : '' }}">
                <i class="fas fa-warehouse"></i>
                <span>Cylinder Stock</span>
                <span class="badge" style="background: #dbeafe; color: #1e40af;">
                    {{ \App\Models\Cylinder::whereIn('status', ['in_house', 'partial_issued'])->count() }}
                </span>
            </a>
            
            <!-- Divider -->
            <div class="border-t border-gray-200 my-4"></div>
            
            <!-- Customers -->
            <!-- Customers -->
            <a href="{{ route('customers.index') }}" 
               class="nav-link {{ Str::startsWith($currentRoute, 'customers.') ? 'active' : '' }}">
                <i class="fas fa-users"></i>
                <span>Customers</span>
                <span class="badge">{{ \App\Models\Customer::count() }}</span>
            </a>
            
            <!-- Suppliers -->
           <!-- Suppliers -->
            <a href="{{ route('suppliers.index') }}" 
               class="nav-link {{ Str::startsWith($currentRoute, 'suppliers.') ? 'active' : '' }}">
                <i class="fas fa-truck"></i>
                <span>Suppliers</span>
                <span class="badge">{{ \App\Models\Supplier::count() }}</span>
            </a>

            <!-- HRM -->
                <a href="{{ route('hrm.employees') }}" 
                   class="nav-link {{ Str::startsWith($currentRoute, 'hrm.') ? 'active' : '' }}">
                    <i class="fas fa-users-cog"></i>
                    <span>HRM</span>
                </a>

                <!-- Accounting -->
                <a href="{{ route('accounting.trial-balance') }}" 
                   class="nav-link {{ Str::startsWith($currentRoute, 'accounting.') ? 'active' : '' }}">
                    <i class="fas fa-balance-scale"></i>
                    <span>Accounting</span>
                </a>

            <!-- Accounts -->
            <a href="{{ route('accounts.index') }}" 
               class="nav-link {{ Str::startsWith($currentRoute, 'accounts.') ? 'active' : '' }}">
                <i class="fas fa-book"></i>
                <span>Accounts</span>
            </a>

            <!-- Income & Expense -->
            <a href="{{ route('income-expense.index') }}" 
               class="nav-link {{ Str::startsWith($currentRoute, 'income-expense.') ? 'active' : '' }}">
                <i class="fas fa-coins"></i>
                <span>Income/Expense</span>
            </a>

            <!-- Tracking -->
            <a href="{{ route('cylinders.tracking') }}" 
               class="nav-link {{ Str::startsWith($currentRoute, 'cylinders.tracking') ? 'active' : '' }}">
                <i class="fas fa-search-location"></i>
                <span>Track Cylinders</span>
                <span class="badge" style="background: #fef3c7; color: #92400e;">
                    {{ \App\Models\Cylinder::sum('issued_quantity') }}
                </span>
            </a>

            <!-- History -->
            <a href="{{ route('cylinders.history') }}" 
               class="nav-link {{ Str::startsWith($currentRoute, 'cylinders.history') ? 'active' : '' }}">
                <i class="fas fa-history"></i>
                <span>History Tracking</span>
                <span class="badge" style="background: #dbeafe; color: #1e40af;">
                    {{ \App\Models\CylinderTransaction::count() }}
                </span>
            </a>
                        
            <!-- Reports -->
            <a href="#" class="nav-link">
                <i class="fas fa-chart-bar"></i>
                <span>Reports</span>
            </a>
            
            <!-- Divider -->
            <div class="border-t border-gray-200 my-4"></div>
            
            <!-- Settings -->
            <a href="{{ route('profile.index') }}" 
               class="nav-link {{ Str::startsWith($currentRoute, 'profile.') ? 'active' : '' }}">
                <i class="fas fa-cog"></i>
                <span>Settings</span>
            </a>
            
            <!-- Logout -->
            <form method="POST" action="{{ route('logout') }}" class="mt-2">
                @csrf
                <button type="submit" class="nav-link w-full text-red-600 hover:bg-red-50 hover:text-red-700">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </button>
            </form>
            
        </nav>
        
        <!-- Footer -->
        <div class="px-6 py-3 border-t border-gray-200 bg-gray-50 text-center">
            <p class="text-xs text-gray-400">© {{ date('Y') }} Medical Gas ERP</p>
            <p class="text-xs text-gray-400">Version 1.0</p>
        </div>
    </div>
</aside>