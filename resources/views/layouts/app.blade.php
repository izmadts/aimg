<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow, noarchive">
    <title>@yield('title', 'Dashboard') - {{ \App\Models\Setting::get('company_name', config('app.name', 'ERP System')) }}</title>
    @php $favicon = \App\Models\Setting::url('favicon'); @endphp
    @if($favicon)
        <link rel="icon" type="image/png" href="{{ $favicon }}">
    @endif

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Tailwind CSS CDN (Fallback) -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- Custom CSS -->
    <style>
        /* ============================================
           SIDEBAR STYLES
           ============================================ */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 280px;
            z-index: 50;
            background: #ffffff;
            border-right: 1px solid #e5e7eb;
            transform: translateX(-100%);
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            overflow-y: auto;
            overflow-x: hidden;
            box-shadow: 4px 0 10px rgba(0, 0, 0, 0.05);
        }

        .sidebar.open {
            transform: translateX(0);
        }

        /* Desktop: Always visible */
        @media (min-width: 1024px) {
            .sidebar {
                transform: translateX(0);
                position: relative;
                width: 280px;
                box-shadow: 2px 0 8px rgba(0, 0, 0, 0.06);
                transition: width 0.2s ease-in-out;
            }

            .sidebar-overlay {
                display: none !important;
            }

            /* Collapsed: icon-only rail. Labels/badges/section headers hide;
               each link keeps its text as a title attribute for a native tooltip. */
            .sidebar.collapsed {
                width: 80px;
            }

            .sidebar.collapsed .logo-text,
            .sidebar.collapsed .user-info-text,
            .sidebar.collapsed .nav-section-label,
            .sidebar.collapsed .nav-link span {
                display: none;
            }

            .sidebar.collapsed .nav-link {
                justify-content: center;
                padding-left: 0.5rem;
                padding-right: 0.5rem;
            }

            .sidebar.collapsed .flex.items-center.justify-between.px-6 {
                justify-content: center;
            }

            .sidebar.collapsed .flex.items-center.space-x-3 {
                justify-content: center;
            }
        }

        /* Mobile: Full height with overlay */
        @media (max-width: 1023px) {
            .sidebar {
                width: 300px;
                max-width: 85vw;
                box-shadow: 8px 0 30px rgba(0, 0, 0, 0.15);
            }
        }

        /* Sidebar Scrollbar */
        .sidebar::-webkit-scrollbar {
            width: 4px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: transparent;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: #d1d5db;
            border-radius: 4px;
        }

        .sidebar::-webkit-scrollbar-thumb:hover {
            background: #9ca3af;
        }

        /* ============================================
           SIDEBAR NAVIGATION LINKS
           ============================================ */
        .nav-link {
            position: relative;
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            margin: 0.125rem 0;
            border-radius: 0.5rem;
            color: #6b7280;
            font-weight: 500;
            font-size: 0.875rem;
            transition: all 0.2s ease-in-out;
            text-decoration: none;
            overflow: hidden;
            cursor: pointer;
        }

        .nav-link i {
            width: 1.25rem;
            text-align: center;
            font-size: 1rem;
            color: #9ca3af;
            transition: color 0.2s;
            flex-shrink: 0;
        }

        .nav-link span {
            margin-left: 0.75rem;
            white-space: nowrap;
        }

        .nav-link .badge {
            margin-left: auto;
            background: #eff6ff;
            color: #1d4ed8;
            font-size: 0.65rem;
            font-weight: 700;
            padding: 0.125rem 0.625rem;
            border-radius: 9999px;
            transition: all 0.2s;
        }

        .nav-link::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 3px;
            background: #3b82f6;
            transform: scaleY(0);
            transition: transform 0.2s ease-in-out;
            border-radius: 0 2px 2px 0;
        }

        .nav-link:hover {
            background: #f3f4f6;
            color: #111827;
        }

        .nav-link:hover i {
            color: #4b5563;
        }

        .nav-link:hover::before {
            transform: scaleY(0.6);
        }

        .nav-link.active {
            background: #eff6ff;
            color: #1d4ed8;
        }

        .nav-link.active i {
            color: #2563eb;
        }

        .nav-link.active::before {
            transform: scaleY(1);
        }

        .nav-link.active .badge {
            background: #dbeafe;
            color: #1d4ed8;
        }

        /* ============================================
           SIDEBAR OVERLAY
           ============================================ */
        .sidebar-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 40;
            display: none;
        }

        .sidebar-overlay.active {
            display: block;
        }

        /* ============================================
           TOP NAVIGATION
           ============================================ */
        .top-nav {
            height: 4rem;
            flex-shrink: 0;
        }

        /* ============================================
           STAT CARDS
           ============================================ */
        .stat-card {
            background: #ffffff;
            border-radius: 0.75rem;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
            border-left: 4px solid #3b82f6;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            transform: translateY(-2px);
        }

        .stat-card .stat-icon {
            width: 3rem;
            height: 3rem;
            border-radius: 9999px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }

        /* ============================================
           ALERTS / NOTIFICATIONS
           ============================================ */
        .alert {
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            border-left-width: 4px;
            margin-bottom: 1rem;
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
        }

        .alert-success {
            background: #f0fdf4;
            border-color: #22c55e;
            color: #166534;
        }

        .alert-danger {
            background: #fef2f2;
            border-color: #ef4444;
            color: #991b1b;
        }

        .alert-warning {
            background: #fffbeb;
            border-color: #f59e0b;
            color: #92400e;
        }

        .alert-info {
            background: #eff6ff;
            border-color: #3b82f6;
            color: #1e40af;
        }

        /* ============================================
           UTILITIES
           ============================================ */
        .truncate {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        /* Smooth transitions */
        a, button, input, select, textarea {
            transition: all 0.15s ease-in-out;
        }

        /* Focus outline */
        *:focus-visible {
            outline: 2px solid #3b82f6;
            outline-offset: 2px;
        }

        /* ============================================
           PRINT STYLES
           ============================================ */
        @media print {
            .sidebar,
            .top-nav,
            .no-print {
                display: none !important;
            }
            
            .main-content {
                margin-left: 0 !important;
                padding: 1rem !important;
            }
        }

        /* ============================================
           ANIMATIONS
           ============================================ */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in {
            animation: fadeIn 0.3s ease-out;
        }

        /* ============================================
           FORM & BUTTON VISIBILITY
           Tailwind's default gray-300 border reads as washed-out on this
           build — give editable fields, checkboxes, and neutral/secondary
           buttons a real, visible colored border instead of flat gray.
           Disabled/readonly fields are deliberately left plain gray so
           "not editable" still reads differently from "editable".
           ============================================ */
        input[type="text"]:not(:disabled):not([readonly]),
        input[type="number"]:not(:disabled):not([readonly]),
        input[type="email"]:not(:disabled):not([readonly]),
        input[type="password"]:not(:disabled):not([readonly]),
        input[type="date"]:not(:disabled):not([readonly]),
        input[type="datetime-local"]:not(:disabled):not([readonly]),
        input[type="tel"]:not(:disabled):not([readonly]),
        input[type="url"]:not(:disabled):not([readonly]),
        input[type="search"]:not(:disabled):not([readonly]),
        select:not(:disabled),
        textarea:not(:disabled):not([readonly]) {
            border-color: #93c5fd !important; /* blue-300 */
            border-width: 1.5px !important;
        }

        input[type="text"]:not(:disabled):not([readonly]):focus,
        input[type="number"]:not(:disabled):not([readonly]):focus,
        input[type="email"]:not(:disabled):not([readonly]):focus,
        input[type="password"]:not(:disabled):not([readonly]):focus,
        input[type="date"]:not(:disabled):not([readonly]):focus,
        input[type="datetime-local"]:not(:disabled):not([readonly]):focus,
        input[type="tel"]:not(:disabled):not([readonly]):focus,
        input[type="url"]:not(:disabled):not([readonly]):focus,
        input[type="search"]:not(:disabled):not([readonly]):focus,
        select:not(:disabled):focus,
        textarea:not(:disabled):not([readonly]):focus {
            border-color: #2563eb !important; /* blue-600 */
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15) !important;
        }

        input[type="checkbox"]:not(:disabled),
        input[type="radio"]:not(:disabled) {
            border-color: #60a5fa !important; /* blue-400 */
            border-width: 1.5px !important;
        }

        /* Neutral "Cancel / Reset / Back" buttons: give them a real border
           accent instead of a flat gray fill with no definition. Scoped to
           .rounded-lg so the small bg-gray-300 toggle-switch track (rounded-full)
           used elsewhere is left untouched. */
        .bg-gray-200.rounded-lg,
        .bg-gray-300.rounded-lg,
        .bg-gray-400.rounded-lg {
            border: 1.5px solid #94a3b8 !important; /* slate-400 */
        }
        .bg-gray-200.rounded-lg:hover,
        .bg-gray-300.rounded-lg:hover,
        .bg-gray-400.rounded-lg:hover {
            border-color: #64748b !important; /* slate-500 */
        }
    </style>
    
    @stack('styles')
</head>
<body class="font-sans antialiased bg-gray-100">
    <div id="app" class="flex h-screen overflow-hidden">
        
        <!-- Sidebar -->
        @include('layouts.sidebar')
        
        <!-- Sidebar Overlay -->
        <div id="sidebarOverlay" class="sidebar-overlay"></div>
        
        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col overflow-hidden min-w-0">
            
            <!-- Top Navigation -->
            @include('layouts.navigation')
            
            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto p-4 md:p-6 bg-gray-50">
                
                <!-- Page Header -->
                @if(isset($header))
                    <div class="mb-6">
                        <h1 class="text-2xl font-bold text-gray-900">{{ $header }}</h1>
                    </div>
                @endif
                
                <!-- Success Message -->
                @if(session('success'))
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle mt-0.5"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif
                
                <!-- Error Message -->
                @if(session('error'))
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle mt-0.5"></i>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif
                
                <!-- Main Content -->
                @yield('content')
                
            </main>
        </div>
    </div>
    
    <!-- JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            
            // Toggle sidebar (for mobile)
            window.toggleSidebar = function() {
                sidebar.classList.toggle('open');
                overlay.classList.toggle('active');
                document.body.classList.toggle('overflow-hidden');
            };
            
            // Close sidebar
            window.closeSidebar = function() {
                sidebar.classList.remove('open');
                overlay.classList.remove('active');
                document.body.classList.remove('overflow-hidden');
            };
            
            // Open sidebar
            window.openSidebar = function() {
                sidebar.classList.add('open');
                overlay.classList.add('active');
                document.body.classList.add('overflow-hidden');
            };
            
            // Overlay click to close
            if (overlay) {
                overlay.addEventListener('click', closeSidebar);
            }
            
            // Escape key to close
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') closeSidebar();
            });
            
            // Close on nav link click (mobile)
            document.querySelectorAll('.nav-link').forEach(function(link) {
                link.addEventListener('click', function() {
                    if (window.innerWidth < 1024) {
                        closeSidebar();
                    }
                });
            });
            
            // Handle window resize
            let resizeTimer;
            window.addEventListener('resize', function() {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(function() {
                    if (window.innerWidth >= 1024) {
                        closeSidebar();
                    }
                }, 250);
            });
        });
    </script>
    
    @stack('scripts')
</body>
</html>