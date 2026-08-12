@php
    $currentRoute = request()->route()->getName();
    $companyName = \App\Models\Setting::get('company_name', config('app.name', 'ERP System'));
    $companyLogoUrl = \App\Models\Setting::url('company_logo');
@endphp

<aside id="sidebar" class="sidebar">
    <div class="flex flex-col h-full">

        <!-- Logo -->
        <div class="flex items-center justify-between px-6 py-5 border-b border-gray-200 bg-gradient-to-r from-blue-600 to-indigo-600">
            <div class="flex items-center space-x-3">
                <div class="bg-white/20 rounded-lg p-2 w-10 h-10 flex items-center justify-center overflow-hidden shrink-0">
                    @if($companyLogoUrl)
                        <img src="{{ $companyLogoUrl }}" alt="{{ $companyName }}" class="w-full h-full object-contain">
                    @else
                        <i class="fas fa-gas-pump text-white text-xl"></i>
                    @endif
                </div>
                <div class="logo-text">
                    <h1 class="text-white font-bold text-lg leading-tight">{{ $companyName }}</h1>
                    <p class="text-blue-100 text-xs">Medical Gas ERP</p>
                </div>
            </div>
            <button type="button" onclick="toggleSidebarCollapse()" class="hidden lg:block text-white hover:text-blue-100" title="Collapse sidebar">
                <i id="sidebarCollapseIcon" class="fas fa-angle-left text-xl"></i>
            </button>
            <button type="button" onclick="closeSidebar()" class="lg:hidden text-white hover:text-blue-100">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <!-- User Info -->
        <div class="px-4 py-4 border-b border-gray-200 bg-gray-50">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-full bg-gradient-to-r from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                    {{ Auth::user()->initials ?? 'U' }}
                </div>
                <div class="flex-1 min-w-0 user-info-text">
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

            @canany(['sales.view', 'purchases.view', 'gas_products.view', 'cylinders.view'])
            <p class="nav-section-label px-4 pt-4 pb-1 text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Operations</p>
            @endcanany

            @can('sales.view')
            <!-- Sales -->
            <a href="{{ route('sales.index') }}"
               class="nav-link {{ Str::startsWith($currentRoute, 'sales.') ? 'active' : '' }}">
                <i class="fas fa-file-invoice"></i>
                <span>Sales</span>
                <span class="badge">{{ \App\Models\Sale::count() }}</span>
            </a>
            @endcan

            @can('purchases.view')
            <!-- Purchases -->
            <a href="{{ route('purchases.index') }}"
               class="nav-link {{ Str::startsWith($currentRoute, 'purchases.') ? 'active' : '' }}">
                <i class="fas fa-shopping-cart"></i>
                <span>Purchases</span>
                <span class="badge">{{ \App\Models\Purchase::count() }}</span>
            </a>
            @endcan

            @can('gas_products.view')
            <!-- Gas Products -->
            <a href="{{ route('gas-products.index') }}"
               class="nav-link {{ Str::startsWith($currentRoute, 'gas-products.') ? 'active' : '' }}">
                <i class="fas fa-flask"></i>
                <span>Gas Products</span>
                <span class="badge">{{ \App\Models\GasProduct::count() }}</span>
            </a>
            @endcan

            @can('cylinders.view')
            <!-- Cylinders -->
            <a href="{{ route('cylinders.index') }}"
               class="nav-link {{ Str::startsWith($currentRoute, 'cylinders.') ? 'active' : '' }}">
                <i class="fas fa-gas-pump"></i>
                <span>Cylinders</span>
                <span class="badge" title="Total cylinder types">
                    {{ \App\Models\Cylinder::count() }}
                </span>
                @php $issuedCount = \App\Models\Cylinder::sum('issued_quantity'); @endphp
                @if($issuedCount > 0)
                <span class="badge" style="background: #fef3c7; color: #92400e; margin-left: 0.25rem;" title="Cylinders currently issued to customers">
                    {{ $issuedCount }} out
                </span>
                @endif
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
            @endcan

            @canany(['customers.view', 'suppliers.view'])
            <p class="nav-section-label px-4 pt-4 pb-1 text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Parties</p>
            @endcanany

            @can('customers.view')
            <!-- Customers -->
            <a href="{{ route('customers.index') }}"
               class="nav-link {{ Str::startsWith($currentRoute, 'customers.') ? 'active' : '' }}">
                <i class="fas fa-users"></i>
                <span>Customers</span>
                <span class="badge">{{ \App\Models\Customer::count() }}</span>
            </a>
            @endcan

            @can('suppliers.view')
            <!-- Suppliers -->
            <a href="{{ route('suppliers.index') }}"
               class="nav-link {{ Str::startsWith($currentRoute, 'suppliers.') ? 'active' : '' }}">
                <i class="fas fa-truck"></i>
                <span>Suppliers</span>
                <span class="badge">{{ \App\Models\Supplier::count() }}</span>
            </a>
            @endcan

            @canany(['accounting.view', 'income_expense.view'])
            <p class="nav-section-label px-4 pt-4 pb-1 text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Finance</p>
            @endcanany

            @can('accounting.view')
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
            @endcan

            @can('income_expense.view')
            <!-- Income & Expense -->
            <a href="{{ route('income-expense.index') }}"
               class="nav-link {{ Str::startsWith($currentRoute, 'income-expense.') ? 'active' : '' }}">
                <i class="fas fa-coins"></i>
                <span>Income/Expense</span>
            </a>
            @endcan

            @can('hrm.view')
            <p class="nav-section-label px-4 pt-4 pb-1 text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Human Resources</p>
            <!-- HRM -->
                <a href="{{ route('hrm.employees') }}"
                   class="nav-link {{ Str::startsWith($currentRoute, 'hrm.') ? 'active' : '' }}">
                    <i class="fas fa-users-cog"></i>
                    <span>HRM</span>
                </a>
            @endcan

            @canany(['users.manage', 'roles.manage', 'units.manage', 'cylinder_types.manage', 'settings.manage'])
            <p class="nav-section-label px-4 pt-4 pb-1 text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Administration</p>
            @endcanany

            @can('users.manage')
            <!-- Users -->
            <a href="{{ route('users.index') }}"
               class="nav-link {{ Str::startsWith($currentRoute, 'users.') ? 'active' : '' }}">
                <i class="fas fa-user-cog"></i>
                <span>Users</span>
            </a>
            @endcan

            @can('roles.manage')
            <!-- Roles & Permissions -->
            <a href="{{ route('roles.index') }}"
               class="nav-link {{ Str::startsWith($currentRoute, 'roles.') ? 'active' : '' }}">
                <i class="fas fa-user-shield"></i>
                <span>Roles & Permissions</span>
            </a>
            @endcan

            @can('units.manage')
            <!-- Units -->
            <a href="{{ route('units.index') }}"
               class="nav-link {{ Str::startsWith($currentRoute, 'units.') ? 'active' : '' }}">
                <i class="fas fa-ruler"></i>
                <span>Units</span>
                <span class="badge">{{ \App\Models\Unit::count() }}</span>
            </a>
            @endcan

            @can('cylinder_types.manage')
            <!-- Cylinder Types -->
            <a href="{{ route('cylinder-types.index') }}"
               class="nav-link {{ Str::startsWith($currentRoute, 'cylinder-types.') ? 'active' : '' }}">
                <i class="fas fa-shapes"></i>
                <span>Cylinder Types</span>
                <span class="badge">{{ \App\Models\CylinderType::count() }}</span>
            </a>
            @endcan

            @can('settings.manage')
            <!-- System Settings -->
            <a href="{{ route('settings.index') }}"
               class="nav-link {{ Str::startsWith($currentRoute, 'settings.') ? 'active' : '' }}">
                <i class="fas fa-sliders-h"></i>
                <span>System Settings</span>
            </a>
            @endcan

            <!-- Divider -->
            <div class="border-t border-gray-200 my-4"></div>

            <!-- Help -->
            <a href="{{ route('help.index') }}"
               class="nav-link {{ Str::startsWith($currentRoute, 'help.') ? 'active' : '' }}">
                <i class="fas fa-circle-question"></i>
                <span>Help / User Guide</span>
            </a>

            <!-- Settings (personal account) -->
            <a href="{{ route('profile.index') }}"
               class="nav-link {{ Str::startsWith($currentRoute, 'profile.') ? 'active' : '' }}">
                <i class="fas fa-cog"></i>
                <span>My Account</span>
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

@push('scripts')
<script>
    (function () {
        const sidebar = document.getElementById('sidebar');
        const icon = document.getElementById('sidebarCollapseIcon');

        function applyTooltips() {
            sidebar.querySelectorAll('.nav-link').forEach(function (link) {
                if (link.hasAttribute('title')) return;
                const label = link.querySelector('span:not(.badge)');
                if (label) link.setAttribute('title', label.textContent.trim());
            });
        }

        function setCollapsed(collapsed) {
            sidebar.classList.toggle('collapsed', collapsed);
            if (icon) icon.classList.toggle('fa-angle-left', !collapsed);
            if (icon) icon.classList.toggle('fa-angle-right', collapsed);
            localStorage.setItem('sidebarCollapsed', collapsed ? '1' : '0');
        }

        window.toggleSidebarCollapse = function () {
            setCollapsed(!sidebar.classList.contains('collapsed'));
        };

        applyTooltips();
        setCollapsed(window.innerWidth >= 1024 && localStorage.getItem('sidebarCollapsed') === '1');
    })();
</script>
@endpush