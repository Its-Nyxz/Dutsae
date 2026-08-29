<div class="min-h-screen bg-slate-100 dark:bg-slate-900 text-slate-800 dark:text-slate-100 p-4 sm:p-6 space-y-4 sm:space-y-6 font-sans transition-colors duration-200">

    <!-- Header Banner -->
    <div class="bg-white dark:bg-slate-800 p-4 sm:p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm dark:shadow-xl flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 sm:gap-4 transition-colors">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white">Manajemen Pengguna (Staff & Admin)</h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5 sm:mt-1">Kelola akun akses sistem POS untuk Admin Utama dan Kasir toko</p>
        </div>
        <button wire:click="openCreateModal" class="bg-amber-500 hover:bg-amber-600 text-slate-950 font-black px-5 py-2.5 sm:py-3 rounded-xl shadow-lg shadow-amber-500/20 text-sm sm:text-base cursor-pointer transition">
            + Tambah Pengguna Baru
        </button>
    </div>

    <!-- Search & Filter Controls -->
    <div class="bg-white dark:bg-slate-800 p-4 sm:p-5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm dark:shadow-xl flex flex-wrap items-center justify-between gap-3 sm:gap-4 transition-colors">
        <div class="flex flex-wrap items-center gap-3 sm:gap-4 text-base w-full md:w-auto">
            <div class="w-full sm:w-80">
                <input 
                    type="text" 
                    wire:model.live.debounce.150ms="search" 
                    placeholder="🔍 Cari nama atau email..." 
                    class="w-full bg-slate-100 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl px-4 py-2.5 outline-none text-xs sm:text-sm font-semibold"
                >
            </div>
            <div class="w-full sm:w-56 relative" x-data="{ open: false }" @click.outside="open = false" @keydown.escape.window="open = false">
                <button 
                    type="button" 
                    @click="open = !open" 
                    class="w-full bg-slate-100 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white font-bold rounded-xl px-4 py-2.5 outline-none text-xs sm:text-sm flex items-center justify-between cursor-pointer focus:border-amber-500 transition shadow-sm"
                >
                    <span>
                        @if ($roleFilter === 'admin')
                            👑 Admin Utama
                        @elseif ($roleFilter === 'kasir')
                            🛒 Staff Kasir
                        @else
                            ⚡ Semua Role Access
                        @endif
                    </span>
                    <span class="text-xs text-slate-400 transition-transform duration-200" :class="{ 'rotate-180': open }">▼</span>
                </button>

                <div 
                    x-show="open" 
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 translate-y-1 scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                    x-transition:leave="transition ease-in duration-100"
                    x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                    x-transition:leave-end="opacity-0 translate-y-1 scale-95"
                    class="absolute z-50 left-0 right-0 mt-1.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-2xl overflow-hidden py-1 divide-y divide-slate-100 dark:divide-slate-700/40"
                    style="display: none;"
                >
                    <div 
                        @click="$wire.set('roleFilter', ''); open = false"
                        class="px-4 py-2.5 hover:bg-amber-500/10 hover:text-amber-600 dark:hover:text-amber-400 cursor-pointer font-bold text-xs sm:text-sm transition flex items-center justify-between {{ $roleFilter === '' ? 'bg-amber-500/20 text-amber-600 dark:text-amber-400 font-black' : 'text-slate-700 dark:text-slate-300' }}"
                    >
                        <span>⚡ Semua Role Access</span>
                        @if ($roleFilter === '') <span class="text-amber-500">✓</span> @endif
                    </div>
                    <div 
                        @click="$wire.set('roleFilter', 'admin'); open = false"
                        class="px-4 py-2.5 hover:bg-amber-500/10 hover:text-amber-600 dark:hover:text-amber-400 cursor-pointer font-bold text-xs sm:text-sm transition flex items-center justify-between {{ $roleFilter === 'admin' ? 'bg-amber-500/20 text-amber-600 dark:text-amber-400 font-black' : 'text-slate-700 dark:text-slate-300' }}"
                    >
                        <span>👑 Admin Utama</span>
                        @if ($roleFilter === 'admin') <span class="text-amber-500">✓</span> @endif
                    </div>
                    <div 
                        @click="$wire.set('roleFilter', 'kasir'); open = false"
                        class="px-4 py-2.5 hover:bg-amber-500/10 hover:text-amber-600 dark:hover:text-amber-400 cursor-pointer font-bold text-xs sm:text-sm transition flex items-center justify-between {{ $roleFilter === 'kasir' ? 'bg-amber-500/20 text-amber-600 dark:text-amber-400 font-black' : 'text-slate-700 dark:text-slate-300' }}"
                    >
                        <span>🛒 Staff Kasir</span>
                        @if ($roleFilter === 'kasir') <span class="text-amber-500">✓</span> @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm dark:shadow-xl overflow-hidden transition-colors">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-base text-slate-800 dark:text-slate-200">
                <thead class="bg-slate-100 dark:bg-slate-900/80 text-slate-500 dark:text-slate-400 text-xs font-bold uppercase tracking-wider">
                    <tr>
                        <th class="p-4">Pengguna</th>
                        <th class="p-4">Alamat Email</th>
                        <th class="p-4 text-center">Hak Akses (Role)</th>
                        <th class="p-4 text-center">Tanggal Didaftarkan</th>
                        <th class="p-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700/60 font-medium">
                    @forelse ($users as $u)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                            <td class="p-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center font-black text-lg border border-amber-500/20">
                                        {{ strtoupper(substr($u->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="font-extrabold text-slate-900 dark:text-white">{{ $u->name }}</div>
                                        @if (auth()->id() === $u->id)
                                            <span class="inline-block bg-sky-500/10 text-sky-600 dark:text-sky-400 text-[10px] font-black px-2 py-0.5 rounded">Akun Anda</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="p-4 font-mono text-sm text-slate-600 dark:text-slate-300">
                                {{ $u->email }}
                            </td>
                            <td class="p-4 text-center">
                                @if ($u->role === 'admin')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-black bg-amber-500/20 text-amber-700 dark:text-amber-400 border border-amber-500/30">
                                        👑 Admin Utama
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-black bg-emerald-500/20 text-emerald-700 dark:text-emerald-400 border border-emerald-500/30">
                                        🛒 Staff Kasir
                                    </span>
                                @endif
                            </td>
                            <td class="p-4 text-center text-sm text-slate-500 dark:text-slate-400 font-mono">
                                {{ $u->created_at ? $u->created_at->format('d/m/Y H:i') : '-' }}
                            </td>
                            <td class="p-4 text-center space-x-2">
                                <button 
                                    wire:click="openEditModal({{ $u->id }})" 
                                    class="bg-amber-500/10 hover:bg-amber-500/20 text-amber-700 dark:text-amber-400 font-bold px-3 py-1.5 rounded-lg text-sm border border-amber-500/30 cursor-pointer transition"
                                >
                                    ✏️ Edit
                                </button>
                                @if (auth()->id() !== $u->id)
                                    <button 
                                        type="button"
                                        onclick="confirmSwal('Hapus Pengguna', 'Apakah Anda yakin ingin menghapus pengguna {{ addslashes($u->name) }}?', 'Hapus', () => @this.call('deleteUser', {{ $u->id }}))"
                                        class="bg-red-500/10 hover:bg-red-500/20 text-red-600 dark:text-red-400 font-bold px-3 py-1.5 rounded-lg text-sm border border-red-500/30 cursor-pointer transition"
                                    >
                                        🗑️ Hapus
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-12 text-center text-slate-400">
                                Tidak ada data pengguna yang ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-200 dark:border-slate-700">
            {{ $users->links() }}
        </div>
    </div>

    <!-- Modal Tambah Pengguna Baru -->
    @if ($showCreateModal)
        <div class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-md flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-3xl max-w-lg w-full p-6 shadow-2xl space-y-5 text-slate-900 dark:text-white">
                <div class="flex justify-between items-center border-b border-slate-200 dark:border-slate-700 pb-3">
                    <h3 class="font-black text-xl">Tambah Pengguna Baru</h3>
                    <button wire:click="$set('showCreateModal', false)" class="text-slate-400 hover:text-slate-600 dark:hover:text-white text-2xl font-bold">&times;</button>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Nama Lengkap *</label>
                        <input type="text" wire:model="name" placeholder="misal: Ahmad (Kasir Shift 1)" class="w-full bg-slate-100 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl p-3 text-sm outline-none font-semibold">
                        @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Alamat Email *</label>
                        <input type="email" wire:model="email" placeholder="email@tokobesi.com" class="w-full bg-slate-100 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl p-3 text-sm outline-none font-semibold">
                        @error('email') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Alpine Custom Role Select: Create Modal -->
                    <div class="relative" x-data="{ openRoleCreate: false }" @click.outside="openRoleCreate = false" @keydown.escape.window="openRoleCreate = false">
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Hak Akses / Role *</label>
                        <button 
                            type="button" 
                            @click="openRoleCreate = !openRoleCreate" 
                            class="w-full bg-slate-100 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white font-bold rounded-xl p-3 text-sm flex items-center justify-between cursor-pointer focus:border-amber-500 transition shadow-sm outline-none"
                        >
                            <span>
                                @if ($role === 'admin')
                                    👑 Admin Utama (Akses Penuh Master & Laporan)
                                @else
                                    🛒 Staff Kasir (Akses POS & Checkout)
                                @endif
                            </span>
                            <span class="text-xs text-slate-400 transition-transform duration-200" :class="{ 'rotate-180': openRoleCreate }">▼</span>
                        </button>

                        <div 
                            x-show="openRoleCreate" 
                            x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 translate-y-1 scale-95"
                            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                            x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                            x-transition:leave-end="opacity-0 translate-y-1 scale-95"
                            class="absolute z-50 left-0 right-0 mt-1.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-2xl overflow-hidden py-1 divide-y divide-slate-100 dark:divide-slate-700/40"
                            style="display: none;"
                        >
                            <div 
                                @click="$wire.set('role', 'kasir'); openRoleCreate = false"
                                class="px-4 py-3 hover:bg-amber-500/10 hover:text-amber-600 dark:hover:text-amber-400 cursor-pointer font-bold text-sm transition flex items-center justify-between {{ $role === 'kasir' ? 'bg-amber-500/20 text-amber-600 dark:text-amber-400 font-black' : 'text-slate-700 dark:text-slate-300' }}"
                            >
                                <span>🛒 Staff Kasir (Akses POS & Checkout)</span>
                                @if ($role === 'kasir') <span class="text-amber-500">✓</span> @endif
                            </div>
                            <div 
                                @click="$wire.set('role', 'admin'); openRoleCreate = false"
                                class="px-4 py-3 hover:bg-amber-500/10 hover:text-amber-600 dark:hover:text-amber-400 cursor-pointer font-bold text-sm transition flex items-center justify-between {{ $role === 'admin' ? 'bg-amber-500/20 text-amber-600 dark:text-amber-400 font-black' : 'text-slate-700 dark:text-slate-300' }}"
                            >
                                <span>👑 Admin Utama (Akses Penuh Master & Laporan)</span>
                                @if ($role === 'admin') <span class="text-amber-500">✓</span> @endif
                            </div>
                        </div>
                        @error('role') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Kata Sandi *</label>
                        <input type="password" wire:model="password" placeholder="Minimal 6 karakter" class="w-full bg-slate-100 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl p-3 text-sm outline-none font-semibold">
                        @error('password') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Konfirmasi Kata Sandi *</label>
                        <input type="password" wire:model="password_confirmation" placeholder="Ulangi kata sandi" class="w-full bg-slate-100 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl p-3 text-sm outline-none font-semibold">
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-3 border-t border-slate-200 dark:border-slate-700">
                    <button wire:click="$set('showCreateModal', false)" class="bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 text-slate-900 dark:text-white font-bold px-5 py-2.5 rounded-xl text-sm">Batal</button>
                    <button wire:click="createUser" class="bg-amber-500 hover:bg-amber-600 text-slate-950 font-black px-6 py-2.5 rounded-xl text-sm shadow-md">Simpan Pengguna</button>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal Edit Pengguna -->
    @if ($showEditModal)
        <div class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-md flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-3xl max-w-lg w-full p-6 shadow-2xl space-y-5 text-slate-900 dark:text-white">
                <div class="flex justify-between items-center border-b border-slate-200 dark:border-slate-700 pb-3">
                    <h3 class="font-black text-xl">Edit Data Pengguna</h3>
                    <button wire:click="$set('showEditModal', false)" class="text-slate-400 hover:text-slate-600 dark:hover:text-white text-2xl font-bold">&times;</button>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Nama Lengkap *</label>
                        <input type="text" wire:model="name" class="w-full bg-slate-100 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl p-3 text-sm outline-none font-semibold">
                        @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Alamat Email *</label>
                        <input type="email" wire:model="email" class="w-full bg-slate-100 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl p-3 text-sm outline-none font-semibold">
                        @error('email') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Alpine Custom Role Select: Edit Modal -->
                    <div class="relative" x-data="{ openRoleEdit: false }" @click.outside="openRoleEdit = false" @keydown.escape.window="openRoleEdit = false">
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Hak Akses / Role *</label>
                        <button 
                            type="button" 
                            @click="openRoleEdit = !openRoleEdit" 
                            class="w-full bg-slate-100 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white font-bold rounded-xl p-3 text-sm flex items-center justify-between cursor-pointer focus:border-amber-500 transition shadow-sm outline-none"
                        >
                            <span>
                                @if ($role === 'admin')
                                    👑 Admin Utama (Akses Penuh Master & Laporan)
                                @else
                                    🛒 Staff Kasir (Akses POS & Checkout)
                                @endif
                            </span>
                            <span class="text-xs text-slate-400 transition-transform duration-200" :class="{ 'rotate-180': openRoleEdit }">▼</span>
                        </button>

                        <div 
                            x-show="openRoleEdit" 
                            x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 translate-y-1 scale-95"
                            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                            x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                            x-transition:leave-end="opacity-0 translate-y-1 scale-95"
                            class="absolute z-50 left-0 right-0 mt-1.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-2xl overflow-hidden py-1 divide-y divide-slate-100 dark:divide-slate-700/40"
                            style="display: none;"
                        >
                            <div 
                                @click="$wire.set('role', 'kasir'); openRoleEdit = false"
                                class="px-4 py-3 hover:bg-amber-500/10 hover:text-amber-600 dark:hover:text-amber-400 cursor-pointer font-bold text-sm transition flex items-center justify-between {{ $role === 'kasir' ? 'bg-amber-500/20 text-amber-600 dark:text-amber-400 font-black' : 'text-slate-700 dark:text-slate-300' }}"
                            >
                                <span>🛒 Staff Kasir (Akses POS & Checkout)</span>
                                @if ($role === 'kasir') <span class="text-amber-500">✓</span> @endif
                            </div>
                            <div 
                                @click="$wire.set('role', 'admin'); openRoleEdit = false"
                                class="px-4 py-3 hover:bg-amber-500/10 hover:text-amber-600 dark:hover:text-amber-400 cursor-pointer font-bold text-sm transition flex items-center justify-between {{ $role === 'admin' ? 'bg-amber-500/20 text-amber-600 dark:text-amber-400 font-black' : 'text-slate-700 dark:text-slate-300' }}"
                            >
                                <span>👑 Admin Utama (Akses Penuh Master & Laporan)</span>
                                @if ($role === 'admin') <span class="text-amber-500">✓</span> @endif
                            </div>
                        </div>
                        @error('role') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="border-t border-slate-200 dark:border-slate-700 pt-3">
                        <label class="block text-xs font-bold uppercase text-amber-600 dark:text-amber-400 mb-1">Ubah Kata Sandi (Kosongkan jika tidak diubah)</label>
                        <div class="space-y-3">
                            <input type="password" wire:model="password" placeholder="Kata sandi baru..." class="w-full bg-slate-100 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl p-3 text-sm outline-none font-semibold">
                            <input type="password" wire:model="password_confirmation" placeholder="Ulangi kata sandi baru" class="w-full bg-slate-100 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl p-3 text-sm outline-none font-semibold">
                        </div>
                        @error('password') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-3 border-t border-slate-200 dark:border-slate-700">
                    <button wire:click="$set('showEditModal', false)" class="bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 text-slate-900 dark:text-white font-bold px-5 py-2.5 rounded-xl text-sm">Batal</button>
                    <button wire:click="updateUser" class="bg-amber-500 hover:bg-amber-600 text-slate-950 font-black px-6 py-2.5 rounded-xl text-sm shadow-md">Simpan Perubahan</button>
                </div>
            </div>
        </div>
    @endif

</div>
