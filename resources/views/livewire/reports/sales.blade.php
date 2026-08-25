<div class="min-h-screen bg-slate-100 dark:bg-slate-900 text-slate-800 dark:text-slate-100 p-6 space-y-6 font-sans transition-colors duration-200">

    <!-- Header Banner -->
    <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm dark:shadow-xl flex flex-wrap items-center justify-between gap-4 transition-colors">
        <div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white">Laporan Omzet & Penjualan</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Analisis total omzet faktur, realisasi kas diterima, piutang, dan riwayat transaksi</p>
        </div>

        <!-- Quick Date Range Filter Buttons & Print PDF Button -->
        <div class="flex flex-wrap items-center gap-2">
            <button wire:click="setFilter('today')" class="bg-slate-200 dark:bg-slate-700 hover:bg-amber-500 hover:text-slate-950 font-bold px-4 py-2.5 rounded-xl text-sm transition cursor-pointer">
                Hari Ini
            </button>
            <button wire:click="setFilter('7days')" class="bg-slate-200 dark:bg-slate-700 hover:bg-amber-500 hover:text-slate-950 font-bold px-4 py-2.5 rounded-xl text-sm transition cursor-pointer">
                7 Hari Terakhir
            </button>
            <button wire:click="setFilter('this_month')" class="bg-slate-200 dark:bg-slate-700 hover:bg-amber-500 hover:text-slate-950 font-bold px-4 py-2.5 rounded-xl text-sm transition cursor-pointer">
                Bulan Ini
            </button>
            <a 
                href="{{ route('exports.sales', ['start_date' => $startDate, 'end_date' => $endDate, 'search' => $search]) }}" 
                target="_blank" 
                class="bg-emerald-600 hover:bg-emerald-500 text-white font-black px-5 py-2.5 rounded-xl text-sm transition cursor-pointer shadow-lg shadow-emerald-600/20 flex items-center gap-1.5"
            >
                📊 Export Excel (.xls)
            </a>
            <a 
                href="{{ route('print.reports.sales', ['start_date' => $startDate, 'end_date' => $endDate, 'search' => $search]) }}" 
                target="_blank" 
                class="bg-amber-500 hover:bg-amber-600 text-slate-950 font-black px-5 py-2.5 rounded-xl text-sm transition cursor-pointer shadow-lg shadow-amber-500/20 flex items-center gap-1.5"
            >
                🖨️ Cetak Laporan PDF
            </a>
        </div>
    </div>

    <!-- Date Range Picker Controls -->
    <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm dark:shadow-xl flex flex-wrap items-center justify-between gap-4 transition-colors">
        <div class="flex flex-wrap items-center gap-4 text-base">
            <div class="w-48">
                <x-ui.date-picker label="DARI TANGGAL" wire:model.live="startDate" />
            </div>
            <div class="w-48">
                <x-ui.date-picker label="SAMPAI TANGGAL" wire:model.live="endDate" />
            </div>
        </div>

        <div class="w-72">
            <label class="block text-xs font-extrabold uppercase text-slate-500 dark:text-slate-400 mb-1">Cari Faktur / Pelanggan</label>
            <input type="text" wire:model.live.debounce.150ms="search" placeholder="Ketik No. Faktur atau Nama..." class="w-full bg-slate-100 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl px-4 py-2.5 outline-none text-sm">
        </div>
    </div>

    <!-- Summary Metrics Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

        <!-- Metric 1: Total Omzet -->
        <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm dark:shadow-xl space-y-2 transition-colors">
            <span class="text-xs uppercase font-black text-slate-500 dark:text-slate-400 tracking-wider">TOTAL OMZET PENJUALAN</span>
            <div class="text-3xl font-black text-amber-600 dark:text-amber-400 font-mono">
                Rp {{ number_format($totalTurnover, 0, ',', '.') }}
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400">Total nilai faktur dalam periode ini</p>
        </div>

        <!-- Metric 2: Uang Masuk Diterima -->
        <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm dark:shadow-xl space-y-2 transition-colors">
            <span class="text-xs uppercase font-black text-slate-500 dark:text-slate-400 tracking-wider">UANG MASUK DITERIMA</span>
            <div class="text-3xl font-black text-emerald-600 dark:text-emerald-400 font-mono">
                Rp {{ number_format($totalPaid, 0, ',', '.') }}
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400">Kas/transfer nyata yang sudah lunas</p>
        </div>

        <!-- Metric 3: Total Sisa Piutang -->
        <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm dark:shadow-xl space-y-2 transition-colors">
            <span class="text-xs uppercase font-black text-slate-500 dark:text-slate-400 tracking-wider">SISA PIUTANG (BELUM LUNAS)</span>
            <div class="text-3xl font-black text-red-600 dark:text-red-400 font-mono">
                Rp {{ number_format($totalOutstanding, 0, ',', '.') }}
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400">Piutang yang belum dibayar</p>
        </div>

        <!-- Metric 4: Jumlah Faktur -->
        <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm dark:shadow-xl space-y-2 transition-colors">
            <span class="text-xs uppercase font-black text-slate-500 dark:text-slate-400 tracking-wider">JUMLAH FAKTUR</span>
            <div class="text-3xl font-black text-sky-600 dark:text-sky-400 font-mono">
                {{ number_format($totalInvoices, 0) }} Faktur
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400">Total transaksi penjualan selesai</p>
        </div>

    </div>

    <!-- Sales Invoices Table -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm dark:shadow-xl overflow-hidden p-5 space-y-4 transition-colors">
        <div class="flex justify-between items-center">
            <h3 class="font-extrabold text-slate-900 dark:text-white text-lg tracking-wide">Rincian Transaksi Penjualan</h3>
            <span class="text-sm font-bold text-slate-500 dark:text-slate-400">Menampilkan {{ count($sales) }} Transaksi</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-700 dark:text-slate-300">
                <thead class="bg-slate-100 dark:bg-slate-900/80 text-slate-500 dark:text-slate-400 uppercase text-xs font-bold">
                    <tr>
                        <th class="p-3.5">No. Faktur</th>
                        <th class="p-3.5">Tanggal & Jam</th>
                        <th class="p-3.5">Pelanggan</th>
                        <th class="p-3.5">Metode Bayar</th>
                        <th class="p-3.5 text-right">Nilai Faktur</th>
                        <th class="p-3.5 text-right">Dibayar</th>
                        <th class="p-3.5 text-center">Status</th>
                        <th class="p-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700/60">
                    @forelse ($sales as $sale)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30">
                            <td class="p-3.5 font-bold text-amber-600 dark:text-amber-400 font-mono">{{ $sale->invoice_number }}</td>
                            <td class="p-3.5 text-slate-600 dark:text-slate-300">{{ $sale->sold_at->format('d/m/Y H:i') }}</td>
                            <td class="p-3.5 font-bold text-slate-900 dark:text-white">{{ $sale->customer?->name ?? 'Pelanggan Umum' }}</td>
                            <td class="p-3.5">
                                <span class="bg-slate-100 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-200 px-2.5 py-1 rounded-lg text-xs font-mono font-bold">
                                    {{ strtoupper($sale->payments->first()?->payment_method ?? 'TUNAI') }}
                                </span>
                            </td>
                            <td class="p-3.5 text-right font-mono font-bold text-amber-600 dark:text-amber-400 text-base">
                                Rp {{ number_format($sale->grand_total, 0, ',', '.') }}
                            </td>
                            <td class="p-3.5 text-right font-mono font-bold text-emerald-600 dark:text-emerald-400 text-base">
                                Rp {{ number_format($sale->paid_amount, 0, ',', '.') }}
                            </td>
                            <td class="p-3.5 text-center">
                                @if ($sale->payment_status === 'paid')
                                    <x-ui.badge variant="emerald">LUNAS</x-ui.badge>
                                @elseif ($sale->payment_status === 'partial')
                                    <x-ui.badge variant="amber">SEBAGIAN</x-ui.badge>
                                @else
                                    <x-ui.badge variant="red">PIUTANG</x-ui.badge>
                                @endif
                            </td>
                            <td class="p-3.5 text-center">
                                <button wire:click="viewDetail({{ $sale->id }})" class="bg-amber-500/10 hover:bg-amber-500/20 text-amber-600 dark:text-amber-400 px-3 py-1.5 rounded-lg text-xs font-bold transition">
                                    👁️ Detail & Cetak
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-8 text-center text-slate-400 text-base">Tidak ada transaksi penjualan dalam periode ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Detail & Print Modal -->
    @if ($showDetailModal && $selectedSale)
        <div class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-md flex items-center justify-center p-4 sm:p-6">
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-3xl max-w-2xl lg:max-w-3xl w-full p-8 shadow-2xl space-y-6 text-slate-900 dark:text-white max-h-[92vh] flex flex-col justify-between overflow-hidden">
                <div class="flex justify-between items-center border-b border-slate-200 dark:border-slate-700 pb-4 shrink-0">
                    <div>
                        <h3 class="font-black text-2xl">Rincian Faktur - {{ $selectedSale->invoice_number }}</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Tanggal: {{ $selectedSale->sold_at->format('d/m/Y H:i') }} | Kasir: {{ $selectedSale->cashier?->name }}</p>
                    </div>
                    <button wire:click="$set('showDetailModal', false)" class="text-slate-400 hover:text-white text-3xl font-bold transition">&times;</button>
                </div>

                <div class="space-y-4 overflow-y-auto pr-1 text-base">
                    <!-- Customer & Payment Info Header -->
                    <div class="grid grid-cols-2 gap-4 bg-slate-100 dark:bg-slate-900 p-4 rounded-2xl border border-slate-200 dark:border-slate-700 font-mono text-sm">
                        <div>
                            <span class="text-slate-500">Pelanggan:</span>
                            <span class="font-bold text-slate-900 dark:text-white block">{{ $selectedSale->customer?->name ?? 'Pelanggan Umum' }}</span>
                        </div>
                        <div>
                            <span class="text-slate-500">Status Pembayaran:</span>
                            <span class="font-bold text-emerald-600 dark:text-emerald-400 block uppercase">{{ $selectedSale->payment_status }}</span>
                        </div>
                    </div>

                    <!-- Items Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-slate-700 dark:text-slate-300">
                            <thead class="bg-slate-100 dark:bg-slate-900/80 text-slate-500 dark:text-slate-400 uppercase text-xs font-bold">
                                <tr>
                                    <th class="p-3">Nama Produk</th>
                                    <th class="p-3 text-center">Qty</th>
                                    <th class="p-3">Satuan</th>
                                    <th class="p-3 text-right">Harga Jual</th>
                                    <th class="p-3 text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-700/60">
                                @foreach ($selectedSale->items as $item)
                                    <tr>
                                        <td class="p-3 font-bold text-slate-900 dark:text-white">
                                            [{{ $item->product_code_snapshot }}] {{ $item->product_name_snapshot }}
                                        </td>
                                        <td class="p-3 text-center font-bold text-amber-600 dark:text-amber-400">
                                            {{ number_format($item->quantity, 2, ',', '.') }}
                                        </td>
                                        <td class="p-3">{{ $item->unit_name_snapshot }}</td>
                                        <td class="p-3 text-right font-mono">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                        <td class="p-3 text-right font-mono font-bold text-emerald-600 dark:text-emerald-400">
                                            Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Total Calculation -->
                    <div class="bg-slate-100 dark:bg-slate-900 p-4 rounded-2xl border border-slate-200 dark:border-slate-700 space-y-1.5 font-mono text-sm">
                        <div class="flex justify-between">
                            <span>Subtotal:</span>
                            <span>Rp {{ number_format($selectedSale->total_amount, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Diskon:</span>
                            <span class="text-amber-600 dark:text-amber-400">Rp {{ number_format($selectedSale->discount_total, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-base font-bold border-t border-slate-200 dark:border-slate-700 pt-2">
                            <span>GRAND TOTAL:</span>
                            <span class="text-amber-600 dark:text-amber-400 text-lg">Rp {{ number_format($selectedSale->grand_total, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>

                <div class="flex justify-between items-center pt-4 border-t border-slate-200 dark:border-slate-700 shrink-0">
                    <div class="flex gap-2">
                        <button onclick="window.print()" class="bg-amber-500 hover:bg-amber-600 text-slate-950 font-black px-5 py-2.5 rounded-xl text-sm shadow">
                            🖨️ Cetak Struk
                        </button>
                        <button onclick="window.print()" class="bg-sky-600 hover:bg-sky-500 text-white font-black px-5 py-2.5 rounded-xl text-sm shadow">
                            🚛 Cetak Surat Jalan
                        </button>
                    </div>
                    <button wire:click="$set('showDetailModal', false)" class="bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 text-slate-900 dark:text-white font-bold px-6 py-2.5 rounded-xl text-sm">Tutup</button>
                </div>
            </div>
        </div>
    @endif

</div>
