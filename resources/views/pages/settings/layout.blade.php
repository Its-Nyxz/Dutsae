<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <!-- Header Banner -->
    <div class="mb-6 bg-white dark:bg-slate-800 rounded-3xl p-6 shadow-sm border border-slate-200 dark:border-slate-700/80 flex flex-wrap items-center justify-between gap-4 transition-colors">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-amber-500/10 border border-amber-500/30 flex items-center justify-center text-2xl shadow-inner">
                ⚙️
            </div>
            <div>
                <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">{{ $heading ?? 'Pengaturan Akun & Sistem' }}</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">{{ $subheading ?? 'Kelola profil, buku panduan, keamanan, dan preferensi aplikasi' }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <span class="bg-amber-500/10 text-amber-700 dark:text-amber-400 border border-amber-500/30 text-xs px-3.5 py-1.5 rounded-xl font-mono font-bold shadow-sm">
                Toko Duta Sae
            </span>
        </div>
    </div>

    <!-- Main Grid Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <!-- Sidebar Navigation Tabs (3 cols) -->
        <div class="lg:col-span-3 bg-white dark:bg-slate-800 rounded-3xl p-3 border border-slate-200 dark:border-slate-700/80 shadow-sm space-y-1.5 transition-colors">
            <a href="{{ route('profile.edit') }}" wire:navigate class="flex items-center gap-3 px-4 py-3 rounded-2xl text-sm font-bold transition {{ request()->routeIs('profile.edit') ? 'bg-amber-500 text-slate-950 font-black shadow-md' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700/60' }}">
                <span class="text-lg">👤</span>
                <span>Profil Pengguna</span>
            </a>
            <a href="{{ route('guide.index') }}" wire:navigate class="flex items-center gap-3 px-4 py-3 rounded-2xl text-sm font-bold transition {{ request()->routeIs('guide.*') ? 'bg-amber-500 text-slate-950 font-black shadow-md' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700/60' }}">
                <span class="text-lg">📖</span>
                <span>Panduan Sistem</span>
            </a>
        </div>

        <!-- Main Content Area (9 cols) -->
        <div class="lg:col-span-9 bg-white dark:bg-slate-800 rounded-3xl p-6 sm:p-8 border border-slate-200 dark:border-slate-700/80 shadow-sm transition-colors">
            <div class="w-full {{ $maxWidth ?? 'max-w-2xl' }}">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
