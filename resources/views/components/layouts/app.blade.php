<!DOCTYPE html>
<html 
    lang="id" 
    x-data="{ 
        theme: localStorage.getItem('theme') || 'dark',
        toggleTheme() {
            this.theme = this.theme === 'dark' ? 'light' : 'dark';
            localStorage.setItem('theme', this.theme);
            this.syncFlatpickr();
        },
        syncFlatpickr() {
            const darkStyle = document.getElementById('flatpickr-dark-theme');
            if (darkStyle) darkStyle.disabled = (this.theme !== 'dark');
        }
    }"
    x-init="syncFlatpickr()"
    :class="theme"
    class="h-full"
>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'POS Toko Duta Sae' }}</title>
    <link rel="icon" type="image/png" href="{{ asset('icon.png') }}">
    <!-- Flatpickr Date Picker CDN & Themes -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link id="flatpickr-dark-theme" rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/dark.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://npmcdn.com/flatpickr/dist/l10n/id.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="h-full bg-slate-100 dark:bg-slate-900 text-slate-800 dark:text-slate-100 font-sans antialiased selection:bg-amber-500 selection:text-slate-950 transition-colors duration-200">

    <!-- Reusable SweetAlert2 Popup Component -->
    <x-ui.swal />

    <!-- Top Navigation Bar -->
    <nav x-data="{ mobileMenuOpen: false }" class="bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700/80 sticky top-0 z-40 shadow-sm transition-colors duration-200">
        <div class="w-full max-w-full px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 gap-3 sm:gap-4">

                <!-- Left Brand & Mobile Hamburger Toggle -->
                <div class="flex items-center gap-3 lg:gap-6 min-w-0">
                    <!-- Mobile Hamburger Toggle Button (Mobile & Tablet) -->
                    <button 
                        @click="mobileMenuOpen = !mobileMenuOpen" 
                        class="lg:hidden p-2 text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition cursor-pointer"
                        title="Buka Menu Navigasi"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path x-show="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16M4 18h16"></path>
                            <path x-show="mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" style="display: none;"></path>
                        </svg>
                    </button>

                    <a href="{{ route('pos') }}" class="flex items-center gap-2 font-black text-xl tracking-tight text-slate-900 dark:text-white shrink-0">
                        <span class="bg-amber-500 text-slate-950 px-2.5 py-1 rounded-xl text-sm font-black shadow-sm">DS</span>
                        <span class="hidden sm:inline">Toko Duta Sae</span>
                    </a>

                    <div class="hidden lg:flex items-center gap-1 xl:gap-2 whitespace-nowrap">
                        <!-- POS Screen -->
                        <a href="{{ route('pos') }}" class="px-3 py-2 rounded-xl text-xs xl:text-sm font-extrabold transition {{ request()->routeIs('pos') ? 'bg-amber-500 text-slate-950 shadow-sm' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700' }}">
                            Kasir POS
                        </a>

                        <!-- Buku Piutang Pelanggan -->
                        <a href="{{ route('receivables.index') }}" class="px-3 py-2 rounded-xl text-xs xl:text-sm font-extrabold transition {{ request()->routeIs('receivables.*') ? 'bg-amber-500 text-slate-950 shadow-sm' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700' }}">
                            Buku Piutang
                        </a>

                        <!-- Admin-Only Links -->
                        @if (Auth::user()?->isAdmin())
                            <a href="{{ route('dashboard') }}" class="px-3 py-2 rounded-xl text-xs xl:text-sm font-extrabold transition {{ request()->routeIs('dashboard') ? 'bg-amber-500 text-slate-950 shadow-sm' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700' }}">
                                Dashboard
                            </a>
                            <a href="{{ route('purchases.create') }}" class="px-3 py-2 rounded-xl text-xs xl:text-sm font-extrabold transition {{ request()->routeIs('purchases.create') ? 'bg-amber-500 text-slate-950 shadow-sm' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700' }}">
                                Barang Masuk
                            </a>
                            <a href="{{ route('reports.sales') }}" class="px-3 py-2 rounded-xl text-xs xl:text-sm font-extrabold transition {{ request()->routeIs('reports.sales') ? 'bg-amber-500 text-slate-950 shadow-sm' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700' }}">
                                Laporan Omzet
                            </a>
                            <a href="{{ route('users.index') }}" class="px-3 py-2 rounded-xl text-xs xl:text-sm font-extrabold transition {{ request()->routeIs('users.index') ? 'bg-amber-500 text-slate-950 shadow-sm' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700' }}">
                                Pengguna
                            </a>

                            <!-- Master Data Dropdown -->
                            <div class="relative" x-data="{ open: false }">
                                <button 
                                    @click="open = !open"
                                    class="px-3 py-2 rounded-xl text-xs xl:text-sm font-extrabold text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 flex items-center gap-1 transition {{ request()->routeIs('products.*', 'units.*', 'suppliers.*', 'customers.*') ? 'bg-slate-200 dark:bg-slate-700 text-amber-600 dark:text-amber-400' : '' }}"
                                >
                                    <span>Data Master</span>
                                    <span class="text-[10px]">▼</span>
                                </button>

                                <div 
                                    x-show="open" 
                                    @click.outside="open = false"
                                    x-transition
                                    class="absolute left-0 mt-2 w-48 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-2xl z-50 overflow-hidden divide-y divide-slate-100 dark:divide-slate-700/50"
                                    style="display: none;"
                                >
                                    <a href="{{ route('products.index') }}" class="block px-4 py-3 text-xs font-bold text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-amber-500 transition">
                                        Master Barang
                                    </a>
                                    <a href="{{ route('units.index') }}" class="block px-4 py-3 text-xs font-bold text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-amber-500 transition">
                                        Master Satuan
                                    </a>
                                    <a href="{{ route('suppliers.index') }}" class="block px-4 py-3 text-xs font-bold text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-amber-500 transition">
                                        Master Supplier
                                    </a>
                                    <a href="{{ route('customers.index') }}" class="block px-4 py-3 text-xs font-bold text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-amber-500 transition">
                                        Master Pelanggan
                                    </a>
                                </div>
                            </div>
                        @endif
                      
                    </div>
                </div>

                <!-- Right Actions, Theme Switcher, Notifications & Interactive User Dropdown -->
                <div class="flex items-center gap-2.5 shrink-0">

                    <!-- Dark / Light Mode Toggle Button -->
                    <button 
                        @click="toggleTheme()" 
                        class="px-3 py-1.5 text-slate-700 dark:text-slate-300 hover:text-amber-500 bg-slate-100 dark:bg-slate-900 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-xl border border-slate-300 dark:border-slate-700 transition flex items-center justify-center cursor-pointer text-xs font-bold shadow-sm"
                        title="Ganti Mode Tampilan"
                    >
                        <span x-text="theme === 'dark' ? '☀️ Light' : '🌙 Dark'"></span>
                    </button>

                    <!-- Livewire Notification Bell Dropdown -->
                    @livewire('notifications.dropdown')

                    <!-- Interactive User Profile Dropdown -->
                    @auth
                        <div class="relative" x-data="{ userMenuOpen: false }">
                            <!-- Trigger Button -->
                            <button 
                                @click="userMenuOpen = !userMenuOpen"
                                class="flex items-center gap-2 pl-2 pr-2.5 py-1 rounded-xl bg-slate-100 dark:bg-slate-900 hover:bg-slate-200 dark:hover:bg-slate-750 border border-slate-200 dark:border-slate-700 transition cursor-pointer"
                                title="Klik untuk Buka Profil & Panduan"
                            >
                                <div class="w-8 h-8 rounded-lg bg-amber-500 text-slate-950 flex items-center justify-center font-black text-xs shadow-sm">
                                    {{ Auth::user()->initials() }}
                                </div>
                                <div class="text-left hidden sm:block text-xs leading-tight">
                                    <div class="font-extrabold text-slate-900 dark:text-white truncate max-w-[110px]">{{ Auth::user()->name }}</div>
                                    <div class="font-mono uppercase text-[10px] font-black {{ Auth::user()->isAdmin() ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                                        {{ Auth::user()->isAdmin() ? '👑 Admin' : '👤 Kasir' }}
                                    </div>
                                </div>
                                <span class="text-[10px] text-slate-400">▼</span>
                            </button>

                            <!-- Floating Dropdown Menu -->
                            <div 
                                x-show="userMenuOpen" 
                                @click.outside="userMenuOpen = false" 
                                x-transition
                                class="absolute right-0 mt-2 w-56 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-2xl z-50 overflow-hidden divide-y divide-slate-100 dark:divide-slate-700/50"
                                style="display: none;"
                            >
                                <div class="p-3.5 bg-slate-50 dark:bg-slate-900/60">
                                    <p class="text-xs text-slate-400 font-bold uppercase tracking-wider">Akun Masuk</p>
                                    <p class="font-extrabold text-sm text-slate-900 dark:text-white truncate">{{ Auth::user()->name }}</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 truncate">{{ Auth::user()->email }}</p>
                                </div>

                                <div class="p-1.5 space-y-0.5 text-xs font-bold">
                                    <a href="{{ route('profile.edit') }}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-slate-700 dark:text-slate-200 hover:bg-amber-500/10 hover:text-amber-600 dark:hover:text-amber-400 transition">
                                        <span>👤</span>
                                        <span>Profil Pengguna</span>
                                    </a>
                                    <a href="{{ route('guide.index') }}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-slate-700 dark:text-slate-200 hover:bg-amber-500/10 hover:text-amber-600 dark:hover:text-amber-400 transition">
                                        <span>📖</span>
                                        <span>Panduan Penggunaan Sistem</span>
                                    </a>
                                </div>

                                <div class="p-1.5">
                                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                                        @csrf
                                        <button type="submit" class="w-full flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-bold text-red-600 dark:text-red-400 hover:bg-red-500/10 transition cursor-pointer">
                                            <span>🚪</span>
                                            <span>Logout / Keluar</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endauth
                </div>

            </div>
        </div>

        <!-- Mobile & Tablet Collapsible Drawer Navigation Menu -->
        <div 
            x-show="mobileMenuOpen" 
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-2"
            class="lg:hidden bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 px-4 pt-3 pb-6 space-y-3 shadow-xl"
            style="display: none;"
        >
            <div class="space-y-1">
                <a href="{{ route('pos') }}" class="block px-4 py-2.5 rounded-xl font-bold text-sm transition {{ request()->routeIs('pos') ? 'bg-amber-500 text-slate-950' : 'text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700' }}">
                    🛒 Kasir POS
                </a>
                <a href="{{ route('receivables.index') }}" class="block px-4 py-2.5 rounded-xl font-bold text-sm transition {{ request()->routeIs('receivables.*') ? 'bg-amber-500 text-slate-950' : 'text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700' }}">
                    💳 Buku Piutang Pelanggan
                </a>
                <a href="{{ route('guide.index') }}" class="block px-4 py-2.5 rounded-xl font-bold text-sm transition {{ request()->routeIs('guide.*') ? 'bg-amber-500 text-slate-950' : 'text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700' }}">
                    📖 Panduan Penggunaan Sistem
                </a>

                @if (Auth::user()?->isAdmin())
                    <a href="{{ route('dashboard') }}" class="block px-4 py-2.5 rounded-xl font-bold text-sm transition {{ request()->routeIs('dashboard') ? 'bg-amber-500 text-slate-950' : 'text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700' }}">
                        📊 Dashboard Analitik
                    </a>
                    <a href="{{ route('purchases.create') }}" class="block px-4 py-2.5 rounded-xl font-bold text-sm transition {{ request()->routeIs('purchases.create') ? 'bg-amber-500 text-slate-950' : 'text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700' }}">
                        📥 Barang Masuk (Supplier)
                    </a>
                    <a href="{{ route('reports.sales') }}" class="block px-4 py-2.5 rounded-xl font-bold text-sm transition {{ request()->routeIs('reports.sales') ? 'bg-amber-500 text-slate-950' : 'text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700' }}">
                        📈 Laporan Omzet
                    </a>
                    <a href="{{ route('users.index') }}" class="block px-4 py-2.5 rounded-xl font-bold text-sm transition {{ request()->routeIs('users.index') ? 'bg-amber-500 text-slate-950' : 'text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700' }}">
                        👥 Manajemen Pengguna
                    </a>

                    <!-- Master Data Accordion / Submenu -->
                    <div class="pt-2 border-t border-slate-200 dark:border-slate-700">
                        <div class="px-4 py-1 text-xs font-black uppercase tracking-wider text-slate-400">Data Master</div>
                        <div class="grid grid-cols-2 gap-1.5 mt-1">
                            <a href="{{ route('products.index') }}" class="block px-3 py-2 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-900/60 hover:text-amber-500">
                                📦 Master Barang
                            </a>
                            <a href="{{ route('units.index') }}" class="block px-3 py-2 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-900/60 hover:text-amber-500">
                                📐 Master Satuan
                            </a>
                            <a href="{{ route('suppliers.index') }}" class="block px-3 py-2 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-900/60 hover:text-amber-500">
                                🏢 Master Supplier
                            </a>
                            <a href="{{ route('customers.index') }}" class="block px-3 py-2 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-900/60 hover:text-amber-500">
                                👤 Master Pelanggan
                            </a>
                        </div>
                    </div>
                @endif

                <!-- Profile & Settings in Mobile Drawer -->
                <div class="pt-2 border-t border-slate-200 dark:border-slate-700 space-y-1">
                    <a href="{{ route('profile.edit') }}" class="block px-4 py-2.5 rounded-xl font-bold text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700">
                        👤 Profil & Pengaturan Akun
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content Area -->
    <main>
        {{ $slot }}
    </main>

    @livewireScripts
</body>
</html>
