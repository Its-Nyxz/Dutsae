<div class="min-h-screen bg-slate-100 dark:bg-slate-900 text-slate-800 dark:text-slate-100 p-6 space-y-6 font-sans transition-colors duration-200">

    <!-- Header Banner -->
    <div class="bg-white dark:bg-gradient-to-r dark:from-slate-800 dark:to-slate-850 p-6 rounded-2xl border border-slate-200 dark:border-slate-700/80 shadow-sm dark:shadow-2xl flex flex-wrap items-center justify-between gap-4 transition-colors">
        <div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">Dashboard Operasional Toko Besi</h1>
            <p class="text-base text-slate-500 dark:text-slate-400 mt-1">Ringkasan Omzet, Uang Masuk, Transaksi & Alert Persediaan Stok</p>
        </div>
        <a href="{{ route('pos') }}" class="bg-amber-500 hover:bg-amber-600 text-slate-950 font-black px-6 py-3.5 rounded-xl shadow-lg transition flex items-center gap-2 text-base">
            🛒 Buka Layar Kasir POS
        </a>
    </div>

    <!-- Summary Metrics Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

        <!-- Card 1: Omzet Hari Ini -->
        <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm dark:shadow-xl space-y-2.5 transition-colors">
            <span class="text-xs uppercase font-extrabold text-slate-500 dark:text-slate-400 tracking-wider">OMZET PENJUALAN HARI INI</span>
            <div class="text-3xl font-black text-amber-600 dark:text-amber-400 font-mono">
                Rp {{ number_format($todayTurnover, 0, ',', '.') }}
            </div>
            <p class="text-sm text-slate-500 dark:text-slate-400">Total nilai faktur penjualan hari ini</p>
        </div>

        <!-- Card 2: Uang Masuk Hari Ini -->
        <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm dark:shadow-xl space-y-2.5 transition-colors">
            <span class="text-xs uppercase font-extrabold text-slate-500 dark:text-slate-400 tracking-wider">UANG MASUK HARI INI</span>
            <div class="text-3xl font-black text-emerald-600 dark:text-emerald-400 font-mono">
                Rp {{ number_format($todayIncomingPayments, 0, ',', '.') }}
            </div>
            <p class="text-sm text-slate-500 dark:text-slate-400">Kas/transfer nyata diterima hari ini</p>
        </div>

        <!-- Card 3: Jumlah Transaksi -->
        <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm dark:shadow-xl space-y-2.5 transition-colors">
            <span class="text-xs uppercase font-extrabold text-slate-500 dark:text-slate-400 tracking-wider">JUMLAH TRANSAKSI</span>
            <div class="text-3xl font-black text-sky-600 dark:text-sky-400 font-mono">
                {{ number_format($todayTransactionCount, 0) }} Transaksi
            </div>
            <p class="text-sm text-slate-500 dark:text-slate-400">Penjualan selesai hari ini</p>
        </div>

        <!-- Card 4: Rata-Rata Transaksi -->
        <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm dark:shadow-xl space-y-2.5 transition-colors">
            <span class="text-xs uppercase font-extrabold text-slate-500 dark:text-slate-400 tracking-wider">RATA-RATA TRANSAKSI</span>
            <div class="text-3xl font-black text-purple-600 dark:text-purple-400 font-mono">
                Rp {{ number_format($averageTransactionValue, 0, ',', '.') }}
            </div>
            <p class="text-sm text-slate-500 dark:text-slate-400">Per transaksi penjualan</p>
        </div>

    </div>

    <!-- Alert Cards & Tables Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Low Stock Alert Box -->
        <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm dark:shadow-xl space-y-4 transition-colors">
            <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-700 pb-3.5">
                <h3 class="font-bold text-xl text-slate-900 dark:text-white flex items-center gap-2">
                    <span class="w-3.5 h-3.5 bg-amber-500 rounded-full animate-pulse"></span>
                    Stok Menipis (Perlu Reorder)
                </h3>
                <span class="bg-amber-500/20 text-amber-600 dark:text-amber-400 text-sm px-3 py-1 rounded-full font-bold">{{ count($lowStockProducts) }} Barang</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-700 dark:text-slate-300">
                    <thead class="bg-slate-100 dark:bg-slate-900/60 uppercase text-xs font-bold text-slate-500 dark:text-slate-400">
                        <tr>
                            <th class="p-3">Kode</th>
                            <th class="p-3">Nama Barang</th>
                            <th class="p-3 text-right">Stok Sekarang</th>
                            <th class="p-3 text-right">Min Stok</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700/60">
                        @forelse ($lowStockProducts as $p)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30">
                                <td class="p-3 font-bold text-amber-600 dark:text-amber-400">{{ $p['code'] }}</td>
                                <td class="p-3 text-slate-900 dark:text-white font-medium">{{ $p['name'] }}</td>
                                <td class="p-3 text-right font-bold text-amber-600 dark:text-amber-400 text-base">{{ number_format($p['current_stock_base'], 2, ',', '.') }}</td>
                                <td class="p-3 text-right text-slate-500 dark:text-slate-400">{{ number_format($p['minimum_stock_base'], 0) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="p-6 text-center text-slate-400">Seluruh stok barang dalam kondisi aman.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Top Selling Products Box -->
        <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm dark:shadow-xl space-y-4 transition-colors">
            <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-700 pb-3.5">
                <h3 class="font-bold text-xl text-slate-900 dark:text-white">🔥 Produk Terlaris</h3>
                <span class="text-sm text-slate-500 dark:text-slate-400">Top 5 Terbanyak</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-700 dark:text-slate-300">
                    <thead class="bg-slate-100 dark:bg-slate-900/60 uppercase text-xs font-bold text-slate-500 dark:text-slate-400">
                        <tr>
                            <th class="p-3">Kode</th>
                            <th class="p-3">Nama Produk</th>
                            <th class="p-3 text-right">Total Terjual (Base)</th>
                            <th class="p-3 text-right">Total Nilai</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700/60">
                        @forelse ($topSellingProducts as $tp)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30">
                                <td class="p-3 font-bold text-amber-600 dark:text-amber-400">{{ $tp['product_code_snapshot'] }}</td>
                                <td class="p-3 text-slate-900 dark:text-white font-medium">{{ $tp['product_name_snapshot'] }}</td>
                                <td class="p-3 text-right font-bold text-emerald-600 dark:text-emerald-400 text-base">{{ number_format($tp['total_qty_base'], 2, ',', '.') }}</td>
                                <td class="p-3 text-right font-mono font-bold text-slate-900 dark:text-white text-base">Rp {{ number_format($tp['total_sales_amount'], 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="p-6 text-center text-slate-400">Belum ada data penjualan tercatat.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</div>
