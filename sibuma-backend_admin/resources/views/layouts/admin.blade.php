<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - SIBUMA Admin</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#2563EB',
                        secondary: '#10B981',
                        accent: '#F59E0B',
                    }
                }
            }
        }
    </script>

    <style>
        * { font-family: 'Inter', sans-serif; }
        h1, h2, h3, h4 { font-family: 'Plus Jakarta Sans', sans-serif; }

        body {
            background: linear-gradient(135deg, #1e3a8a 0%, #1D4ED8 60%, #2563EB 100%);
            min-height: 100vh;
        }
        .sidebar-active {
            background: linear-gradient(135deg, #2563EB 0%, #1D4ED8 100%);
            color: white;
        }
        .card-hover:hover { transform: translateY(-4px); box-shadow: 0 20px 25px -5px rgba(0,0,0,0.15); }
        .gradient-primary { background: linear-gradient(135deg, #2563EB 0%, #1D4ED8 100%); }
        .gradient-success { background: linear-gradient(135deg, #10B981 0%, #059669 100%); }
        .gradient-warning { background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%); }
        .gradient-info    { background: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%); }

        main > header h2 { color: #ffffff; letter-spacing: -0.3px; }
        main > header p  { color: #bfdbfe; font-size: 0.95rem; }

        #sidebar-overlay {
            display: none;
            position: fixed; inset: 0;
            background: rgba(0,0,0,0.5);
            z-index: 40;
        }
        #sidebar-overlay.active { display: block; }

        @media (max-width: 767px) {
            #sidebar { transform: translateX(-100%); transition: transform 0.3s ease; }
            #sidebar.open { transform: translateX(0); }
            #main-content { margin-left: 0 !important; padding: 1rem !important; padding-bottom: 5rem !important; }
            .mobile-topbar { display: flex !important; }
            .desktop-header { display: none; }
        }
        @media (min-width: 768px) {
            .mobile-topbar { display: none !important; }
            .mobile-bottom-nav { display: none !important; }
        }

        .table-responsive { overflow-x: auto; -webkit-overflow-scrolling: touch; }

        @media (max-width: 640px) {
            .stats-grid { grid-template-columns: 1fr 1fr !important; gap: 0.75rem !important; }
        }
    </style>

    @yield('styles')
</head>
<body>

<div id="sidebar-overlay" onclick="closeSidebar()"></div>

<!-- Mobile Top Bar -->
<div class="mobile-topbar fixed top-0 left-0 right-0 z-30 gradient-primary px-4 py-3 flex items-center justify-between shadow-lg">
    <button onclick="openSidebar()" class="text-white text-xl w-9 h-9 flex items-center justify-center rounded-lg bg-white bg-opacity-20">
        <i class="fas fa-bars"></i>
    </button>
    <h1 class="text-white font-extrabold text-lg tracking-tight">SIBUMA</h1>
    <div class="w-9 h-9 rounded-full bg-white bg-opacity-20 flex items-center justify-center">
        <i class="fas fa-user text-white text-sm"></i>
    </div>
</div>

<!-- Sidebar -->
<aside id="sidebar" class="fixed top-0 left-0 h-screen w-64 bg-white shadow-lg z-50 flex flex-col">
    <div class="p-6 gradient-primary flex items-center justify-between flex-shrink-0">
        <div>
            <h1 class="text-2xl font-extrabold text-white tracking-tight">SIBUMA</h1>
            <p class="text-sm text-blue-100 mt-1 font-medium">Admin Dashboard</p>
        </div>
        <button onclick="closeSidebar()" class="md:hidden text-white text-xl bg-white bg-opacity-20 w-8 h-8 rounded-lg flex items-center justify-center">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <nav class="mt-6 px-4 flex-1 overflow-y-auto">
        <a href="{{ route('admin.dashboard') }}" onclick="closeSidebar()"
            class="flex items-center px-4 py-3 mb-2 rounded-lg transition {{ request()->routeIs('admin.dashboard') ? 'sidebar-active' : 'text-gray-700 hover:bg-blue-50' }}">
            <i class="fas fa-home w-5"></i>
            <span class="ml-3 font-medium">Dashboard</span>
        </a>
        <a href="{{ route('admin.usaha') }}" onclick="closeSidebar()"
            class="flex items-center px-4 py-3 mb-2 rounded-lg transition {{ request()->routeIs('admin.usaha*') ? 'sidebar-active' : 'text-gray-700 hover:bg-blue-50' }}">
            <i class="fas fa-store w-5"></i>
            <span class="ml-3 font-medium">Data Usaha</span>
        </a>
        <a href="{{ route('admin.pendapatan') }}" onclick="closeSidebar()"
            class="flex items-center px-4 py-3 mb-2 rounded-lg transition {{ request()->routeIs('admin.pendapatan') ? 'sidebar-active' : 'text-gray-700 hover:bg-blue-50' }}">
            <i class="fas fa-chart-line w-5"></i>
            <span class="ml-3 font-medium">Data Pendapatan</span>
        </a>

        {{-- ===== MENU STATISTIK (DROPDOWN 3 KATEGORI) ===== --}}
        <div class="mb-2">
            <button type="button" onclick="toggleStatistikMenu()"
                class="w-full flex items-center justify-between px-4 py-3 rounded-lg transition {{ request()->routeIs('admin.statistik') ? 'sidebar-active' : 'text-gray-700 hover:bg-blue-50' }}">
                <span class="flex items-center">
                    <i class="fas fa-chart-pie w-5"></i>
                    <span class="ml-3 font-medium">Statistik</span>
                </span>
                <i id="statistik-chevron"
                   class="fas fa-chevron-down text-xs transition-transform duration-200 {{ request()->routeIs('admin.statistik') ? '' : '-rotate-90' }}"></i>
            </button>

            <div id="statistik-submenu"
                 class="{{ request()->routeIs('admin.statistik') ? '' : 'hidden' }} mt-1 ml-4 pl-3 border-l-2 border-blue-100 space-y-1">

                <a href="{{ route('admin.statistik') }}" onclick="closeSidebar()"
                   class="flex items-center px-3 py-2 rounded-lg text-sm transition {{ request()->routeIs('admin.statistik') && !request('kategori') ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-gray-500 hover:bg-blue-50' }}">
                    <i class="fas fa-layer-group w-4 text-xs"></i>
                    <span class="ml-2">Semua Kategori</span>
                </a>

                <a href="{{ route('admin.statistik', ['kategori' => 'industri']) }}" onclick="closeSidebar()"
                   class="flex items-center px-3 py-2 rounded-lg text-sm transition {{ request('kategori') === 'industri' ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-gray-500 hover:bg-blue-50' }}">
                    <i class="fas fa-industry w-4 text-xs"></i>
                    <span class="ml-2">Industri</span>
                </a>

                <a href="{{ route('admin.statistik', ['kategori' => 'perdagangan']) }}" onclick="closeSidebar()"
                   class="flex items-center px-3 py-2 rounded-lg text-sm transition {{ request('kategori') === 'perdagangan' ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-gray-500 hover:bg-blue-50' }}">
                    <i class="fas fa-shop w-4 text-xs"></i>
                    <span class="ml-2">Perdagangan</span>
                </a>

                <a href="{{ route('admin.statistik', ['kategori' => 'jasa']) }}" onclick="closeSidebar()"
                   class="flex items-center px-3 py-2 rounded-lg text-sm transition {{ request('kategori') === 'jasa' ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-gray-500 hover:bg-blue-50' }}">
                    <i class="fas fa-handshake w-4 text-xs"></i>
                    <span class="ml-2">Jasa</span>
                </a>

            </div>
        </div>

        <a href="{{ route('admin.catatan') }}" onclick="closeSidebar()"
            class="flex items-center px-4 py-3 mb-2 rounded-lg transition {{ request()->routeIs('admin.catatan') ? 'sidebar-active' : 'text-gray-700 hover:bg-blue-50' }}">
            <i class="fas fa-comment-dots w-5"></i>
            <span class="ml-3 font-medium">Catatan & Keluhan</span>
        </a>

        <div class="border-t border-gray-200 my-4"></div>

        <form action="{{ route('admin.logout') }}" method="POST">
            @csrf
            <button type="submit" class="flex items-center w-full px-4 py-3 mb-2 rounded-lg text-red-600 hover:bg-red-50 transition">
                <i class="fas fa-sign-out-alt w-5"></i>
                <span class="ml-3 font-medium">Logout</span>
            </button>
        </form>
    </nav>

    <div class="flex-shrink-0 p-4 border-t border-gray-200">
        <div class="flex items-center">
            <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-user text-blue-600"></i>
            </div>
            <div class="ml-3 min-w-0">
                <p class="text-sm font-semibold text-gray-800 truncate">{{ Auth::user()->name }}</p>
                <p class="text-xs text-gray-500">Administrator</p>
            </div>
        </div>
    </div>
</aside>

<!-- Main Content -->
<main id="main-content" class="md:ml-64 p-8 pt-8 md:pt-8">
    <header class="desktop-header mb-8 hidden md:block">
        <h2 class="text-3xl font-bold">@yield('page-title')</h2>
        <p class="mt-1">@yield('page-description')</p>
    </header>

    <div class="md:hidden pt-16 mb-4">
        <h2 class="text-xl font-bold text-white">@yield('page-title')</h2>
        <p class="text-sm text-blue-200 mt-0.5">@yield('page-description')</p>
    </div>

    @yield('content')
</main>

<!-- Mobile Bottom Navigation -->
<nav class="mobile-bottom-nav fixed bottom-0 left-0 right-0 z-30 bg-white border-t border-gray-200 flex md:hidden shadow-lg">
    <a href="{{ route('admin.dashboard') }}" class="flex-1 flex flex-col items-center py-2 {{ request()->routeIs('admin.dashboard') ? 'text-blue-600' : 'text-gray-500' }}">
        <i class="fas fa-home text-lg"></i>
        <span class="text-xs mt-1 font-medium">Dashboard</span>
    </a>
    <a href="{{ route('admin.usaha') }}" class="flex-1 flex flex-col items-center py-2 {{ request()->routeIs('admin.usaha*') ? 'text-blue-600' : 'text-gray-500' }}">
        <i class="fas fa-store text-lg"></i>
        <span class="text-xs mt-1 font-medium">Usaha</span>
    </a>
    <a href="{{ route('admin.pendapatan') }}" class="flex-1 flex flex-col items-center py-2 {{ request()->routeIs('admin.pendapatan') ? 'text-blue-600' : 'text-gray-500' }}">
        <i class="fas fa-chart-line text-lg"></i>
        <span class="text-xs mt-1 font-medium">Pendapatan</span>
    </a>
    <a href="{{ route('admin.statistik') }}" class="flex-1 flex flex-col items-center py-2 {{ request()->routeIs('admin.statistik') ? 'text-blue-600' : 'text-gray-500' }}">
        <i class="fas fa-chart-pie text-lg"></i>
        <span class="text-xs mt-1 font-medium">Statistik</span>
    </a>
    <a href="{{ route('admin.catatan') }}" class="flex-1 flex flex-col items-center py-2 {{ request()->routeIs('admin.catatan') ? 'text-blue-600' : 'text-gray-500' }}">
        <i class="fas fa-comment-dots text-lg"></i>
        <span class="text-xs mt-1 font-medium">Catatan</span>
    </a>
</nav>

@yield('scripts')

<script>
function openSidebar() {
    document.getElementById('sidebar').classList.add('open');
    document.getElementById('sidebar-overlay').classList.add('active');
    document.body.style.overflow = 'hidden';
}
function closeSidebar() {
    document.getElementById('sidebar').classList.remove('open');
    document.getElementById('sidebar-overlay').classList.remove('active');
    document.body.style.overflow = '';
}
function toggleStatistikMenu() {
    document.getElementById('statistik-submenu').classList.toggle('hidden');
    document.getElementById('statistik-chevron').classList.toggle('-rotate-90');
}
</script>
</body>
</html>