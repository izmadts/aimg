<nav class="top-nav bg-white border-b border-gray-200 shadow-sm">
    <div class="px-4 sm:px-6 lg:px-8 h-full flex items-center justify-between">
        
        <!-- Left: Mobile Menu -->
        <div class="flex items-center lg:hidden">
            <button onclick="openSidebar()" class="mobile-toggle">
                <i class="fas fa-bars text-xl"></i>
            </button>
            <span class="ml-3 text-sm font-semibold text-gray-700">ERP System</span>
        </div>
        
        <!-- Left: Breadcrumb (Desktop) -->
        <div class="hidden lg:flex items-center text-sm text-gray-500">
            <i class="fas fa-home mr-2 text-gray-400"></i>
            <span>{{ ucfirst(str_replace('.', ' / ', request()->route()->getName() ?? 'Dashboard')) }}</span>
        </div>
        
        <!-- Right -->
        <div class="flex items-center space-x-4">
            
            <!-- Notifications -->
            <button type="button" class="relative text-gray-400 hover:text-gray-600">
                <i class="fas fa-bell text-xl"></i>
                <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs font-bold rounded-full h-4 w-4 flex items-center justify-center">3</span>
            </button>

            <!-- System Health Check (admin only) -->
            @if(Auth::user()->isAdmin())
                <a href="{{ route('system-health.index') }}" title="System Health Check"
                   class="relative text-gray-400 hover:text-red-500 transition-colors">
                    <i class="fas fa-heart-pulse text-xl"></i>
                </a>
            @endif
            
            <!-- Quick Actions -->
            <div class="hidden md:flex items-center space-x-2">
                <a href="{{ route('sales.create') }}" 
                   class="bg-green-600 hover:bg-green-700 text-white text-sm font-medium px-3 py-1.5 rounded-lg transition-colors">
                    <i class="fas fa-plus mr-1"></i> New Sale
                </a>
                <a href="{{ route('purchases.create') }}" 
                   class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-3 py-1.5 rounded-lg transition-colors">
                    <i class="fas fa-plus mr-1"></i> New Purchase
                </a>
            </div>
            
            <!-- User Dropdown (Simple without Alpine.js) -->
            <div class="relative" id="userDropdown">
                <button onclick="toggleDropdown()" 
                        class="flex items-center space-x-3 text-sm focus:outline-none group">
                    <div class="w-8 h-8 rounded-full bg-gradient-to-r from-blue-500 to-indigo-600 flex items-center justify-center text-white font-semibold text-sm">
                        {{ Auth::user()->initials ?? 'U' }}
                    </div>
                    <span class="hidden md:inline text-gray-700 font-medium">{{ Auth::user()->name }}</span>
                    <i class="fas fa-chevron-down text-gray-400 text-xs" id="dropdownArrow"></i>
                </button>
                
                <div id="dropdownMenu" 
                     class="hidden absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50">
                    <a href="{{ route('profile.index') }}" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-100">
                        <i class="fas fa-user w-5 text-gray-400"></i>
                        <span class="ml-3">My Profile</span>
                    </a>
                    <a href="{{ route('profile.edit') }}" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-100">
                        <i class="fas fa-edit w-5 text-gray-400"></i>
                        <span class="ml-3">Edit Profile</span>
                    </a>
                    <a href="{{ route('profile.change-password') }}" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-100">
                        <i class="fas fa-key w-5 text-gray-400"></i>
                        <span class="ml-3">Change Password</span>
                    </a>
                    <hr class="my-1 border-gray-200">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex items-center w-full text-left px-4 py-2.5 text-sm text-red-600 hover:bg-red-50">
                            <i class="fas fa-sign-out-alt w-5 text-red-400"></i>
                            <span class="ml-3">Logout</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</nav>

<script>
    // Simple dropdown toggle without Alpine.js
    function toggleDropdown() {
        const menu = document.getElementById('dropdownMenu');
        const arrow = document.getElementById('dropdownArrow');
        
        if (menu.classList.contains('hidden')) {
            menu.classList.remove('hidden');
            arrow.style.transform = 'rotate(180deg)';
        } else {
            menu.classList.add('hidden');
            arrow.style.transform = 'rotate(0deg)';
        }
    }
    
    // Close dropdown on outside click
    document.addEventListener('click', function(e) {
        const dropdown = document.getElementById('userDropdown');
        if (dropdown && !dropdown.contains(e.target)) {
            const menu = document.getElementById('dropdownMenu');
            const arrow = document.getElementById('dropdownArrow');
            if (menu) {
                menu.classList.add('hidden');
                if (arrow) arrow.style.transform = 'rotate(0deg)';
            }
        }
    });
</script>