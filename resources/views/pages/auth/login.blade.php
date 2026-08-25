<!DOCTYPE html>
<html 
    lang="id" 
    x-data="{ 
        theme: localStorage.getItem('theme') || 'dark',
        toggleTheme() {
            this.theme = this.theme === 'dark' ? 'light' : 'dark';
            localStorage.setItem('theme', this.theme);
        }
    }"
    :class="theme"
    class="h-full"
>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Toko Duta Sae POS</title>
    <link rel="icon" type="image/png" href="{{ asset('icon.png') }}">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="h-full bg-slate-100 dark:bg-slate-950 text-slate-800 dark:text-slate-100 font-sans antialiased flex items-center justify-center p-4 sm:p-6 select-none relative overflow-x-hidden transition-colors duration-200">

    <!-- Top Right Light / Dark Mode Toggle Button -->
    <div class="fixed top-5 right-5 z-50">
        <button 
            @click="toggleTheme()" 
            type="button" 
            class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-md border border-slate-200 dark:border-slate-800 hover:border-amber-500 text-slate-800 dark:text-slate-100 font-extrabold px-4 py-2.5 rounded-2xl shadow-xl transition-all duration-200 flex items-center gap-2 text-sm cursor-pointer"
        >
            <span x-text="theme === 'dark' ? '🌙 Dark Mode' : '☀️ Light Mode'"></span>
        </button>
    </div>

    <!-- Background Glow Effects -->
    <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-1/4 right-1/4 w-96 h-96 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="max-w-4xl w-full grid grid-cols-1 lg:grid-cols-12 gap-8 items-center relative z-10" x-data="{
        email: '{{ old('email', '') }}',
        password: '',
        showPassword: false,
        fillCredentials(userEmail, userPass) {
            this.email = userEmail;
            this.password = userPass;
        }
    }">

        <!-- Left Branding Panel (Visible on LG screens) -->
        <div class="lg:col-span-6 space-y-6 text-left hidden lg:block">
            <div class="flex items-center gap-4">
                <img src="{{ asset('icon.png') }}" alt="Toko Duta Sae" class="w-16 h-16 object-contain rounded-2xl shadow-2xl border border-amber-500/30">
                <div>
                    <h1 class="text-3xl font-black tracking-tight text-slate-900 dark:text-white">TOKO DUTA SAE</h1>
                    <p class="text-sm font-bold text-amber-600 dark:text-amber-400">Sistem POS Kasir & Inventaris Gudang</p>
                </div>
            </div>

            <p class="text-slate-600 dark:text-slate-400 text-base leading-relaxed">
                Kelola penjualan cepat toko besi, konversi multi-satuan, lokasi penempatan rak, serta laporan omzet & piutang secara akurat.
            </p>

            <div class="space-y-3 pt-2">
                <div class="flex items-center gap-3 bg-white dark:bg-slate-900/60 p-3.5 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
                    <span class="text-2xl">⚡</span>
                    <div>
                        <div class="font-extrabold text-sm text-slate-900 dark:text-white">Penjualan POS Kilat</div>
                        <div class="text-xs text-slate-500 dark:text-slate-400">Dukungan Tombol Shortcut [F2], [F6], [F9]</div>
                    </div>
                </div>

                <div class="flex items-center gap-3 bg-white dark:bg-slate-900/60 p-3.5 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
                    <span class="text-2xl">🔄</span>
                    <div>
                        <div class="font-extrabold text-sm text-slate-900 dark:text-white">Multi-Satuan Konversi</div>
                        <div class="text-xs text-slate-500 dark:text-slate-400">Jual eceran Meter/Batang maupun grosir Ikat/Dus</div>
                    </div>
                </div>

                <div class="flex items-center gap-3 bg-white dark:bg-slate-900/60 p-3.5 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
                    <span class="text-2xl">📍</span>
                    <div>
                        <div class="font-extrabold text-sm text-slate-900 dark:text-white">Manajemen Lokasi Rak</div>
                        <div class="text-xs text-slate-500 dark:text-slate-400">Pencarian posisi rak fisik barang secara cepat</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Login Form Card -->
        <div class="lg:col-span-6">
            <div class="bg-white/90 dark:bg-slate-900/80 backdrop-blur-2xl border border-slate-200 dark:border-slate-800 rounded-3xl p-8 sm:p-10 shadow-2xl space-y-6 transition-colors duration-200">

                <!-- Header Mobile Logo & Title -->
                <div class="text-center space-y-2">
                    <div class="flex justify-center lg:hidden mb-3">
                        <img src="{{ asset('icon.png') }}" alt="Toko Duta Sae" class="w-14 h-14 object-contain rounded-2xl shadow-lg">
                    </div>
                    <h2 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900 dark:text-white">Selamat Datang 👋</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400 font-medium">Masuk ke akun Anda untuk mulai bertransaksi</p>
                </div>

                <!-- Quick Demo Fill Badges -->
                <div class="bg-slate-100 dark:bg-slate-950/60 p-3.5 rounded-2xl border border-slate-200 dark:border-slate-800/80 space-y-2">
                    <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider block text-center">👇 Akses Cepat Login (Klik untuk Mengisi)</span>
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <button 
                            type="button" 
                            @click="fillCredentials('admin@tokobesi.com', 'password')"
                            class="bg-amber-500/10 hover:bg-amber-500/20 text-amber-700 dark:text-amber-400 border border-amber-500/30 p-2.5 rounded-xl font-bold transition flex items-center justify-center gap-1.5 cursor-pointer"
                        >
                            <span>👑 Admin Utama</span>
                        </button>

                        <button 
                            type="button" 
                            @click="fillCredentials('kasir@tokobesi.com', 'password')"
                            class="bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-700 dark:text-emerald-400 border border-emerald-500/30 p-2.5 rounded-xl font-bold transition flex items-center justify-center gap-1.5 cursor-pointer"
                        >
                            <span>🛒 Budi (Kasir)</span>
                        </button>
                    </div>
                </div>

                <!-- Form Login -->
                <form method="POST" action="{{ route('login.store') }}" class="space-y-4">
                    @csrf

                    <!-- Email Input -->
                    <div class="space-y-1.5 text-left">
                        <label class="block text-xs font-extrabold uppercase tracking-wider text-slate-700 dark:text-slate-300">Alamat Email</label>
                        <div class="relative flex items-center">
                            <span class="absolute left-3.5 text-slate-400">✉️</span>
                            <input 
                                type="email" 
                                name="email" 
                                x-model="email"
                                required 
                                autofocus 
                                placeholder="email@tokobesi.com" 
                                class="w-full bg-slate-100 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl py-3.5 pl-10 pr-4 text-base focus:border-amber-500 outline-none transition font-medium shadow-inner"
                            >
                        </div>
                        @error('email')
                            <p class="text-xs text-red-500 dark:text-red-400 font-medium mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password Input -->
                    <div class="space-y-1.5 text-left">
                        <div class="flex justify-between items-center">
                            <label class="block text-xs font-extrabold uppercase tracking-wider text-slate-700 dark:text-slate-300">Kata Sandi</label>
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="text-xs font-bold text-amber-600 dark:text-amber-400 hover:underline">Lupa Kata Sandi?</a>
                            @endif
                        </div>
                        <div class="relative flex items-center">
                            <span class="absolute left-3.5 text-slate-400">🔒</span>
                            <input 
                                :type="showPassword ? 'text' : 'password'" 
                                name="password" 
                                x-model="password"
                                required 
                                placeholder="••••••••" 
                                class="w-full bg-slate-100 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl py-3.5 pl-10 pr-12 text-base focus:border-amber-500 outline-none transition font-medium shadow-inner"
                            >
                            <button 
                                type="button" 
                                @click="showPassword = !showPassword"
                                class="absolute right-3.5 text-slate-400 hover:text-slate-600 dark:hover:text-white text-base select-none cursor-pointer"
                            >
                                <span x-text="showPassword ? '🙈' : '👁️'"></span>
                            </button>
                        </div>
                        @error('password')
                            <p class="text-xs text-red-500 dark:text-red-400 font-medium mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Remember Me Checkbox -->
                    <div class="flex items-center justify-between pt-1">
                        <label class="flex items-center gap-2.5 cursor-pointer text-sm text-slate-700 dark:text-slate-300 font-medium">
                            <input type="checkbox" name="remember" class="rounded border-slate-300 dark:border-slate-700 text-amber-500 focus:ring-amber-500 w-4 h-4 bg-slate-100 dark:bg-slate-950">
                            <span>Ingat Saya</span>
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <button 
                        type="submit" 
                        class="w-full bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-slate-950 font-black py-4 rounded-xl shadow-lg shadow-amber-500/20 text-lg transition duration-200 cursor-pointer flex items-center justify-center gap-2 mt-2"
                    >
                        <span>Masuk ke Sistem POS</span>
                        <span>→</span>
                    </button>
                </form>

                <!-- Footer Text -->
                <div class="text-center text-xs text-slate-500 pt-2 border-t border-slate-200 dark:border-slate-800/60">
                    Toko Duta Sae POS &copy; {{ date('Y') }} &bull; Hak Cipta Dilindungi
                </div>

            </div>
        </div>

    </div>

</body>
</html>
