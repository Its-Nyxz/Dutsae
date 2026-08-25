<div class="relative" x-data="{ open: false }">
    <!-- Notification Bell Button -->
    <button 
        @click="open = !open" 
        class="relative p-2.5 text-slate-700 dark:text-slate-300 hover:text-amber-500 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-xl border border-slate-300 dark:border-slate-700 transition cursor-pointer shadow-sm"
        title="Notifikasi Sistem"
    >
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
        </svg>

        @if ($unreadCount > 0)
            <span class="absolute -top-1 -right-1 bg-red-600 text-white font-black text-xs w-5.5 h-5.5 rounded-full flex items-center justify-center border-2 border-white dark:border-slate-900 animate-pulse">
                {{ $unreadCount }}
            </span>
        @endif
    </button>

    <!-- Notification Dropdown Menu -->
    <div 
        x-show="open" 
        @click.outside="open = false" 
        x-transition
        class="absolute right-0 mt-2 w-88 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-2xl z-50 overflow-hidden text-slate-900 dark:text-white transition-colors"
    >
        <div class="p-3.5 bg-slate-50 dark:bg-slate-900 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center">
            <h4 class="font-extrabold text-sm uppercase text-slate-900 dark:text-white tracking-wider">Notifikasi Sistem</h4>
            <span class="text-xs bg-amber-500/20 text-amber-700 dark:text-amber-400 font-bold px-2.5 py-0.5 rounded-md">{{ $unreadCount }} Baru</span>
        </div>

        <div class="max-h-80 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-700/60">
            @forelse ($notifications as $n)
                <div class="p-3.5 hover:bg-slate-100 dark:hover:bg-slate-700/50 transition flex gap-3 text-sm">
                    <span class="text-xl">
                        {{ $n['type'] === 'low_stock' ? '⚠️' : '🛒' }}
                    </span>
                    <div>
                        <div class="font-bold text-slate-900 dark:text-white text-sm">{{ $n['title'] }}</div>
                        <div class="text-slate-600 dark:text-slate-300 text-xs mt-0.5">{{ $n['message'] }}</div>
                        <div class="text-xs text-slate-400 dark:text-slate-500 mt-1">{{ $n['created_at'] }}</div>
                    </div>
                </div>
            @empty
                <div class="p-5 text-center text-sm text-slate-400 dark:text-slate-500">Tidak ada notifikasi baru.</div>
            @endforelse
        </div>
    </div>
</div>
